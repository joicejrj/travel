<?php
// interactions_list.php
require_once __DIR__ . '/_auth.php'; // adjust if your auth path differs
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php'; // optional

require_once __DIR__ . '/includes/header.php'; // include your page header (nav, styles)

$uid = $_SESSION['person_id'] ?? 0;
$uname = $_SESSION['person_name'] ?? 'Agent';

?>
<!-- Page content: Filters on top -> DataTable below (Layout A) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
/* Minimal styling to match your app */
.page-filters { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; margin:14px 0; }
.filter-field { display:flex; flex-direction:column; min-width:180px; }
.filter-field label { font-size:12px; color:#6b7280; margin-bottom:6px; font-weight:600; }
.filter-actions { display:flex; gap:8px; align-items:center; }
.dt-actions button { margin-right:6px; padding:6px 8px; border-radius:6px; border:1px solid #e5e7eb; background:#fff; cursor:pointer; }
#interactionsTable { width:100% !important; }
</style>

<div class="container py-4">
  <div class="d-flex align-items-center gap-3 mb-3">
    <h4 class="mb-0">Admin Dashboard</h4>

    <input type="text"
           id="dash-range"
           class="form-control"
           style="max-width:260px">
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <small>Total</small>
          <h3 id="d-total">—</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <small>Open</small>
          <h3 id="d-open" class="text-primary">—</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <small>Working</small>
          <h3 id="d-working" class="text-warning">—</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <small>Closed</small>
          <h3 id="d-closed" class="text-success">—</h3>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">

    <div class="col-md-12">
      <h6 class="fw-semibold mb-2">By Channel</h6>
      <div class="row g-2" id="by-channel"></div>
    </div>

    <div class="col-md-6">
      <h6 class="fw-semibold mb-2">By Contact Type</h6>
      <ul id="by-type" class="list-group small mb-3"></ul>
    </div>

    <div class="col-md-6">
      <h6 class="fw-semibold mb-2">By Scenario</h6>
      <ul id="by-scenario" class="list-group small mb-3" style="max-height: 240px; overflow-y: scroll;"></ul>
    </div>

  </div>

</div>


<!-- Dependencies -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<?php
require_once __DIR__ . '/includes/footer.php'; // optional: your page footer
?>

<script>
const start = moment().subtract(6,'days');
const end   = moment();
$('#dash-range').daterangepicker({
  startDate: start,
  endDate: end,
  locale: { format: 'DD-MM-YYYY' },
  opens: 'right',
  ranges: {
      'Today': [moment(), moment()],
      'Yesterday': [moment().subtract(1,'days'), moment().subtract(1,'days')],
      'Last 7 Days': [moment().subtract(6,'days'), moment()],
      'Last 30 Days': [moment().subtract(29,'days'), moment()],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
      'Last Month': [
          moment().subtract(1,'month').startOf('month'),
          moment().subtract(1,'month').endOf('month')
      ]
  }
});

function loadDashboard() {
  $.getJSON(
    'public/ajax/admin_dashboard.php',
    { date_range: $('#dash-range').val() },
    function(res){
      if (!res.success) return;

      $('#d-total').text(res.summary.total);
      $('#d-open').text(res.summary.open);
      $('#d-working').text(res.summary.working);
      $('#d-closed').text(res.summary.closed);

      $('#by-channel').html(
        res.channels.map(c => `
          <div class="col-3">
            <div class="card text-center shadow-sm">
              <div class="card-body py-3">
                <small class="text-muted">${c.name ?? 'Unknown'}</small>
                <h4 class="mb-0 text-primary">${c.cnt}</h4>
              </div>
            </div>
          </div>
        `).join('')
      );

      $('#by-type').html(
        res.types.map(t =>
          `<li class="list-group-item d-flex justify-content-between">
             ${t.name ?? 'Unknown'} <span>${t.cnt}</span>
           </li>`
        ).join('')
      );


      $('#by-scenario').html(
        res.scenarios.map(s =>
          `<li class="list-group-item d-flex justify-content-between">
             ${s.name ?? 'Unknown'} <span>${s.cnt}</span>
           </li>`
        ).join('')
      );


    }
  );
}

$('#dash-range').on('apply.daterangepicker', loadDashboard);
loadDashboard();

</script>