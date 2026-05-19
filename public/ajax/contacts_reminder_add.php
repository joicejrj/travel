<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$rtn = ['success' => 0, 'msg' => ''];

// contact ID must exist
$contact_id = isset($_POST['contact_id']) ? (int)$_POST['contact_id'] : 0;
if ($contact_id <= 0) {
    $rtn['msg'] = "Invalid Customer ID";
    echo json_encode($rtn);
    exit;
}

// Collect form values
$type          = trim($_POST['type'] ?? '');
$reminder_date = trim($_POST['reminder_date'] ?? '');
$reminder_time = trim($_POST['reminder_time'] ?? '');
$note          = trim($_POST['notes'] ?? '');

// Validate required fields
if (empty($type) || empty($reminder_date) || empty($reminder_time)) {
    $rtn['msg'] = "Type, date, and time are required";
    echo json_encode($rtn);
    exit;
}

// Normalize action type
$atype = strtolower(trim($type));
if ($atype === 'notes') $atype = 'note';
if ($atype !== 'note' && $atype !== 'reminder') {
    // allow other values but treat unknown as 'reminder' default
    $atype = 'reminder';
}

// Combine date + time
$reminder_at = date('Y-m-d H:i:s', strtotime("$reminder_date $reminder_time"));

// Prevent past reminders
if (strtotime($reminder_at) < time()) {
    $rtn['msg'] = "Cannot set reminder for past date/time";
    echo json_encode($rtn);
    exit;
}

// Insert into customers_reminders
$created_at = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare("INSERT INTO contacts_reminders (contact_id, reminder_at, type, note, created_at) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    $rtn['msg'] = "Prepare failed: " . $mysqli->error;
    echo json_encode($rtn);
    exit;
}
$stmt->bind_param("issss", $contact_id, $reminder_at, $type, $note, $created_at);
$success = $stmt->execute();
$insert_id = $stmt->insert_id;
$stmt->close();

if (!$success) {
    $rtn['msg'] = "Failed to add reminder";
    echo json_encode($rtn);
    exit;
}
else {
    $getcontact = $db->get('contacts',array('id'=>$contact_id),'name');
    $site->agent_log("New Reminder added for ".date("d M Y h:i A",strtotime($reminder_at)),$contact_id,'contact');
}

// Now maintain daily_followup using the stricter rules:
// Determine agent id from auth.
$agent_id = 0;
if (isset($CURRENT_USER_ID) && $CURRENT_USER_ID) {
    $agent_id = (int)$CURRENT_USER_ID;
} elseif (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
    $agent_id = (int)$_SESSION['user_id'];
}

$today = date('Y-m-d');


$rtn['success'] = 1;
$rtn['msg'] = "Reminder added successfully";
$rtn['data'] = [
    'id' => $insert_id,
    'contact_id' => $contact_id,
    'reminder_at' => $reminder_at,
    'type' => $type,
    'note' => $note,
];

echo json_encode($rtn);
