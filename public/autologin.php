<?php
// agent/autologin.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../config/db.php'; // adjust path

$token = $_GET['token'] ?? '';
if (!$token || !preg_match('/^[0-9a-f]{64}$/', $token)) {
    http_response_code(400);
    echo "Invalid token.";
    exit;
}

// find token
$stmt = $mysqli->prepare("SELECT id, person_id, expires_at, used FROM autologin_tokens WHERE token = ? LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res) {
    http_response_code(404);
    echo "Token not found or expired.";
    exit;
}

// check used / expiry
// if ((int)$res['used'] === 1) {
//     http_response_code(403);
//     echo "Token already used.";
//     exit;
// }
// if (strtotime($res['expires_at']) < time()) {
//     http_response_code(403);
//     echo "Token expired.";
//     exit;
// }

$person_id = (int)$res['person_id'];

// load person record and start session (similar to login.php)
$stmt = $mysqli->prepare('SELECT id, name, role FROM people WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $person_id);
$stmt->execute();
$person = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$person) {
    http_response_code(404);
    echo "Person not found.";
    exit;
}

// mark token used (single-use)
$stmt = $mysqli->prepare("UPDATE autologin_tokens SET used = 1, used_at = NOW() WHERE id = ?");
$stmt->bind_param('i', $res['id']);
$stmt->execute();
$stmt->close();

// set session as if logged in
session_regenerate_id(true);
$_SESSION['person_id'] = (int)$person['id'];
$_SESSION['person_name'] = $person['name'];
$_SESSION['person_role'] = $person['role'];
isset($_SESSION['user'])?$_SESSION['person_name_admin']=$_SESSION['user']:'';

// optionally fetch mailbox id (like your login.php)
$stmt1 = $mysqli->prepare("SELECT id FROM mailboxes WHERE person_id = ? LIMIT 1");
$stmt1->bind_param('i', $person['id']);
$stmt1->execute();
$stmt1->bind_result($mailbox_id);
$stmt1->fetch();
$stmt1->close();

$_SESSION['person_mailbox_id'] = $mailbox_id ?? null;

// redirect to dashboard
header('Location: ../index.php?page=bookings');
exit;
