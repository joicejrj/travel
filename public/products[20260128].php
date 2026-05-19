<?php
require_once __DIR__ . '/_auth.php'; // adjust if your auth path differs
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php'; // optional

require_once __DIR__ . '/includes/header.php'; // include your page header (nav, styles)

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id     = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

/* DELETE */
if ($action === 'delete' && $id) {
    $stmt = $mysqli->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: ./?page=products");
    exit;
}

/* SAVE (ADD / EDIT) */
if ($action === 'save') {

    if ($id) {
        $sql = "UPDATE products SET
            product_type=?, mode=?, supplier_id=?, name=?, valid_from=?, valid_to=?,
            cost=?, margin_type=?, margin_value=?, description=?
            WHERE id=?";
    } else {
        $sql = "INSERT INTO products
            (product_type, mode, supplier_id, name, valid_from, valid_to,
             cost, margin_type, margin_value, description)
            VALUES (?,?,?,?,?,?,?,?,?,?)";

    $stmt = $mysqli->prepare($sql);
    if ($id) {
        $stmt->bind_param(
            "ssisssdsdsi",
            $_POST['product_type'],
            $_POST['mode'],
            $_POST['supplier_id'],
            $_POST['name'],
            $_POST['valid_from'],
            $_POST['valid_to'],
            $_POST['cost'],
            $_POST['margin_type'],
            $_POST['margin_value'],
            $_POST['description'],
            $id
        );
    } else {
        $stmt->bind_param(
            "ssisssdsds",
            $_POST['product_type'],
            $_POST['mode'],
            $_POST['supplier_id'],
            $_POST['name'],
            $_POST['valid_from'],
            $_POST['valid_to'],
            $_POST['cost'],
            $_POST['margin_type'],
            $_POST['margin_value'],
            $_POST['description']
        );
    }
    $stmt->execute();
    $product_id = $id ?: $mysqli->insert_id;



    /* SAVE DETAILS */
    $stmt = $mysqli->prepare("DELETE FROM product_details WHERE product_id=?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();


    if (!empty($_POST['details'])) {
        $stmt = $mysqli->prepare(
            "INSERT INTO product_details (product_id, field_key, field_value)
             VALUES (?,?,?)"
        );

        foreach ($_POST['details'] as $k => $v) {
            if ($v === '') continue;
            $stmt->bind_param("iss", $product_id, $k, $v);
            $stmt->execute();
        }
    }

    /* PACKAGE ITEMS */
    $stmt = $mysqli->prepare("DELETE FROM package_items WHERE package_id=?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();


    if ($_POST['product_type'] === 'package' && !empty($_POST['package_items'])) {
        $stmt = $mysqli->prepare(
            "INSERT INTO package_items (package_id, product_id)
             VALUES (?,?)"
        );

        foreach ($_POST['package_items'] as $pid) {
            $stmt->bind_param("ii", $product_id, $pid);
            $stmt->execute();
        }
    }

    header("Location: ./?page=products");
    exit;
}


$edit = null;
$edit_details = [];
$edit_package_items = [];

if ($action === 'edit' && $id) {

    $stmt = $mysqli->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();

    // Product details
    $stmt = $mysqli->prepare("SELECT field_key, field_value FROM product_details WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $edit_details[$r['field_key']] = $r['field_value'];
    }

    // Package items
    $stmt = $mysqli->prepare("SELECT product_id FROM package_items WHERE package_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $edit_package_items[] = $r['product_id'];
    }
}


/* DATA */
$products = [];
$result = $mysqli->query("
    SELECT p.*, s.name AS supplier
    FROM products p
    JOIN suppliers s ON s.id = p.supplier_id
    ORDER BY p.id DESC
");
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$suppliers = [];
$result = $mysqli->query("SELECT id, name FROM suppliers");
while ($row = $result->fetch_assoc()) {
    $suppliers[] = $row;
}

$all_products = [];
$result = $mysqli->query("
    SELECT id, name, product_type
    FROM products
    WHERE product_type != 'package'
");
while ($row = $result->fetch_assoc()) {
    $all_products[] = $row;
}


$productFields = [

  'taxi' => [
    'vehicle_type' => 'Vehicle Type',
    'seating_capacity' => 'Seating Capacity',
    'ac_type' => 'AC / Non-AC',
    'km_limit' => 'KM Limit',
    'extra_km_rate' => 'Extra KM Rate',
    'driver_allowance' => 'Driver Allowance',
  ],

  'hotel' => [
    'hotel_name' => 'Hotel Name',
    'room_type' => 'Room Type',
    'meal_plan' => 'Meal Plan',
    'star_rating' => 'Star Rating',
    'city' => 'City',
    'checkin_time' => 'Check-in Time',
    'checkout_time' => 'Check-out Time',
  ],

  'flight' => [
    'airline' => 'Airline',
    'flight_number' => 'Flight Number',
    'fare_class' => 'Fare Class',
    'from_city' => 'From',
    'to_city' => 'To',
    'baggage' => 'Baggage Allowance',
    'refundable' => 'Refundable',
  ],
];


?>

<div class="container mt-4">
  <div class="d-flex justify-content-between mb-3">
    <h4>Products</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
      + Add Product
    </button>
  </div>

  <table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Mode</th>
        <th>Supplier</th>
        <th>Validity</th>
        <th>Cost</th>
        <th>Margin</th>
        <th width="120">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
      <tr>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td class="text-capitalize"><?= $p['product_type'] ?></td>
        <td>
          <span class="badge bg-<?= $p['mode']=='online'?'success':'secondary' ?>">
            <?= ucfirst($p['mode']) ?>
          </span>
        </td>
        <td><?= $p['supplier'] ?></td>
        <td><?= $p['valid_from'] ?> → <?= $p['valid_to'] ?></td>
        <td><?= number_format($p['cost'],2) ?></td>
        <td><?= $p['margin_value'] ?> <?= $p['margin_type']=='percentage'?'%':'' ?></td>
        <td>
          <a href="./?page=products&action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
          <a href="./?page=products&action=delete&id=<?= $p['id'] ?>"
             class="btn btn-sm btn-danger"
             onclick="return confirm('Delete product?')">Del</a>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="productModal">
<div class="modal-dialog modal-lg">
<form method="post" class="modal-content">
<input type="hidden" name="action" value="save">
<input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">

<div class="modal-header">
  <h5 class="modal-title">Product</h5>
  <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body row g-3">

<div class="col-md-6">
  <label>Product Type</label>
  <select name="product_type" class="form-select" required>
    <option value="">Select</option>
    <option value="taxi" <?= ($edit['product_type'] ?? '')=='taxi'?'selected':'' ?>>Taxi</option>
    <option value="hotel" <?= ($edit['product_type'] ?? '')=='hotel'?'selected':'' ?>>Hotel</option>
    <option value="flight" <?= ($edit['product_type'] ?? '')=='flight'?'selected':'' ?>>Flight</option>
    <option value="package" <?= ($edit['product_type'] ?? '')=='package'?'selected':'' ?>>Package</option>
  </select>
</div>

<div class="col-md-6">
  <label>Mode</label>
  <select name="mode" class="form-select" required>
    <option value="">Select</option>
    <option value="online" <?= ($edit['mode'] ?? '')=='online'?'selected':'' ?>>Online</option>
    <option value="offline" <?= ($edit['mode'] ?? '')=='offline'?'selected':'' ?>>Offline</option>
  </select>
</div>

<?php foreach ($productFields as $type => $fields): ?>
<div class="col-12 product-fields d-none" data-type="<?= $type ?>">
  <h6 class="text-muted"><?= ucfirst($type) ?> Details</h6>
  <div class="row g-2">
    <?php foreach ($fields as $key => $label): ?>
      <div class="col-md-4">
        <label><?= $label ?></label>
        <input
          name="details[<?= $key ?>]"
          value="<?= $edit_details[$key] ?? '' ?>"
          class="form-control">
      </div>
    <?php endforeach ?>
  </div>
</div>
<?php endforeach ?>


<div class="col-md-6">
  <label>Supplier</label>
  <select name="supplier_id" class="form-select" required>
    <?php foreach ($suppliers as $s): ?>
        <option value="<?= $s['id'] ?>" <?= (($edit['supplier_id'] ?? '')==$s['id'])?'selected':'' ?>><?= $s['name'] ?></option>
    <?php endforeach ?>
  </select>
</div>

<div class="col-md-12">
  <label>Product Name</label>
  <input name="name" class="form-control" value="<?= $edit['name'] ?? '' ?>" required>
</div>

<div class="col-md-6">
  <label>Valid From</label>
  <input type="date" name="valid_from" class="form-control" value="<?= $edit['valid_from'] ?? '' ?>" required>
</div>

<div class="col-md-6">
  <label>Valid To</label>
  <input type="date" name="valid_to" class="form-control" value="<?= $edit['valid_to'] ?? '' ?>" required>
</div>

<div class="col-md-4">
  <label>Cost</label>
  <input name="cost" class="form-control" value="<?= $edit['cost'] ?? '' ?>" required>
</div>

<div class="col-md-4">
  <label>Margin Type</label>
  <select name="margin_type" class="form-select">
    <option value="amount">Amount</option>
    <option value="percentage">Percentage</option>
  </select>
</div>

<div class="col-md-4">
  <label>Margin</label>
  <input name="margin_value" class="form-control" value="<?= $edit['margin_value'] ?? '' ?>" required>
</div>

<div class="col-md-12">
  <label>Description</label>
  <textarea name="description" class="form-control"><?= $edit['description'] ?? '' ?></textarea>
</div>

<!-- PACKAGE ITEMS -->
<div class="col-md-12">
  <label>Package Items</label>
  <select name="package_items[]" class="form-select" multiple>
    <?php foreach ($all_products as $ap): ?>
      <option value="<?= $ap['id'] ?>" <?= in_array($ap['id'], $edit_package_items) ? 'selected' : '' ?>>
        <?= $ap['name'] ?> (<?= $ap['product_type'] ?>)
      </option>
    <?php endforeach ?>
  </select>
  <small class="text-muted">Only for package type</small>
</div>

</div>

<div class="modal-footer">
  <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
  <button class="btn btn-success">Save Product</button>
</div>

</form>
</div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php'; // optional: your page footer
?>

<?php if ($action === 'edit'): ?>
<script>
  var modal = new bootstrap.Modal(document.getElementById('productModal'));
  modal.show();
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {

  function toggleProductFields(type) {
    if (!type) return;
    type = type.toLowerCase();

    document.querySelectorAll('.product-fields').forEach(el => {
      el.classList.toggle('d-none', el.dataset.type !== type);
    });
  }

  const typeSelect = document.querySelector('[name="product_type"]');
  if (typeSelect) {
    typeSelect.addEventListener('change', e => {
      toggleProductFields(e.target.value);
    });
  }

  // On edit
  <?php if (!empty($edit['product_type'])): ?>
    toggleProductFields('<?= $edit['product_type'] ?>');
  <?php endif; ?>

});
</script>

