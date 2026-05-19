<?php
// check_phone_exists.php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';   // adjust path if needed
require_once __DIR__ . '/../../config/functions.php';

$response = [
  'success' => false
];

$phone = $_POST['phone'] ?? '';
$phone = preg_replace('/\D+/', '', $phone); // keep digits only

if ($phone === '') {
  echo json_encode($response);
  exit;
}

/* --------------------------------
   1. Check Customers (Highest Priority)
---------------------------------- */
$stmt = $mysqli->prepare("
  SELECT id 
  FROM customers 
  WHERE REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', '') = ?
  LIMIT 1
");
$stmt->bind_param("s", $phone);
$stmt->execute();
$stmt->bind_result($id);

if ($stmt->fetch()) {
  echo json_encode([
    'success' => true,
    'type'    => 'customer',
    'id'      => $id
  ]);
  exit;
}
$stmt->close();

/* --------------------------------
   2. Check Contacts
---------------------------------- */
$stmt = $mysqli->prepare("
  SELECT id 
  FROM contacts 
  WHERE REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', '') = ?
  LIMIT 1
");
$stmt->bind_param("s", $phone);
$stmt->execute();
$stmt->bind_result($id);

if ($stmt->fetch()) {
  echo json_encode([
    'success' => true,
    'type'    => 'contact',
    'id'      => $id
  ]);
  exit;
}
$stmt->close();

/* --------------------------------
   3. Check Suppliers
---------------------------------- */
$stmt = $mysqli->prepare("
  SELECT id 
  FROM suppliers 
  WHERE REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', '') = ?
  LIMIT 1
");
$stmt->bind_param("s", $phone);
$stmt->execute();
$stmt->bind_result($id);

if ($stmt->fetch()) {
  echo json_encode([
    'success' => true,
    'type'    => 'supplier',
    'id'      => $id
  ]);
  exit;
}
$stmt->close();

/* --------------------------------
   4. Nothing Found
---------------------------------- */
echo json_encode($response);
exit;
?>