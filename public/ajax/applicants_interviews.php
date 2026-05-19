<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

/* -----------------------
   Helpers
------------------------ */
function timeline($mysqli, $aid, $title, $details=null) {
  $type='INTERVIEW_SCHEDULED';
  $stmt=$mysqli->prepare("
    INSERT INTO applicant_activity_logs
    (applicant_id, activity_type, title, details)
    VALUES (?,?,?,?)
  ");
  $stmt->bind_param("isss",$aid,$type,$title,$details);
  $stmt->execute();
  $stmt->close();
}

function reminder($mysqli,$aid,$at,$note){
  $type='Meeting';
  $stmt=$mysqli->prepare("
    INSERT INTO applicants_reminders
    (applicant_id, reminder_at, type, note)
    VALUES (?,?,?,?)
  ");
  $stmt->bind_param("isss",$aid,$at,$type,$note);
  $stmt->execute();
  $stmt->close();
}

/* -----------------------
   ACTIONS
------------------------ */
switch ($action) {

  case 'schedule':

    $aid = (int)$_POST['applicant_id'];
    $at  = $_POST['interview_at'];
    $mode = $_POST['mode'];
    $rem  = (int)$_POST['reminder_minutes'];
    $loc  = $_POST['location'] ?? null;
    $ivw  = $_POST['interviewer'] ?? null;
    $note = $_POST['notes'] ?? null;

    if (!$aid || !$at || !$mode) {
      echo json_encode(['status'=>false,'msg'=>'Missing data']); exit;
    }

    // Insert interview
    $stmt=$mysqli->prepare("
      INSERT INTO applicants_interviews
      (applicant_id, interview_at, mode, location, interviewer, notes)
      VALUES (?,?,?,?,?,?)
    ");
    $stmt->bind_param("isssss",$aid,$at,$mode,$loc,$ivw,$note);
    $stmt->execute();
    $stmt->close();

    // Reminder
    if ($rem>0) {
      $rAt = date('Y-m-d H:i:s', strtotime("$at -$rem minutes"));
      reminder($mysqli,$aid,$rAt,"Interview reminder ($mode)");
    }

    // Timeline
    timeline(
      $mysqli,
      $aid,
      "Interview scheduled",
      "$mode interview at ".date('d M Y H:i', strtotime($at))
    );

    echo json_encode(['status'=>true]);
    break;

  case 'list':

    $aid=(int)$_POST['applicant_id'];

    $res=$mysqli->query("
      SELECT *,
      CASE status
        WHEN 'SCHEDULED' THEN 'warning'
        WHEN 'COMPLETED' THEN 'success'
        ELSE 'danger'
      END AS status_color
      FROM applicants_interviews
      WHERE applicant_id=$aid
      ORDER BY interview_at DESC
    ");

    $rows=[];
    $active=false;

    while($r=$res->fetch_assoc()){
      if($r['status']==='SCHEDULED') $active=true;
      $r['interview_at']=date('d M Y H:i',strtotime($r['interview_at']));
      $rows[]=$r;
    }

    echo json_encode([
      'status'=>true,
      'data'=>$rows,
      'has_active'=>$active
    ]);
    break;

  case 'status':

    $iid=(int)$_POST['interview_id'];
    $st = $_POST['status'];

    if (!in_array($st,['COMPLETED','CANCELLED'],true)) {
      echo json_encode(['status'=>false]); exit;
    }

    $stmt=$mysqli->prepare("
      UPDATE applicants_interviews
      SET status=?
      WHERE id=?
    ");
    $stmt->bind_param("si",$st,$iid);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['status'=>true]);
    break;

  default:
    echo json_encode(['status'=>false,'msg'=>'Invalid action']);
}
?>