<?php
// public/ajax/ajax_add_recruiter_contact.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { echo json_encode(['success'=>false,'error'=>'invalid_json']); exit; }

$recruiter_id = isset($data['recruiter_id']) ? (int)$data['recruiter_id'] : 0;
$name = isset($data['name']) ? trim($data['name']) : '';
$designation = isset($data['designation']) ? trim($data['designation']) : '';
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';

if (!$recruiter_id) { echo json_encode(['success'=>false,'error'=>'missing_recruiter_id']); exit; }
if ($name === '') { echo json_encode(['success'=>false,'error'=>'missing_name']); exit; }

$sql = "INSERT INTO `recruiters_contacts` (recruiter_id, name, designation, phone, email, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())";
$stmt = $mysqli->prepare($sql);
if (!$stmt) { echo json_encode(['success'=>false,'error'=>'db_prepare_failed','sql_error'=>$mysqli->error]); exit; }
$stmt->bind_param('issss', $recruiter_id, $name, $designation, $phone, $email);
if (!$stmt->execute()) {
    echo json_encode(['success'=>false,'error'=>'db_exec_failed','sql_error'=>$stmt->error]);
    $stmt->close();
    exit;
}
$newId = $stmt->insert_id;
$stmt->close();

$sql2 = "SELECT id, COALESCE(name,'') AS name, COALESCE(email,'') AS email, COALESCE(phone,'') AS phone, COALESCE(designation,'') AS designation FROM `recruiters_contacts` WHERE id = ? LIMIT 1";
$stmt2 = $mysqli->prepare($sql2);
$item = null;
if ($stmt2) {
    $stmt2->bind_param('i', $newId);
    $stmt2->execute();
    $res = $stmt2->get_result();
    $item = $res->fetch_assoc() ?: null;
    $stmt2->close();
}

echo json_encode(['success'=>true, 'id'=>$newId, 'item'=>$item]);
exit;
