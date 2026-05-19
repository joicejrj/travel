<?php
// temp debug helper — remove when done
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../mail/class.PHPMailer.php';

$success = null;
$error = null;

// fetch mailboxes if table exists
$mailboxes = [];
$mbq = $mysqli->query("SELECT id, name, username, host, port, smtp_secure FROM mailboxes WHERE active=1 ORDER BY id ASC");
if($mbq){ while($r = $mbq->fetch_assoc()) $mailboxes[] = $r; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mailbox_id = (int)($_POST['mailbox_id'] ?? 0);
    $to = trim($_POST['to'] ?? '');
    $cc = trim($_POST['cc'] ?? '');
    $bcc = trim($_POST['bcc'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $body_html = $_POST['message'] ?? '';
    $body_text = strip_tags($body_html);

    $upload_dir = __DIR__ . '/../storage/mail_uploads/';
    @mkdir($upload_dir, 0755, true);

    // mailbox defaults
    $host = 'mail.mediatel.com';
    $username = '';
    $password = '';
    $port = 465;
    $smtp_secure = 'ssl';
    $from_name = 'CRM Mailer';

    if ($mailbox_id) {
        $stmt = $mysqli->prepare("SELECT host, port, smtp_secure, username, password, name FROM mailboxes WHERE id=? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $mailbox_id);
            $stmt->execute();
            $stmt->bind_result($mh, $mp, $ms, $mu, $mpwd, $mname);
            if ($stmt->fetch()) {
                $host = $mh ?: $host;
                $port = $mp ?: $port;
                $smtp_secure = $ms ?: $smtp_secure;
                $username = $mu ?: $username;
                $password = $mpwd ?: $password;
                $from_name = $mname ?: $from_name;
            }
            $stmt->close();
        }
    }

    try {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->SMTPSecure = $smtp_secure;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->setFrom($username ?: 'noreply@local', $from_name);

        foreach (preg_split('/\s*[;,]+\s*/', $to, -1, PREG_SPLIT_NO_EMPTY) as $addr) {
            $mail->addAddress(trim($addr));
        }
        if (!empty($cc)) foreach (preg_split('/\s*[;,]+\s*/', $cc, -1, PREG_SPLIT_NO_EMPTY) as $addr) $mail->addCC(trim($addr));
        if (!empty($bcc)) foreach (preg_split('/\s*[;,]+\s*/', $bcc, -1, PREG_SPLIT_NO_EMPTY) as $addr) $mail->addBCC(trim($addr));

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body_html;
        $mail->AltBody = $body_text;

        $saved_attachments = [];
        if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
            for ($i=0;$i<count($_FILES['attachments']['name']);$i++){
                if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $orig = basename($_FILES['attachments']['name'][$i]);
                $uniq = time().'_'.bin2hex(random_bytes(6)).'_'.preg_replace('/[^a-zA-Z0-9._-]/','_',$orig);
                $dest = $upload_dir . $uniq;
                if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], $dest)) {
                    $mail->addAttachment($dest, $orig);
                    $saved_attachments[] = ['filename'=>$orig, 'path'=>$dest, 'size'=>filesize($dest)];
                }
            }
        }

        $ok = $mail->send();
        $message_id = method_exists($mail, 'getLastMessageID') ? $mail->getLastMessageID() : ('<' . time() . '.' . rand(1000,9999) . '@crm.local>');
        $from_address = $mail->From ?: $username;
        $from_name = $mail->FromName ?: $from_name;

        $stmt = $mysqli->prepare("INSERT INTO email_log
            (mailbox_id, folder, message_id, subject, from_name, from_email, to_emails, cc, bcc, body_html, body_text, is_sent, sent_via, created_at)
            VALUES (?, 'Sent', ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'mailer', NOW())");
        if ($stmt) {
            $to_field = $to;
            $cc_field = $cc;
            $bcc_field = $bcc;
            $stmt->bind_param('isssssssss', $mailbox_id, $message_id, $subject, $from_name, $from_address, $to_field, $cc_field, $bcc_field, $body_html, $body_text);
            $stmt->execute();
            $email_id = $stmt->insert_id;
            $stmt->close();

            if (!empty($saved_attachments)) {
                $att_stmt = $mysqli->prepare("INSERT INTO email_attachments (email_id, filename, path, size, mime, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                foreach ($saved_attachments as $sa) {
                    $mime = function_exists('mime_content_type') ? mime_content_type($sa['path']) : '';
                    $att_stmt->bind_param('issis', $email_id, $sa['filename'], $sa['path'], $sa['size'], $mime);
                    $att_stmt->execute();
                }
                $att_stmt->close();
            }
        }

        if ($ok) $success = "Message sent and saved (email id: {$email_id}).";
        else $error = "Message saved but mailer returned failure.";
    } catch (Exception $ex) {
        $error = $ex->getMessage();
    }
}
?>
<div class="container">
  <h1 class="h3 mb-3">Compose Mail</h1>
  <?php if ($success): ?><div class="alert alert-success"><?=esc($success)?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?=esc($error)?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label>From mailbox</label>
      <select name="mailbox_id" class="form-select">
        <option value="0">Default</option>
        <?php foreach ($mailboxes as $m): ?>
          <option value="<?=esc($m['id'])?>"><?=esc($m['name'].' <'.$m['username'].'>')?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3"><label>To</label><input name="to" class="form-control" required></div>
    <div class="mb-3"><label>CC</label><input name="cc" class="form-control"></div>
    <div class="mb-3"><label>BCC</label><input name="bcc" class="form-control"></div>
    <div class="mb-3"><label>Subject</label><input name="subject" class="form-control"></div>
    <div class="mb-3"><label>Message (HTML)</label><textarea name="message" rows="10" class="form-control"></textarea></div>
    <div class="mb-3">
      <label>Attachments</label>
      <input type="file" name="attachments[]" multiple class="form-control">
    </div>
    <div class="mb-3">
      <button class="btn btn-primary" type="submit">Send</button>
    </div>
  </form>
</div>
