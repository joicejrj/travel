<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

$uid = $CURRENT_USER_ID;

// --- Capture filters ---
$search        = $_GET['kw'] ?? '';
$phone_filter  = $_GET['phone_filter'] ?? '';
$status_filter = $_GET['status'] ?? '';
$timezone      = $_GET['timezone'] ?? '';

// $where  = ["s.agent_id = ?"];
// $params = [$uid];
// $types  = 'i';
$where  = [];
$params = [];
$types  = '';

// --- Search keyword ---
if (!empty($search)) {
  $where[] = "(s.name LIKE ? OR s.phone LIKE ? OR s.email LIKE ? OR s.company LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $types   .= 'ssss';
}

// --- Phone filter ---
if ($phone_filter === 'has') {
  $where[] = "(s.phone IS NOT NULL AND s.phone <> '')";
} elseif ($phone_filter === 'none') {
  $where[] = "(s.phone IS NULL OR s.phone = '')";
}

// --- Status filter ---
if (!empty($status_filter)) {
  $where[] = "s.type = ?";
  $params[] = $status_filter;
  $types   .= 's';
}

// --- Timezone filter ---
if (!empty($timezone)) {
  $where[] = "s.timezone = ?";
  $params[] = $timezone;
  $types   .= 's';
}

// Fetch distinct timezones from customers
$tz_result = $mysqli->query("SELECT DISTINCT timezone FROM customers WHERE timezone IS NOT NULL AND timezone <> '' ORDER BY timezone ASC");

$timezones = [];
if ($tz_result && $tz_result->num_rows > 0) {
  while ($row = $tz_result->fetch_assoc()) {
    $timezones[] = $row['timezone'];
  }
}

?>
<!-- NOTE: 'My customers' heading removed as requested -->

