<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php'; // for $site->agent_log()
require_once __DIR__ . '/../../public/_auth.php';

header('Content-Type: application/json');

// ini_set('display_errors',1);

$response = ['success' => false];

$package_id = (int)($_POST['id'] ?? 0);
$tab        = $_POST['tab'] ?? '';

if (!$package_id || !$tab) {
  echo json_encode(['success'=>false,'error'=>'Invalid request']);
  exit;
}

$changes = [];

/* ---------------------------------
   FETCH CURRENT PACKAGE
--------------------------------- */
$stmt = $mysqli->prepare("SELECT * FROM packages WHERE id=?");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$old = $stmt->get_result()->fetch_assoc();

if (!$old) {
  echo json_encode(['success'=>false,'error'=>'Package not found']);
  exit;
}

/* ---------------------------------
   HELPER: LOG CHANGE
--------------------------------- */
// function log_change($label, $oldVal, $newVal, $package_id, $site) {
//   if ($oldVal != $newVal) {
//     $site->agent_log(
//       "$label updated",
//       $package_id,
//       "package"
//     );
//   }
// }
function log_change($label, $oldVal, $newVal, &$changes) {
  if ($oldVal != $newVal) {
    $changes[] = $label;
  }
}

/* =========================================================
   BASIC INFO
========================================================= */
if ($tab === 'basic') {

  $name            = trim($_POST['name']);
  $origin     = $_POST['origin'] ?? null;
  $destination     = $_POST['destination'] ?? null;
  $zone_id     = $_POST['zone_id'] ?? null;
  $status          = $_POST['status'];
  $duration_days   = (int)$_POST['duration_days'];
  $duration_nights = (int)$_POST['duration_nights'];
  $min_passengers  = (int)$_POST['min_passengers'];
  $max_passengers  = (int)$_POST['max_passengers'];
  $valid_from      = $_POST['valid_from'] ?: null;
  $valid_to        = $_POST['valid_to'] ?: null;
  $description     = $_POST['description'] ?? null;
  $wordpress_url     = $_POST['wordpress_url'] ?? null;

  $highlights = $_POST['highlights'] ?? null;
  $inclusions = $_POST['inclusions'] ?? null;
  $exclusions = $_POST['exclusions'] ?? null;

  $stmt = $mysqli->prepare("
    UPDATE packages SET
      name=?,
      origin=?,
      destination=?,
      zone_id=?,
      status=?,
      duration_days=?,
      duration_nights=?,
      min_passengers=?,
      max_passengers=?,
      valid_from=?,
      valid_to=?,
      description=?,
      highlights=?,
      inclusions=?,
      exclusions=?,
      wordpress_url=?,
      updated_at=NOW()
    WHERE id=?
  ");

  $stmt->bind_param(
    "sssisiiiisssssssi",
    $name,
    $origin,
    $destination,
    $zone_id,
    $status,
    $duration_days,
    $duration_nights,
    $min_passengers,
    $max_passengers,
    $valid_from,
    $valid_to,
    $description,
    $highlights,
    $inclusions,
    $exclusions,
    $wordpress_url,
    $package_id
  );

  if ($stmt->execute()) {

    log_change("Name", $old['name'], $name, $changes);
    log_change("Status", $old['status'], $status, $changes);
    log_change("Zone", $old['zone_id'], $zone_id, $changes);
    log_change("Origin", $old['origin'], $origin, $changes);
    log_change("Destination", $old['destination'], $destination, $changes);
    log_change("Duration", $old['duration_days'], $duration_days, $changes);
    log_change("Package URL", $old['wordpress_url'], $wordpress_url, $changes);
    log_change("Validity", $old['valid_from'].'-'.$old['valid_to'], $valid_from.'-'.$valid_to, $changes);

    if ($changes) {
      $site->agent_log(
        "Basic info updated (" . implode(', ', $changes) . ")",
        $package_id,
        "package"
      );
    }

    $response['success'] = true;
  }
}

/* =========================================================
   COMPONENTS
========================================================= */
if ($tab === 'components') {

  $components = $_POST['components'] ?? '[]';

  $stmt = $mysqli->prepare("
    UPDATE packages SET
      components=?,
      updated_at=NOW()
    WHERE id=?
  ");
  $stmt->bind_param("si", $components, $package_id);

  if ($stmt->execute()) {

    // Sync package_items table
    $mysqli->query("DELETE FROM package_items WHERE package_id=$package_id");

    $items = json_decode($components, true);
    if (is_array($items)) {
      $ins = $mysqli->prepare("
        INSERT INTO package_items (package_id, product_id)
        VALUES (?,?)
      ");
      foreach ($items as $c) {
        if (!empty($c['product_id'])) {
          $pid = (int)$c['product_id'];
          $ins->bind_param("ii", $package_id, $pid);
          $ins->execute();
        }
      }
    }

    $site->agent_log(
      "Components updated (" . count($items) . " items)",
      $package_id,
      "package"
    );
    $response['success'] = true;
  }
}

/* =========================================================
   ITINERARY
========================================================= */
if ($tab === 'itinerary') {

  $itinerary = $_POST['itinerary'] ?? '[]';

  $stmt = $mysqli->prepare("
    UPDATE packages SET
      itinerary=?,
      updated_at=NOW()
    WHERE id=?
  ");
  $stmt->bind_param("si", $itinerary, $package_id);

  if ($stmt->execute()) {
    $days = count(json_decode($itinerary, true) ?: []);
    $site->agent_log(
      "Itinerary updated ($days days)",
      $package_id,
      "package"
    );

    $response['success'] = true;
  }
}

/* =========================================================
   PRICING
========================================================= */
if ($tab === 'pricing') {

  /* ------------------------------
     PRICING JSON
  ------------------------------ */
  $pricing = [
    'total_cost'       => (float)($_POST['total_cost'] ?? 0),
    'sell_price'       => (float)($_POST['sell_price'] ?? 0),
    'room_rate'       => (float)($_POST['room_rate'] ?? 0),
    'markup_type'      => $_POST['markup_type'] ?? 'percentage',
    'markup_value'     => (float)($_POST['markup_value'] ?? 0),
    'margin_source'    => $_POST['margin_source'] ?? 'package',
    'commission_type'  => $_POST['commission_type'] ?? null,
    'commission_value' => (float)($_POST['commission_value'] ?? 0),
    'currency'         => $_POST['currency'] ?? 'EUR',
    'valid_from'       => $_POST['valid_from'] ?? null,
    'valid_to'         => $_POST['valid_to'] ?? null
  ];

  $pricing_json = json_encode($pricing, JSON_UNESCAPED_UNICODE);

  /* ------------------------------
     DISCOUNT (NULL SAFE)
  ------------------------------ */
  $discount_id     = !empty($_POST['discount_id'])
                      ? (int)$_POST['discount_id']
                      : null;

  $discount_expiry = !empty($_POST['discount_expiry'])
                      ? $_POST['discount_expiry']
                      : null;

  /* ------------------------------
     UPDATE PACKAGE
  ------------------------------ */
  $stmt = $mysqli->prepare("
    UPDATE packages SET
      pricing=?,
      discount_id=?,
      discount_expiry=?,
      updated_at=NOW()
    WHERE id=?
  ");

  $stmt->bind_param(
    "sisi",
    $pricing_json,
    $discount_id,
    $discount_expiry,
    $package_id
  );

  if ($stmt->execute()) {

    /* ------------------------------
       SINGLE SUMMARY LOG
    ------------------------------ */
    $summary = [];

    $summary[] =
      ($pricing['margin_source'] === 'product')
        ? 'Product margin'
        : ucfirst($pricing['markup_type']) . ' markup';

    $summary[] = 'Currency ' . $pricing['currency'];

    if (!empty($pricing['commission_value'])) {
      $summary[] = 'Commission';
    }

    if ($discount_id) {
      $summary[] = 'Discount enabled';
    } else {
      $summary[] = 'No discount';
    }

    $site->agent_log(
      "Pricing updated (" . implode(', ', $summary) . ")",
      $package_id,
      "package"
    );

    $response['success'] = true;
  }
}

/* =========================================================
   MEDIA
========================================================= */
if ($tab === 'media' && !empty($_FILES['media']['name'][0])) {

  $uploadDir = __DIR__ . '/../../uploads/packages/' . $package_id . '/';
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }

  $allowedExtensions = ['jpg','jpeg','png','gif','webp','pdf'];
  $allowedMimeTypes  = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf'
  ];

  $files = [];

  foreach ($_FILES['media']['name'] as $i => $name) {

    $tmp  = $_FILES['media']['tmp_name'][$i];
    $size = $_FILES['media']['size'][$i] ?? 0;

    if (!$tmp || !is_uploaded_file($tmp)) {
      continue;
    }

    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $mime = mime_content_type($tmp);

    // ❌ Reject invalid file types
    if (!in_array($ext, $allowedExtensions, true) ||
        !in_array($mime, $allowedMimeTypes, true)) {
      continue;
    }

    // Extra safety for images (prevents disguised scripts)
    if ($ext !== 'pdf' && !@getimagesize($tmp)) {
      continue;
    }

    // Optional size limit (5 MB)
    if ($size > 5 * 1024 * 1024) {
      continue;
    }

    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '', basename($name));
    $file = time() . '_' . $safeName;

    if (move_uploaded_file($tmp, $uploadDir . $file)) {
      $files[] = $file;
      $site->agent_log("Media uploaded: $file", $package_id, "package");
    }
  }

  if ($files) {
    $existing = json_decode($old['media'] ?? '[]', true) ?: [];
    $merged   = json_encode(array_merge($existing, $files));

    $mysqli->query(
      "UPDATE packages SET media='" . $mysqli->real_escape_string($merged) . "'
       WHERE id=" . (int)$package_id
    );

    $site->agent_log(
      count($files) . " media file(s) uploaded",
      $package_id,
      "package"
    );
  }

  $response['success'] = true;
}

/* ---------------------------------
   OUTPUT
--------------------------------- */
echo json_encode($response);
?>