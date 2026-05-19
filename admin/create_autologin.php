<?php
// public/create_autologin.php
// Creates an autologin token and returns JSON.
// NOTE: This version allows any caller who POSTs the required fields to create a token.
// If you want to restrict creation to admins, re-enable the require-login block below.

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// avoid PHP warnings breaking JSON
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');

// adjust this require path if your config is elsewhere
$dbPath = __DIR__ . '/../config/db.php';
if (!file_exists($dbPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'DB config file not found: ' . $dbPath]);
    exit;
}
require_once $dbPath;

/* --------------------------
   OPTIONAL: restrict generator
   --------------------------
   If you want only logged-in users to create tokens, uncomment:
*/
// if (empty($_SESSION['person_id'])) {
//     http_response_code(403);
//     echo json_encode(['error' => 'Permission denied (not logged in)']);
//     exit;
// }

/* ---------- CSRF handling ----------
   If your page includes a CSRF token in session + form, keep this check.
   If you set the page to be usable without session (public), you can remove this block.
*/
if (!empty($_SESSION['csrf_token'])) {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
} else {
    // No CSRF in session — caller probably not logged-in or session not present.
    // We allow it, but you might prefer to reject such requests:
    // http_response_code(403); echo json_encode(['error'=>'No session/CSRF']); exit;
    // For now, continue but set created_by = NULL (anonymous).
}

/* ------------------------
   Validate incoming payload
   ------------------------ */
$person_id = (int)($_POST['person_id'] ?? 0);
if ($person_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid person_id']);
    exit;
}

// verify person exists
if (!($stmt = $mysqli->prepare("SELECT id FROM people WHERE id = ? LIMIT 1"))) {
    http_response_code(500);
    echo json_encode(['error' => 'DB prepare error: ' . $mysqli->error]);
    exit;
}
$stmt->bind_param('i', $person_id);
$stmt->execute();
$res = $stmt->get_result();
$person = $res->fetch_assoc();
$stmt->close();
if (!$person) {
    http_response_code(404);
    echo json_encode(['error' => 'Person not found']);
    exit;
}

/* -----------------
   Generate token
   ----------------- */
try {
    $token = bin2hex(random_bytes(32)); // 64 hex chars
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Token generation failed']);
    exit;
}

$expires_in_minutes = 5;
$expires_at = date('Y-m-d H:i:s', time() + ($expires_in_minutes * 60));

// created_by = session person id if present, otherwise NULL
$created_by = (!empty($_SESSION['person_id'])) ? (int)$_SESSION['person_id'] : null;

/* -----------------
   Insert token row
   ----------------- */
// Ensure table has columns: id, person_id, token, created_by (nullable), expires_at, used (default 0), created_at
$insert = $mysqli->prepare("INSERT INTO autologin_tokens (person_id, token, created_by, expires_at, created_at) VALUES (?, ?, ?, ?, NOW())");
if (!$insert) {
    http_response_code(500);
    echo json_encode(['error' => 'DB prepare error: ' . $mysqli->error]);
    exit;
}
if ($created_by === null) {
    // bind as NULL (safest: supply null as integer with 'i' still, but we need to pass null properly)
    // We'll bind as string for simplicity: use bind_param('isss', ...) but ensure created_by is null string
    $created_by_for_bind = null;
    $insert->bind_param('isss', $person_id, $token, $created_by_for_bind, $expires_at);
} else {
    $insert->bind_param('isis', $person_id, $token, $created_by, $expires_at);
}

if (!$insert->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'DB execute error: ' . $insert->error]);
    $insert->close();
    exit;
}
$insert->close();

/* -----------------
   Build URL
   ----------------- */
// adjust path if your autologin consumer is elsewhere. Example uses /agent/autologin.php
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$currentDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$autologin_path = $currentDir.'/../public/autologin.php';
$autologin_url = $scheme . '://' . $host . $autologin_path . '?token=' . urlencode($token);

/* -----------------
   Return JSON
   ----------------- */
http_response_code(200);
echo json_encode([
    'url' => $autologin_url,
    'expires_in' => $expires_in_minutes,
    'created_by' => $created_by // may be null
]);
exit;