<!-- Compact Filter Bar with Add New on the left -->
<div class="card mb-0">
  <div class="card-body py-2">
    <form method="get" class="row g-2 align-items-center" id="customersFilterForm">
      <input type="hidden" name="page" value="contacts">

      <!-- Add New moved into filter line as left-most -->
    

      <!-- Spacer so the rest of filters align to the right -->
      <div class="col"></div>

      <!-- Phone Filter (still hidden) -->
      <div class="col-auto" style="display: none;">
        <select name="phone_filter" class="form-select auto-submit">
          <option value="">Phone (Any)</option>
          <option value="has" <?= ($_GET['phone_filter'] ?? '') == 'has' ? 'selected' : '' ?>>Has Phone</option>
          <option value="none" <?= ($_GET['phone_filter'] ?? '') == 'none' ? 'selected' : '' ?>>No Phone</option>
        </select>
      </div>

      <!-- Status Filter -->
      <div class="col-auto"  style="display: none;">
        <select name="status" id="statussel" class="form-select auto-submit" style="display: none;">
          <option value="">Status (All)</option>
          <?php foreach ($customers_statuses as $key => $label): ?>
            <option value="<?= esc($key) ?>" <?= ($_GET['status'] ?? '') === $key ? 'selected' : '' ?>>
              <?= esc($key) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button type="button" class="btn <?= ((isset($_GET['status'])&&$_GET['status']=='')||(!isset($_GET['status']))?'btn-primary' : 'btn-outline-primary') ?> btn-sm status-btn" data-value="">
            All
          </button>
        <?php
          foreach($customers_statuses as $key=>$val): ?>
            <button type="button" class="btn <?= (isset($_GET['status'])&&$_GET['status']==$key?'btn-primary' : 'btn-outline-primary') ?> btn-sm status-btn" data-value="<?=htmlspecialchars($key)?>">
              <?=htmlspecialchars($key)?>
            </button>
        <?php endforeach; ?>

      </div>

      <!-- Timezone Filter (hidden by default) -->
      <div class="col-auto" style="display: none;">
        <label class="form-label visually-hidden">Timezone</label>
        <select name="timezone" class="form-select auto-submit" id="tzSelect">
          <option value="">Any Timezone</option>
          <?php foreach ($timezones as $tz): ?>
            <option value="<?= esc($tz) ?>" <?= $timezone === $tz ? 'selected' : '' ?>>
              <?= esc($tz) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Search (auto-submit on keyup, min 3 chars) -->
      <div class="col-auto">
        <input type="text"
               name="kw"
               value="<?= esc($search) ?>"
               placeholder="Search Customers Name or Company..."
               class="form-control"
               id="kwInput"
               style="min-width: 300px;">
      </div>

      <!-- Actions: removed filter click button as requested -->
      <div class="col-auto d-flex gap-1">
        <?php if (!empty($search) || !empty($_GET['status']) || !empty($_GET['timezone']) || !empty($_GET['phone_filter'])): ?>
          <a href="index.php?page=contacts" class="btn btn-sm btn-secondary">
            <i class="fas fa-times"></i>
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Main card lifted up: removed mt-n2 and reduced spacing -->
<div class="card shadow-sm mt-0">
  <div class="card-body">
    <div class="table-responsive">
      <table id="customersTable" class="table table-striped table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Company Name</th>
            <th>Contact</th>
            <th>Phone</th>
            <th>Email</th>
            <th class="text-center" style="width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT DISTINCT s.id, s.name, s.company, s.email, s.phone, s.date_added 
                  FROM contacts AS s
                  WHERE s.archived = 0";
          if ($where) $sql .= " AND " . implode(" AND ", $where);
          $sql .= " ORDER BY s.id DESC";

          $stmt = $mysqli->prepare($sql);
          if ($params) $stmt->bind_param($types, ...$params);
          $stmt->execute();
          $res = $stmt->get_result();

          while ($r = $res->fetch_assoc()):
            // $starClass = $r['favourite'] ? "fas fa-star" : "far fa-star";

          ?>
          <tr data-id="<?= (int)$r['id'] ?>">
            <td><?= (int)$r['id'] ?></td>
            <td>
              <?= esc($r['company']) ?>
            </td>
            
            <td>
              <?= esc($r['name']) ?>
            </td>

            <td>
              <?= $r['email'] ? '<br>' . esc($r['email']) : '' ?>
            </td>

            <td>
              <?= $r['phone'] ? '<br>' . esc($r['phone']) : '' ?>
            </td>

            <td class="text-center">
              <button class="btn btn-sm btn-outline-success me-1" title="Recent Actions" onclick="openRecentActionsModal(<?=$r['id'] ?? 0 ?>)">
                <i class="fas fa-history"></i>
              </button>
              <a href="?page=contacts_view&id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="View & Edit">
                <i class="fas fa-user-cog"></i>
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- RECENT ACTIONS MODAL -->
<div class="modal fade" id="recentActionsModal" tabindex="-1" aria-labelledby="recentActionsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg overflow-hidden">

      <!-- HEADER -->
      <div class="modal-header py-3">
        <h5 class="modal-title fw-semibold d-flex align-items-center" id="recentActionsModalLabel">
          <i class="fa fa-history me-2"></i> Recent Actions
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body bg-light p-4">

        <!-- Loader -->
        <div id="recentActionsLoader" class="text-center py-5 text-muted small">
          <i class="fa fa-spinner fa-spin me-2"></i> Loading recent actions...
        </div>

        <!-- Table -->
        <div class="card border-0 shadow-sm d-none" id="recentActionsTableBox">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-striped mb-0 align-middle" id="recentActionsTable" style="width:100%;">
                <thead class="table-light">
                  <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 25%;">Action</th>
                    <!-- <th>Details</th> -->
                    <th style="width: 20%;">By</th>
                    <th style="width: 15%;">Date</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- FOOTER -->
      <div class="modal-footer bg-white border-top">
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
          <i class="fa fa-times me-1"></i> Close
        </button>
      </div>

    </div>
  </div>
