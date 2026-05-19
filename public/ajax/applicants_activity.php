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
  SELECT activity_type, title, details, created_by, created_at
  FROM applicant_activity_logs
  WHERE applicant_id = ?
  ORDER BY created_at ASC
");
$stmt->bind_param("i", $applicantId);
$stmt->execute();

$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$icons = [
  'CREATED' => ['🟢','#16a34a'],
  'DOC_UPLOADED' => ['📄','#2563eb'],
  'STATUS_CHANGED' => ['🔄','#0d6efd'],
  'NOTE' => ['📝','#6b7280'],
  'WHATSAPP_SENT' => ['💬','#22c55e'],
  'EMAIL_SENT' => ['✉️','#6366f1'],
  'CALL_LOG' => ['📞','#f59e0b'],
  'INTERVIEW_SCHEDULED' => ['📅','#9333ea'],
];

$data = [];

foreach ($rows as $r) {
  [$icon,$color] = $icons[$r['activity_type']] ?? ['•','#6b7280'];

  $data[] = [
    'icon' => $icon,
    'color' => $color,
    'title' => $r['title'],
    'details' => $r['details'],
    'created_by' => $r['created_by'],
    'created_at' => date('d M Y, H:i', strtotime($r['created_at']))
  ];
}

echo json_encode(['status'=>true,'data'=>$data]);
?>