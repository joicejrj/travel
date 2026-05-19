<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

$action = $_POST['action'] ?? '';

/* =========================================================
   ACTION: Get Existing Contacts by Customer ID
   ========================================================= */
if ($action === 'get_existing_contacts') {

    $customer_id = (int)($_REQUEST['customer_id'] ?? 0);

    if ($customer_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid customer'
        ]);
        exit;
    }

    $stmt = $mysqli->prepare("
        SELECT 
            id,
            name,
            email,
            phone,
            dob, gender
        FROM customers_contacts
        WHERE customer_id = ?
        ORDER BY name
    ");
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $contacts = [];
    while ($row = $res->fetch_assoc()) {
        $row["dobd"] = $row['dob']!=''&&$row['dob']!='0000-00-00'?date("d-m-Y",strtotime($row['dob'])):'-';
        $contacts[] = $row;
    }

    echo json_encode([
        'success' => true,
        'items'   => $contacts
    ]);
    exit;
}

/* ---------------- FETCH CONTACTS ---------------- */
if ($action === 'fetch') {
    $customer_id = intval($_POST['customer_id'] ?? 0);

    $stmt = $mysqli->prepare("
        SELECT * 
        FROM customers_contacts 
        WHERE customer_id = ? 
        ORDER BY id DESC
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo '<p class="text-muted text-center small">No contacts found for this customer.</p>';
        exit;
    }

    while ($r = $result->fetch_assoc()) {
        $id          = $r['id'];
        $name        = htmlspecialchars($r['name']);
        $designation = htmlspecialchars($r['designation']);
        $phone       = htmlspecialchars($r['phone']);
        $email       = htmlspecialchars($r['email']);
        $dob       = htmlspecialchars($r['dob']);
        $gender       = $r['gender']!=''?htmlspecialchars($r['gender']):'';
        $created_by  = htmlspecialchars($r['created_by']);
        $created_at  = date('d M Y', strtotime($r['created_at']));

        // Visiting card images
        // $photo1 = !empty($r['photo1']) ? "uploads/contacts/" . htmlspecialchars($r['photo1']) : "";
        // $photo2 = !empty($r['photo2']) ? "uploads/contacts/" . htmlspecialchars($r['photo2']) : "";

        $showid = !empty($r['photo1'])||!empty($r['photo2']);

        $viewid = $showid?"<button class='btn btn-sm btn-outline-primary' onclick='viewContactCard({$r["id"]})'>
                  <i class='fa fa-id-card me-1'></i>
                </button>":'';

        echo "
        <div class='card border-0 rounded-4 shadow-sm mb-2'>
          <div class='card-body py-2 px-3'>
            <div class='d-flex justify-content-between align-items-start'>
              <div class='flex-grow-1'>
                <p class='fw-semibold mb-1 text-dark'>
                  $name ". (!empty($gender)?" - ".$gender:"") . (!empty($dob) && $dob!="0000-00-00"  ? "<small class='text-muted ms-2'>".(date("d-m-Y",strtotime($dob)))."</small>" : "") . "
                </p>
                <p class='text-muted small mb-1'>
                  <i class='fa fa-phone me-1'></i> $phone &nbsp;
                  <i class='fa fa-envelope me-1'></i> $email
                </p>
              </div>

              <div class='d-flex gap-1'>
                $viewid
                <button class='btn btn-sm btn-outline-secondary edit-contact' data-id='$id' title='Edit'>
                  <i class='fa fa-pen'></i>
                </button>
                <button class='btn btn-sm btn-outline-danger delete-contact' data-id='$id' title='Delete'>
                  <i class='fa fa-trash'></i>
                </button>
              </div>
            </div>

            <p class='text-end text-muted small mt-1 mb-0'>
              <em>By $created_by on $created_at</em>
            </p>
          </div>
        </div>";
    }

    $stmt->close();
    exit;
}


