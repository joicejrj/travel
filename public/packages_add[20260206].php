<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

$errors = [];

/* Handle form submit */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name             = trim($_POST['name'] ?? '');
    $destination      = trim($_POST['destination'] ?? '');
    $status           = trim($_POST['status'] ?? 'draft');
    $valid_from       = $_POST['valid_from'] ?: null;
    $valid_to         = $_POST['valid_to'] ?: null;
    $duration_days    = (int)($_POST['duration_days'] ?? 0);
    $duration_nights  = (int)($_POST['duration_nights'] ?? 0);
    $min_passengers   = (int)($_POST['min_passengers'] ?? 1);
    $max_passengers   = (int)($_POST['max_passengers'] ?? 10);
    $description      = trim($_POST['description'] ?? '');
    $zone_id = (int)($_POST['zone_id'] ?? 0);

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
                created_at
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())
        ");

        $stmt->bind_param(
            "ssissiiiiis",
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
            $description
        );

        if ($stmt->execute()) {
            $new_package_id = $stmt->insert_id;

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

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4" style="max-width: 900px;">

  <form method="post" class="card shadow-sm p-4">

    <h4 class="mb-4">Create Package</h4>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- BASIC INFO -->

    <div class="row g-3">

      <div class="col-md-12">
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

      <div class="col-md-6">
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

      <div class="col-md-6">
        <label class="form-label fw-semibold">Status</label>
        <select name="status" class="form-select jselect">
          <option value="draft">Draft</option>
          <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
          <option value="archived" <?= ($status ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Valid From</label>
        <input type="date"
               name="valid_from"
               class="form-control"
               value="<?= htmlspecialchars($valid_from ?? '') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Valid To</label>
        <input type="date"
               name="valid_to"
               class="form-control"
               value="<?= htmlspecialchars($valid_to ?? '') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Duration (Days)</label>
        <input type="number"
               name="duration_days"
               class="form-control jnumber"
               value="<?= htmlspecialchars($duration_days ?? 0) ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Duration (Nights)</label>
        <input type="number"
               name="duration_nights"
               class="form-control jnumber"
               value="<?= htmlspecialchars($duration_nights ?? 0) ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Min Passengers</label>
        <input type="number"
               name="min_passengers"
               class="form-control jnumber"
               value="<?= htmlspecialchars($min_passengers ?? 1) ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Max Passengers</label>
        <input type="number"
               name="max_passengers"
               class="form-control jnumber"
               value="<?= htmlspecialchars($max_passengers ?? 10) ?>">
      </div>

      <div class="col-md-12">
        <label class="form-label fw-semibold">Description</label>
        <textarea name="description"
                  class="form-control"
                  rows="4"
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="public/assets/js/jselect1.js"></script>
<script src="public/assets/js/jnumber.js"></script>