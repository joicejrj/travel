<?php
// ini_set('display_startup_errors', 1);
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$token = $_GET['token'] ?? '';
if (empty($token)) {
    die('Invalid or missing token.');
}

// --- Validate agent ---
$agent = $db->get('people', ['login_token' => $token]);
if (!$agent) {
    die('Invalid or expired token.');
}
$agent_id = $agent->id;

// regenerate and set session
session_regenerate_id(true);
$_SESSION['person_id'] = (int)$agent->id;
$_SESSION['person_name'] = $agent->name;

$getmail = $db->get('mailboxes',array('person_id'=>$agent->id),'id');
$_SESSION['person_mailbox_id'] = $getmail?$getmail->id:'0';
header('Location: index.php?page=dashboard');
exit;

?>