<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../public/_auth.php';
require_once __DIR__ . '/../../lib/amadeus.php';

header('Content-Type: application/json');

$customer_id = !empty($_POST['contact_entity_id']) ? (int)$_POST['contact_entity_id'] : null;

try {
    $origin        = strtoupper(trim($_POST['origin'] ?? ''));
    $destination   = strtoupper(trim($_POST['destination'] ?? ''));
    $departureDate = $_POST['departureDate'] ?? '';
    $returnDate    = $_POST['returnDate'] ?? '';
    $adults        = max(1, (int)($_POST['adults'] ?? 1));
    $travelClass   = $_POST['travelClass'] ?? 'ECONOMY';
    $nonStop       = !empty($_POST['nonStop']);
    $max           = min(50, max(1, (int)($_POST['max'] ?? 10)));

    if (!$origin || !$destination || !$departureDate) {
        throw new Exception('Missing required fields'.($origin." ".$destination." ".$departureDate));
    }

    if ($origin === $destination) {
        throw new Exception('Origin and destination cannot be the same');
    }

    if ($returnDate && $returnDate <= $departureDate) {
        throw new Exception('Return date must be after departure date');
    }

    $query = [
        'originLocationCode'      => $origin,
        'destinationLocationCode' => $destination,
        'departureDate'           => $departureDate,
        'adults'                  => $adults,
        'currencyCode'            => 'GBP',
        'max'                     => $max
    ];

    if ($returnDate)  $query['returnDate']  = $returnDate;
    if ($travelClass) $query['travelClass'] = $travelClass;
    if ($nonStop)     $query['nonStop']     = 'true';

    $resp = amadeus_request('GET', '/v2/shopping/flight-offers', null, $query);

    if($customer_id!=null) {
        $site->agent_log("Searched flights with origin: $origin, destination: $destination, departureDate: $departureDate, pax: $adults".($returnDate?", returnDate: $returnDate":"").", class: $travelClass".($nonStop?", non-stop: Yes":"")."",$customer_id,"customer");
    }

    /* ---------------------------------------------------
       SAVE SEARCH HISTORY
    --------------------------------------------------- */

    if($customer_id){

        $stmt = $mysqli->prepare("
            INSERT INTO flight_search_history
            (
                customer_id,
                origin,
                destination,
                departure_date,
                return_date,
                adults,
                travel_class,
                non_stop,
                currency,
                max_results,
                search_payload
            )
            VALUES
            (?,?,?,?,?,?,?,?,?,?,?)
        ");

        $payload = json_encode($query);

        $stmt->bind_param(
            "issssisssss",
            $customer_id,
            $origin,
            $destination,
            $departureDate,
            $returnDate,
            $adults,
            $travelClass,
            $nonStop,
            $query['currencyCode'],
            $max,
            $payload
        );

        $stmt->execute();
    }

    echo json_encode([
        'success' => true,
        'offers'  => $resp['data'] ?? []
    ]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
?>