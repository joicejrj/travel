<?php
// public/email_report.php
require_once __DIR__ . '/includes/header.php';
require_login();

@include_once __DIR__ . '/../config/app.php';
if (defined('APP_TIMEZONE')) { date_default_timezone_set(APP_TIMEZONE); }

$is_router = (strpos($_SERVER['SCRIPT_NAME'], 'index.php') !== false) || isset($_GET['page']);
$base_url  = $is_router ? '?page=reports' : '?page=reports';

if (!function_exists('esc')) {
    function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
}

/* ---------------------- Range presets ---------------------- */
$allowed_presets = ['today','yesterday','this_week','last_week','this_month','last_month','custom'];
$preset_request = $_GET['preset'] ?? 'this_week';
$preset = in_array($preset_request, $allowed_presets, true) ? $preset_request : 'this_week';

/* custom dates (YYYY-MM-DD) if preset == custom */
$custom_from = $_GET['from'] ?? null;
$custom_to   = $_GET['to'] ?? null;

/* Helper: start/end periods in app timezone (DateTime) */
$appTz = new DateTimeZone(defined('APP_TIMEZONE') ? APP_TIMEZONE : date_default_timezone_get());
$today = new DateTime('today', $appTz);

switch ($preset) {
  case 'today':
    $startApp = (clone $today);
    $endApp   = (clone $today)->modify('+1 day');
    break;
  case 'yesterday':
    $startApp = (clone $today)->modify('-1 day');
    $endApp   = (clone $today);
    break;
  case 'this_week':
    // week starting Monday (PHP's "monday this week" works when locale is english)
    $startApp = (clone $today)->modify('monday this week');
    $endApp   = (clone $startApp)->modify('+1 week');
    break;
  case 'last_week':
    $startApp = (clone $today)->modify('monday last week');
    $endApp   = (clone $startApp)->modify('+1 week');
    break;
  case 'this_month':
    $startApp = new DateTime($today->format('Y-m-01'), $appTz);
    $endApp   = (clone $startApp)->modify('+1 month');
    break;
  case 'last_month':
    $startApp = (new DateTime($today->format('Y-m-01'), $appTz))->modify('-1 month');
    $endApp   = (clone $startApp)->modify('+1 month');
    break;
  case 'custom':
    if ($custom_from && $custom_to) {
      try {
        $startApp = new DateTime($custom_from, $appTz);
        $endApp   = (new DateTime($custom_to, $appTz))->modify('+1 day');
      } catch (Exception $e) {
        $startApp = (clone $today)->modify('-6 days');
        $endApp   = (clone $today)->modify('+1 day');
        $preset = 'this_week';
      }
    } else {
      $startApp = (clone $today)->modify('-6 days');
      $endApp   = (clone $today)->modify('+1 day');
      $preset = 'this_week';
    }
    break;
  default:
    $startApp = (clone $today)->modify('-6 days');
    $endApp   = (clone $today)->modify('+1 day');
}

/* Compute previous period (equal length immediately before current period) */
$periodInterval = $endApp->getTimestamp() - $startApp->getTimestamp();
$prevEndApp   = (clone $startApp);
$prevStartApp = (clone $startApp)->modify('-' . $periodInterval . ' seconds');

/* Convert app timezone DateTime to MySQL local time using server offset */
$offRow = $mysqli->query("SELECT TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), NOW()) AS off")->fetch_assoc();
$mysqlOffset = (int)($offRow['off'] ?? 0);
function to_mysql_local($dtApp, $offsetSec){
  $utcTs = (clone $dtApp)->setTimezone(new DateTimeZone('UTC'))->getTimestamp();
  return gmdate('Y-m-d H:i:s', $utcTs + $offsetSec);
}

$start_mysql      = to_mysql_local($startApp, $mysqlOffset);
$end_mysql        = to_mysql_local($endApp, $mysqlOffset);
$prev_start_mysql = to_mysql_local($prevStartApp, $mysqlOffset);
$prev_end_mysql   = to_mysql_local($prevEndApp, $mysqlOffset);

