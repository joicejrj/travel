<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

$errors = [];
$success = false;

/* Fetch suppliers */
$suppliers = [];
$res = $mysqli->query("SELECT id, name, company FROM suppliers ORDER BY company ASC");
while ($row = $res->fetch_assoc()) {
    $suppliers[] = $row;
}

/* Fetch zones */
$zones = [];
$res = $mysqli->query("SELECT id, zone_name, region, country FROM zones ORDER BY zone_name ASC");
while ($row = $res->fetch_assoc()) {
    $zones[] = $row;
}

/* Handle form submit */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $supplier_id       = (int)($_POST['supplier_id'] ?? 0);
    $zone_id       = (int)($_POST['zone_id'] ?? 0);
    $product_type      = trim($_POST['product_type'] ?? '');
    $mode              = trim($_POST['mode'] ?? 'offline');
    $name              = trim($_POST['name'] ?? '');
    // $sku               = trim($_POST['sku'] ?? '');
    $currency = trim($_POST['currency'] ?? 'GBP');
    $short_description = trim($_POST['short_description'] ?? '');
    $description       = trim($_POST['description'] ?? '');
    $destination       = trim($_POST['destination'] ?? '');
    $duration          = trim($_POST['duration'] ?? '');
    $status            = trim($_POST['status'] ?? 'active');

    /* Validation */
    if (!$supplier_id) $errors[] = "Supplier is required";
    if (!$zone_id) $errors[] = "Zone is required";
    if ($name === '') $errors[] = "Product name is required";
    if ($product_type === '') $errors[] = "Category is required";
    if ($currency === '') $errors[] = "Currency is required";

    if (empty($errors)) {

        $stmt = $mysqli->prepare("
            INSERT INTO products (
                product_type, mode, supplier_id, zone_id,
                name, cost_currency,
                short_description, description,
                destination, duration,
                status, created_at
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())
        ");

        $stmt->bind_param(
            "ssiisssssss",
            $product_type,
            $mode,
            $supplier_id,
            $zone_id,
            $name,
            $currency,
            $short_description,
            $description,
            $destination,
            $duration,
            $status
        );

        if ($stmt->execute()) {
            $new_product_id = $stmt->insert_id;

            // Auto-generate SKU like PKD000123
            $sku = 'PKD' . str_pad($new_product_id, 6, '0', STR_PAD_LEFT);
            $up = $mysqli->prepare("UPDATE products SET sku=? WHERE id=?");
            $up->bind_param("si", $sku, $new_product_id);
            $up->execute();

            /* Redirect to edit page later */
            header("Location: index.php?page=products_view&id=".$new_product_id);
            exit;
        } else {
            $errors[] = "Database error: ".$mysqli->error;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4" style="max-width: 900px;">
  <form method="post" class="card shadow-sm p-4">

    <h4 class="mb-4">Create Product</h4>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- BASIC INFO -->

    <div class="row g-3">

      <div class="col-md-6">
        <label class="form-label fw-semibold">Supplier *</label>
        <select name="supplier_id" class="form-select" required>
          <option value="">Select supplier</option>
          <?php foreach ($suppliers as $s): ?>
            <option value="<?= $s['id'] ?>" <?= ($supplier_id ?? '') == $s['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['company']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Mode</label>
        <select name="mode" class="form-select jselect">
          <option value="offline">Offline</option>
          <option value="online" <?= ($mode ?? '') === 'online' ? 'selected' : '' ?>>Online</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Product Name *</label>
        <input type="text" name="name" class="form-control"
               value="<?= htmlspecialchars($name ?? '') ?>"
               placeholder="Enter product name" required>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Zone *</label>
        <select name="zone_id" class="form-select" required>
          <option value="">Select Zone</option>
          <?php foreach ($zones as $s): ?>
            <option value="<?= $s['id'] ?>" <?= ($zone_id ?? '') == $s['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['zone_name']." - ".$s['region']." - ".$s['country']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-12">
        <label class="form-label fw-semibold">Category *</label>
        <select name="product_type" class="form-select jselect" required>
          <!-- <option value="">Select category</option> -->
          <?php
          $types = [
            'flight'=>'Flight','hotel'=>'Hotel','transfer'=>'Transfer',
            'tour'=>'Tour','sightseeing'=>'Sightseeing',
            'ticket'=>'Ticket','car_rental'=>'Car Rental','package'=>'Package'
          ];
          foreach ($types as $k=>$v):
          ?>
            <option value="<?= $k ?>" <?= ($product_type ?? '') == $k ? 'selected' : '' ?>>
              <?= $v ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Status</label>
        <select name="status" class="form-select jselect">
          <option value="active" selected>Active</option>
          <!-- <option value="inactive">Inactive</option> -->
          <option value="seasonal">Seasonal</option>
          <!-- <option value="sold_out">Sold Out</option> -->
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Currency *</label>
        <select name="currency" class="form-select jselect" required>
          <option value="USD">USD</option><option value="EUR">EUR</option><option value="GBP" selected="">GBP</option><option value="AED">AED</option><option value="INR">INR</option><option value="AUD">AUD</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Destination</label>
        <input type="text" name="destination" class="form-control"
               value="<?= htmlspecialchars($destination ?? '') ?>"
               placeholder="e.g. Paris, France">
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Duration</label>
        <input type="text" name="duration" class="form-control"
               value="<?= htmlspecialchars($duration ?? '') ?>"
               placeholder="e.g. 3 nights / 4 hours">
      </div>

      <div class="col-md-12">
        <label class="form-label fw-semibold">Short Description</label>
        <input type="text" name="short_description" class="form-control"
               value="<?= htmlspecialchars($short_description ?? '') ?>"
               placeholder="Short description for listings">
      </div>

      <div class="col-md-12">
        <label class="form-label fw-semibold">Full Description</label>
        <textarea name="description" class="form-control" rows="4"
                  placeholder="Detailed product description"><?= htmlspecialchars($description ?? '') ?></textarea>
      </div>

    </div>

    <!-- ACTIONS -->
    <div class="d-flex justify-content-end gap-3 mt-4">
      <a href="index.php?page=products" class="btn btn-outline-secondary">
        Cancel
      </a>
      <button type="submit" class="btn btn-primary">
        Create Product
      </button>
    </div>

  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="public/assets/js/jselect1.js"></script>