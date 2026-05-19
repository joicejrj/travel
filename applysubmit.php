<?php
// submit.php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

define('UPLOAD_DIR', __DIR__ . '/uploads'); // ideally outside webroot
define('MAX_CV_MB', 10);
define('MAX_DOC_MB', 10);

function ensure_upload_dir(): void {
  if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
  }
}
ensure_upload_dir();

function bad_request(string $msg): void {
  $_SESSION['form_error'] = $msg;
  $_SESSION['old'] = $_POST;
  header("Location: apply.php");
  exit;
}

function normalize_mobile(string $mobile): string {
  $m = trim($mobile);
  $m = preg_replace('/\s+/', '', $m);
  // Convert UAE local 05xxxxxxxx to +9715xxxxxxxx
  if (preg_match('/^05\d{8}$/', $m)) {
    $m = '+971' . substr($m, 1);
  }
  // If starts with 971 without +, add +
  if (preg_match('/^971\d+$/', $m)) {
    $m = '+' . $m;
  }
  return $m;
}

function move_upload(array $file, int $maxMB, array $allowedExt, string $prefix): array {
  if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
    bad_request("File upload failed.");
  }

  $size = (int)$file['size'];
  if ($size <= 0 || $size > $maxMB * 1024 * 1024) {
    bad_request("File size exceeds limit ({$maxMB}MB).");
  }

  $orig = $file['name'] ?? 'file';
  $origLower = strtolower($orig);

  $extOk = false;
  foreach ($allowedExt as $ext) {
    if (str_ends_with($origLower, $ext)) { $extOk = true; break; }
  }
  if (!$extOk) {
    bad_request("Invalid file type for {$orig}.");
  }

  $tmp = $file['tmp_name'];
  $mime = mime_content_type($tmp) ?: 'application/octet-stream';

  $stored = $prefix . '_' . bin2hex(random_bytes(10)) . '_' . time() . '.' . pathinfo($origLower, PATHINFO_EXTENSION);
  $path = UPLOAD_DIR . '/' . $stored;

  if (!move_uploaded_file($tmp, $path)) {
    bad_request("Failed to save uploaded file.");
  }

  $sha256 = hash_file('sha256', $path);

  return [
    'original_filename' => $orig,
    'stored_filename' => $stored,
    'file_path' => 'uploads/' . $stored, // relative
    'mime_type' => $mime,
    'size_bytes' => $size,
    'sha256' => $sha256
  ];
}

if(!isset($_SESSION['apprand']) || !isset($_POST['apprand']) || (isset($_POST['apprand']) && $_POST['apprand']!=$_SESSION['apprand']) ) {
  bad_request("Session time out. Please try again");
}
unset($_SESSION['apprand']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['g-recaptcha-response'])) {
        // $_SESSION['form_error'] = 'Please verify that you are not a robot.';
        // header('Location: apply.php');
        // exit;
        bad_request("Please verify that you are not a robot.");
    }

    $secret = '6LfECkIsAAAAAJEDx5oURCspIcylD1hdnsLQznLr';
    $response = $_POST['g-recaptcha-response'];

    $verify = file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}"
    );

    $captcha = json_decode($verify);

    if (!$captcha->success) {
        // $_SESSION['form_error'] = 'reCAPTCHA verification failed. Please try again.';
        // header('Location: apply.php');
        // exit;
        bad_request("reCAPTCHA verification failed. Please try again.");
    }
}

// ---------- Collect and validate fields ----------
$full_name = trim($_POST['full_name'] ?? '');
$mobile = normalize_mobile($_POST['mobile'] ?? '');
$email = trim($_POST['email'] ?? '');
$nationality = trim($_POST['nationality'] ?? '');
$current_location = $_POST['current_location'] ?? '';
$city = trim($_POST['city'] ?? '');

$position_category = trim($_POST['position_category'] ?? '');
$other_position = trim($_POST['other_position'] ?? '');
$years_experience = $_POST['years_experience'] ?? null;

$preferred_work_location = trim($_POST['preferred_work_location'] ?? '');
$availability = $_POST['availability'] ?? '';
$visa_status = trim($_POST['visa_status'] ?? '');
$notice_period = trim($_POST['notice_period'] ?? '');

$expected_salary_aed = $_POST['expected_salary_aed'] ?? null;
$communication_preference = $_POST['communication_preference'] ?? null;
$consent = isset($_POST['consent']) ? 1 : 0;