/* ---------------------- Aggregate per-user query ----------------------
   We compute current and previous period received/sent counts and order
   by the sum of current received+sent. Avoid referencing select aliases in ORDER BY.
------------------------------------------------------------------- */

$sql = "
SELECT
  COALESCE(p.id, 0) AS person_id,
  COALESCE(p.name, m.name, 'Unknown') AS person_name,
  COALESCE(p.email, '') AS person_email,

  SUM(CASE WHEN e.created_at >= ? AND e.created_at < ? 
            AND (LOWER(e.folder) = 'inbox' OR e.folder IS NULL OR e.folder = '') 
            AND (e.sent_via IS NULL OR LOWER(e.sent_via) <> 'sent_account')
       THEN 1 ELSE 0 END) AS received_current,

  SUM(CASE WHEN e.created_at >= ? AND e.created_at < ?
            AND (LOWER(e.folder) = 'sent' OR LOWER(e.sent_via) = 'mailer' OR LOWER(e.sent_via) = 'sent_account' OR COALESCE(e.is_sent,0) = 1)
       THEN 1 ELSE 0 END) AS sent_current,

  SUM(CASE WHEN e.created_at >= ? AND e.created_at < ?
            AND (LOWER(e.folder) = 'inbox' OR e.folder IS NULL OR e.folder = '') 
            AND (e.sent_via IS NULL OR LOWER(e.sent_via) <> 'sent_account')
       THEN 1 ELSE 0 END) AS received_prev,

  SUM(CASE WHEN e.created_at >= ? AND e.created_at < ?
            AND (LOWER(e.folder) = 'sent' OR LOWER(e.sent_via) = 'mailer' OR LOWER(e.sent_via) = 'sent_account' OR COALESCE(e.is_sent,0) = 1)
       THEN 1 ELSE 0 END) AS sent_prev

FROM email_log e
LEFT JOIN mailboxes m ON e.mailbox_id = m.id
LEFT JOIN people p ON m.person_id = p.id
WHERE e.created_at >= ? AND e.created_at < ?
GROUP BY person_id
ORDER BY (
  SUM(CASE WHEN e.created_at >= ? AND e.created_at < ? 
            AND (LOWER(e.folder) = 'inbox' OR e.folder IS NULL OR e.folder = '') 
            AND (e.sent_via IS NULL OR LOWER(e.sent_via) <> 'sent_account')
       THEN 1 ELSE 0 END)
  +
  SUM(CASE WHEN e.created_at >= ? AND e.created_at < ?
            AND (LOWER(e.folder) = 'sent' OR LOWER(e.sent_via) = 'mailer' OR LOWER(e.sent_via) = 'sent_account' OR COALESCE(e.is_sent,0) = 1)
       THEN 1 ELSE 0 END)
) DESC
LIMIT 200
";

/* Bind parameters - order must match the SQL placeholders */
$params = [
  /* 1-2 current received */
  $start_mysql, $end_mysql,
  /* 3-4 current sent */
  $start_mysql, $end_mysql,
  /* 5-6 prev received */
  $prev_start_mysql, $prev_end_mysql,
  /* 7-8 prev sent */
  $prev_start_mysql, $prev_end_mysql,
  /* 9-10 overall WHERE filter (limit scan range) */
  $prev_start_mysql, $end_mysql,
  /* 11-12 ORDER BY: current received again */
  $start_mysql, $end_mysql,
  /* 13-14 ORDER BY: current sent again */
  $start_mysql, $end_mysql
];

$types = str_repeat('s', count($params));

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
  die("DB error prepare: " . $mysqli->error);
}

// bind params via references for mysqli
$bind_names = [];
$bind_names[] = $types;
foreach ($params as $i => $p) {
  ${"bindvar$i"} = $p;
  $bind_names[] = &${"bindvar$i"};
}
call_user_func_array([$stmt, 'bind_param'], $bind_names);

$stmt->execute();
$res = $stmt->get_result();

