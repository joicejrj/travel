<?php
require_once __DIR__ . '/includes/header.php';
require_login();


// --- Capture filters ---
$search = $_GET['kw'] ?? '';
$country = $_GET['country'] ?? '';
// $daterange = $_GET['daterange'] ?? '';

$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(s.name LIKE ? OR s.company LIKE ? OR s.phone LIKE ? OR s.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ssss';
}
if ($country != '') {
    $where[] = "s.country=?";
    $params[] = $country;
    $types .= 's';
}

?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Carriers Archived</h1>
</div>

<!-- Filter Bar -->
<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-3 align-items-end">
      <input type="hidden" name="page" value="carriers_archived">

      <!-- Search -->
      <div class="col-12 col-md-4">
        <label class="form-label">Search</label>
        <input type="text" 
               name="kw" 
               value="<?=esc($search)?>" 
               placeholder="Name, Company, Phone, Email" 
               class="form-control">
      </div>

      <!-- Category -->
      <div class="col-12 col-md-4">
        <label class="form-label">Country</label>
        <select name="country" class="form-select">
          <option value="">-- All Countries --</option>
          <?php
          $cats = $mysqli->query("SELECT distinct country FROM carriers ORDER BY country ASC");
          while($c = $cats->fetch_assoc()){
            $sel = ($country==$c['country']) ? "selected" : "";
            echo "<option value='".$c['country']."' $sel>".esc($c['country'])."</option>";
          }
          ?>
        </select>
      </div>

      <!-- Actions -->
      <div class="col-12 col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary w-100">
          <i class="fas fa-filter"></i> Filter
        </button>
        <?php if ($search || $country): ?>
          <a href="index.php?page=carriers_archived" class="btn btn-secondary w-100">
            <i class="fas fa-times"></i> Clear
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table id="carriersTable" class="table table-striped table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Company</th>
            <th>Contact</th>
            <th>Created</th>
            <th class="text-center" style="width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // $res = $mysqli->query("SELECT id,name,company,email,phone,favourite,created_at FROM carriers as s ORDER BY id DESC");
          $sql = "SELECT id,name,company,email,phone,favourite,created_at FROM carriers as s WHERE archived='1' ";
          if ($where) {
            $sql .= " AND " . implode(" AND ", $where);
          }
          $sql .= " ORDER BY id DESC";
          $stmt = $mysqli->prepare($sql);
          if ($params) {
            $stmt->bind_param($types, ...$params);
          }
          $stmt->execute();
          $res = $stmt->get_result();
          while($r = $res->fetch_assoc()):
            $starClass = $r['favourite'] ? "fas fa-star" : "far fa-star";
          ?>
          <tr data-id="<?= (int)$r['id'] ?>">
            <td><?= (int)$r['id'] ?></td>
            <td><?= esc($r['name']) ?></td>
            <td><?= esc($r['company']) ?></td>
            <td><?= esc($r['phone']) ?><?=$r['phone']!=''&&$r['email']!=''?'<br>'.esc($r['email']):''?></td>
            <td><?= esc(human_dt($r['created_at'])) ?></td>
            <td class="text-center">
              <button class="btn btn-sm btn-outline-success me-1 view-supplier" data-id="<?=(int)$r['id'] ?>" title="View">
                <i class="fas fa-eye"></i>
              </button>
              <a href="javascript:void(0);"
                 class="btn btn-sm btn-outline-danger delete-supplier"
                 data-id="<?= (int)$r['id'] ?>" title="Unarchive">
                <i class="fas fa-undo-alt"></i>
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
  // Initialize DataTable
  $('#carriersTable').DataTable({
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],
    order: [[0, 'desc']],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search recruiter..."
    }
  });

  // Toggle favourite
  $(document).on('click', '.fav-star', function(){
    var star = $(this);
    var id = star.data('id');

    $.post('public/ajax/carriers_fav.php', { id: id }, function(resp){
      if(resp.success){
        star.toggleClass('fas far');
      } else {
        alert('Error toggling favourite');
      }
    }, 'json');
  });

  // Delete supplier
  $(document).on('click', '.delete-supplier', function(){
    if(!confirm('Are you sure you want to unarchive this recruiter?')) return;

    var btn = $(this);
    var id = btn.data('id');

    $.post('public/ajax/carriers_delete.php', { id: id, unarchive: 1 }, function(resp){
      if(resp.success){
        btn.closest('tr').fadeOut(300, function(){ $(this).remove(); });
      } else {
        alert('Error unarchiving recruiter');
      }
    }, 'json');
  });

  // View work entry
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
