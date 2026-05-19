<?php
// public/email_view.php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/header.php';
require_login();
require_once __DIR__ . '/../config/db.php';

/* ----------------- Attachment helper functions ----------------- */
if (!function_exists('fetch_attachments_for_email_log')) {
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
}

/**
 * Fetch attachments for an inbound message.
 * Returns array of rows: ['id', 'filename', 'path', 'src'] where src = 'email' (email_attachments) or 'inbound' (inbound_attachments)
 */
if (!function_exists('fetch_inbound_attachments')) {
  function fetch_inbound_attachments($db, $inbound_id, $linked_email_log_id = null){
    $out = [];

    // 1) If linked email_log_id present, try email_attachments first
    if (!empty($linked_email_log_id)) {
      $q = $db->prepare("SELECT id, filename, path, size, mime FROM email_attachments WHERE email_id = ? ORDER BY id ASC");
      if ($q) {
        $q->bind_param('i', $linked_email_log_id);
        $q->execute();
        $res = $q->get_result();
        while($r = $res->fetch_assoc()) {
          $r['src'] = 'email';
          $out[] = $r;
        }
        $q->close();
        if (!empty($out)) return $out;
      }
    }

    // 2) inbound_attachments table (if exists)
    $chk = $db->query("SHOW TABLES LIKE 'inbound_attachments'");
    if ($chk && $chk->num_rows) {
      $q2 = $db->prepare("SELECT id, filename, path, size, mime FROM inbound_attachments WHERE inbound_email_id = ? ORDER BY id ASC");
      if ($q2) {
        $q2->bind_param('i', $inbound_id);
        $q2->execute();
        $res2 = $q2->get_result();
        while($r2 = $res2->fetch_assoc()) {
          $r2['src'] = 'inbound';
          $out[] = $r2;
        }
        $q2->close();
        if (!empty($out)) return $out;
      }
    }

    // 3) fallback: maybe inbound attachments were stored in email_attachments using inbound id
    $q3 = $db->prepare("SELECT id, filename, path, size, mime FROM email_attachments WHERE email_id = ? ORDER BY id ASC");
    if ($q3) {
      $q3->bind_param('i', $inbound_id);
      $q3->execute();
      $res3 = $q3->get_result();
      while($r3 = $res3->fetch_assoc()) {
        $r3['src'] = 'email';
        $out[] = $r3;
      }
      $q3->close();
    }

    return $out;
  }
}

/* ----------------- small helpers ----------------- */
function normalize_subject($s){
  $s = trim($s ?? '');
  $s = preg_replace('/^\s*(re|fwd|fw)\s*[:\-]+\s*/i','',$s);
  $s = preg_replace('/\s+/', ' ', $s);
  return mb_strtolower($s);
}

function safe_prepare($mysqli, $sql){
  $stmt = $mysqli->prepare($sql);
  if ($stmt === false) return false;
  return $stmt;
}

/* ---------- identify message ---------- */
$type = (isset($_GET['type']) && $_GET['type'] === 'sent') ? 'sent' : 'inbound';
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
  echo '<div class="alert alert-warning">Invalid id</div>';
  require_once __DIR__.'/includes/footer.php';
  exit;
}

/* ---------- load the main message (robust: inbound id OR inbound linked by email_log_id OR email_log fallback) ---------- */
$main = null;

