<?php
require_once __DIR__.'/../../config/db.php';
require_once __DIR__.'/../../public/_auth.php';

header('Content-Type: application/json');

// $customer_id = (int)($_GET['customer_id'] ?? 0);

$stmt = $mysqli->prepare("
    SELECT
        MAX(id) as id,
        origin,
        destination,
        departure_date,
        return_date,
        adults,
        travel_class,
        non_stop,
        MAX(created_at) as created_at
    FROM flight_search_history
    GROUP BY origin, destination
    ORDER BY created_at DESC
    LIMIT 5
");

// $stmt->bind_param("i",$customer_id);
$stmt->execute();

$res = $stmt->get_result();

$data = [];

while($row = $res->fetch_assoc()){
    $data[] = $row;
}

echo json_encode([
    "success"=>true,
    "data"=>$data
]);
?>