$lead_source = $_POST['lead_source'] ?? 'Website';
$allowedSources = ['WhatsApp','Email','Phone','Walk-in','Website','Other'];
if (!in_array($lead_source, $allowedSources, true)) $lead_source = 'Website';

if (mb_strlen($full_name) < 3) bad_request("Full name is required.");
if ($mobile === '' || mb_strlen($mobile) < 8) bad_request("Valid mobile number is required.");
if ($nationality === '') bad_request("Nationality is required.");
if (!in_array($current_location, ['UAE','Outside UAE'], true)) bad_request("Current location is required.");
if ($city === '') bad_request("City is required.");
if ($position_category === '') bad_request("Position is required.");
if ($position_category === 'Other' && $other_position === '') bad_request("Other position is required.");
if ($availability === '') bad_request("Availability is required.");
if ($consent !== 1) bad_request("Consent is required.");

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  bad_request("Invalid email address.");
}

if ($current_location === 'UAE' && $visa_status === '') {
  bad_request("Visa status is required if location is UAE.");
}

if ($years_experience !== null && $years_experience !== '') {
  $years_experience = (int)$years_experience;
  if ($years_experience < 0 || $years_experience > 40) bad_request("Years of experience must be between 0 and 40.");
} else {
  $years_experience = null;
}

if ($expected_salary_aed !== null && $expected_salary_aed !== '') {
  $expected_salary_aed = (int)$expected_salary_aed;
  if ($expected_salary_aed < 500 || $expected_salary_aed > 30000) bad_request("Expected salary out of range.");
} else {
  $expected_salary_aed = null;
}

$allowedComm = ['WhatsApp','Phone','Email'];
if ($communication_preference !== null && $communication_preference !== '' && !in_array($communication_preference, $allowedComm, true)) {
  $communication_preference = null;
}

// ---------- Validate and store files ----------
if (!isset($_FILES['cv_file'])) bad_request("CV file is required.");
$cvMeta = move_upload($_FILES['cv_file'], MAX_CV_MB, ['.pdf','.doc','.docx'], 'CV');

