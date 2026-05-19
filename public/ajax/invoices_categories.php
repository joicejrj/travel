<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$action   = $_POST['action'] ?? 'add';
$category = trim($_POST['category'] ?? '');
$type     = trim($_POST['type'] ?? 'Expense');

switch ($action) {

  /* ============================================================
     🟢 ADD CATEGORY
     ============================================================ */
  case 'add':
    if ($category == '') {
      echo json_encode(['success' => false, 'error' => 'Category name is required.']);
      exit;
    }

    // Check if category already exists
    $stmt = $mysqli->prepare("SELECT id FROM payment_categories WHERE category = ? AND type = ?");
    $stmt->bind_param("ss", $category, $type);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      echo json_encode(['success' => false, 'error' => 'Category already exists.']);
      $stmt->close();
      exit;
    }

    $stmt->close();

    // Insert new category
    $stmt = $mysqli->prepare("INSERT INTO payment_categories (category, type, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $category, $type);
    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => $ok, 'category' => $category]);
    break;

  /* ============================================================
     🟡 FETCH ALL CATEGORIES
     ============================================================ */
  case 'fetch':
    $categories = [];
    $stmt = $mysqli->prepare("SELECT id, category, type FROM payment_categories WHERE type=? ORDER BY id ASC");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $categories[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $categories]);
    break;

  /* ============================================================
     🔴 DELETE CATEGORY
     ============================================================ */
  case 'delete':
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
      echo json_encode(['success' => false, 'error' => 'Invalid category ID.']);
      exit;
    }

    $stmt = $mysqli->prepare("DELETE FROM payment_categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => $ok]);
    break;

  /* ============================================================
     ⚙️ DEFAULT
     ============================================================ */
  default:
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    break;
}
?>