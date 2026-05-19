<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();
header('Content-Type: application/json');

// read JSON body
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!is_array($input)) {
    echo json_encode(['ok' => false, 'error' => 'Invalid input']);
    exit;
}

$id = isset($input['id']) ? (int)$input['id'] : 0;
$action = isset($input['action']) ? trim($input['action']) : '';

if ($id <= 0 || $action === '') {
    echo json_encode(['ok' => false, 'error' => 'Missing parameters']);
    exit;
}

/* ---------- Verify ownership: reminder -> contact -> customers.agent_id = current user ---------- */
$stmt = $mysqli->prepare("
  SELECT r.id
  FROM customers_reminders r
  JOIN customers c ON r.customer_id = c.id
  WHERE r.id = ?
  LIMIT 1
");
if (!$stmt) {
    echo json_encode(['ok' => false, 'error' => 'DB prepare error (ownership check)']);
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    echo json_encode(['ok' => false, 'error' => 'Not allowed']);
    exit;
}

/* ---------- Helper: require non-empty note ---------- */
function require_note_or_exit($input) {
    $note = isset($input['note']) ? trim($input['note']) : '';
    if ($note === '') {
        echo json_encode(['ok' => false, 'error' => 'Note is required']);
        exit;
    }
    return $note;
}

/* ---------- Actions ---------- */

if ($action === 'update_note') {
    // Save/replace note
    $note = require_note_or_exit($input);

    $u = $mysqli->prepare("UPDATE customers_reminders SET note = ?, updated_at = NOW() WHERE id = ?");
    if (!$u) { echo json_encode(['ok' => false, 'error' => 'DB prepare error (update_note)']); exit; }
    $u->bind_param('si', $note, $id);
    $ok = $u->execute();
    $u->close();

    if ($ok) {
        $site->agent_log("Reminder [".$id."] Note is updated (".$note.")");
        echo json_encode(['ok' => true, 'msg' => 'Note updated', 'note' => $note]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Failed to update note']);
    }
    exit;
}

if ($action === 'snooze') {
    // require minutes and note
    $minutes = isset($input['minutes']) ? (int)$input['minutes'] : 60;
    if ($minutes <= 0) $minutes = 60;

    $note = require_note_or_exit($input);

    // Shift reminder_at forward by minutes and save note + updated_at.
    // We'll update reminder_at = DATE_ADD(reminder_at, INTERVAL ? MINUTE)
    $u = $mysqli->prepare("UPDATE customers_reminders SET reminder_at = DATE_ADD(reminder_at, INTERVAL ? MINUTE), note = ?, updated_at = NOW() WHERE id = ?");
    if (!$u) { echo json_encode(['ok' => false, 'error' => 'DB prepare error (snooze)']); exit; }
    $u->bind_param('isi', $minutes, $note, $id);
    $ok = $u->execute();
    $u->close();

    if (!$ok) {
        echo json_encode(['ok' => false, 'error' => 'Failed to snooze reminder']);
        exit;
    }

    // Return the new reminder_at value
    $q = $mysqli->prepare("SELECT reminder_at FROM customers_reminders WHERE id = ? LIMIT 1");
    if (!$q) { echo json_encode(['ok' => true, 'msg' => 'Snoozed (no follow-up)']); exit; }
    $q->bind_param('i', $id);
    $q->execute();
    $res = $q->get_result()->fetch_assoc();
    $q->close();

    $new_when = $res['reminder_at'] ?? null;
    $site->agent_log("Reminder [".$id."] is snoozed to ".date("d M Y",strtotime($new_when))."");
    echo json_encode(['ok' => true, 'msg' => 'Reminder snoozed', 'minutes' => $minutes, 'reminder_at' => $new_when]);
    exit;
}

if ($action === 'complete') {
    // require note
    $note = require_note_or_exit($input);

    // Mark completed and save note
    $u = $mysqli->prepare("UPDATE customers_reminders SET completed = 1, note = ?, updated_at = NOW() WHERE id = ?");
    if (!$u) { echo json_encode(['ok' => false, 'error' => 'DB prepare error (complete)']); exit; }
    $u->bind_param('si', $note, $id);
    $ok = $u->execute();
    $u->close();

    if ($ok) {
        $site->agent_log("Reminder [".$id."] is marked as completed");
        echo json_encode(['ok' => true, 'msg' => 'Reminder completed']);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Failed to mark complete']);
    }
    exit;
}

/* unknown action */
echo json_encode(['ok' => false, 'error' => 'Unknown action']);
exit;
?>