<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$rtn = ['success' => 0, 'msg' => ''];

// contact ID must exist
$customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
if ($customer_id <= 0) {
    $rtn['msg'] = "Invalid Customer ID";
    echo json_encode($rtn);
    exit;
}

// Collect form values
$type          = trim($_POST['type'] ?? '');
$reminder_date = trim($_POST['reminder_date'] ?? '');
$reminder_time = trim($_POST['reminder_time'] ?? '');
$note          = trim($_POST['notes'] ?? '');
$contact_id          = trim($_POST['contact_id'] ?? NULL);
$contact_id = $contact_id==''?NULL:$contact_id;

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
$stmt = $mysqli->prepare("INSERT INTO customers_reminders (customer_id, reminder_at, type, contact_id, note, created_at) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    $rtn['msg'] = "Prepare failed: " . $mysqli->error;
    echo json_encode($rtn);
    exit;
}
$stmt->bind_param("isssss", $customer_id, $reminder_at, $type, $contact_id, $note, $created_at);
$success = $stmt->execute();
$insert_id = $stmt->insert_id;
$stmt->close();

if (!$success) {
    $rtn['msg'] = "Failed to add reminder";
    echo json_encode($rtn);
    exit;
}
else {
    $getcontact = $db->get('customers',array('id'=>$customer_id),'name');
    $site->agent_log("New Reminder added for ".date("d M Y h:i A",strtotime($reminder_at)),$customer_id);
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

// get contact type
$status = '';
$s = $mysqli->prepare("SELECT `type` FROM customers WHERE id = ? LIMIT 1");
if ($s) {
    $s->bind_param("i", $customer_id);
    $s->execute();
    $s->bind_result($status_res);
    if ($s->fetch()) {
        $status = (string)$status_res;
    }
    $s->close();
}

// fetch existing daily_followup row
$q = $mysqli->prepare("SELECT id, count_of, reminder_done, note_done FROM daily_followup WHERE agent_id = ? AND contact_id = ? AND date_followup = ? LIMIT 1");
if ($q) {
    $q->bind_param("iis", $agent_id, $customer_id, $today);
    $q->execute();
    $q->bind_result($df_id, $df_count, $df_rem_done, $df_note_done);
    if ($q->fetch()) {
        $q->close();
        // Row exists
        if ($atype === 'reminder') {
            if ((int)$df_rem_done === 1) {
                // already counted a reminder today for this agent/contact -> do nothing
            } else {
                // mark reminder_done and increment count_of by 1
                $newcount = (int)$df_count + 1;
                $u = $mysqli->prepare("UPDATE daily_followup SET reminder_done = 1, count_of = ? WHERE id = ?");
                if ($u) {
                    $u->bind_param("ii", $newcount, $df_id);
                    $u->execute();
                    $u->close();
                }
            }
        } else { // 'note' shouldn't happen here for reminder add, but keep logic
            if ((int)$df_note_done === 1) {
                // already counted
            } else {
                $newcount = (int)$df_count + 1;
                $u = $mysqli->prepare("UPDATE daily_followup SET note_done = 1, count_of = ? WHERE id = ?");
                if ($u) {
                    $u->bind_param("ii", $newcount, $df_id);
                    $u->execute();
                    $u->close();
                }
            }
        }
    } else {
        // no row -> insert with appropriate flag
        $q->close();
        $rem_done = ($atype === 'reminder') ? 1 : 0;
        $note_done = ($atype === 'note') ? 1 : 0;
        $startcount = 1;
        $ins = $mysqli->prepare("INSERT INTO daily_followup (agent_id, contact_id, date_followup, status_followup, count_of, reminder_done, note_done) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($ins) {
            $ins->bind_param("iissiii", $agent_id, $customer_id, $today, $status, $startcount, $rem_done, $note_done);
            $ins->execute();
            $ins->close();
        }
    }
}

$rtn['success'] = 1;
$rtn['msg'] = "Reminder added successfully";
$rtn['data'] = [
    'id' => $insert_id,
    'customer_id' => $customer_id,
    'reminder_at' => $reminder_at,
    'type' => $type,
    'note' => $note,
];

echo json_encode($rtn);
