<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

$action = $_POST['action'] ?? '';

/* =====================================================
   1️⃣ LIST QUOTATIONS (Latest + Older Versions)
===================================================== */
if ($action === 'list_old') {
    $customer_id = intval($_POST['customer_id'] ?? 0);

    $stmt = $mysqli->prepare("
        SELECT * FROM customers_quotations 
        WHERE customer_id = ? 
        ORDER BY id DESC,version DESC
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo json_encode(['latest' => null, 'others' => []]);
        exit;
    }

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            'id' => $r['id'],
            'customer_id' => $r['customer_id'],
            'version' => 'v' . intval($r['version']),
            'ref_no' => $r['ref_no'],
            'quotation_name' => $r['quotation_name']?$r['quotation_name']:'-',
            'quotation_date' => $r['quotation_date'] ? date('d M Y', strtotime($r['quotation_date'])) : '-',
            'status' => ucfirst($r['status'] ?? 'draft'),
            'created_by' => $r['created_by'] ?? 'Admin',
            'file_name' => "Quotation_{$r['ref_no']}.pdf"
        ];
    }

    echo json_encode([
        'latest' => $rows[0],
        'others' => array_slice($rows, 1)
    ]);
    exit;
}

/* =====================================================
   1️⃣ LIST QUOTATIONS (Grouped by ref_no)
   Shows only the latest version of each quotation
   + Adds version count info
===================================================== */
if ($action === 'list') {
    $customer_id = intval($_POST['customer_id'] ?? 0);

    $stmt = $mysqli->prepare("
        SELECT q1.*, 
               (SELECT COUNT(*) 
                FROM customers_quotations qx 
                WHERE qx.customer_id = q1.customer_id 
                  AND qx.ref_no = q1.ref_no) AS version_count
        FROM customers_quotations q1
        INNER JOIN (
            SELECT ref_no, MAX(version) AS max_ver
            FROM customers_quotations
            WHERE customer_id = ?
            GROUP BY ref_no
        ) q2 
        ON q1.ref_no = q2.ref_no AND q1.version = q2.max_ver
        WHERE q1.customer_id = ?
        ORDER BY q1.id DESC, q1.ref_no DESC
    ");
    $stmt->bind_param("ii", $customer_id, $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo json_encode(['latest' => null, 'others' => []]);
        exit;
    }

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            'id' => $r['id'],
            'customer_id' => $r['customer_id'],
            'version' => 'v' . intval($r['version']),
            'ref_no' => $r['ref_no'],
            'quotation_name' => $r['quotation_name'] ?: '-',
            'quotation_date' => $r['quotation_date'] ? date('d M Y', strtotime($r['quotation_date'])) : '-',
            'status' => ucfirst($r['status'] ?? 'Draft'),
            'created_by' => $r['created_by'] ?? 'Admin',
            'file_name' => "Quotation_{$r['ref_no']}.pdf",
            'has_versions' => intval($r['version_count']) > 1,
            'version_count' => intval($r['version_count'])
        ];
    }

    echo json_encode([
        'latest' => $rows[0],
        'others' => array_slice($rows, 1)
    ]);
    exit;
}