if ($type === 'inbound') {

  // 1) Try inbound_emails by id (preferred)
  $stmt = safe_prepare($mysqli, "SELECT id, email_log_id, mailbox_id, created_at, sender, recipient, subject, body_text, body_html, message_id, in_reply_to, thread_id FROM inbound_emails WHERE id=? LIMIT 1");
  if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $main = $stmt->get_result()->fetch_assoc();
    $stmt->close();
  }

  // 2) If not found, maybe the passed id is actually an email_log id — try to find inbound_emails linked to that email_log_id
  if (!$main) {
    $stmt2 = safe_prepare($mysqli, "SELECT id, email_log_id, mailbox_id, created_at, sender, recipient, subject, body_text, body_html, message_id, in_reply_to, thread_id FROM inbound_emails WHERE email_log_id = ? LIMIT 1");
    if ($stmt2) {
      $stmt2->bind_param('i', $id);
      $stmt2->execute();
      $main = $stmt2->get_result()->fetch_assoc();
      $stmt2->close();
      if ($main) {
        // mark that original id was email_log id
        $main['via_email_log_id'] = $id;
      }
    }
  }

  // 3) If still not found, fallback: try to read email_log directly (best-effort)
  if (!$main) {
    $stmt3 = safe_prepare($mysqli, "SELECT id, mailbox_id, created_at, from_name AS sender, from_email, to_emails AS recipient, subject, body_html, body_text, message_id, in_reply_to FROM email_log WHERE id=? LIMIT 1");
    if ($stmt3) {
      $stmt3->bind_param('i', $id);
      $stmt3->execute();
      $row = $stmt3->get_result()->fetch_assoc();
      $stmt3->close();
      if ($row) {
        $main = [
          'id' => (int)$row['id'],
          'email_log_id' => (int)$row['id'],
          'mailbox_id' => $row['mailbox_id'] ?? null,
          'created_at' => $row['created_at'] ?? null,
          'sender' => $row['sender'] ?? ($row['from_email'] ?? ''),
          'recipient' => $row['recipient'] ?? ($row['to_emails'] ?? ''),
          'subject' => $row['subject'] ?? '',
          'body_html' => $row['body_html'] ?? ($row['body_text'] ?? ''),
          'body_text' => $row['body_text'] ?? '',
          'message_id' => $row['message_id'] ?? null,
          'in_reply_to' => $row['in_reply_to'] ?? null,
          'source_table' => 'email_log'
        ];
      }
    }
  }

} else {
  // Sent messages: read from email_log
  $stmt = safe_prepare($mysqli, "SELECT id, created_at, from_name AS sender, from_email, to_emails AS recipient, subject, body_html, body_text, message_id, in_reply_to, thread_id FROM email_log WHERE id=? LIMIT 1");
  if ($stmt === false) {
    $stmt = safe_prepare($mysqli, "SELECT id, created_at, from_name AS sender, from_email, to_emails AS recipient, subject, body_html, body_text, message_id FROM email_log WHERE id=? LIMIT 1");
  }
  if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $main = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($main) $main['source_table'] = 'email_log';
  }
}

// If still not found -> message not found
if (!$main) {
  echo '<div class="alert alert-warning">Message not found</div>';
  require_once __DIR__.'/includes/footer.php';
  exit;
}

/* Ensure keys exist to avoid undefined index notices */
if (!isset($main['message_id'])) $main['message_id'] = null;
if (!isset($main['in_reply_to'])) $main['in_reply_to'] = null;
if (!isset($main['thread_id'])) $main['thread_id'] = null;

/* ---------- THREAD collection with dedupe ---------- */

$thread_msgs = [];
$seen_keys = []; // dedupe map

function msg_key($row) {
  $mid = trim($row['message_id'] ?? '');
  if ($mid !== '') return 'mid:' . $mid;
  $body = $row['body_text'] ?? ($row['body_html'] ?? '');
  $sub = $row['subject'] ?? '';
  $date = $row['created_at'] ?? '';
  return 'hash:' . substr(md5($sub . '||' . strip_tags($body) . '||' . $date), 0, 16) . ':' . intval($row['id']);
}

function push_msg_dedupe(&$arr, $row, $source, &$seen) {
  $key = msg_key($row);
  if (isset($seen[$key])) return;
  $seen[$key] = true;
  $msg = [
    'source' => $source,
    'id' => (int)$row['id'],
    'created_at' => $row['created_at'] ?? ($row['date'] ?? null),
    'sender' => $row['sender'] ?? ($row['from_name'] ?? ($row['from_email'] ?? '')),
    'recipient' => $row['recipient'] ?? ($row['to_emails'] ?? ''),
    'subject' => $row['subject'] ?? '',
    'body_html' => $row['body_html'] ?? ($row['body_text'] ?? ''),
    'body_text' => $row['body_text'] ?? '',
    'message_id' => $row['message_id'] ?? null,
    // include email_log_id if present so attachments can be looked up
    'email_log_id' => isset($row['email_log_id']) ? (int)$row['email_log_id'] : (isset($row['id']) ? (int)$row['id'] : null)
  ];
  $arr[] = $msg;
}

