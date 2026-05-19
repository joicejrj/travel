<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

$action = $_POST['action'] ?? '';

if ($action == 'load_editor') {
    $customer_id = intval($_POST['customer_id']);
    $jobs_html = '';
    $terms_html = '';

    /* -------------------------------------------------------
       1️⃣  LOAD JOB REQUIREMENTS FROM customers_requirements
    ------------------------------------------------------- */
    $q = $mysqli->prepare("SELECT * FROM customers_requirements WHERE customer_id = ? ORDER BY id ASC");
    $q->bind_param("i", $customer_id);
    $q->execute();
    $result = $q->get_result();
    $index = 1;

    $getjobs = $db->get('job_titles',array("#all"=>1,'#srt'=>'title asc'));
    $rno = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $rno++;
            $selected = function($value, $current) {
                return $value === $current ? 'selected' : '';
            };

            $jobs = "";
            foreach ($getjobs->data as $key => $job) {
              $jobs .= '<option value="'.$job->title.'" '.($selected($job->title,$row['job_title'])).'>'.$job->title.'</option>';
            }

            $jobs_html .= "
            <div class='card border shadow-sm p-3 mb-3 job-item bg-white rounded-3'>
              <div class='d-flex justify-content-between align-items-center mb-2'>
                <h6 class='fw-semibold mb-0 text-secondary'>Job {$index}</h6>
                <button type='button' class='btn btn-sm btn-outline-danger remove-job' title='Remove Job'>
                  <i class='fa fa-trash'></i>
                </button>
              </div>

              <div class='row g-3 align-items-center'>
                <div class='col-md-12'>
                  <label class='form-label small fw-semibold mb-1'>Job Title</label>
                  <select name='job_title[]' class='form-select form-select-sm jselect' id='jobselect".$rno."' required>
                    ".$jobs."
                  </select>
                </div>

                <div class='col-md-4'>
                  <label class='form-label small fw-semibold mb-1'>Rate (AED)</label>
                  <input type='number' step='0.01' name='rate_pay[]' value=\"{$row['rate_pay']}\" 
                         class='form-control form-control-sm' placeholder='Hourly Rate' required>
                </div>

                <div class='col-md-4'>
                  <label class='form-label small fw-semibold mb-1'>Number of Employees</label>
                  <div class='input-group input-group-sm'>
                    <button type='button' class='btn btn-outline-secondary emp-minus'>-</button>
                    <input type='number' name='num_employees[]' class='form-control form-control-sm text-center' min='1' value=\"{$row['num_employees']}\" required>
                    <button type='button' class='btn btn-outline-secondary emp-plus'>+</button>
                  </div>
                </div>

                <div class='col-md-4'>
                  <label class='form-label small fw-semibold mb-1'>Start Date</label>
                  <input type='date' name='start_date[]' value=\"{$row['start_date']}\" 
                         class='form-control form-control-sm'>
                </div>
              </div>
            </div>";
            $index++;
        }
    } else {
        $jobs_html = "
        <div class='alert alert-light border text-center small mb-0'>
          No job requirements found for this customer.<br>
          <button type='button' class='btn btn-outline-primary btn-sm mt-2' id='addJobBtnInline'>
            <i class='fa fa-plus'></i> Add Job
          </button>
        </div>";
    }

    /* -------------------------------------------------------
       2️⃣  LOAD DEFAULT TERMS (STATIC OR FROM quotation_terms)
    ------------------------------------------------------- */
    $terms = [
        ['Duration of Contract', '1 Year (extendable)'],
        ['VAT', 'Applicable 5% on Invoice Amount'],
        ['Accommodation, Food & Transportation', 'Provided by M/s Al Nasr General Services Est.'],
        ['Safety Equipment\'s', 'Basic safety items provided by M/s Al Nasr General Services Est.'],
        ['Working Hours', 'Daily work hours minimum 11 hours at 6 days a week.'],
        ['Payment of Invoice', 'Payment within 30 days upon receiving certified timesheets and invoice.'],
    ];

    foreach ($terms as $term) {
        [$title, $text] = $term;
        $terms_html .= "
        <div class='p-4 border rounded-lg bg-white mb-3 term-item shadow-sm'>
          <div class='d-flex justify-content-between align-items-center gap-2 mb-2'>
            <h6 class='fw-semibold text-secondary flex-grow-1 mb-0'>$title</h6>
            <input type='hidden' name='term_title[]' value='$title'>
            <button type='button' class='btn btn-sm btn-outline-danger remove-term'>
              <i class='fa fa-times'></i>
            </button>
          </div>
          <textarea name='term_text[]' class='form-control form-control-sm' rows='3'>$text</textarea>
        </div>";
    }

    echo json_encode([
        'jobs_html' => $jobs_html,
        'jobs_no' => $rno,
        'terms_html' => $terms_html
    ]);
    exit;
}
?>