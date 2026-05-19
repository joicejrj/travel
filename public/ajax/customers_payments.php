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

$uploadDir = __DIR__ . '/../../uploads/customers/payments/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

switch ($action) {

  /* ============================================================
     🟢 FETCH ALL PAYMENTS (Supports labeled multi-files)
     ============================================================ */
    case 'fetch':

    $customer_id = intval($_POST['customer_id'] ?? 0);

    // DATE RANGE FILTER
    $start_date = $_POST['start_date'] ?? '';
    $end_date   = $_POST['end_date'] ?? '';

    $where = "customer_id = $customer_id";

    // Apply filter only when both dates exist
    if (!empty($start_date) && !empty($end_date)) {
        // Convert display format "05 Nov 2025" → "2025-11-05"
        $sd = date("Y-m-d", strtotime($start_date));
        $ed = date("Y-m-d", strtotime($end_date));

        $where .= " AND invoice_date BETWEEN '$sd' AND '$ed'";
    }

    // Query with date filter
    $res = $mysqli->query("SELECT * FROM customers_payments WHERE $where ORDER BY id DESC");

    $data = [];

    while ($row = $res->fetch_assoc()) {

        // Format invoice date
        $row['invoice_dated'] = date("d M Y", strtotime($row['invoice_date']));

        // -------------------------------
        // DOCUMENT DECODING (UNCHANGED)
        // -------------------------------
        $docs = [];
        if (!empty($row['document'])) {
            $decoded = json_decode($row['document'], true);

            if (is_array($decoded)) {
                // structured: [{file,name}, ...]
                if (isset($decoded[0]['file'])) {
                    foreach ($decoded as $d) {
                        if (!empty($d['file'])) {
                            $docs[] = [
                                'file' => $d['file'],
                                'name' => $d['name'] ?? '',
                                'url'  => 'uploads/customers/payments/' . $d['file'],
                                'type' => (strtolower(pathinfo($d['file'], PATHINFO_EXTENSION)) === 'pdf') ? 'pdf' : 'image'
                            ];
                        }
                    }
                }
                // legacy: ["file1.jpg","file2.pdf"]
                else {
                    foreach ($decoded as $f) {
                        if (!empty($f)) {
                            $docs[] = [
                                'file' => $f,
                                'name' => '',
                                'url'  => 'uploads/customers/payments/' . $f,
                                'type' => (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') ? 'pdf' : 'image'
                            ];
                        }
                    }
                }
            }
            // comma-separated string
            else {
                $parts = array_filter(array_map('trim', explode(',', $row['document'])));
                foreach ($parts as $f) {
                    $docs[] = [
                        'file' => $f,
                        'name' => '',
                        'url'  => 'uploads/customers/payments/' . $f,
                        'type' => (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') ? 'pdf' : 'image'
                    ];
                }
            }
        }

        // First file type for preview icon
        $file_type = '';
        if (count($docs)) {
            $file_type = $docs[0]['type'] ?? '';
        }

        $row['documents'] = $docs;
        $row['file_type'] = $file_type;

        $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data]);
    break;

  /* ============================================================
   🟡 SAVE OR UPDATE PAYMENT (MULTI FILES)
   ============================================================ */
  case 'save':
      $id             = intval($_POST['id'] ?? 0);
      $customer_id    = intval($_POST['customer_id']);
      $invoice_date   = $_POST['payment_date'] ?? date('Y-m-d');
      $invoice_type   = $_POST['payment_type'] ?? 'Income';
      $invoice_cat    = $_POST['payment_category'] ?? 'General';
      $payment_status = $_POST['payment_status'] ?? 'Unpaid';
      $invoice_amount = $_POST['payment_amount'] ?? '0';
      $invoice_partial = $_POST['payment_partial'] ?? '0';
      $reclaim_by   = $_POST['reclaim_by'] ?? '';
      $invoice_payment_method = $_POST['payment_payment_method'] ?? '';

      $card_last4 = $_POST['card_last4'] ?? '';
      $cheque_bank = $_POST['cheque_bank'] ?? '';
      $cheque_issuer = $_POST['cheque_issuer'] ?? '';
      $reimbursable = $_POST['reimbursable'] ?? '';
      $reimbursement_amount = $_POST['reimbursement_amount'] ?? '';
      
      $notes          = trim($_POST['notes'] ?? '');
      $existing_files = json_decode($_POST['existing_documents'] ?? '[]', true) ?? [];

        $document_names = json_decode($_POST['document_names'] ?? '[]', true) ?? [];
        if (!is_array($document_names)) $document_names = [];

      if($payment_status!='Partial Paid') {
        $invoice_partial = '0';
      }
      if(!($payment_status=='Partial Paid'||$payment_status=='Paid')) {
        $invoice_payment_method = '';
      }

      if (!is_array($existing_files)) $existing_files = [];

      // Load old record to check removed files
      $old_files = [];
      if ($id > 0) {
          $old = $mysqli->query("SELECT document FROM customers_payments WHERE id=$id")->fetch_assoc();
          if (!empty($old['document'])) {
              $decoded = json_decode($old['document'], true);
              $old_files = is_array($decoded)
                  ? $decoded
                  : array_filter(array_map('trim', explode(',', $old['document'])));
          }
      }

      if($invoice_cat!='') {
        $getpcat = $db->get('payment_categories',array('category'=>$invoice_cat));
        if(!$getpcat) {
            $db->insert('payment_categories',array('category'=>$invoice_cat,'created_at'=>$datetime));
        }
      }

        /* ============================================================
           HANDLE FILE UPLOADS (Supports single or multiple)
           ============================================================ */
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

        /* ============================================================
           REMOVE FILES THAT WERE DELETED ON EDIT
           ============================================================ */
        // Normalize existing_files early
        $existing_files = array_map(function($f) {
            return is_array($f) ? $f : ['file' => $f, 'name' => ''];
        }, $existing_files);

        foreach ($old_files as $oldFile) {
            $oldFileName = is_array($oldFile) ? $oldFile['file'] : $oldFile;
            if (!in_array($oldFileName, array_column($existing_files, 'file')) && file_exists($uploadDir . $oldFileName)) {
                unlink($uploadDir . $oldFileName);
            }
        }

        /* ============================================================
           MERGE OLD (KEPT) + NEW FILES
           ============================================================ */
        $all_files = array_merge($existing_files, $new_files);
        $all_files = array_values(array_filter($all_files, fn($f) => !empty($f['file'])));
        $fileJson  = json_encode($all_files, JSON_UNESCAPED_SLASHES);


      /* ============================================================
         UPDATE OR INSERT RECORD
         ============================================================ */
      if ($id > 0) {
          $stmt = $mysqli->prepare("
              UPDATE customers_payments 
              SET invoice_date=?, type=?, category=?, payment_status=?, invoice_amount=?, invoice_partial=?, invoice_payment_method=?, reclaim_by=?, card_last4=?, cheque_bank=?, cheque_issuer=?, reimbursable=?, reimbursement_amount=?, document=?, notes=? 
              WHERE id=?");
          $stmt->bind_param("sssssssssssssssi", $invoice_date, $invoice_type, $invoice_cat, $payment_status, $invoice_amount, $invoice_partial, $invoice_payment_method, $reclaim_by, $card_last4, $cheque_bank, $cheque_issuer, $reimbursable, $reimbursement_amount, $fileJson, $notes, $id);
          $ok = $stmt->execute();

          $site->agent_log(
              "Payment [$id] updated (".(date("d M Y",strtotime($invoice_date))).", $invoice_type, $invoice_cat, $payment_status, $invoice_amount, $invoice_partial, $invoice_payment_method, $reclaim_by, $notes)",
              $customer_id,
              'customer'
          );

      } else {
          $stmt = $mysqli->prepare("
              INSERT INTO customers_payments 
              (customer_id, invoice_date, type, category, payment_status, invoice_amount, invoice_partial, invoice_payment_method, reclaim_by, card_last4, cheque_bank, cheque_issuer, reimbursable, reimbursement_amount, document, notes, created_by)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
          $stmt->bind_param("isssssssssssssssi", $customer_id, $invoice_date, $invoice_type, $invoice_cat, $payment_status, $invoice_amount, $invoice_partial, $invoice_payment_method, $reclaim_by, $card_last4, $cheque_bank, $cheque_issuer, $reimbursable, $reimbursement_amount, $fileJson, $notes, $agent_id);

          $ok = $stmt->execute();
          $new_id = $mysqli->insert_id;

          $site->agent_log(
              "Payment [$new_id] added (".(date("d M Y",strtotime($invoice_date))).", $invoice_type, $invoice_cat, $payment_status, $notes)",
              $customer_id,
              'customer'
          );
      }

      $stmt->close();

      echo json_encode(['success' => $ok]);
      break;


  /* ============================================================
     🔴 DELETE PAYMENT
     ============================================================ */
  case 'delete':
    $id = intval($_POST['id'] ?? 0);
    $customer_id = intval($_POST['customer_id'] ?? 0);
    $old = $mysqli->query("SELECT document FROM customers_payments WHERE id=$id")->fetch_assoc();

    if (!empty($old['document'])) {
      $decoded = json_decode($old['document'], true);
      $docs = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $old['document'])));
      // foreach ($docs as $f) {
      //   if (file_exists($uploadDir . $f)) unlink($uploadDir . $f);
      // }
        foreach ($docs as $f) {
            // case: structured array → extract file key
            if (is_array($f)) {
                $file = $f['file'] ?? '';
            } else {
                $file = $f;
            }
            if ($file && file_exists($uploadDir . $file)) {
                unlink($uploadDir . $file);
            }
        }
    }

    $mysqli->query("DELETE FROM customers_payments WHERE id=$id");
    $site->agent_log("Payment [$id] deleted",$customer_id,'customer');
    echo json_encode(['success' => true]);
    break;

  /* ============================================================
     🟣 GET SINGLE PAYMENT (FOR EDIT)
     ============================================================ */
  case 'get':
    $id = intval($_POST['id'] ?? 0);
    $row = $mysqli->query("SELECT * FROM customers_payments WHERE id=$id LIMIT 1")->fetch_assoc();
    if ($row) {
      $docs = [];
      if (!empty($row['document'])) {
        $decoded = json_decode($row['document'], true);
        $docs = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $row['document'])));
      }
      $row['documents'] = $docs;
      echo json_encode(['success' => true, 'data' => $row]);
    } else {
      echo json_encode(['success' => false]);
    }
    break;

  default:
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>