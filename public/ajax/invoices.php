<?php
// public/ajax/invoices.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();
header('Content-Type: application/json');

// Inputs (DataTables)
$start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = $_POST['search']['value'] ?? "";
$range  = $_POST['daterange'] ?? "";
$filterType = $_POST['filterType'] ?? "all";
$orderColIndex = $_POST['order'][0]['column'] ?? null;
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
if ($orderDir !== 'asc') $orderDir = 'desc';

// parse daterange (expected "DD-MM-YYYY - DD-MM-YYYY")
$dates = explode(" - ", $range);
if (count($dates) !== 2) {
    $from = date('Y-m-d');
    $to   = date('Y-m-d');
} else {
    $d1 = DateTime::createFromFormat("d-m-Y", trim($dates[0]));
    $d2 = DateTime::createFromFormat("d-m-Y", trim($dates[1]));
    if (!$d1) $d1 = new DateTime();
    if (!$d2) $d2 = new DateTime();
    $from = $d1->format("Y-m-d");
    $to   = $d2->format("Y-m-d");
}

// helper to escape
$fromEsc = $mysqli->real_escape_string($from);
$toEsc   = $mysqli->real_escape_string($to);

// $sqlRec = "
//     SELECT
//       'recruiter' AS source_type,
//       i.id,
//       r.name,
//       i.category,
//       i.type,
//       i.invoice_amount AS amount,
//       i.invoice_date AS date,
//       i.due_date,
//       i.notes,
//       i.document
//     FROM recruiters_invoices i
//     LEFT JOIN recruiters r ON r.id = i.recruiter_id
//     WHERE i.invoice_date BETWEEN '{$fromEsc}' AND '{$toEsc}'
// ";

$sqlCust = "
    SELECT
      'customer' AS source_type,
      i.id,
      c.name,
      i.category,
      i.type,
      i.invoice_amount AS amount,
      i.invoice_date AS date,
      i.due_date,
      i.notes,
      i.document
    FROM customers_invoices i
    LEFT JOIN customers c ON c.id = i.customer_id
    WHERE i.invoice_date BETWEEN '{$fromEsc}' AND '{$toEsc}'
";

// union based on filterType
if ($filterType === "employees") {
    $baseUnion = $sqlEmp;
} elseif ($filterType === "recruiters") {
    $baseUnion = $sqlRec;
} elseif ($filterType === "customers") {
    $baseUnion = $sqlCust;
} else {
    // $baseUnion = "$sqlEmp UNION ALL $sqlRec UNION ALL $sqlCust";
    $baseUnion = "$sqlCust";
}

// records total (within date range)
$countAllQ = "SELECT COUNT(*) AS cnt FROM ({$baseUnion}) AS t_all";
$countAllRes = $mysqli->query($countAllQ);
$totalRecords = $countAllRes ? intval($countAllRes->fetch_assoc()['cnt'] ?? 0) : 0;

// search filtering
$finalSource = $baseUnion;
if ($search !== "") {
    $s = $mysqli->real_escape_string($search);
    $finalSource = "
        SELECT * FROM ({$baseUnion}) AS X
        WHERE X.name LIKE '%{$s}%' OR X.category LIKE '%{$s}%' OR X.notes LIKE '%{$s}%'
    ";
    $countFilteredQ = "SELECT COUNT(*) AS cnt FROM ({$finalSource}) AS t_filt";
    $countFilteredRes = $mysqli->query($countFilteredQ);
    $filteredRecords = $countFilteredRes ? intval($countFilteredRes->fetch_assoc()['cnt'] ?? 0) : 0;
} else {
    $filteredRecords = $totalRecords;
}

// order map: DataTable columns -> DB field
$columnMap = [
    0 => 'id',
    1 => 'source_type',
    2 => 'name',
    3 => 'category',
    4 => 'type',
    5 => 'amount',
    6 => 'date',
    7 => 'due_date',
    8 => 'notes'
];
$orderBy = "date DESC";
if ($orderColIndex !== null && isset($columnMap[intval($orderColIndex)])) {
    $col = $columnMap[intval($orderColIndex)];
    $orderBy = "{$col} {$orderDir}";
}

// limit
$limit = "LIMIT {$start}, {$length}";

// final query
$finalSql = "SELECT * FROM ({$finalSource}) AS result_table ORDER BY {$orderBy} {$limit}";
$res = $mysqli->query($finalSql);

$data = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        // convert document path (single filename)
        if (!empty($row['document'])) {
            $row['document'] = "uploads/".($row['source_type'] === 'employee' ? 'employees' : ($row['source_type']==='recruiter'?'recruiters':'customers'))."/invoices/" . $row['document'];
        }
        // normalize
        $row['pid'] = $row['id'];
        // friendly id: prefix + numeric (you used +1000 earlier, replicate)
        $row['id'] = ucfirst(substr($row['source_type'], 0, 1)) . ($row['id'] + 1000);
        $row['source_typed'] = ucfirst($row['source_type']);
        $data[] = $row;
    }
}

// summary totals (use the filtered finalSource so summary respects search if present)
$summaryQ = "
    SELECT 
        COUNT(*) AS totalCount,
        COALESCE(SUM(CASE WHEN type = 'Received' THEN amount ELSE 0 END),0) AS totalReceived,
        COALESCE(SUM(CASE WHEN type = 'Sent' THEN amount ELSE 0 END),0) AS totalSent
    FROM ({$finalSource}) AS summary_table
";
$summaryRes = $mysqli->query($summaryQ);
$summary = ['received' => 0, 'sent' => 0, 'total' => 0];
if ($summaryRes) {
    $sr = $summaryRes->fetch_assoc();
    $summary = [
        'received' => floatval($sr['totalReceived']),
        'sent'     => floatval($sr['totalSent']),
        'total'    => intval($sr['totalCount'])
    ];
}

echo json_encode([
    "draw" => intval($_POST['draw'] ?? 0),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $filteredRecords,
    "data" => $data,
    "summary" => $summary
]);
?>