</div>
<script>
$(function() {
  const modal = new bootstrap.Modal('#recentActionsModal');
  let dt = null;

  // Function to open the modal and load recent actions
  window.openRecentActionsModal = function(customerId) {
    modal.show();
    $('#recentActionsLoader').removeClass('d-none');
    $('#recentActionsTableBox').addClass('d-none');

    // If DataTable already exists — update its AJAX data and reload
    if (dt !== null) {
      dt.settings()[0].ajax.data.contact_id = customerId; // update param
      dt.ajax.reload(() => {
        $('#recentActionsLoader').addClass('d-none');
        $('#recentActionsTableBox').removeClass('d-none');
      }, false);
      return;
    }

    // Initialize DataTable only once
    dt = $('#recentActionsTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      searching: true,
      ordering: true,
      pageLength: 10,
      order: [[3, 'desc']], // Sort by date DESC

      ajax: {
        url: 'public/ajax/contacts_recent_actions.php',
        type: 'POST',
        data: { contact_id: customerId }, // Initial param
        dataSrc: function(json) {
          $('#recentActionsLoader').addClass('d-none');
          $('#recentActionsTableBox').removeClass('d-none');
          return json.data || [];
        },
        error: function() {
          $('#recentActionsLoader').html(`<div class="text-danger small py-3">Error loading actions.</div>`);
        }
      },

      columns: [
        { data: 'id', title: '#' },
        { data: 'action', title: 'Action' },
        // { data: 'details', title: 'Details' },
        { data: 'by', title: 'By' },
        { data: 'date', title: 'Date' }
      ],

      language: {
        processing: '<i class="fa fa-spinner fa-spin me-2"></i> Loading...'
      }
    });
  };
});
</script>

<script>
$('.status-btn').on('click', function() {
    $('.status-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
    $(this).addClass('active btn-primary').removeClass('btn-outline-primary');
    $('#statussel').val($(this).data('value')).trigger('change');
    this.form.submit();
});
</script>
<script>
$(document).ready(function(){
  $('#customersTable').DataTable({
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],
    order: [[0, 'desc']],
    searching: false, // hides the search box
    language: { search: "_INPUT_", searchPlaceholder: "Search customers..." }
  });

  // Toggle favourite
  $(document).on('click', '.fav-star', function(){
    var star = $(this);
    var id = star.data('id');
    $.post('ajax/customers_fav.php', { id: id }, function(resp){
      if(resp.success) star.toggleClass('fas far');
      else alert('Error toggling favourite');
    }, 'json');
  });

});
</script>

<script>
// Populate timezone list dynamically
document.addEventListener('DOMContentLoaded', () => {
  const tzSelect = document.getElementById('tzSelect');
  if (tzSelect && Intl.supportedValuesOf) {
    const zones = Intl.supportedValuesOf('timeZone');
    const current = "<?= esc($_GET['timezone'] ?? '') ?>";
    zones.forEach(z => {
      const opt = document.createElement('option');
      opt.value = z;
      opt.textContent = z;
      if (z === current) opt.selected = true;
      tzSelect.appendChild(opt);
    });
  }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // existing auto-submit on change
  document.querySelectorAll('.auto-submit').forEach(function(el) {
    el.addEventListener('change', function() {
      this.form.submit();
    });
  });

  // Search on keyup (min 3 chars) with debounce, and reset when cleared to 0
  const kwInput = document.getElementById('kwInput');
  const form = document.getElementById('customersFilterForm');
  let debounceTimer = null;
  if (kwInput && form) {
    kwInput.addEventListener('keyup', function(e) {
      clearTimeout(debounceTimer);
      const val = this.value.trim();
      debounceTimer = setTimeout(() => {
        // Submit only when 3 or more chars, or when empty (to reset)
        if (val.length === 0 || val.length >= 3) {
          form.submit();
        }
        // else: do nothing (wait for more typing)
      }, 350);
    });

    // Optional: allow Enter to submit only if >=3 or empty
    kwInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        const v = this.value.trim();
        if (v.length === 0 || v.length >= 3) {
          // let normal submit happen
        } else {
          e.preventDefault();
        }
      }
    });
  }
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
