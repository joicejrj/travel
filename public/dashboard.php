<?php
// agent/dashboard.php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

$uid = $CURRENT_USER_ID;

if (!function_exists('esc')) {
    function esc($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
}

//divert based on the user type
$utype = "customers";
$ucol = "customer";
$type_statuses = $customers_statuses;
if(isset($_GET['utype']) && $_GET['utype']!='') {
  $utypet = $site->esc($_GET['utype']);
  if(in_array($utypet,array("customers","recruiters","employees"))) {
    $utype = $utypet;
    $ucol = rtrim($utype, "s");
    $type_statuses = ${$utype."_statuses"};
  }
}


// ---------- My Coverage (daily_followup) ----------
$today_date = date('Y-m-d');

// If your date_followup column contains time (YYYY-MM-DD HH:MM:SS) set this to true
$use_date_only = true;
$date_field_expr = $use_date_only ? "DATE(date_followup)" : "date_followup";

// 1) total followups for current agent today
$tot_sql = "SELECT COUNT(*) AS total FROM daily_followup WHERE {$date_field_expr} = ? AND agent_id = ?";
$my_followup_total = 0;
if ($tot_stmt = $mysqli->prepare($tot_sql)) {
    $tot_stmt->bind_param('si', $today_date, $uid);
    $tot_stmt->execute();
    $tot_res = $tot_stmt->get_result();
    if ($r = $tot_res->fetch_assoc()) $my_followup_total = (int)$r['total'];
    $tot_stmt->close();
} else {
    error_log("dashboard: prepare failed for daily_followup total: " . $mysqli->error);
    $my_followup_total = 0;
}

// 2) breakdown by status_followup
$status_sql = "
    SELECT COALESCE(status_followup, 'Unknown') AS status, COUNT(*) AS cnt
    FROM daily_followup
    WHERE {$date_field_expr} = ? AND agent_id = ?
    GROUP BY status_followup
";
$followup_by_status = [];
if ($status_stmt = $mysqli->prepare($status_sql)) {
    $status_stmt->bind_param('si', $today_date, $uid);
    $status_stmt->execute();
    $sr = $status_stmt->get_result();
    while ($row = $sr->fetch_assoc()) {
        $followup_by_status[$row['status']] = (int)$row['cnt'];
    }
    $status_stmt->close();
}

// 3) load per-status required targets from settings
$preferred_order = ['Active','Inactive','Work in Progress','Prospect','Suspect','Dead'];
$targets = [];
$placeholders = implode(',', array_fill(0, count($preferred_order), '?'));
$types = str_repeat('s', count($preferred_order));
$sql = "SELECT name, value FROM settings WHERE name IN ({$placeholders})";

if ($stmt = $mysqli->prepare($sql)) {
    $bindParams = [$types];
    foreach ($preferred_order as $k => $name) $bindParams[] = &$preferred_order[$k];
    call_user_func_array([$stmt, 'bind_param'], $bindParams);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $targets[$r['name']] = (int)$r['value'];
    $stmt->close();
}

$default_target = 0;
$sq = $mysqli->prepare("SELECT value FROM settings WHERE name = 'daily_followup_target' LIMIT 1");
if ($sq) {
    $sq->execute();
    $res = $sq->get_result();
    if ($row = $res->fetch_assoc()) $default_target = max(0, (int)$row['value']);
    $sq->close();
}
foreach ($preferred_order as $st) if (!isset($targets[$st])) $targets[$st] = $default_target;

// 4) Ordered list
$ordered_list = [];
foreach ($preferred_order as $st) {
    $ordered_list[$st] = [
        'count' => $followup_by_status[$st] ?? 0,
        'target' => $targets[$st]
    ];
    unset($followup_by_status[$st]);
}
foreach ($followup_by_status as $st => $cnt) {
    $ordered_list[$st] = ['count' => $cnt, 'target' => $targets[$st] ?? $default_target];
}

/* --------------------------
   Reminders (supports customers_reminders or reminders)
   Server-side filtering: ?status=&when=&contact=
-------------------------- */
// determine reminders table
$use_table = $utype.'_reminders';
$rem_col = $ucol.'_id';

// filters
$filter_status = isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : '';
$filter_when = isset($_GET['when']) ? $_GET['when'] : 'next7';

$now = time();
switch ($filter_when) {
  case 'today':
    $range_start = date('Y-m-d 00:00:00', $now);
    $range_end   = date('Y-m-d 23:59:59', $now);
    break;
  case 'overdue':
    $range_start = '1970-01-01 00:00:00';
    $range_end   = date('Y-m-d H:i:s', $now);
    break;
  case 'all':
    $range_start = '1970-01-01 00:00:00';
    $range_end   = '2099-12-31 23:59:59';
    break;
  case 'next7':
  default:
    $range_start = date('Y-m-d 00:00:00', $now);
    $range_end   = date('Y-m-d 23:59:59', strtotime('+7 days', $now));
    break;
}

$reminders = [];
if ($use_table) {
    // Base SQL
    $sql = "
      SELECT r.id, r.{$rem_col} AS contact_ref, r.reminder_at, r.type, r.note, r.completed,
             c.id AS {$ucol}_id, c.name AS contact_name, c.email AS contact_email, 
             c.phone AS contact_phone, c.agent_id AS contact_agent, c.type AS contact_status, r.contact_id
      FROM {$use_table} r
      LEFT JOIN {$utype} c ON r.{$rem_col} = c.id
      WHERE r.reminder_at BETWEEN ? AND ? 
        AND r.completed = 0
    ";

    $params = [$range_start, $range_end];
    // echo $range_start." AND ".$range_end;
    $types  = 'ss';

    // Filter by contact status if selected
    if ($filter_status !== '') {
        $sql .= " AND c.type = ? ";
        $params[] = $filter_status;
        $types .= 's';
    } else {
        // Only show reminders for this agent or unassigned
        $sql .= " AND (c.agent_id = ? OR c.agent_id IS NULL) ";
        $params[] = $uid;
        $types .= 'i';
    }

    $sql .= " ORDER BY c.type, r.reminder_at ASC";

    $rem_stmt = $mysqli->prepare($sql);
    if ($rem_stmt === false) {
        error_log("dashboard: prepare failed for reminders SQL: " . $mysqli->error);
        $reminders = [];
    } else {
        // bind params dynamically
        $bind_names = [$types];
        foreach ($params as $k => $v) {
            $bind_names[] = &$params[$k];
        }
        call_user_func_array([$rem_stmt, 'bind_param'], $bind_names);

        $rem_stmt->execute();
        $rem_res = $rem_stmt->get_result();
        // echo count($rem_res->fetch_all());

        // Collect all reminders grouped by status
        $grouped = [];
        while ($row = $rem_res->fetch_assoc()) {
            $status = $row['contact_status'] ?? 'Unknown';
            if (!isset($grouped[$status])) {
                $grouped[$status] = [];
            }
            $grouped[$status][] = [
                'id' => $row['id'],
                $ucol.'_id' => $row[$ucol.'_id'] ?? $row['contact_ref'],
                'reminder_at' => $row['reminder_at'],
                'type' => $row['type'],
                'note' => $site->more($row['note'],40),
                'completed' => (int)$row['completed'],
                'contact_name' => $row['contact_name'] ?? '',
                'contact_email' => $row['contact_email'] ?? '',
                'contact_phone' => $row['contact_phone'] ?? '',
                'contact_agent' => $row['contact_agent'] ?? null,
                'contact_status' => $status,
                'contact_id' => $row['contact_id']
            ];
        }
        $rem_stmt->close();


        // Slice to first 10 per status + indicate if more
        foreach ($grouped as $status => $list) {
            // count only reminders that are overdue
            $due_count = 0;
            $now = time();
            foreach ($list as $item) {
                if (strtotime($item['reminder_at']) <= $now) {
                    $due_count++;
                }
            }
            $reminders[$status] = [
                'items' => array_slice($list, 0, 10),
                'total' => $due_count, // only overdue ones
                'has_more' => count($list) > 10
            ];
        }
    }
}


/* --------------------------
   Read daily follow-up target from settings (default 20)
   Admin will later provide UI to change this.
-------------------------- */
$daily_target = 20;
$sq = $mysqli->prepare("SELECT value FROM settings WHERE name = 'daily_followup_target' LIMIT 1");
if ($sq) {
    $sq->execute();
    $res = $sq->get_result();
    if ($row = $res->fetch_assoc()) {
        $val = (int)$row['value'];
        if ($val > 0) $daily_target = $val;
    }
    $sq->close();
}
?>
<style>
  .btn-outline-secondary {
    color: #000;
  }
  .pill[aria-checked="true"] { background: linear-gradient(90deg,#fff4d6,#ffe38a); border-color: transparent; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
  .quick-reminder-pill.active { background: linear-gradient(90deg,#fff4d6,#ffe38a); border-color: transparent; transform: translateY(-2px); }
</style>

<style>
    .reminder-section {
      height: 420px;
      display: flex;
      flex-direction: column;
    }
    .reminder-body {
      overflow-y: auto;
      flex: 1;
    }
    .reminder-card {
      background-color: #f8f9fa;
      transition: background-color 0.2s;
    }
    .reminder-card:hover {
      background-color: #f1f3f5;
    }
  </style>
  <style>
    .btn-outline-secondary.btn-primary {
      background-color: #6c757d !important;
      color: #fff !important;
    }
  </style>
<!-- Page content -->

<div class="row gx-4 gy-4">



  <div class="col-lg-12">

    <!-- ================= DAILY OVERVIEW SECTION ================= -->
    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body">

        <!-- Header Row -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
          <div>
            <div class="fw-semibold fs-6 mb-0">Today: <?= date('l, d M Y', strtotime($date ?? 'now')) ?></div>
          </div>

          <!-- Single-line My coverage (label, number, small inline "i" icon that opens modal) -->
<div class="text-end d-flex align-items-center justify-content-end" style="gap:.5rem; white-space:nowrap;">
  <div class="text-muted small">My coverage</div>
  <div class="fw-bold fs-5 text-primary"><?= esc($my_followup_total) ?></div>

  <a href="#" class="ms-2" data-bs-toggle="modal" data-bs-target="#coverageInfoModal" title="View breakdown" style="text-decoration:none;">
    <span class="d-inline-flex align-items-center justify-content-center border rounded-circle"
          style="width:26px;height:26px;font-size:13px;line-height:1;color:#6c757d;background:#fff;">
      i
    </span>
  </a>
</div>

<!-- Coverage Info Modal -->
<div class="modal fade" id="coverageInfoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Today's followups — <?= esc(date('d M Y', strtotime($today_date))) ?></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <?php if (empty($ordered_list)): ?>
          <div class="text-center text-muted py-2">No followups recorded for today.</div>
        <?php else: ?>
          <ul class="list-group">
            <?php foreach ($ordered_list as $st => $data): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><?= esc($st) ?></span>
                <strong><?= (int)$data['count'] ?> / <?= (int)$data['target'] ?></strong>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <!-- Right-aligned Total -->
        <div class="mt-3 text-end">
          <strong>Total: <?= esc($my_followup_total) ?> / <?= array_sum(array_column($ordered_list, 'target')) ?></strong>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
        </div>

        <hr class="my-2">

        <!-- Filters Row -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">

          <!-- DATE FILTER -->
          <div>
            <div class="btn-group flex-wrap" role="group" aria-label="Date Filter">
              <?php
                $dates = [
                  'next7' => 'Next 7 days',
                  'today' => 'Today',
                  'overdue' => 'Overdue',
                  'all' => 'All'
                ];
                foreach ($dates as $val => $label):
                  $id = 'when-' . $val;
                  $active = ($filter_when ?? 'today') === $val ? 'active' : '';
              ?>
                <input type="radio" class="btn-check" name="filterWhen" id="<?= $id ?>" value="<?= esc($val) ?>" <?= $active ? 'checked' : '' ?>>
                <label class="btn btn-outline-success btn-sm <?= $active ?>" for="<?= $id ?>"><?= esc($label) ?></label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- User Type FILTER -->
          <div>
            <div class="btn-group flex-wrap" role="group" aria-label="User Type Filter">
              <?php
                $utypes = [
                  'customers' => 'Customers',
                  'recruiters' => 'Recruiters',
                  'employees' => 'Employees'
                ];
                foreach ($utypes as $valt => $labelt):
                  $id = 'utype-' . $valt;
                  $active = ($utype ?? 'customers') === $valt ? 'active' : '';
              ?>
                <input type="radio" class="btn-check" name="filterUtype" id="<?= $id ?>" value="<?= esc($valt) ?>" <?= $active ? 'checked' : '' ?>>
                <label class="btn btn-outline-success btn-sm <?= $active ?>" for="<?= $id ?>"><?= esc($labelt) ?></label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- STATUS FILTER -->
          <div>
            <div class="btn-group flex-wrap" role="group" aria-label="Status Filter" id="statusFilterGroup">
                <input type="radio" class="btn-check" name="filterStatus" id="status-all" value="" <?= $filter_status==''?'checked':''?>>
                <label class="btn btn-outline-primary btn-sm <?= $filter_status==''?'active':''?>" for="status-all">All</label>
              <?php
                foreach ($type_statuses as $st => $s):
                  $id = 'status-' . strtolower(str_replace(' ', '-', $s ?: 'all'));
                  $active = ($filter_status ?? '') === $s ? 'active' : '';
              ?>
                <input type="radio" class="btn-check" name="filterStatus" id="<?= $id ?>" value="<?= esc($s) ?>" <?= $active ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary btn-sm <?= $active ?>" for="<?= $id ?>"><?= esc($st) ?></label>
              <?php endforeach; ?>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>

  <!-- ================= REMINDER SECTIONS ================= -->
  <div class="row g-4" id="reminderContainer" style="margin-top: -1rem;">
    <?php
      foreach ($type_statuses as $st => $status):
        if ($status === '') continue;

        // get reminders for this status
        $data = $reminders[$status] ?? ['items' => [], 'total' => 0, 'has_more' => false];
        $items = $data['items'];
        $total = $data['total'];
        $has_more = $data['has_more'];
    ?>
      <div class="col-12 col-md-4 col-xl-4 reminder-section-wrapper" data-status="<?= strtolower($status) ?>">
        <div class="card reminder-section shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <strong class="text-uppercase small"><?= esc($status) ?></strong>
            <span class="text-muted small"><?= $total ?> due</span>
          </div>

          <div class="card-body reminder-body" style="max-height:420px; overflow-y:auto;">
            <?php if (empty($items)): ?>
              <div class="text-center text-muted py-4 small"><em>No reminders found.</em></div>
            <?php else: ?>
              <?php foreach ($items as $r): 
                  $payload = [
                    'id' => (int)$r['id'],
                    $ucol.'_id' => (int)$r[$ucol.'_id'],
                    'reminder_at' => $r['reminder_at'],
                    'note' => $r['note'],
                    'completed' => (int)$r['completed'],
                    'contact_name' => $r['contact_name'],
                    'contact_email' => $r['contact_email'],
                    'contact_phone' => $r['contact_phone'],
                    'contact_id' => $r['contact_id']
                  ];
                  $rem_json = json_encode($payload, JSON_HEX_APOS|JSON_HEX_QUOT);
              ?>
                <div class="reminder-card border rounded-3 p-3 mb-3">
                  <div class="d-flex align-items-start gap-2">
                    <div class="flex-grow-1">
                      <div class="fw-semibold"><?= esc($r['contact_name'] ?: '—') ?></div>
                      <div class="text-muted small"><?= esc($r['contact_email']) ?></div>
                      <div class="text-muted small"><?= esc($r['contact_phone']) ?></div>
                      <?php if (!empty($r['note'])): ?>
                        <div class="text-secondary small mt-1 fst-italic">
                          "<?= esc(strlen($r['note']) > 80 ? substr($r['note'], 0, 77) . '…' : $r['note']) ?>"
                        </div>
                      <?php endif; ?>
                    </div>
                    <span class="badge bg-primary">
                      Due: <strong><?= date('d M, h:i A', strtotime($r['reminder_at'])) ?></strong>
                    </span>
                  </div>

                  <div class="mt-2 d-flex flex-wrap gap-2">
                    <button class="btn btn-success btn-sm btn-rem-action"
                      data-action="complete"
                      data-id="<?= $r['id'] ?>">
                      <i class="fas fa-check me-1"></i> Done
                    </button>
                    <button class="btn btn-outline-secondary btn-sm btn-rem-action"
                      data-action="snooze"
                      data-reminder='<?= $rem_json ?>'>
                      <i class="fas fa-clock me-1"></i> Snooze
                    </button>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($has_more): ?>
              <button class="btn btn-sm btn-outline-primary btn-load-more d-block mx-auto mt-3" data-status="<?= strtolower($status) ?>" data-offset="10">Load More</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>


<script>
document.addEventListener('DOMContentLoaded', function() {
  const radios = document.querySelectorAll('input[name="filterStatus"]');
  const sections = document.querySelectorAll('.reminder-section-wrapper');

  radios.forEach(radio => {
    radio.addEventListener('change', function() {
      const value = this.value.trim().toLowerCase();

      // Update active class on labels
      radios.forEach(r => {
        const label = document.querySelector(`label[for="${r.id}"]`);
        if (label) label.classList.remove('active');
      });
      const activeLabel = document.querySelector(`label[for="${this.id}"]`);
      if (activeLabel) activeLabel.classList.add('active');

      // Filter sections based on selected status
      sections.forEach(sec => {
        const secStatus = (sec.dataset.status || '').trim().toLowerCase();
        if (value === '' || secStatus === value) {
          sec.style.display = '';
        } else {
          sec.style.display = 'none';
        }
      });
    });
  });
});
</script>
<script>
function openEditReminderModal(reminder) {
  const modalEl = document.getElementById('editReminderModal');
  if (!modalEl) return;

  // console.log(reminder);        // "Follow up with client."

  const modal = new bootstrap.Modal(modalEl);

  // Set reminder ID
  document.getElementById('edit_reminder_id').value = reminder.id || '';
  document.getElementById('<?=$ucol?>_id').value = reminder.<?=$ucol?>_id || '';
  document.getElementById('contact_id').value = reminder.contact_id || '';

  // Split datetime (YYYY-MM-DD HH:MM:SS)
  const [date, time] = (reminder.reminder_at || '').split(' ');
  document.getElementById('edit-reminder-date').value = date || '';
  document.getElementById('edit-reminder-time').value = (time || '').slice(0, 5) || '';

  // Notes
  document.getElementById('edit-reminder-notes').value = reminder.note || '';

  // Handle quick reminder buttons
  const quickBtns = modalEl.querySelectorAll('.edit-quick-reminder');
  const customDiv = document.getElementById('editCustomDateTime');
  const dateInput = document.getElementById('edit-reminder-date');
  const timeInput = document.getElementById('edit-reminder-time');

  quickBtns.forEach(b => b.classList.remove('btn-primary'));

  // Detect which button fits the date difference
  const now = new Date();
  const remindDate = new Date(reminder.reminder_at);
  const diffDays = Math.round((remindDate - now) / (1000 * 60 * 60 * 24));

  let matched = false;
  quickBtns.forEach(btn => {
    const days = btn.dataset.days;
    if (days !== 'custom' && Math.abs(diffDays - parseInt(days)) <= 1) {
      btn.classList.add('btn-primary');
      customDiv.style.display = 'none';
      matched = true;
    }
  });

  // If no matching quick button, show custom date/time
  if (!matched) {
    const customBtn = modalEl.querySelector('.edit-quick-reminder[data-days="custom"]');
    if (customBtn) customBtn.classList.add('btn-primary');
    customDiv.style.display = 'flex';
  }

  modal.show();
}

// Handle first reminder form submit
$(document).on("submit", "#edit-reminder-form", function(e) {
    e.preventDefault();

    const $form = $(this);
    // const supplierId = <?= $id ?? 0 ?>; // your global supplierId variable

    const formData = {
        reminder_id: $form.find("input[name='reminder_id']").val(),
        supplier_id: $form.find("input[name='<?=$ucol?>_id']").val(),
        contact_id: $form.find("input[name='contact_id']").val(),
        type: 'Callback',
        reminder_date: $form.find("input[name='reminder_date']").val(),
        reminder_time: $form.find("input[name='reminder_time']").val(),
        notes: $form.find("textarea[name='notes']").val()
    };

    // const $msg = $('<span class="small mt-1"></span>').insertAfter($form.find("button[type='submit']"));
    const $msg = $('#first_rem');

    $.ajax({
        url: "public/ajax/<?=$utype?>_reminder_update.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(res) {
            if (res.success) {
                $msg.text(res.msg)
                    .removeClass("text-danger")
                    .addClass("text-success")
                    .fadeIn(200).delay(1500).fadeOut(500);

                setTimeout(function() {
                    $("#editReminderModal").modal('hide');
                },500);
                
                // Optionally reload reminders list
                window.location.reload();
            } else {
                $msg.text(res.msg || "Failed to update reminder")
                    .removeClass("text-success")
                    .addClass("text-danger")
                    .fadeIn(200).delay(2000).fadeOut(500);
            }
        },
        error: function() {
            $msg.text("Error: Could not update reminder")
                .removeClass("text-success")
                .addClass("text-danger")
                .fadeIn(200).delay(2000).fadeOut(500);
        }
    });
});

document.addEventListener("DOMContentLoaded", () => {
  const quickBtns = document.querySelectorAll('.edit-quick-reminder');
  const dateInput = document.getElementById('edit-reminder-date');
  const timeInput = document.getElementById('edit-reminder-time');
  const customDiv = document.getElementById('editCustomDateTime');

  quickBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const days = btn.dataset.days;
      const now = new Date();

      quickBtns.forEach(b => b.classList.remove('btn-primary'));
      btn.classList.add('btn-primary');

      if (days === 'custom') {
        customDiv.style.display = 'flex';
        dateInput.required = true;
        timeInput.required = true;
      } else {
        customDiv.style.display = 'none';
        dateInput.required = false;
        timeInput.required = false;

        now.setDate(now.getDate() + parseInt(days));
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const dd = String(now.getDate()).padStart(2, '0');
        dateInput.value = `${yyyy}-${mm}-${dd}`;
        timeInput.value = '10:00';
      }
    });
  });
});
</script>
<script>
function bindReminderButtons(context = document) {
  context.querySelectorAll('button[data-action]').forEach(btn => {
    btn.addEventListener('click', function(e) {
      const $btn = $(this); // Now `this` correctly refers to the clicked button
      const action = $btn.data('action');


      if (action === 'snooze') {
        // get reminder data
        const remData = $btn.data('reminder') || '{}';
        let reminder = {};
        try {
          reminder = typeof remData === 'object' ? remData : JSON.parse(remData);
        } catch (err) {
          console.error('Invalid reminder data', err);
          reminder = {};
        }
        console.log(reminder);
        openEditReminderModal(reminder);
      }

      if (action === 'complete') {
        const reminder_id = $btn.data('id');
        // open compact confirmation modal (requires note)
        $('#completeReminderId').val(reminder_id || '');
        $('#completeReminderNote').val('');
        $('#completeConfirmModal .modal-title').text('Mark Reminder Complete');
        $('#completeConfirmModal').modal('show');
        setTimeout(()=>$('#completeReminderNote').focus(),150);
        return;
      }
    });
  });
}
document.addEventListener('DOMContentLoaded', function() {
  document.body.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-load-more');
    if (!btn) return;

    const status = btn.dataset.status;
    const offset = parseInt(btn.dataset.offset || 0, 10);
    btn.disabled = true;
    btn.textContent = 'Loading...';

    fetch('ajax/load_reminders_<?=$utype?>.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ status, offset })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success && Array.isArray(data.data) && data.data.length > 0) {
        const container = btn.closest('.card-body');

        data.data.forEach(r => {
          // prepare the same JSON payload as in PHP
          const payload = {
            id: Number(r.id),
            <?=$ucol?>_id: Number(r.<?=$ucol?>_id),
            reminder_at: r.reminder_at,
            note: r.note,
            completed: Number(r.completed),
            contact_name: r.contact_name,
            contact_email: r.contact_email,
            contact_phone: r.contact_phone,
            contact_id: r.contact_id
          };
          const remJson = encodeURIComponent(JSON.stringify(payload));

          const card = document.createElement('div');
          card.className = 'reminder-card border rounded-3 p-3 mb-3';
          card.innerHTML = `
            <div class="d-flex align-items-start gap-2">
              <div class="flex-grow-1">
                <div class="fw-semibold">${r.contact_name || '—'}</div>
                <div class="text-muted small">${r.contact_email || ''}</div>
                <div class="text-muted small">${r.contact_phone || ''}</div>
                ${r.note ? `<div class="text-secondary small mt-1 fst-italic">"${r.note.length > 80 ? r.note.substring(0, 77) + '…' : r.note}"</div>` : ''}
              </div>
              <span class="badge bg-primary">
                Due: <strong>${r.reminder_at}</strong>
              </span>
            </div>
            <div class="mt-2 d-flex flex-wrap gap-2">
              <button class="btn btn-success btn-sm btn-rem-action" data-action="complete" data-id="${r.id}">
                <i class="fas fa-check me-1"></i> Done
              </button>
              <button class="btn btn-outline-secondary btn-sm btn-rem-action" data-action="snooze" data-reminder="${remJson}">
                <i class="fas fa-clock me-1"></i> Snooze
              </button>
            </div>
          `;

          // INSERT BEFORE Load More button so it stays at the bottom
          container.insertBefore(card, btn);
        });

        // ✅ Re-bind event listeners for newly added buttons
        bindReminderButtons(container);

        // ✅ Handle Load More button
        if (data.has_more) {
          btn.dataset.offset = data.next_offset;
          btn.disabled = false;
          btn.textContent = 'Load More';
        } else {
          btn.remove();
        }
      } else {
        btn.remove();
      }
    })
    .catch(err => {
      console.error('Error loading reminders:', err);
      btn.textContent = 'Error';
    });

  });
});
</script>

