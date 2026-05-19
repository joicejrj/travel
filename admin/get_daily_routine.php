<?php
// agent/get_daily_routine.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php'; // make sure $pdo is created here
require_once __DIR__ . '/_auth.php'; // ensures user logged in

// available statuses - read from config table to preserve admin order
try {
    // get statuses and total_followups
    $stmt = $pdo->prepare("SELECT status, total_followups FROM daily_routine_config ORDER BY id ASC");
    $stmt->execute();
    $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // build list of statuses
    $statuses = array_map(function($r){ return $r['status']; }, $configs);
    $statusToTotal = [];
    foreach ($configs as $r) {
        $statusToTotal[$r['status']] = (int)$r['total_followups'];
    }

    // today's date boundaries (server timezone)
    $todayStart = (new DateTime('today'))->format('Y-m-d 00:00:00');
    $todayEnd   = (new DateTime('today'))->format('Y-m-d 23:59:59');

    // Logic:
    // Count distinct contacts that had either a reminder created today OR a note created today
    // grouped by contact.status. Each contact counted only once (DISTINCT contact id).
    //
    // Assumptions:
    // - contacts table -> `contacts` with fields id, status
    // - reminders table -> `reminders` with fields id, contact_id, created_at
    // - notes table -> `notes` with fields id, contact_id, created_at
    //
    // If your table names / date fields differ, adjust below.

    $sql = "
    SELECT c.status, COUNT(DISTINCT c.id) AS today_count
    FROM contacts c
    LEFT JOIN (
      SELECT DISTINCT contact_id FROM reminders WHERE created_at BETWEEN :s AND :e
      UNION
      SELECT DISTINCT contact_id FROM notes WHERE created_at BETWEEN :s AND :e
    ) t ON t.contact_id = c.id
    WHERE t.contact_id IS NOT NULL
    GROUP BY c.status
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':s' => $todayStart, ':e' => $todayEnd]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // map status => today_count
    $counts = [];
    foreach ($rows as $r) $counts[$r['status']] = (int)$r['today_count'];

    // build response with all configured statuses (if a status has zero, return 0)
    $items = [];
    foreach ($statuses as $s) {
        $items[] = [
            'status' => $s,
            'today_count' => $counts[$s] ?? 0,
            'total_followups' => $statusToTotal[$s] ?? 0
        ];
    }

    echo json_encode(['success' => true, 'items' => $items]);
    exit;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
