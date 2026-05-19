<?php
// public/payment_settings.php
// Manage application payment_settings: view + update value per row.
// Requires includes/header.php to provide $mysqli, require_login(), Bootstrap CSS/JS, etc.

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/includes/header.php';
require_login();

// esc() helper fallback
if (!function_exists('esc')) {
    function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
}

// CSRF token
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf_token'];

$msg = ''; $err = '';
$action = $_POST['action'] ?? null;

// POST handling: update setting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    if (empty($_POST['csrf_token']) || !hash_equals($csrf, $_POST['csrf_token'])) {
        $err = "Invalid CSRF token.";
    } else {
        if ($action === 'update') {
            $setting_id = (int)($_POST['setting_id'] ?? 0);
            $value = trim($_POST['value'] ?? '');

            if ($setting_id <= 0) {
                $err = "Invalid setting id.";
            } else {
                // Example: you may want to restrict certain payment_settings to numeric values.
                // Leave generic so it accepts strings/numbers. Add validation here if needed.
                $stmt = $mysqli->prepare("UPDATE payment_settings SET value = ?, updated_at = NOW() WHERE id = ?");
                if (!$stmt) {
                    $err = "DB prepare error: " . $mysqli->error;
                } else {
                    $stmt->bind_param('si', $value, $setting_id);
                    if ($stmt->execute()) {
                        $msg = "Setting updated.";
                    } else {
                        $err = "DB error: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        } else {
            $err = "Unknown action.";
        }
    }
}

// If requested via GET edit parameter (non-JS fallback)
$editing = null;
$edit_id = (int)($_GET['edit'] ?? 0);
if ($edit_id) {
    $stmt = $mysqli->prepare("SELECT id, name, value, updated_at FROM payment_settings WHERE id=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $edit_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $editing = $res->fetch_assoc();
        $stmt->close();
    }
}

// Fetch all payment_settings
$list = [];
$res = $mysqli->query("SELECT id, name, value, updated_at FROM payment_settings ORDER BY id ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) $list[] = $r;
}
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Payment Settings</h1>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?=esc($msg)?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger"><?=esc($err)?></div><?php endif; ?>

  <div class="row">
    <div class="col-12">
      <div class="table-responsive">
        <table id="payment_settingsTable" class="table table-sm table-striped" style="width:100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Value</th>
              <th>Updated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($list as $s): ?>
              <?php
                $data = json_encode([
                  'id' => (int)$s['id'],
                  'name' => $s['name'],
                  'value' => $s['value'],
                ], JSON_HEX_APOS | JSON_HEX_QUOT);
              ?>
              <tr>
                <td><?= (int)$s['id'] ?></td>
                <td><?= esc($s['name']) ?></td>
                <td><?= esc($s['value']) ?></td>
                <td><?= esc($s['updated_at']) ?></td>
                <td>
                  <button
                    class="btn btn-sm btn-outline-primary btn-edit-setting"
                    type="button"
                    data-setting='<?= $data ?>'
                    data-bs-toggle="modal"
                    data-bs-target="#settingModal"
                  >Edit</button>
                  
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($list)): ?>
              <tr><td colspan="5" class="text-muted">No payment settings found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Edit Setting Modal -->
<div class="modal fade" id="settingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form method="post" id="settingForm">
        <div class="modal-header">
          <h5 class="modal-title" id="settingModalTitle">Edit Setting</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?=esc($csrf)?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="setting_id" id="settingId" value="0">

          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" id="settingName" class="form-control" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label">Value</label>
            <input type="text" name="value" id="settingValue" class="form-control" required>
            <div class="form-text">Enter the new value for this setting. If numeric expected, ensure correct format.</div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" id="modalSubmitBtn" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
if (typeof jQuery === 'undefined') {
  document.write('<script src="https://code.jquery.com/jquery-3.7.1.min.js"><\/script>');
}
</script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // init datatable (unchanged)...
  (function initDt(){
    function ready(fn) {
      var tries = 0;
      var t = setInterval(function(){
        tries++;
        if (typeof $ !== 'undefined' && $.fn && $.fn.dataTable) {
          clearInterval(t); fn(); return;
        }
        if (tries > 60) clearInterval(t);
      }, 80);
    }
    ready(function(){
      $('#payment_settingsTable').DataTable({
        pageLength: 25,
        lengthMenu: [10,25,50,100],
        columnDefs: [{ orderable: false, targets: -1 }],
        order: [[0, 'asc']]
      });
    });
  })();

  // modal elements
  var settingModalEl = document.getElementById('settingModal');
  var settingId = document.getElementById('settingId');
  var settingName = document.getElementById('settingName');
  var settingValue = document.getElementById('settingValue');
  var modalTitle = document.getElementById('settingModalTitle');

  // Edit buttons: populate form only. DO NOT call bs.show() here because the button has data-bs-toggle/data-bs-target.
  document.querySelectorAll('.btn-edit-setting').forEach(function(btn){
    btn.addEventListener('click', function(){
      var raw = btn.getAttribute('data-setting') || '{}';
      var data;
      try { data = JSON.parse(raw); } catch(e) { data = {}; }

      modalTitle.textContent = 'Edit Setting';
      settingId.value = data.id || 0;
      settingName.value = data.name || '';
      settingValue.value = data.value ?? '';

      // Let Bootstrap handle opening (data-bs-* on the button).
    });
  });

  // If page was loaded with ?edit=ID (server-provided $editing), populate and show modal programmatically
  <?php if ($editing): ?>
    (function(){
      var edit = {
        id: <?= (int)$editing['id'] ?>,
        name: <?= json_encode($editing['name'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,
        value: <?= json_encode($editing['value'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,
      };
      settingId.value = edit.id;
      settingName.value = edit.name;
      settingValue.value = edit.value ?? '';
      modalTitle.textContent = 'Edit Setting';

      // Use getInstance if exists, otherwise create & show
      try {
        var inst = bootstrap.Modal.getInstance(settingModalEl);
        if (!inst) inst = new bootstrap.Modal(settingModalEl);
        inst.show();
      } catch(e) {
        // ignore; fallback to data attribute behavior
      }
    })();
  <?php endif; ?>

  // CLEANUP: ensure no stray backdrops remain when any modal hides.
  document.addEventListener('hidden.bs.modal', function(event) {
    // remove any leftover backdrop elements
    document.querySelectorAll('.modal-backdrop').forEach(function(d){
      d.parentNode && d.parentNode.removeChild(d);
    });
    // ensure body doesn't keep modal-open class
    document.body.classList.remove('modal-open');
    // restore body padding-right if bootstrap modified it (safe fallback)
    document.body.style.paddingRight = '';
  });
});
</script>
