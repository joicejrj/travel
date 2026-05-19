<?php
// public/ajax/dashboard_reminders.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
// require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

// configuration: how many days ahead to consider "going to expire"
$lookahead_days = 7;

// --------------------
// Employees missing site/customer
// Modify column names if necessary: site_id, customer_id, active flag
// --------------------
$employees = [];
$empQ = "
  SELECT id, name, site_id, customer_id, email, phone
  FROM employees
  WHERE (site_id IS NULL OR TRIM(site_id) = '')
    AND (customer_id IS NULL OR TRIM(customer_id) = '')
    AND (type IS NOT NULL AND type = 'Active')
  LIMIT 200
";
$res = $mysqli->query($empQ);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $employees[] = $r;
    }
}

// --------------------
// Customer job requirements going to expire
// We consider: req_type='Enquiry' AND expiry between today and today + lookahead_days
// Table: customers_requirements (example) with fields: id, job_title, expiry, req_type, customer_id
// --------------------
$requirements = [];
$today = date('Y-m-d', strtotime("-{$lookahead_days} days"));
$future = date('Y-m-d', strtotime("+{$lookahead_days} days"));

$reqQ = "
  SELECT cr.id, cr.job_title, cr.expiry, cr.req_type, cr.customer_id, c.name as customer_name
  FROM customers_requirements cr
  LEFT JOIN customers c ON c.id = cr.customer_id
  WHERE cr.req_type = 'Enquiry'
    AND cr.expiry IS NOT NULL
    AND cr.expiry_alert='1'
    AND cr.expiry BETWEEN ? AND ?
  ORDER BY cr.expiry ASC
  LIMIT 200
";
$stmt = $mysqli->prepare($reqQ);
$stmt->bind_param('ss', $today, $future);
$stmt->execute();
$res2 = $stmt->get_result();
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        // also return computed expiry_plus_14 for frontend
        $row['expiry_plus_14'] = date('Y-m-d', strtotime($row['expiry'] . ' +14 days'));
        $requirements[] = $row;
    }
}
$stmt->close();

// --------------------
// Documents going to expire - update below code pending
// --------------------
$utype = isset($_POST['utype'])?$site->esc($_POST['utype']):'customers';
$documents = [];
$today = date('Y-m-d');
$future = date('Y-m-d', strtotime("+30 days"));

$doc_table = $utype=='suppliers'?'suppliers_documents':($utype=='customers'?'customers_documents':($utype=='recruiters'?'recruiters_documents':'employees_documents'));
$doc_col = rtrim($utype, "s");

$reqQ = "
  SELECT cr.id, cr.label, cr.expiry_date, cr.file_type, cr.file_name, cr.".$doc_col."_id as user_id, c.name as user_name
  FROM $doc_table cr
  LEFT JOIN $utype c ON c.id = cr.".$doc_col."_id
  WHERE cr.expiry_date IS NOT NULL
    AND cr.expiry_date BETWEEN ? AND ?
  ORDER BY cr.expiry_date ASC
  LIMIT 200
";

$stmt = $mysqli->prepare($reqQ);
$stmt->bind_param('ss', $today, $future);
$stmt->execute();
$res2 = $stmt->get_result();
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        // $row['expiry_plus_14'] = date('Y-m-d', strtotime($row['expiry_date']));
        $row['utype'] = $utype;
        $documents[] = $row;
    }
}
$stmt->close();

echo json_encode([
    'success' => true,
    'employees' => $employees,
    'requirements' => $requirements,
    'documents' => $documents,
    'counts' => [
        'employees' => count($employees),
        'requirements' => count($requirements),
        'documents' => count($documents)
    ]
]);
?>