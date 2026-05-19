<?php
require_once __DIR__ . '/includes/header.php';
require_login();

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf_token'];

if (!function_exists('esc')) {
  function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
}

$action = $_POST['action'] ?? null;
$msg = ''; $err = '';

$person_id_q = isset($_GET['person']) ? (int)$_GET['person'] : 0;

// ---------- Handle form actions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
  if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf) {
    $err = "Invalid CSRF token.";
  } else {
    $id = (int)($_POST['id'] ?? 0);
    $person_id = (int)($_POST['person_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $host = trim($_POST['host'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $port = (int)($_POST['port'] ?? 993);
    $smtp_secure = in_array($_POST['smtp_secure'] ?? 'ssl', ['ssl','tls','']) ? ($_POST['smtp_secure'] ?: '') : 'ssl';
    $folder_inbox = trim($_POST['folder_inbox'] ?? 'INBOX');
    $folder_sent = trim($_POST['folder_sent'] ?? 'Sent');
    $active = isset($_POST['active']) ? 1 : 0;

    $sent_host = trim($_POST['sent_host'] ?? '');
    $sent_username = trim($_POST['sent_username'] ?? '');
    $sent_password = trim($_POST['sent_password'] ?? '');
    $sent_port = (int)($_POST['sent_port'] ?? 993);
    $sent_smtp_secure = in_array($_POST['sent_smtp_secure'] ?? 'ssl', ['ssl','tls','']) ? ($_POST['sent_smtp_secure'] ?: '') : 'ssl';
    $sent_folder_inbox = trim($_POST['sent_folder_inbox'] ?? 'INBOX');

    if ($name === '' || $host === '' || $username === '') {
      $err = "Name, host and username are required.";
    } else {
      if ($action === 'create') {
        $stmt = $mysqli->prepare("INSERT INTO mailboxes
          (name, host, username, password, port, smtp_secure, folder_inbox, folder_sent,
           sent_host, sent_username, sent_password, sent_port, sent_smtp_secure, sent_folder_inbox,
           person_id, active, created_at)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('ssssissssssissii',
          $name, $host, $username, $password, $port, $smtp_secure, $folder_inbox, $folder_sent,
          $sent_host, $sent_username, $sent_password, $sent_port, $sent_smtp_secure, $sent_folder_inbox,
          $person_id, $active);
        $stmt->execute();
        if ($stmt->errno) $err = "DB error: " . $stmt->error; else $msg = "Mailbox created.";
        $stmt->close();
      } elseif ($action === 'update') {
        $stmt = $mysqli->prepare("UPDATE mailboxes SET
          name=?, host=?, username=?, password=?, port=?, smtp_secure=?, folder_inbox=?, folder_sent=?,
          sent_host=?, sent_username=?, sent_password=?, sent_port=?, sent_smtp_secure=?, sent_folder_inbox=?,
          person_id=?, active=? WHERE id=?");
        $stmt->bind_param('ssssissssssissiii',
          $name, $host, $username, $password, $port, $smtp_secure, $folder_inbox, $folder_sent,
          $sent_host, $sent_username, $sent_password, $sent_port, $sent_smtp_secure, $sent_folder_inbox,
          $person_id, $active, $id);
        $stmt->execute();
        if ($stmt->errno) $err = "DB error: " . $stmt->error; else $msg = "Mailbox updated.";
        $stmt->close();
      }
    }
  }
}

// ---------- Load mailbox for editing ----------
$editing = null;
if ($person_id_q) {
  $stmt = $mysqli->prepare("SELECT * FROM mailboxes WHERE person_id=? LIMIT 1");
  $stmt->bind_param('i', $person_id_q);
  $stmt->execute();
  $res = $stmt->get_result();
  $editing = $res->fetch_assoc();
  $stmt->close();
}

$edit_id = (int)($_GET['edit'] ?? 0);
if ($edit_id) {
  $stmt = $mysqli->prepare("SELECT * FROM mailboxes WHERE id=? LIMIT 1");
  $stmt->bind_param('i', $edit_id);
  $stmt->execute();
  $res = $stmt->get_result();
  $editing = $res->fetch_assoc();
  $stmt->close();
}

function person_name_by_id($mysqli, $pid) {
  $pid = (int)$pid;
  if (!$pid) return '';
  $r = $mysqli->query("SELECT name FROM people WHERE id={$pid} LIMIT 1")->fetch_assoc();
  return $r['name'] ?? '';
}
?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Mailbox Configuration</h1>
    <?php if ($person_id_q): ?>
      <a href="?page=users" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to Users
      </a>
    <?php endif; ?>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?=esc($msg)?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger"><?=esc($err)?></div><?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf_token" value="<?=esc($csrf)?>">
    <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
    <input type="hidden" name="id" value="<?= esc($editing['id'] ?? 0) ?>">
    <input type="hidden" name="person_id" value="<?= esc($person_id_q ?: ($editing['person_id'] ?? 0)) ?>">

    <!-- ========== Grid layout ========== -->
    <div class="row g-3">
      <!-- Column 1: Primary credentials -->
      <div class="col-md-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title mb-3">Primary Account</h5>

            <div class="mb-2">
              <label class="form-label">Friendly Name</label>
              <input name="name" class="form-control" value="<?=esc($editing['name'] ?? '')?>" required>
            </div>

            <div class="mb-2">
              <label class="form-label">IMAP Host</label>
              <input name="host" class="form-control" value="<?=esc($editing['host'] ?? '')?>" placeholder="imap.example.com" required>
            </div>

            <div class="mb-2">
              <label class="form-label">Username</label>
              <input name="username" class="form-control" value="<?=esc($editing['username'] ?? '')?>" required>
            </div>

            <div class="mb-2">
              <label class="form-label">Password</label>
              <input name="password" type="password" class="form-control" value="<?=esc($editing['password'] ?? '')?>">
            </div>

            <div class="row mb-2">
              <div class="col-6">
                <label class="form-label">Port</label>
                <input name="port" class="form-control" value="<?=esc($editing['port'] ?? 993)?>">
              </div>
              <div class="col-6">
                <label class="form-label">Secure</label>
                <?php $cur = $editing['smtp_secure'] ?? 'ssl'; ?>
                <select name="smtp_secure" class="form-select">
                  <option value="ssl" <?= $cur==='ssl'?'selected':'' ?>>SSL</option>
                  <option value="tls" <?= $cur==='tls'?'selected':'' ?>>TLS</option>
                  <option value="" <?= $cur===''?'selected':'' ?>>None</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Column 2: Folders and status -->
      <div class="col-md-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title mb-3">Folders & Settings</h5>

            <div class="mb-2">
              <label class="form-label">Folder - Inbox</label>
              <input name="folder_inbox" class="form-control" value="<?=esc($editing['folder_inbox'] ?? 'INBOX')?>">
            </div>

            <div class="mb-2">
              <label class="form-label">Folder - Sent</label>
              <input name="folder_sent" class="form-control" value="<?=esc($editing['folder_sent'] ?? 'Sent')?>">
            </div>

            <div class="mb-2">
              <label class="form-label">Active</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="active" value="1" id="activeCheck"
                  <?= (!isset($editing) || $editing['active']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="activeCheck">Enable this mailbox</label>
              </div>
            </div>

            <?php if ($person_id_q): ?>
              <div class="mt-3">
                <small class="text-muted">Linked to person:
                  <strong><?= esc(person_name_by_id($mysqli, $person_id_q)) ?></strong></small>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Column 3: Sent-account (second login) -->
      <div class="col-md-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title mb-3">Sent Account (2nd Login)</h5>
            <p class="text-muted small">Optional: use if sent messages are stored in another mailbox's INBOX.</p>

            <div class="mb-2">
              <label class="form-label">Sent Host</label>
              <input name="sent_host" class="form-control" value="<?=esc($editing['sent_host'] ?? '')?>">
            </div>
            <div class="mb-2">
              <label class="form-label">Sent Username</label>
              <input name="sent_username" class="form-control" value="<?=esc($editing['sent_username'] ?? '')?>">
            </div>
            <div class="mb-2">
              <label class="form-label">Sent Password</label>
              <input name="sent_password" type="password" class="form-control" value="<?=esc($editing['sent_password'] ?? '')?>">
            </div>

            <div class="row mb-2">
              <div class="col-6">
                <label class="form-label">Port</label>
                <input name="sent_port" class="form-control" value="<?=esc($editing['sent_port'] ?? 993)?>">
              </div>
              <div class="col-6">
                <label class="form-label">Secure</label>
                <?php $s_cur = $editing['sent_smtp_secure'] ?? 'ssl'; ?>
                <select name="sent_smtp_secure" class="form-select">
                  <option value="ssl" <?= $s_cur==='ssl'?'selected':'' ?>>SSL</option>
                  <option value="tls" <?= $s_cur==='tls'?'selected':'' ?>>TLS</option>
                  <option value="" <?= $s_cur===''?'selected':'' ?>>None</option>
                </select>
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label">Sent Folder to Read</label>
              <input name="sent_folder_inbox" class="form-control" value="<?=esc($editing['sent_folder_inbox'] ?? 'INBOX')?>">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Buttons -->
    <div class="mt-4 text-end">
      <button class="btn btn-primary" type="submit"><?= $editing ? 'Update Mailbox' : 'Create Mailbox' ?></button>
      <?php if ($person_id_q): ?>
        <a href="?page=users" class="btn btn-secondary">Back</a>
      <?php else: ?>
        <a href="?page=mailboxes" class="btn btn-secondary">Cancel</a>
      <?php endif; ?>
    </div>
  </form>
</div>
