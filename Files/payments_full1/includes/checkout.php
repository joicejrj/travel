<?php
// includes/checkout.php  –  Checkout.com API helpers

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';

/**
 * POST /payment-sessions  →  returns id, payment_session_token, payment_session_secret
 */
function createPaymentSession(array $data): array {
    $url     = CKO_API_BASE . '/payment-sessions';
    $payload = buildSessionPayload($data);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . CKO_SECRET_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response   = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new RuntimeException('cURL error: ' . $curlError);
    }

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Invalid JSON from Checkout API: ' . $response);
    }

    if ($httpStatus !== 201) {
        $errorType  = $decoded['error_type'] ?? 'error';
        $errorCodes = isset($decoded['error_codes']) && is_array($decoded['error_codes'])
                      ? implode(' | ', $decoded['error_codes'])
                      : ($decoded['message'] ?? 'no detail returned');

        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("=== Checkout API {$httpStatus} ===");
            error_log("Payload:  " . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            error_log("Response: " . json_encode($decoded,  JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        throw new RuntimeException("Checkout API error ({$httpStatus}): [{$errorType}] {$errorCodes}");
    }

    // Attach the reference we sent so callers can save it — the API response doesn't echo it back
    $decoded['reference'] = $payload['reference'];

    return $decoded;
}

/**
 * GET /payments/{id}
 */
function getPaymentDetails(string $paymentId): array {
    $url = CKO_API_BASE . '/payments/' . urlencode($paymentId);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . CKO_SECRET_KEY],
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true) ?? [];
}

/**
 * Build the /payment-sessions request payload.
 * processing_channel_id is required by the sandbox even though
 * Checkout.com's basic doc sample omits it.
 */
function buildSessionPayload(array $data): array {
    $reference  = 'REF-' . strtoupper(bin2hex(random_bytes(6)));
    $currency   = strtoupper($data['currency'] ?? APP_CURRENCY);
    $country    = strtoupper($data['billing_country'] ?? 'GB');
    $email      = $data['customer_email'];

    // Look up existing Checkout.com customer ID for this email (returning customer)
    $ckoCustomerId = getCustomerIdByEmail($email);

    $payload = [
        'amount'                 => (int) $data['amount'],
        'currency'               => $currency,
        'reference'              => $reference,
        'display_name'           => APP_NAME,
        'processing_channel_id'  => CKO_PROCESSING_CHANNEL,
        'billing'                => [
            'address' => [
                'country' => $country,
            ],
        ],
        'customer'               => [
            'name'  => $data['customer_name'],
            'email' => $email,
        ],
        '3ds'                    => [
            'enabled'             => true,
            'challenge_indicator' => 'challenge_requested',
        ],
        // Always ask the customer if they want to save their card
        'payment_method_configuration' => [
            'card' => [
                'store_payment_details' => 'collect_consent',
            ],
        ],
        'success_url'            => SUCCESS_URL . '&ref=' . $reference,
        'failure_url'            => FAILURE_URL . '&ref=' . $reference,
    ];

    // If returning customer, show their stored cards in Flow
    if ($ckoCustomerId) {
        $payload['payment_method_configuration']['stored_card'] = [
            'customer_id' => $ckoCustomerId,
        ];
    }

    return $payload;
}

/**
 * Persist a new session row.
 */
