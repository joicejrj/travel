<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

$action = $_POST['action'] ?? '';

$uploadDir = __DIR__ . '/../../uploads/recruiters/documents/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0775, true);

/* ---------------- FETCH DOCUMENTS ---------------- */
if ($action === 'fetch') {
    $recruiter_id = intval($_POST['recruiter_id'] ?? 0);

    $stmt = $mysqli->prepare("SELECT * FROM recruiters_documents WHERE recruiter_id=? ORDER BY id DESC");
    $stmt->bind_param("i", $recruiter_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<tr><td colspan='5' class='text-center small text-muted'>No documents found.</td></tr>";
        exit;
    }

    while ($r = $result->fetch_assoc()) {
        $icon = $r['file_type'] == 'pdf'
            ? '<i class="fa fa-file-pdf text-danger"></i>'
            : '<i class="fa fa-image text-success"></i>';

        $fileLink = "./uploads/recruiters/documents/" . htmlspecialchars($r['file_name']);
        $expiry = $r['expiry_date'] ? date("d M Y", strtotime($r['expiry_date'])) : '-';

        echo "
        <tr>
          <td>{$r['label']}</td>
          <td>$icon</td>
          <td>$expiry</td>
          <td>
              <button class='btn btn-link p-0 text-primary small view-document-gallery' 
                      data-file='$fileLink' 
                      data-type='{$r['file_type']}' 
                      data-label='".htmlspecialchars($r['label'], ENT_QUOTES)."'>
                View
              </button>
            </td>
          <td class='text-end'>
            <button class='btn btn-light btn-xs border text-danger delete-document' data-id='{$r['id']}'><i class='fa fa-trash'></i></button>
          </td>
        </tr>";
    }
    $stmt->close();
    exit;
}

/* ---------------- SAVE / UPLOAD DOCUMENT ---------------- */
if ($action === 'save') {
    $recruiter_id = intval($_POST['recruiter_id']);
    $label = trim($_POST['label']);
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $created_by = $_SESSION['person_name'] ?? 'Admin';

    if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
        echo "No file uploaded.";
        exit;
    }

    $fileTmp  = $_FILES['file']['tmp_name'];
    $fileName = time() . '_' . basename($_FILES['file']['name']);
    $target   = $uploadDir . $fileName;

    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $file_type = ($ext == 'pdf') ? 'pdf' : 'image';

    if (!move_uploaded_file($fileTmp, $target)) {
        echo "Upload failed.";
        exit;
    }

    $stmt = $mysqli->prepare("
        INSERT INTO recruiters_documents (recruiter_id, label, file_name, file_type, expiry_date, created_by)
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $recruiter_id, $label, $fileName, $file_type, $expiry_date, $created_by);

    $succe = $stmt->execute();
    if($succe) {
        $site->agent_log("Added new Document ".$label." (".$fileName.")",$recruiter_id,'recruiter');

        //if expiry date is valid, create a reminder
        if ($expiry_date && strtotime($expiry_date)) {
          // subtract 30 days
          $reminder_at = date('Y-m-d 10:00:00', strtotime($expiry_date . ' -30 days'));
          // only insert if reminder_at is valid date
          if ($reminder_at && strtotime($reminder_at)) {
              $db->insert('recruiters_reminders',array('recruiter_id'=>$recruiter_id,'type'=>'General','reminder_at'=>$reminder_at,'note'=>"Document $label expiry on ".date("d M Y",strtotime($expiry_date)),'created_at'=>$datetime));
          }
        }

    }
    echo $succe ? 'success' : 'error';

    $stmt->close();
    exit;
}

/* ---------------- DELETE DOCUMENT ---------------- */
if ($action === 'delete') {
    $id = intval($_POST['id']);
    $recruiter_id = intval($_POST['recruiter_id']);

    // Get file name before delete
    $stmt = $mysqli->prepare("SELECT file_name,label FROM recruiters_documents WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res && file_exists($uploadDir . $res['file_name'])) {
        unlink($uploadDir . $res['file_name']);
    }

    $stmt = $mysqli->prepare("DELETE FROM recruiters_documents WHERE id=?");
    $stmt->bind_param("i", $id);

    $succe = $stmt->execute();
    if($succe) {
        $site->agent_log("Deleted Document ".$res['label']." (".$res['file_name'].")",$recruiter_id,'recruiter');
    }
    echo $succe ? 'success' : 'error';
    
    $stmt->close();
    exit;
}
?>