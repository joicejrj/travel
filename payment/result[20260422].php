<?php

require_once __DIR__."/config/config.php";
require_once __DIR__."/../config/functions.php";
require_once __DIR__."/includes/checkout.php";

$paymentId = $_GET['cko-payment-id'] ?? '';
$reference = $_GET['ref'] ?? '';

$status = "pending";
$payment = null;

/* ---------------------------------------------------
   FETCH PAYMENT DETAILS FROM CHECKOUT
--------------------------------------------------- */

if($paymentId){

    $details = getPaymentDetails($paymentId);

    if (empty($details)) return null;

    $status = strtolower($details['status'] ?? 'failed');
    $method        = $details['payment_type'] ?? ($details['source']['type'] ?? null);
    $ckoCustomerId = $details['customer']['id'] ?? null;
    $email         = $details['customer']['email'] ?? null;

    $raw = json_encode($details);

    $chargedAmount = (int)($details['amount'] ?? 0);
    // $cardCategory = strtolower($details['source']['card_category'] ?? '');
    // $cardType     = $details['source']['card_type'] ?? null;
    // $cardScheme   = $details['source']['scheme'] ?? null;
    $baseAmount = $payment['amount'] ?? 0;
    $feeAmount  = $chargedAmount - $baseAmount;

    /* ---------------------------------------------------
       UPDATE bookings_payments
    --------------------------------------------------- */

    $stmt = $mysqli->prepare("
    UPDATE bookings_payments
    SET
        payment_id=?,
        status=?,
        payment_method=?,
        raw_response=?,
        cko_customer_id=?
    WHERE reference=?
    ");

    $stmt->bind_param(
        "ssssss",
        $paymentId,
        $status,
        $method,
        $raw,
        $ckoCustomerId,
        $reference
    );

    $stmt->execute();

    // Persist email → customer ID mapping for future returning-customer sessions
    if ($ckoCustomerId && $email) {
        upsertCustomer($email, $ckoCustomerId);
    }


    /* ---------------------------------------------------
       GET BOOKING ID
    --------------------------------------------------- */

    $stmt = $mysqli->prepare("
    SELECT p.booking_id,p.amount,p.currency,p.customer_name,p.customer_email,p.description,p.created_at,b.contact_entity_id,b.type_id
    FROM bookings_payments as p left join bookings as b on b.id=p.booking_id
    WHERE p.reference=?
    LIMIT 1
    ");

    $stmt->bind_param("s",$reference);
    $stmt->execute();

    $res = $stmt->get_result();
    $payment = $res->fetch_assoc();


    /* ---------------------------------------------------
       UPDATE BOOKINGS TABLE STATUS
    --------------------------------------------------- */

    if($payment){

        $booking_id = $payment['booking_id'];

        if(in_array($status,['authorized','captured','success'])){
            $bookingStatus = "Payment Success";
        }else{
            $bookingStatus = "Payment Failed";
        }

        $stmt = $mysqli->prepare("
        UPDATE bookings
        SET status=?
        WHERE id=?
        ");

        $stmt->bind_param("si",$bookingStatus,$booking_id);
        $stmt->execute();

        // if flight booking and provider=travelport - then get tickets and update
        if($payment['type_id']=='1') {

            $stmt1 = $mysqli->prepare("SELECT provider, pnr FROM bookings_flights WHERE booking_id=? LIMIT 1");
            $stmt1->bind_param("i",$payment['booking_id']);
            $stmt1->execute();
            $res1 = $stmt1->get_result();
            $fbooking = $res1->fetch_assoc();
            $stmt1->close();

            if(($fbooking['provider'] ?? '') === "TRAVELPORT"){

                try{
                    require_once __DIR__ . '/../public/includes/TravelportAPI.php';

                    $api = new TravelportAPI(); // already initialized

                    sleep(2); // allow ticketing

                    $ticketListResult = $api->getTicketList($fbooking['pnr']);
                    $ticketData = $ticketListResult['data'] ?? [];
                    $ticketNumbers = [];

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

                    // fallback
                    if(empty($ticketNumbers)){
                        $retrieveResult = $api->retrieveReservation($fbooking['pnr']);
                        $resData = $retrieveResult['data'] ?? [];
                        $ticketNumbers = extractTicketNumbers($resData);
                    }

                    // ✅ UPDATE bookings_flights table
                    if(!empty($ticketNumbers)){

                        // $ticketsStr = implode(',', $ticketNumbers);
                        $ticketsStr = json_encode($ticketNumbers);

                        $stmt2 = $mysqli->prepare("
                            UPDATE bookings_flights 
                            SET tickets = ? 
                            WHERE booking_id = ?
                        ");
                        $stmt2->bind_param("si", $ticketsStr, $payment['booking_id']);
                        $stmt2->execute();
                        $stmt2->close();

                    }else{
                        // error_log("No tickets found for ".$fbooking['pnr']);
                    }

                }catch(Exception $e){
                    // error_log("Ticket fetch failed: ".$e->getMessage());
                }
            }
        }


        /* ---------------------------------------------------
           ADD TIMELINE ENTRY
        --------------------------------------------------- */

        $amountFormatted = $payment['currency']."".number_format($payment['amount']/100,2);

        $timelineMessage = "Payment Result\n".
        "Status : ".strtoupper($status)."\n".
        "Amount : ".$amountFormatted."\n".
        "Reference : ".$reference."\n".
        "Payment ID : ".$paymentId."\n".
        "Customer : ".$payment['customer_name']." (".$payment['customer_email'].")";

        // AGENT LOG (Timeline)
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $logarr = array('customer_id' =>$payment['contact_entity_id'],'log' => $timelineMessage,'type'=>'general','ip'=> $user_ip,'timestamp' => $datetime);
        $db->insert('people_logs',$logarr);

        // BOOKINGS FOLLOWUP ENTRY
        $stmt = $mysqli->prepare("
        INSERT INTO bookings_followup
        (
            booking_id,
            note_text,
            created_at
        )
        VALUES
        (
            ?, ?, NOW()
        )
        ");

        $stmt->bind_param(
            "is",
            $booking_id,
            $timelineMessage,
        );

        $stmt->execute();

    }
}

/* ---------------------------------------------------
   UI STATUS CONFIG
--------------------------------------------------- */

$statusConfig = [

"authorized"=>["icon"=>"bi-check-circle-fill","color"=>"success","label"=>"Payment Authorized"],
"captured"=>["icon"=>"bi-check-circle-fill","color"=>"success","label"=>"Payment Successful"],
"success"=>["icon"=>"bi-check-circle-fill","color"=>"success","label"=>"Payment Successful"],

"declined"=>["icon"=>"bi-x-circle-fill","color"=>"danger","label"=>"Payment Declined"],
"failure"=>["icon"=>"bi-x-circle-fill","color"=>"danger","label"=>"Payment Failed"],
"cancel"=>["icon"=>"bi-x-circle-fill","color"=>"danger","label"=>"Payment Cancelled"],

"pending"=>["icon"=>"bi-hourglass-split","color"=>"warning","label"=>"Payment Pending"],
];

$cfg = $statusConfig[$status] ?? [
    "icon"=>"bi-question-circle",
    "color"=>"secondary",
    "label"=>"Unknown Status"
];

function currencySymbol($currency){

    $symbols = [
        "USD"=>"$",
        "EUR"=>"€",
        "GBP"=>"£",
        "AED"=>"د.إ",
        "SAR"=>"﷼",
        "INR"=>"₹",
        "JPY"=>"¥",
        "CAD"=>"$",
        "AUD"=>"$",
        "SGD"=>"$",
    ];

    return $symbols[$currency] ?? $currency;
}
?>
<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Payment Result</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
background:#f3f6fb;
font-family:system-ui;
}

.result-wrapper{
max-width:720px;
margin:auto;
margin-top:70px;
}

.result-card{
border-radius:14px;
overflow:hidden;
}

.status-banner{
padding:40px 20px;
text-align:center;
}

.status-banner.success{
background:#eafaf1;
}

.status-banner.danger{
background:#fdecea;
}

.status-banner.warning{
background:#fff8e6;
}

.status-icon{
font-size:70px;
}

.amount{
font-size:32px;
font-weight:600;
margin-top:10px;
}

.ref{
font-family:monospace;
font-size:13px;
color:#6c757d;
margin-top:5px;
}

.detail-card{
background:white;
border-radius:10px;
padding:20px;
}

.detail-row{
display:flex;
justify-content:space-between;
padding:12px 0;
border-bottom:1px solid #f0f0f0;
}

.detail-row:last-child{
border-bottom:none;
}

.detail-label{
color:#6c757d;
}

.detail-value{
font-weight:500;
}

.footer-actions{
padding:25px;
text-align:center;
}

.btn-primary{
padding:10px 30px;
border-radius:8px;
}

</style>

</head>

<body>

<div class="container result-wrapper">

<div class="card shadow result-card">

<!-- STATUS BANNER -->

<div class="status-banner <?= $cfg['color'] ?>">

<div class="status-icon text-<?= $cfg['color'] ?>">
<i class="bi <?= $cfg['icon'] ?>"></i>
</div>

<h3 class="mt-3"><?= $cfg['label'] ?></h3>

<?php if($payment): ?>

<?php
$totalCharged = $chargedAmount ?: $payment['amount'];
$fee = max(0, $totalCharged - $payment['amount']);
?>

<div class="amount">
<?= currencySymbol($payment['currency']) ?><?= number_format($totalCharged/100,2) ?>
</div>

<?php if($fee > 0): ?>
<div class="text-muted mt-2">

<div>Subtotal: <?= currencySymbol($payment['currency']) ?><?= number_format($payment['amount']/100,2) ?></div>

<div>Corporate Card Fee: <?= currencySymbol($payment['currency']) ?><?= number_format($fee/100,2) ?></div>

</div>
<?php endif; ?>

<?php endif; ?>

<?php if($reference): ?>
<div class="ref">Reference: <?= htmlspecialchars($reference) ?></div>
<?php endif; ?>

</div>

<!-- PAYMENT DETAILS -->

<?php if($payment): ?>

<div class="card-body">

<div class="detail-card">

<div class="detail-row">
<span class="detail-label">Status</span>
<span class="detail-value text-<?= $cfg['color'] ?>">
<?= strtoupper($status) ?>
</span>
</div>

<div class="detail-row">
<span class="detail-label">Customer</span>
<span class="detail-value"><?= htmlspecialchars($payment['customer_name']) ?></span>
</div>

<div class="detail-row">
<span class="detail-label">Email</span>
<span class="detail-value"><?= htmlspecialchars($payment['customer_email']) ?></span>
</div>

<?php if($payment['description']): ?>
<div class="detail-row">
<span class="detail-label">Description</span>
<span class="detail-value"><?= htmlspecialchars($payment['description']) ?></span>
</div>
<?php endif; ?>

<?php if($paymentId): ?>
<div class="detail-row">
<span class="detail-label">Payment ID</span>
<span class="detail-value"><?= htmlspecialchars($paymentId) ?></span>
</div>
<?php endif; ?>

<div class="detail-row">
<span class="detail-label">Created</span>
<span class="detail-value"><?= date("d M Y, h:i A",strtotime($payment['created_at'])) ?></span>
</div>

</div>

</div>

<?php endif; ?>


<!-- ACTIONS -->

<!-- <div class="footer-actions">

<a href="/" class="btn btn-primary">
<i class="bi bi-house"></i> Back to Home
</a>

</div> -->

</div>

</div>

</body>
</html>