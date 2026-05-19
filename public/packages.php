<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

/* -------------------------------
   CAPTURE FILTERS
-------------------------------- */
$search = $_GET['kw'] ?? '';
$status = $_GET['status'] ?? '';

$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
  $where[]  = "(p.name LIKE ? OR p.destination LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $types   .= 'ss';
}

if ($status !== '') {
  $where[]  = "p.status = ?";
  $params[] = $status;
  $types   .= 's';
}
?>

<!-- FILTER BAR -->
<div class="card mb-0">
  <div class="card-body py-2">
    <form method="get" class="row g-2 align-items-center" id="packagesFilterForm">
      <input type="hidden" name="page" value="packages">

      <!-- ADD NEW -->
      <div class="col-auto">
        <a href="?page=packages_add" class="btn btn-sm btn-primary">
          <i class="fas fa-plus me-1"></i> Add Package
        </a>
      </div>

      <div class="col"></div>

      <!-- STATUS -->
      <div class="col-auto">
        <?php $statuses = ['draft','active','inactive','archived']; ?>

        <button type="button"
          class="btn btn-sm <?= $status==''?'btn-primary':'btn-outline-primary' ?> status-btn"
          data-value="">All</button>

        <?php foreach ($statuses as $s): ?>
          <button type="button"
            class="btn btn-sm <?= $status==$s?'btn-primary':'btn-outline-primary' ?> status-btn"
            data-value="<?= $s ?>">
            <?= ucfirst($s) ?>
          </button>
        <?php endforeach; ?>

        <input type="hidden" name="status" id="statusInput" value="<?= htmlspecialchars($status) ?>">
      </div>

      <!-- SEARCH -->
      <div class="col-auto">
        <input type="text"
               name="kw"
               value="<?= htmlspecialchars($search) ?>"
               placeholder="Search package / destination..."
               class="form-control"
               style="min-width:300px">
      </div>

      <!-- RESET -->
      <div class="col-auto">
        <?php if ($search || $status): ?>
          <a href="?page=packages" class="btn btn-sm btn-secondary">
            <i class="fas fa-times"></i>
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- PACKAGES TABLE -->
<div class="card shadow-sm mt-0">
  <div class="card-body">
    <div class="table-responsive">
      <table id="packagesTable" class="table table-striped table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Package</th>
            <th>Destination</th>
            <th>Validity</th>
            <th>Duration</th>
            <th>Status</th>
            <th class="text-center" style="width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>

        <?php
        $sql = "
          SELECT p.*
          FROM packages p
          WHERE 1=1 and type='normal'
        ";

        if ($where) {
          $sql .= " AND " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY p.id DESC";

        $stmt = $mysqli->prepare($sql);
        if ($params) {
          $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $res = $stmt->get_result();

        while ($p = $res->fetch_assoc()):
        ?>
          <tr>
            <td><?= (int)$p['id'] ?></td>

            <td>
              <strong><?= htmlspecialchars($p['name']) ?></strong><br>
              <small class="text-muted">
                Created <?= date('d M Y', strtotime($p['created_at'])) ?>
                <?php if (!empty($p['wordpress_url'])): ?>
                  <a href="<?= htmlspecialchars($p['wordpress_url']) ?>" 
                     target="_blank" 
                     class="text-decoration-none ms-2">
                    <i class="fa fa-link"></i> Package Link
                  </a>
                <?php endif; ?>
              </small>
            </td>

            <td><?= htmlspecialchars($p['destination'] ?: '—') ?></td>

            <td>
              <?php if ($p['valid_from'] || $p['valid_to']): ?>
                <?= $p['valid_from'] ?: '—' ?> → <?= $p['valid_to'] ?: '—' ?>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>

            <td>
              <?= (int)$p['duration_days'] ?>D /
              <?= (int)$p['duration_nights'] ?>N
            </td>

            <td>
              <?php
                $badge = match ($p['status']) {
                  'active'   => 'success',
                  'inactive' => 'secondary',
                  'archived' => 'dark',
                  default    => 'warning',
                };
              ?>
              <span class="badge bg-<?= $badge ?>">
                <?= ucfirst($p['status']) ?>
              </span>
            </td>

            <td class="text-center">
              <a href="?page=packages_view&id=<?= $p['id'] ?>"
                 class="btn btn-sm btn-outline-primary" title="View / Edit">
                <i class="fas fa-eye"></i>
              </a>
            </td>
          </tr>
        <?php endwhile; ?>

        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
$('.status-btn').on('click', function(){
  $('#statusInput').val($(this).data('value'));
  $('#packagesFilterForm').submit();
});

$(document).ready(function(){
  $('#packagesTable').DataTable({
    pageLength: 10,
    lengthMenu: [5,10,25,50],
    searching: false,
    order: [[0,'desc']]
  });
});
</script>