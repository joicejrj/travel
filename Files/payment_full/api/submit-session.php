<?php
// api/submit-session.php

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/checkout.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON body']);
    exit;
}

$sessionId   = $input['session_id']   ?? '';
$sessionData = $input['session_data'] ?? null;
$amount      = (int)($input['amount'] ?? 0);

if (!$sessionId || !$sessionData || $amount < 1) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'session_id, session_data and amount are required']);
    exit;
}

// Unwrap preview: show what token will actually be sent to the API
$sessionDataPreview = $sessionData;
if (is_array($sessionData) && isset($sessionData['session_data'])) {
    $sessionDataPreview = $sessionData['session_data'];  // bare token
}

$debugLog = [
    'session_id'        => $sessionId,
    'amount_sent'       => $amount,
    'session_data_type' => gettype($sessionData) . (is_array($sessionData) ? ' (keys: ' . implode(',', array_keys($sessionData)) . ')' : ''),
    'session_data_preview' => is_string($sessionDataPreview)
        ? substr($sessionDataPreview, 0, 300)
        : json_encode($sessionDataPreview),
    'cko_response'      => null,
    'http_status'       => null,
    'error'             => null,
    'timestamp'         => date('Y-m-d H:i:s'),
];

try {
    [$ckoResponse, $httpStatus] = submitPaymentSessionDebug($sessionId, $sessionData, $amount);

    $debugLog['cko_response'] = $ckoResponse;
    $debugLog['http_status']  = $httpStatus;

    // Save debug log and charged amount to DB
    saveSubmitDebug($sessionId, $amount, $debugLog);

    echo json_encode([
        'success'      => true,
        'cko_response' => $ckoResponse,
    ]);

} catch (Throwable $e) {
    $debugLog['error'] = $e->getMessage();
    saveSubmitDebug($sessionId, $amount, $debugLog);

    $friendly = mapCheckoutError($e->getMessage());
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => $friendly,
        'debug'   => DEBUG_MODE ? $e->getMessage() : null,
    ]);
}

function mapCheckoutError(string $raw): string {
    $lower = strtolower($raw);
    if (str_contains($lower, '20005'))           return 'This card was declined by the issuer. Please try a different card.';
    if (str_contains($lower, '20051'))           return 'Insufficient funds. Please try a different card.';
    if (str_contains($lower, '20057'))           return 'This transaction type is not permitted for this card.';
    if (str_contains($lower, '20061'))           return 'Transaction amount limit exceeded for this card.';
    if (str_contains($lower, '20062'))           return 'This card is restricted. Please contact your bank or try a different card.';
    if (str_contains($lower, '20059'))           return 'This transaction was flagged as suspicious. Please try a different card.';
    if (str_contains($lower, '20154'))           return '3D Secure authentication is required. Please try again.';
    if (str_contains($lower, '30041'))           return 'This card has been reported lost. Please use a different card.';
    if (str_contains($lower, '30043'))           return 'This card has been reported stolen. Please use a different card.';
    if (str_contains($lower, 'declined'))        return 'Your payment was declined. Please try a different card.';
    if (str_contains($lower, 'not_supported'))   return 'This card does not support this transaction type. Please try a different card.';
    if (str_contains($lower, 'curl error'))      return 'A network error occurred. Please try again.';
    return 'Payment could not be completed. Please try a different card or contact your bank.';
}

/**
 * Save submit debug info to the payment_sessions row.
 */
function saveSubmitDebug(string $sessionId, int $chargedAmount, array $debugLog): void {
    try {
        $db   = getDB();
        $stmt = $db->prepare("
            UPDATE payment_sessions
            SET debug_log      = :debug_log,
                charged_amount = :charged_amount
            WHERE session_id   = :session_id
        ");
        $stmt->execute([
            ':debug_log'      => json_encode($debugLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ':charged_amount' => $chargedAmount,
            ':session_id'     => $sessionId,
        ]);
    } catch (Throwable $e) {
        // Non-fatal — don't break the payment flow
        error_log('saveSubmitDebug failed: ' . $e->getMessage());
    }
}
