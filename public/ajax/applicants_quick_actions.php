<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$aid    = (int)($_POST['applicant_id'] ?? 0);

if (!$aid || $action === '') {
  echo json_encode(['status'=>false,'msg'=>'Invalid request']);
  exit;
}

$now = date('Y-m-d H:i:s');

/* -----------------------------
   Helper functions
------------------------------ */
function addTimeline($mysqli, $aid, $title, $details=null) {
  $stmt = $mysqli->prepare("
    INSERT INTO applicant_activity_logs
    (applicant_id, activity_type, title, details)
    VALUES (?,?,?,?)
  ");
  $type = 'NOTE';
  $stmt->bind_param("isss", $aid, $type, $title, $details);
  $stmt->execute();
  $stmt->close();
}

function addReminder($mysqli, $aid, $at, $type, $note) {
  $stmt = $mysqli->prepare("
    INSERT INTO applicants_reminders
    (applicant_id, reminder_at, type, note)
    VALUES (?,?,?,?)
  ");
  $stmt->bind_param("isss", $aid, $at, $type, $note);
  $stmt->execute();
  $stmt->close();
}

function changeStatus($mysqli, $aid, $new, $note=null) {

  // fetch old
  $old = null;
  $r = $mysqli->query("SELECT status FROM applicants WHERE id=$aid");
  if ($row = $r->fetch_row()) $old = $row[0];

  $stmt = $mysqli->prepare("UPDATE applicants SET status=? WHERE id=?");
  $stmt->bind_param("si", $new, $aid);
  $stmt->execute();
  $stmt->close();

  // history
  $stmt = $mysqli->prepare("
    INSERT INTO applicant_status_history
    (applicant_id, old_status, new_status, note)
    VALUES (?,?,?,?)
  ");
  $stmt->bind_param("isss", $aid, $old, $new, $note);
  $stmt->execute();
  $stmt->close();
}

$msg = "Quick Action Success";

/* -----------------------------
   ACTION MAP
------------------------------ */
switch ($action) {

  case 'CALL_NO_ANSWER':
    addTimeline($mysqli, $aid, 'Call attempted', 'No answer');
    $msg = "Marked as Call attempted, No answer";
    break;

  case 'CALL_ANSWERED':
    addTimeline($mysqli, $aid, 'Call connected', 'Candidate answered');
    $msg = "Marked as Call connected, Candidate answered";
    break;

  case 'CALL_CALLBACK_2H':
    $at = date('Y-m-d H:i:s', strtotime('+2 hours'));
    addReminder($mysqli, $aid, $at, 'Call', 'Callback candidate');
    addTimeline($mysqli, $aid, 'Callback scheduled', 'Callback in 2 hours');
    $msg = "Reminder is Set";
    break;

  case 'REM_CALL_TOM_10':
    $at = date('Y-m-d 10:00:00', strtotime('+1 day'));
    addReminder($mysqli, $aid, $at, 'Call', 'Call candidate');
    addTimeline($mysqli, $aid, 'Reminder set', 'Call tomorrow at 10:00');
    $msg = "Reminder is set";
    break;

  case 'REM_FOLLOWUP_2D_10':
    $at = date('Y-m-d 10:00:00', strtotime('+2 days'));
    addReminder($mysqli, $aid, $at, 'General', 'Follow-up');
    addTimeline($mysqli, $aid, 'Follow-up reminder', 'After 2 days');
    $msg = "Reminder is set";
    break;

  case 'REM_REQUEST_DOCS':
    addReminder($mysqli, $aid, date('Y-m-d H:i:s'), 'Email', 'Request documents');
    addTimeline($mysqli, $aid, 'Documents requested');
    $msg = "Reminder is set to request documents";
    break;

  case 'STATUS_SHORTLISTED':
    changeStatus($mysqli, $aid, 'SHORTLISTED');
    addTimeline($mysqli, $aid, 'Candidate shortlisted');
    $msg = "Marked as Candidate is shortlisted";
    break;

  case 'STATUS_OFFER_SENT':
    changeStatus($mysqli, $aid, 'OFFERED');
    addTimeline($mysqli, $aid, 'Offer sent to candidate');
    $msg = "Marked as Offer sent to candidate";
    break;

  case 'STATUS_JOINED':
    changeStatus($mysqli, $aid, 'JOINED');
    addTimeline($mysqli, $aid, 'Candidate joined');
    $msg = "Marked as Candidate joined";
    break;

  case 'STATUS_REJECTED':
    changeStatus($mysqli, $aid, 'REJECTED');
    addTimeline($mysqli, $aid, 'Candidate rejected');
    $msg = "Marked as Candidate rejected";
    break;

  default:
    echo json_encode(['status'=>false,'msg'=>'Unknown action']);
    exit;
}

echo json_encode(['status'=>true,'msg'=>$msg]);
?>