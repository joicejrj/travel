<?php
require_once __DIR__ . '/config/db.php';

$phone = $_POST['phone'] ?? '';

if (!$phone) {
    echo json_encode(['success'=>false]);
    exit;
}

// Remove spaces, +, -, brackets
$phone = preg_replace('/[^0-9]/', '', $phone);

$stmt = $mysqli->prepare("SELECT name, email FROM customers WHERE phone=? LIMIT 1");
$stmt->bind_param("s", $phone);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row) {
    echo json_encode([
        'success'=>true,
        'name'=>$row['name'],
        'email'=>$row['email']
    ]);
} else {
    echo json_encode(['success'=>false]);
}
?>