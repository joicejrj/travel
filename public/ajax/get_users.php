<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

$type    = $_POST['type'] ?? "";
$keyword = $_POST['keyword'] ?? "";
$keyword = trim($keyword);

$table = "";
if ($type === "employee")  $table = "employees";
if ($type === "recruiter") $table = "recruiters";
if ($type === "customer")  $table = "customers";

if (!$table) {
    echo json_encode(["success" => false, "data" => []]);
    exit;
}

// Build search query
$sql = "SELECT id, name FROM {$table} ";
if ($keyword !== "") {
    $safe = $mysqli->real_escape_string($keyword);
    $sql .= "WHERE name LIKE '%{$safe}%' ";
}

$sql .= "ORDER BY name LIMIT 10";

$res = $mysqli->query($sql);
$data = [];

while ($r = $res->fetch_assoc()) {
    $data[] = $r;
}

echo json_encode(["success" => true, "data" => $data]);
?>