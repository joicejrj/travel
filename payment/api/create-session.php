<?php

require_once __DIR__."/../config/config.php";
require_once __DIR__."/../includes/checkout.php";

header("Content-Type: application/json");

$token = $_POST['token'] ?? '';

if(!$token){
    echo json_encode([
        "success"=>false,
        "message"=>"Invalid token"
    ]);
    exit;
}

/* ---------------------------------------------------
   GET BOOKING + CUSTOMER
--------------------------------------------------- */

$stmt = $mysqli->prepare("
SELECT 
    b.id,
    b.type_id,
    b.status,
    c.name  AS customer_name,
    c.phone AS customer_phone,
    c.email AS customer_email
FROM bookings b
LEFT JOIN customers c 
    ON c.id = b.contact_entity_id
WHERE b.payment_token = ?
LIMIT 1
");

$stmt->bind_param("s",$token);
$stmt->execute();

$res = $stmt->get_result();
$booking = $res->fetch_assoc();

if(!$booking){
    echo json_encode([
        "success"=>false,
        "message"=>"Booking not found"
    ]);
    exit;
}

$booking_id = (int)$booking['id'];
$type_id    = (int)$booking['type_id'];

/* ---------------------------------------------------
   IF BOOKING ALREADY PAID
--------------------------------------------------- */

if($booking['status'] === "Payment Success"){

    $stmt = $mysqli->prepare("
    SELECT payment_id,reference
    FROM bookings_payments
    WHERE booking_id = ?
    LIMIT 1
    ");

    $stmt->bind_param("i",$booking_id);
    $stmt->execute();

    $res = $stmt->get_result();
    $payment = $res->fetch_assoc();

    echo json_encode([
        "success"=>true,
        "reference"=>$payment['reference'],
        "paymentSession"=>[
            "id"=>$payment['payment_id']
        ]
    ]);

    exit;
}

/* ---------------------------------------------------
   GET AMOUNT
--------------------------------------------------- */

if($type_id == 1){

    $stmt = $mysqli->prepare("
    SELECT total_amount AS amount, currency
    FROM bookings_flights
    WHERE booking_id = ?
    LIMIT 1
    ");

    $description = "Flight Booking #".$booking_id;
}
elseif($type_id == 2){

    $stmt = $mysqli->prepare("
    SELECT final_amount AS amount, currency_symbol AS currency
    FROM bookings_tours
    WHERE booking_id = ?
    LIMIT 1
    ");

    $description = "Tour Booking #".$booking_id;
}
else{

    echo json_encode([
        "success"=>false,
        "message"=>"Invalid booking type"
    ]);
    exit;

}

$stmt->bind_param("i",$booking_id);
$stmt->execute();

$res = $stmt->get_result();
$row = $res->fetch_assoc();

if(!$row){
    echo json_encode([
        "success"=>false,
        "message"=>"Payment details not found"
    ]);
    exit;
}

$amount   = (int) round(((float)$row['amount']) * 100);
$currency = trim($row['currency']);

/* ---------------------------------------------------
   CREATE NEW CHECKOUT SESSION
--------------------------------------------------- */

$data = [

    "customer_name"  => $booking['customer_name'],
    "customer_email" => $booking['customer_email'],
    "amount"         => $amount,
    "currency"       => $currency,
    "billing_country"=> "GB"

];

// Look up booking ID
$data['booking_id'] = $booking_id;

try{

    $apiResponse = createPaymentSession($data);

}catch(Throwable $e){

    echo json_encode([
        "success"=>false,
        "message"=>$e->getMessage()
    ]);
    exit;

}

$reference = $apiResponse['reference'] ?? ('REF-' . time());
$raw_response = json_encode($apiResponse);

/* ---------------------------------------------------
   CHECK IF PAYMENT ROW EXISTS
--------------------------------------------------- */

$stmt = $mysqli->prepare("
SELECT id
FROM bookings_payments
WHERE booking_id = ?
LIMIT 1
");

$stmt->bind_param("i",$booking_id);
$stmt->execute();

$res = $stmt->get_result();
$existing = $res->fetch_assoc();

/* ---------------------------------------------------
   UPDATE OR INSERT
--------------------------------------------------- */

if($existing){

    $stmt = $mysqli->prepare("
    UPDATE bookings_payments
    SET
        session_id=?,
        reference=?,
        amount=?,
        currency=?,
        status='pending',
        customer_name=?,
        customer_email=?,
        description=?,
        raw_response=?
    WHERE booking_id=?
    ");

    $stmt->bind_param(
        "ssisssssi",
        $apiResponse['id'],
        $reference,
        $amount,
        $currency,
        $booking['customer_name'],
        $booking['customer_email'],
        $description,
        $raw_response,
        $booking_id
    );

}else{

    $stmt = $mysqli->prepare("
    INSERT INTO bookings_payments
    (
        booking_id,
        session_id,
        reference,
        amount,
        currency,
        status,
        customer_name,
        customer_email,
        description,
        raw_response
    )
    VALUES
    (
        ?,?,?,?,?, 'pending',?,?,?,?
    )
    ");

    $stmt->bind_param(
        "ississsss",
        $booking_id,
        $apiResponse['id'],
        $reference,
        $amount,
        $currency,
        $booking['customer_name'],
        $booking['customer_email'],
        $description,
        $raw_response
    );

}

$stmt->execute();

/* ---------------------------------------------------
   RESPONSE
--------------------------------------------------- */

echo json_encode([
    "success"=>true,
    "reference"=>$reference,
    "paymentSession"=>$apiResponse
]);

?>