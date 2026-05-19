<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

$product_id = (int)($_GET['id'] ?? 0);
if (!$product_id) {
  echo "<div class='alert alert-danger'>Invalid Product</div>";
  require_once __DIR__ . '/includes/footer.php';
  exit;
}

/* -------------------------------
   FETCH PRODUCT
-------------------------------- */
$stmt = $mysqli->prepare("
  SELECT p.*, s.company AS supplier_name, z.zone_name
  FROM products p
  JOIN suppliers s ON s.id = p.supplier_id
  left join zones z ON z.id = p.zone_id
  WHERE p.id = ?
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
  echo "<div class='alert alert-danger'>Product not found</div>";
  require_once __DIR__ . '/includes/footer.php';
  exit;
}

function normalize_list($value) {
  if (!$value) return [];

  // If JSON
  if (is_string($value) && ($value[0] === '[' || $value[0] === '{')) {
    $decoded = json_decode($value, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
      return array_filter(array_map('trim', $decoded));
    }
  }

  // Fallback: comma-separated
  return array_filter(array_map('trim', explode(',', $value)));
}

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

$inclusions = normalize_list($product['inclusions'] ?? '');
$exclusions = normalize_list($product['exclusions'] ?? '');
?>

<!-- ================= HEADER ================= -->
<div class="header border-bottom pb-2 mb-3x">
  <div class="d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">

    <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">

      <h4 class="mb-0 fw-bold">
        <?= htmlspecialchars($product['name']) ?>
      </h4>

      <!-- VIEW -->
      <button class="btn btn-xs btn-outline-primary fw-bold tab-btn active"
              data-tab="view" id="viewBtn">
        <i class="fa fa-eye"></i>
      </button>

      <!-- BASIC -->
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold"
              data-tab="basic" id="basicBtn">
        Basic Info
      </button>

      <!-- PRICING -->
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold"
              data-tab="pricing" id="pricingBtn">
        Pricing
      </button>

      <!-- DETAILS -->
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold"
              data-tab="details" id="detailsBtn">
        Details
      </button>

      <!-- MEDIA -->
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold"
              data-tab="media" id="mediaBtn">
        Media
      </button>

      <!-- Integration -->
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold"
              data-tab="integration" id="integrationBtn">
        Integration
      </button>

      <!-- TIMELINE -->
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold"
              data-tab="timeline" id="timelineBtn">
        Timeline
      </button>

      <!-- STATUS -->
      <div class="d-flex align-items-center gap-2">
        <span class="fw-semibold">Status:</span>
        <span class="badge bg-<?= $product['status'] ? 'success' : 'secondary' ?>">
          <?= $product['status'] ? 'Active' : 'Inactive' ?>
        </span>

        <span class="badge text-dark">
          <i class="fa fa-building me-1"></i>
          <?= htmlspecialchars($product['supplier_name']) ?>
        </span>

        <span class="badge bg-info me-2 text-dark">
          <?= ucfirst($product['product_type']) ?>
        </span>

      </div>

    </div>
  </div>
</div>