function saveSession(array $formData, array $apiResponse): string {
    $db  = getDB();
    $ref = $apiResponse['reference'] ?? ('REF-' . time());

    $stmt = $db->prepare("
        INSERT INTO payment_sessions
            (session_id, reference, amount, currency, customer_name, customer_email, description, raw_response)
        VALUES
            (:session_id, :reference, :amount, :currency, :customer_name, :customer_email, :description, :raw_response)
    ");

    $stmt->execute([
        ':session_id'     => $apiResponse['id'],
        ':reference'      => $ref,
        ':amount'         => (int) $formData['amount'],
        ':currency'       => strtoupper($formData['currency'] ?? APP_CURRENCY),
        ':customer_name'  => $formData['customer_name'],
        ':customer_email' => $formData['customer_email'],
        ':description'    => $formData['description'] ?? '',
        ':raw_response'   => json_encode($apiResponse),
    ]);

    return $ref;
}

/**
 * Update payment status after redirect.
 */
function updateSessionStatus(string $reference, string $paymentId): ?array {
    $details = getPaymentDetails($paymentId);
    if (empty($details)) return null;

    $status        = strtolower($details['status'] ?? 'pending');
    $method        = $details['payment_type'] ?? ($details['source']['type'] ?? null);
    $ckoCustomerId = $details['customer']['id'] ?? null;
    $email         = $details['customer']['email'] ?? null;

    $db   = getDB();
    $stmt = $db->prepare("
        UPDATE payment_sessions
        SET payment_id      = :payment_id,
            status          = :status,
            payment_method  = :method,
            raw_response    = :raw_response,
            cko_customer_id = :cko_customer_id
        WHERE reference     = :reference
    ");
    $stmt->execute([
        ':payment_id'      => $paymentId,
        ':status'          => in_array($status, ['authorized','captured','declined','cancelled','expired'])
                              ? $status : 'pending',
        ':method'          => $method,
        ':raw_response'    => json_encode($details),
        ':cko_customer_id' => $ckoCustomerId,
        ':reference'       => $reference,
    ]);

    // Persist email → customer ID mapping for future returning-customer sessions
    if ($ckoCustomerId && $email) {
        upsertCustomer($email, $ckoCustomerId);
    }

    return $details;
}

/**
 * Fetch session row by reference.
 */
function getSessionByReference(string $reference): ?array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM payment_sessions WHERE reference = :ref LIMIT 1");
    $stmt->execute([':ref' => $reference]);
    return $stmt->fetch() ?: null;
}

/**
 * Update session status when we only have the payment ID (async redirect, no ref in URL).
 */
function updateSessionStatusById(string $paymentId): ?array {
    $details = getPaymentDetails($paymentId);
    if (empty($details)) return null;

    $status = strtolower($details['status'] ?? 'pending');
    $method = $details['payment_type'] ?? ($details['source']['type'] ?? null);

    $db   = getDB();
    $psId          = $details['metadata']['cko_payment_session_id'] ?? '';
    $ckoCustomerId = $details['customer']['id']    ?? null;
    $email         = $details['customer']['email'] ?? null;

    $stmt = $db->prepare("
        UPDATE payment_sessions
        SET payment_id      = :payment_id,
            status          = :status,
            payment_method  = :method,
            raw_response    = :raw_response,
            cko_customer_id = :cko_customer_id
        WHERE payment_id    = :payment_id
           OR (session_id   = :session_id AND :session_id != '')
    ");
    $stmt->execute([
        ':payment_id'      => $paymentId,
        ':status'          => in_array($status, ['authorized','captured','declined','cancelled','expired'])
                              ? $status : 'pending',
        ':method'          => $method,
        ':raw_response'    => json_encode($details),
        ':cko_customer_id' => $ckoCustomerId,
        ':session_id'      => $psId,
    ]);

    if ($ckoCustomerId && $email) {
        upsertCustomer($email, $ckoCustomerId);
    }

    return $details;
}

/**
 * Fetch session row by payment ID (for async redirects where ref isn't in the URL).
 */
function getSessionByPaymentId(string $paymentId): ?array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM payment_sessions WHERE payment_id = :pid LIMIT 1");
    $stmt->execute([':pid' => $paymentId]);
    return $stmt->fetch() ?: null;
}

/**
 * GET /payments — search by reference to find a payment ID when we only have ref.
 */
function getPaymentByReference(string $reference): ?array {
    // The payments search API uses the base sandbox URL without the custom prefix
    $baseUrl = 'https://api.sandbox.checkout.com/payments?reference=' . urlencode($reference) . '&limit=1';

    // Also try with the configured prefix (some accounts use it for all endpoints)
    $prefixUrl = CKO_API_BASE . '/payments?reference=' . urlencode($reference) . '&limit=1';

    foreach ([$prefixUrl, $baseUrl] as $url) {
        // Retry up to 3 times with a short delay — 3DS processing can be slightly async
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            if ($attempt > 1) sleep(1);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . CKO_SECRET_KEY,
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT => 15,
            ]);
            $response   = curl_exec($ch);
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (DEBUG_MODE) {
                error_log("getPaymentByReference [{$attempt}] URL={$url} HTTP={$httpStatus} response=" . substr($response, 0, 300));
            }

            if ($httpStatus === 200) {
                $decoded = json_decode($response, true);
                if (!empty($decoded['data'][0])) {
                    return $decoded['data'][0];
                }
            }
        }
    }

    return null;
}

/**
 * Fetch session row by Checkout.com payment session ID (ps_xxx).
 */
function getSessionBySessionId(string $sessionId): ?array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM payment_sessions WHERE session_id = :sid LIMIT 1");
    $stmt->execute([':sid' => $sessionId]);
    return $stmt->fetch() ?: null;
}

