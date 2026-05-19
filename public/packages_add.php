<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

$errors = [];

/* Handle form submit */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name             = trim($_POST['name'] ?? '');
    $destination      = trim($_POST['destination'] ?? '');
    $status           = trim($_POST['status'] ?? 'draft');
    $currency = trim($_POST['currency'] ?? 'GBP');
    $valid_from       = $_POST['valid_from'] ?: null;
    $valid_to         = $_POST['valid_to'] ?: null;
    $duration_days    = (int)($_POST['duration_days'] ?? 0);
    $duration_nights  = (int)($_POST['duration_nights'] ?? 0);
    $min_passengers   = (int)($_POST['min_passengers'] ?? 1);
    $max_passengers   = (int)($_POST['max_passengers'] ?? 10);
    $description      = trim($_POST['description'] ?? '');
    $zone_id = (int)($_POST['zone_id'] ?? 0);
    $wordpress_url         = $_POST['wordpress_url'] ?: null;

    $pricing = json_encode(['currency'=>$currency,'valid_to'=>$valid_to,'valid_from'=>$valid_from]);

    $copy_of = (int)($_POST['copy_of'] ?? 0);
    if ($copy_of <= 0) {
        $copy_of = null;
    }

    /* Validation */
    if ($name === '') {
        $errors[] = "Package name is required";
    }

    if ($zone_id <= 0) {
        $errors[] = "Please select a zone";
    }

    if (empty($errors)) {

        $stmt = $mysqli->prepare("
            INSERT INTO packages (
                name,
                destination,
                zone_id,
                status,
                valid_from,
                valid_to,
                duration_days,
                duration_nights,
                min_passengers,
                max_passengers,
                description,
                pricing,
                copy_of,
                wordpress_url,
                created_at
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())
        ");

        $stmt->bind_param(
            "ssisssiiiissis",
            $name,
            $destination,
            $zone_id,
            $status,
            $valid_from,
            $valid_to,
            $duration_days,
            $duration_nights,
            $min_passengers,
            $max_passengers,
            $description,
            $pricing,
            $copy_of,
            $wordpress_url
        );

        if ($stmt->execute()) {
            $new_package_id = $stmt->insert_id;

            if ($copy_of) {

              /* Copy remaining package columns (JSON & system fields) */
              $mysqli->query("
                  UPDATE packages p_new
                  JOIN packages p_old ON p_old.id = $copy_of
                  SET
                      p_new.components   = p_old.components,
                      p_new.itinerary    = p_old.itinerary,
                      p_new.pricing      = p_old.pricing,
                      p_new.media        = p_old.media,
                      p_new.highlights   = p_old.highlights,
                      p_new.inclusions   = p_old.inclusions,
                      p_new.exclusions   = p_old.exclusions
                  WHERE p_new.id = $new_package_id
              ");

              /* Copy package_items */
              $mysqli->query("
                  INSERT INTO package_items (package_id, product_id)
                  SELECT
                      $new_package_id,
                      product_id
                  FROM package_items
                  WHERE package_id = $copy_of
              ");

            }

            /* Redirect to view/edit page */
            header("Location: index.php?page=packages_view&id=".$new_package_id);
            exit;
        } else {
            $errors[] = "Database error: " . $mysqli->error;
        }
    }
}

/* Fetch zones for dropdown */
$zones = [];
$zq = $mysqli->query("
    SELECT id, zone_name, region, country
    FROM zones
    ORDER BY zone_name ASC
");
if ($zq) {
    while ($row = $zq->fetch_assoc()) {
        $zones[] = $row;
    }
}

/* Fetch packages for copy modal */
$copy_packages = [];
$pq = $mysqli->query("
    SELECT id, name
    FROM packages WHERE type='normal'
    ORDER BY name ASC
");
while ($row = $pq->fetch_assoc()) {
    $copy_packages[] = $row;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4" style="max-width: 1000px;">

  <form method="post" class="card shadow-sm p-4">

    <input type="hidden" name="copy_of" id="copy_of" value="">
    
    <!-- HEADER ROW -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">Create Package</h4>
      <button type="button"
              class="btn btn-outline-primary"
              data-bs-toggle="modal"
              data-bs-target="#copyPackageModal">
        Copy Package
      </button>
    </div>

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
        <label class="form-label fw-semibold">Package Name *</label>
        <input type="text"
               name="name"
               class="form-control"
               value="<?= htmlspecialchars($name ?? '') ?>"
               placeholder="e.g. Romantic Paris Getaway"
               required>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Destination</label>
        <input type="text"
               name="destination"
               class="form-control"
               value="<?= htmlspecialchars($destination ?? '') ?>"
               placeholder="e.g. Paris, France">
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Zone *</label>
        <select name="zone_id" class="form-select" required>
          <option value="">-- Select Zone --</option>

          <?php foreach ($zones as $z): ?>
            <option value="<?= $z['id'] ?>"
              <?= ($zone_id ?? 0) == $z['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($z['zone_name']) ?>
              <?php if ($z['region'] || $z['country']): ?>
                (<?= htmlspecialchars(trim($z['region'].' / '.$z['country'], ' /')) ?>)
              <?php endif; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Status</label>
        <select name="status" class="form-select jselect" id="status">
          <option value="draft">Draft</option>
          <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
          <!-- <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option> -->
          <!-- <option value="archived" <?= ($status ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option> -->
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Currency *</label>
        <select name="currency" class="form-select jselect" required>
          <option value="USD">USD</option><option value="EUR">EUR</option><option value="GBP" selected="">GBP</option><option value="AED">AED</option><option value="INR">INR</option><option value="AUD">AUD</option>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Valid From</label>
        <input type="date"
               name="valid_from"
               class="form-control"
               value="<?= htmlspecialchars($valid_from ?? '') ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Valid To</label>
        <input type="date"
               name="valid_to"
               class="form-control"
               value="<?= htmlspecialchars($valid_to ?? '') ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Duration (Days)</label>
        <input type="number"
               name="duration_days" min="0"
               class="form-control jnumber"
               value="<?= htmlspecialchars($duration_days ?? 1) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Duration (Nights)</label>
        <input type="number"
               name="duration_nights" min="0"
               class="form-control jnumber"
               value="<?= htmlspecialchars($duration_nights ?? 0) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Min Passengers</label>
        <input type="number"
               name="min_passengers"
               class="form-control jnumber" min="1"
               value="<?= htmlspecialchars($min_passengers ?? 1) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Max Passengers</label>
        <input type="number"
               name="max_passengers"
               class="form-control jnumber"  min="1"
               value="<?= htmlspecialchars($max_passengers ?? 10) ?>">
      </div>

      <div class="col-md-12">
        <label class="form-label fw-semibold">Website Package URL (Optional)</label>
        <input type="text"
               name="wordpress_url"
               class="form-control"
               value="<?= htmlspecialchars($wordpress_url ?? '') ?>"
               placeholder="e.g. https://www.indiavacations.com/brahmaputra-river-island-cruise/">
      </div>

      <div class="col-md-12">
        <label class="form-label fw-semibold">Description</label>
        <textarea name="description"
                  class="form-control"
                  rows="2"
                  placeholder="Describe the package..."><?= htmlspecialchars($description ?? '') ?></textarea>
      </div>

    </div>

    <!-- ACTIONS -->
    <div class="d-flex justify-content-end gap-3 mt-4">
      <a href="index.php?page=packages" class="btn btn-outline-secondary">
        Cancel
      </a>
      <button type="submit" class="btn btn-primary">
        Create Package
      </button>
    </div>

  </form>

</div>

<div class="modal fade" id="copyPackageModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Copy Existing Package</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <label class="form-label fw-semibold">Select Package</label>
        <select id="copy_package_select" class="form-select">
          <option value="">-- Select Package --</option>
          <?php foreach ($copy_packages as $p): ?>
            <option value="<?= $p['id'] ?>">
              <?= htmlspecialchars($p['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="copyPackageBtn" class="btn btn-primary">
          Copy
        </button>
      </div>

    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="public/assets/js/jselect1.js"></script>
<script src="public/assets/js/jnumber.js"></script>
<script>
document.getElementById('copyPackageBtn').addEventListener('click', function () {

  const id = document.getElementById('copy_package_select').value;
  if (!id) return;

  fetch('public/ajax/get_package_for_copy.php?id=' + id)
    .then(r => r.json())
    .then(res => {
      if (!res.success) return;

      const p = res.package;

      /* BASIC INFO */
      document.querySelector('[name="name"]').value = p.name || '';
      document.querySelector('[name="destination"]').value = p.destination || '';
      document.querySelector('[name="zone_id"]').value = p.zone_id || '';
      
      // const statusEl = document.querySelector('[name="status"]');
      document.getElementById("status").value = p.status || 'draft';
      document.getElementById("status").setAttribute('name', 'status');
      // statusEl.value = p.status || 'draft';
      /* trigger change */
      // statusEl.dispatchEvent(new Event('change', { bubbles: true }));
      refreshJSelect("status");

      /* VALIDITY */
      document.querySelector('[name="valid_from"]').value = p.valid_from || '';
      document.querySelector('[name="valid_to"]').value = p.valid_to || '';

      /* DURATION */
      document.querySelector('[name="duration_days"]').value = p.duration_days || 0;
      document.querySelector('[name="duration_nights"]').value = p.duration_nights || 0;

      /* PASSENGERS */
      document.querySelector('[name="min_passengers"]').value = p.min_passengers || 1;
      document.querySelector('[name="max_passengers"]').value = p.max_passengers || 10;

      /* DESCRIPTION */
      document.querySelector('[name="description"]').value = p.description || '';

      /* SET COPY SOURCE */
      document.getElementById('copy_of').value = id;

      /* Refresh custom UI components */
      if (window.jSelectRefresh) jSelectRefresh();

      /* Close modal */
      const modalEl = document.getElementById('copyPackageModal');
      bootstrap.Modal.getInstance(modalEl).hide();
    });
});
</script>
