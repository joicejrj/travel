<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header("Content-Type: application/json");

$supplier_id = intval($_POST['supplier_id'] ?? 0);
$offset = intval($_POST['offset'] ?? 0);
$limit  = intval($_POST['limit'] ?? 10);

$agent_id = intval($_SESSION['person_id'] ?? 0); // logged-in agent
$data = [];

if ($supplier_id > 0) {
    /**
     * ✅ Fetch logic:
     * - Show all "Public" notes for this customer
     * - Show "Private" notes only if created_by matches logged-in agent
     * - Order newest first, support pagination
     */
    $query = "
        SELECT 
            id, 
            name, 
            notes, 
            type, 
            visibility,
            agent_id,
            DATE_FORMAT(created_at, '%d %b %Y %h:%i %p') as created_at
        FROM customers_logs 
        WHERE customer_id = ?
          AND (
                visibility = 'Public' 
                OR (visibility = 'Private' AND agent_id = ?)
              )
        ORDER BY id DESC 
        LIMIT ?, ?
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("iiii", $supplier_id, $agent_id, $offset, $limit);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
}

// Return JSON response
echo json_encode(['logs' => $data]);
?>