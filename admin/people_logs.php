<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/includes/header.php';
require_login();

if (!function_exists('esc')) {
    function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
}

// get agents for filter dropdown
$agents = [];
$res = $mysqli->query("SELECT id, name FROM people ORDER BY name ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) $agents[] = $r;
}

// filters
$agent_id = isset($_GET['agent_id']) ? (int)$_GET['agent_id'] : 0;
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

$where = [];
$params = [];
$types = '';

if ($agent_id > 0) {
    $where[] = 'pl.agent_id = ?';
    $params[] = $agent_id;
    $types .= 'i';
}

if ($from && $to) {
    $where[] = 'DATE(pl.timestamp) BETWEEN ? AND ?';
    $params[] = date('Y-m-d', strtotime($from));
    $params[] = date('Y-m-d', strtotime($to));
    $types .= 'ss';
}

$sql = "SELECT pl.id, pl.agent_id, pl.admin, pl.log, pl.timestamp, pl.ip, p.name AS agent_name, pl.customer_id, pl.recruiter_id, pl.employee_id 
        FROM people_logs pl 
        LEFT JOIN people p ON pl.agent_id = p.id";

if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY pl.id DESC";

$stmt = $mysqli->prepare($sql);
if ($stmt && $params) $stmt->bind_param($types, ...$params);
if ($stmt) {
    $stmt->execute();
    $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $logs = [];
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Users Activities</h1>
  </div>

  <form method="get" class="row gy-2 gx-3 align-items-end mb-3">
    <div class="col-md-4">
        <input type="hidden" name="page" value="users_logs">
      <label for="agent_id" class="form-label">User</label>
      <select name="agent_id" id="agent_id" class="form-select">
        <option value="0">All Users</option>
        <?php foreach($agents as $a): ?>
          <option value="<?= (int)$a['id'] ?>" <?= $agent_id==$a['id']?'selected':'' ?>>
            <?= esc($a['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-4">
      <label for="daterange" class="form-label">Date Range</label>
      <input type="text" id="daterange" class="form-control" readonly>
      <input type="hidden" name="from" id="from" value="<?= esc($from) ?>">
      <input type="hidden" name="to" id="to" value="<?= esc($to) ?>">
    </div>

    <div class="col-md-4">
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="?page=users_logs" class="btn btn-secondary">Reset</a>
    </div>
  </form>

  <div class="table-responsive">
      <table id="logsTable" class="table table-striped table-bordered table-sm">
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Type</th>
            <th>Name</th>
            <th>Activity</th>
            <th>IP</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <!-- <tr class="no-data">
              <td colspan="5" class="text-center text-muted py-4">No logs found</td>
            </tr> -->
          <?php else: ?>
            <?php foreach($logs as $log): 
                $admin_display = '';
                if (!empty($log['admin'])) {
                    $a = json_decode($log['admin'], true);
                    if (is_array($a)) {
                        $admin_display = '<br>(Autologin:'.esc($a['role'] ?? '') . '-' . esc($a['name'] ?? '') . ')';
                    } else {
                        $admin_display = '';
                    }
                }
                $utype = $uname = "-";
                if (!empty($log['customer_id'])) {
                  $utype = "Customer";
                  $getu = $db->get('customers',array('id'=>$log['customer_id']),'id,name');
                  $uname = $getu?$getu->name:'-';
                }
                else if (!empty($log['recruiter_id'])) {
                  $utype = "Recruiter";
                  $getu = $db->get('recruiters',array('id'=>$log['recruiter_id']),'id,name');
                  $uname = $getu?$getu->name:'-';
                }
                else if (!empty($log['employee_id'])) {
                  $utype = "Employee";
                  $getu = $db->get('employees',array('id'=>$log['employee_id']),'id,name');
                  $uname = $getu?$getu->name:'-';
                }
            ?>
              <tr>
                <td><?= (int)$log['id'] ?></td>
                <td><?= esc($log['agent_name'] ?? '') ?><?= $admin_display ?></td>
                <td><?= esc($utype) ?></td>
                <td><?= esc($uname) ?></td>
                <td><?= $log['log'] ?></td>
                <td><?= esc($log['ip']) ?></td>
                <td><?= esc($log['timestamp']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

</div>

<!-- DOCUMENT VIEWER MODAL -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-sm rounded-3 overflow-hidden" style="max-height: 90vh;">
      
      <!-- HEADER -->
      <div class="modal-header bg-white border-bottom py-2 px-3">
        <h6 class="modal-title fw-semibold text-primary mb-0 d-flex align-items-center gap-2">
          <i class="fa fa-file-text"></i> <span id="documentModalLabel">View Document</span>
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body p-0 bg-light" id="documentViewer" style="height: 80vh; display: flex; justify-content: center; align-items: center;">
        <div class="text-center text-muted small">Loading document...</div>
      </div>

      <!-- FOOTER -->
      <div class="modal-footer bg-white py-2 px-3">
        <button type="button" class="btn btn-light btn-sm border" data-bs-dismiss="modal">
          <i class="fa fa-times me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>
<script>
$(document).on('click', '.view-document', function () {
    const file  = $(this).attr('data-file');
    const type  = $(this).attr('data-type');
    const label = $(this).attr('data-label');

  $('#documentModalLabel').text(label);
  const viewer = $('#documentViewer');

  if (!file) {
    viewer.html('<div class="text-center text-muted small py-5">File not found.</div>');
    new bootstrap.Modal('#documentModal').show();
    return;
  }

  // Choose rendering mode
  let html = '';
  if (type === 'pdf') {
    html = `
      <iframe src="../${file}" frameborder="0" width="100%" height="100%" 
              style="border:none; background:#fff;"></iframe>`;
    // html = `
    //     <iframe src="https://docs.google.com/gview?url=${file}&embedded=true" 
    //       frameborder="0" width="100%" height="100%" 
    //       style="border:none; background:#fff;"></iframe>`;

  } else {
    html = `
      <img src="${file}" alt="Document Preview" class="img-fluid rounded shadow-sm"
           style="max-height: 80vh; object-fit: contain;">`;
  }

  //quotation
  $('#versionsModal').modal('hide');

  viewer.html(html);
  new bootstrap.Modal('#documentModal').show();
});
</script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
$(function () {
  // --- Initialize moment.js date range values safely ---
  const from = '<?= isset($from) && $from ? esc($from) : '' ?>';
  const to = '<?= isset($to) && $to ? esc($to) : '' ?>';

  const start = from ? moment(from, 'YYYY-MM-DD') : moment().subtract(6, 'days');
  const end = to ? moment(to, 'YYYY-MM-DD') : moment();

  // --- Callback to update input fields ---
  function updateRange(start, end) {
    $('#daterange').val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
    $('#from').val(start.format('YYYY-MM-DD'));
    $('#to').val(end.format('YYYY-MM-DD'));
  }

  // --- Initialize Date Range Picker ---
  if ($('#daterange').length) {
    $('#daterange').daterangepicker({
      locale: {
        format: 'DD-MM-YYYY',
        separator: ' - ',
      },
      startDate: start,
      endDate: end,
      ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [
          moment().subtract(1, 'month').startOf('month'),
          moment().subtract(1, 'month').endOf('month'),
        ],
      },
    }, updateRange);

    // Set initial range display
    updateRange(start, end);
  }

    // --- Initialize DataTable safely ---
    const $table = $('#logsTable');
      // Initialize DataTable safely
      $table.DataTable({
        pageLength: 10,
        order: [[0, 'desc']],
        autoWidth: false,
        responsive: true,
        language: {
          emptyTable: 'No activity available',
        },
        columnDefs: [
          { targets: '_all', defaultContent: '' } // Prevents mismatch errors
        ]
      });

});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>