// Threading: prefer message-id/in-reply-to/thread_id matches
$thread_key = trim($main['message_id'] ?? '');
$in_reply_to = trim($main['in_reply_to'] ?? '');
$thread_id = trim($main['thread_id'] ?? '');
$norm_sub = normalize_subject($main['subject'] ?? '');

$found_relationship = false;

if ($thread_key !== '') {
  // email_log replies & same thread
  $sql = "SELECT id, created_at, from_name AS sender, to_emails AS recipient, subject, body_html, body_text, message_id, in_reply_to, thread_id, NULL AS email_log_id
          FROM email_log WHERE in_reply_to=? OR thread_id=? OR message_id=? LIMIT 200";
  $stmt = safe_prepare($mysqli, $sql);
  if ($stmt) {
    $stmt->bind_param('sss', $thread_key, $thread_key, $thread_key);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
      // set email_log_id so push_msg_dedupe knows where attachments are
      $r['email_log_id'] = $r['id'];
      push_msg_dedupe($thread_msgs, $r, 'sent', $seen_keys);
      $found_relationship = true;
    }
    $stmt->close();
  }

  // inbound_emails replies & same thread
  $sql2 = "SELECT id, created_at, sender, recipient, subject, body_html, body_text, message_id, in_reply_to, thread_id, email_log_id
           FROM inbound_emails WHERE in_reply_to=? OR thread_id=? OR message_id=? LIMIT 200";
  $stmt2 = safe_prepare($mysqli, $sql2);
  if ($stmt2) {
    $stmt2->bind_param('sss', $thread_key, $thread_key, $thread_key);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($r = $res2->fetch_assoc()) {
      push_msg_dedupe($thread_msgs, $r, 'inbound', $seen_keys);
      $found_relationship = true;
    }
    $stmt2->close();
  }

  // If main is itself a reply, try to load parent(s)
  if (!empty($in_reply_to)) {
    $p1 = safe_prepare($mysqli, "SELECT id, created_at, from_name AS sender, to_emails AS recipient, subject, body_html, body_text, message_id FROM email_log WHERE message_id=? LIMIT 20");
    if ($p1) {
      $p1->bind_param('s', $in_reply_to);
      $p1->execute();
      $r = $p1->get_result();
      while ($row = $r->fetch_assoc()) {
        $row['email_log_id'] = $row['id'];
        push_msg_dedupe($thread_msgs, $row, 'sent', $seen_keys);
      }
      $p1->close();
    }
    $p2 = safe_prepare($mysqli, "SELECT id, created_at, sender, recipient, subject, body_html, body_text, message_id, email_log_id FROM inbound_emails WHERE message_id=? LIMIT 20");
    if ($p2) {
      $p2->bind_param('s', $in_reply_to);
      $p2->execute();
      $r2 = $p2->get_result();
      while ($row = $r2->fetch_assoc()) push_msg_dedupe($thread_msgs, $row, 'inbound', $seen_keys);
      $p2->close();
    }
  }
}

// If no reliable relationship, do conservative subject-based fallback (avoid duplicates)
if (!$found_relationship) {
  $like = '%' . $mysqli->real_escape_string($norm_sub) . '%';

  $q = safe_prepare($mysqli, "SELECT id, created_at, sender, recipient, subject, body_html, body_text, message_id, email_log_id FROM inbound_emails WHERE LOWER(subject) LIKE ? LIMIT 200");
  if ($q) {
    $q->bind_param('s', $like);
    $q->execute();
    $res = $q->get_result();
    while ($r = $res->fetch_assoc()) {
      if (!empty($main['message_id']) && !empty($r['message_id']) && trim($r['message_id']) === trim($main['message_id'])) continue;
      push_msg_dedupe($thread_msgs, $r, 'inbound', $seen_keys);
    }
    $q->close();
  }

  $q2 = safe_prepare($mysqli, "SELECT id, created_at, from_name AS sender, to_emails AS recipient, subject, body_html, body_text, message_id FROM email_log WHERE LOWER(subject) LIKE ? LIMIT 200");
  if ($q2) {
    $q2->bind_param('s', $like);
    $q2->execute();
    $res2 = $q2->get_result();
    while ($r = $res2->fetch_assoc()) {
      if (!empty($main['message_id']) && !empty($r['message_id']) && trim($r['message_id']) === trim($main['message_id'])) continue;
      $r['email_log_id'] = $r['id'];
      push_msg_dedupe($thread_msgs, $r, 'sent', $seen_keys);
    }
    $q2->close();
  }
}

