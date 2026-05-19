<?php
// api/create-session.php  –  Called via AJAX from index.php

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/checkout.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$data = [];

foreach (['customer_name', 'customer_email', 'amount', 'currency'] as $field) {
    $value = trim($_POST[$field] ?? '');
    if ($value === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => "Field '{$field}' is required."]);
        exit;
    }
    $data[$field] = $value;
}

$data['customer_email']  = filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL);
if (!$data['customer_email']) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$data['amount']          = (int) preg_replace('/[^0-9]/', '', $data['amount']);
$data['billing_country'] = preg_replace('/[^A-Z]/', '', strtoupper($_POST['billing_country'] ?? 'GB'));
$data['description']     = htmlspecialchars(trim($_POST['description'] ?? 'Sandbox test payment'), ENT_QUOTES, 'UTF-8');

if ($data['amount'] < 1) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Amount must be greater than zero.']);
    exit;
}

try {
    $apiResponse = createPaymentSession($data);
    $reference   = saveSession($data, $apiResponse);

    // Return the FULL Checkout.com API response object.
    // Flow.js needs the entire response as `paymentSession` — not just the token.
    echo json_encode([
        'success'        => true,
        'reference'      => $reference,
        'paymentSession' => $apiResponse,   // ← passed directly to CheckoutWebComponents()
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => DEBUG_MODE ? $e->getMessage() : 'Failed to create payment session.',
    ]);
}
