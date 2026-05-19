<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php'; // for $site->agent_log()
require_once __DIR__ . '/../../public/_auth.php';

header('Content-Type: application/json');

$response = ['success' => false];

$product_id = (int)($_POST['id'] ?? 0);
$tab        = $_POST['tab'] ?? '';

if (!$product_id || !$tab) {
  $response['error'] = 'Invalid request';
  echo json_encode($response);
  exit;
}

/* ---------------------------------
   FETCH CURRENT PRODUCT
--------------------------------- */
$stmt = $mysqli->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$old = $stmt->get_result()->fetch_assoc();

if (!$old) {
  $response['error'] = 'Product not found';
  echo json_encode($response);
  exit;
}

/* ---------------------------------
   HELPER: LOG CHANGE
--------------------------------- */
function log_change($label, $oldVal, $newVal, $product_id, $site) {
  if ($oldVal != $newVal) {
    $msg = "$label updated from \"$oldVal\" to \"$newVal\"";
    $site->agent_log($msg, $product_id, "product");
  }
}

/* ---------------------------------
   BASIC INFO
--------------------------------- */
if ($tab === 'basic') {

  $supplier_id       = (int)$_POST['supplier_id'];
  $zone_id       = (int)$_POST['zone_id'];
  $name              = trim($_POST['name']);
  $product_type      = $_POST['product_type'];
  $status            = $_POST['status'];
  $destination       = $_POST['destination'] ?? null;
  $duration          = $_POST['duration'] ?? null;
  $short_description = $_POST['short_description'] ?? null;
  $description       = $_POST['description'] ?? null;

  $stmt = $mysqli->prepare("
    UPDATE products SET
      supplier_id=?,
      zone_id=?,
      name=?,
      product_type=?,
      status=?,
      destination=?,
      duration=?,
      short_description=?,
      description=?,
      updated_at=NOW()
    WHERE id=?
  ");

  $stmt->bind_param(
    "iisssssssi",
    $supplier_id,
    $zone_id,
    $name,
    $product_type,
    $status,
    $destination,
    $duration,
    $short_description,
    $description,
    $product_id
  );

  if ($stmt->execute()) {

    log_change("Supplier", $old['supplier_id'], $supplier_id, $product_id, $site);
    log_change("Zone", $old['zone_id'], $zone_id, $product_id, $site);
    log_change("Product name", $old['name'], $name, $product_id, $site);
    log_change("Product type", $old['product_type'], $product_type, $product_id, $site);
    log_change("Status", $old['status'], $status, $product_id, $site);

    $response['success'] = true;
  }
}

/* ---------------------------------
   PRICING
--------------------------------- */
if ($tab === 'pricing') {

  $cost         = $_POST['cost'] ?? null;
  $margin_type  = $_POST['margin_type'];
  $margin_value = $_POST['margin_value'] ?? null;

  $stmt = $mysqli->prepare("
    UPDATE products SET
      cost=?,
      margin_type=?,
      margin_value=?,
      updated_at=NOW()
    WHERE id=?
  ");

  $stmt->bind_param("dsdi", $cost, $margin_type, $margin_value, $product_id);

  if ($stmt->execute()) {

    log_change("Cost", $old['cost'], $cost, $product_id, $site);
    log_change("Margin type", $old['margin_type'], $margin_type, $product_id, $site);
    log_change("Margin value", $old['margin_value'], $margin_value, $product_id, $site);

    $response['success'] = true;
  }
}

/* ---------------------------------
   DETAILS
--------------------------------- */
if ($tab === 'details') {

  $inclusions = $_POST['inclusions'] ?? null;
  $exclusions = $_POST['exclusions'] ?? null;
  $tags       = $_POST['tags'] ?? null;

  $stmt = $mysqli->prepare("
    UPDATE products SET
      inclusions=?,
      exclusions=?,
      tags=?,
      updated_at=NOW()
    WHERE id=?
  ");

  $stmt->bind_param("sssi", $inclusions, $exclusions, $tags, $product_id);

  if ($stmt->execute()) {

    log_change("Inclusions", $old['inclusions'], $inclusions, $product_id, $site);
    log_change("Exclusions", $old['exclusions'], $exclusions, $product_id, $site);
    log_change("Tags", $old['tags'], $tags, $product_id, $site);

    $response['success'] = true;
  }
}

/* ---------------------------------
   MEDIA
--------------------------------- */
if ($tab === 'media' && !empty($_FILES['media']['name'][0])) {

  $uploadDir = __DIR__ . '/../../uploads/products/' . $product_id . '/';
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }

  // Allowed extensions and MIME types
  $allowedExtensions = ['jpg','jpeg','png','gif','webp','pdf'];
  $allowedMimeTypes  = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf'
  ];

  foreach ($_FILES['media']['name'] as $i => $name) {

    $tmp  = $_FILES['media']['tmp_name'][$i];
    $size = $_FILES['media']['size'][$i];

    if (!$tmp || !is_uploaded_file($tmp)) {
      continue;
    }

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $mime = mime_content_type($tmp);

    // ❌ Reject invalid files
    if (!in_array($ext, $allowedExtensions, true) ||
        !in_array($mime, $allowedMimeTypes, true)) {
      continue;
    }

    // Optional: limit file size (e.g., 5MB)
    if ($size > 5 * 1024 * 1024) {
      continue;
    }

    $file = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($name));

    if (move_uploaded_file($tmp, $uploadDir . $file)) {
      $site->agent_log("Media uploaded: $file", $product_id, "product");
    }
  }

  $response['success'] = true;
}

