<?php
// public/ajax/invoices_actions.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$agent_id = $_SESSION['person_id'] ?? 0;
$datetime = date('Y-m-d H:i:s');

// user_type switch (employee|recruiter|customer)
$user_type = strtolower($_POST['user_type'] ?? 'employee'); // default employee for fetch/save
switch ($user_type) {
    case 'recruiter':
        $table = 'recruiters_invoices';
        $userTable = 'recruiters';
        $uploadSubdir = 'recruiters';
        $idCol = 'recruiter_id';
        break;
    case 'customer':
        $table = 'customers_invoices';
        $userTable = 'customers';
        $uploadSubdir = 'customers';
        $idCol = 'customer_id';
        break;
    case 'employee':
    default:
        $table = 'employees_invoices';
        $userTable = 'employees';
        $uploadSubdir = 'employees';
        $idCol = 'employee_id';
        break;
}

// upload dir
$uploadDir = __DIR__ . "/../../uploads/{$uploadSubdir}/invoices/";
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

switch ($action) {

    // fetch invoices for a given user (post: action=fetch user_type user_id)
    case 'fetch':
        $uid = intval($_POST['user_id'] ?? 0);
        if ($uid <= 0) {
            echo json_encode(['success'=>false,'data'=>[],'error'=>'Invalid user id']);
            exit;
        }

        // column name may be different historically but we use $idCol
        $stmt = $mysqli->prepare("SELECT * FROM {$table} WHERE {$idCol} = ? ORDER BY id DESC");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) {
            $row['invoice_dated'] = date("d M Y", strtotime($row['invoice_date'] ?? $row['created_at'] ?? date('Y-m-d')));
            $row['due_dated'] = !empty($row['due_date']) ? date("d M Y", strtotime($row['due_date'])) : '-';
            // single document filename -> url
            $row['file_type'] = '';
            if (!empty($row['document'])) {
                $ext = strtolower(pathinfo($row['document'], PATHINFO_EXTENSION));
                $row['file_type'] = ($ext === 'pdf' ? 'pdf' : 'image');
                $row['document_url'] = "uploads/{$uploadSubdir}/invoices/" . $row['document'];
            } else {
                $row['document_url'] = '';
            }
            $data[] = $row;
        }
        echo json_encode(['success'=>true,'data'=>$data]);
        exit;
        break;

    // get single invoice (for edit/view)
    case 'get':
        $id = intval($_POST['id'] ?? 0);
        $src = $_POST['user_type'] ?? $user_type;
        if (!$id) {
            echo json_encode(['success'=>false,'error'=>'Invalid id']);
            exit;
        }
        $res = $mysqli->query("SELECT * FROM {$table} WHERE id = {$id} LIMIT 1");
        if (!$res || $res->num_rows == 0) {
            echo json_encode(['success'=>false,'error'=>'Not found']);
            exit;
        }
        $row = $res->fetch_assoc();
        $uid = intval($row[$idCol] ?? 0);
        $uname = '';
        if ($uid > 0) {
            $u = $mysqli->query("SELECT name FROM {$userTable} WHERE id = {$uid} LIMIT 1")->fetch_assoc();
            $uname = $u['name'] ?? '';
        }
        $doc = '';
        $doc_url = '';
        $file_type = '';
        if (!empty($row['document'])) {
            $doc = $row['document'];
            $doc_url = "uploads/{$uploadSubdir}/invoices/" . $doc;
            $ext = strtolower(pathinfo($doc, PATHINFO_EXTENSION));
            $file_type = ($ext === 'pdf' ? 'pdf' : 'image');
        }

        echo json_encode([
            'success'=>true,
            'data'=>[
                'pid' => $row['id'],
                'id'  => ucfirst(substr($src,0,1)).($row['id'] + 1000),
                'user_id' => $uid,
                "source_type" => $src,
                'name' => $uname,
                'invoice_date' => $row['invoice_date'],
                'due_date' => $row['due_date'],
                'amount' => $row['invoice_amount'],
                'type' => $row['type'],
                'category' => $row['category'],
                'notes' => $row['notes'],
                'document' => $doc,
                'document_url' => $doc_url,
                'file_type' => $file_type
            ]
        ]);
        exit;
        break;

    // save (insert/update)
    case 'save':
        // common fields
        $id = intval($_POST['id'] ?? 0);
        // for add: need user_type and invoice_user (user id). For edit: keep existing link col
        $incoming_user_type = strtolower($_POST['invoice_user_type'] ?? $user_type);
        $target_user_id = intval($_POST['invoice_user'] ?? 0);

        // safe fields
        $invoice_date = $_POST['invoice_date'] ?? date('Y-m-d');
        $due_date     = $_POST['due_date'] ?? null;
        $invoice_amount = floatval($_POST['invoice_amount'] ?? 0);
        $invoice_type   = $_POST['invoice_type'] ?? 'Received'; // Received/Sent
        $category       = $_POST['invoice_category'] ?? '';
        $notes          = trim($_POST['notes'] ?? '');

        // DELETE existing document if requested
        if (!empty($_POST['remove_existing_doc']) && intval($_POST['id'] ?? 0) > 0) {
            // fetch old filename
            $id = intval($_POST['id']);
            $row = $mysqli->query("SELECT document FROM {$table} WHERE id = {$id} LIMIT 1")->fetch_assoc();
            $oldFile = $row['document'] ?? '';
            // delete physical file
            if ($oldFile !== '' && file_exists($uploadDir . $oldFile)) {
                @unlink($uploadDir . $oldFile);
            }
            // update DB: remove file reference
            $mysqli->query("UPDATE {$table} SET document='' WHERE id={$id}");
        }

        // handle file (single)
        $finalFile = '';
        if (!empty($_FILES['document']['name'])) {
            $name = $_FILES['document']['name'];
            $tmp  = $_FILES['document']['tmp_name'];
            if (is_uploaded_file($tmp) && $name !== '') {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $newFile = uniqid('inv_', true) . '.' . $ext;
                if (move_uploaded_file($tmp, $uploadDir . $newFile)) {
                    $finalFile = $newFile;
                }
            }
        }

        if ($id > 0) {
            // editing: keep existing user link and replace file if new uploaded (delete old file)
            $row = $mysqli->query("SELECT document FROM {$table} WHERE id = {$id} LIMIT 1")->fetch_assoc();
            $oldFile = $row['document'] ?? '';
            if ($finalFile !== '' && $oldFile !== '' && file_exists($uploadDir . $oldFile)) {
                @unlink($uploadDir . $oldFile);
            }
            // if no new file, keep old
            if ($finalFile === '') $finalFile = $oldFile;

            // update (link column left as-is)
            $stmt = $mysqli->prepare("
                UPDATE {$table} SET invoice_date = ?, due_date = ?, invoice_amount = ?, type = ?, category = ?, document = ?, notes = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssdssssi",
                $invoice_date,
                $due_date,
                $invoice_amount,
                $invoice_type,
                $category,
                $finalFile,
                $notes,
                $id
            );
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok && (function_exists('site') || isset($site))) {
                @$site->agent_log("Invoice [{$id}] updated ({$invoice_date}, {$invoice_type}, {$category}, {$invoice_amount})", $target_user_id, $incoming_user_type);
            }

            echo json_encode(['success' => !empty($ok)]);
            exit;
        } else {
            // INSERT: determine which table to use (user_type parameter defines table)
            $incoming_user_type = strtolower($incoming_user_type);
            switch ($incoming_user_type) {
                case 'recruiter':
                    $itable = 'recruiters_invoices';
                    $icol = 'recruiter_id';
                    $dir = 'recruiters';
                    break;
                case 'customer':
                    $itable = 'customers_invoices';
                    $icol = 'customer_id';
                    $dir = 'customers';
                    break;
                case 'employee':
                default:
                    $itable = 'employees_invoices';
                    $icol = 'employee_id';
                    $dir = 'employees';
                    break;
            }

            // If a file was uploaded but we used a different uploadDir earlier, move/rename accordingly (we already moved it to $uploadDir based on $user_type variable at top).
            // For consistency, assume uploads were placed in the desired folder already.

            $stmt = $mysqli->prepare("
                INSERT INTO {$itable} ({$icol}, invoice_date, due_date, invoice_amount, type, category, document, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("issdssssi",
                $target_user_id,
                $invoice_date,
                $due_date,
                $invoice_amount,
                $invoice_type,
                $category,
                $finalFile,
                $notes,
                $agent_id
            );
            $ok = $stmt->execute();
            $newId = $mysqli->insert_id;
            $stmt->close();

            if ($ok && (function_exists('site') || isset($site))) {
                @$site->agent_log("Invoice [{$newId}] added ({$invoice_date}, {$invoice_type}, {$category}, {$invoice_amount})", $target_user_id, $incoming_user_type);
            }

            echo json_encode(['success' => !empty($ok)]);
            exit;
        }

        break;

    // delete
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success'=>false,'error'=>'Invalid id']);
            exit;
        }
        // fetch file and delete if exists
        $stmt = $mysqli->prepare("SELECT document FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!empty($row['document'])) {
            $file = $row['document'];
            if ($file && file_exists($uploadDir . $file)) @unlink($uploadDir . $file);
        }

        $stmt = $mysqli->prepare("DELETE FROM {$table} WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => !empty($ok)]);
        exit;
        break;

    default:
        echo json_encode(['success'=>false,'error'=>'Invalid action']);
        exit;
}
?>