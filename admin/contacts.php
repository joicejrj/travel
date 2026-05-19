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

$where  = ["s.agent_id = ?"];
$params = [$uid];
$types  = 'i';

// --- Search keyword ---
if (!empty($search)) {
  $where[] = "(s.name LIKE ? OR s.phone LIKE ? OR s.email LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $types   .= 'sss';
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

// Fetch distinct timezones from contacts
$tz_result = $mysqli->query("SELECT DISTINCT timezone FROM contacts WHERE timezone IS NOT NULL AND timezone <> '' ORDER BY timezone ASC");

$timezones = [];
if ($tz_result && $tz_result->num_rows > 0) {
  while ($row = $tz_result->fetch_assoc()) {
    $timezones[] = $row['timezone'];
  }
}

$statuses = [
  'Suspect' => 'Suspect — not verified',
  'Lead (Active)' => 'Lead (Active) — you’ve reached out / they responded',
  'Opportunity' => 'Opportunity — quote/proposal/demo sent; decision pending',
  'Won' => 'Won — became a customer',
  'Archive' => 'Archive — not a fit or chose someone else'
];

?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">My Contacts</h1>
  <div>
    <a href="?page=contacts_add" class="btn btn-sm btn-primary">
      <i class="fas fa-plus me-1"></i> Add New
    </a>
    <!-- <a href="index.php?page=contacts_archived" class="btn btn-sm btn-danger ms-1">
      <i class="fas fa-archive me-1"></i> Archived
    </a> -->
  </div>
</div>

<!-- Compact Filter Bar (right aligned) -->
<div class="card mb-1">
  <div class="card-body py-2">
    <form method="get" class="row g-2 justify-content-end align-items-center">
      <input type="hidden" name="page" value="contacts">

      <!-- Phone Filter -->
      <div class="col-auto">
        <select name="phone_filter" class="form-select auto-submit">
          <option value="">Phone (Any)</option>
          <option value="has" <?= ($_GET['phone_filter'] ?? '') == 'has' ? 'selected' : '' ?>>Has Phone</option>
          <option value="none" <?= ($_GET['phone_filter'] ?? '') == 'none' ? 'selected' : '' ?>>No Phone</option>
        </select>
      </div>

      <!-- Status Filter -->
      <div class="col-auto">
        <select name="status" class="form-select auto-submit">
          <option value="">Status (All)</option>
          <?php foreach ($statuses as $key => $label): ?>
            <option value="<?= esc($key) ?>" <?= ($_GET['status'] ?? '') === $key ? 'selected' : '' ?>>
              <?= esc($key) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Timezone Filter -->
      <div class="col-auto">
        <label class="form-label visually-hidden">Timezone</label>
        <select name="timezone" class="form-select auto-submit">
          <option value="">Any Timezone</option>
          <?php foreach ($timezones as $tz): ?>
            <option value="<?= esc($tz) ?>" <?= $timezone === $tz ? 'selected' : '' ?>>
              <?= esc($tz) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Search -->
      <div class="col-auto">
        <input type="text"
               name="kw"
               value="<?= esc($search) ?>"
               placeholder="Search Contacts..."
               class="form-control"
               style="min-width: 200px;">
      </div>

      <!-- Actions -->
      <div class="col-auto d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary auto-submit">
          <i class="fas fa-filter"></i>
        </button>
        <?php if (!empty($search) || !empty($_GET['status']) || !empty($_GET['timezone']) || !empty($_GET['phone_filter'])): ?>
          <a href="index.php?page=contacts" class="btn btn-sm btn-secondary">
            <i class="fas fa-times"></i>
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm mt-n2">
  <div class="card-body">
    <div class="table-responsive">
      <table id="contactsTable" class="table table-striped table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>TimeZone</th>
            <th>Status</th>
            <th>Last Touch</th>
            <th>Next Followup</th>
            <th class="text-center" style="width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT DISTINCT s.id, s.name, s.company, s.city, s.state, s.email, s.phone,s.type,s.timezone, s.favourite, s.created_at 
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


            $in = trim($r['email']);
            $in_esc = str_replace(['%', '_'], ['\\%', '\\_'], $in); // escape wildcards for LIKE
            $regexp = '(^|[^a-zA-Z0-9._-])' . preg_quote($in_esc, '/') . '([^a-zA-Z0-9._-]|$)';

            $sql = "SELECT created_at 
                    FROM email_log 
                    WHERE (
                        ((LOWER(folder) = 'inbox' OR folder IS NULL OR folder = '') 
                         AND (sent_via IS NULL OR LOWER(sent_via) <> 'sent_account') 
                         AND from_email = ?) 
                      OR ((LOWER(folder) = 'sent' OR LOWER(sent_via) = 'mailer' OR LOWER(sent_via) = 'sent_account') 
                         AND to_emails = ?) 
                      OR body_text REGEXP ?
                    ) 
                    ORDER BY created_at DESC 
                    LIMIT 1";

            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sss", $in, $in, $regexp);
            $stmt->execute();
            $stmt->bind_result($lastCreatedAt);
            $stmt->fetch();
            $stmt->close();

            // Query to get next pending reminder
            $sqlReminder = "SELECT reminder_at FROM contacts_reminders WHERE contact_id = ? AND completed = 0 ORDER BY reminder_at ASC LIMIT 1";
            $stmtReminder = $mysqli->prepare($sqlReminder);
            $stmtReminder->bind_param("i", $r['id']);
            $stmtReminder->execute();
            $stmtReminder->bind_result($nextReminderAt);
            $stmtReminder->fetch();
            $stmtReminder->close();

            // Format for display if exists
            $nextReminderTime = (!empty($nextReminderAt))?date('d M Y h:i A', strtotime($nextReminderAt)):'-';

          ?>
          <tr data-id="<?= (int)$r['id'] ?>">
            <td><?= (int)$r['id'] ?></td>
            <td>
              <?= esc($r['name']) ?>
              <?= $r['email'] ? '<br>' . esc($r['email']) : '' ?>
            </td>
            <td>
              <?= $r['phone'] ? '<br>' . esc($r['phone']) : '' ?>
            </td>
            <td>
              <?= esc($r['timezone']) ?>
            </td>
            <td>
              <span class="badge 
                bg-<?=
                  isset($r['type']) && $r['type'] == 'Won' ? 'success' :
                  (isset($r['type']) && $r['type'] == 'Opportunity' ? 'primary' :
                  (isset($r['type']) && $r['type'] == 'Lead (Active)' ? 'warning' :
                  (isset($r['type']) && $r['type'] == 'Suspect' ? 'warning' :
                  (isset($r['type']) && $r['type'] == 'Archive' ? 'secondary' : 'light text-dark'))))
                ?>" id="typed" onclick="editStatus()" style="cursor: pointer;">
                <?= $r['type'] ? ucwords($r['type']) : '' ?>
              </span>
            </td>
            <td>
              <?= !empty($lastCreatedAt) 
                  ? esc(date('d M Y, h:i A', strtotime($lastCreatedAt))) 
                  : '-' ?>
            </td>
            <td><?=!empty($nextReminderTime)?$nextReminderTime:'-'?></td>
            <td class="text-center">
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

<!-- View Modal -->
<div class="modal fade" id="workViewModal" tabindex="-1" aria-labelledby="workViewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="workViewModalLabel">Contact Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="workViewBody">
        Loading...
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
  $('#contactsTable').DataTable({
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],
    order: [[0, 'desc']],
    searching: false, // hides the search box
    language: { search: "_INPUT_", searchPlaceholder: "Search Contacts..." }
  });

  // Toggle favourite
  $(document).on('click', '.fav-star', function(){
    var star = $(this);
    var id = star.data('id');
    $.post('public/ajax/contacts_fav.php', { id: id }, function(resp){
      if(resp.success) star.toggleClass('fas far');
      else alert('Error toggling favourite');
    }, 'json');
  });

  // Delete (archive)
  // $(document).on('click', '.delete-supplier', function(){
  //   if(!confirm('Are you sure you want to archive this Carrier?')) return;
  //   var btn = $(this);
  //   var id = btn.data('id');
  //   $.post('public/ajax/contacts_delete.php', { id: id }, function(resp){
  //     if(resp.success) btn.closest('tr').fadeOut(300, function(){ $(this).remove(); });
  //     else alert('Error archiving Carrier');
  //   }, 'json');
  // });

  // View details
  // $(document).on('click', '.view-supplier', function(){
  //   var id = $(this).data('id');
  //   $('#workViewBody').html('Loading...');
  //   $('#workViewModal').modal('show');
  //   $.get('public/ajax/contacts_view.php', { id: id }, function(resp){
  //     $('#workViewBody').html(resp);
  //   });
  // });

});
</script>
<script>
// Populate timezone list dynamically
document.addEventListener('DOMContentLoaded', () => {
  const tzSelect = document.getElementById('tzSelect');
  if (Intl.supportedValuesOf) {
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
  document.querySelectorAll('.auto-submit').forEach(function(el) {
    el.addEventListener('change', function() {
      this.form.submit();
    });
  });
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
