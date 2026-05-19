<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header("Content-Type: application/json");

$action = $_POST['action'] ?? '';
$agent_id = $_SESSION['person_id'] ?? 0;
$uploadDir = __DIR__ . '/../../uploads/customers/invoices/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

switch ($action) {

  case 'fetch':
    $customer_id = intval($_POST['customer_id'] ?? 0);
    // DATE RANGE FILTER
    $start_date = $_POST['start_date'] ?? '';
    $end_date   = $_POST['end_date'] ?? '';
    $where = "customer_id = $customer_id";
    // Validate dates and apply filter only if both are present
    if (!empty($start_date) && !empty($end_date)) {
        // Convert "05 Nov 2025" → "2025-11-05"
        $sd = date("Y-m-d", strtotime($start_date));
        $ed = date("Y-m-d", strtotime($end_date));
        $where .= " AND invoice_date BETWEEN '$sd' AND '$ed'";
    }
    // FINAL QUERY
    $res = $mysqli->query("SELECT * FROM customers_invoices WHERE $where ORDER BY id DESC");
    $data = [];
    while ($row = $res->fetch_assoc())  {
        // FILE TYPE
        $file_type = "";
        if ($row["document"] != '') {
            $ext = strtolower(pathinfo($row['document'], PATHINFO_EXTENSION));
            $file_type = ($ext == 'pdf') ? 'pdf' : 'image';
        }
        $row['file_type'] = $file_type;
        // FORMATTED DATES
        $row['invoice_dated'] = date("d M Y", strtotime($row['invoice_date']));
        $row['due_dated']     = $row['due_date'] != '' ? date("d M Y", strtotime($row['due_date'])) : '-';
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
    break;

  case 'save':

    $id           = intval($_POST['id'] ?? 0);
    $customer_id  = intval($_POST['customer_id']);
    $invoice_date = $_POST['invoice_date'] ?? date('Y-m-d');
    $due_date = $_POST['due_date'] ?? date('Y-m-d',strtotime("+7 days"));

    // NEW FIELDS
    $invoice_amount = floatval($_POST['invoice_amount'] ?? 0);
    $vat_amount = floatval($_POST['vat_amount'] ?? 0);
    $invoice_type   = trim($_POST['invoice_type'] ?? '');
    $category       = trim($_POST['invoice_category'] ?? '');

    $notes = trim($_POST['notes'] ?? '');

    /* ------------------------------
        FILE UPLOAD
    ------------------------------ */
    $fileName = '';
    if (!empty($_FILES['document']['name'])) {
        $ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('inv_') . '.' . $ext;
        move_uploaded_file($_FILES['document']['tmp_name'], $uploadDir . $fileName);
    }

    /* ------------------------------
        UPDATE EXISTING INVOICE
    ------------------------------ */
    if ($id > 0) {

        // Get the old document for deletion
        $old = $mysqli->query("SELECT document FROM customers_invoices WHERE id=$id")->fetch_assoc();

        // If a new file was uploaded → delete old
        if ($fileName && !empty($old['document']) && file_exists($uploadDir . $old['document'])) {
            unlink($uploadDir . $old['document']);
        }

        // If no new document, keep old
        $finalFile = $fileName ?: $old['document'];

        $stmt = $mysqli->prepare("
            UPDATE customers_invoices 
            SET invoice_date = ?, due_date = ?, 
                invoice_amount = ?, 
                vat_amount = ?, 
                type = ?, 
                category = ?, 
                notes = ?, 
                document = ? 
            WHERE id = ?
        ");

        $stmt->bind_param("ssddssssi",
            $invoice_date, $due_date,
            $invoice_amount,
            $vat_amount,
            $invoice_type,
            $category,
            $notes,
            $finalFile,
            $id
        );

    } else {

        /* ------------------------------
            ADD NEW INVOICE
        ------------------------------ */
        $stmt = $mysqli->prepare("
            INSERT INTO customers_invoices 
            (customer_id, invoice_date, due_date, invoice_amount, vat_amount, type, category, document, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("issddssssi",
            $customer_id,
            $invoice_date,
            $due_date,
            $invoice_amount,
            $vat_amount,
            $invoice_type,
            $category,
            $fileName,
            $notes,
            $agent_id
        );
    }

    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        $laction = ($id > 0) ? "Updated invoice [$id]" : "Created";
        $text = "$laction | Date: ".(date("d M Y",strtotime($invoice_date)))." | Amount: $invoice_amount | VAT Amount: $vat_amount | Type: $invoice_type | Category: $category";
        $site->agent_log($text, $customer_id, 'customer');
    }

    echo json_encode(['success' => $ok]);
    break;

  case 'delete':
    $id = intval($_POST['id'] ?? 0);
    $old = $mysqli->query("SELECT document,customer_id FROM customers_invoices WHERE id=$id")->fetch_assoc();
    if (!empty($old['document']) && file_exists($uploadDir . $old['document'])) unlink($uploadDir . $old['document']);
    $mysqli->query("DELETE FROM customers_invoices WHERE id=$id");
    $site->agent_log("Invoice [$id] deleted",$old['customer_id'],'customer');
    echo json_encode(['success' => true]);
    break;
}
?>