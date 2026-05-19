<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

$action = $_POST['action'] ?? '';
$uploadDir = __DIR__ . '/../../uploads/customers/requirements/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

/* =========================================================
   FETCH REQUIREMENTS
   ========================================================= */
if ($action === 'fetch') {
    $customer_id = intval($_POST['customer_id'] ?? 0);

    $show_actions = isset($_POST['quotation'])?0:1;

    $stmt = $mysqli->prepare("SELECT * FROM customers_requirements WHERE customer_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo '<p class="text-muted text-center small">No requirements found for this customer.</p>';
        exit;
    }

    while ($r = $result->fetch_assoc()) {
        $acc   = $r['accommodation'] ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';
        $trans = $r['transport']     ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';
        $over  = $r['overtime']      ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';

        $accDetails = $r['accommodation_details'] ? "<div class='small text-muted'>🏠 " . htmlspecialchars($r['accommodation_details']) . "</div>" : "";
        $transDetails = $r['transport_details'] ? "<div class='small text-muted'>🚗 " . htmlspecialchars($r['transport_details']) . "</div>" : "";

        // Decode overtime policies
        $overtimeHTML = '';
        if (!empty($r['overtime_policies'])) {
            $policies = json_decode($r['overtime_policies'], true);
            if ($policies && is_array($policies)) {
                $overtimeHTML .= "<ul class='small text-muted mb-0'>";
                foreach ($policies as $p) {
                    $policy = htmlspecialchars($p['policy'] ?? '');
                    $rate = htmlspecialchars($p['rate'] ?? '');
                    $overtimeHTML .= "<li>⏱️ {$policy} - AED {$rate}</li>";
                }
                $overtimeHTML .= "</ul>";
            }
        }

        // Attachment preview
        $attachment = '';
        if (!empty($r['attachment'])) {
            $path = "uploads/customers/requirements/" . rawurlencode($r['attachment']);
            $ext = strtolower(pathinfo($r['attachment'], PATHINFO_EXTENSION));
            $icon = ($ext === 'pdf') ? 'fa-file-pdf text-danger' : 'fa-image text-success';
            $attachment = "<button data-file='{$path}' data-type='".($ext=='pdf'?'pdf':'image')."' data-label='Attachment' class='btn btn-light btn-sm border view-document'>
                <i class='fa {$icon} me-1'></i>
            </button>";
        }

        $expiryLabel = $r['expiry'] ? date('d M Y', strtotime($r['expiry'])) : '<span class="text-muted small">N/A</span>';
        $typeLabel = htmlspecialchars($r['req_type'] ?? 'Enquiry');

        echo "
        <div class='card border rounded-3 shadow-sm mb-3'>
          <div class='card-body py-3 px-3'>
            <div class='d-flex justify-content-between align-items-start'>
              <div>
                <p class='fw-bold mb-1'>".intval($r['num_employees'])."x ".htmlspecialchars($r['job_title'])."</p>
                <p class='text-muted small mb-0'>Rate: AED ".htmlspecialchars($r['rate_pay'])."/hr | Start: " . date('d M Y', strtotime($r['start_date'])) . " | Expiry: {$expiryLabel} | Type: {$typeLabel}</p>
                <!--{$accDetails}-->
                <!--{$transDetails}-->
                <!--{$overtimeHTML}-->
              </div>".
              
              ($show_actions?"<div class='d-flex gap-2'>
                " . (!empty($attachment) ? $attachment : "") . "
                <button class='btn btn-light btn-sm border edit-requirement' data-id='".intval($r['id'])."'><i class='fa fa-pen'></i></button>
                <button class='btn btn-light btn-sm border text-danger delete-requirement' data-id='".intval($r['id'])."'><i class='fa fa-trash'></i></button>
              </div>":"").
            "</div>

            <div class='row mt-2 small text-secondary'>
              <div class='col-4 d-flex align-items-center gap-1'>Accom: $acc</div>
              <div class='col-4 d-flex align-items-center gap-1'>Transport: $trans</div>
              <div class='col-4 d-flex align-items-center gap-1'>Overtime: $over</div>
            </div>

            <p class='text-end text-muted small mt-2 mb-0'>
              <em>By ".htmlspecialchars($r['created_by'])." on " . date('d M Y', strtotime($r['created_at'])) . "</em>
            </p>
          </div>
        </div>";
    }
    $stmt->close();
    exit;
}

/* =========================================================
   MULTI-SAVE REQUIREMENTS (NEW ENHANCED)
   ========================================================= */
if ($action === 'multi_save') {
    $customer_id = intval($_POST['customer_id'] ?? 0);
    if ($customer_id <= 0) {
        echo "Invalid customer ID";
        exit;
    }

    $created_by = $_SESSION['person_name'] ?? 'Admin';
    $now = date("Y-m-d H:i:s");

    $job_titles = $_POST['job_title'] ?? [];
    $num_employees = $_POST['num_employees'] ?? [];
    $rate_pays = $_POST['rate_pay'] ?? [];
    $start_dates = $_POST['start_date'] ?? [];
    $expiries = $_POST['expiry'] ?? [];
    $expiry_alerts  = $_POST['expiry_alert'] ?? [];
    $types = $_POST['req_type'] ?? [];
    $accommodations = $_POST['accommodation'] ?? [];
    $accommodation_details = $_POST['accommodation_details'] ?? [];
    $transports = $_POST['transport'] ?? [];
    $transport_details = $_POST['transport_details'] ?? [];
    $overtimes = $_POST['overtime'] ?? [];
    $overtime_policies = $_POST['overtime_policy'] ?? [];
    $overtime_rates = $_POST['overtime_rate'] ?? [];

    // Attachments: normalize array
    $attachmentNames = [];
    if (!empty($_FILES['attachment']['name'])) {
        $names = $_FILES['attachment']['name'];
        $tmp = $_FILES['attachment']['tmp_name'];
        if (!is_array($names)) { $names = [$names]; $tmp = [$tmp]; }
        for ($i=0;$i<count($names);$i++) {
            if (empty($names[$i]) || empty($tmp[$i]) || !is_uploaded_file($tmp[$i])) { $attachmentNames[$i] = null; continue; }
            $ext = pathinfo($names[$i], PATHINFO_EXTENSION);
            $attachmentNames[$i] = uniqid('req_') . '.' . strtolower($ext);
            move_uploaded_file($tmp[$i], $uploadDir . $attachmentNames[$i]);
        }
    }

    $insert = $mysqli->prepare("INSERT INTO customers_requirements
      (customer_id, job_title, num_employees, rate_pay, start_date, expiry, expiry_alert, req_type, accommodation, accommodation_details, transport, transport_details, overtime, overtime_policies, attachment, created_by, created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($job_titles as $i => $jt) {
        $jt = trim($jt);
        if ($jt === '') continue;
        $n = intval($num_employees[$i] ?? 1);
        $r = $rate_pays[$i] ?? '';
        $s = $start_dates[$i] ?? null;
        $ex = $expiries[$i] ?? null;
        $exa = $expiry_alerts[$i] ?? '0';
        $tp = $types[$i] ?? 'Enquiry';
        // default expiry = start_date + 14 days if not provided
        if (empty($ex) && !empty($s)) {
            $ex = date('Y-m-d', strtotime($s . ' +14 days'));
        }
        $ac = isset($accommodations[$i]) ? 1 : 0;
        $acd = $accommodation_details[$i] ?? '';
        $tr = isset($transports[$i]) ? 1 : 0;
        $trd = $transport_details[$i] ?? '';
        $ot = isset($overtimes[$i]) ? 1 : 0;

        // gather overtime policies (best-effort: include all pairs)
        $policies = [];
        // We will try to take policies sequentially; this is a best-effort approach.
        // If you need strict per-row grouping, change input naming to overtime_policy[row][]
        for ($j=0;$j<count($overtime_policies);$j++) {
            $p = trim($overtime_policies[$j] ?? '');
            $rate = $overtime_rates[$j] ?? '';
            if ($p !== '') $policies[] = ['policy' => $p, 'rate' => $rate];
        }
        $polJson = !empty($policies) ? json_encode($policies, JSON_UNESCAPED_SLASHES) : null;

        $att = $attachmentNames[$i] ?? null;

        $insert->bind_param("issdsssssississss", $customer_id, $jt, $n, $r, $s, $ex, $exa, $tp, $ac, $acd, $tr, $trd, $ot, $polJson, $att, $created_by, $now);
        $insert->execute();

        $site->agent_log("Added job requirement $jt ($n x $r) start: $s expiry: $ex", $customer_id);
    }
    $insert->close();

    echo "success";
    exit;
}

/* =========================================================
   SINGLE SAVE / UPDATE (with optional attachment upload)
   ========================================================= */
if ($action === 'save') {

    // Always index [0] because edit mode sends arrays with one row
    $id             = intval($_POST['edit_id'][0] ?? 0);
    $is_new = $id==0;
    $customer_id    = intval($_POST['customer_id'] ?? 0);
    $job_title      = trim($_POST['job_title'][0] ?? '');
    $num_employees  = intval($_POST['num_employees'][0] ?? 1);
    $rate_pay       = floatval($_POST['rate_pay'][0] ?? 0);
    $start_date     = $_POST['start_date'][0] ?? null;

    $expiry         = $_POST['expiry'][0] ?? null;
    $expiry_alert  = isset($_POST['expiry_alert'][0]) ? 1 : 0;
    $req_type       = $_POST['req_type'][0] ?? 'Enquiry';

    $accommodation  = isset($_POST['accommodation'][0]) ? 1 : 0;
    $accommodation_details = $_POST['accommodation_details'][0] ?? '';

    $transport      = isset($_POST['transport'][0]) ? 1 : 0;
    $transport_details = $_POST['transport_details'][0] ?? '';

    $overtime       = isset($_POST['overtime'][0]) ? 1 : 0;

    // Collect overtime policies (multiple)
    $overtimePol = [];
    if (!empty($_POST['overtime_policy']) && !empty($_POST['overtime_rate'])) {
        foreach ($_POST['overtime_policy'] as $i => $p) {
            $p = trim($p);
            if ($p === '') continue;
            $overtimePol[] = [
                'policy' => $p,
                'rate'   => $_POST['overtime_rate'][$i] ?? 0
            ];
        }
    }
    $overtime_json = !empty($overtimePol) ? json_encode($overtimePol, JSON_UNESCAPED_SLASHES) : null;

    $created_by     = $_SESSION['person_name'] ?? 'Admin';

    // default expiry = +14 days if missing
    if (empty($expiry) && !empty($start_date)) {
        $expiry = date('Y-m-d', strtotime($start_date . ' +14 days'));
    }

    /* ---------------- HANDLE ATTACHMENT UPLOAD ---------------- */
    $attachmentName = '';

    if (!empty($_FILES['attachment']['name'][0])) {
        $fileTmp  = $_FILES['attachment']['tmp_name'][0];
        $fileName = basename($_FILES['attachment']['name'][0]);

        if (is_uploaded_file($fileTmp)) {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $newName = uniqid('req_') . '.' . strtolower($ext);
            if (move_uploaded_file($fileTmp, $uploadDir . $newName)) {
                $attachmentName = $newName;
            }
        }
    }

    /* =========================================================
       UPDATE EXISTING REQUIREMENT
       ========================================================= */
    // if ($id) {
    if (!$is_new) {

        // fetch existing file
        $oldFile = null;
        $chk = $mysqli->prepare("SELECT attachment FROM customers_requirements WHERE id=?");
        $chk->bind_param("i", $id);
        $chk->execute();
        $chk->bind_result($oldFile);
        $chk->fetch();
        $chk->close();

        $sql = "
        UPDATE customers_requirements SET
            customer_id=?, job_title=?, num_employees=?, rate_pay=?, start_date=?, expiry=?, expiry_alert=?, req_type=?,
            accommodation=?, accommodation_details=?, transport=?, transport_details=?, overtime=?, overtime_policies=?";

        if ($attachmentName != '') $sql .= ", attachment=?";
        $sql .= " WHERE id=?";

        $stmt = $mysqli->prepare($sql);

        if ($attachmentName != '') {
            // WITH attachment
            $stmt->bind_param(
                "isidsssssssssssi",
                $customer_id,
                $job_title,
                $num_employees,
                $rate_pay,
                $start_date,
                $expiry,
                $expiry_alert,
                $req_type,
                $accommodation,
                $accommodation_details,
                $transport,
                $transport_details,
                $overtime,
                $overtime_json,
                $attachmentName,
                $id
            );

        } else {
            // WITHOUT attachment
            $stmt->bind_param(
                "isidssssisisisi",
                $customer_id,            // i
                $job_title,              // s
                $num_employees,          // i
                $rate_pay,               // d
                $start_date,             // s
                $expiry,                 // s
                $expiry_alert,                 // s
                $req_type,               // s
                $accommodation,          // i
                $accommodation_details,  // s
                $transport,              // i
                $transport_details,      // s
                $overtime,               // i
                $overtime_json,          // s
                $id                      // i
            );

        }

        echo $stmt->execute() ? "success" : "error";
        $stmt->close();
        exit;
    }

    /* =========================================================
       INSERT NEW REQUIREMENT
       ========================================================= */
    $stmt = $mysqli->prepare("
        INSERT INTO customers_requirements
        (customer_id, job_title, num_employees, rate_pay, start_date, expiry, expiry_alert, req_type,
         accommodation, accommodation_details, transport, transport_details, overtime, overtime_policies,
         attachment, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->bind_param(
        "isidsssssssisssss",
        $customer_id, $job_title, $num_employees, $rate_pay,
        $start_date, $expiry, $expiry_alert, $req_type,
        $accommodation, $accommodation_details,
        $transport, $transport_details, $overtime,
        $overtime_json,
        $attachmentName, $created_by
    );

    echo $stmt->execute() ? "success" : "error";
    $stmt->close();
    exit;
}

/* =========================================================
   GET SINGLE REQUIREMENT
   ========================================================= */
if ($action === 'get') {
    $id = intval($_POST['id']);
    $stmt = $mysqli->prepare("SELECT * FROM customers_requirements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
    $stmt->close();
    exit;
}

/* =========================================================
   DELETE REQUIREMENT
   ========================================================= */
if ($action === 'delete') {
    $id = intval($_POST['id']);
    // delete attached file if any
    $chk = $mysqli->prepare("SELECT attachment FROM customers_requirements WHERE id=?");
    $chk->bind_param("i", $id);
    $chk->execute();
    $chk->bind_result($afile);
    $chk->fetch();
    $chk->close();
    if ($afile && file_exists($uploadDir . $afile)) unlink($uploadDir . $afile);

    $stmt = $mysqli->prepare("DELETE FROM customers_requirements WHERE id = ?");
    $stmt->bind_param("i", $id);
    echo $stmt->execute() ? "success" : "error";
    $stmt->close();
    exit;
}
?>
