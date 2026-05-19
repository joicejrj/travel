<?php
// public/ajax/notifications.php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../_auth.php';

ob_clean();
header('Content-Type: application/json; charset=utf-8');

$LIMIT = 10;
$lookahead_days = 7;

/* ============================================================
   REMINDERS (CUSTOMERS / EMPLOYEES / RECRUITERS / CONTACTS / SUPPLIERS)
   ============================================================ */
$reminders = [];

$remQ = "
(
  SELECT r.id, r.reminder_at, r.note, c.name AS user_name, 'customers' AS utype, c.id AS uid, r.updated_at
  FROM customers_reminders r
  LEFT JOIN customers c ON c.id = r.customer_id
  WHERE r.completed = 0
)
UNION ALL
(
  SELECT r.id, r.reminder_at, r.note, ct.name AS user_name, 'contacts' AS utype, ct.id AS uid, r.updated_at
  FROM contacts_reminders r
  LEFT JOIN contacts ct ON ct.id = r.contact_id
  WHERE r.completed = 0
)
UNION ALL
(
  SELECT r.id, r.reminder_at, r.note, s.name AS user_name, 'suppliers' AS utype, s.id AS uid, r.updated_at
  FROM suppliers_reminders r
  LEFT JOIN suppliers s ON s.id = r.supplier_id
  WHERE r.completed = 0
)
ORDER BY reminder_at ASC
LIMIT ?
";

$stmt = $mysqli->prepare($remQ);
$stmt->bind_param('i', $LIMIT);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $reminders[] = [
        'title' => $row['user_name'],
        'note'  => $row['note'],
        'due'   => date('d M, h:i A', strtotime($row['reminder_at'])),
        'utype' => $row['utype'],
        'url'   => '?page=' . $row['utype'] . '_view&id=' . $row['uid'],
        'updated_at' => strtotime($row['updated_at'] ?? $row['reminder_at']),
    ];
}
$stmt->close();

/* ============================================================
   2. JOB REQUIREMENTS EXPIRY
   ============================================================ */
$requirements = [];

$today  = date('Y-m-d');
$future = date('Y-m-d', strtotime("+{$lookahead_days} days"));

$reqQ = "
  SELECT
    cr.id,
    cr.job_title,
    cr.expiry,
    c.name AS customer_name,
    cr.customer_id
  FROM customers_requirements cr
  LEFT JOIN customers c ON c.id = cr.customer_id
  WHERE cr.req_type = 'Enquiry'
    AND cr.expiry IS NOT NULL
    AND cr.expiry_alert = '1'
    AND cr.expiry BETWEEN ? AND ?
  ORDER BY cr.expiry ASC
  LIMIT ?
";

$stmt = $mysqli->prepare($reqQ);
$stmt->bind_param('ssi', $today, $future, $LIMIT);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $requirements[] = [
        'id'       => $row['id'],
        'title'    => $row['job_title'],
        'company'  => $row['customer_name'],
        'expiry'   => strtotime($row['expiry']), // IMPORTANT: timestamp
        'expiry_text' => 'Expires on ' . date('d M', strtotime($row['expiry'])),
        'url'      => '?page=customers_view&id=' . $row['customer_id']
    ];
}
$stmt->close();


/* ============================================================
   bookings ASSIGNED
   ============================================================ */
$bookings = [];

$today  = date('Y-m-d');
$past = date('Y-m-d', strtotime("-2 days"));

$uid = $_SESSION['person_id'] ?? 0;