// Ensure main message is present once
$main_key = msg_key($main);
if (!isset($seen_keys[$main_key])) {
  // ensure main includes email_log_id if available
  if (!isset($main['email_log_id']) && isset($main['id']) && isset($main['source_table']) && $main['source_table'] === 'email_log') {
    $main['email_log_id'] = $main['id'];
  }
  push_msg_dedupe($thread_msgs, $main, $type, $seen_keys);
}

// Sort by date asc
usort($thread_msgs, function($a,$b){
  $ta = strtotime($a['created_at'] ?? '1970-01-01 00:00:00');
  $tb = strtotime($b['created_at'] ?? '1970-01-01 00:00:00');
  return $ta <=> $tb;
});

/* ---------- Render HTML ---------- */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Email Thread</h1>
  <div>
    <a href="index.php?page=email_log" class="btn btn-sm btn-outline-secondary">Back to Log</a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <h5 class="card-title mb-3"><?= esc($main['subject'] ?? '') ?></h5>

    <?php foreach($thread_msgs as $m): ?>
      <div class="mb-3 p-3 <?= $m['source']==='sent' ? 'border rounded bg-light' : 'border rounded' ?>">
        <div class="d-flex justify-content-between">
          <div>
            <strong><?= $m['source']==='sent' ? 'Sent' : 'Received' ?></strong>
            &nbsp;—&nbsp; <?= esc($m['sender']) ?> &nbsp;→&nbsp; <?= esc($m['recipient']) ?>
          </div>
          <div class="text-muted"><?= esc(human_dt($m['created_at'])) ?></div>
        </div>
        <hr/>
        <div>
          <?php if (!empty($m['body_html'])): ?>
            <div><?= $m['body_html'] ?></div>
          <?php else: ?>
            <pre style="white-space:pre-wrap;"><?= esc($m['body_text']) ?></pre>
          <?php endif; ?>
        </div>

        <div class="mt-2">
          <?php
            if ($m['source'] === 'sent') {
              $atts = fetch_attachments_for_email_log($mysqli, $m['email_log_id'] ?? $m['id']);
              if (!empty($atts)) {
                echo '<div><strong>Attachments:</strong><ul>';
                foreach ($atts as $a) {
                  $aid = (int)$a['id'];
                  $fn = htmlspecialchars($a['filename']);
                  $link = 'index.php?page=download_attachment&src=email&id=' . $aid;
                  echo "<li><a target=\"_blank\" href=\"" . esc($link) . "\">{$fn}</a></li>";
                }
                echo '</ul></div>';
              }
            } else {
              $linked = $m['email_log_id'] ?? null;
              $inbAtts = fetch_inbound_attachments($mysqli, $m['id'], $linked);
              if (!empty($inbAtts)) {
                echo '<div><strong>Attachments:</strong><ul>';
                foreach ($inbAtts as $a) {
                  $aid = (int)$a['id'];
                  $fn = htmlspecialchars($a['filename']);
                  $src = ($a['src'] ?? 'email'); // 'email' or 'inbound'
                  $link = 'index.php?page=download_attachment&src=' . rawurlencode($src) . '&id=' . $aid;
                  echo "<li><a target=\"_blank\" href=\"" . esc($link) . "\">{$fn}</a></li>";
                }
                echo '</ul></div>';
              }
            }
          ?>
        </div>

      </div>
    <?php endforeach; ?>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