/* ---------------------------------
   INTEGRATION
--------------------------------- */
if ($tab === 'integration') {

  $integration_type     = $_POST['integration_type'] ?? 'manual';
  $purchase_currency    = $_POST['purchase_currency'] ?? 'GBP';
  $api_endpoint         = $_POST['api_endpoint'] ?? null;
  $api_credentials_ref = $_POST['api_credentials_ref'] ?? null;
  $payment_terms        = $_POST['payment_terms'] ?? null;
  $price_sheet_file     = null;

  /* ---- File upload for static pricing ---- */
  if ($integration_type === 'static_pricing' && !empty($_FILES['price_sheet']['name'])) {

    $allowedExt = ['xls', 'xlsx', 'pdf', 'csv'];
    $allowedMime = [
      'application/pdf',
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'text/csv',
      'application/csv',
      'text/plain' // some servers report CSV as text/plain
    ];

    $fileTmp  = $_FILES['price_sheet']['tmp_name'];
    $fileName = $_FILES['price_sheet']['name'];
    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $fileMime = mime_content_type($fileTmp);

    // ❌ Invalid extension
    if (!in_array($fileExt, $allowedExt)) {
      $response['error'] = 'Only Excel (.xls, .xlsx), CSV, or PDF files are allowed';
      echo json_encode($response);
      exit;
    }

    // ❌ Invalid MIME type
    if (!in_array($fileMime, $allowedMime)) {
      $response['error'] = 'Invalid file type uploaded';
      echo json_encode($response);
      exit;
    }

    $uploadDir = __DIR__ . '/../../uploads/price_sheets/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $price_sheet_file = 'product_' . $product_id . '_' . time() . '.' . $fileExt;

    move_uploaded_file($fileTmp, $uploadDir . $price_sheet_file);
  }

  /* ---- Load old values ---- */
  $check = $mysqli->prepare("
    SELECT * FROM product_integrations WHERE product_id=?
  ");
  $check->bind_param("i", $product_id);
  $check->execute();
  $old = $check->get_result()->fetch_assoc();

  $api_endpoint_db = null;
  $api_credentials_ref_db = null;

  if ($integration_type === 'api') {
    $api_endpoint_db = $api_endpoint;
    $api_credentials_ref_db = $api_credentials_ref;
  }

  if ($old) {

    $stmt = $mysqli->prepare("
      UPDATE product_integrations SET
        integration_type=?,
        purchase_currency=?,
        api_endpoint=?,
        api_credentials_ref=?,
        price_sheet_file=COALESCE(?, price_sheet_file),
        payment_terms=?,
        updated_at=NOW()
      WHERE product_id=?
    ");

    $stmt->bind_param(
      "ssssssi",
      $integration_type,
      $purchase_currency,
      $api_endpoint_db,
      $api_credentials_ref_db,
      $price_sheet_file,
      $payment_terms,
      $product_id
    );

  } else {

    $stmt = $mysqli->prepare("
      INSERT INTO product_integrations
        (product_id, integration_type, purchase_currency, api_endpoint,
         api_credentials_ref, price_sheet_file, payment_terms)
      VALUES (?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
      "issssss",
      $product_id,
      $integration_type,
      $purchase_currency,
      $api_endpoint_db,
      $api_credentials_ref_db,
      $price_sheet_file,
      $payment_terms
    );

  }

  if ($stmt->execute()) {

    log_change("Integration type", $old['integration_type'] ?? '-', $integration_type, $product_id, $site);
    log_change("Purchase currency", $old['purchase_currency'] ?? '-', $purchase_currency, $product_id, $site);

    if ($integration_type === 'api') {
      log_change("API endpoint", $old['api_endpoint'] ?? '-', $api_endpoint, $product_id, $site);
      log_change("API credentials", $old['api_credentials_ref'] ?? '-', $api_credentials_ref, $product_id, $site);
    }

    if ($integration_type === 'static_pricing' && $price_sheet_file) {
      log_change("Price sheet", $old['price_sheet_file'] ?? '-', $price_sheet_file, $product_id, $site);
    }

    log_change("Payment terms", $old['payment_terms'] ?? '-', $payment_terms, $product_id, $site);

    $response['success'] = true;
  }
}

/* ---------------------------------
   OUTPUT
--------------------------------- */
echo json_encode($response);
?>