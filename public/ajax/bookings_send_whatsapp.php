<?php
session_start();

// require_once __DIR__ . '/../../config/db.php';        // must provide $mysqli
// require_once __DIR__ . '/../../config/functions.php';
// require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$contact_id  = $_POST['contact_id'] ?? '';
$booking_id  = $_POST['booking_id'] ?? '';
$travel_date = $_POST['travel_date'] ?? '';
$url = $_POST['url'] ?? '';
$amount      = $_POST['amount'] ?? '';

$apiUrl = 'https://travel.jrjapp.com/Whatsapp/sendAgentTemplateMediaAPI.php';

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => [
        'contact_id'  => $contact_id,
        'booking_id'  => $booking_id,
        'travel_date' => $travel_date,
        'url'         => $url,
        'amount'      => $amount
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);
$error    = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(['success' => false, 'error' => $error]);
    exit;
}
// var_dump($response);
// die();

$res = json_decode($response, true);

if (isset($res['status']) && $res['status'] == '202') {
    echo json_encode([
        'success'   => true,
        'messageId' => $res['messageId'] ?? null,
        // 'response' => $res
    ]);
} else {
    echo json_encode([
        'success'  => false,
        'response' => $res
    ]);
}
?>