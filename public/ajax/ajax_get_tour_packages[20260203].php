<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';

header('Content-Type: application/json');

$q = $mysqli->query("
  SELECT id, name, description, duration, amount
  FROM tour_packages
  ORDER BY name
");

$data = [];
while ($r = $q->fetch_assoc()) {
  $data[] = $r;
}

echo json_encode([
  'success' => true,
  'packages' => $data
]);
?>