<!-- ================= CONTENT ================= -->
<div class="row g-3 mt-1 mb-2">

  <!-- LEFT -->
  <div class="col-lg-8">

    <!-- VIEW -->
    <div class="tab-content-section" id="tab-view">

      <!-- HEADER CARD -->
      <div class="card mb-3 shadow-sm d-none">
        <div class="card-body">

          <div class="d-flex justify-content-between flex-wrap gap-3">

            <div>
              <div class="mb-2">
                <span class="badge bg-info me-2 text-dark">
                  <?= ucfirst($product['product_type']) ?>
                </span>
                <span class="badge bg-success">
                  <?= ucfirst($product['status']) ?>
                </span>
              </div>

              <h3 class="fw-bold mb-1">
                <?= htmlspecialchars($product['name']) ?>
              </h3>

            </div>

            <div class="d-flex gap-2">
              <a href="?page=suppliers_view&id=<?= $product['supplier_id'] ?>"
                 class="text-decoration-none text-muted" target="_blank">
                <i class="fa fa-building me-1"></i>
                <?= htmlspecialchars($product['supplier_name']) ?>
              </a>
            </div>

          </div>
        </div>
      </div>

      <!-- DESCRIPTION -->
      <div class="card mb-3 shadow-sm">
        <div class="card-body">
          <h5 class="fw-semibold mb-2">Description</h5>
          <p class="text-muted mb-0">
            <?= nl2br(htmlspecialchars($product['description'] ?? '—')) ?>
          </p>
        </div>
      </div>

      <!-- INCLUSIONS / EXCLUSIONS -->
      <div class="row g-3 mb-3">

        <!-- INCLUSIONS -->
        <div class="col-md-6">
          <div class="card h-100 shadow-sm border-success-subtle">
            <div class="card-body">
              <h6 class="fw-semibold text-success mb-3">
                <i class="fa fa-check-circle me-1"></i> Inclusions
              </h6>

              <?php if ($inclusions): ?>
                <ul class="list-unstyled mb-0">
                  <?php foreach ($inclusions as $item): ?>
                    <li class="d-flex align-items-start mb-2">
                      <i class="fa fa-check text-success me-2 mt-1"></i>
                      <span><?= htmlspecialchars($item) ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <div class="text-muted fst-italic">No inclusions specified</div>
              <?php endif; ?>

            </div>
          </div>
        </div>

        <!-- EXCLUSIONS -->
        <div class="col-md-6">
          <div class="card h-100 shadow-sm border-danger-subtle">
            <div class="card-body">
              <h6 class="fw-semibold text-danger mb-3">
                <i class="fa fa-times-circle me-1"></i> Exclusions
              </h6>

              <?php if ($exclusions): ?>
                <ul class="list-unstyled mb-0">
                  <?php foreach ($exclusions as $item): ?>
                    <li class="d-flex align-items-start mb-2">
                      <i class="fa fa-times text-danger me-2 mt-1"></i>
                      <span><?= htmlspecialchars($item) ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <div class="text-muted fst-italic">No exclusions specified</div>
              <?php endif; ?>

            </div>
          </div>
        </div>

      </div>

      <!-- PRICING -->
      <div class="card mb-3 shadow-sm">
        <div class="card-body">
          <h5 class="fw-semibold mb-3">Pricing</h5>

          <?php
            $sell_price = $product['margin_type']=='percentage'
              ? $product['cost'] + ($product['cost'] * $product['margin_value'] / 100)
              : $product['cost'] + $product['margin_value'];
          ?>

          <div class="row text-center">

            <div class="col-md-3 mb-2">
              <div class="p-3 bg-light rounded">
                <small class="text-muted">Cost</small>
                <div class="fw-bold"><?= $product['cost']!=''?number_format($product['cost']??0,2):'-' ?></div>
              </div>
            </div>

            <div class="col-md-3 mb-2">
              <div class="p-3 bg-light rounded">
                <small class="text-muted">Markup</small>
                <div class="fw-bold">
                  <?= $product['margin_value'] ?>
                  <?= $product['margin_type']=='percentage'?'%':'' ?>
                </div>
              </div>
            </div>

            <div class="col-md-3 mb-2">
              <div class="p-3 bg-success-subtle rounded">
                <small class="text-muted">Sell Price</small>
                <div class="fw-bold text-success">
                  <?= number_format($sell_price??0,2) ?>
                </div>
              </div>
            </div>

            <div class="col-md-3 mb-2">
              <div class="p-3 bg-light rounded">
                <small class="text-muted">Commission</small>
                <div class="fw-bold">
                  <?= $product['commission_value'] ?>
                  <?= $product['commission_type']=='percentage'?'%':'' ?>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- DETAILS -->
      <div class="card mb-3 shadow-sm">
        <div class="card-body">
          <h5 class="fw-semibold mb-3">Details</h5>

          <div class="row">
            <div class="col-md-4 mb-2">
              <strong>Destination</strong><br>
              <?= $product['destination'] ?? '—' ?>
            </div>
            <div class="col-md-4 mb-2">
              <strong>Duration</strong><br>
              <?= $product['duration'] ?? '—' ?>
            </div>
            <div class="col-md-4 mb-2">
              <strong>Capacity</strong><br>
              <?= $product['min_passengers']."-".$product['max_passengers'] ?>
            </div>
          </div>
        </div>
      </div>

      <?php $tags = normalize_list($product['tags'] ?? ''); ?>
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h6 class="fw-semibold mb-3">
            <i class="fa fa-tags me-1"></i> Tags
          </h6>

          <?php if ($tags): ?>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach ($tags as $tag): ?>
                <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
                  <?= htmlspecialchars($tag) ?>
                </span>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="text-muted fst-italic">No tags added</div>
          <?php endif; ?>

        </div>
      </div>


    </div>

    <!-- BASIC -->
    <div class="tab-content-section d-none" id="tab-basic">
      <div class="card shadow-sm border-0">
        <div class="card-body">

          <!-- Header -->
          <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
              <h5 class="mb-0">Basic Info</h5>
              <small class="text-muted">Product overview & configuration</small>
            </div>

            <button class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#editBasicModal">
              <i class="fa fa-edit me-1"></i> Edit
            </button>
          </div>

          <!-- Info Grid -->
          <div class="row g-4">

            <div class="col-md-6">
              <div class="text-muted small mb-1">
                <i class="fa fa-building me-1"></i> Supplier
              </div>
              <div class="fw-semibold">
                <?= htmlspecialchars($product['supplier_name']) ?>
              </div>
            </div>

            <div class="col-md-6">
              <div class="text-muted small mb-1">
                <i class="fa fa-box me-1"></i> Product Name
              </div>
              <div class="fw-semibold">
                <?= htmlspecialchars($product['name']) ?>
              </div>
            </div>

            <div class="col-md-3">
              <div class="text-muted small mb-1">
                <i class="fa fa-tags me-1"></i> Type
              </div>
              <div class="fw-semibold text-capitalize">
                <?= $product['product_type'] ?>
              </div>
            </div>

            <div class="col-md-3">
              <div class="text-muted small mb-1">
                <i class="fa fa-map me-1"></i> Zone
              </div>
              <div class="fw-semibold">
                <?= $product['zone_name']!=''?htmlspecialchars($product['zone_name']):'-' ?>
              </div>
            </div>

            <div class="col-md-3">
              <div class="text-muted small mb-1">
                <i class="fa fa-toggle-on me-1"></i> Status
              </div>
              <span class="badge
                <?php
                  echo match($product['status']) {
                    'active' => 'bg-success',
                    'inactive' => 'bg-secondary',
                    'seasonal' => 'bg-warning text-dark',
                    'sold_out' => 'bg-danger',
                    default => 'bg-light text-dark'
                  };
                ?>">
                <?= ucfirst(str_replace('_',' ',$product['status'])) ?>
              </span>
            </div>

            <div class="col-md-3">
              <div class="text-muted small mb-1">
                <i class="fa fa-clock me-1"></i> Duration
              </div>
              <div class="fw-semibold">
                <?= $product['duration'] ?: '-' ?>
              </div>
            </div>

            <div class="col-md-6">
              <div class="text-muted small mb-1">
                <i class="fa fa-map-marker-alt me-1"></i> Destination
              </div>
              <div class="fw-semibold">
                <?= $product['destination'] ?: '-' ?>
              </div>
            </div>

            <div class="col-md-6">
              <div class="text-muted small mb-1">
                <i class="fa fa-align-left me-1"></i> Short Description
              </div>
              <div class="fw-normal">
                <?= $product['short_description'] ?: '-' ?>
              </div>
            </div>

            <!-- Description Block -->
            <div class="col-md-12">
              <div class="text-muted small mb-1">
                <i class="fa fa-file-alt me-1"></i> Full Description
              </div>

              <div class="bg-light rounded p-3 small">
                <?= $product['description'] ? nl2br($product['description']) : '-' ?>
              </div>
            </div>

          </div>

        </div>
      </div>
    </div>

    <div class="modal fade" id="editBasicModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">Edit Basic Info</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">

            <form id="basicForm">
              <input type="hidden" name="id" value="<?= $product_id ?>">

              <div class="row g-3">

                <div class="col-md-6">
                  <label class="form-label">Supplier *</label>
                  <select name="supplier_id" class="form-select" required>
                    <?php foreach ($suppliers as $s): ?>
                      <option value="<?= $s['id'] ?>"
                        <?= $s['id']==$product['supplier_id']?'selected':'' ?>>
                        <?= htmlspecialchars($s['company']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Zone *</label>
                  <select name="zone_id" class="form-select" required>
                    <?php foreach ($zones as $s): ?>
                      <option value="<?= $s['id'] ?>"
                        <?= $s['id']==$product['zone_id']?'selected':'' ?>>
                        <?= htmlspecialchars($s['zone_name']." - ".$s['region']." ".$s['country']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-12">
                  <label class="form-label">Product Name *</label>
                  <input type="text" name="name"
                         class="form-control"
                         value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Product Type</label>
                  <select name="product_type" class="form-select jselect">
                    <?php foreach (['hotel','flight','taxi','package'] as $t): ?>
                      <option value="<?= $t ?>" <?= $product['product_type']==$t?'selected':'' ?>>
                        <?= ucfirst($t) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Status</label>
                  <select name="status" class="form-select jselect">
                    <?php foreach (['active','inactive','seasonal','sold_out'] as $st): ?>
                      <option value="<?= $st ?>" <?= $product['status']==$st?'selected':'' ?>>
                        <?= ucfirst(str_replace('_',' ',$st)) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Destination</label>
                  <input type="text" name="destination"
                         class="form-control"
                         value="<?= $product['destination'] ?>">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Duration</label>
                  <input type="text" name="duration"
                         class="form-control"
                         value="<?= $product['duration'] ?>">
                </div>

                <div class="col-md-12">
                  <label class="form-label">Short Description</label>
                  <input type="text" name="short_description"
                         class="form-control"
                         value="<?= $product['short_description'] ?>">
                </div>

                <div class="col-md-12">
                  <label class="form-label">Full Description</label>
                  <textarea name="description" rows="4"
                            class="form-control"><?= $product['description'] ?></textarea>
                </div>

              </div>
            </form>

          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary"
                    onclick="saveTab('basic')">
              <i class="fa fa-save me-1"></i> Save Changes
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- PRICING -->
    <div class="tab-content-section d-none" id="tab-pricing">
      <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">

          <!-- Header -->
          <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
              <h5 class="mb-0">Pricing</h5>
              <small class="text-muted">Cost, margin & selling configuration</small>
            </div>

            <button class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#editPricingModal">
              <i class="fa fa-edit me-1"></i> Edit
            </button>
          </div>

          <!-- Pricing Grid -->
          <div class="row g-4 align-items-stretch">

            <div class="col-md-4">
              <div class="h-100 border rounded p-3">
                <div class="text-muted small mb-1">
                  <i class="fa fa-money-bill me-1"></i> Cost Price
                </div>
                <div class="fw-semibold fs-6">
                  <?= number_format($product['cost']??0, 2) ?>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="h-100 border rounded p-3">
                <div class="text-muted small mb-1">
                  <i class="fa fa-percentage me-1"></i> Margin Type
                </div>
                <div class="fw-semibold text-capitalize">
                  <?= $product['margin_type'] ?>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="h-100 border rounded p-3">
                <div class="text-muted small mb-1">
                  <i class="fa fa-chart-line me-1"></i> Margin Value
                </div>
                <div class="fw-semibold">
                  <?= number_format($product['margin_value']??0, 2) ?>
                  <?= $product['margin_type'] === 'percentage' ? '%' : '' ?>
                </div>
              </div>
            </div>

            <!-- Optional Final Price -->
            <?php
              $finalPrice = $product['margin_type'] === 'percentage'
                ? $product['cost'] + ($product['cost'] * $product['margin_value'] / 100)
                : $product['cost'] + $product['margin_value'];
            ?>

            <div class="col-md-12">
              <div class="bg-light border rounded p-3 d-flex justify-content-between align-items-center">
                <div>
                  <div class="text-muted small">
                    <i class="fa fa-tag me-1"></i> Selling Price
                  </div>
                  <div class="fw-bold fs-5 text-success">
                    <?= ($product['cost_currency']!=''?$product['cost_currency']." ":"").number_format($finalPrice??0, 2) ?>
                  </div>
                </div>

                <span class="badge bg-success-subtle text-success border">
                  Auto calculated
                </span>
              </div>
            </div>

          </div>

        </div>
      </div>
    </div>

    <div class="modal fade" id="editPricingModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">Edit Pricing</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">

            <form id="pricingForm">
              <input type="hidden" name="id" value="<?= $product_id ?>">

              <div class="row g-3">

                <div class="col-md-4">
                  <label class="form-label">Cost</label>
                  <input type="number" step="0.01" name="cost"
                         class="form-control"
                         value="<?= $product['cost'] ?>">
                </div>

                <div class="col-md-4">
                  <label class="form-label">Margin Type</label>
                  <select name="margin_type" class="form-select jselect">
                    <option value="amount" <?= $product['margin_type']=='amount'?'selected':'' ?>>
                      Amount
                    </option>
                    <option value="percentage" <?= $product['margin_type']=='percentage'?'selected':'' ?>>
                      Percent
                    </option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Margin Value</label>
                  <input type="number" step="0.01" name="margin_value"
                         class="form-control"
                         value="<?= $product['margin_value'] ?>">
                </div>

              </div>
            </form>

          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary"
                    onclick="saveTab('pricing')">
              <i class="fa fa-save me-1"></i> Save Changes
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- DETAILS -->
    <div class="tab-content-section d-none" id="tab-details">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3">Details</h5>

          <form id="detailsForm">
            <input type="hidden" name="id" value="<?= $product_id ?>">

            <!-- INCLUSIONS -->
            <div class="mb-3">
              <label class="form-label fw-semibold text-success">
                <i class="fa fa-check-circle me-1"></i> Inclusions
              </label>
              <div class="form-control d-flex flex-wrap gap-2" id="inclusionsBox">
                <input type="text" class="border-0 flex-grow-1"
                       placeholder="Type inclusion & press Enter">
              </div>
            </div>

            <!-- EXCLUSIONS -->
            <div class="mb-3">
              <label class="form-label fw-semibold text-danger">
                <i class="fa fa-times-circle me-1"></i> Exclusions
              </label>
              <div class="form-control d-flex flex-wrap gap-2" id="exclusionsBox">
                <input type="text" class="border-0 flex-grow-1"
                       placeholder="Type exclusion & press Enter">
              </div>
            </div>

            <!-- TAGS -->
            <div class="mb-3">
              <label class="form-label fw-semibold">
                <i class="fa fa-tags me-1"></i> Tags
              </label>
              <div class="form-control d-flex flex-wrap gap-2" id="tagsBox">
                <input type="text" class="border-0 flex-grow-1"
                       placeholder="Type tag & press Enter">
              </div>
            </div>

            <!-- HIDDEN JSON FIELDS -->
            <input type="hidden" name="inclusions">
            <input type="hidden" name="exclusions">
            <input type="hidden" name="tags">

            <div class="text-end mt-4">
              <button type="button" class="btn btn-primary" onclick="prepareAndSave()">
                <i class="fa fa-save me-1"></i> Save Details
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>


<!-- DOCUMENT GALLERY VIEWER MODAL (NEW IMPROVED UI) -->
<style>
#prevDocBtn:hover, #nextDocBtn:hover {
    background: #0d6efd;
    color: white !important;
    border-color: #0d6efd;
}

#galleryCounter {
    font-size: 14px;
    font-weight: 600;
}
#zoomControls button:hover {
    background: #0d6efd;
    color: #fff !important;
    border-color: #0d6efd;
}

