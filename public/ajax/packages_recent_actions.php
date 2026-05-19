<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';

ob_clean();
header('Content-Type: application/json');

$package_id = intval($_POST['package_id'] ?? 0);
$type       = $_POST['type'] ?? '';

if (!$package_id || $type !== 'timeline') {
  echo json_encode([
    'success' => false,
    'data' => []
  ]);
  exit;
}

$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 20);

$sql = "
  SELECT l.*, p.name AS aname
  FROM people_logs AS l
  LEFT JOIN people AS p ON p.id = l.agent_id
  WHERE l.package_id = ?
    AND l.type != 'notimeline'
  ORDER BY l.timestamp DESC
  LIMIT ?, ?
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("iii", $package_id, $start, $length);
$stmt->execute();
$result = $stmt->get_result();

$timeline = [];

while ($row = $result->fetch_assoc()) {

  $agentName = 'System';

  if (!empty($row['admin'])) {
    $admin = json_decode($row['admin'], true);
    if (json_last_error() === JSON_ERROR_NONE && isset($admin['name'])) {
      $role = ucfirst($admin['role'] ?? '');
      $name = htmlspecialchars($admin['name']);
      $agentName = $role ? "$role - $name" : $name;
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
  'success' => true,
  'data' => $timeline
]);
?>