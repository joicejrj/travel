<?php
// public/ajax/applicants_get.php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';

header('Content-Type: application/json');

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  echo json_encode(['status'=>false,'msg'=>'Invalid ID']);
  exit;
}

// Applicant
$stmt = $mysqli->prepare("
  SELECT *
  FROM applicants
  WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$app = $res->fetch_assoc();
$stmt->close();

if (!$app) {
  echo json_encode(['status'=>false,'msg'=>'Not found']);
  exit;
}

// Position label
$position = $app['position_category'] === 'Other'
  ? ($app['other_position'] ?: 'Other')
  : $app['position_category'];

// Documents
$stmt = $mysqli->prepare("
  SELECT doc_type, original_filename, file_path, size_bytes
  FROM applicant_documents
  WHERE applicant_id = ?
  ORDER BY uploaded_at
");
$stmt->bind_param("i", $id);
$stmt->execute();
$docs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$documents = [];
foreach ($docs as $d) {
  $documents[] = [
    'doc_type' => $d['doc_type'],
    'original_filename' => $d['original_filename'],
    'file_path' => $d['file_path'],
    'size_kb' => round($d['size_bytes'] / 1024)
  ];
}

echo json_encode([
  'status' => true,
  'data' => [
    'id' => $app['id'],
    'ref_no' => $app['ref_no'],
    'full_name' => $app['full_name'],
    'mobile' => $app['mobile'],
    'email' => $app['email'],
    'nationality' => $app['nationality'],
    'position' => $position,
    'years_experience' => $app['years_experience'],
    'expected_salary_aed' => $app['expected_salary_aed'],
    'status' => $app['status'],
    'current_location' => $app['current_location'],
    'city' => $app['city'],
    'visa_status' => $app['visa_status'],
    'availability' => $app['availability'],
    'lead_source' => $app['lead_source'],
    'created_at' => date('d M Y, H:i', strtotime($app['created_at'])),
    'documents' => $documents
  ]
]);
?>