.zoom-image {
    transition: transform 0.15s ease;
    cursor: grab;
}
</style>
<div class="modal fade" id="documentGalleryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden" style="max-height: 92vh;">
      <!-- HEADER -->
      <div class="modal-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <i class="fa fa-file-text text-primary"></i>
          <h6 class="modal-title fw-semibold text-primary mb-0" id="galleryDocumentTitle">
            Document Preview
          </h6>
        </div>
        <!-- Navigation + Counter -->
        <div class="d-flex align-items-center gap-2">
            <!-- Image Zoom Controls -->
            <div id="zoomControls" class="d-none me-2">
              <button class="btn btn-outline-secondary btn-sm px-2" id="zoomOutBtn">
                <i class="fa fa-search-minus"></i>
              </button>
              <button class="btn btn-outline-secondary btn-sm px-2" id="zoomInBtn">
                <i class="fa fa-search-plus"></i>
              </button>
              <button class="btn btn-outline-secondary btn-sm px-2" id="zoomResetBtn">
                <i class="fa fa-sync"></i>
              </button>
            </div>
            <!-- Counter -->
            <span id="galleryCounter" class="text-muted small me-2">1 of 1</span>
            <!-- Prev / Next -->
            <button id="prevDocBtn" class="btn btn-outline-primary btn-sm rounded-pill px-2">
              <i class="fa fa-chevron-left"></i>
            </button>
            <button id="nextDocBtn" class="btn btn-outline-primary btn-sm rounded-pill px-2">
              <i class="fa fa-chevron-right"></i>
            </button>
            <!-- Close -->
            <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <!-- VIEWER BODY -->
      <div class="modal-body p-0 bg-light" id="galleryViewer"
           style="height: 82vh; display:flex; justify-content:center; align-items:center;">
        <div class="text-center text-muted small">Loading...</div>
      </div>
    </div>
  </div>
