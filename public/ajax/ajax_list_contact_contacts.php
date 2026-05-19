<?php
// public/ajax/ajax_list_contact_contacts.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';

$contact_id = isset($_GET['contact_id']) ? (int) $_GET['contact_id'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000;
$limit = ($limit > 0 && $limit <= 5000) ? $limit : 1000;

if (!$contact_id) {
    echo json_encode(['success' => false, 'error' => 'missing_contact_id']);
    exit;
}

$sql = "SELECT id,
               COALESCE(name,'') AS name,
               COALESCE(email,'') AS email,
               COALESCE(phone,'') AS phone,
               COALESCE(designation,'') AS designation
        FROM `contacts_contacts`
        WHERE contact_id = ?
        ORDER BY name
        LIMIT ?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) { echo json_encode(['success'=>false,'error'=>'db_prepare_failed','sql_error'=>$mysqli->error]); exit; }
$stmt->bind_param('ii', $contact_id, $limit);
if (!$stmt->execute()) {
    echo json_encode(['success'=>false,'error'=>'db_exec_failed','sql_error'=>$stmt->error]);
    $stmt->close();
    exit;
}
$res = $stmt->get_result();
$items = [];
while ($row = $res->fetch_assoc()) $items[] = $row;
$stmt->close();

echo json_encode(['success'=>true, 'items'=>$items]);
exit;
