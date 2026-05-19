<?php
// public/ajax/ajax_list_customer_contacts.php
// Returns JSON { success: true, items: [...] } or { success: false, error: '...' }

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/db.php'; // adjust path if needed

$customer_id = isset($_GET['customer_id']) ? (int) $_GET['customer_id'] : 0;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 1000;
$limit = ($limit > 0 && $limit <= 5000) ? $limit : 1000;

if (!$customer_id) {
    echo json_encode(['success' => false, 'error' => 'missing_customer_id']);
    exit;
}

$sql = "SELECT id,
               COALESCE(name,'') AS name,
               COALESCE(email,'') AS email,
               COALESCE(phone,'') AS phone,
               COALESCE(designation,'') AS designation
        FROM `customers_contacts`
        WHERE customer_id = ?
        ORDER BY name
        LIMIT ?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(['success'=>false,'error'=>'db_prepare_failed','sql_error'=>$mysqli->error]);
    exit;
}
$stmt->bind_param('ii', $customer_id, $limit);
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
