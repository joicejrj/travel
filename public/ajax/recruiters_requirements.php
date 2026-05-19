<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

$action = $_POST['action'] ?? '';
$uploadDir = __DIR__ . '/../../uploads/recruiters/requirements/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

/* =========================================================
   FETCH REQUIREMENTS
   ========================================================= */
if ($action === 'fetch') {
    $recruiter_id = intval($_POST['recruiter_id'] ?? 0);

    $stmt = $mysqli->prepare("SELECT * FROM recruiters_requirements WHERE recruiter_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $recruiter_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo '<p class="text-muted text-center small">No requirements found for this recruiter.</p>';
        exit;
    }

    while ($r = $result->fetch_assoc()) {
        $acc   = $r['accommodation'] ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';
        $trans = $r['transport']     ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';
        $over  = $r['overtime']      ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';

        $accDetails = $r['accommodation_details'] ? "<div class='small text-muted'>🏠 {$r['accommodation_details']}</div>" : "";
        $transDetails = $r['transport_details'] ? "<div class='small text-muted'>🚗 {$r['transport_details']}</div>" : "";

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
            $path = "uploads/recruiters/requirements/" . $r['attachment'];
            $ext = strtolower(pathinfo($r['attachment'], PATHINFO_EXTENSION));
            $icon = ($ext === 'pdf') ? 'fa-file-pdf text-danger' : 'fa-image text-success';
            $attachment = "<button data-file='{$path}' data-type='".($ext=='pdf'?'pdf':'image')."' data-label='Attachment' target='_blank' class='btn btn-light btn-sm border view-document'>
                <i class='fa {$icon} me-1'></i>
            </button>";
        }

        echo "
        <div class='card border rounded-3 shadow-sm mb-3'>
          <div class='card-body py-3 px-3'>
            <div class='d-flex justify-content-between align-items-start'>
              <div>
                <p class='fw-bold mb-1'>{$r['num_employees']}x {$r['job_title']}</p>
                <p class='text-muted small mb-0'>Rate: AED {$r['rate_pay']}/hr | Start: " . date('d M Y', strtotime($r['start_date'])) . "</p>
                <!-- {$accDetails} -->
                <!-- {$transDetails} -->
                <!-- {$overtimeHTML} -->
              </div>
              <div class='d-flex gap-2'>
                " . (!empty($attachment) ? $attachment : "") . "
                <button class='btn btn-light btn-sm border edit-requirement' data-id='{$r['id']}'><i class='fa fa-pen'></i></button>
                <button class='btn btn-light btn-sm border text-danger delete-requirement' data-id='{$r['id']}'><i class='fa fa-trash'></i></button>
              </div>
            </div>

            <div class='row mt-2 small text-secondary'>
              <div class='col-4 d-flex align-items-center gap-1'>Accom: $acc</div>
              <div class='col-4 d-flex align-items-center gap-1'>Transport: $trans</div>
              <div class='col-4 d-flex align-items-center gap-1'>Overtime: $over</div>
            </div>

            <p class='text-end text-muted small mt-2 mb-0'>
              <em>By {$r['created_by']} on " . date('d M Y', strtotime($r['created_at'])) . "</em>
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
    $recruiter_id = intval($_POST['recruiter_id'] ?? 0);
    if ($recruiter_id <= 0) {
        echo "Invalid recruiter ID";
        exit;
    }

    $count = count($_POST['job_title']);
    $created_by = $_SESSION['person_name'] ?? 'Admin';
    $now = date("Y-m-d H:i:s");

    for ($i = 0; $i < $count; $i++) {
        $job_title = trim($_POST['job_title'][$i] ?? '');
        $num_employees = intval($_POST['num_employees'][$i] ?? 1);
        $rate_pay = floatval($_POST['rate_pay'][$i] ?? 0);
        $start_date = $_POST['start_date'][$i] ?? '';
        $accommodation = isset($_POST['accommodation'][$i]) ? 1 : 0;
        $accommodation_details = $_POST['accommodation_details'][$i] ?? '';
        $transport = isset($_POST['transport'][$i]) ? 1 : 0;
        $transport_details = $_POST['transport_details'][$i] ?? '';
        $overtime = isset($_POST['overtime'][$i]) ? 1 : 0;

        // Handle overtime policies (flat arrays across form)
        $overtime_policies = [];
        if (!empty($_POST['overtime_policy']) && !empty($_POST['overtime_rate'])) {
            for ($j = 0; $j < count($_POST['overtime_policy']); $j++) {
                $p = $_POST['overtime_policy'][$j];
                $r = $_POST['overtime_rate'][$j];
                if (!empty($p)) {
                    $overtime_policies[] = ['policy' => $p, 'rate' => $r];
                }
            }
        }
        $overtime_json = !empty($overtime_policies) ? json_encode($overtime_policies) : null;

        // Handle attachment
        $attachment = '';
        if (isset($_FILES['attachment']['name'][$i]) && $_FILES['attachment']['name'][$i] !== '') {
            $ext = pathinfo($_FILES['attachment']['name'][$i], PATHINFO_EXTENSION);
            $attachment = uniqid('req_') . '.' . $ext;
            move_uploaded_file($_FILES['attachment']['tmp_name'][$i], $uploadDir . $attachment);
        }

        $stmt = $mysqli->prepare("
            INSERT INTO recruiters_requirements
            (recruiter_id, job_title, num_employees, rate_pay, start_date, accommodation, accommodation_details,
             transport, transport_details, overtime, overtime_policies, attachment, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "isidsssissssss",
            $recruiter_id,
            $job_title,
            $num_employees,
            $rate_pay,
            $start_date,
            $accommodation,
            $accommodation_details,
            $transport,
            $transport_details,
            $overtime,
            $overtime_json,
            $attachment,
            $created_by,
            $now
        );
        $stmt->execute();
        $stmt->close();

        $site->agent_log("Added job requirement $job_title ($num_employees x $rate_pay) on date ".$start_date,$recruiter_id,'recruiter');
    }

    echo "success";
    exit;
}

/* =========================================================
   SINGLE SAVE / UPDATE (with optional attachment upload)
   ========================================================= */
if ($action === 'save') {
    $id             = $_POST['id'] ?? '';
    $recruiter_id    = intval($_POST['recruiter_id']);
    $job_title      = trim($_POST['job_title']);
    $num_employees  = intval($_POST['num_employees']);
    $rate_pay       = floatval($_POST['rate_pay']);
    $start_date     = $_POST['start_date'] ?? null;
    $accommodation  = isset($_POST['accommodation']) ? 1 : 0;
    $transport      = isset($_POST['transport']) ? 1 : 0;
    $overtime       = isset($_POST['overtime']) ? 1 : 0;
    $created_by     = $_SESSION['person_name'] ?? 'Admin';

    /* ---------------- HANDLE ATTACHMENT UPLOAD ---------------- */
    $attachmentName = '';

    if (!empty($_FILES['attachment']['name'][0])) {
        // single file (since edit uses one file input, not multiple)
        $fileTmp  = $_FILES['attachment']['tmp_name'][0];
        $fileName = basename($_FILES['attachment']['name'][0]);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $newName = uniqid('req_') . '.' . strtolower($ext);

        if (move_uploaded_file($fileTmp, $uploadDir . $newName)) {
            $attachmentName = $newName;
        }
    }

    /* ---------------- UPDATE ---------------- */
    if ($id) {
        // fetch existing file (for replacement cleanup)
        $oldFile = null;
        $chk = $mysqli->prepare("SELECT attachment FROM recruiters_requirements WHERE id=? LIMIT 1");
        $chk->bind_param("i", $id);
        $chk->execute();
        $chk->bind_result($oldFile);
        $chk->fetch();
        $chk->close();

        $sql = "UPDATE recruiters_requirements 
                SET recruiter_id=?, job_title=?, num_employees=?, rate_pay=?, start_date=?, 
                    accommodation=?, transport=?, overtime=?";
        if ($attachmentName!='') {
            $sql .= ", attachment=?";
        }
        $sql .= " WHERE id=?";

        if ($attachmentName!='') {
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param(
                "isidsiiiss",
                $recruiter_id, $job_title, $num_employees, $rate_pay, $start_date,
                $accommodation, $transport, $overtime, $attachmentName, $id
            );
        } else {
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param(
                "isidsiiii",
                $recruiter_id, $job_title, $num_employees, $rate_pay, $start_date,
                $accommodation, $transport, $overtime, $id
            );
        }

        if ($stmt->execute()) {
            // delete old file if new one uploaded
            if ($attachmentName!='' && $oldFile && file_exists($uploadDir . $oldFile)) {
                unlink($uploadDir . $oldFile);
            }
            echo "success";

            $site->agent_log("Updated job requirement $job_title ($num_employees x $rate_pay) on date ".$start_date,$recruiter_id,'recruiter');

        } else {
            echo "error";
        }
        $stmt->close();
    } 
    /* ---------------- INSERT ---------------- */
    else {
        $stmt = $mysqli->prepare("
            INSERT INTO recruiters_requirements 
            (recruiter_id, job_title, num_employees, rate_pay, start_date, accommodation, transport, overtime, attachment, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "isidsiiiss",
            $recruiter_id, $job_title, $num_employees, $rate_pay, $start_date,
            $accommodation, $transport, $overtime, $attachmentName, $created_by
        );
        echo $stmt->execute() ? "success" : "error";
        $stmt->close();
    }
    exit;
}

/* =========================================================
   GET SINGLE REQUIREMENT
   ========================================================= */
if ($action === 'get') {
    $id = intval($_POST['id']);
    $stmt = $mysqli->prepare("SELECT * FROM recruiters_requirements WHERE id = ?");
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
    $stmt = $mysqli->prepare("DELETE FROM recruiters_requirements WHERE id = ?");
    $stmt->bind_param("i", $id);
    echo $stmt->execute() ? "success" : "error";
    $stmt->close();
    exit;
}
?>