</div>

<!-- Complete Confirmation Modal (compact: mandatory note) -->
<div class="modal fade" id="completeConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="completeConfirmForm">
        <div class="modal-header">
          <h5 class="modal-title">Mark Complete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="completeReminderId" name="id" value="">
          <div class="mb-2">
            <label class="form-label small">Note <span class="text-danger">*</span></label>
            <textarea id="completeReminderNote" name="note" class="form-control" rows="4" required></textarea>
          </div>
          <div class="small text-muted">A note is required to mark a reminder as complete.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Confirm & Mark Done</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Daily task modal -->
<div class="modal fade" id="dailyTaskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Your task for today</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Follow up <strong id="dailyTargetCount"><?= (int)$daily_target ?></strong> customers from your customers.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Reminder Modal -->
<div class="modal fade" id="editReminderModal" tabindex="-1" aria-labelledby="editReminderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="editReminderModalLabel">Update Reminder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body: Reminder Form -->
      <div class="modal-body">
        <form id="edit-reminder-form" class="mb-3 p-2">
          <!-- Hidden Reminder ID -->
          <input type="hidden" name="reminder_id" id="edit_reminder_id" value="">
          <input type="hidden" name="<?=$ucol?>_id" id="<?=$ucol?>_id" value="">
          <input type="hidden" name="contact_id" id="contact_id" value="">
          <input type="hidden" class="btn-check" name="type" value="Callback">

          <!-- Quick Select Reminder Time -->
          <div class="mb-3">
            <label class="form-label mb-1 small fw-bold">Reminder At</label>
            <div class="d-flex flex-wrap gap-1">
              <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill edit-quick-reminder" data-days="1">Tomorrow</button>
              <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill edit-quick-reminder" data-days="7">1 Week Later</button>
              <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill edit-quick-reminder" data-days="30">1 Month Later</button>
              <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill edit-quick-reminder" data-days="90">3 Months Later</button>
              <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill edit-quick-reminder" data-days="custom">Other</button>
            </div>
          </div>

          <!-- Date and Time -->
          <div id="editCustomDateTime" class="row g-2 mb-3" style="display:none;">
            <div class="col-md-6">
              <label class="small mb-1">Date</label>
              <input type="date" class="form-control form-control-xs rounded-pill" name="reminder_date" id="edit-reminder-date" required>
            </div>
            <div class="col-md-6">
              <label class="small mb-1">Time</label>
              <input type="time" class="form-control form-control-xs rounded-pill" name="reminder_time" id="edit-reminder-time" required>
            </div>
          </div>

          <!-- Notes -->
          <div class="mb-2">
            <textarea class="form-control form-control-xs rounded-3" name="notes" id="edit-reminder-notes" rows="2" placeholder="Write notes..."></textarea>
          </div>

          <div id="first_rem"></div>

          <!-- Submit Button -->
          <div class="d-flex justify-content-end mt-2">
            <button type="submit" class="btn btn-success btn-xs rounded-pill">Update Reminder</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<script>
