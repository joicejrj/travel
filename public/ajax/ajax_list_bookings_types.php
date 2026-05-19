<?php
// public/ajax/ajax_list_bookings_types.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';

$inter_type = isset($_GET['inter_type']) ? $mysqli->real_escape_string($_GET['inter_type']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 500;

$sql = "SELECT id, name, COALESCE(inter_type,'') AS inter_type
        FROM bookings_types
        ORDER BY name
        LIMIT {$limit}";

$out = ['success' => false];
if ($res = $mysqli->query($sql)) {
    $items = [];
    while ($r = $res->fetch_assoc()) $items[] = $r;
    $res->free();
    $out = ['success' => true, 'items' => $items];
} else {
    $out = ['success' => false, 'error' => 'db_error', 'sql_error' => $mysqli->error];
}
echo json_encode($out);
exit;
?>