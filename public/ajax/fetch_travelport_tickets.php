<?php
// ini_set('display_errors', 0);
// error_reporting(E_ALL);

require_once __DIR__ . '/../_auth.php'; // adjust if your auth path differs
require_once __DIR__ . '/../../config/db.php';
// require_once __DIR__ . '/../../config/functions.php'; // optional
require_once __DIR__ . '/../includes/TravelportAPI.php';

$data = json_decode(file_get_contents("php://input"), true);

$pnr = $data['pnr'] ?? '';
$booking_id = (int)($data['booking_id'] ?? 0);

if(!$pnr || !$booking_id){
    echo json_encode(['success'=>false,'error'=>'Invalid input']);
    exit;
}

/* ---------------- GET BOOKING ---------------- */
$stmt = $mysqli->prepare("
SELECT provider, travellers 
FROM bookings_flights 
WHERE booking_id=?
LIMIT 1
");
$stmt->bind_param("i",$booking_id);
$stmt->execute();
$res = $stmt->get_result();
$fbooking = $res->fetch_assoc();

if(($fbooking['provider'] ?? '') !== 'TRAVELPORT'){
    echo json_encode(['success'=>false,'error'=>'Not travelport']);
    exit;
}

try{

    $api = new TravelportAPI();

    /* ---------------- GET TICKETS (ROBUST) ---------------- */

    $ticketNumbers = [];

    /* 1️⃣ TRY RECEIPT LIST (FAST) */
    $ticketListResult = $api->getTicketList($pnr, $booking_id);
    $ticketData = $ticketListResult['data'] ?? [];

    $receiptList = $ticketData['ReceiptListResponse']['ReceiptID']
                ?? $ticketData['ReceiptID']
                ?? [];

    if(isset($receiptList['@type'])) $receiptList = [$receiptList];

    foreach($receiptList as $receipt){
        $documents = $receipt['Document'] ?? [];
        if(isset($documents['@type'])) $documents = [$documents];

        foreach($documents as $doc){
            $num = $doc['Number'] ?? $doc['number'] ?? null;
            if($num && !in_array($num,$ticketNumbers)){
                $ticketNumbers[] = $num;
            }
        }
    }

    /* 2️⃣ RETRY AFTER DELAY (important for one-way) */
    if(empty($ticketNumbers)){
        sleep(2);

        $ticketListResult = $api->getTicketList($pnr, $booking_id);
        $ticketData = $ticketListResult['data'] ?? [];

        $receiptList = $ticketData['ReceiptListResponse']['ReceiptID']
                    ?? $ticketData['ReceiptID']
                    ?? [];

        if(isset($receiptList['@type'])) $receiptList = [$receiptList];

        foreach($receiptList as $receipt){
            $documents = $receipt['Document'] ?? [];
            if(isset($documents['@type'])) $documents = [$documents];

            foreach($documents as $doc){
                $num = $doc['Number'] ?? $doc['number'] ?? null;
                if($num && !in_array($num,$ticketNumbers)){
                    $ticketNumbers[] = $num;
                }
            }
        }
    }

    /* 3️⃣ STRONG FALLBACK → RETRIEVE RESERVATION */
    if(empty($ticketNumbers)){

        $retrieveResult = $api->retrieveReservation($pnr, true, $booking_id);
        $resData = $retrieveResult['data'] ?? [];

        /* 🔥 extract from multiple possible paths */
        $ticketNumbers = extractTicketNumbers($resData);

        /* EXTRA fallback (important for one-way) */
        if(empty($ticketNumbers)){

            $offers = $resData['ReservationResponse']['Reservation']['Offer'] ?? [];

            foreach($offers as $offer){

                $products = $offer['Product'] ?? [];

                foreach($products as $prod){

                    $tickets = $prod['Ticket'] ?? [];

                    if(isset($tickets['@type'])) $tickets = [$tickets];

                    foreach($tickets as $t){
                        $num = $t['Number'] ?? $t['number'] ?? null;
                        if($num && !in_array($num,$ticketNumbers)){
                            $ticketNumbers[] = $num;
                        }
                    }
                }
            }
        }
    }

    /* ---------------- FALLBACK ---------------- */
    if(empty($ticketNumbers)){
        $retrieveResult = $api->retrieveReservation($pnr, true, $booking_id);
        $resData = $retrieveResult['data'] ?? [];
        $ticketNumbers = extractTicketNumbers($resData);
    }

    if(empty($ticketNumbers)){
        echo json_encode(['success'=>false,'error'=>'No tickets found']);
        exit;
    }

    /* ---------------- SAVE ---------------- */
    $ticketsStr = json_encode($ticketNumbers);

    $stmt = $mysqli->prepare("
        UPDATE bookings_flights 
        SET tickets=? 
        WHERE booking_id=?
    ");
    $stmt->bind_param("si",$ticketsStr,$booking_id);
    $stmt->execute();

    /* ---------------- INSERT TABLE ---------------- */
    foreach ($ticketNumbers as $i => $tkt) {

        $stmt = $mysqli->prepare("
        INSERT INTO travelport_tickets 
        (booking_id, passenger_id, ticket_number, status)
        VALUES (?, ?, ?, 'issued')
        ON DUPLICATE KEY UPDATE status='issued'
        ");

        $stmt->bind_param("iis",$booking_id,$i,$tkt);
        $stmt->execute();
    }

    echo json_encode([
        'success'=>true,
        'tickets'=>$ticketNumbers
    ]);

}catch(Exception $e){

    echo json_encode([
        'success'=>false,
        'error'=>$e->getMessage()
    ]);
}
?>