$rows = [];
$total_current = 0;
$total_prev = 0;
while ($r = $res->fetch_assoc()) {
  $received_current = (int)$r['received_current'];
  $sent_current = (int)$r['sent_current'];
  $received_prev = (int)$r['received_prev'];
  $sent_prev = (int)$r['sent_prev'];
  $total_current_user = $received_current + $sent_current;
  $total_prev_user    = $received_prev + $sent_prev;

  // compute percent change safely
  if ($total_prev_user === 0 && $total_current_user === 0) {
    $pct = 0;
  } elseif ($total_prev_user === 0) {
    $pct = 100;
  } else {
    $pct = round((($total_current_user - $total_prev_user) / $total_prev_user) * 100, 1);
  }

  $rows[] = [
    'person_id' => (int)$r['person_id'],
    'person_name' => $r['person_name'],
    'person_email' => $r['person_email'],
    'received_current' => $received_current,
    'sent_current' => $sent_current,
    'total_current' => $total_current_user,
    'received_prev' => $received_prev,
    'sent_prev' => $sent_prev,
    'total_prev' => $total_prev_user,
    'pct_change' => $pct
  ];

  $total_current += $total_current_user;
  $total_prev += $total_prev_user;
}

/* ---------------------- Daily trend data for chart ---------------------- */
function daily_counts($mysqli, $start_mysql, $end_mysql, $mysqlOffset) {
  $sql = "
    SELECT DATE(DATE_SUB(created_at, INTERVAL ? SECOND)) AS day_local, COUNT(*) AS cnt
    FROM email_log
    WHERE created_at >= ? AND created_at < ?
    GROUP BY day_local
    ORDER BY day_local ASC
  ";
  $stmt = $mysqli->prepare($sql);
  if (!$stmt) return [];
  $stmt->bind_param('iss', $mysqlOffset, $start_mysql, $end_mysql);
  $stmt->execute();
  $res = $stmt->get_result();
  $out = [];
  while ($r = $res->fetch_assoc()) {
    $out[$r['day_local']] = (int)$r['cnt'];
  }
  return $out;
}

$cur_daily  = daily_counts($mysqli, $start_mysql, $end_mysql, $mysqlOffset);
$prev_daily = daily_counts($mysqli, $prev_start_mysql, $prev_end_mysql, $mysqlOffset);

/* Build labels across current period (each day) */
$labels = [];
$periodDays = [];
$iter = clone $startApp;
while ($iter < $endApp) {
  $labels[] = $iter->format('Y-m-d');
  $periodDays[] = $iter->format('Y-m-d');
  $iter->modify('+1 day');
}

$cur_values = [];
$prev_values = [];
$daysCount = count($periodDays);
for ($i=0;$i<$daysCount;$i++) {
  $d = $periodDays[$i];
  $cur_values[] = $cur_daily[$d] ?? 0;
  $pd = (clone $prevStartApp)->modify("+{$i} days")->format('Y-m-d');
  $prev_values[] = $prev_daily[$pd] ?? 0;
}

/* For display range without mutating $endApp, create display strings */
$displayStart = $startApp->format('Y-m-d');
$displayEnd = (clone $endApp)->modify('-1 day')->format('Y-m-d'); // inclusive end for humans

/* ---------------------- Render page ---------------------- */
?>
<style>
  /* Prevent infinite height growth */
  canvas {
    max-height: 400px !important;
  }

  .card-body canvas {
    display: block;
    width: 100% !important;
    height: 350px !important;
  }

  /* For DataTables responsive overflow */
  .dataTables_wrapper {
    overflow-x: auto;
  }
</style>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Email Activity Report</h1>
  <div class="text-end">
    <small class="text-muted">Period: <strong><?= esc($displayStart) ?> → <?= esc($displayEnd) ?></strong></small>
  </div>
</div>

