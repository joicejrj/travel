<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

/* -------------------------
      INPUTS (safe casting)
---------------------------*/
$start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = $_POST['search']['value'] ?? "";
$range  = $_POST['daterange'] ?? "";
$filterType = $_POST['filterType'] ?? "all";

/* ORDER (DataTables) */
$orderColIndex = $_POST['order'][0]['column'] ?? null;
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
if ($orderDir !== 'asc') $orderDir = 'desc'; // default to desc

/* -------------------------
      DATE RANGE parsing
---------------------------*/
$dates = explode(" - ", $range);
if (count($dates) !== 2) {
    // fallback to today if not provided
    $from = date('Y-m-d');
    $to = date('Y-m-d');
} else {
    $d1 = DateTime::createFromFormat("d-m-Y", trim($dates[0]));
    $d2 = DateTime::createFromFormat("d-m-Y", trim($dates[1]));
    if (!$d1) $d1 = new DateTime();
    if (!$d2) $d2 = new DateTime();
    $from = $d1->format("Y-m-d");
    $to   = $d2->format("Y-m-d");
}

/* -------------------------
      BASE SUBQUERIES (with date filters)
---------------------------*/

// $sql2 = "
//     SELECT 
//         'recruiter' AS source_type,
//         rp.id,
//         r.name,
//         rp.category,
//         rp.type,
//         rp.invoice_amount AS amount,
//         rp.payment_status AS status,
//         rp.invoice_date AS date,
//         rp.notes,
//         rp.document AS document
//     FROM recruiters_payments rp
//     LEFT JOIN recruiters r ON r.id = rp.recruiter_id
//     WHERE rp.invoice_date BETWEEN '{$mysqli->real_escape_string($from)}' AND '{$mysqli->real_escape_string($to)}'
// ";

$sql3 = "
    SELECT 
        'customer' AS source_type,
        cp.id,
        c.name,
        cp.category,
        cp.type,
        cp.invoice_amount AS amount,
        cp.payment_status AS status,
        cp.invoice_date AS date,
        cp.notes,
        cp.document AS document
    FROM customers_payments cp
    LEFT JOIN customers c ON c.id = cp.customer_id
    WHERE cp.invoice_date BETWEEN '{$mysqli->real_escape_string($from)}' AND '{$mysqli->real_escape_string($to)}'
";

/* -------------------------
      Choose union by filterType
---------------------------*/
if ($filterType === "employees") {
    $baseUnion = $sql1;
} elseif ($filterType === "recruiters") {
    $baseUnion = $sql2;
} elseif ($filterType === "customers") {
    $baseUnion = $sql3;
} else {
    // $baseUnion = "$sql1 UNION ALL $sql2 UNION ALL $sql3";
    $baseUnion = "$sql3";
}

/* -------------------------
      TOTALS (before/after search)
      recordsTotal = count without search (but within date range)
      recordsFiltered = count with search (if applied)
---------------------------*/
// total without search (within date range)
$countAllQ = "SELECT COUNT(*) AS cnt FROM ({$baseUnion}) AS t_all";
$countAllRes = $mysqli->query($countAllQ);
$totalRecords = $countAllRes ? intval($countAllRes->fetch_assoc()['cnt'] ?? 0) : 0;

/* -------------------------
      SEARCH FILTER (if any)
---------------------------*/
$finalSource = $baseUnion;
if ($search !== "") {
    $searchEsc = $mysqli->real_escape_string($search);
    $finalSource = "
        SELECT * FROM ({$baseUnion}) AS X
        WHERE 
            X.name LIKE '%{$searchEsc}%' OR
            X.category LIKE '%{$searchEsc}%' OR
            X.notes LIKE '%{$searchEsc}%'
    ";
    // count after search
    $countFilteredQ = "SELECT COUNT(*) AS cnt FROM ({$finalSource}) AS t_filt";
    $countFilteredRes = $mysqli->query($countFilteredQ);
    $filteredRecords = $countFilteredRes ? intval($countFilteredRes->fetch_assoc()['cnt'] ?? 0) : 0;
} else {
    $filteredRecords = $totalRecords;
}

/* -------------------------
      ORDER BY mapping (safe)
---------------------------*/
/*
 DataTables column indexes expected in your table:
 0 => id
 1 => source_type
 2 => name
 3 => category
 4 => amount
 5 => status
 6 => date
 7 => notes
 8 => document (not sortable)
 9 => action (not sortable)

 Only map sortable columns to allowed DB fields.
*/
$columnMap = [
    0 => 'id',
    1 => 'source_type',
    2 => 'name',
    3 => 'category',
    4 => 'amount',
    5 => 'status',
    6 => 'date',
    7 => 'notes'
];

$orderBy = "date DESC"; // default
if ($orderColIndex !== null && isset($columnMap[intval($orderColIndex)])) {
    $col = $columnMap[intval($orderColIndex)];
    // prevent SQL injection by allowing only mapped columns and dir checked above
    $orderBy = "{$col} {$orderDir}";
}

/* -------------------------
      FINAL QUERY with ORDER + LIMIT
---------------------------*/
$limit = "LIMIT {$start}, {$length}";

$finalSql = "SELECT * FROM ({$finalSource}) AS result_table ORDER BY {$orderBy} {$limit}";

$res = $mysqli->query($finalSql);

$data = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        if (!empty($row['document'])) {
            // if document stored relative filename, adjust path as needed
            $row['document'] = "uploads/payments/" . $row['document'];
        }
        // Normalize keys if needed, e.g., ensure date appears as readable string
        $row['pid'] = $row['id'];
        $row['id'] = ucfirst(substr($row['source_type'], 0, 1)).$row['id']+1000;
        $row['source_typed'] = ucfirst($row['source_type']);

        $data[] = $row;
    }
}

/* -------------------------
      SUMMARY TOTALS
---------------------------*/
$summaryQ = "
    SELECT 
        SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) AS totalIncome,
        SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) AS totalExpense,

        SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) AS paidAmount,
        SUM(CASE WHEN status = 'Unpaid' THEN amount ELSE 0 END) AS unpaidAmount,
        SUM(CASE WHEN status = 'Partial Paid' THEN amount ELSE 0 END) AS partialAmount
    FROM ({$finalSource}) AS summary_table
";

$summaryRes = $mysqli->query($summaryQ);
$summary = [
    "totalIncome"   => 0,
    "totalExpense"  => 0,
    "paidAmount"    => 0,
    "unpaidAmount"  => 0,
    "partialAmount" => 0
];

if ($summaryRes) {
    $summaryRow = $summaryRes->fetch_assoc();
    $summary = [
        "totalIncome"   => floatval($summaryRow['totalIncome']),
        "totalExpense"  => floatval($summaryRow['totalExpense']),
        "paidAmount"    => floatval($summaryRow['paidAmount']),
        "unpaidAmount"  => floatval($summaryRow['unpaidAmount']),
        "partialAmount" => floatval($summaryRow['partialAmount'])
    ];
}



/* -------------------------
      OUTPUT JSON for DataTables
---------------------------*/
echo json_encode([
    "draw" => intval($_POST['draw'] ?? 0),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $filteredRecords,
    "data" => $data,
    "summary" => $summary
]);