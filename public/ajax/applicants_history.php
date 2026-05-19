<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$applicantId = (int)($_POST['applicant_id'] ?? 0);
if ($applicantId <= 0) {
  echo json_encode(['status'=>false,'data'=>[]]);
  exit;
}

$stmt = $mysqli->prepare("
  SELECT old_status, new_status, note, changed_by, created_at
  FROM applicant_status_history
  WHERE applicant_id = ?
  ORDER BY created_at DESC
");
$stmt->bind_param("i", $applicantId);
$stmt->execute();

$res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$data = [];
foreach ($res as $r) {
  $data[] = [
    'old_status' => $r['old_status'],
    'new_status' => $r['new_status'],
    'note' => $r['note'],
    'changed_by' => $r['changed_by'],
    'created_at' => date('d M Y, H:i', strtotime($r['created_at']))
  ];
}

echo json_encode(['status'=>true,'data'=>$data]);
?>