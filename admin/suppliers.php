<?php
require_once __DIR__ . '/includes/header.php';
require_login();


// --- Capture filters ---
$search = $_GET['kw'] ?? '';
$country = $_GET['country'] ?? '';
$callst = $_GET['recording'] ?? 'all';
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

$callsq = "";
$callsqw = "";
if($callst!='') {
  if($callst=='with') {
    // $callsq = ",(SELECT count(id) as callno1 FROM carriers_call_logs AS c JOIN carriers AS sc ON JSON_CONTAINS(sc.phones, JSON_QUOTE(c.destination)) WHERE sc.id = s.id and recording_status='Yes' ORDER BY c.id DESC) as callno";
    $callsq = " LEFT JOIN carriers_call_logs AS c ON JSON_CONTAINS(s.phones, JSON_QUOTE(c.destination))";
    $callsqw = " AND recording_status='Yes'";
  }
  // else if($callst=='without') {
  //   $callsq = " JOIN carriers_call_logs AS c ON JSON_CONTAINS(s.phones, JSON_QUOTE(c.destination))";
  //   $callsqw = " AND recording_status!='Yes'";
  // }
}

?>

<div class="d-flex1 justify-content-between1 align-items-center mb-4">
  <a href="index.php?page=carriers_add" class="btn btn-sm btn-primary float-end">
    <i class="fas fa-plus me-1"></i> Add New
  </a>
  <a href="index.php?page=carriers_archived" class="btn btn-sm btn-danger me-1 float-end">
    <i class="fas fa-archive me-1"></i> Archived
  </a>
  <h1 class="h3 mb-0">Carriers</h1>
</div>

<!-- Filter Bar -->
<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-3 align-items-end">
      <input type="hidden" name="page" value="carriers">

      <!-- Search -->
      <div class="col-12 col-md-3">
        <label class="form-label">Search</label>
        <input type="text" 
               name="kw" 
               value="<?=esc($search)?>" 
               placeholder="Name, Company, Phone, Email" 
               class="form-control">
      </div>

      <!-- Category -->
      <div class="col-12 col-md-3">
        <label class="form-label">Country</label>
        <select name="country" class="form-select">
          <option value="">-- All Countries --</option>
          <?php
          $cats = $mysqli->query("SELECT distinct country FROM carriers WHERE DATE(created_at)>=DATE('2025-10-01') ORDER BY country ASC");
          while($c = $cats->fetch_assoc()){
            $sel = ($country==$c['country']) ? "selected" : "";
            echo "<option value='".$c['country']."' $sel>".esc($c['country'])."</option>";
          }
          ?>
        </select>
      </div>

      <!-- Category -->
      <div class="col-12 col-md-3" style="display:none;">
        <label class="form-label">Call Recording</label>
        <select name="recording" class="form-select">
          <option value="all">-- All --</option>
          <option value="with" <?=$callst=='with'?'selected':''?>>With Recording</option>
          <!-- <option value="without">Without Recoring</option> -->
        </select>
      </div>

      <!-- Actions -->
      <div class="col-12 col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary w-100">
          <i class="fas fa-filter"></i> Filter
        </button>
        <?php if ($search || $country || ($callst!='all')): ?>
          <a href="index.php?page=carriers&recording=all" class="btn btn-secondary w-100">
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
            <th>Company</th>
            <th>Contact</th>
            <th>Created</th>
            <th class="text-center">Favourite</th>
            <th class="text-center" style="width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // $res = $mysqli->query("SELECT id,name,company,email,phone,favourite,created_at FROM carriers as s ORDER BY id DESC");
          $sql = "SELECT DISTINCT s.id,s.name,s.company,s.city,s.state,s.email,s.phone,s.favourite,s.created_at FROM carriers as s ".$callsq." WHERE DATE(s.created_at)>=DATE('2025-10-01') and s.archived='0' ";
          if ($where) {
            $sql .= " AND " . implode(" AND ", $where);
          }
          if($callsqw!='') {
            $sql .= $callsqw;
          }
          $sql .= " ORDER BY s.id DESC";
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
            <td><?= esc($r['company']).($r['city']!=''?'<br>'.esc($r['city']):'').($r['state']!=''?'<br>'.esc($r['state']):'') ?></td>
            <td><?=$r['name'].($r['phone']!=''?($r['name']!=''?'<br>':'').esc($r['phone']):'')?><?=$r['email']!=''?(($r['name']!=''||$r['phone']!='')?'<br>':'').esc($r['email']):''?></td>
            <td><?= esc(human_dt($r['created_at'])) ?></td>
            <td class="text-center">
              <i class="fav-star <?= $starClass ?>"
                 data-id="<?= (int)$r['id'] ?>"
                 style="color:#fbbf24; font-size:18px; cursor:pointer;"></i>
            </td>
            <td class="text-center">
              <button class="btn btn-sm btn-outline-success me-1 view-supplier" data-id="<?=(int)$r['id'] ?>" title="View">
                <i class="fas fa-envelope"></i>
              </button>
              <a href="index.php?page=carriers_view&id=<?= (int)$r['id'] ?>"
                 class="btn btn-sm btn-outline-primary me-1" title="Edit">
                <i class="fas fa-user-cog"></i>
              </a>
              <a href="javascript:void(0);"
                 class="btn btn-sm btn-outline-danger delete-supplier"
                 data-id="<?= (int)$r['id'] ?>" title="Delete">
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
        <h5 class="modal-title" id="workViewModalLabel">Carrier Emails</h5>
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
      searchPlaceholder: "Search Carrier..."
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
    if(!confirm('Are you sure you want to archive this Carrier?')) return;

    var btn = $(this);
    var id = btn.data('id');

    $.post('public/ajax/carriers_delete.php', { id: id }, function(resp){
      if(resp.success){
        btn.closest('tr').fadeOut(300, function(){ $(this).remove(); });
      } else {
        alert('Error archiving Carrier');
      }
    }, 'json');
  });

  // View work entry
  $(document).on('click', '.view-supplier', function(){
      var id = $(this).data('id');
      $('#workViewBody').html('Loading...');
      $('#workViewModal').modal('show');

      $.get('public/ajax/carriers_view_emails.php', { id: id }, function(resp){
          $('#workViewBody').html(resp);
      });
  });

});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