// make resetSnoozeUI available globally and DOM-driven so any script can call it
window.resetSnoozeUI = function() {
  // find pill buttons and other controls fresh from DOM
  const pillBtns = Array.from(document.querySelectorAll('.quick-reminder-pill'));
  const preset = document.getElementById('snoozePreset');
  const snoozeNumber = document.getElementById('snoozeNumber');
  const snoozeUnit = document.getElementById('snoozeUnit');

  // reset active states
  pillBtns.forEach(b => b.classList.remove('active'));

  // find default button (60min) and mark it
  const defaultBtn = pillBtns.find(b => b.getAttribute('data-min') === '60');
  if (defaultBtn) defaultBtn.classList.add('active');

  if (preset) preset.value = '';
  if (snoozeNumber) snoozeNumber.value = '';
  if (snoozeUnit) snoozeUnit.value = 'm';

  // attach click handlers to pills so future clicks still work if not already attached
  pillBtns.forEach(btn => {
    // avoid double-binding: only attach if not already marked
    if (!btn.__snooze_bound) {
      btn.addEventListener('click', function() {
        // clear other pills
        pillBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        if (preset) preset.value = '';
        if (snoozeNumber) snoozeNumber.value = '';
        if (snoozeUnit) snoozeUnit.value = 'm';
        // store minutes or days on the element for usage by other code if needed
        this.__selectedMinutes = parseInt(this.getAttribute('data-min') || 0, 10);
      });
      btn.__snooze_bound = true;
    }
  });

  // store a globally accessible value representing currently selected minutes (used by the old code)
  // compute initial value from the default pill or fallback 60
  let globalMinutes = 60;
  if (defaultBtn && defaultBtn.__selectedMinutes) globalMinutes = defaultBtn.__selectedMinutes;
  window.__snooze_selected_minutes = globalMinutes;
};
</script>

