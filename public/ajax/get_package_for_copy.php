<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../public/_auth.php';

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false]);
    exit;
}

/* Fetch original package */
$stmt = $mysqli->prepare("
    SELECT
        name,
        destination,
        zone_id,
        status,
        valid_from,
        valid_to,
        duration_days,
        duration_nights,
        min_passengers,
        max_passengers,
        description
    FROM packages
    WHERE id = ?
      AND type = 'normal'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo json_encode(['success' => false]);
    exit;
}

/* ---------------------------------------------------
   Generate new name with incremental suffix
--------------------------------------------------- */

$baseName = $data['name'];

/* Remove existing numeric suffix if any */
$baseName = preg_replace('/\s+\d+$/', '', $baseName);

/* Count existing packages with same base name */
$countStmt = $mysqli->prepare("
    SELECT COUNT(*) as total
    FROM packages
    WHERE type = 'normal'
      AND (name = ? OR name REGEXP ?)
");

$regexp = '^' . $mysqli->real_escape_string($baseName) . '[[:space:]][0-9]+$';

$countStmt->bind_param("ss", $baseName, $regexp);
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();

$total = (int)$countResult['total'];

if ($total > 0) {
    $data['name'] = $baseName . ' ' . $total;
} else {
    $data['name'] = $baseName;
}

echo json_encode([
    'success' => true,
    'package' => $data
]);
?>