<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'list') {

  $aid = (int)$_POST['applicant_id'];

  $stmt = $mysqli->prepare("
    SELECT id, reminder_at, type, note, completed
    FROM applicants_reminders
    WHERE applicant_id=?
    ORDER BY reminder_at ASC
  ");
  $stmt->bind_param("i", $aid);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  echo json_encode(['status'=>true,'data'=>$res]);
  exit;
}

/* ---------------- ADD ---------------- */
if ($action === 'add') {

  $aid  = (int)$_POST['applicant_id'];
  $at   = $_POST['reminder_at'];
  $type = $_POST['type'] ?? 'General';
  $note = trim($_POST['note'] ?? '');

  if (!$aid || !$at || $note === '') {
    echo json_encode(['status'=>false,'msg'=>'Invalid input']);
    exit;
  }

  $stmt = $mysqli->prepare("
    INSERT INTO applicants_reminders
    (applicant_id, reminder_at, type, note)
    VALUES (?,?,?,?)
  ");
  $stmt->bind_param("isss", $aid, $at, $type, $note);
  $stmt->execute();
  $stmt->close();

  // Timeline log
  $stmt = $mysqli->prepare("
    INSERT INTO applicant_activity_logs
    (applicant_id, activity_type, title, details)
    VALUES (?,?,?,?)
  ");
  $title = 'Reminder added';
  $details = "$type – $note ($at)";
  $typeLog = 'NOTE';
  $stmt->bind_param("isss", $aid, $typeLog, $title, $details);
  $stmt->execute();
  $stmt->close();

  echo json_encode(['status'=>true]);
  exit;
}

/* ---------------- COMPLETE ---------------- */
if ($action === 'complete') {

  $id = (int)$_POST['id'];

  $stmt = $mysqli->prepare("
    UPDATE applicants_reminders
    SET completed=1
    WHERE id=?
  ");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();

  echo json_encode(['status'=>true]);
  exit;
}

echo json_encode(['status'=>false,'msg'=>'Invalid action']);
?>