/* =====================================================
   2️⃣ GET VERSIONS (Fetch all versions by ref_no)
===================================================== */
if ($action === 'get_versions') {
    $ref_no = trim($_POST['ref_no'] ?? '');
    $customer_id = intval($_POST['customer_id'] ?? 0);

    if ($ref_no === '') {
        echo json_encode(['error' => 'Missing reference number']);
        exit;
    }

    $stmt = $mysqli->prepare("
        SELECT * FROM customers_quotations
        WHERE customer_id = ? AND ref_no = ?
        ORDER BY version DESC
    ");
    $stmt->bind_param("is", $customer_id, $ref_no);
    $stmt->execute();
    $res = $stmt->get_result();

    $versions = [];
    while ($r = $res->fetch_assoc()) {
        $versions[] = [
            'id' => $r['id'],
            'version' => 'v' . intval($r['version']),
            'quotation_name' => $r['quotation_name'] ?: '-',
            'quotation_date' => $r['quotation_date'] ? date('d M Y', strtotime($r['quotation_date'])) : '-',
            'status' => ucfirst($r['status'] ?? 'Draft'),
            'created_by' => $r['created_by'] ?? 'Admin',
            'file_name' => "Quotation_{$r['ref_no']}.pdf"
        ];
    }

    echo json_encode(['ref_no' => $ref_no, 'versions' => $versions]);
    exit;
}

/* =====================================================
   2️⃣ SAVE NEW QUOTATION (Full Data + JSON)
===================================================== */
if ($action === 'save') {
    $customer_id = intval($_POST['customer_id'] ?? 0);
    $q_id = intval($_POST['id'] ?? 0);
    $is_new = $q_id>0?false:true;
    $quotation_date = $_POST['quotation_date'] ?? date('Y-m-d');
    $attention = trim($_POST['attention']);
    $quotation_name = trim($_POST['quotation_name']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $closing = trim($_POST['closing']);
    $created_by = $_SESSION['person_name'] ?? 'Admin';

    /* --- Build job requirements JSON --- */
    $jobs = [];
    if (!empty($_POST['job_title'])) {
        foreach ($_POST['job_title'] as $i => $title) {
            $jobs[] = [
                'job_title' => $title,
                'num_employees' => $_POST['num_employees'][$i] ?? '',
                'rate_pay' => $_POST['rate_pay'][$i] ?? '',
                'start_date' => $_POST['start_date'][$i] ?? ''
            ];
        }
    }

    /* --- Build terms JSON --- */
    $terms = [];
    if (!empty($_POST['term_title'])) {
        foreach ($_POST['term_title'] as $i => $t) {
            $terms[] = [
                'title' => $t,
                'text' => $_POST['term_text'][$i] ?? ''
            ];
        }
    }

    $jobs_json = json_encode($jobs, JSON_UNESCAPED_UNICODE);
    $terms_json = json_encode($terms, JSON_UNESCAPED_UNICODE);

    $qextra = "";
    if(!$is_new) {
        $qextra = " and ref_no in (select ref_no from customers_quotations WHERE id=$q_id)";
    }

    /* --- Get next version number --- */
    $ver_stmt = $mysqli->prepare("SELECT MAX(version) AS max_ver,ref_no FROM customers_quotations WHERE customer_id=?".$qextra);
    $ver_stmt->bind_param("i", $customer_id);
    $ver_stmt->execute();
    $ver_res = $ver_stmt->get_result()->fetch_assoc();
    $next_version = intval($ver_res['max_ver'] ?? 0) + 1;

    /* --- Keep same ref_no if exists and editing quotation, else generate new --- */
    if (!empty($ver_res['ref_no']) && !$is_new) {
        $ref_no = $ver_res['ref_no']; // keep same reference for all versions
    } else {
        $today = date('ymd');
        $ref_no = 'QTN' . $today . $customer_id . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $next_version = 1;
    }

    /* --- Insert quotation --- */
    $stmt = $mysqli->prepare("
        INSERT INTO customers_quotations
        (customer_id, ref_no, version, quotation_name, quotation_date, attention, subject, message, jobs_json, terms_json, closing, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "isisssssssss",
        $customer_id,
        $ref_no,
        $next_version,
        $quotation_name,
        $quotation_date,
        $attention,
        $subject,
        $message,
        $jobs_json,
        $terms_json,
        $closing,
        $created_by
    );

    $succe = $stmt->execute();
    if($succe) {
        $site->agent_log("Quotation ref #$ref_no (v$next_version) created",$customer_id);
    }
    
    echo $succe ? 'success' : $mysqli->error;


    $stmt->close();
    exit;
}

/* =====================================================
   3️⃣ GET SINGLE QUOTATION (With Decoded JSON)
===================================================== */
if ($action === 'get') {
    $id = intval($_POST['id']);
    $stmt = $mysqli->prepare("SELECT * FROM customers_quotations WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    // Decode jobs and terms JSON
    $res['jobs'] = json_decode($res['jobs_json'], true) ?? [];
    $res['terms'] = json_decode($res['terms_json'], true) ?? [];

    $getjobs = $db->get('job_titles',array("#all"=>1,'#srt'=>'title asc'));
    $rno = 0;

    // Prepare HTML for UI (reuse in modal)
    $jobs_html = '';
    $i = 1;
    foreach ($res['jobs'] as $job) {
        $rno++;

        $selected = function($value, $current) {
            return $value === $current ? 'selected' : '';
        };
        $jobs = "";
        foreach ($getjobs->data as $key => $job1) {
          $jobs .= '<option value="'.$job1->title.'" '.($selected($job1->title,$job['job_title'])).'>'.$job1->title.'</option>';
        }

        $jobs_html .= "
        <div class='card border p-2 mb-2 job-item'>
          <div class='d-flex justify-content-between align-items-center mb-2'>
            <h6 class='fw-semibold mb-0 text-primary'>Job {$i}</h6>
            <button type='button' class='btn btn-sm btn-light border text-danger remove-job'><i class='fa fa-trash'></i></button>
          </div>
          <div class='row g-2'>
            <div class='col-md-12'>
              <label class='form-label small fw-semibold'>Job Title</label>
              <label class='form-label small fw-semibold mb-1'>Job Title</label>
              <select name='job_title[]' class='form-select form-select-sm jselect' id='jobselect".$rno."' required>
                ".$jobs."
              </select>
            </div>
            <div class='col-md-4'>
              <label class='form-label small fw-semibold'>Employees</label>
              <input type='number' name='num_employees[]' value='{$job['num_employees']}' class='form-control form-control-sm'>
            </div>
            <div class='col-md-4'>
              <label class='form-label small fw-semibold'>Rate (AED)</label>
              <input type='number' step='0.01' name='rate_pay[]' value='{$job['rate_pay']}' class='form-control form-control-sm'>
            </div>
            <div class='col-md-4'>
              <label class='form-label small fw-semibold'>Start Date</label>
              <input type='date' name='start_date[]' value='{$job['start_date']}' class='form-control form-control-sm'>
            </div>
          </div>
        </div>";
        $i++;
    }

    $terms_html = '';
    foreach ($res['terms'] as $t) {
        $title = htmlspecialchars($t['title']);
        $text = htmlspecialchars($t['text']);
        $terms_html .= "
        <div class='card border p-2 mb-2 term-item'>
          <div class='d-flex justify-content-between align-items-center mb-2'>
            <input type='text' name='term_title[]' value='{$title}' class='form-control form-control-sm me-2' style='max-width:300px;'>
            <button type='button' class='btn btn-sm btn-light border text-danger remove-term'><i class='fa fa-trash'></i></button>
          </div>
          <textarea name='term_text[]' rows='2' class='form-control form-control-sm'>{$text}</textarea>
        </div>";
    }

    $res['jobs_html'] = $jobs_html;
    $res['jobs_no'] = $rno;
    $res['terms_html'] = $terms_html;

    echo json_encode($res);
    exit;
}

/* =====================================================
   4️⃣ CLONE QUOTATION (Copy JSON too)
===================================================== */
if ($action === 'clone') {
    $id = intval($_POST['id']);
    $res = $mysqli->query("SELECT * FROM customers_quotations WHERE id=$id");
    if (!$res->num_rows) {
        echo "Quotation not found.";
        exit;
    }

    $r = $res->fetch_assoc();

    $ver_stmt = $mysqli->prepare("SELECT MAX(version) AS max_ver FROM customers_quotations WHERE customer_id=?");
    $ver_stmt->bind_param("i", $r['customer_id']);
    $ver_stmt->execute();
    $ver_res = $ver_stmt->get_result()->fetch_assoc();
    $next_version = intval($ver_res['max_ver'] ?? 0) + 1;

    $stmt = $mysqli->prepare("
        INSERT INTO customers_quotations
        (customer_id, ref_no, version, quotation_date, attention, subject, message, jobs_json, terms_json, closing, status, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)
    ");
    $stmt->bind_param(
        "isissssss",
        $r['customer_id'],
        $r['ref_no'],
        $next_version,
        $r['quotation_date'],
        $r['attention'],
        $r['subject'],
        $r['message'],
        $r['jobs_json'],
        $r['terms_json'],
        $r['closing'],
        $_SESSION['person_name'] ?? $r['created_by']
    );

    echo $stmt->execute() ? 'success' : $mysqli->error;
    $stmt->close();
    exit;
}
?>
