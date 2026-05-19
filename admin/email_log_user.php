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

// ---------- If not admin: gather mailbox IDs for the current user ----------
$mb_csv = ''; // default - when empty, we'll use IN (0) to ensure no accidental full-scan
if (!$is_admin) {
  $stmt = $mysqli->prepare("SELECT id FROM mailboxes WHERE person_id = ?");
  $stmt->bind_param('i', $uid);
  $stmt->execute();
  $res = $stmt->get_result();
  $mbids = [];
  while ($r = $res->fetch_assoc()) {
    $mbids[] = (int)$r['id'];
  }
  $stmt->close();
  $mb_csv = $mbids ? implode(',', $mbids) : '0';
}

// ---------- Router / base URLs (same logic as your admin page) ----------
@include_once __DIR__ . '/../config/app.php';
if (defined('APP_TIMEZONE')) { date_default_timezone_set(APP_TIMEZONE); }

$is_router = (strpos($_SERVER['SCRIPT_NAME'], 'index.php') !== false) || isset($_GET['page']);
$base_url  = $is_router ? 'index.php?page=email_log' : 'email_log.php';
$sync_url  = $is_router ? 'index.php?page=ajax_sync'  : 'ajax_sync.php';
$qsJoin    = (strpos($base_url, '?') !== false) ? '&' : '?';
function rlink($r){ global $base_url,$qsJoin; return $base_url . $qsJoin . 'range=' . $r; }

// ---------- Range options & application time bounds ----------
$allowed = ['today','yday','5days','older'];
$range = (isset($_GET['range']) && in_array($_GET['range'], $allowed, true)) ? $_GET['range'] : 'today';

$appTz = new DateTimeZone(defined('APP_TIMEZONE') ? APP_TIMEZONE : date_default_timezone_get());
$startToday     = new DateTime('today', $appTz);
$startTomorrow  = (clone $startToday)->modify('+1 day');
$startYesterday = (clone $startToday)->modify('-1 day');
$start5DaysAgo  = (clone $startToday)->modify('-5 days');
$mode = 'between';
switch ($range) {
  case 'today':  $startApp = $startToday;     $endApp = $startTomorrow;  break;
  case 'yday':   $startApp = $startYesterday; $endApp = $startToday;     break;
  case '5days':  $startApp = $start5DaysAgo;  $endApp = $startToday;     break;
  case 'older':  $mode = 'lt';                $limitApp = $start5DaysAgo; break;
}

/* ---- Convert timezone to MySQL local ---- */
$offRow = $mysqli->query("SELECT TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), NOW()) AS off")->fetch_assoc();
$mysqlOffset = (int)($offRow['off'] ?? 0);
function to_mysql_local($dtApp, $offsetSec){
  $utcTs = (clone $dtApp)->setTimezone(new DateTimeZone('UTC'))->getTimestamp();
  return gmdate('Y-m-d H:i:s', $utcTs + $offsetSec);
}

if ($mode === 'between'){
  $from = to_mysql_local($startApp, $mysqlOffset);
  $to   = to_mysql_local($endApp,   $mysqlOffset);
  $bind = [$from, $to];
  $bindType = "ss";
} else {
  $to = to_mysql_local($limitApp, $mysqlOffset);
  $bind = [$to];
  $bindType = "s";
}

// ---------- Build SQL (same received/sent separation as admin) ----------
// If not admin, add mailbox filter
$mb_filter_sql = '';
if (!$is_admin) {
  $mb_filter_sql = " AND mailbox_id IN ($mb_csv) ";
}

/* RECEIVED */
$sql_received = "SELECT id, created_at, from_name AS sender, from_email, to_emails AS recipient,
                        subject, folder, sent_via
                 FROM email_log
                 WHERE (LOWER(folder) = 'inbox' OR folder IS NULL OR folder = '')
                   AND (sent_via IS NULL OR LOWER(sent_via) <> 'sent_account')
                   $mb_filter_sql
                   AND created_at " . ($mode==='between' ? ">= ? AND created_at < ?" : "< ?") . "
                 ORDER BY id DESC";
$stmt_r = $mysqli->prepare($sql_received);
if ($stmt_r === false) { die("Prepare failed: " . $mysqli->error); }
$stmt_r->bind_param($bindType, ...$bind);
$stmt_r->execute();
$res_received = $stmt_r->get_result();

/* SENT */
$sql_sent = "SELECT id, created_at, from_name AS sender, from_email, to_emails AS recipient,
                    subject, folder, sent_via
             FROM email_log
             WHERE (LOWER(folder) = 'sent' OR LOWER(sent_via) = 'mailer' OR LOWER(sent_via) = 'sent_account')
               $mb_filter_sql
               AND created_at " . ($mode==='between' ? ">= ? AND created_at < ?" : "< ?") . "
             ORDER BY id DESC";
