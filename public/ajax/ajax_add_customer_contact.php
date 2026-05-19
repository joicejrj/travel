<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../../config/db.php';

// read JSON
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { echo json_encode(['success'=>false,'error'=>'invalid_json']); exit; }

$customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;
$name = isset($data['name']) ? trim($mysqli->real_escape_string($data['name'])) : '';
$email = isset($data['email']) ? trim($mysqli->real_escape_string($data['email'])) : '';
$phone = isset($data['phone']) ? trim($mysqli->real_escape_string($data['phone'])) : '';
$designation = isset($data['designation']) ? trim($mysqli->real_escape_string($data['designation'])) : '';

if (!$customer_id || $name === '') {
    echo json_encode(['success'=>false,'error'=>'missing_params']);
    exit;
}

$sql = "INSERT INTO customers_contacts (customer_id, name, email, phone, designation, created_at)
        VALUES ({$customer_id}, '{$name}', '{$email}', '{$phone}', '{$designation}', NOW())";

if ($mysqli->query($sql)) {
    $id = $mysqli->insert_id;
    echo json_encode(['success'=>true,'id'=>$id]);
} else {
    echo json_encode(['success'=>false,'error'=>'db_error','sql_error'=>$mysqli->error]);
}
