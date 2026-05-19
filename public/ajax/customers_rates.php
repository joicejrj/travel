<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

/* ======================================================
   FETCH TRADES FOR SITE (FROM customers_trades)
====================================================== */
if ($action === 'fetch_trades') {

  $site_id = (int)$_POST['site_id'];

  $stmt = $mysqli->prepare("
    SELECT DISTINCT t.id, t.trade_name
    FROM customers_trades t
    INNER JOIN customer_site_trades st ON st.trade_id = t.id
    WHERE st.site_id = ?
    ORDER BY t.trade_name
  ");
  $stmt->bind_param("i", $site_id);
  $stmt->execute();
  $res = $stmt->get_result();

  $rows = [];
  while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
  }

  echo json_encode($rows);
  exit;
}

/* ======================================================
   FETCH ALL TRADES
====================================================== */
if ($action === 'fetch_all_trades') {

  $res = $mysqli->query("
    SELECT id, trade_name
    FROM customers_trades
    WHERE status = 1
    ORDER BY trade_name
  ");

  $rows = [];
  while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
  }

  echo json_encode($rows);
  exit;
}

/* ======================================================
   ADD NEW TRADE
====================================================== */
if ($action === 'add_trade') {

  $name = trim($_POST['trade_name'] ?? '');

  if ($name === '') {
    echo json_encode(['success'=>false,'error'=>'Trade name required']);
    exit;
  }

  // prevent duplicates
  $stmt = $mysqli->prepare("
    SELECT id FROM customers_trades WHERE trade_name = ?
  ");
  $stmt->bind_param("s", $name);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows) {
    echo json_encode(['success'=>false,'error'=>'Trade already exists']);
    exit;
  }

  $stmt = $mysqli->prepare("
    INSERT INTO customers_trades (trade_name)
    VALUES (?)
  ");
  $stmt->bind_param("s", $name);
  $stmt->execute();
  $trade_id = $mysqli->insert_id;

  $site_id = (int)($_POST['site_id'] ?? 0);
  if ($site_id) {
    $stmt = $mysqli->prepare("
      INSERT IGNORE INTO customer_site_trades (site_id, trade_id)
      VALUES (?,?)
    ");
    $stmt->bind_param("ii", $site_id, $trade_id);
    $stmt->execute();
  }

  echo json_encode([
    'success'=>true,
    'trade'=>[
      'id'=>$trade_id,
      'trade_name'=>$name
    ]
  ]);
  exit;
}

/* ======================================================
   FETCH EXISTING RATES FOR SITE
====================================================== */
if ($action === 'fetch_rates') {

  $customer_id = (int)$_POST['customer_id'];
  $site_id     = (int)$_POST['site_id'];

  $stmt = $mysqli->prepare("
    SELECT trade_id, rate_per_hour, is_fixed_rate, allow_overtime, not_rate, hot_rate, phot_rate, default_hours, food_allowance, travel_allowance, accommodation_allowance
    FROM customer_trade_rates
    WHERE customer_id=? AND site_id=?
  ");
  $stmt->bind_param("ii", $customer_id, $site_id);
  $stmt->execute();
  $res = $stmt->get_result();

  $rates = [];
  while ($r = $res->fetch_assoc()) {
    $rates[$r['trade_id']] = $r;
  }

  echo json_encode($rates);
  exit;
}

/* ======================================================
   SAVE / UPDATE RATES
====================================================== */
if ($action === 'save') {

  $customer_id = (int)$_POST['customer_id'];
  $site_id     = (int)$_POST['site_id'];
  $trades      = $_POST['trades'] ?? [];

  if (!is_array($trades) || !count($trades)) {
    echo json_encode(['success'=>false,'error'=>'No trades selected']);
    exit;
  }

  $rate_hour = $_POST['rate_hour'] ?: null;
  $not_rate  = $_POST['not_rate']  ?: null;
  $hot_rate  = $_POST['hot_rate']  ?: null;
  $phot_rate  = $_POST['phot_rate']  ?: null;
  $default_hours  = $_POST['default_hours']  ?: "8";
  $allow_overtime = $_POST['allow_overtime'] ?? "1";
  $is_fixed_rate = $_POST['is_fixed_rate'] ?? "1";
  $food_allowance = $_POST['food_allowance'] ?? 0;
  $travel_allowance = $_POST['travel_allowance'] ?? 0;
  $accommodation_allowance = $_POST['accommodation_allowance'] ?? 0;

  // prepare rate upsert ONCE
  $rateStmt = $mysqli->prepare("
    INSERT INTO customer_trade_rates
      (customer_id, site_id, trade_id, rate_per_hour, is_fixed_rate, allow_overtime, not_rate, hot_rate, phot_rate, default_hours, food_allowance, travel_allowance, accommodation_allowance)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
      rate_per_hour = VALUES(rate_per_hour),
      is_fixed_rate      = VALUES(is_fixed_rate),
      allow_overtime      = VALUES(allow_overtime),
      not_rate      = VALUES(not_rate),
      hot_rate      = VALUES(hot_rate),
      phot_rate      = VALUES(phot_rate),
      default_hours      = VALUES(default_hours),
      food_allowance      = VALUES(food_allowance),
      travel_allowance      = VALUES(travel_allowance),
      accommodation_allowance      = VALUES(accommodation_allowance)
  ");

  // prepare site ↔ trade link ONCE
  $linkStmt = $mysqli->prepare("
    INSERT IGNORE INTO customer_site_trades (site_id, trade_id)
    VALUES (?, ?)
  ");

  foreach ($trades as $tid) {

    $tid = (int)$tid;

    // save / update rate
    $rateStmt->bind_param(
      "iiidssddddsss",
      $customer_id,
      $site_id,
      $tid,
      $rate_hour,
      $is_fixed_rate,
      $allow_overtime,
      $not_rate,
      $hot_rate,
      $phot_rate,
      $default_hours,
      $food_allowance,
      $travel_allowance,
      $accommodation_allowance
    );
    $rateStmt->execute();

    // ensure trade linked to site
    $linkStmt->bind_param("ii", $site_id, $tid);
    $linkStmt->execute();
  }

  echo json_encode(['success'=>true]);
  exit;
}

/* ======================================================
   FETCH ALL CUSTOMERS → SITES → TRADES → RATES
====================================================== */
if ($action === 'fetch_all_rates') {

  $sql = "
    SELECT
      c.id            AS customer_id,
      c.company       AS customer_name,

      s.id            AS site_id,
      s.site_name,

      t.id            AS trade_id,
      t.trade_name,

      r.rate_per_hour,
      r.is_fixed_rate,
      r.allow_overtime,
      r.default_hours,
      r.not_rate,
      r.hot_rate,
      r.phot_rate, r.food_allowance, r.travel_allowance, r.accommodation_allowance

    FROM customers c

    INNER JOIN customers_sites s
      ON s.customer_id = c.id

    INNER JOIN customer_trade_rates r
      ON r.customer_id = c.id
     AND r.site_id = s.id

    INNER JOIN customers_trades t
      ON t.id = r.trade_id

    ORDER BY c.company, s.site_name, t.trade_name
  ";

  $res = $mysqli->query($sql);

  $out = [];

  while ($row = $res->fetch_assoc()) {

    $cid = $row['customer_id'];
    $sid = $row['site_id'];

    // customer node
    if (!isset($out[$cid])) {
      $out[$cid] = [
        'customer_id'   => $cid,
        'customer_name' => $row['customer_name'],
        'sites'         => []
      ];
    }

    // site node
    if (!isset($out[$cid]['sites'][$sid])) {
      $out[$cid]['sites'][$sid] = [
        'site_id'   => $sid,
        'site_name' => $row['site_name'],
        'trades'    => []
      ];
    }

    // trade + rate
    $out[$cid]['sites'][$sid]['trades'][] = [
      'trade_id'        => $row['trade_id'],
      'trade_name'      => $row['trade_name'],
      'rate_per_hour'   => $row['rate_per_hour'],
      'is_fixed_rate'   => $row['is_fixed_rate'],
      'allow_overtime'  => $row['allow_overtime'],
      'default_hours'   => $row['default_hours'],
      'not_rate'        => $row['not_rate'],
      'hot_rate'        => $row['hot_rate'],
      'phot_rate'       => $row['phot_rate'],
      'food_allowance'       => $row['food_allowance'],
      'travel_allowance'       => $row['travel_allowance'],
      'accommodation_allowance'       => $row['accommodation_allowance']
    ];
  }

  // normalize numeric keys → arrays
  $final = [];
  foreach ($out as $cust) {
    $cust['sites'] = array_values($cust['sites']);
    $final[] = $cust;
  }

  echo json_encode($final);
  exit;
}

echo json_encode(['success'=>false,'error'=>'Invalid action']);
?>