$q = $mysqli->prepare("
    SELECT i.id,
           i.date,
           i.time,
           i.status,
           i.contact_name,
           c.name AS channel_name, i.nature, sc.name as type
    FROM bookings i
    LEFT JOIN channels c ON c.id = i.channel_id
    LEFT JOIN bookings_types sc ON i.type_id = sc.id
    WHERE i.assigned_to = ?
      AND i.status='New'
      AND i.date BETWEEN ? AND ?
    ORDER BY i.date DESC, i.time DESC
");

$q->bind_param('iss', $uid, $past, $today);
$q->execute();
$res = $q->get_result();

$cnt_bookings = $res->num_rows;

while ($row = $res->fetch_assoc()) {
    $row['dated'] = $row['date']==$date?'Today':($row['date']==date("Y-m-d",strtotime("-1 day"))?'Yesterday':date("d M Y",strtotime($row['date'])));
    $row['timed'] = date("h:i A",strtotime($row['time']));
    $bookings[] = $row;
}

/* ============================================================
   DOCUMENT EXPIRY (ALL ENTITIES) — FIXED
   ============================================================ */
$documents = [];

$today  = date('Y-m-d');
$future = date('Y-m-d', strtotime('+30 days'));

$docQ = "
SELECT *
FROM (
  SELECT d.id, d.label, d.expiry_date, c.name AS user_name, c.id AS uid, 'customers' AS utype
  FROM customers_documents d
  LEFT JOIN customers c ON c.id = d.customer_id

  UNION ALL

  SELECT d.id, d.label, d.expiry_date, s.name AS user_name, s.id AS uid, 'suppliers' AS utype
  FROM suppliers_documents d
  LEFT JOIN suppliers s ON s.id = d.supplier_id
) AS docs
WHERE docs.expiry_date IS NOT NULL
  AND docs.expiry_date BETWEEN ? AND ?
ORDER BY docs.expiry_date ASC
LIMIT ?
";

$stmt = $mysqli->prepare($docQ);
$stmt->bind_param('ssi', $today, $future, $LIMIT);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $documents[] = [
        'id'       => $row['id'],
        'title'    => $row['label'],
        'name'     => $row['user_name'],
        'expiry'   => strtotime($row['expiry_date']), // timestamp
        'expiry_text' => 'Expires on ' . date('d M', strtotime($row['expiry_date'])),
        'utype'    => $row['utype'],
        'url'      => '?page=' . $row['utype'] . '_view&id=' . $row['uid']
    ];
}
$stmt->close();

$cnt_reminders = $mysqli->query("
  SELECT
    (
      SELECT COUNT(*) FROM customers_reminders  WHERE completed = 0
    ) +
    (
      SELECT COUNT(*) FROM contacts_reminders   WHERE completed = 0
    ) +
    (
      SELECT COUNT(*) FROM suppliers_reminders  WHERE completed = 0
    ) AS total
")->fetch_assoc()['total'];

$cnt_requirements = $mysqli->query("
  SELECT COUNT(*)
  FROM customers_requirements
  WHERE req_type = 'Enquiry'
    AND expiry IS NOT NULL
    AND expiry_alert = '1'
    AND expiry BETWEEN '{$today}' AND '{$future}'
")->fetch_assoc()['COUNT(*)'];

$cnt_documents = $mysqli->query("
SELECT
  (
    SELECT COUNT(*) FROM customers_documents
    WHERE expiry_date BETWEEN '{$today}' AND '{$future}'
  ) +
  (
    SELECT COUNT(*) FROM suppliers_documents
    WHERE expiry_date BETWEEN '{$today}' AND '{$future}'
  ) AS total
")->fetch_assoc()['total'];

/* ============================================================
   FINAL RESPONSE
   ============================================================ */
echo json_encode([
    'success' => true,
    'reminders'     => $reminders,
    'requirements'  => $requirements,
    'bookings'  => $bookings,
    'documents'     => $documents,
    'counts' => [
        'reminders'    => (int)$cnt_reminders,
        'requirements' => (int)$cnt_requirements,
        'bookings' => (int)$cnt_bookings,
        'documents'    => (int)$cnt_documents
    ],
    // 'counts' => [
    //     'reminders'    => count($reminders),
    //     'requirements' => count($requirements),
    //     'documents'    => count($documents)
    // ]
]);
exit;
?>