// Extra docs optional
$extraDocs = [];
if (isset($_FILES['extra_docs']) && is_array($_FILES['extra_docs']['name'])) {
  $count = count($_FILES['extra_docs']['name']);
  for ($i = 0; $i < $count; $i++) {
    if ($_FILES['extra_docs']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
    $file = [
      'name' => $_FILES['extra_docs']['name'][$i],
      'type' => $_FILES['extra_docs']['type'][$i] ?? '',
      'tmp_name' => $_FILES['extra_docs']['tmp_name'][$i],
      'error' => $_FILES['extra_docs']['error'][$i],
      'size' => $_FILES['extra_docs']['size'][$i],
    ];
    $extraDocs[] = move_upload($file, MAX_DOC_MB, ['.pdf','.jpg','.jpeg','.png'], 'DOC');
  }
}

// ---------- Insert into DB ----------
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

$mysqli->begin_transaction();

try {

  $email = $email !== '' ? $email : null;
  $other_position = ($position_category === 'Other') ? $other_position : null;
  $preferred_work_location = $preferred_work_location !== '' ? $preferred_work_location : null;
  $visa_status = $visa_status !== '' ? $visa_status : null;
  $notice_period = $notice_period !== '' ? $notice_period : null;
  $communication_preference = $communication_preference !== '' ? $communication_preference : null;
  $ip = $ip ?: null;
  $ua = $ua ? mb_substr($ua, 0, 255) : null;

  $stmt = $mysqli->prepare("
    INSERT INTO applicants (
      full_name, mobile, email, nationality, current_location, city,
      position_category, other_position, years_experience, preferred_work_location,
      availability, visa_status, notice_period, expected_salary_aed,
      communication_preference, consent, lead_source, status, ip_address, user_agent
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'CV_RECEIVED',?,?)
  ");

  $stmt->bind_param(
    "ssssssssissssisisss",
    $full_name,
    $mobile,
    $email,
    $nationality,
    $current_location,
    $city,
    $position_category,
    $other_position,
    $years_experience,
    $preferred_work_location,
    $availability,
    $visa_status,
    $notice_period,
    $expected_salary_aed,
    $communication_preference,
    $consent,
    $lead_source,
    $ip,
    $ua
  );

  $stmt->execute();
  $applicantId = $stmt->insert_id;
  $stmt->close();

  // Reference number
  // $ref = 'AN-' . date('Ymd') . '-' . str_pad((string)$applicantId, 6, '0', STR_PAD_LEFT);
  $ref = date('ymd').str_pad((string)$applicantId, 3, '0', STR_PAD_LEFT);

  $stmt = $mysqli->prepare("UPDATE applicants SET ref_no=? WHERE id=?");
  $stmt->bind_param("si", $ref, $applicantId);
  $stmt->execute();
  $stmt->close();

  // Documents
  $stmt = $mysqli->prepare("
    INSERT INTO applicant_documents
    (applicant_id, doc_type, doc_label, original_filename, stored_filename,
     file_path, mime_type, size_bytes, sha256)
    VALUES (?,?,?,?,?,?,?,?,?)
  ");

  // CV
  $docType = 'CV';
  $docLabel = 'CV';
  $stmt->bind_param(
    "issssssss",
    $applicantId,
    $docType,
    $docLabel,
    $cvMeta['original_filename'],
    $cvMeta['stored_filename'],
    $cvMeta['file_path'],
    $cvMeta['mime_type'],
    $cvMeta['size_bytes'],
    $cvMeta['sha256']
  );
  $stmt->execute();

  // Extra docs
  foreach ($extraDocs as $m) {
    $docType = 'OTHER';
    $docLabel = 'Additional Document';

    $stmt->bind_param(
      "issssssss",
      $applicantId,
      $docType,
      $docLabel,
      $m['original_filename'],
      $m['stored_filename'],
      $m['file_path'],
      $m['mime_type'],
      $m['size_bytes'],
      $m['sha256']
    );
    $stmt->execute();
  }

  $stmt->close();
  $mysqli->commit();

  // add to timeline
  $stmtt = $mysqli->prepare("
    INSERT INTO applicant_activity_logs
    (applicant_id, activity_type, title, details)
    VALUES (?,?,?,?)
  ");
  $activityType = 'CREATED';
  $title        = 'Application submitted';
  $details      = 'Lead source: ' . $lead_source;
  $stmtt->bind_param("isss", $applicantId, $activityType, $title, $details);
  $stmtt->execute();
  $stmtt->close();

  $stmtt1 = $mysqli->prepare("
    INSERT INTO applicant_activity_logs
    (applicant_id, activity_type, title, details)
    VALUES (?,?,?,?)
  ");
  $activityType = 'DOC_UPLOADED';
  $title        = 'CV uploaded';
  $details      = 'While application submission';
  $stmtt1->bind_param("isss", $applicantId, $activityType, $title, $details);
  $stmtt1->execute();
  $stmtt1->close();

} catch (Throwable $e) {
  $mysqli->rollback();
  bad_request("DB Error: " . $e->getMessage());
}
// ---------- Success page ----------
unset($_SESSION['form_error'], $_SESSION['old']);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Application Submitted</title>
<style>
body{
  font-family:Inter,Arial,sans-serif;
  background:#f3f4f6;
  margin:0;
}
.box{
  max-width:520px;
  margin:60px auto;
  background:#fff;
  padding:28px;
  border-radius:18px;
  box-shadow:0 12px 30px rgba(0,0,0,.08);
  text-align:center;
}
.check{
  width:64px;
  height:64px;
  border-radius:50%;
  background:#dcfce7;
  color:#166534;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:32px;
  margin:0 auto 16px;
}
.ref{
  background:#f1f5f9;
  padding:10px;
  border-radius:10px;
  margin:12px 0;
  font-size:15px;
}
.btn{
  display:inline-block;
  margin-top:18px;
  padding:12px 18px;
  border-radius:12px;
  background:#2563eb;
  color:#fff;
  text-decoration:none;
  font-size:14px;
}
</style>
</head>
<body>

<div class="box">
  <div class="check">✓</div>
  <h2>Application Submitted</h2>
  <p>Thank you for applying. Your details have been received successfully.</p>

  <div class="ref">
    <strong>Reference Number:</strong><br>
    <?= htmlspecialchars($ref) ?>
  </div>

  <p style="font-size:14px;color:#6b7280">
    Our recruitment team will review your application and contact you if shortlisted.
  </p>

  <a class="btn" href="apply.php">Submit Another Application</a>
</div>

</body>
</html>