<script>
// FILTERS: reload page when filters (radio buttons) change
function applyServerFilters() {
  const params = new URLSearchParams(window.location.search);

  // Get selected status and date filters
  // const status = document.querySelector('input[name="filterStatus"]:checked');
  const when = document.querySelector('input[name="filterWhen"]:checked');

  // if (status && status.value) params.set('status', status.value);
  // else params.delete('status');

  if (when && when.value) params.set('when', when.value);
  else params.delete('when');
  
  const utype = document.querySelector('input[name="filterUtype"]:checked');
  if (utype && utype.value) params.set('utype', utype.value);
  else params.delete('utype');

  // Reload with new query parameters
  window.location = window.location.pathname + '?' + params.toString();
}

// Listen for clicks on all filter radio buttons
// document.querySelectorAll('input[name="filterStatus"], input[name="filterWhen"]').forEach(el => {
document.querySelectorAll('input[name="filterWhen"]').forEach(el => {
  el.addEventListener('change', applyServerFilters);
});
document.querySelectorAll('input[name="filterUtype"]').forEach(el => {
  el.addEventListener('change', applyServerFilters);
});
</script>

<script>
bindReminderButtons();
document.addEventListener('DOMContentLoaded', function() {

  // Modal & snooze logic
  const remForm = document.getElementById('reminderForm');
  const remId = document.getElementById('rem_id');
  const remDt = document.getElementById('rem_dt');
  const remContact = document.getElementById('rem_contact');
  const remNote = document.getElementById('rem_note');

  const pillBtns = Array.from(document.querySelectorAll('.quick-reminder-pill'));
  const preset = document.getElementById('snoozePreset');
  const snoozeNumber = document.getElementById('snoozeNumber');
  const snoozeUnit = document.getElementById('snoozeUnit');

  let selectedMinutes = null;

  function apiAction(payload){
    // return fetch('index.php?page=reminder_action', {
    return fetch('public/ajax/reminder_action_<?=$utype?>.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    }).then(r => r.json());
  }

  function resetSnoozeUI(){
    selectedMinutes = 60;
    pillBtns.forEach(b => b.classList.remove('active'));
    const defaultBtn = pillBtns.find(b => b.getAttribute('data-min') === '60');
    if (defaultBtn) defaultBtn.classList.add('active');
    if (preset) preset.value = '';
    if (snoozeNumber) snoozeNumber.value = '';
    if (snoozeUnit) snoozeUnit.value = 'm';
  }
  resetSnoozeUI();

  pillBtns.forEach(btn => {
    btn.addEventListener('click', function(){
      pillBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      preset.value = '';
      snoozeNumber.value = '';
      snoozeUnit.value = 'm';
      selectedMinutes = parseInt(this.getAttribute('data-min') || 0, 10);
    });
  });

  if (preset) {
    preset.addEventListener('change', function(){
      if (this.value) {
        selectedMinutes = parseInt(this.value, 10);
        pillBtns.forEach(b=>b.classList.remove('active'));
        snoozeNumber.value = '';
        snoozeUnit.value = 'm';
      } else selectedMinutes = null;
    });
  }
  if (snoozeNumber) {
    snoozeNumber.addEventListener('input', function(){
      const v = Number(this.value || 0);
      if (!v || v <= 0) { selectedMinutes = null; return; }
      const unit = snoozeUnit.value;
      if (unit === 'm') selectedMinutes = v;
      else if (unit === 'h') selectedMinutes = v * 60;
      else if (unit === 'd') selectedMinutes = v * 60 * 24;
      pillBtns.forEach(b=>b.classList.remove('active'));
      preset.value = '';
    });
    snoozeUnit.addEventListener('change', function(){ if (snoozeNumber.value) snoozeNumber.dispatchEvent(new Event('input')); });
  }

  // showToast wrapper (uses bootstrap toast in footer if present)
  function showToast(msg, timeout) {
    window.showToast && window.showToast(msg, timeout);
  }

  // Open reminder modal for snooze / complete / note
  function openReminderModal(reminder, mode) {
    const modalEl = document.getElementById('reminderModal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);

    remId.value = reminder.id || '';
    remDt.value = reminder.reminder_at ? (new Date(reminder.reminder_at)).toLocaleString() : '';
    remContact.value = reminder.contact_name || '';

    remNote.value = reminder.note || '';

    // reset snooze UI
    resetSnoozeUI();

    // If reminder has parseable date/time and you want to prefill custom date/time inputs, do it here:
    const parts = (reminder.reminder_at) ? (new Date(reminder.reminder_at)) : null;
    if (parts && !isNaN(parts)) {
      const yyyy = parts.getFullYear();
      const mm = String(parts.getMonth()+1).padStart(2,'0');
      const dd = String(parts.getDate()).padStart(2,'0');
      const hh = String(parts.getHours()).padStart(2,'0');
      const min = String(parts.getMinutes()).padStart(2,'0');
      document.getElementById('edit-reminder-date').value = `${yyyy}-${mm}-${dd}`;
      document.getElementById('edit-reminder-time').value = `${hh}:${min}`;
    } else {
      document.getElementById('edit-reminder-date').value = '';
      document.getElementById('edit-reminder-time').value = '';
    }

    // Show modal
    modal.show();

    // if mode is 'complete' - focus note
    if (mode === 'complete') {
      remNote.focus();
    }
  }

  // quick safeguard: prevent selecting past date/time
  document.getElementById('edit-reminder-date')?.addEventListener('change', function(){
    const today = new Date().toISOString().split('T')[0];
    if (this.value < today) {
      alert('Past dates are not allowed.');
      this.value = today;
    }
  });

  // Show daily task modal on first dashboard load of the day (per-browser)
  (function(){
    const dailyTarget = <?= (int)$daily_target ?>;
    const todayKey = 'dashboard_task_shown_' + (new Date()).toISOString().slice(0,10);
    if (!localStorage.getItem(todayKey)) {
      const modal = new bootstrap.Modal(document.getElementById('dailyTaskModal'));
      document.getElementById('dailyTargetCount').textContent = dailyTarget;
      modal.show();
      localStorage.setItem(todayKey, '1');
    }
  })();

  // delegated handler for quick-reminder-pill custom buttons
  document.addEventListener('click', function(e){
    const el = e.target.closest('.quick-reminder-pill');
    if (!el) return;
    e.preventDefault();
    const days = el.getAttribute('data-days');
    if (String(days) === 'custom') {
      document.getElementById('editCustomDateTime').style.display = '';
      document.getElementById('edit-reminder-date').required = true;
      document.getElementById('edit-reminder-time').required = true;
      return;
    }
    document.getElementById('editCustomDateTime').style.display = 'none';
    document.getElementById('edit-reminder-date').required = false;
    document.getElementById('edit-reminder-time').required = false;

    // mark active
    document.querySelectorAll('.quick-reminder-pill').forEach(b=>b.classList.remove('active'));
    el.classList.add('active');

    // If days > 0 set date accordingly
    if (days && parseInt(days,10) > 0) {
      const now = new Date();
      now.setDate(now.getDate() + parseInt(days,10));
      const yyyy = now.getFullYear();
      const mm = String(now.getMonth()+1).padStart(2,'0');
      const dd = String(now.getDate()).padStart(2,'0');
      document.getElementById('edit-reminder-date').value = `${yyyy}-${mm}-${dd}`;
      document.getElementById('edit-reminder-time').value = '10:00';
    }
  });

  // ----- COMPLETE modal submit handler (posts to reminder_action_<?=$utype?>.php) -----
  $('#completeConfirmForm').on('submit', function(e) {
    e.preventDefault();

    const id = $('#completeReminderId').val();
    const note = $('#completeReminderNote').val().trim();

    if (!note) {
        alert('Please enter a note before marking complete.');
        $('#completeReminderNote').focus();
        return;
    }

    if (!confirm('Mark this reminder complete?')) return;

    // fetch('index.php?page=reminder_action', {
    fetch('public/ajax/reminder_action_<?=$utype?>.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, action: 'complete', note: note })
    })
    .then(r => r.json())
    .then(resp => {
        if (resp && resp.ok) {
            let anyRemoved = false;

            // Remove matching reminder cards
            $('.btn-rem-action[data-reminder]').each(function() {
                try {
                    const dataAttr = $(this).data('reminder');
                    const reminder = (typeof dataAttr === 'object') ? dataAttr : JSON.parse(dataAttr || '{}');
                    if (String(reminder.id) === String(id)) {
                        const $card = $(this).closest('.reminder-card');
                        $card.remove();
                        anyRemoved = true;

                        // Check if card-body has no reminder cards left
                        const $cardBody = $card.closest('.reminder-body');
                        if ($cardBody.find('.reminder-card').length === 0) {
                            window.location.reload(); // reload if empty
                        }
                    }
                } catch (err) {
                    console.error('Invalid reminder JSON', err);
                }
            });

            if (anyRemoved) {
                $('#completeConfirmModal').modal('hide');
                if (typeof window.showToast === 'function') {
                    window.showToast('Reminder marked complete', 2500);
                }
            }

        } else {
            alert(resp?.error || 'Failed to mark complete');
        }
    })
    .catch(err => {
        console.error('Network or fetch error:', err);
        alert('Network error');
    });
  });


});
</script>


<!-- Employees missing site/customer modal -->
<div class="modal fade" id="reminderEmployeesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="z-index: 99999;">
      <div class="modal-header">
        <h5 class="modal-title">Employees missing Site / Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="reminderEmployeesBody">
        <div class="text-muted small">Loading...</div>
      </div>
      <div class="modal-footer">
        <div class="me-auto">
          <small class="text-muted" id="reminderEmployeesCount"></small>
        </div>
        <div class="btn-group">
          <button class="btn btn-outline-secondary btn-sm snooze-employees" data-days="1">Snooze 1d</button>
          <button class="btn btn-outline-secondary btn-sm snooze-employees" data-days="7">Snooze 7d</button>
          <button class="btn btn-outline-secondary btn-sm snooze-employees" data-days="30">Snooze 30d</button>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- Customer requirements expiring modal -->
<div class="modal fade" id="reminderReqsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="z-index: 99999;">
      <div class="modal-header">
        <h5 class="modal-title">Customer Job Requirements — Expiry Alert</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="reminderReqsBody">
        <div class="text-muted small">Loading...</div>
      </div>
      <div class="modal-footer">
        <div class="me-auto">
          <small class="text-muted" id="reminderReqsCount"></small>
        </div>
        <div class="btn-group">
          <button class="btn btn-outline-secondary btn-sm snooze-reqs" data-days="1">Snooze 1d</button>
          <button class="btn btn-outline-secondary btn-sm snooze-reqs" data-days="7">Snooze 7d</button>
          <button class="btn btn-outline-secondary btn-sm snooze-reqs" data-days="30">Snooze 30d</button>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- User documents expiring modal -->
<div class="modal fade" id="reminderDocsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="z-index: 99999;">
      <div class="modal-header">
        <h5 class="modal-title"><?=ucfirst($ucol)?> Documents — Expiry Alert</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="reminderDocsBody">
        <div class="text-muted small">Loading...</div>
      </div>
      <div class="modal-footer">
        <div class="me-auto">
          <small class="text-muted" id="reminderDocsCount"></small>
        </div>
        <!-- <div class="btn-group"> -->
          <!-- <button class="btn btn-outline-secondary btn-sm snooze-docs" data-days="1">Snooze 1d</button> -->
          <!-- <button class="btn btn-outline-secondary btn-sm snooze-docs" data-days="7">Snooze 7d</button> -->
          <!-- <button class="btn btn-outline-secondary btn-sm snooze-docs" data-days="30">Snooze 30d</button> -->
        <!-- </div> -->
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
(function () {
  const ajaxURL = 'public/ajax/dashboard_reminders.php';

  // localStorage keys
  const KEY_EMP_SNOOZE = 'snooze_reminder_employees_until';
  const KEY_REQ_SNOOZE = 'snooze_reminder_reqs_until';
  const KEY_DOC_SNOOZE = 'snooze_reminder_docs_until';

  function isSnoozed(key) {
    const v = localStorage.getItem(key);
    if (!v) return false;
    const until = parseInt(v, 10);
    if (isNaN(until)) return false;
    return Date.now() < until;
  }

  function setSnooze(key, days) {
    const ms = days * 24 * 60 * 60 * 1000;
    const until = Date.now() + ms;
    localStorage.setItem(key, String(until));
  }

  function humanDate(dStr) {
    if (!dStr) return '-';
    const dt = new Date(dStr);
    if (isNaN(dt)) return dStr;
    return dt.toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function renderEmployeesModal(employees) {
    const $body = $('#reminderEmployeesBody');
    if (!employees.length) {
      $body.html('<div class="small text-muted">No employees without site/customer.</div>');
      $('#reminderEmployeesCount').text('');
      return;
    }

    let html = '<div class="list-group">';
    employees.forEach(emp => {
      html += `<div class="list-group-item d-flex justify-content-between align-items-start">
        <div>
          <div class="fw-bold">${escapeHtml(emp.name || '—')}</div>
          <div class="small text-muted">Site: ${escapeHtml(emp.current_site || '—')} &nbsp; • &nbsp; Customer: ${escapeHtml(emp.current_customer || '—')}</div>
          <div class="small text-muted">Phone: ${escapeHtml(emp.phone || '')} ${emp.email? ' • ' + escapeHtml(emp.email) : ''}</div>
        </div>
        <div class="text-end">
          <a href="./?page=employees_view&id=${emp.id}" class="btn btn-sm btn-outline-primary" target="_blank">Open</a>
        </div>
      </div>`;
    });
    html += '</div>';
    $body.html(html);
    $('#reminderEmployeesCount').text(`${employees.length} employee(s) missing site/customer`);
  }

  function renderReqsModal(reqs) {
    const $body = $('#reminderReqsBody');
    if (!reqs.length) {
      $body.html('<div class="small text-muted">No customer requirements expiring soon.</div>');
      $('#reminderReqsCount').text('');
      return;
    }

    let html = '<div class="list-group">';
    reqs.forEach(r => {
      html += `<div class="list-group-item d-flex justify-content-between align-items-start">
        <div>
          <div class="fw-bold">${escapeHtml(r.job_title || '—')}</div>
          <div class="small text-muted">${escapeHtml(r.customer_name || '—')}</div>
          <div class="small text-muted">Expiry: ${humanDate(r.expiry)} → Expires on: ${humanDate(r.expiry_plus_14)}</div>
        </div>
        <div class="text-end">
          <a href="./?page=customers_view&id=${r.customer_id}" class="btn btn-sm btn-outline-primary" target="_blank">Open</a>
        </div>
      </div>`;
    });
    html += '</div>';
    $body.html(html);
    $('#reminderReqsCount').text(`${reqs.length} requirement(s) expiring soon`);
  }

  function renderDocsModal(docs) {
      const $body = $('#reminderDocsBody');

      if (!docs.length) {
          $body.html('<div class="small text-muted">No documents expiring soon.</div>');
          $('#reminderDocsCount').text('');
          return;
      }

      let html = '<div class="list-group">';

      docs.forEach(d => {

          // Determine correct view page based on utype
          let page = 'customers_view'; // default fallback

          switch (d.utype) {
              case 'customers':
                  page = 'customers_view';
                  break;
              case 'suppliers':
                  page = 'suppliers_view';
                  break;
              case 'employees':
                  page = 'employees_view';
                  break;
              case 'recruiters':
                  page = 'recruiters_view';
                  break;
          }

          html += `<div class="list-group-item d-flex justify-content-between align-items-start">
            <div>
              <div class="fw-bold">${escapeHtml(d.user_name || '—')}</div>
              <div class="small text-muted">${escapeHtml(d.label || '—')}</div>
              <div class="small text-muted">
                Expires on ${humanDate(d.expiry_date)}
              </div>
            </div>
            <div class="text-end">
              <a href="./?page=${page}&id=${d.user_id}"
                 class="btn btn-sm btn-outline-primary"
                 target="_blank">Open</a>
            </div>
          </div>`;
      });

      html += '</div>';

      $body.html(html);
      $('#reminderDocsCount').text(`${docs.length} document(s) expiring soon`);
  }


  // Minimal escape to avoid XSS
  function escapeHtml(s) {
    if (!s && s !== 0) return '';
    return String(s).replace(/[&<>"'`]/g, function (m) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',"`":'&#96;'}[m];
    });
  }

  // attach snooze button handlers
  $(document).on('click', '.snooze-employees', function () {
    const days = parseInt($(this).attr('data-days') || '1', 10);
    setSnooze(KEY_EMP_SNOOZE, days);
    const modal = bootstrap.Modal.getInstance($('#reminderEmployeesModal')[0]);
    if (modal) modal.hide();
  });

  $(document).on('click', '.snooze-reqs', function () {
    const days = parseInt($(this).attr('data-days') || '1', 10);
    setSnooze(KEY_REQ_SNOOZE, days);
    const modal = bootstrap.Modal.getInstance($('#reminderReqsModal')[0]);
    if (modal) modal.hide();
  });

  // main fetch & show
  function fetchReminders() {
    $.post(ajaxURL, {utype: '<?=$utype?>'}, function (res) {
      if (!res || !res.success) return;

      // employees
      const emps = res.employees || [];
      renderEmployeesModal(emps);
      if (emps.length && !isSnoozed(KEY_EMP_SNOOZE)) {
        new bootstrap.Modal($('#reminderEmployeesModal')).show();
      }

      // docuements expiry - update pending
      const docs = res.documents || [];
      renderDocsModal(docs);
      if (docs.length && !isSnoozed(KEY_DOC_SNOOZE)) {
        new bootstrap.Modal($('#reminderDocsModal')).show();
      }

      // requirements
      const reqs = res.requirements || [];
      renderReqsModal(reqs);
      if (reqs.length && !isSnoozed(KEY_REQ_SNOOZE)) {
        new bootstrap.Modal($('#reminderReqsModal')).show();
      }
    }, 'json').fail(function () {
      // optional console error
      // console.error('Failed to load reminders');
    });
  }

  // run on page load
  $(function () {
    // fetch once
    fetchReminders();

    // optional: refresh every X minutes (e.g., 30 minutes)
    setInterval(fetchReminders, 30 * 60 * 1000);
  });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
