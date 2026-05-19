<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
// require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$rtn = ['success' => 0, 'msg' => ''];

// Get POST data
$reminder_id  = isset($_POST['reminder_id']) ? (int)$_POST['reminder_id'] : 0;
$contact_id   = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;
$type         = trim($_POST['type'] ?? '');
$date         = trim($_POST['reminder_date'] ?? '');
$time         = trim($_POST['reminder_time'] ?? '');
$note         = trim($_POST['notes'] ?? '');
$datetime     = date('Y-m-d H:i:s');

// Validate
if ($reminder_id <= 0) {
    $rtn['msg'] = "Invalid reminder ID";
    echo json_encode($rtn);
    exit;
}

if ($contact_id <= 0) {
    $rtn['msg'] = "Invalid contact ID";
    echo json_encode($rtn);
    exit;
}

if ($date === '' || $time === '') {
    $rtn['msg'] = "Date and Time are required";
    echo json_encode($rtn);
    exit;
}

// Combine date and time
$reminder_at = $date . ' ' . $time;

// Update reminder
$data = [
    'type'        => $type,
    'note'        => $note,
    'contact_id'  => $contact_id,
    'reminder_at' => $reminder_at,
    'updated_at'  => $datetime
];

$updated = $db->update('contacts_reminders', ['id' => $reminder_id, 'contact_id' => $contact_id], $data);

if ($updated) {
    $getcontact = $db->get('contacts',array('id'=>$contact_id),'name');
    $site->agent_log("Reminder updated to ".date("d M Y h:i A",strtotime($reminder_at)),$contact_id,'contact');
    $rtn['success'] = 1;
    $rtn['msg'] = "Reminder updated successfully";
    $rtn['data'] = $data;
} else {
    $rtn['msg'] = "Failed to update reminder";
}

echo json_encode($rtn);
?>
