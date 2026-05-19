<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header("Content-Type: application/json");

$action   = $_POST['action'] ?? '';
$agent_id = $_SESSION['person_id'] ?? 0;
$datetime = date('Y-m-d H:i:s');

// determine user type and target table
$user_type = $_POST['user_type'] ?? 'employee'; // employee|recruiter|customer
$user_id   = intval($_POST['user_id'] ?? 0);

$user_type = strtolower($user_type);
switch ($user_type) {
    case 'recruiter':
        $table = 'recruiters_payments';
        $userTable = 'recruiters';
        $uploadSubdir = 'recruiters';
        break;
    case 'customer':
        $table = 'customers_payments';
        $userTable = 'customers';
        $uploadSubdir = 'customers';
        break;
    case 'employee':
        $table = 'employees_payments';
        $userTable = 'employees';
        $uploadSubdir = 'employees';
        break;
    default: echo json_encode(["success" => false, "error" => "Unknown payment source"]);
            exit;
}

// make upload dir per type
$uploadDir = __DIR__ . "/../../uploads/{$uploadSubdir}/payments/";
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

switch ($action) {

    /* ============================================================
       FETCH payments for a given user (user_type + user_id)
       POST: action=fetch, user_type, user_id
       ============================================================ */
    case 'fetch':
        $uid = intval($_POST['user_id'] ?? 0);
        if ($uid <= 0) {
            echo json_encode(['success' => false, 'data' => [], 'error' => 'Invalid user id']);
            exit;
        }

        $sql = "SELECT * FROM {$table} WHERE ";
        // tables may store user id column differently historically; try common names:
        // We'll expect column user_id for recruiters/customers but employees table previously used employee_id
        // if ($user_type === 'employee') {
            // $sql .= "employee_id = ?";
        // } else {
            // for recruiter/customer tables assume column is user_id
            $sql .= $user_type."_id = ?";
        // }
        $sql .= " ORDER BY id DESC";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $res = $stmt->get_result();

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $row['invoice_dated'] = date("d M Y", strtotime($row['invoice_date'] ?? $row['created_at'] ?? date('Y-m-d')));
            // decode documents (can be structured or legacy)
            $docs = [];
            if (!empty($row['document'])) {
                $decoded = json_decode($row['document'], true);
                if (is_array($decoded)) {
                    if (isset($decoded[0]['file'])) {
                        foreach ($decoded as $d) {
                            if (!empty($d['file'])) {
                                $docs[] = [
                                    'file' => $d['file'],
                                    'name' => $d['name'] ?? '',
                                    'url'  => "uploads/{$uploadSubdir}/payments/" . $d['file'],
                                    'type' => (strtolower(pathinfo($d['file'], PATHINFO_EXTENSION)) === 'pdf') ? 'pdf' : 'image'
                                ];
                            }
                        }
                    } else {
                        foreach ($decoded as $f) {
                            if (!empty($f)) {
                                $docs[] = [
                                    'file' => $f,
                                    'name' => '',
                                    'url'  => "uploads/{$uploadSubdir}/payments/" . $f,
                                    'type' => (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') ? 'pdf' : 'image'
                                ];
                            }
                        }
                    }
                } else {
                    $parts = array_filter(array_map('trim', explode(',', $row['document'])));
                    foreach ($parts as $f) {
                        $docs[] = [
                            'file' => $f,
                            'name' => '',
                            'url'  => "uploads/{$uploadSubdir}/payments/" . $f,
                            'type' => (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') ? 'pdf' : 'image'
                        ];
                    }
                }
            }

            $file_type = count($docs) ? ($docs[0]['type'] ?? '') : '';
            $row['documents'] = $docs;
            $row['file_type'] = $file_type;
            $data[] = $row;
        }

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
        break;


    /* ============================================================
       SAVE or UPDATE payment — works per-table (employee/recruiter/customer)
       Expected POST fields:
         - id (0 for insert)
         - user_id (the id of employee/recruiter/customer)
         - user_type (employee|recruiter|customer)
         - payment_date, payment_type, payment_category, payment_status, payment_amount, payment_partial, payment_payment_method
         - other fields (card_last4, cheque_*, reimbursable, notes, existing_documents, document_names)
         - files in input name "document[]" (multiple)
       ============================================================ */
    case 'save':
        // basic inputs
        $id                    = intval($_POST['id'] ?? 0);
        $target_user_id        = intval($_POST['user_id'] ?? 0);

        $invoice_date          = $_POST['payment_date'] ?? date('Y-m-d');
        $invoice_type          = $_POST['payment_type'] ?? 'Income';
        $invoice_cat           = $_POST['payment_category'] ?? 'General';
        $payment_status        = $_POST['payment_status'] ?? 'Unpaid';
        $invoice_amount        = $_POST['payment_amount'] ?? '0';
        $invoice_partial       = $_POST['payment_partial'] ?? '0';
        $reclaim_by            = $_POST['reclaim_by'] ?? '';
        $invoice_payment_method= $_POST['payment_payment_method'] ?? '';
        $card_last4            = $_POST['card_last4'] ?? '';
        $cheque_bank           = $_POST['cheque_bank'] ?? '';
        $cheque_issuer         = $_POST['cheque_issuer'] ?? '';
        $reimbursable          = $_POST['reimbursable'] ?? '';
        $reimbursement_amount  = $_POST['reimbursement_amount'] ?? '';
        $notes                 = trim($_POST['notes'] ?? '');

        $existing_files = json_decode($_POST['existing_documents'] ?? '[]', true) ?? [];
        $document_names = json_decode($_POST['document_names'] ?? '[]', true) ?? [];
        if (!is_array($document_names)) $document_names = [];

        if ($payment_status !== 'Partial Paid') $invoice_partial = '0';
        if (!($payment_status === 'Partial Paid' || $payment_status === 'Paid')) $invoice_payment_method = '';

        // normalize existing_files to [{file,name},...]
        $existing_files = array_map(function($f) {
            return is_array($f) ? $f : ['file' => $f, 'name' => ''];
        }, $existing_files);

        // load old record files if editing (to delete removed files)
        $old_files = [];
        if ($id > 0) {
            // read old row from respective table
            $qcol = $user_type.'_id';
            $stmtOld = $mysqli->prepare("SELECT document FROM {$table} WHERE id = ?");
            $stmtOld->bind_param('i', $id);
            $stmtOld->execute();
            $oldRow = $stmtOld->get_result()->fetch_assoc();
            $stmtOld->close();
            if (!empty($oldRow['document'])) {
                $decoded = json_decode($oldRow['document'], true);
                $old_files = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $oldRow['document'])));
            }
        }

        // handle new uploaded files
        $new_files = [];
        if (!empty($_FILES['document']['name'])) {
            $names = $_FILES['document']['name'];
            $tmp_names = $_FILES['document']['tmp_name'];
            if (!is_array($names)) {
                $names = [$names];
                $tmp_names = [$tmp_names];
            }
            foreach ($names as $i => $name) {
                if (empty($name) || empty($tmp_names[$i]) || !is_uploaded_file($tmp_names[$i])) continue;
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if ($ext === '') continue;
                $newFile = uniqid('inv_', true) . '.' . $ext;
                if (move_uploaded_file($tmp_names[$i], $uploadDir . $newFile)) {
                    $label = $document_names[$i] ?? '';
                    $new_files[] = ['file' => $newFile, 'name' => $label];
                }
            }
        }

        // remove old files deleted on edit
        foreach ($old_files as $oldFile) {
            $oldFileName = is_array($oldFile) ? $oldFile['file'] : $oldFile;
            // if old file not present in current existing_files, delete physical file
            if (!in_array($oldFileName, array_column($existing_files, 'file')) && file_exists($uploadDir . $oldFileName)) {
                @unlink($uploadDir . $oldFileName);
            }
        }

        // add category to payment_categories if missing (keeps your behavior)
        if ($invoice_cat !== '') {
            $getpcat = $db->get('payment_categories', ['category' => $invoice_cat]);
            if (!$getpcat) {
                $db->insert('payment_categories', ['category' => $invoice_cat, 'created_at' => $datetime]);
            }
        }


        // merge kept existing files + new files
        $all_files = array_merge($existing_files, $new_files);
        $all_files = array_values(array_filter($all_files, fn($f) => !empty($f['file'])));
        $fileJson = json_encode($all_files, JSON_UNESCAPED_SLASHES);

        // prepare insert/update using appropriate columns
        if ($id > 0) {
            // UPDATE
            // column for linking to user differs for employees table historically (employee_id)
            $linkCol = $user_type.'_id';

            $stmt = $mysqli->prepare("
                UPDATE {$table} SET
                  {$linkCol} = ?,
                  invoice_date = ?, type = ?, category = ?, payment_status = ?,
                  invoice_amount = ?, invoice_partial = ?, invoice_payment_method = ?,
                  reclaim_by = ?, card_last4 = ?, cheque_bank = ?, cheque_issuer = ?,
                  reimbursable = ?, reimbursement_amount = ?, document = ?, notes = ?
                WHERE id = ?
            ");
            $stmt->bind_param(
                "issssdssdsssssssi",
                $target_user_id,
                $invoice_date, $invoice_type, $invoice_cat, $payment_status,
                $invoice_amount, $invoice_partial, $invoice_payment_method,
                $reclaim_by, $card_last4, $cheque_bank, $cheque_issuer,
                $reimbursable, $reimbursement_amount, $fileJson, $notes, $id
            );

            $ok = $stmt->execute();
            $stmt->close();

            // optional logging — keep your existing site->agent_log if present
            if (function_exists('site') || isset($site)) {
                @$site->agent_log("Payment [$id] updated ({$invoice_date}, {$invoice_type}, {$invoice_cat}, {$payment_status}, {$invoice_amount})", $target_user_id, $user_type);
            }
        } else {
            // INSERT
            $linkCol = $user_type.'_id';

            $columns = "{$linkCol}, invoice_date, type, category, payment_status, invoice_amount, invoice_partial, invoice_payment_method, reclaim_by, card_last4, cheque_bank, cheque_issuer, reimbursable, reimbursement_amount, document, notes, created_by";
            $placeholders = implode(',', array_fill(0, 17, '?')); // 17 placeholders

            $stmt = $mysqli->prepare("
                INSERT INTO {$table} ({$columns})
                VALUES ({$placeholders})
            ");

            // bind types and values consistent with your table
            $stmt->bind_param(
                "isssssssssssssssi",
                $target_user_id,
                $invoice_date, $invoice_type, $invoice_cat, $payment_status,
                $invoice_amount, $invoice_partial, $invoice_payment_method,
                $reclaim_by, $card_last4, $cheque_bank, $cheque_issuer,
                $reimbursable, $reimbursement_amount, $fileJson, $notes, $agent_id
            );

            $ok = $stmt->execute();
            $new_id = $mysqli->insert_id;
            $stmt->close();

            if (function_exists('site') || isset($site)) {
                @$site->agent_log("Payment [{$new_id}] added ({$invoice_date}, {$invoice_type}, {$invoice_cat}, {$payment_status}, {$invoice_amount})", $target_user_id, $user_type);
            }
        }

        echo json_encode(['success' => !empty($ok)]);
        exit;
        break;


    case 'get':

    $id  = intval($_POST['id'] ?? 0);
    $src = $user_type; //$_POST['src'] ?? '';

    if (!$id || !$src) {
        echo json_encode(["success" => false, "error" => "Invalid ID/source"]);
        exit;
    }

    // Fetch payment
    $sql = "SELECT * FROM $table WHERE id=$id LIMIT 1";
    $res = $mysqli->query($sql);

    if (!$res || $res->num_rows == 0) {
        echo json_encode(["success" => false, "error" => "Payment not found"]);
        exit;
    }

    $row = $res->fetch_assoc();

    // Load user name
    $uid = intval($row[$user_type.'_id'] ?? 0);
    $uname = "";

    if ($uid > 0) {
        $u = $mysqli->query("SELECT name FROM $userTable WHERE id=$uid")->fetch_assoc();
        $uname = $u['name'] ?? "";
    }

    // Decode documents into full list
    $docs = [];
    if (!empty($row['document'])) {

        $decoded = json_decode($row['document'], true);

        if (is_array($decoded)) {

            // structured [{file,name}, ...]
            if (isset($decoded[0]['file'])) {
                foreach ($decoded as $d) {
                    $file = $d['file'];
                    $docs[] = [
                        "file" => $file,
                        "name" => $d['name'] ?? "",
                        "url"  => "uploads/$uploadSubdir/payments/" . $file,
                        "type" => strtolower(pathinfo($file, PATHINFO_EXTENSION)) === "pdf" ? "pdf" : "image"
                    ];
                }
            }

            // legacy: ["file1.jpg", "file2.pdf"]
            else {
                foreach ($decoded as $file) {
                    if ($file == "") continue;
                    $docs[] = [
                        "file" => $file,
                        "name" => "",
                        "url"  => "uploads/$uploadSubdir/payments/" . $file,
                        "type" => strtolower(pathinfo($file, PATHINFO_EXTENSION)) === "pdf" ? "pdf" : "image"
                    ];
                }
            }
        }
    }

    // Final response
    $rid = ucfirst(substr($user_type, 0, 1)).$row["id"]+1000;
    echo json_encode([
        "success" => true,
        "data" => [
            "id"       => $rid,
            "pid"       => $row['id'],
            "user_id"  => $uid,
            "name"     => $uname,
            "source_type" => $src,

            "type"     => $row['type'],
            "category" => $row['category'],
            "amount"   => $row['invoice_amount'],
            "status"   => $row['payment_status'],
            "date"     => $row['invoice_date'],
            "notes"    => $row['notes'],

            "invoice_payment_method" => $row['invoice_payment_method'],
            "invoice_partial"        => $row['invoice_partial'],
            "reclaim_by"             => $row['reclaim_by'],
            "card_last4"             => $row['card_last4'],
            "cheque_bank"            => $row['cheque_bank'],
            "cheque_issuer"          => $row['cheque_issuer'],
            "reimbursable"           => $row['reimbursable'],
            "reimbursement_amount"   => $row['reimbursement_amount'],

            "documents" => $docs
        ]
    ]);
    break;


    /* ============================================================
       DELETE payment
       POST: action=delete, id, user_type
       ============================================================ */
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid id']);
            exit;
        }

        // fetch document list to delete files
        $stmt = $mysqli->prepare("SELECT document FROM {$table} WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!empty($row['document'])) {
            $decoded = json_decode($row['document'], true);
            $files = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $row['document'])));
            foreach ($files as $f) {
                $filename = is_array($f) ? $f['file'] : $f;
                if ($filename && file_exists($uploadDir . $filename)) @unlink($uploadDir . $filename);
            }
        }

        $stmt = $mysqli->prepare("DELETE FROM {$table} WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => !empty($ok)]);
        exit;
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit;
}
?>