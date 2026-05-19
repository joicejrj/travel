<?php
// public/ajax/ajax_list_suppliers.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000;
$limit = ($limit > 0 && $limit <= 5000) ? $limit : 1000;

$sql = "SELECT id, COALESCE(name,'') AS name,
               COALESCE(email,'') AS email, COALESCE(phone,'') AS phone,
               COALESCE(company,'') AS company
        FROM `suppliers`
        ORDER BY name
        LIMIT ?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) { echo json_encode(['success'=>false,'error'=>'db_prepare_failed','sql_error'=>$mysqli->error]); exit; }
$stmt->bind_param('i', $limit);
$stmt->execute();
$res = $stmt->get_result();
$items = [];
while ($row = $res->fetch_assoc()) $items[] = $row;
$stmt->close();

echo json_encode(['success'=>true,'items'=>$items]);
exit;
