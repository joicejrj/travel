<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';

header('Content-Type: application/json');

/* ---------------------------------------
   INPUT: TRAVEL DATE
--------------------------------------- */
$travel_date = $_GET['travel_date'] ?? null;

/* ---------------------------------------
   OPTIONAL FILTERS
--------------------------------------- */
$origin      = trim($_GET['origin'] ?? '');
$destination = trim($_GET['destination'] ?? '');

if (!$travel_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $travel_date)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or missing travel date'
    ]);
    exit;
}

/* ---------------------------------------
   LOAD ACTIVE PRODUCTS (same as view)
--------------------------------------- */
$products = [];

$res = $mysqli->query("
  SELECT
    id,
    name,
    product_type,
    cost,
    margin_type,
    margin_value
  FROM products
  WHERE status = 'active'
");

while ($row = $res->fetch_assoc()) {
  $products[$row['id']] = $row;
}

/* ---------------------------------------
   LOAD ACTIVE PACKAGES
--------------------------------------- */
$sql = "
  SELECT
    id,
    name,
    description,
    duration_days,
    duration_nights,
    components,
    discount_id,
    discount_expiry,
    pricing
  FROM packages
  WHERE status = 'active'
    AND type = 'normal'
    AND ((? BETWEEN valid_from AND valid_to)
         OR (valid_from <= ? AND valid_to IS NULL))
";

$params = [$travel_date, $travel_date];
$types  = 'ss';

/* ORIGIN FILTER */
if ($origin !== '') {
    $sql .= " AND origin LIKE ?";
    $params[] = '%' . $origin . '%';
    $types   .= 's';
}

/* DESTINATION FILTER */
if ($destination !== '') {
    $sql .= " AND destination LIKE ?";
    $params[] = '%' . $destination . '%';
    $types   .= 's';
}

$sql .= " ORDER BY name";

$q = $mysqli->prepare($sql);

$q->bind_param($types, ...$params);

$q->execute();
$res = $q->get_result();

$packages = [];

while ($pkg = $res->fetch_assoc()) {

  $components = json_decode($pkg['components'] ?? '[]', true) ?: [];
  $pricing    = json_decode($pkg['pricing'] ?? '{}', true) ?: [];

  $marginSource = $pricing['margin_source'] ?? 'package';
  $room_rate = $pricing['room_rate'] ?? 0;

  $baseCost  = 0;
  $sellPrice = 0;

  $componentRows = [];

  /* ---------------------------------------
     COMPONENT CALCULATION (VIEW LOGIC)
  --------------------------------------- */
  foreach ($components as $c) {

    $productId = (int)($c['product_id'] ?? 0);
    $qty       = (float)($c['qty'] ?? 1);
    $cost      = (float)($c['cost'] ?? 0);

    $lineCost = $qty * $cost;
    $baseCost += $lineCost;

    $lineSell = $lineCost;

    /* PRODUCT-LEVEL MARGIN */
    if ($marginSource === 'product' && isset($products[$productId])) {

      $p  = $products[$productId];
      $mt = $p['margin_type'] ?? 'percentage';
      $mv = (float)($p['margin_value'] ?? 0);

      if ($mt === 'percentage') {
        $lineSell += $lineCost * ($mv / 100);
      } else {
        $lineSell += $mv * $qty;
      }
    }

    $sellPrice += $lineSell;

    $componentRows[] = [
      'product_id' => $productId,
      'name'       => $products[$productId]['name'] ?? '',
      'type'       => $products[$productId]['product_type'] ?? '',
      'qty'        => $qty,
      'cost'       => round($lineCost, 2),
      'sell'       => round($lineSell, 2)
    ];
  }

  /* ---------------------------------------
     PACKAGE-LEVEL MARGIN
  --------------------------------------- */
  if ($marginSource === 'package') {

    $sellPrice = $baseCost;

    $markupType  = $pricing['markup_type'] ?? 'percentage';
    $markupValue = (float)($pricing['markup_value'] ?? 0);

    if ($markupType === 'percentage') {
      $sellPrice += $baseCost * ($markupValue / 100);
    } else {
      $sellPrice += $markupValue;
    }
  }

  /* ---------------------------------------
     FINAL PACKAGE OBJECT
  --------------------------------------- */
  $packages[] = [
    'id'            => (int)$pkg['id'],
    'name'          => $pkg['name'],
    'description'   => $pkg['description'],
    'duration'      => (int)$pkg['duration_days'] . ' Days / ' .
                       (int)$pkg['duration_nights'] . ' Nights',
    'base_cost'     => round($baseCost, 2),
    'sell_price'    => round($sellPrice, 2),
    'room_rate'    => round($room_rate, 2),
    'discount_id'   => (int)($pkg['discount_id'] ?? 0),
    'discount_expiry' => $pkg['discount_expiry'] ?? null,
    'margin_source' => $marginSource,
    'components'    => $componentRows
  ];
}

/* ---------------------------------------
   RESPONSE
--------------------------------------- */
echo json_encode([
  'success'  => true,
  'packages' => $packages
]);
?>