<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';
session_start();

header('Content-Type: application/json');

$applicantId = (int)($_POST['applicant_id'] ?? 0);
$newStatus   = trim($_POST['new_status'] ?? '');
$note        = trim($_POST['note'] ?? '');
$changedBy  = $_SESSION['person_name'] ?? 'Admin';

if ($applicantId <= 0 || $newStatus === '') {
  echo json_encode(['status'=>false,'msg'=>'Invalid input']);
  exit;
}

$mysqli->begin_transaction();

try {

  // current status
  $stmt = $mysqli->prepare("SELECT status FROM applicants WHERE id=?");
  $stmt->bind_param("i", $applicantId);
  $stmt->execute();
  $oldStatus = $stmt->get_result()->fetch_column();
  $stmt->close();

  if (!$oldStatus) throw new Exception('Applicant not found');

  // update applicant
  $stmt = $mysqli->prepare("UPDATE applicants SET status=? WHERE id=?");
  $stmt->bind_param("si", $newStatus, $applicantId);
  $stmt->execute();
  $stmt->close();

  // status history
  $stmt = $mysqli->prepare("
    INSERT INTO applicant_status_history
    (applicant_id, old_status, new_status, note, changed_by)
    VALUES (?,?,?,?,?)
  ");
  $stmt->bind_param(
    "issss",
    $applicantId,
    $oldStatus,
    $newStatus,
    $note,
    $changedBy
  );
  $stmt->execute();
  $stmt->close();

  // activity log
  $stmt = $mysqli->prepare("
    INSERT INTO applicant_activity_logs
    (applicant_id, activity_type, title, details, created_by)
    VALUES (?,?,?,?,?)
  ");
  $title = "Status changed to {$newStatus}";
  $details = $note ?: null;
  $type = 'STATUS_CHANGED';

  $stmt->bind_param(
    "issss",
    $applicantId,
    $type,
    $title,
    $details,
    $changedBy
  );
  $stmt->execute();
  $stmt->close();

  $mysqli->commit();
  echo json_encode(['status'=>true]);

} catch (Throwable $e) {
  $mysqli->rollback();
  echo json_encode(['status'=>false,'msg'=>$e->getMessage()]);
}
?>