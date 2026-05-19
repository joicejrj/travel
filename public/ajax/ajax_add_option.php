<?php
// ajax_add_option.php
require_once __DIR__ . '/../../config/db.php'; // must provide $mysqli
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { echo json_encode(['success'=>false,'error'=>'invalid_json']); exit; }

$type = $data['type'] ?? '';
$name = trim($data['name'] ?? '');
$inter_type = isset($data['inter_type']) ? trim($data['inter_type']) : '';

if (!$type || $name === '') { echo json_encode(['success'=>false,'error'=>'missing_params']); exit; }

$map = [
  'channel' => 'channels',
  'contact_type' => 'contact_types',
  'scenario' => 'scenarios'
];

if (!isset($map[$type])) { echo json_encode(['success'=>false,'error'=>'invalid_type']); exit; }
$table = $map[$type];

// create a URL-friendly slug base
$slugBase = preg_replace('/[^\w]+/u', '-', mb_strtolower($name));
$slugBase = trim($slugBase, '-');
if ($slugBase === '') $slugBase = 'opt';
$slug = $slugBase;
$i = 1;

// ensure slug is unique in the target table
while (true) {
    $q = $mysqli->prepare("SELECT id FROM `{$table}` WHERE slug = ? LIMIT 1");
    if (!$q) { echo json_encode(['success'=>false,'error'=>'db_prepare_failed','sql_error'=>$mysqli->error]); exit; }
    $q->bind_param('s', $slug);
    $q->execute();
    $q->store_result();
    if ($q->num_rows === 0) { $q->close(); break; }
    $q->close();
    $slug = $slugBase . '-' . $i; $i++;
}

// Insert according to table/columns. scenarios needs inter_type; others keep (name,slug)
if ($type === 'scenario') {
    // ensure inter_type is set (optional). If not provided, store empty string.
    $stmt = $mysqli->prepare("INSERT INTO `{$table}` (name, slug, inter_type, created_at) VALUES (?, ?, ?, NOW())");
    if (!$stmt) { echo json_encode(['success'=>false,'error'=>'db_prepare_failed','sql_error'=>$mysqli->error]); exit; }
    $stmt->bind_param('sss', $name, $slug, $inter_type);
} else {
    $stmt = $mysqli->prepare("INSERT INTO `{$table}` (name, slug) VALUES (?, ?)");
    if (!$stmt) { echo json_encode(['success'=>false,'error'=>'db_prepare_failed','sql_error'=>$mysqli->error]); exit; }
    $stmt->bind_param('ss', $name, $slug);
}

$ok = $stmt->execute();
$id = $mysqli->insert_id;
$err = $stmt->error;
$stmt->close();

if ($ok) {
    $resp = ['success' => true, 'id' => $id, 'value' => $slug, 'name' => $name];
    if ($type === 'scenario') $resp['inter_type'] = $inter_type;
    echo json_encode($resp);
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'db_insert_failed', 'sql_error' => $err]);
    exit;
}
