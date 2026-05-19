<?php
// agent/dashboard.php — stylish dashboard with modal snooze and company fallback
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

// email_log.php  — single file usable for admin & agent
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

// ---------- Detect admin (adapt if your app uses another mechanism) ----------
$is_admin = false;
if (function_exists('is_admin') && is_admin()) {
  $is_admin = true;
} elseif (!empty($_SESSION['is_admin'])) {
  $is_admin = (bool) $_SESSION['is_admin'];
} elseif (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') {
  $is_admin = true;
}

// Current user id (from _auth.php)
$uid = $CURRENT_USER_ID ?? (int) ($_SESSION['person_id'] ?? 0);


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

if (!function_exists('fetch_inbound_attachments')) {
  function fetch_inbound_attachments($db, $inbound_id, $linked_email_log_id = null){
    $out = [];

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

function short_preview($html_or_text, $len = 140) {
  $plain = trim(strip_tags($html_or_text ?? ''));
  if (mb_strlen($plain) <= $len) return $plain;
  return mb_substr($plain, 0, $len - 1) . '…';
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

  $stmt = safe_prepare($mysqli, "SELECT id, email_log_id, mailbox_id, created_at, sender, recipient, subject, body_text, body_html, message_id, in_reply_to, thread_id FROM inbound_emails WHERE id=? LIMIT 1");
  if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $main = $stmt->get_result()->fetch_assoc();
    $stmt->close();
  }

  if (!$main) {
    $stmt2 = safe_prepare($mysqli, "SELECT id, email_log_id, mailbox_id, created_at, sender, recipient, subject, body_text, body_html, message_id, in_reply_to, thread_id FROM inbound_emails WHERE email_log_id = ? LIMIT 1");
    if ($stmt2) {
      $stmt2->bind_param('i', $id);
      $stmt2->execute();
      $main = $stmt2->get_result()->fetch_assoc();
      $stmt2->close();
      if ($main) $main['via_email_log_id'] = $id;
    }
  }

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

if (!$main) {
  echo '<div class="alert alert-warning">Message not found</div>';
  require_once __DIR__.'/includes/footer.php';
  exit;
}

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
    'email_log_id' => isset($row['email_log_id']) ? (int)$row['email_log_id'] : (isset($row['id']) ? (int)$row['id'] : null)
  ];
  $arr[] = $msg;
}

$thread_key = trim($main['message_id'] ?? '');
$in_reply_to = trim($main['in_reply_to'] ?? '');
$thread_id = trim($main['thread_id'] ?? '');
$norm_sub = normalize_subject($main['subject'] ?? '');

$found_relationship = false;

if ($thread_key !== '') {
  $sql = "SELECT id, created_at, from_name AS sender, to_emails AS recipient, subject, body_html, body_text, message_id, in_reply_to, thread_id, NULL AS email_log_id
          FROM email_log WHERE in_reply_to=? OR thread_id=? OR message_id=? LIMIT 200";
  $stmt = safe_prepare($mysqli, $sql);
  if ($stmt) {
    $stmt->bind_param('sss', $thread_key, $thread_key, $thread_key);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
      $r['email_log_id'] = $r['id'];
      push_msg_dedupe($thread_msgs, $r, 'sent', $seen_keys);
      $found_relationship = true;
    }
    $stmt->close();
  }

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

$main_key = msg_key($main);
if (!isset($seen_keys[$main_key])) {
  if (!isset($main['email_log_id']) && isset($main['id']) && isset($main['source_table']) && $main['source_table'] === 'email_log') {
    $main['email_log_id'] = $main['id'];
  }
  push_msg_dedupe($thread_msgs, $main, $type, $seen_keys);
}

usort($thread_msgs, function($a,$b){
  $ta = strtotime($a['created_at'] ?? '1970-01-01 00:00:00');
  $tb = strtotime($b['created_at'] ?? '1970-01-01 00:00:00');
  return $ta <=> $tb;
});

