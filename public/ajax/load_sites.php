<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$sites = [];

// customers_sites
$res1 = $mysqli->query("SELECT id, site_name FROM customers_sites where site_name!='Site 1' and site_name!='Site 2'");
if ($res1) {
    while ($r = $res1->fetch_assoc()) {
        $r['general'] = 0;
        $sites[] = $r;
    }
}

// general_sites
$res2 = $mysqli->query("SELECT id, site_name FROM general_sites");
if ($res2) {
    while ($r = $res2->fetch_assoc()) {
        $r['general'] = 1;
        $sites[] = $r;
    }
}

echo json_encode($sites);
?>