/* ---------------- SAVE (ADD / UPDATE) ---------------- */
if ($action === 'save') {
    $id           = $_POST['id'] ?? '';
    $customer_id  = intval($_POST['customer_id']);
    $name         = trim($_POST['name']);
    $phone        = trim($_POST['phone']);
    $email        = trim($_POST['email']);
    $designation  = trim($_POST['designation']);
    $dob  = $_POST['dob']!=''?date("Y-m-d",strtotime($_POST['dob'])):'';
    $gender  = isset($_POST['gender'])&&$_POST['gender']!=''?trim($_POST['gender']):null;
    $created_by   = $_SESSION['person_name'] ?? 'Admin';

    // ✅ Directory for uploads
    $uploadDir = __DIR__ . '/../../uploads/customers/contacts/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // ✅ Helper function to handle uploads
    function uploadFile($fileKey, $uploadDir) {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $tmpName  = $_FILES[$fileKey]['tmp_name'];
        $safeName = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $_FILES[$fileKey]['name']);
        $target   = $uploadDir . $safeName;

        if (move_uploaded_file($tmpName, $target)) {
            return $safeName;
        }
        return null;
    }

    // Handle uploads
    $photo1 = uploadFile('photo1', $uploadDir);
    $photo2 = uploadFile('photo2', $uploadDir);

    if ($id) {
        // 🧠 Fetch existing record
        $existing = $mysqli->query("SELECT photo1, photo2, name FROM customers_contacts WHERE id=" . intval($id))->fetch_assoc();
        $oldPhoto1 = $existing['photo1'] ?? '';
        $oldPhoto2 = $existing['photo2'] ?? '';

        // 🧹 If new photo uploaded → delete old one
        if ($photo1 && !empty($oldPhoto1) && file_exists($uploadDir . $oldPhoto1)) {
            unlink($uploadDir . $oldPhoto1);
        } else {
            $photo1 = $photo1 ?: $oldPhoto1; // keep old if not replaced
        }

        if ($photo2 && !empty($oldPhoto2) && file_exists($uploadDir . $oldPhoto2)) {
            unlink($uploadDir . $oldPhoto2);
        } else {
            $photo2 = $photo2 ?: $oldPhoto2;
        }

        // 📝 Update contact
        $stmt = $mysqli->prepare("
            UPDATE customers_contacts 
            SET customer_id=?, name=?, phone=?, email=?, designation=?, dob=?, gender=?, photo1=?, photo2=? 
            WHERE id=?");
        $stmt->bind_param("issssssssi", $customer_id, $name, $phone, $email, $designation, $dob, $gender, $photo1, $photo2, $id);

        $succe = $stmt->execute();
        if($succe) {
            $site->agent_log("Updated contact ".$existing['name']."[$id] ($phone $email $dob $gender)",$customer_id);
        }
        echo $succe ? 'success' : 'error';

        $stmt->close();
    } 
    else {
        // 🆕 Add new contact
        $stmt = $mysqli->prepare("
            INSERT INTO customers_contacts (customer_id, name, phone, email, designation, dob, gender, photo1, photo2, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?)");
        $stmt->bind_param("isssssssss", $customer_id, $name, $phone, $email, $designation, $dob, $gender, $photo1, $photo2, $created_by);

        $succe = $stmt->execute();
        if($succe) {
            $site->agent_log("Added new contact with ".$name." ($phone $email $dob $gender)",$customer_id);
        }
        echo $succe ? 'success' : 'error';

        $stmt->close();
    }

    exit;
}

/* ---------------- GET SINGLE CONTACT ---------------- */
if ($action === 'get') {
    $id = intval($_POST['id']);
    $stmt = $mysqli->prepare("SELECT * FROM customers_contacts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
    $stmt->close();
    exit;
}

/* ---------------- DELETE CONTACT ---------------- */
if ($action === 'delete') {
    $id = intval($_POST['id']);
    $customer_id = intval($_POST['customer_id'] ?? 0);

    $stmt1 = $mysqli->prepare("SELECT * FROM customers_contacts WHERE id=?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $res = $stmt1->get_result()->fetch_assoc();
    $stmt1->close();

    $stmt = $mysqli->prepare("DELETE FROM customers_contacts WHERE id = ?");
    $stmt->bind_param("i", $id);

    $succe = $stmt->execute();
    if($succe) {
        $site->agent_log("Deleted contact ".$res['name']."[".$res['id']."]",$customer_id);
    }
    echo $succe ? 'success' : 'error';

    $stmt->close();
    exit;
}

/* ---------------- LIST CONTACTS (JSON for dynamic buttons) ---------------- */
if ($action === 'list') {
    $customer_id = intval($_POST['customer_id'] ?? 0);
    $contacts = [];

    $stmt = $mysqli->prepare("SELECT id, name FROM customers_contacts WHERE customer_id = ? ORDER BY name ASC");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }

    $stmt->close();
    echo json_encode($contacts);
    exit;
}

?>