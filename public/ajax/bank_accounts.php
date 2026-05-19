<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

$action = $_POST['action'] ?? '';

switch ($action) {

    /* ============================================================
         🟢 LIST ALL BANK ACCOUNTS
       ============================================================ */
    case 'list':

        $stmt = $mysqli->prepare("
            SELECT 
                id,
                bank_name,
                account_number,
                branch_name,
                branch_id
            FROM bank_accounts
            ORDER BY bank_name ASC
        ");

        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'error' => 'SQL Error: ' . $mysqli->error
            ]);
            exit;
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $banks = [];
        while ($row = $result->fetch_assoc()) {
            $banks[] = $row;
        }

        $stmt->close();

        echo json_encode([
            'success' => true,
            'data' => $banks
        ]);
        break;


    /* ============================================================
         ❌ INVALID ACTION
       ============================================================ */
    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
        break;
}

$mysqli->close();
?>