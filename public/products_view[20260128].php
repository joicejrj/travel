<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

$id   = (int)($_GET['id'] ?? 0);
$edit = isset($_GET['edit']);

if (!$id) {
  die('Invalid product');
}

/* -------------------------------------------------
   FETCH PRODUCT
------------------------------------------------- */
$stmt = $mysqli->prepare("
  SELECT p.*, s.name AS supplier_name
  FROM products p
  JOIN suppliers s ON s.id = p.supplier_id
  WHERE p.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
  die('Product not found');
}

/* -------------------------------------------------
   FETCH MEDIA
------------------------------------------------- */
$media = [];
$res = $mysqli->query("SELECT * FROM product_media WHERE product_id = {$id}");
while ($r = $res->fetch_assoc()) $media[] = $r;

/* -------------------------------------------------
   FETCH TIMELINE
------------------------------------------------- */
$logs = [];
$res = $mysqli->query("
  SELECT * FROM product_logs
  WHERE product_id = {$id}
  ORDER BY created_at DESC
");
while ($r = $res->fetch_assoc()) $logs[] = $r;
?>

<?php if (!$edit): ?>
<div class="space-y-6">

  <!-- HEADER -->
  <div class="flex justify-between items-start">
    <div>
      <div class="flex gap-2 mb-2">
        <span class="badge bg-purple-100 text-purple-700"><?= ucfirst($product['product_type']) ?></span>
        <span class="badge bg-success">Active</span>
      </div>
      <h1 class="text-2xl fw-bold"><?= htmlspecialchars($product['name']) ?></h1>
      <a class="text-muted" href="?page=suppliers_view&id=<?= $product['supplier_id'] ?>">
        <?= htmlspecialchars($product['supplier_name']) ?>
      </a>
    </div>

    <div class="d-flex gap-2">
      <a href="?page=products_view&id=<?= $id ?>&edit=1"
         class="btn btn-outline-primary">
        <i class="fas fa-edit me-1"></i> Edit
      </a>
      <button class="btn btn-outline-danger">
        <i class="fas fa-trash me-1"></i> Delete
      </button>
    </div>
  </div>

  <!-- DESCRIPTION -->
  <div class="card">
    <div class="card-body">
      <h5>Description</h5>
      <p><?= nl2br(htmlspecialchars($product['description'] ?? '—')) ?></p>
    </div>
  </div>

  <!-- PRICING -->
  <div class="card">
    <div class="card-body">
      <h5>Pricing</h5>

      <div class="d-flex justify-content-between">
        <span>Cost</span>
        <strong><?= $product['cost'] ? number_format($product['cost'],2) : '—' ?></strong>
      </div>

      <div class="d-flex justify-content-between">
        <span>Markup</span>
        <strong>
          <?= $product['margin_value']
            ? $product['margin_value'].($product['margin_type']=='percentage'?'%':'')
            : '—' ?>
        </strong>
      </div>
    </div>
  </div>

  <!-- MEDIA -->
  <div class="card">
    <div class="card-body">
      <h5>Media</h5>
      <div class="d-flex gap-2 flex-wrap">
        <?php foreach ($media as $m): ?>
          <img src="<?= htmlspecialchars($m['file_path']) ?>"
               class="rounded border"
               style="height:120px">
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- TIMELINE -->
  <div class="card">
    <div class="card-body">
      <h5>Timeline</h5>
      <ul class="list-unstyled">
        <?php foreach ($logs as $l): ?>
          <li class="mb-2">
            <small class="text-muted"><?= $l['created_at'] ?></small><br>
            <?= htmlspecialchars($l['action']) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

</div>
<?php endif; ?>

<?php if ($edit): ?>
<form method="post" enctype="multipart/form-data" action="products_update.php">

<input type="hidden" name="id" value="<?= $id ?>">

<!-- TABS -->
<ul class="nav nav-pills mb-3">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#basic">Basic Info</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pricing">Pricing</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#details">Details</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#media">Media</button></li>
</ul>

<div class="tab-content">

<!-- BASIC -->
<div class="tab-pane fade show active" id="basic">
  <div class="card p-4">
    <label>Product Name</label>
    <input class="form-control" name="name" value="<?= htmlspecialchars($product['name']) ?>">

    <label class="mt-3">Description</label>
    <textarea class="form-control" name="description"><?= htmlspecialchars($product['description']) ?></textarea>
  </div>
</div>

<!-- PRICING -->
<div class="tab-pane fade" id="pricing">
  <div class="card p-4">
    <label>Cost</label>
    <input class="form-control" name="cost" value="<?= $product['cost'] ?>">

    <label class="mt-3">Margin</label>
    <input class="form-control" name="margin_value" value="<?= $product['margin_value'] ?>">
  </div>
</div>

<!-- DETAILS -->
<div class="tab-pane fade" id="details">
  <div class="card p-4">
    <label>Additional Details</label>
    <textarea class="form-control"></textarea>
  </div>
</div>

<!-- MEDIA -->
<div class="tab-pane fade" id="media">
  <div class="card p-4">
    <input type="file" name="media[]" multiple>
  </div>
</div>

</div>

<div class="text-end mt-4">
  <a href="?page=products_view&id=<?= $id ?>" class="btn btn-secondary">Cancel</a>
  <button class="btn btn-primary">Update Product</button>
</div>

</form>
<?php endif; ?>
