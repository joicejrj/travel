<?php
// ajax/check_session.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/db.php'; // must provide $mysqli

$contact_id = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : 0;
if ($contact_id <= 0) {
    echo json_encode(['allow_normal' => false, 'reason' => 'missing_contact_id']);
    exit;
}


if(isset($_GET['contact_type'])&&$_GET['contact_type']!='')
{
    $contact_type = $_GET['contact_type'];
}
else
{
    $contact_type='Contact';
}


$cid = (int)$contact_id;
$sql = "SELECT session_started FROM whatsapp_customer_session WHERE contact_id = $cid and contact_type='".$contact_type."' LIMIT 1";
$res = $mysqli->query($sql);
if (!$res) {
    http_response_code(500);
    echo json_encode(['allow_normal' => false, 'reason' => 'db_error', 'db_error' => $mysqli->error]);
    exit;
}

if ($res->num_rows === 0) {
    // no session — block normal messages
    echo json_encode(['allow_normal' => false, 'reason' => 'no_session']);
    exit;
}

$row = $res->fetch_assoc();
$ts = strtotime($row['session_started']);
if ($ts === false) {
    // invalid timestamp -> treat as expired
    echo json_encode(['allow_normal' => false, 'reason' => 'invalid_session']);
    exit;
}

$now = time();
if (($now - $ts) > 24*3600) {
    echo json_encode(['allow_normal' => false, 'reason' => 'expired']);
} else {
    echo json_encode(['allow_normal' => true]);
}
