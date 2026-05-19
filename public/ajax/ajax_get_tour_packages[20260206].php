<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';

header('Content-Type: application/json');

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
$q = $mysqli->query("
  SELECT
    id,
    name,
    description,
    duration_days,
    duration_nights,
    components,
    pricing
  FROM packages
  WHERE status = 'active' and type = 'normal'
  ORDER BY name
");

$packages = [];

while ($pkg = $q->fetch_assoc()) {

  $components = json_decode($pkg['components'] ?? '[]', true) ?: [];
  $pricing    = json_decode($pkg['pricing'] ?? '{}', true) ?: [];

  $marginSource = $pricing['margin_source'] ?? 'package';

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