<?php
// public/ajax/ajax_list_customers.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000;
$limit = ($limit > 0 && $limit <= 5000) ? $limit : 1000;

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$items = [];

/* ---------------------------------
   Build query dynamically
---------------------------------- */
if ($q !== '') {
    $search = '%' . $q . '%';

    $sql = "
        SELECT id,
               COALESCE(name,'') AS name,
               COALESCE(email,'') AS email,
               COALESCE(phone,'') AS phone,
               COALESCE(dob,'') AS dob,
               COALESCE(gender,'') AS gender,
               COALESCE(company,'') AS company
        FROM customers
        WHERE name LIKE ?
           OR email LIKE ?
           OR phone LIKE ?
           OR company LIKE ?
        ORDER BY name
        LIMIT ?
    ";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'error'   => 'db_prepare_failed',
            'sql_error' => $mysqli->error
        ]);
        exit;
    }

    $stmt->bind_param('ssssi', $search, $search, $search, $search, $limit);

} else {

    $sql = "
        SELECT id,
               COALESCE(name,'') AS name,
               COALESCE(email,'') AS email,
               COALESCE(phone,'') AS phone,
               COALESCE(dob,'') AS dob,
               COALESCE(gender,'') AS gender,
               COALESCE(company,'') AS company
        FROM customers
        ORDER BY name
        LIMIT ?
    ";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'error'   => 'db_prepare_failed',
            'sql_error' => $mysqli->error
        ]);
        exit;
    }

    $stmt->bind_param('i', $limit);
}

/* ---------------------------------
   Execute & return result
---------------------------------- */
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}

$stmt->close();

echo json_encode([
    'success' => true,
    'items'   => $items
]);
exit;
?>