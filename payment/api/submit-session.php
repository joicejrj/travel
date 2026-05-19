<?php

header('Content-Type: application/json');

require_once __DIR__."/../config/config.php";
require_once __DIR__."/../includes/checkout.php";

/* ---------------------------------------------------
   METHOD CHECK
--------------------------------------------------- */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success"=>false,
        "message"=>"Method Not Allowed"
    ]);
    exit;
}

/**
 * Save submit debug info to the bookings_payments row.
 */
function saveSubmitDebug(string $sessionId, int $chargedAmount, array $debugLog): void {
    global $mysqli;
    try {

        $debug = json_encode($debugLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $stmt = $mysqli->prepare("
            UPDATE bookings_payments
            SET 
                debug_log = ?,
                charged_amount = ?
            WHERE session_id = ?
        ");

        $stmt->bind_param("sis",$debug,$chargedAmount,$sessionId);
        $stmt->execute();

        if($stmt->affected_rows === 0){
            error_log("saveSubmitDebug: No rows updated for session ".$sessionId);
        }

    } catch (Throwable $e) {

        error_log('saveSubmitDebug failed: ' . $e->getMessage());

    }
}

/* ---------------------------------------------------
   READ JSON INPUT
--------------------------------------------------- */

$input = json_decode(file_get_contents("php://input"), true);

if(!$input){
    http_response_code(422);
    echo json_encode([
        "success"=>false,
        "message"=>"Invalid JSON body"
    ]);
    exit;
}

$sessionId   = $input['session_id']   ?? '';
$sessionData = $input['session_data'] ?? null;
$amount      = (int)($input['amount'] ?? 0);

if(!$sessionId || !$sessionData || $amount < 1){
    http_response_code(422);
    echo json_encode([
        "success"=>false,
        "message"=>"session_id, session_data and amount are required"
    ]);
    exit;
}

/* ---------------------------------------------------
   DEBUG PREVIEW
--------------------------------------------------- */

$sessionDataPreview = $sessionData;

if(is_array($sessionData) && isset($sessionData['session_data'])){
    $sessionDataPreview = $sessionData['session_data'];
}

$debugLog = [
    "session_id"        => $sessionId,
    "amount_sent"       => $amount,
    "session_data_type" => gettype($sessionData),
    "session_data_preview" => is_string($sessionDataPreview)
        ? substr($sessionDataPreview,0,300)
        : json_encode($sessionDataPreview),
    "cko_response"      => null,
    "http_status"       => null,
    "error"             => null,
    "timestamp"         => date("Y-m-d H:i:s")
];

/* ---------------------------------------------------
   SUBMIT SESSION
--------------------------------------------------- */

try{

    [$ckoResponse,$httpStatus] =
        submitPaymentSessionDebug(
            $sessionId,
            $sessionData,
            $amount
        );

    $debugLog['cko_response'] = $ckoResponse;
    $debugLog['http_status']  = $httpStatus;

    if(function_exists("saveSubmitDebug")){
        saveSubmitDebug($sessionId,$amount,$debugLog);
    }

    echo json_encode($ckoResponse);

}
catch(Throwable $e){

    $debugLog['error'] = $e->getMessage();

    if(function_exists("saveSubmitDebug")){
        saveSubmitDebug($sessionId,$amount,$debugLog);
    }

    $friendly = mapCheckoutError($e->getMessage());

    http_response_code(200);

    echo json_encode([
        "success"=>false,
        "message"=>$friendly,
        "debug"=>defined("DEBUG_MODE") && DEBUG_MODE ? $e->getMessage() : null
    ]);
}

/* ---------------------------------------------------
   FRIENDLY CHECKOUT ERRORS
--------------------------------------------------- */

function mapCheckoutError(string $raw): string {

    $lower = strtolower($raw);

    if (str_contains($lower,'20005')) return 'This card was declined by the issuer.';
    if (str_contains($lower,'20051')) return 'Insufficient funds.';
    if (str_contains($lower,'20057')) return 'Transaction not permitted for this card.';
    if (str_contains($lower,'20061')) return 'Transaction amount limit exceeded.';
    if (str_contains($lower,'20062')) return 'This card is restricted.';
    if (str_contains($lower,'20059')) return 'Transaction flagged as suspicious.';
    if (str_contains($lower,'20154')) return '3D Secure authentication required.';
    if (str_contains($lower,'30041')) return 'This card has been reported lost.';
    if (str_contains($lower,'30043')) return 'This card has been reported stolen.';
    if (str_contains($lower,'declined')) return 'Payment was declined.';
    if (str_contains($lower,'not_supported')) return 'This card does not support this transaction type. Please try a different card.';
    if (str_contains($lower,'amex_scheme_not_configured')) return 'American Express is not enabled on this account. Please use a Visa or Mastercard.';
    if (str_contains($lower,'scheme_not_configured')) return 'This card scheme is not enabled on this account. Please use a different card.';
    if (str_contains($lower,'payment_attempt_failed')) return 'Payment attempt failed. Please try a different card.';
    if (str_contains($lower,'curl error')) return 'A network error occurred. Please try again.';

    return 'Payment could not be completed. Please try another card.';
}

?>