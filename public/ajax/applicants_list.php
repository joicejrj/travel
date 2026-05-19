<?php
// public/ajax/applicants_list.php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

/* -------------------------
   DataTables params
-------------------------- */
$draw   = (int)($_POST['draw'] ?? 1);
$start  = (int)($_POST['start'] ?? 0);
$length = (int)($_POST['length'] ?? 10);
$orderCol = (int)($_POST['order'][0]['column'] ?? 7);
$orderDir = ($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

/* -------------------------
   Filters
-------------------------- */
$dateRange  = trim($_POST['date_range'] ?? '');
$searchText = trim($_POST['search_text'] ?? '');
$status     = trim($_POST['status'] ?? '');
$source     = trim($_POST['source'] ?? '');

/* -------------------------
   WHERE + bindings
-------------------------- */
$where  = [];
$bind   = [];
$types  = '';

if ($searchText !== '') {
    $where[] = "(ref_no LIKE ? OR full_name LIKE ? OR mobile LIKE ?)";
    $bind[]  = "%$searchText%";
    $bind[]  = "%$searchText%";
    $bind[]  = "%$searchText%";
    $types  .= 'sss';
}

if ($status !== '') {
    $where[] = "status = ?";
    $bind[]  = $status;
    $types  .= 's';
}

if ($source !== '') {
    $where[] = "lead_source = ?";
    $bind[]  = $source;
    $types  .= 's';
}

if ($dateRange && strpos($dateRange, ' - ') !== false) {
    [$d1, $d2] = explode(' - ', $dateRange);
    $from = DateTime::createFromFormat('d-m-Y', trim($d1));
    $to   = DateTime::createFromFormat('d-m-Y', trim($d2));

    if ($from && $to) {
        $where[] = "created_at BETWEEN ? AND ?";
        $bind[]  = $from->format('Y-m-d 00:00:00');
        $bind[]  = $to->format('Y-m-d 23:59:59');
        $types  .= 'ss';
    }
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* -------------------------
   Column map
-------------------------- */
$columns = [
  'ref_no',
  'full_name',
  'mobile',
  'position_category',
  'current_location',
  'status',
  'lead_source',
  'created_at'
];
$orderBy = $columns[$orderCol] ?? 'created_at';

/* -------------------------
   Total records
-------------------------- */
$res = $mysqli->query("SELECT COUNT(*) FROM applicants");
$recordsTotal = (int)$res->fetch_row()[0];

/* -------------------------
   Filtered records
-------------------------- */
$sql = "SELECT COUNT(*) FROM applicants $whereSql";
$stmt = $mysqli->prepare($sql);

if ($bind) {
    $stmt->bind_param($types, ...$bind);
}

$stmt->execute();
$recordsFiltered = (int)$stmt->get_result()->fetch_row()[0];
$stmt->close();

/* -------------------------
   Main data query
-------------------------- */
$sql = "
SELECT
  id,
  ref_no,
  full_name,
  mobile,
  position_category,
  other_position,
  current_location,
  city,
  status,
  lead_source,
  created_at
FROM applicants
$whereSql
ORDER BY $orderBy $orderDir
LIMIT ?, ?
";

$stmt = $mysqli->prepare($sql);

// add LIMIT bindings
$bind[]  = $start;
$bind[]  = $length;
$types  .= 'ii';

$stmt->bind_param($types, ...$bind);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* -------------------------
   Format rows
-------------------------- */
$data = [];

foreach ($rows as $r) {

  $position = ($r['position_category'] === 'Other')
    ? ($r['other_position'] ?: 'Other')
    : $r['position_category'];

  $data[] = [
    'ref_no' => '<span class="badge bg-light text-dark">'.htmlspecialchars($r['ref_no']).'</span>',
    'full_name' => htmlspecialchars($r['full_name']),
    'mobile' => htmlspecialchars($r['mobile']),
    'position' => htmlspecialchars($position),
    'location' => htmlspecialchars($r['current_location'].' - '.$r['city']),
    'status' => '<span class="badge bg-info">'.htmlspecialchars($r['status']).'</span>',
    'source' => htmlspecialchars($r['lead_source']),
    'created_at' => date('d M Y, H:i', strtotime($r['created_at'])),
    'actions' => '
      <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-primary"
          onclick="viewApplicant('.(int)$r['id'].')">View</button>
        <button class="btn btn-outline-secondary"
          onclick="openTimeline('.(int)$r['id'].')">Timeline</button>
      </div>'
  ];
}

/* -------------------------
   Output
-------------------------- */
echo json_encode([
  'draw' => $draw,
  'recordsTotal' => $recordsTotal,
  'recordsFiltered' => $recordsFiltered,
  'data' => $data
]);
?>