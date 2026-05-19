<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
// require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');


function fetch_attachments_for_email_log($db, $email_id){
  $out = [];
  if (empty($email_id)) return $out;
  $q = $db->prepare("SELECT id, filename, path, size, mime FROM email_attachments WHERE email_id = ? ORDER BY id ASC");
  if ($q) {
    $q->bind_param('i', $email_id);
    $q->execute();
    $res = $q->get_result();
    while ($a = $res->fetch_assoc()) $out[] = $a;
    $q->close();
  }
  return $out;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch single email
$email = $db->get('email_log', ['id' => $id]);

if ($email) {

    $attachs = [];

    $mailbox_id = isset($_SESSION['person_mailbox_id'])?$_SESSION['person_mailbox_id']:0;

    $attachments = fetch_attachments_for_email_log($mysqli, $id);
    foreach ($attachments as $a) {
      $attachs[] = [
        'id' => $a['id'],
        'filename' => $a['filename'],
        'size' => round($a['size'] / 1024, 1) . ' KB',
        // 'url' => 'index.php?page=download_attachment&src=email&id=' . $a['id']
        'url' => '../storage/mail_attachments/mb_'.$mailbox_id.'/'.basename($a['path']),
      ];
    }

    echo json_encode([
        'subject' => $email->subject,
        'from' => $email->from_email,
        'to' => $email->to_emails,
        'date' => date('d M Y h:i A', strtotime($email->created_at)),
        'body' => $email->body_text ?? '',
        'attachments' => $attachs,
    ]);
} else {
    echo json_encode(['error' => 'Email not found']);
}
?>