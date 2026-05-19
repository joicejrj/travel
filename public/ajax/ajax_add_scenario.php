<?php
// public/ajax/ajax_add_scenario.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';

/**
 * Minimal slugify (ASCII-friendly)
 */
function slugify($text) {
    // convert to UTF-8, lower, strip tags
    $text = mb_strtolower(trim(strip_tags($text)), 'UTF-8');
    // replace non-alnum with hyphen
    $text = preg_replace('/[^\p{L}\p{Nd}]+/u', '-', $text);
    // remove leading/trailing hyphens
    $text = trim($text, '-');
    // fallback if empty
    if ($text === '') $text = 'item-' . time();
    // limit length
    return substr($text, 0, 120);
}

// accept POST (recommended) or GET if caller uses GET
$inter_type = '';
$title = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inter_type = isset($_POST['inter_type']) ? trim($_POST['inter_type']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
} else {
    $inter_type = isset($_GET['inter_type']) ? trim($_GET['inter_type']) : '';
    $title = isset($_GET['title']) ? trim($_GET['title']) : '';
}

$out = ['success' => false];

if ($inter_type === '' || $title === '') {
    echo json_encode(['success' => false, 'error' => 'missing_parameters']);
    exit;
}

$name = $mysqli->real_escape_string($title);
$slug = $mysqli->real_escape_string(slugify($title));
$inter_type_esc = $mysqli->real_escape_string($inter_type);
$now = date('Y-m-d H:i:s');

// using prepared statement for insert
$stmt = $mysqli->prepare("INSERT INTO scenarios (name, slug, inter_type, created_at) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'prepare_failed', 'mysqli_error' => $mysqli->error]);
    exit;
}
$stmt->bind_param('ssss', $name, $slug, $inter_type_esc, $now);

if ($stmt->execute()) {
    $id = $stmt->insert_id;
    $stmt->close();
    $item = [
        'id' => $id,
        'title' => $title,
        'slug' => $slug,
        'inter_type' => $inter_type
    ];
    $out = ['success' => true, 'item' => $item];
} else {
    $err = $stmt->error;
    $stmt->close();
    $out = ['success' => false, 'error' => 'insert_failed', 'stmt_error' => $err];
}

echo json_encode($out);
exit;
