<?php
// agent/reminder_action.php
header('Content-Type: application/json; charset=utf-8');

// load auth + DB
require_once __DIR__ . '/_auth.php';          // sets $CURRENT_USER_ID, etc.
require_once __DIR__ . '/../config/db.php';   // adjust path if your config is elsewhere

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

/* ---------- Verify ownership: reminder -> carrier -> carriers.agent_id = current user ---------- */
$stmt = $mysqli->prepare("
  SELECT r.id
  FROM contacts_reminders r
  JOIN contacts c ON r.contact_id = c.id
  WHERE r.id = ? AND c.agent_id = ?
  LIMIT 1
");
if (!$stmt) {
    echo json_encode(['ok' => false, 'error' => 'DB prepare error']);
    exit;
}
$stmt->bind_param('ii', $id, $CURRENT_USER_ID);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    echo json_encode(['ok' => false, 'error' => 'Not allowed']);
    exit;
}

/* ---------- Actions ---------- */
if ($action === 'complete') {
    $u = $mysqli->prepare("UPDATE contacts_reminders SET completed = 1, updated_at = NOW() WHERE id = ?");
    if (!$u) { echo json_encode(['ok' => false, 'error' => 'DB prepare error']); exit; }
    $u->bind_param('i', $id);
    $ok = $u->execute();
    $u->close();
    echo json_encode(['ok' => (bool)$ok]);
    exit;
}

// if ($action === 'snooze') {
//     $minutes = isset($input['minutes']) ? (int)$input['minutes'] : 60;
//     if ($minutes <= 0) $minutes = 60;
//     $u = $mysqli->prepare("UPDATE contacts_reminders SET reminder_at = DATE_ADD(reminder_at, INTERVAL ? MINUTE), updated_at = NOW() WHERE id = ?");
//     if (!$u) { echo json_encode(['ok' => false, 'error' => 'DB prepare error']); exit; }
//     $u->bind_param('ii', $minutes, $id);
//     $ok = $u->execute();
//     $u->close();
//     echo json_encode(['ok' => (bool)$ok]);
//     exit;
// }

// if ($action === 'update_note') {
//     $note = isset($input['note']) ? trim($input['note']) : '';
//     $u = $mysqli->prepare("UPDATE contacts_reminders SET note = ?, updated_at = NOW() WHERE id = ?");
//     if (!$u) { echo json_encode(['ok' => false, 'error' => 'DB prepare error']); exit; }
//     $u->bind_param('si', $note, $id);
//     $ok = $u->execute();
//     $u->close();
//     echo json_encode(['ok' => (bool)$ok, 'note' => $note]);
//     exit;
// }

/* unknown action */
echo json_encode(['ok' => false, 'error' => 'Unknown action']);
exit;