</div>
<script>
// =====================================================
// DOCUMENT GALLERY VIEWER - ENHANCED WITH ZOOM + KEYBOARD NAV
// =====================================================
let docGallery = [];
let currentIndex = 0;

let zoomLevel = 1;
let imageElement = null;

// Build gallery dataset
function buildDocumentGallery() {
    docGallery = [];

    $(".view-document-gallery").each(function () {
        docGallery.push({
            file: $(this).data("file"),
            type: $(this).data("type"),
            label: $(this).data("label")
        });
    });
}
$(document).ajaxComplete(() => buildDocumentGallery());
// Keyboard navigation
document.addEventListener("keydown", function (e) {
    const galleryOpen = $("#documentGalleryModal").hasClass("show");
    if (!galleryOpen) return;

    if (e.key === "ArrowRight") $("#nextDocBtn").click();
    if (e.key === "ArrowLeft") $("#prevDocBtn").click();
    if (e.key === "Escape") $("#documentGalleryModal").modal("hide");
});
// Open gallery viewer
$(document).on("click", ".view-document-gallery", function () {
    const file = $(this).data("file");
    currentIndex = docGallery.findIndex(d => d.file === file);
    openGalleryDocument(currentIndex);
});
// Render selected document
function openGalleryDocument(index) {
    if (index < 0 || index >= docGallery.length) return;
    const doc = docGallery[index];
    $("#galleryDocumentTitle").text(doc.label);
    $("#galleryCounter").text(`${index + 1} of ${docGallery.length}`);
    zoomLevel = 1;
    $("#zoomControls").addClass("d-none");
    imageElement = null;
    let html = "";
    if (doc.type === "pdf") {
        html = `<iframe src="${doc.file}" width="100%" height="100%" 
                 style="border:none;background:#fff;"></iframe>`;
    } else {
        html = `<img src="${doc.file}" id="galleryImage" class="img-fluid rounded shadow-sm zoom-image" 
                 style="max-height:82vh; object-fit:contain;">`;
        // enable zoom controls for images
        setTimeout(() => {
            imageElement = document.getElementById("galleryImage");
            $("#zoomControls").removeClass("d-none");
        }, 50);
    }
    $("#galleryViewer").html(html);
    // const modal = new bootstrap.Modal(document.getElementById("documentGalleryModal"));
    // modal.show();
    $("#documentGalleryModal").modal("show");
}
// Navigation
$("#nextDocBtn").click(() => {
    currentIndex = (currentIndex + 1) % docGallery.length;
    openGalleryDocument(currentIndex);
});
$("#prevDocBtn").click(() => {
    currentIndex = (currentIndex - 1 + docGallery.length) % docGallery.length;
    openGalleryDocument(currentIndex);
});
// Zoom controls
$("#zoomInBtn").click(() => adjustZoom(0.1));
$("#zoomOutBtn").click(() => adjustZoom(-0.1));
$("#zoomResetBtn").click(() => {
    zoomLevel = 1;
    applyZoom();
});
function adjustZoom(delta) {
    zoomLevel += delta;
    if (zoomLevel < 0.4) zoomLevel = 0.4;
    if (zoomLevel > 4) zoomLevel = 4;
    applyZoom();
}
function applyZoom() {
    if (imageElement) {
        imageElement.style.transform = `scale(${zoomLevel})`;
    }
}
</script>
    

    <!-- MEDIA -->
    <style>
    .media-thumb {
      position: relative;
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid #ddd;
    }
    .media-thumb img {
      width: 100%;
      height: 120px;
      object-fit: cover;
    }
    .media-thumb .remove-btn {
      position: absolute;
      top: 4px;
      right: 4px;
    }
    .media-thumb .view-document-gallery {
      position: absolute;
      top: 4px;
      right: 38px;
    }
    .media-pdf {
      width: 100%;
      height: 120px;
    }
    </style>
    <div class="tab-content-section d-none" id="tab-media">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3">Media</h5>

          <form id="mediaForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $product_id ?>">

            <div class="mb-3">
              <label class="form-label fw-semibold">Upload Images / Documents</label>
              <input type="file" name="media[]" id="mediaInput" class="form-control" accept="image/*, .pdf" multiple>
            </div>

            <!-- PREVIEW -->
            <div class="row g-2 mb-3" id="mediaPreview"></div>

            <div class="text-end">
              <button type="button" class="btn btn-primary" onclick="saveTab('media')">
                <i class="fa fa-upload me-1"></i> Upload Media
              </button>
            </div>
          </form>

          <hr>

          <!-- UPLOADED MEDIA -->
          <div class="row g-2" id="uploadedMedia"></div>

        </div>
      </div>
    </div>
    <script>
      const mediaInput  = document.getElementById('mediaInput');
      const previewBox  = document.getElementById('mediaPreview');
      const uploadedBox = document.getElementById('uploadedMedia');

      /* PREVIEW */
      mediaInput.addEventListener('change', () => {
        previewBox.innerHTML = '';
        [...mediaInput.files].forEach(file => {
          const col = document.createElement('div');
          col.className = 'col-2';

          if (file.type.startsWith('image')) {
            col.innerHTML = `<img src="${URL.createObjectURL(file)}"
                               class="img-fluid rounded border"
                               style="width:100%; height:120px;object-fit:cover;">`;
          } else {
            col.innerHTML = `
              <div class="border rounded p-3 text-center media-pdf small">
                <i class="fa fa-file fa-2x mb-1"></i><br>${file.name}
              </div>`;
          }
          previewBox.appendChild(col);
        });
      });

      /* LOAD MEDIA */
      function loadMedia() {
        fetch('public/ajax/products_media.php?action=list&id=<?= $product_id ?>')
          .then(r => r.json())
          .then(res => {

            if (!res || !res.success || !Array.isArray(res.files)) {
              uploadedBox.innerHTML = '<p class="text-muted">No media found</p>';
              return;
            }

            uploadedBox.innerHTML = '';

            res.files.forEach(f => {
              uploadedBox.innerHTML += `
                <div class="col-3">
                  <div class="media-thumb">
                    ${f.is_image
                      ? `<img src="${f.url}">`
                      : `<div class="p-3 text-center pt-5 media-pdf">
                           <i class="fa fa-file fa-2x"></i><br>${f.name}
                         </div>`}
                    <button class="btn btn-sm btn-info view-document-gallery" data-label="Media" data-file="${f.url}" data-type="${f.is_image?'image':'pdf'}">
                      <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-danger remove-btn"
                            onclick="deleteMedia('${f.name}')">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </div>`;
            });

            buildDocumentGallery();
          })
          .catch(err => console.error('Media load error:', err));
      }

      /* DELETE MEDIA */
      function deleteMedia(file) {
        if (!confirm('Delete this file?')) return;

        fetch('public/ajax/products_media.php', {
          method: 'POST',
          body: new URLSearchParams({
            id: '<?= $product_id ?>',
            file: file,
            action: 'delete'
          })
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) loadMedia();
          else alert(res.error || 'Delete failed');
        });
      }

      /* INITIAL LOAD */
      loadMedia();
    </script>


    <!-- INTEGRATION -->
    <?php
      /* ---------------------------------
         LOAD INTEGRATION DATA
      --------------------------------- */
      $integration = [];

      $stmt = $mysqli->prepare("
        SELECT *
        FROM product_integrations
        WHERE product_id = ?
        LIMIT 1
      ");
      $stmt->bind_param("i", $product_id);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($row = $result->fetch_assoc()) {
        $integration = $row;
      }
    ?>
    <div class="tab-content-section d-none" id="tab-integration">
      <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">

          <!-- Header -->
          <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
              <h5 class="mb-0">Integration</h5>
              <small class="text-muted">Supplier integration & purchase setup</small>
            </div>

            <button class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#editIntegrationModal">
              <i class="fa fa-edit me-1"></i> Edit
            </button>
          </div>

          <!-- Integration Grid -->
          <div class="row g-4 align-items-stretch">

            <!-- Integration Type -->
            <div class="col-md-4">
              <div class="h-100 border rounded p-3">
                <div class="text-muted small mb-1">
                  <i class="fa fa-plug me-1"></i> Integration Type
                </div>
                <div class="fw-semibold text-capitalize">
                  <?= $integration['integration_type'] ?? 'Manual' ?>
                </div>
              </div>
            </div>

            <!-- Purchase Currency -->
            <div class="col-md-4">
              <div class="h-100 border rounded p-3">
                <div class="text-muted small mb-1">
                  <i class="fa fa-coins me-1"></i> Purchase Currency
                </div>
                <div class="fw-semibold">
                  <?= $product['cost_currency'] ?? 'GBP' ?>
                </div>
              </div>
            </div>

            <!-- Payment Terms -->
            <div class="col-md-4">
              <div class="h-100 border rounded p-3">
                <div class="text-muted small mb-1">
                  <i class="fa fa-credit-card me-1"></i> Payment Terms
                </div>
                <div class="fw-semibold">
                  <?= $integration['payment_terms'] ?? '-' ?>
                </div>
              </div>
            </div>

            <?php if (($integration['integration_type'] ?? 'manual') === 'api'): ?>

              <!-- API Endpoint -->
              <div class="col-md-12">
                <div class="bg-light border rounded p-3">
                  <div class="text-muted small mb-1">
                    <i class="fa fa-link me-1"></i> API Endpoint
                  </div>
                  <div class="fw-semibold text-break">
                    <?= $integration['api_endpoint'] ?: '-' ?>
                  </div>
                </div>
              </div>

              <!-- API Credentials -->
              <div class="col-md-12">
                <div class="bg-light border rounded p-3">
                  <div class="text-muted small mb-1">
                    <i class="fa fa-key me-1"></i> API Credentials Reference
                  </div>
                  <div class="fw-semibold">
                    <?= $integration['api_credentials_ref'] ?: '-' ?>
                  </div>
                </div>
              </div>

            <?php elseif (($integration['integration_type'] ?? '') === 'static_pricing'): ?>

              <!-- Price Sheet -->
              <div class="col-md-12">
                <div class="bg-light border rounded p-3">
                  <div class="text-muted small mb-1">
                    <i class="fa fa-file-excel me-1"></i> Price Sheet
                  </div>

                  <?php if (!empty($integration['price_sheet_file'])): ?>
                    <a href="uploads/price_sheets/<?= $integration['price_sheet_file'] ?>"
                       target="_blank"
                       class="fw-semibold text-decoration-none">
                      <?= $integration['price_sheet_file'] ?>
                    </a>
                  <?php else: ?>
                    <div class="fw-semibold text-muted">No file uploaded</div>
                  <?php endif; ?>

                </div>
              </div>

            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="editIntegrationModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">Edit Integration</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">

            <form id="integrationForm">
              <input type="hidden" name="id" value="<?= $product_id ?>">

              <div class="row g-3">

                <div class="col-md-12">
                  <label class="form-label">Integration Type</label>
                  <select name="integration_type" class="form-select jselect">
                    <option value="api" <?= ($integration['integration_type'] ?? '')=='api'?'selected':'' ?>>
                      API Integration
                    </option>
                    <option value="static_pricing" <?= ($integration['integration_type'] ?? '')=='static_pricing'?'selected':'' ?>>
                      Static Pricing (Excel/PDF)
                    </option>
                    <option value="manual" <?= ($integration['integration_type'] ?? 'manual')=='manual'?'selected':'' ?>>
                      Manual Process
                    </option>
                  </select>
                </div>

                <div class="col-md-6 d-none">
                  <label class="form-label">Purchase Currency</label>
                  <select name="purchase_currency" class="form-select jselect">
                    <?php
                    foreach (['USD','EUR','GBP','AED','INR','SGD','AUD','JPY','CNY','THB'] as $cur) {
                      $sel = (($integration['purchase_currency'] ?? 'GBP') === $cur) ? 'selected' : '';
                      echo "<option value=\"$cur\" $sel>$cur</option>";
                    }
                    ?>
                  </select>
                </div>

                <div class="row g-3 api-fields">

                  <div class="col-md-12">
                    <label class="form-label">API Endpoint</label>
                    <input type="text" name="api_endpoint"
                           class="form-control"
                           value="<?= $integration['api_endpoint'] ?? '' ?>">
                  </div>

                  <div class="col-md-12">
                    <label class="form-label">API Credentials Reference</label>
                    <input type="text" name="api_credentials_ref"
                           class="form-control"
                           value="<?= $integration['api_credentials_ref'] ?? '' ?>">
                  </div>

                </div>

                <div class="row g-3 static-fields">

                  <div class="col-md-12">
                    <label class="form-label">Price Sheet</label>
                    <input type="file"
                           name="price_sheet"
                           class="form-control"
                           accept=".xls,.xlsx,.pdf,.csv">

                    <?php if (!empty($integration['price_sheet_file'])): ?>
                      <small class="text-muted d-block mt-1">
                        Current:
                        <a href="uploads/price_sheets/<?= $integration['price_sheet_file'] ?>"
                           target="_blank">
                          <?= $integration['price_sheet_file'] ?>
                        </a>
                      </small>
                    <?php endif; ?>
                  </div>

                </div>


                <div class="col-md-12">
                  <label class="form-label">Payment Terms</label>
                  <input type="text" name="payment_terms"
                         class="form-control"
                         value="<?= $integration['payment_terms'] ?? '' ?>"
                         placeholder="Net 30 / Prepaid">
                </div>

              </div>
            </form>

          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary"
                    onclick="saveTab('integration')">
              <i class="fa fa-save me-1"></i> Save Changes
            </button>
          </div>

        </div>
      </div>
    </div>
    <script>
      function toggleIntegrationFields() {
        const type = document.querySelector('[name="integration_type"]').value;

        document.querySelector('.api-fields').style.display =
          (type === 'api') ? 'block' : 'none';

        document.querySelector('.static-fields').style.display =
          (type === 'static_pricing') ? 'block' : 'none';
      }

      document.querySelector('[name="integration_type"]')
        .addEventListener('change', toggleIntegrationFields);

      // Init on modal open
      document.getElementById('editIntegrationModal')
        .addEventListener('shown.bs.modal', toggleIntegrationFields);
    </script>


    <!-- TIMELINE -->
    <div class="tab-content-section d-none" id="tab-timeline">

      <style>
        #productTimelineSection .timeline {
          position: relative;
          padding-left: 25px;
          border-left: 2px solid #0d6efd33;
        }
        #productTimelineSection .timeline-date-header {
          font-size: 0.78rem;
          font-weight: 700;
          color: #0d6efd;
          margin: 12px 0 6px 4px;
          opacity: 0.9;
        }
        #productTimelineSection .timeline-item {
          position: relative;
          display: flex;
          gap: 12px;
          margin-bottom: 18px;
        }
        .timeline-bullet {
          width: 32px;
          height: 32px;
          flex: 0 0 32px;
          border-radius: 50%;
          display: flex;
          justify-content: center;
          align-items: center;
          font-size: 0.75rem;
          background: #f1f5ff;
          border: 2px solid #d1e2ff;
        }
        .timeline-icon-success { background:#e7f9ee; border-color:#b8ecc6; }
        .timeline-icon-warning { background:#fff6dd; border-color:#ffe8b0; }
        .timeline-icon-danger  { background:#fde2e4; border-color:#f8b6b9; }
        .timeline-icon-info    { background:#e0f3ff; border-color:#b4e2fc; }
        .timeline-content { flex-grow: 1; }
        .timeline-time {
          font-size: 0.8rem;
          font-weight: 600;
          color: #6c757d;
        }
        .timeline-text {
          font-size: 0.95rem;
          color: #444;
          line-height: 1.35rem;
        }
        .timeline-agent {
          font-size: 0.75rem;
          color: #0d6efd;
          opacity: 0.85;
          display: block;
          margin-top: 2px;
        }
      </style>

      <div id="productTimelineSection" class="card shadow-sm">
        <div class="card-header d-flex justify-content-between">
          <strong>Product Timeline</strong>
          <button class="btn btn-sm btn-outline-secondary" id="btn-refresh-timeline">
            <i class="fa fa-refresh"></i> Refresh
          </button>
        </div>

        <div class="card-body" style="max-height:450px; overflow-y:auto;">
          <div id="timeline-container">
            <div class="text-muted small text-center py-3">
              <i class="fa fa-spinner fa-spin"></i> Loading timeline...
            </div>
          </div>
        </div>

        <div class="card-footer text-center d-none" id="timelineLoadMoreWrapper">
          <button class="btn btn-outline-primary btn-sm rounded-pill" id="btn-load-more">
            <i class="fa fa-chevron-down"></i> Load More
          </button>
        </div>
      </div>
    </div>

    <script>
    let timelineStart = 0;
    const timelineLimit = 20;
    let timelineEnded = false;
    let isLoading = false;
    const productId = <?= (int)$product_id ?>;

    function renderTimelineItems(logs) {
      let html = '';
      let lastDateGroup = '';

      logs.forEach(item => {

        // 🔒 Safe date parsing
        const parts = item.date.split(" ");
        const timeOnly = parts.slice(-2).join(" ");
        const fullDate = parts.slice(0, -2).join(" ");
        const logDateObj = new Date(item.date.replace(/(\d{2}) (\w{3}) (\d{4})/, '$2 $1 $3'));

        let dateLabel = fullDate;
        const today = new Date();
        const yesterday = new Date();
        yesterday.setDate(today.getDate() - 1);

        if (logDateObj.toDateString() === today.toDateString()) dateLabel = "Today";
        else if (logDateObj.toDateString() === yesterday.toDateString()) dateLabel = "Yesterday";

        if (dateLabel !== lastDateGroup) {
          html += `<div class="timeline-date-header">${dateLabel}</div>`;
          lastDateGroup = dateLabel;
        }

        let icon = "fa-info-circle";
        let bulletClass = "timeline-icon-info";
        const logText = item.action.toLowerCase();

        if (logText.includes("payment") || logText.includes("paid")) {
          icon = "fa-wallet"; bulletClass = "timeline-icon-success";
        } else if (logText.includes("reminder") || logText.includes("expire")) {
          icon = "fa-bell"; bulletClass = "timeline-icon-warning";
        } else if (logText.includes("deleted") || logText.includes("rejected")) {
          icon = "fa-times-circle"; bulletClass = "timeline-icon-danger";
        } else if (logText.includes("update")) {
          icon = "fa-edit";
        }

        html += `
          <div class="timeline-item">
            <div class="timeline-bullet ${bulletClass}">
              <i class="fa ${icon}"></i>
            </div>
            <div class="timeline-content">
              <div class="timeline-time">${fullDate} ${timeOnly}</div>
              <div class="timeline-text">${item.action}</div>
              <span class="timeline-agent">${item.by || 'System'}</span>
            </div>
          </div>
        `;
      });

      return html;
    }

    function loadTimeline(reset = false) {
      if (isLoading || (timelineEnded && !reset)) return;
      isLoading = true;

      if (reset) {
        timelineStart = 0;
        timelineEnded = false;
        $('#timeline-container').html('<div class="timeline"></div>');
        $('#timelineLoadMoreWrapper').addClass('d-none');
      }

      $.ajax({
        url: 'public/ajax/products_recent_actions.php',
        type: 'POST',
        dataType: 'json',
        data: {
          product_id: productId,
          type: 'timeline',
          start: timelineStart,
          length: timelineLimit
        },
        success: function(res) {
          const logs = res.data || [];

          if (!logs.length) {
            if (!timelineStart) {
              $('#timeline-container').html('<div class="text-muted text-center py-3">No activity found</div>');
            }
            timelineEnded = true;
            $('#timelineLoadMoreWrapper').addClass('d-none');
            isLoading = false;
            return;
          }

          $('#timeline-container .timeline').append(renderTimelineItems(logs));

          timelineStart += logs.length;
          logs.length < timelineLimit
            ? $('#timelineLoadMoreWrapper').addClass('d-none')
            : $('#timelineLoadMoreWrapper').removeClass('d-none');

          isLoading = false;
        },
        error: function() {
          $('#timeline-container').html('<div class="text-danger text-center py-3">Failed to load timeline</div>');
          isLoading = false;
        }
      });
    }

    /* Load on tab open */
    $('button[data-bs-target="#tab-timeline"]').on('shown.bs.tab', function () {
      if (!$('#timeline-container .timeline').length) loadTimeline(true);
    });

    /* Load more */
    $('#btn-load-more').on('click', () => loadTimeline());

    /* Refresh */
    $('#btn-refresh-timeline').on('click', () => loadTimeline(true));
    loadTimeline(true);
    </script>
    <!-- timeline end -->


  </div>

  <!-- RIGHT -->
  <div class="col-lg-4">

    <!-- QUICK NAVIGATION PANEL -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 quick-actions">
      <div class="card-body">
        <h6 class="fw-semibold text-primary mb-2 d-flex align-items-center gap-2">
          <i class="fa fa-layer-group"></i> Quick Navigate
        </h6>

        <div class="row g-1">

          <div class="col-4">
            <button class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn"
                    onclick="document.getElementById('viewBtn').click();">
              <i class="fa fa-eye text-primary"></i>
              <span class="fw-semibold text-nowrap">View</span>
            </button>
          </div>

          <div class="col-4">
            <button class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn"
                    onclick="document.getElementById('basicBtn').click();">
              <i class="fa fa-info-circle text-info"></i>
              <span class="fw-semibold text-nowrap">Basic</span>
            </button>
          </div>

          <div class="col-4">
            <button class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn"
                    onclick="document.getElementById('pricingBtn').click();">
              <i class="fa fa-pound-sign text-success"></i>
              <span class="fw-semibold text-nowrap">Pricing</span>
            </button>
          </div>

          <div class="col-4">
            <button class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn"
                    onclick="document.getElementById('detailsBtn').click();">
              <i class="fa fa-list-check text-warning"></i>
              <span class="fw-semibold text-nowrap">Details</span>
            </button>
          </div>

          <div class="col-4">
            <button class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn"
                    onclick="document.getElementById('mediaBtn').click();">
              <i class="fa fa-images text-purple"></i>
              <span class="fw-semibold text-nowrap">Media</span>
            </button>
          </div>

          <div class="col-4">
            <button class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn"
                    onclick="document.getElementById('timelineBtn').click();">
              <i class="fa fa-clock text-secondary"></i>
              <span class="fw-semibold text-nowrap">Timeline</span>
            </button>
          </div>

        </div>
      </div>
    </div>


    <!-- RECENT ACTIONS -->
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <strong>Recent Actions</strong>
        <button class="btn btn-sm btn-outline-secondary" id="refreshRecentActions">
          <i class="fa fa-refresh"></i>
        </button>
      </div>

      <div class="card-body">
        <div id="recentActionsBox">
          <div class="text-muted small text-center py-2">
            <i class="fa fa-spinner fa-spin"></i> Loading...
          </div>
        </div>
      </div>
    </div>
    <script>
      $(function () {

        const productId = <?= (int)$product_id ?>;
        const logURL = 'public/ajax/products_recent_actions.php';

        function loadRecentActions() {

          $('#recentActionsBox').html(
            `<div class="text-muted small text-center py-2">
              <i class="fa fa-spinner fa-spin"></i> Loading...
            </div>`
          );

          $.ajax({
            url: logURL,
            type: 'POST',
            dataType: 'json',
            data: {
              product_id: productId,
              type: 'timeline',
              start: 0,
              length: 5
            },
            success: function (res) {

              const logs = res.data || [];

              if (!logs.length) {
                $('#recentActionsBox').html(
                  `<div class="text-muted small text-center py-2">
                    No recent activity
                  </div>`
                );
                return;
              }

              let html = '';

              logs.forEach(item => {
                html += `
                  <div class="mb-2">
                    <strong>${item.by || 'System'}</strong><br>
                    ${item.action}<br>
                    <small class="text-muted">${item.date}</small>
                  </div>
                  <hr>
                `;
              });

              $('#recentActionsBox').html(html);
            },
            error: function () {
              $('#recentActionsBox').html(
                `<div class="text-danger small text-center py-2">
                  Failed to load data
                </div>`
              );
            }
          });
        }

        /* Initial load */
        loadRecentActions();

        /* Refresh */
        $('#refreshRecentActions').on('click', function () {
          loadRecentActions();
        });

      });
      </script>


  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
$('.tab-btn').on('click', function(){
  $('.tab-btn').removeClass('active');
  $(this).addClass('active');

  const tab = $(this).data('tab');
  $('.tab-content-section').addClass('d-none');
  $('#tab-' + tab).removeClass('d-none');
});
</script>

<script>
function saveTab(tab) {
  const form = document.getElementById(tab + 'Form');
  const fd = new FormData(form);
  fd.append('tab', tab);

  fetch('public/ajax/products_save.php', {
    method: 'POST',
    body: fd
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {

      if(tab=='media') {
        const mediaInput  = document.getElementById('mediaInput');
        const previewBox  = document.getElementById('mediaPreview');
        const uploadedBox = document.getElementById('uploadedMedia');
        mediaInput.value = '';
        previewBox.innerHTML = '';
        loadMedia();
      }

      alert('Saved successfully');
      window.location.reload();
    } else {
      alert(res.error || 'Save failed');
    }
  });
}
</script>

<script>
function chipInput(boxId, data = [], badgeClass) {
  const box = document.getElementById(boxId);
  const input = box.querySelector('input');
  let items = [...data];

  function render() {
    box.querySelectorAll('.chip').forEach(c => c.remove());
    items.forEach((text, i) => {
      const chip = document.createElement('span');
      chip.className = `badge ${badgeClass} chip d-flex align-items-center`;
      chip.innerHTML = `${text} <i class="fa fa-times ms-2"></i>`;
      chip.onclick = () => { items.splice(i, 1); render(); };
      box.insertBefore(chip, input);
    });
  }

  input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && input.value.trim()) {
      e.preventDefault();
      items.push(input.value.trim());
      input.value = '';
      render();
    }
  });

  render();
  return () => items;
}

/* EXISTING JSON FROM DB */
const getInclusions = chipInput(
  'inclusionsBox',
  <?= $product['inclusions'] ?: '[]' ?>,
  'bg-success'
);

const getExclusions = chipInput(
  'exclusionsBox',
  <?= $product['exclusions'] ?: '[]' ?>,
  'bg-danger'
);

const getTags = chipInput(
  'tagsBox',
  <?= $product['tags'] ?: '[]' ?>,
  'bg-primary'
);

/* SAVE */
function prepareAndSave() {
  document.querySelector('[name="inclusions"]').value =
    JSON.stringify(getInclusions());

  document.querySelector('[name="exclusions"]').value =
    JSON.stringify(getExclusions());

  document.querySelector('[name="tags"]').value =
    JSON.stringify(getTags());

  saveTab('details');
}
</script>

<script src="public/assets/js/jselect1.js"></script>