<?php
session_start();

require_once __DIR__ . '/../../config/db.php';        // must provide $mysqli
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$range = $_GET['date_range'] ?? '';
$start = $end = null;


if ($range) {
    [$s, $e] = array_map('trim', explode(' - ', $range));
    $start = date('Y-m-d', strtotime($s));
    $end   = date('Y-m-d', strtotime($e));
}

$where = [];
$params = [];

if ($start && $end) {
    $where[] = "DATE(i.created_at) BETWEEN ? AND ?";
    $params[] = $start;
    $params[] = $end;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* =========================
   TOTAL + STATUS COUNTS
========================= */
$sql = "
SELECT
    COUNT(*) AS total,
    COUNT(CASE WHEN i.status = 'open' THEN 1 END)    AS open,
    COUNT(CASE WHEN i.status = 'working' THEN 1 END) AS working,
    COUNT(CASE WHEN i.status = 'closed' THEN 1 END)  AS closed
FROM interactions i
$whereSql
";

$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();


/* =========================
   COUNTS BY CHANNEL
========================= */
$channels = [];
$sql = "
SELECT 
    c.id,
    c.name,
    COUNT(i.id) AS cnt
FROM channels c
LEFT JOIN interactions i 
    ON i.channel_id = c.id
    " . ($whereSql ? str_replace('WHERE', 'AND', $whereSql) : '') . "
GROUP BY c.id, c.name
ORDER BY c.name
";
$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $channels[] = $r;
}

/* =========================
   COUNTS BY CONTACT TYPE
========================= */
$types = [];
$sql = "
SELECT 
    ct.id,
    ct.name,
    COUNT(i.id) AS cnt
FROM contact_types ct
LEFT JOIN interactions i 
    ON i.contact_type_id = ct.id
    " . ($whereSql ? str_replace('WHERE', 'AND', $whereSql) : '') . "
GROUP BY ct.id, ct.name
ORDER BY ct.name
";
$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $types[] = $r;
}

/* =========================
   COUNTS BY SCENARIO
========================= */
$scenarios = [];
$sql = "
SELECT 
    s.id,
    s.name,
    COUNT(i.id) AS cnt
FROM scenarios s
LEFT JOIN interactions i 
    ON i.scenario_id = s.id
    " . ($whereSql ? str_replace('WHERE', 'AND', $whereSql) : '') . "
GROUP BY s.id, s.name
ORDER BY s.name
";
$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $scenarios[] = $r;
}

echo json_encode([
    'success'  => true,
    'summary'  => $summary,
    'channels' => $channels,
    'types'    => $types,
    'scenarios' => $scenarios
]);
?>