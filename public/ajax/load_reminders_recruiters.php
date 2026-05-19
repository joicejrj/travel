<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
// require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$status = $_POST['status'] ?? '';
$offset = (int)($_POST['offset'] ?? 0);
$limit  = 10;

$use_table = 'recruiters_reminders';
$rem_col = 'recruiter_id';
$uid = $_SESSION['user_id']; // adjust if different

$now = time();
$range_start = date('Y-m-d 00:00:00', $now);
$range_end   = date('Y-m-d 23:59:59', strtotime('+7 days', $now));

$sql = "
  SELECT r.id, r.{$rem_col} AS contact_ref, r.reminder_at, r.type, r.note, r.completed,
         c.id AS recruiter_id, c.name AS contact_name, c.email AS contact_email, c.phone AS contact_phone, 
         c.agent_id AS contact_agent, c.type AS contact_status, r.contact_id
  FROM {$use_table} r
  LEFT JOIN recruiters c ON r.{$rem_col} = c.id
  WHERE r.reminder_at BETWEEN ? AND ? 
    AND r.completed = 0
    AND c.type = ?
  ORDER BY r.reminder_at ASC
  LIMIT ?, ?
";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
  echo json_encode(['success' => 0, 'msg' => $mysqli->error]);
  exit;
}

$stmt->bind_param('sssii', $range_start, $range_end, $status, $offset, $limit);
$stmt->execute();
$res = $stmt->get_result();

$reminders = [];
while ($row = $res->fetch_assoc()) {
  $reminders[] = [
    'id' => $row['id'],
    'contact_name' => $row['contact_name'],
    'contact_email' => $row['contact_email'],
    'contact_phone' => $row['contact_phone'],
    'note' => $site->more($row['note'],40),
    'reminder_at' => date('d M, h:i A', strtotime($row['reminder_at']))
  ];
}

$stmt->close();

echo json_encode([
  'success' => 1,
  'data' => $reminders,
  'next_offset' => $offset + count($reminders),
  'has_more' => count($reminders) === $limit
]);
?>