<div class="card mb-4">
  <div class="card-body">
    <form id="rangeForm" class="row g-2 align-items-center">
      <div class="col-auto">
        <label class="form-label mb-0">Preset</label>
        <select id="preset" name="preset" class="form-select">
          <option value="today" <?= $preset==='today' ? 'selected' : '' ?>>Today</option>
          <option value="yesterday" <?= $preset==='yesterday' ? 'selected' : '' ?>>Yesterday</option>
          <option value="this_week" <?= $preset==='this_week' ? 'selected' : '' ?>>This week</option>
          <option value="last_week" <?= $preset==='last_week' ? 'selected' : '' ?>>Last week</option>
          <option value="this_month" <?= $preset==='this_month' ? 'selected' : '' ?>>This month</option>
          <option value="last_month" <?= $preset==='last_month' ? 'selected' : '' ?>>Last month</option>
          <option value="custom" <?= $preset==='custom' ? 'selected' : '' ?>>Custom range</option>
        </select>
      </div>

      <div class="col-auto" id="customRange" style="<?= $preset==='custom' ? '' : 'display:none' ?>">
        <label class="form-label mb-0">From</label>
        <input type="date" name="from" class="form-control" value="<?= esc($custom_from ?: $startApp->format('Y-m-d')) ?>">
      </div>
      <div class="col-auto" id="customRangeTo" style="<?= $preset==='custom' ? '' : 'display:none' ?>">
        <label class="form-label mb-0">To</label>
        <input type="date" name="to" class="form-control" value="<?= esc($custom_to ?: (clone $endApp)->modify('-1 day')->format('Y-m-d')) ?>">
      </div>

      <div class="col-auto">
        <button type="submit" class="btn btn-primary mt-4">Apply</button>
      </div>

      <div class="col-auto ms-auto text-end">
        <div class="h5 mb-0">Total (current): <strong><?= number_format($total_current) ?></strong></div>
        <div class="text-muted small">Previous period: <?= number_format($total_prev) ?></div>
      </div>
    </form>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Top users (by emails this period)</strong>
        <small class="text-muted">Top <?= count($rows) ?> users</small>
      </div>
      <div class="card-body">
        <canvas id="topUsersChart" height="150"></canvas>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header"><strong>Period trend (daily)</strong></div>
      <div class="card-body">
        <canvas id="trendChart" height="120"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><strong>Summary</strong></div>
      <div class="card-body">
        <table class="table table-borderless mb-0">
          <tr><th>Current period</th><td class="text-end"><?= number_format($total_current) ?></td></tr>
          <tr><th>Previous period</th><td class="text-end"><?= number_format($total_prev) ?></td></tr>
          <tr><th>Change</th>
            <td class="text-end">
              <?php
                if ($total_prev === 0 && $total_current === 0) {
                  echo '0%';
                } elseif ($total_prev === 0) {
                  echo '<span class="text-success">+100%</span>';
                } else {
                  $totpct = round((($total_current - $total_prev)/max(1,$total_prev))*100,1);
                  echo ($totpct >= 0 ? '<span class="text-success">+' : '<span class="text-danger">') . $totpct . '%</span>';
                }
              ?>
            </td>
          </tr>
        </table>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header"><strong>Export</strong></div>
      <div class="card-body">
        <a href="#" id="exportCsv" class="btn btn-outline-secondary btn-sm">Export CSV</a>
        <a href="#" id="exportJson" class="btn btn-outline-secondary btn-sm">Export JSON</a>
        <div class="mt-3 text-muted small">Report limited to top 200 users. Use export for full data if you adjust limit.</div>
      </div>
    </div>
  </div>
</div>

<!-- Users table -->
<div class="card mb-4">
  <div class="card-body">
    <div class="table-responsive">
      <table id="reportTable" class="table table-striped table-hover">
        <thead>
          <tr>
            <th>User</th>
            <th class="text-end">Received</th>
            <th class="text-end">Sent</th>
            <th class="text-end">Total</th>
            <th class="text-end">Prev Total</th>
            <th class="text-end">Change</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): 
            $chgClass = ($r['pct_change'] >= 0) ? 'text-success' : 'text-danger';
          ?>
            <tr>
              <td>
                <?= esc($r['person_name']) ?> 
                <div class="text-muted small"><?= esc($r['person_email']) ?></div>
              </td>
              <td class="text-end"><?= number_format($r['received_current']) ?></td>
              <td class="text-end"><?= number_format($r['sent_current']) ?></td>
              <td class="text-end"><?= number_format($r['total_current']) ?></td>
              <td class="text-end"><?= number_format($r['total_prev']) ?></td>
              <td class="text-end <?= $chgClass ?>"><?= ($r['pct_change'] >= 0 ? '+':'') . $r['pct_change'] ?>%</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Include Chart.js (CDN) and DataTables if not already present -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
