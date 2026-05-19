<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../public/_auth.php';

header('Content-Type: application/json');

$response = ['success' => false];

/* ---------------------------------
   BASIC VALIDATION
--------------------------------- */
$name = trim($_POST['pname'] ?? '');

if ($name === '') {
  echo json_encode(['success' => false, 'message' => 'Package name is required']);
  exit;
}

/* ---------------------------------
   BASIC PACKAGE DATA
--------------------------------- */
$destination     = $_POST['pdestination'] ?? null;
$status          = $_POST['status'] ?? 'draft';
$duration_days   = (int)($_POST['duration_days'] ?? 1);
$duration_nights = (int)($_POST['duration_nights'] ?? 0);
$min_passengers  = (int)($_POST['min_passengers'] ?? 1);
$max_passengers  = (int)($_POST['max_passengers'] ?? 1);
$valid_from      = $_POST['valid_from'] ?? null;
$valid_to        = $_POST['valid_to'] ?? null;
$description     = $_POST['description'] ?? null;

$components_json = $_POST['components'] ?? '[]';
$components      = json_decode($components_json, true) ?: [];

$ptype = 'custom';

/* ---------------------------------
   PRICING INPUTS (FROM CLIENT)
--------------------------------- */
$currency          = $_POST['currency'] ?? 'GBP';

$margin_source     = $_POST['margin_source'] ?? 'package';
$markup_type       = $_POST['markup_type'] ?? 'percentage';
$markup_value      = max(0, (float)($_POST['markup_value'] ?? 0));

$commission_type   = $_POST['commission_type'] ?? 'percentage';
$commission_value  = max(0, (float)($_POST['commission_value'] ?? 0));

/* ---------------------------------
   TOTAL COST
--------------------------------- */
$total_cost = 0.0;

foreach ($components as $c) {
  $qty  = max(0, (float)($c['qty'] ?? 1));
  $cost = max(0, (float)($c['cost'] ?? 0));
  $total_cost += ($qty * $cost);
}

/* ---------------------------------
   MARKUP CALCULATION
--------------------------------- */
$markup_amount = 0.0;

if ($margin_source === 'product' && $components) {

  foreach ($components as $c) {
    $qty  = max(0, (float)($c['qty'] ?? 1));
    $cost = max(0, (float)($c['cost'] ?? 0));
    $line_cost = $qty * $cost;

    if ($markup_type === 'percentage') {
      $markup_amount += $line_cost * ($markup_value / 100);
    } else {
      $markup_amount += ($markup_value / count($components));
    }
  }

} else {

  if ($markup_type === 'percentage') {
    $markup_amount = $total_cost * ($markup_value / 100);
  } else {
    $markup_amount = $markup_value;
  }
}

/* ---------------------------------
   SELL PRICE BEFORE COMMISSION
--------------------------------- */
$sell_price = $sell_before_commission = $total_cost + $markup_amount;
$sell_before_commission = $total_cost + $markup_amount;

/* ---------------------------------
   COMMISSION (CLIENT-DRIVEN)
--------------------------------- */
$commission_amount = 0.0;

if ($commission_type === 'percentage') {
  $commission_amount = $sell_before_commission * ($commission_value / 100);
}

/* ---------------------------------
   FINAL PRICES
--------------------------------- */
$total_cost  = round($total_cost, 2);
// $sell_price  = round($sell_before_commission + $commission_amount, 2);

$room_rate = 0;

/* ---------------------------------
   PRICING JSON (EXACT FORMAT)
--------------------------------- */
$pricing = [
  'currency'          => $currency,
  'valid_from'        => $valid_from,
  'valid_to'          => $valid_to,
  'total_cost'        => $total_cost,
  'sell_price'        => $sell_price,
  'room_rate'         => $room_rate,
  'markup_type'       => $markup_type,
  'markup_value'      => $markup_value,
  'margin_source'     => $margin_source,
  'commission_type'   => $commission_type,
  'commission_value'  => $commission_value
];

$pricing_json = json_encode($pricing, JSON_UNESCAPED_UNICODE);

/* ---------------------------------
   CREATE PACKAGE
--------------------------------- */
$stmt = $mysqli->prepare("
  INSERT INTO packages (
    type,
    name,
    destination,
    status,
    duration_days,
    duration_nights,
    min_passengers,
    max_passengers,
    valid_from,
    valid_to,
    description,
    components,
    pricing,
    created_at,
    updated_at
  ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
");

$stmt->bind_param(
  "ssssiiiisssss",
  $ptype,
  $name,
  $destination,
  $status,
  $duration_days,
  $duration_nights,
  $min_passengers,
  $max_passengers,
  $valid_from,
  $valid_to,
  $description,
  $components_json,
  $pricing_json
);

if (!$stmt->execute()) {
  echo json_encode(['success' => false, 'message' => 'Failed to create package']);
  exit;
}

$package_id = $stmt->insert_id;

/* ---------------------------------
   SYNC PACKAGE ITEMS
--------------------------------- */
if ($components) {
  $ins = $mysqli->prepare("
    INSERT INTO package_items (package_id, product_id)
    VALUES (?,?)
  ");

  foreach ($components as $c) {
    if (!empty($c['product_id'])) {
      $pid = (int)$c['product_id'];
      $ins->bind_param("ii", $package_id, $pid);
      $ins->execute();
    }
  }
}

/* ---------------------------------
   LOG ACTIVITY
--------------------------------- */
$site->agent_log(
  "Custom package created (£{$sell_price}, " . count($components) . " components)",
  $package_id,
  "package"
);

/* ---------------------------------
   BUILD UI-READY COMPONENTS
--------------------------------- */
$ui_components = [];

if ($components) {

  $pstmt = $mysqli->prepare("
    SELECT name, product_type
    FROM products
    WHERE id = ?
    LIMIT 1
  ");

  foreach ($components as $c) {

    if (empty($c['product_id'])) continue;

    $pid  = (int)$c['product_id'];
    $qty  = max(1, (float)($c['qty'] ?? 1));
    $cost = max(0, (float)($c['cost'] ?? 0));

    // Fetch product details
    $pstmt->bind_param("i", $pid);
    $pstmt->execute();
    $res = $pstmt->get_result();
    $p   = $res->fetch_assoc();

    if (!$p) continue;

    // Sell per component (simple version)
    $sell = $cost;

    if ($markup_type === 'percentage') {
      $sell += $cost * ($markup_value / 100);
    } else if ($markup_type === 'fixed') {
      $sell += ($markup_value / max(1, count($components)));
    }

    $ui_components[] = [
      'product_id' => $pid,
      'name'       => $p['name'],
      'type'       => $p['product_type'],
      'qty'        => $qty,
      'cost'       => round($cost, 2),
      'sell'       => round($sell, 2)
    ];
  }
}

/* ---------------------------------
   RETURN UI-READY PACKAGE
--------------------------------- */
$response['success'] = true;
$response['package'] = [
  'id'              => $package_id,
  'name'            => $name,
  'description'     => $description ?? '',
  'duration_days'   => $duration_days,
  'duration_nights' => $duration_nights,
  'duration'        => $duration_days . 'D / ' . $duration_nights . 'N',
  'base_cost'       => $total_cost,
  'sell_price'      => $sell_price,
  'room_rate'      => $room_rate,
  'components'      => $ui_components,
  'type'            => 'custom'
];

echo json_encode($response);
?>