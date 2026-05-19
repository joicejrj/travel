<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();
header('Content-Type: application/json');

$contact_id = intval($_POST['contact_id'] ?? 0);
if (!$contact_id) {
  echo json_encode([
    "draw" => intval($_POST['draw'] ?? 1),
    "recordsTotal" => 0,
    "recordsFiltered" => 0,
    "data" => []
  ]);
  exit;
}


/* ============================================================
   ⭐ TIMELINE MODE (supports pagination + clean JSON)
   ============================================================ */
if ($type === 'timeline') {

    $start  = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 20);

    $sql = "
        SELECT l.*, p.name AS aname
        FROM people_logs AS l
        LEFT JOIN people AS p ON p.id = l.agent_id
        WHERE l.contact_id = ? and l.type!='notimeline'
        ORDER BY l.timestamp DESC
        LIMIT ?, ?
    ";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iii", $contact_id, $start, $length);
    $stmt->execute();

    $result = $stmt->get_result();
    $timeline = [];

    while ($row = $result->fetch_assoc()) {

        // Determine agent name
        $agentName = 'System';

        if (!empty($row['admin'])) {
            $adminData = json_decode($row['admin'], true);
            if (json_last_error() === JSON_ERROR_NONE && isset($adminData['name'])) {
                $role = ucfirst($adminData['role'] ?? '');
                $name = htmlspecialchars($adminData['name']);
                $agentName = ($role ? "$role - $name" : $name);
            }
        }
        elseif (!empty($row['agent_id'])) {
            $agentName = htmlspecialchars($row['aname']);
        }

        $timeline[] = [
            'date'   => date('d M Y h:i A', strtotime($row['timestamp'])),
            'action' => htmlspecialchars($row['log']),
            'by'     => $agentName
        ];
    }

    echo json_encode([
        "success" => true,
        "data"    => $timeline
    ]);
    exit;
}


// DataTables parameters
$draw   = intval($_POST['draw'] ?? 1);
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$orderColumnIndex = $_POST['order'][0]['column'] ?? 0;
$orderDir = ($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$searchValue = trim($_POST['search']['value'] ?? '');

// Map DataTable column index to DB column
$columns = ['l.id', 'p.name', 'l.log', 'l.agent_id', 'l.timestamp'];
$orderBy = $columns[$orderColumnIndex] ?? 'l.timestamp';

// Base query
$baseQuery = "
  FROM people_logs AS l
  LEFT JOIN people AS p ON p.id = l.agent_id
  WHERE l.contact_id = ?
";

// Add search filter if present
if ($searchValue !== '') {
  $baseQuery .= " AND (l.log LIKE ? OR p.name LIKE ? OR l.admin LIKE ?)";
}

// Count total
$countQuery = "SELECT COUNT(*) " . $baseQuery;
$countStmt = $mysqli->prepare($countQuery);

if ($searchValue !== '') {
  $likeSearch = "%$searchValue%";
  $countStmt->bind_param("isss", $contact_id, $likeSearch, $likeSearch, $likeSearch);
} else {
  $countStmt->bind_param("i", $contact_id);
}

$countStmt->execute();
$countStmt->bind_result($recordsTotal);
$countStmt->fetch();
$countStmt->close();

// Fetch paginated data
$dataQuery = "
  SELECT l.*, p.name AS aname
  $baseQuery
  ORDER BY $orderBy $orderDir
  LIMIT ?, ?
";
$dataStmt = $mysqli->prepare($dataQuery);

if ($searchValue !== '') {
  $dataStmt->bind_param("isssii", $contact_id, $likeSearch, $likeSearch, $likeSearch, $start, $length);
} else {
  $dataStmt->bind_param("iii", $contact_id, $start, $length);
}

$dataStmt->execute();
$result = $dataStmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
  // Extract & clean fields
  $agentName = 'System';
  if (!empty($row['admin'])) {
    $adminData = json_decode($row['admin'], true);
    if (json_last_error() === JSON_ERROR_NONE && isset($adminData['name'])) {
      $role = ucfirst($adminData['role'] ?? '');
      $name = htmlspecialchars($adminData['name']);
      $agentName = htmlspecialchars($row['aname']) . " [{$row['agent_id']}]<br>
        <span class='text-secondary small'>(" . ($role ? "$role - $name" : $name) . ")</span>";
    }
  } elseif (!empty($row['agent_id'])) {
    $agentName = htmlspecialchars($row['aname']) . " [{$row['agent_id']}]";
  }

  $log = htmlspecialchars($row['log'] ?? '');
  $date = date('d M Y h:i A', strtotime($row['timestamp']));

  $data[] = [
    'id' => $row['id'],
    'action' => $log,
    'details' => $log, // You can adjust to show separate "details" if needed
    'by' => $agentName,
    'date' => $date
  ];
}

$dataStmt->close();

// Return DataTables JSON
echo json_encode([
  "draw" => $draw,
  "recordsTotal" => $recordsTotal,
  "recordsFiltered" => $recordsTotal,
  "data" => $data
]);
?>