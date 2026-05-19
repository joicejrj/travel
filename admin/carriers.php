<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

$uid = $CURRENT_USER_ID;

// --- Capture filters ---
$search   = $_GET['kw'] ?? '';

$where = ["s.agent_id = ?"];
$params = [$uid];
$types  = 'i';

if (!empty($search)) {
  $where[] = "(s.name LIKE ? OR s.company LIKE ? OR s.phone LIKE ? OR s.email LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $types .= 'ssss';
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">My Carriers</h1>
  <div>
    <a href="?page=carriers_add" class="btn btn-sm btn-primary">
      <i class="fas fa-plus me-1"></i> Add New
    </a>
    <a href="index.php?page=carriers_archived" class="btn btn-sm btn-danger ms-1">
      <i class="fas fa-archive me-1"></i> Archived
    </a>
  </div>
</div>

<!-- Compact Filter Bar (right aligned) -->
<div class="card mb-1">
  <div class="card-body py-2">
    <form method="get" class="row g-2 justify-content-end align-items-center">
      <input type="hidden" name="page" value="carriers">

      <!-- Small Search (right side) -->
      <div class="col-auto">
        <label class="form-label visually-hidden">Search</label>
        <input type="text"
               name="kw"
               value="<?= esc($search) ?>"
               placeholder="Search Carrier..."
               class="form-control form-control-sm"
               style="min-width: 220px;">
      </div>

      <!-- Actions -->
      <div class="col-auto d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary">
          <i class="fas fa-filter"></i>
        </button>
        <?php if ($search): ?>
          <a href="index.php?page=carriers" class="btn btn-sm btn-secondary">
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
      <table id="carriersTable" class="table table-striped table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Company</th>
            <th>Contact</th>
            <th>Created</th>
            <th class="text-center">Favourite</th>
            <th class="text-center" style="width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT DISTINCT s.id, s.name, s.company, s.city, s.state, s.email, s.phone, s.favourite, s.created_at 
                  FROM carriers AS s
                  WHERE s.archived = 0";
          if ($where) $sql .= " AND " . implode(" AND ", $where);
          $sql .= " ORDER BY s.id DESC";

          $stmt = $mysqli->prepare($sql);
          if ($params) $stmt->bind_param($types, ...$params);
          $stmt->execute();
          $res = $stmt->get_result();

          while ($r = $res->fetch_assoc()):
            $starClass = $r['favourite'] ? "fas fa-star" : "far fa-star";
          ?>
          <tr data-id="<?= (int)$r['id'] ?>">
            <td><?= (int)$r['id'] ?></td>
            <td>
              <?= esc($r['company']) ?>
              <?= $r['city'] ? '<br>' . esc($r['city']) : '' ?>
              <?= $r['state'] ? '<br>' . esc($r['state']) : '' ?>
            </td>
            <td>
              <?= esc($r['name']) ?>
              <?= $r['phone'] ? '<br>' . esc($r['phone']) : '' ?>
              <?= $r['email'] ? '<br>' . esc($r['email']) : '' ?>
            </td>
            <td><?= !empty($r['created_at']) ? esc(date('d M Y, h:i A', strtotime($r['created_at']))) : '-' ?></td>
            <td class="text-center">
              <i class="fav-star <?= $starClass ?>" 
                 data-id="<?= (int)$r['id'] ?>" 
                 style="color:#fbbf24; font-size:18px; cursor:pointer;"></i>
            </td>
            <td class="text-center">
              <button class="btn btn-sm btn-outline-success me-1 view-supplier" data-id="<?= (int)$r['id'] ?>" title="View">
                <i class="fas fa-eye"></i>
              </button>
              <a href="?page=carriers_view&id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                <i class="fas fa-user-cog"></i>
              </a>
              <a href="javascript:void(0);" class="btn btn-sm btn-outline-danger delete-supplier" data-id="<?= (int)$r['id'] ?>" title="Archive">
                <i class="fas fa-archive"></i>
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
        <h5 class="modal-title" id="workViewModalLabel">Carrier Details</h5>
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
  $('#carriersTable').DataTable({
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],
    order: [[0, 'desc']],
    language: { search: "_INPUT_", searchPlaceholder: "Search Carrier..." }
  });

  // Toggle favourite
  $(document).on('click', '.fav-star', function(){
    var star = $(this);
    var id = star.data('id');
    $.post('public/ajax/carriers_fav.php', { id: id }, function(resp){
      if(resp.success) star.toggleClass('fas far');
      else alert('Error toggling favourite');
    }, 'json');
  });

  // Delete (archive)
  $(document).on('click', '.delete-supplier', function(){
    if(!confirm('Are you sure you want to archive this Carrier?')) return;
    var btn = $(this);
    var id = btn.data('id');
    $.post('public/ajax/carriers_delete.php', { id: id }, function(resp){
      if(resp.success) btn.closest('tr').fadeOut(300, function(){ $(this).remove(); });
      else alert('Error archiving Carrier');
    }, 'json');
  });

  // View details
  $(document).on('click', '.view-supplier', function(){
    var id = $(this).data('id');
    $('#workViewBody').html('Loading...');
    $('#workViewModal').modal('show');
    $.get('public/ajax/carriers_view.php', { id: id }, function(resp){
      $('#workViewBody').html(resp);
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