(function($){
  $(function(){
    $('#preset').on('change', function(){
      if ($(this).val() === 'custom') {
        $('#customRange, #customRangeTo').show();
      } else {
        $('#customRange, #customRangeTo').hide();
      }
    });

    $('#rangeForm').on('submit', function(e){
      e.preventDefault();
      const preset = $('#preset').val();
      let joiner = "<?= (strpos($base_url, '?') !== false) ? '&' : '?' ?>";
      let url = "<?= esc($base_url) ?>" + joiner + "preset=" + encodeURIComponent(preset);
      if (preset === 'custom') {
        const from = $(this).find('[name=from]').val();
        const to = $(this).find('[name=to]').val();
        url += '&from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to);
      }
      window.location.href = url;
    });

    $('#reportTable').DataTable({
      order:[[3,'desc']],
      pageLength: 25,
      lengthMenu: [10,25,50,100],
      stateSave: true
    });

    // Prepare chart data
    const labels = <?= json_encode($labels) ?>;
    const topNames = <?= json_encode(array_map(function($r){ return $r['person_name']; }, $rows)) ?>;
    const topTotals = <?= json_encode(array_map(function($r){ return $r['total_current']; }, $rows)) ?>;

    // Top Users Bar Chart (horizontal)
    const ctxTop = document.getElementById('topUsersChart').getContext('2d');
    new Chart(ctxTop, {
      type: 'bar',
      data: {
        labels: topNames,
        datasets: [{
          label: 'Emails (current)',
          data: topTotals,
          backgroundColor: topTotals.map(() => 'rgba(13,110,253,0.85)')
        }]
      },
      options: {
        indexAxis: 'y',
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { mode: 'index', intersect: false }
        },
        scales: {
          x: { beginAtZero: true },
          y: { ticks: { autoSkip: false } }
        }
      }
    });

    // Trend chart (current vs previous)
    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    new Chart(ctxTrend, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Current period',
            data: <?= json_encode($cur_values) ?>,
            tension: 0.3,
            borderWidth: 2,
            borderColor: 'rgba(13,110,253,0.9)',
            backgroundColor: 'rgba(13,110,253,0.08)',
            fill: true
          },
          {
            label: 'Previous period',
            data: <?= json_encode($prev_values) ?>,
            tension: 0.3,
            borderWidth: 2,
            borderColor: 'rgba(108,117,125,0.9)',
            backgroundColor: 'rgba(108,117,125,0.06)',
            borderDash: [6,4],
            fill: true
          }
        ]
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top' },
          tooltip: { mode: 'index', intersect: false }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });

    setTimeout(() => {
  window.dispatchEvent(new Event('resize'));
}, 500);

    // Export handlers (CSV/JSON)
    $('#exportCsv').on('click', function(e){
      e.preventDefault();
      const rows = [];
      rows.push(['User','Email','Received','Sent','Total','Prev Total','Change%']);
      $('#reportTable tbody tr').each(function(){
        const cols = $(this).find('td');
        const user = $(cols[0]).text().trim().replace(/\n/g,' ');
        const received = $(cols[1]).text().trim();
        const sent = $(cols[2]).text().trim();
        const total = $(cols[3]).text().trim();
        const prev = $(cols[4]).text().trim();
        const change = $(cols[5]).text().trim();
        rows.push([user, '', received, sent, total, prev, change]);
      });
      const csv = rows.map(r => r.map(c => '"' + String(c).replace(/"/g,'""') + '"').join(',')).join('\n');
      const blob = new Blob([csv], {type:'text/csv'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = 'email_activity_report.csv'; document.body.appendChild(a); a.click(); a.remove();
      URL.revokeObjectURL(url);
    });

    $('#exportJson').on('click', function(e){
      e.preventDefault();
      const data = <?= json_encode($rows) ?>;
      const blob = new Blob([JSON.stringify(data, null, 2)], {type:'application/json'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = 'email_activity_report.json'; document.body.appendChild(a); a.click(); a.remove();
      URL.revokeObjectURL(url);
    });

  });
})(jQuery);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
