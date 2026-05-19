<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

/* -------------------------------
   CAPTURE FILTERS
-------------------------------- */
$search   = $_GET['kw'] ?? '';
$type     = $_GET['type'] ?? '';
$mode     = $_GET['mode'] ?? '';
$supplier = $_GET['supplier'] ?? '';

$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
  $where[] = "(p.name LIKE ?)";
  $params[] = "%$search%";
  $types .= 's';
}

if ($type !== '') {
  $where[] = "p.product_type = ?";
  $params[] = $type;
  $types .= 's';
}

if ($mode !== '') {
  $where[] = "p.mode = ?";
  $params[] = $mode;
  $types .= 's';
}

if ($supplier !== '') {
  $where[] = "p.supplier_id = ?";
  $params[] = $supplier;
  $types .= 'i';
}

/* -------------------------------
   SUPPLIERS LIST
-------------------------------- */
$suppliers = [];
$res = $mysqli->query("SELECT id, name, company FROM suppliers ORDER BY company");
while ($r = $res->fetch_assoc()) {
  $suppliers[] = $r;
}
?>

<!-- FILTER BAR -->
<div class="card mb-0">
  <div class="card-body py-2">
    <form method="get" class="row g-2 align-items-center" id="productsFilterForm">
      <input type="hidden" name="page" value="products">

      <!-- ADD NEW -->
      <div class="col-auto">
        <a href="?page=products_add" class="btn btn-sm btn-primary">
          <i class="fas fa-plus me-1"></i> Add Product
        </a>
      </div>

      <div class="col"></div>

      <!-- PRODUCT TYPE -->
      <div class="col-auto">
        <?php $typesArr = ['taxi','hotel','flight','package']; ?>
        <button type="button"
          class="btn btn-sm <?= $type==''?'btn-primary':'btn-outline-primary' ?> type-btn"
          data-value="">All</button>

        <?php foreach ($typesArr as $t): ?>
          <button type="button"
            class="btn btn-sm <?= $type==$t?'btn-primary':'btn-outline-primary' ?> type-btn"
            data-value="<?= $t ?>">
            <?= ucfirst($t) ?>
          </button>
        <?php endforeach; ?>

        <input type="hidden" name="type" id="typeInput" value="<?= htmlspecialchars($type) ?>">
      </div>

      <!-- MODE -->
      <div class="col-auto">
        <button type="button"
          class="btn btn-sm <?= $mode==''?'btn-secondary':'btn-outline-secondary' ?> mode-btn"
          data-value="">All</button>
        <button type="button"
          class="btn btn-sm <?= $mode=='online'?'btn-secondary':'btn-outline-secondary' ?> mode-btn"
          data-value="online">Online</button>
        <button type="button"
          class="btn btn-sm <?= $mode=='offline'?'btn-secondary':'btn-outline-secondary' ?> mode-btn"
          data-value="offline">Offline</button>

        <input type="hidden" name="mode" id="modeInput" value="<?= htmlspecialchars($mode) ?>">
      </div>

      <!-- SUPPLIER -->
      <div class="col-auto">
        <select name="supplier" class="form-select auto-submit">
          <option value="">All Suppliers</option>
          <?php foreach ($suppliers as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $supplier==$s['id']?'selected':'' ?>>
              <?= htmlspecialchars($s['company']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- SEARCH -->
      <div class="col-auto">
        <input type="text"
               name="kw"
               value="<?= htmlspecialchars($search) ?>"
               placeholder="Search product name..."
               class="form-control"
               style="min-width:300px">
      </div>

      <!-- RESET -->
      <div class="col-auto">
        <?php if ($search || $type || $mode || $supplier): ?>
          <a href="?page=products" class="btn btn-sm btn-secondary">
            <i class="fas fa-times"></i>
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- PRODUCTS TABLE -->
<div class="card shadow-sm mt-0">
  <div class="card-body">
    <div class="table-responsive">
      <table id="productsTable" class="table table-striped table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Type</th>
            <th>Mode</th>
            <th>Supplier</th>
            <th>Validity</th>
            <th>Cost</th>
            <th>Margin</th>
            <th class="text-center" style="width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $sql = "
          SELECT p.*, s.company AS supplier
          FROM products p
          JOIN suppliers s ON s.id = p.supplier_id
          WHERE 1=1
        ";
        if ($where) $sql .= " AND " . implode(" AND ", $where);
        $sql .= " ORDER BY p.id DESC";

        $stmt = $mysqli->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($p = $res->fetch_assoc()):
        ?>
          <tr>
            <td><?= (int)$p['id'] ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><span class="badge bg-info"><?= ucfirst($p['product_type']) ?></span></td>
            <td>
              <span class="badge bg-<?= $p['mode']=='online'?'success':'secondary' ?>">
                <?= ucfirst($p['mode']) ?>
              </span>
            </td>
            <td><?= htmlspecialchars($p['supplier']) ?></td>
            <td>
              <?= $p['valid_from'] && $p['valid_to']
                ? $p['valid_from'].' → '.$p['valid_to']
                : '—' ?>
            </td>
            <td><?= $p['cost'] !== null ? number_format($p['cost'],2) : '—' ?></td>
            <td>
              <?= $p['margin_value'] !== null
                ? number_format($p['margin_value'],2).($p['margin_type']=='percentage'?'%':'')
                : '—' ?>
            </td>
            <td class="text-center">
              <a href="?page=products_view&id=<?= $p['id'] ?>"
                 class="btn btn-sm btn-outline-primary" title="View">
                <i class="fas fa-eye"></i>
              </a>
              <!-- <a href="?page=products_edit&id=<?= $p['id'] ?>"
                 class="btn btn-sm btn-outline-warning" title="Edit">
                <i class="fas fa-edit"></i>
              </a> -->
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
$('.type-btn').on('click', function(){
  $('#typeInput').val($(this).data('value'));
  $('#productsFilterForm').submit();
});

$('.mode-btn').on('click', function(){
  $('#modeInput').val($(this).data('value'));
  $('#productsFilterForm').submit();
});

$(document).ready(function(){
  $('#productsTable').DataTable({
    pageLength: 10,
    lengthMenu: [5,10,25,50],
    searching: false,
    order: [[0,'desc']]
  });

  $('.auto-submit').on('change', function(){
    this.form.submit();
  });
});
</script>