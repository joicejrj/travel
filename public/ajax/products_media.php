<?php
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';
$product_id = (int)($_REQUEST['id'] ?? 0);

$baseDir = __DIR__ . '/../../uploads/products/' . $product_id . '/';
$baseUrl = 'uploads/products/' . $product_id . '/';

if (!$product_id) {
  echo json_encode(['success' => false, 'error' => 'Invalid product']);
  exit;
}

/* ---------------------------------
   LIST MEDIA
--------------------------------- */
if ($action === 'list') {

  $files = [];

  if (is_dir($baseDir)) {
    foreach (scandir($baseDir) as $f) {
      if ($f === '.' || $f === '..') continue;

      $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));

      $files[] = [
        'name'     => $f,
        'url'      => $baseUrl . $f,
        'is_image' => in_array($ext, ['jpg','jpeg','png','webp'])
      ];
    }
  }

  echo json_encode(['success' => true, 'files' => $files]);
  exit;
}

/* ---------------------------------
   DELETE MEDIA
--------------------------------- */
if ($action === 'delete') {

  $file = basename($_POST['file'] ?? '');

  if (!$file) {
    echo json_encode(['success' => false, 'error' => 'File missing']);
    exit;
  }

  $path = $baseDir . $file;

  if (is_file($path)) {
    unlink($path);
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'error' => 'File not found']);
  }
  exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>