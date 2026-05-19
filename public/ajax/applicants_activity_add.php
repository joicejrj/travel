<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';
session_start();

header('Content-Type: application/json');

$applicantId = (int)($_POST['applicant_id'] ?? 0);
$note = trim($_POST['note'] ?? '');
$createdBy = $_SESSION['person_name'] ?? 'Admin';

if ($applicantId <= 0 || $note === '') {
  echo json_encode(['status'=>false,'msg'=>'Invalid input']);
  exit;
}

$stmt = $mysqli->prepare("
  INSERT INTO applicant_activity_logs
  (applicant_id, activity_type, title, details, created_by)
  VALUES (?,?,?,?,?)
");

$type = 'NOTE';
$title = 'Internal Note';

$stmt->bind_param(
  "issss",
  $applicantId,
  $type,
  $title,
  $note,
  $createdBy
);

$stmt->execute();
$stmt->close();

echo json_encode(['status'=>true]);
?>