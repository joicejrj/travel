<?php
require_once __DIR__ . '/../../config/db.php';

$code = trim($_POST['code'] ?? '');
$id   = intval($_POST['id'] ?? 0);

if ($code === '') {
    echo json_encode(['exists' => false]);
    exit;
}

$stmt = $mysqli->prepare("
    SELECT id 
    FROM discounts 
    WHERE discount_code = ?
    AND id != ?
    LIMIT 1
");
$stmt->bind_param("si", $code, $id);
$stmt->execute();
$stmt->store_result();

echo json_encode([
    'exists' => $stmt->num_rows > 0
]);

$stmt->close();
?>