$stmt_s = $mysqli->prepare($sql_sent);
if ($stmt_s === false) { die("Prepare failed: " . $mysqli->error); }
$stmt_s->bind_param($bindType, ...$bind);
$stmt_s->execute();
$res_sent = $stmt_s->get_result();

?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Email Log <?= $is_admin ? '' : '<small class="text-muted"> — My Mailboxes</small>' ?></h1>
  <div>
    <?php if ($is_admin): ?>
    <button class="btn btn-sm btn-outline-primary me-2" id="btnSync">
      <i class="fas fa-sync-alt me-1"></i> Sync
    </button>
    <?php endif; ?>
    <a class="btn btn-sm btn-outline-secondary" href="<?= esc($base_url) ?>">Refresh</a>
  </div>
</div>

<div class="btn-group mb-3" role="group">
  <a class="btn btn-sm btn-outline-secondary <?= $range==='today' ? 'active':'' ?>" href="<?= rlink('today') ?>">Today</a>
  <a class="btn btn-sm btn-outline-secondary <?= $range==='yday'  ? 'active':'' ?>" href="<?= rlink('yday') ?>">Yesterday</a>
  <a class="btn btn-sm btn-outline-secondary <?= $range==='5days' ? 'active':'' ?>" href="<?= rlink('5days') ?>">Last 5 days</a>
  <a class="btn btn-sm btn-outline-secondary <?= $range==='older' ? 'active':'' ?>" href="<?= rlink('older') ?>">Older</a>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" id="emailTabs" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pane-received" type="button">Received</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-sent" type="button">Sent</button></li>
</ul>

<div class="tab-content">
  <!-- RECEIVED TAB -->
  <div class="tab-pane fade show active" id="pane-received" role="tabpanel">
    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <div class="table-responsive">
          <table id="tblReceived" class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th><th>Received</th><th>From</th><th>To</th><th>Subject</th><th>Folder</th><th>Via</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while($r = $res_received->fetch_assoc()):
                $viewUrl = $is_router ? 'index.php?page=email_view&type=received&id='.(int)$r['id']
                                      : 'email_view.php?type=received&id='.(int)$r['id']; ?>
              <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= !empty($r['created_at']) ? esc(date('d M Y, h:i A', strtotime($r['created_at']))) : '-' ?></td>
                <td><?= esc($r['sender'] ?: $r['from_email']) ?></td>
                <td><?= esc($r['recipient']) ?></td>
                <td><?= esc($r['subject']) ?></td>
                <td><?= esc($r['folder']) ?></td>
                <td><?= esc($r['sent_via']) ?></td>
                <td><a href="<?= esc($viewUrl) ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- SENT TAB -->
  <div class="tab-pane fade" id="pane-sent" role="tabpanel">
    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <div class="table-responsive">
          <table id="tblSent" class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th><th>Sent</th><th>From</th><th>To</th><th>Subject</th><th>Folder</th><th>Via</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while($s = $res_sent->fetch_assoc()):
                $viewUrl = $is_router ? 'index.php?page=email_view&type=sent&id='.(int)$s['id']
                                      : 'email_view.php?type=sent&id='.(int)$s['id']; ?>
              <tr>
                <td><?= (int)$s['id'] ?></td>
                <td><?= !empty($s['created_at']) ? esc(date('d M Y, h:i A', strtotime($s['created_at']))) : '-' ?></td>
                <td><?= esc($s['sender'] ?: $s['from_email']) ?></td>
                <td><?= esc($s['recipient']) ?></td>
                <td><?= esc($s['subject']) ?></td>
                <td><?= esc($s['folder']) ?></td>
                <td><?= esc($s['sent_via']) ?></td>
                <td><a href="<?= esc($viewUrl) ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
  $('#tblReceived').DataTable({
    pageLength:10,
    lengthMenu:[5,10,25,50],
    order:[[0,'desc']],
    language:{search:"_INPUT_",searchPlaceholder:"Search received..."}
  });
  $('#tblSent').DataTable({
    pageLength:10,
    lengthMenu:[5,10,25,50],
    order:[[0,'desc']],
    language:{search:"_INPUT_",searchPlaceholder:"Search sent..."}
  });

  <?php if ($is_admin): // only admin has the sync button ?>
  $('#btnSync').on('click',function(){
    const $b=$(this);
    $b.prop('disabled',true).html('<i class="fas fa-sync fa-spin me-1"></i> Syncing…');
    $.ajax({url:'<?= $sync_url ?>',method:'POST',dataType:'json',timeout:60000})
      .done(function(resp){
        if(resp&&resp.ok){
          $b.html('<i class="fas fa-check me-1"></i> Synced');
          setTimeout(()=>location.reload(),800);
        }else{
          alert('Sync failed: '+(resp&&resp.error?resp.error:'Unknown'));
          $b.prop('disabled',false).html('<i class="fas fa-sync-alt me-1"></i> Sync');
        }
      })
      .fail(()=>{alert('Sync failed');$b.prop('disabled',false).html('<i class="fas fa-sync-alt me-1"></i> Sync');});
  });
  <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
