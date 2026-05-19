<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$cid    = intval($_POST['customer_id'] ?? 0);

if ($action === 'fetch' && $cid > 0) {

  $res = $mysqli->query("
    SELECT distinct t.id, t.trade_name
    FROM customer_trade_rates as ct left join customers_trades as t on t.id=ct.trade_id
    WHERE ct.customer_id = $cid
    ORDER BY t.trade_name ASC
  ");

  $out = [];
  while ($r = $res->fetch_assoc()) {
    $out[] = $r;
  }

  echo json_encode($out);
  exit;
}

echo json_encode([]);
?>