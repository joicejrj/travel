<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === "list") {
    $res = $mysqli->query("SELECT id, category FROM incident_categories ORDER BY id ASC");

    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(["status" => true, "data" => $data]);
    exit;
}

if ($action === "add") {
    $category = trim($_POST['category'] ?? '');

    if ($category === '') {
        echo json_encode(["status" => false, "msg" => "Category name is required"]);
        exit;
    }

    $stmt = $mysqli->prepare("INSERT INTO incident_categories (category) VALUES (?)");
    $stmt->bind_param("s", $category);
    $stmt->execute();

    $insert_id = $stmt->insert_id;

    $site->agent_log("Added new incident category ".$category);

    echo json_encode(["status" => true, "msg" => "Category added", "id" => $insert_id]);
    exit;
}

if ($action === "delete") {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(["status" => false, "msg" => "Invalid ID"]);
        exit;
    }

    $mysqli->query("DELETE FROM incident_categories WHERE id=$id LIMIT 1");

    $site->agent_log("Deleted incident category #".$id);

    echo json_encode(["status" => true]);
    exit;
}

echo json_encode(["status" => false, "msg" => "Invalid action"]);
?>