/* ---------- Render HTML (compact) ---------- */
?>
<style>
/* compact agent portal tweaks */
.email-compact .card { margin-bottom: .5rem; }
.email-compact .msg-line { padding: .5rem .75rem; font-size: .9rem; }
.email-compact .msg-line .meta { font-size: .8rem; color:#6c757d; }
.email-compact .msg-body { padding: .6rem .75rem; background:#fff; border-top:1px solid #e9ecef; }
.email-compact .attachment-badge { font-size: .78rem; margin-right:.4rem; }
.email-compact .sender { font-weight:600; font-size:.92rem; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0" style="font-size:1.05rem;"><?= esc($main['subject'] ?? '(No subject)') ?></h4>
    <div class="text-muted" style="font-size:.85rem;"><?= !empty($main['created_at']) ? esc(date('d M Y, h:i A', strtotime($main['created_at']))) : '-' ?></div>
  </div>
  <div>
    <a href="index.php?page=email_log" class="btn btn-sm btn-outline-secondary">Back</a>
  </div>
</div>

<div class="card email-compact">
  <div class="card-body p-2">
    <div id="emailThreadAccordion" class="accordion">
      <?php foreach($thread_msgs as $idx => $m):
        $compactId = 'msg-' . $m['id'] . '-' . $idx;
        $isSent = ($m['source'] === 'sent');
        $preview = short_preview($m['body_html'] ?: $m['body_text'], 160);
        $sender_display = esc($m['sender']);
        $recipient_display = esc($m['recipient']);
        ?>
        <div class="accordion-item border-0 mb-1">
          <h2 class="accordion-header" id="heading-<?= $compactId ?>">
            <button class="accordion-button collapsed msg-line d-flex align-items-start" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $compactId ?>" aria-expanded="false" aria-controls="<?= $compactId ?>">
              <div class="me-2" style="min-width:4px;">
                <?php if ($isSent): ?>
                  <span class="badge bg-primary" title="Sent">S</span>
                <?php else: ?>
                  <span class="badge bg-secondary" title="Received">R</span>
                <?php endif; ?>
              </div>
              <div class="flex-grow-1 text-start">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <span class="sender"><?= $sender_display ?></span>
                    <span class="meta">→ <?= $recipient_display ?></span>
                    <?php if (!empty($m['subject'])): ?>
                      <div class="meta"><?= esc($m['subject']) ?></div>
                    <?php endif; ?>
                  </div>
                  <div class="text-end meta" style="min-width:130px;"><?= !empty($m['created_at']) ? esc(date('d M Y, h:i A', strtotime($m['created_at']))) : '-' ?></div>
                </div>
                <div class="text-truncate meta" title="<?= htmlspecialchars($preview) ?>"><?= htmlspecialchars($preview) ?></div>
              </div>
            </button>
          </h2>

          <div id="<?= $compactId ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?= $compactId ?>" data-bs-parent="#emailThreadAccordion">
            <div class="accordion-body msg-body">
              <?php if (!empty($m['body_html'])): ?>
                <div class="mb-2"><?= $m['body_html'] ?></div>
              <?php else: ?>
                <pre style="white-space:pre-wrap; margin:0; font-size:.92rem;"><?= esc($m['body_text']) ?></pre>
              <?php endif; ?>

              <div class="mt-2">
                <?php
                  if ($isSent) {
                    $atts = fetch_attachments_for_email_log($mysqli, $m['email_log_id'] ?? $m['id']);
                    if (!empty($atts)) {
                      foreach ($atts as $a) {
                        $aid = (int)$a['id'];
                        $fn = htmlspecialchars($a['filename']);
                        $link = 'index.php?page=download_attachment&src=email&id=' . $aid;
                        $sizeTxt = isset($a['size']) ? ' (' . round($a['size']/1024,1) . ' KB)' : '';
                        echo '<a class="btn btn-sm btn-outline-secondary attachment-badge" target="_blank" href="'.esc($link).'"><i class="bi bi-paperclip"></i> ' . $fn . $sizeTxt . '</a>';
                      }
                    } else {
                      echo '<span class="text-muted small">No attachments</span>';
                    }
                  } else {
                    $linked = $m['email_log_id'] ?? null;
                    $inbAtts = fetch_inbound_attachments($mysqli, $m['id'], $linked);
                    if (!empty($inbAtts)) {
                      foreach ($inbAtts as $a) {
                        $aid = (int)$a['id'];
                        $fn = htmlspecialchars($a['filename']);
                        $src = ($a['src'] ?? 'email');
                        $link = 'index.php?page=download_attachment&src=' . rawurlencode($src) . '&id=' . $aid;
                        $sizeTxt = isset($a['size']) ? ' (' . round($a['size']/1024,1) . ' KB)' : '';
                        echo '<a class="btn btn-sm btn-outline-secondary attachment-badge" target="_blank" href="'.esc($link).'"><i class="bi bi-paperclip"></i> ' . $fn . $sizeTxt . '</a>';
                      }
                    } else {
                      echo '<span class="text-muted small">No attachments</span>';
                    }
                  }
                ?>
              </div>

              <div class="mt-2 text-end">
                <!-- optional quick actions for agents: reply/forward/open in full view -->
                
                <a class="btn btn-sm btn-outline-secondary" href="index.php?page=email_log&amp;id=<?= intval($m['email_log_id'] ?? $m['id']) ?>">Open full</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