/**
 * POST /payment-sessions/{id}/submit
 * Submits the session with optional modified amount (e.g. corporate card surcharge).
 * Returns the raw Checkout.com response which Flow.js needs to handle 3DS / redirects.
 */
function submitPaymentSession(string $sessionId, array|string $sessionData, int $amount): array {
    $url = CKO_API_BASE . '/payment-sessions/' . urlencode($sessionId) . '/submit';

    // Flow.js passes session_data as a string token — pass it through as-is.
    // Only encode if we somehow received an array.
    $sessionDataFinal = is_array($sessionData)
        ? json_encode($sessionData)
        : $sessionData;

    $payload = [
        'session_data' => $sessionDataFinal,
        'amount'       => $amount,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . CKO_SECRET_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response   = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new RuntimeException('cURL error: ' . $curlError);
    }

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Invalid JSON from Checkout API: ' . $response);
    }

    // Always log submit details when DEBUG_MODE is on
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("=== /submit {$httpStatus} ===");
        error_log("URL:      {$url}");
        error_log("session_data type: " . gettype($sessionData));
        error_log("session_data encoded: " . substr($sessionDataFinal, 0, 200));
        error_log("amount sent: {$amount}");
        error_log("Response: " . json_encode($decoded, JSON_PRETTY_PRINT));
    }

    if ($httpStatus >= 400) {
        $errorType  = $decoded['error_type'] ?? 'error';
        $errorCodes = isset($decoded['error_codes']) && is_array($decoded['error_codes'])
                      ? implode(' | ', $decoded['error_codes'])
                      : ($decoded['message'] ?? 'no detail');

        throw new RuntimeException("Submit error ({$httpStatus}): [{$errorType}] {$errorCodes}");
    }

    return $decoded;
}

/**
 * Same as submitPaymentSession but returns [response, httpStatus] for debug logging.
 */
function submitPaymentSessionDebug(string $sessionId, array|string $sessionData, int $amount): array {
    $url = CKO_API_BASE . '/payment-sessions/' . urlencode($sessionId) . '/submit';

    // Flow.js gives us { "session_data": "eyJ..." } — unwrap to get the raw token string.
    // The API expects the bare base64 string, not the wrapper object.
    if (is_array($sessionData) && isset($sessionData['session_data'])) {
        $sessionDataFinal = $sessionData['session_data'];
    } elseif (is_array($sessionData)) {
        $sessionDataFinal = json_encode($sessionData);
    } else {
        $sessionDataFinal = $sessionData;
    }

    $payload = [
        'session_data' => $sessionDataFinal,
        'amount'       => $amount,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . CKO_SECRET_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response   = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    if ($curlError) throw new RuntimeException('cURL error: ' . $curlError);

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Invalid JSON from Checkout API: ' . $response);
    }

    if ($httpStatus >= 400) {
        $errorType  = $decoded['error_type'] ?? 'error';
        $errorCodes = isset($decoded['error_codes']) && is_array($decoded['error_codes'])
                      ? implode(' | ', $decoded['error_codes'])
                      : ($decoded['message'] ?? 'no detail');
        throw new RuntimeException("Submit error ({$httpStatus}): [{$errorType}] {$errorCodes}");
    }

    return [$decoded, $httpStatus];
}

/**
 * Look up Checkout.com customer ID by email from our local mapping table.
 * Returns null if the customer has never paid before (new customer).
 */
function getCustomerIdByEmail(string $email): ?string {
    try {
        $db   = getDB();
        $stmt = $db->prepare("SELECT cko_customer_id FROM checkout_customers WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ? $row['cko_customer_id'] : null;
    } catch (Throwable $e) {
        return null;
    }
}

/**
 * Save or update the email → Checkout.com customer ID mapping.
 * Called after a successful payment when we receive customer.id from the API.
 */
function upsertCustomer(string $email, string $ckoCustomerId): void {
    try {
        $db   = getDB();
        $stmt = $db->prepare("
            INSERT INTO checkout_customers (email, cko_customer_id)
            VALUES (:email, :cko_customer_id)
            ON DUPLICATE KEY UPDATE
                cko_customer_id = VALUES(cko_customer_id),
                updated_at      = CURRENT_TIMESTAMP
        ");
        $stmt->execute([
            ':email'           => $email,
            ':cko_customer_id' => $ckoCustomerId,
        ]);
    } catch (Throwable $e) {
        error_log('upsertCustomer failed: ' . $e->getMessage());
    }
}
