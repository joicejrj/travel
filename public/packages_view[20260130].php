<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

$package_id = (int)($_GET['id'] ?? 0);
if (!$package_id) {
  echo "<div class='alert alert-danger'>Invalid Package</div>";
  require_once __DIR__ . '/includes/footer.php';
  exit;
}

/* FETCH PACKAGE */
$stmt = $mysqli->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();

if (!$package) {
  echo "<div class='alert alert-danger'>Package not found</div>";
  require_once __DIR__ . '/includes/footer.php';
  exit;
}

function normalize_json($value) {
  if (!$value) return [];
  if (is_string($value)) {
    $decoded = json_decode($value, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
      // Only keep scalar string values
      return array_values(array_filter(array_map(
        fn($v) => is_string($v) ? trim($v) : null,
        $decoded
      )));
    }
  }
  return [];
}

$highlights = normalize_json($package['highlights'] ?? '[]');
$inclusions = normalize_json($package['inclusions'] ?? '[]');
$exclusions = normalize_json($package['exclusions'] ?? '[]');

$components = json_decode($package['components'] ?? '[]', true) ?: [];
$itinerary  = json_decode($package['itinerary'] ?? '[]', true) ?: [];
$pricing    = json_decode($package['pricing'] ?? '{}', true) ?: [];

/* -------------------------------
   FETCH PRODUCTS FOR COMPONENTS
-------------------------------- */
$products = [];

$res = $mysqli->query("
  SELECT id, name, product_type, cost
  FROM products
  WHERE status = 'active'
  ORDER BY name
");

while ($row = $res->fetch_assoc()) {
  $products[] = $row;
}


?>
<?php
$currency = $pricing['currency'] ?? 'USD';

/* Total component cost */
$totalCost = 0;
foreach ($components as $c) {
  $qty  = (float)($c['qty'] ?? 1);
  $cost = (float)($c['cost'] ?? 0);
  $totalCost += $qty * $cost;
}

/* Markup */
$markupType  = $pricing['markup_type'] ?? 'percentage';
$markupValue = (float)($pricing['markup_value'] ?? 0);

if ($markupType === 'percentage') {
  $markupAmount = $totalCost * ($markupValue / 100);
} else {
  $markupAmount = $markupValue;
}

/* Sell price */
$sellPrice = $totalCost + $markupAmount;

/* Profit margin % */
$profitMargin = $totalCost > 0
  ? ($markupAmount / $sellPrice) * 100
  : 0;

/* Agent commission */
$commissionType  = $pricing['commission_type'] ?? 'percentage';
$commissionValue = $pricing['commission_value'] ?? 0;
?>

<!-- ================= HEADER ================= -->
<div class="header border-bottom pb-2 mb-3">
  <div class="d-flex flex-wrap align-items-center gap-2">

    <h4 class="mb-0 fw-bold"><?= htmlspecialchars($package['name']) ?></h4>

    <?php
    $tabs = [
      'view'       => ['View','eye'],
      'basic'      => ['Basic Info','info-circle'],
      'components' => ['Components','layer-group'],
      'itinerary'  => ['Itinerary','route'],
      'pricing'    => ['Pricing','pound-sign'],
      'media'      => ['Media','images'],
      'timeline'   => ['Timeline','clock']
    ];
    foreach ($tabs as $k=>$v):
    ?>
      <button class="btn btn-outline-primary btn-sm fw-bold tab-btn <?= $k==='view'?'active':'' ?>"
              data-tab="<?= $k ?>" id="<?= $k ?>Btn">
        <i class="fa fa-<?= $v[1] ?>"></i> <?= $v[0] ?>
      </button>
    <?php endforeach; ?>

    <span class="badge bg-<?= $package['status']=='active'?'success':'secondary' ?>">
      <?= ucfirst($package['status']) ?>
    </span>

  </div>
</div>

<!-- ================= CONTENT ================= -->
<div class="row g-3">

<div class="col-lg-8">

<script>
function formatCurrency(value, currency = 'GBP') {
  try {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 2
    }).format(value || 0);
  } catch (e) {
    return currency + ' ' + (value || 0).toFixed(2);
  }
}
</script>
<script>
function calculateSellPrice() {

  const markupTypeEl  = document.querySelector('[name="markup_type"]');
  const markupValueEl = document.querySelector('[name="markup_value"]');
  const currencyEl    = document.querySelector('[name="currency"]');

  // Pricing tab not loaded yet → safely exit
  if (!markupTypeEl || !markupValueEl || !currencyEl) {
    return;
  }

  const totalCost   = Number(calculateComponentTotal()) || 0;
  const markupType  = markupTypeEl.value;
  const markupValue = Number(markupValueEl.value) || 0;
  const currency    = currencyEl.value;

  let sellPrice = totalCost;

  if (markupType === 'percentage') {
    sellPrice += totalCost * (markupValue / 100);
  } else {
    sellPrice += markupValue;
  }

  const totalCostEl = document.getElementById('uiTotalCost');
  const sellPriceEl = document.getElementById('uiSellPrice');

  if (totalCostEl) {
    totalCostEl.innerText = formatCurrency(totalCost, currency);
  }

  if (sellPriceEl) {
    sellPriceEl.innerText = formatCurrency(sellPrice, currency);
  }

  const sellInput = document.querySelector('[name="sell_price"]');
  if (sellInput) {
    sellInput.value = sellPrice;
  }
}
</script>
<script>
  $('.tab-btn').on('click', function(){
    $('.tab-btn').removeClass('active');
    $(this).addClass('active');
    $('.tab-content-section').addClass('d-none');
    $('#tab-' + $(this).data('tab')).removeClass('d-none');

    if ($(this).data('tab') === 'pricing') {
      calculateSellPrice();
    }
  });
</script>

<!-- ================= VIEW ================= -->
<div class="tab-content-section m-2" id="tab-view">

  <!-- ================= HEADER ================= -->
  <div class="mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">

      <!-- LEFT: Destination + Duration -->
      <div class="text-muted d-flex align-items-center gap-4 flex-wrap">
        <span>📍 <?= htmlspecialchars($package['destination']) ?></span>
        <span>⏱ <?= $package['duration_days'] ?>D / <?= $package['duration_nights'] ?>N</span>
      </div>

      <!-- CENTER: VIEW TABS -->
      <div class="btn-group bg-light p-1 rounded-pill">
        <button class="btn btn-sm btn-outline-primary active view-tab" data-view="overview">
          Overview
        </button>
        <button class="btn btn-sm btn-outline-primary view-tab" data-view="itinerary">
          Itinerary
        </button>
        <button class="btn btn-sm btn-outline-primary view-tab" data-view="components">
          Components
        </button>
      </div>

      <!-- RIGHT: STATUS -->
      <span class="badge
        <?= $package['status']==='active'
          ? 'bg-success-subtle text-success'
          : 'bg-secondary-subtle text-secondary' ?>">
        <?= ucfirst($package['status']) ?>
      </span>

    </div>
  </div>


  <div class="row g-4">

    <!-- ================= LEFT COLUMN ================= -->
    <div class="col-lg-8">

      <!-- ================= OVERVIEW ================= -->
      <div class="view-panel" id="view-overview">

        <!-- Description -->
        <div class="card mb-4">
          <div class="card-body">
            <h5>Description</h5>
            <p class="text-muted" style="white-space:pre-wrap">
              <?= htmlspecialchars($package['description']) ?>
            </p>
          </div>
        </div>

        <!-- Highlights -->
        <?php if ($highlights): ?>
        <div class="card mb-4">
          <div class="card-body">
            <h5>Highlights</h5>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach ($highlights as $h): ?>
                <span class="badge bg-primary-subtle text-primary">
                  <?= htmlspecialchars($h) ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Inclusions / Exclusions -->
        <div class="row g-4">
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-body">
                <h5 class="text-success">Inclusions</h5>
                <ul class="list-unstyled">
                  <?php foreach ($inclusions as $i): ?>
                    <li class="mb-2">✔ <?= htmlspecialchars($i) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-body">
                <h5 class="text-danger">Exclusions</h5>
                <ul class="list-unstyled">
                  <?php foreach ($exclusions as $e): ?>
                    <li class="mb-2">✖ <?= htmlspecialchars($e) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ================= ITINERARY ================= -->
      <div class="view-panel d-none" id="view-itinerary">

        <div class="card">
          <div class="card-body">
            <h5 class="mb-4">Day-by-Day Itinerary</h5>

            <?php foreach ($itinerary as $day): ?>
              <div class="position-relative ps-4 pb-4 border-start border-primary-subtle">
                <div class="position-absolute start-0 translate-middle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                     style="width:26px;height:26px;">
                  <?= (int)$day['day'] ?>
                </div>

                <h6 class="fw-semibold">
                  <?= htmlspecialchars($day['title'] ?? '') ?>
                </h6>

                <p class="text-muted">
                  <?= nl2br(htmlspecialchars($day['description'] ?? '')) ?>
                </p>

                <div class="text-muted small d-flex gap-3">
                  <?php if (!empty($day['meals'])): ?>
                    <span>🍽 <?= htmlspecialchars($day['meals']) ?></span>
                  <?php endif; ?>
                  <?php if (!empty($day['accommodation'])): ?>
                    <span>🏨 <?= htmlspecialchars($day['accommodation']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>

          </div>
        </div>

      </div>

      <!-- ================= COMPONENTS ================= -->
      <div class="view-panel d-none" id="view-components">

        <div class="card">
          <div class="card-body">
            <h5 class="mb-4">Package Components</h5>

            <?php
              $total = 0;
              $currency = $pricing['currency'] ?? 'GBP';
            ?>

            <?php foreach ($components as $c): ?>
              <?php
                $line = ($c['qty'] ?? 0) * ($c['cost'] ?? 0);
                $total += $line;
              ?>
              <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-2">
                <div>
                  <div class="fw-medium">
                    Product #<?= (int)$c['product_id'] ?>
                  </div>
                  <div class="text-muted small">
                    Day <?= (int)$c['day'] ?> • Qty <?= (int)$c['qty'] ?>
                  </div>
                </div>
                <strong><?= $currency ?> <?= number_format($line,2) ?></strong>
              </div>
            <?php endforeach; ?>

            <div class="border-top pt-3 mt-3 d-flex justify-content-between">
              <strong>Total Component Cost</strong>
              <span class="fs-5 fw-bold">
                <?= $currency ?> <?= number_format($total,2) ?>
              </span>
            </div>

          </div>
        </div>

      </div>

    </div>

    <!-- ================= RIGHT COLUMN ================= -->
    <div class="col-lg-4">

      <!-- Pricing -->
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

          <h5 class="fw-semibold mb-4">Pricing</h5>

          <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 mb-3">
            <span class="text-secondary">Total Cost</span>
            <span class="fw-semibold">
              <?= $currency ?> <?= number_format($totalCost, 2) ?>
            </span>
          </div>

          <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 mb-3">
            <span class="text-secondary">Markup</span>
            <span class="fw-semibold">
              <?= $markupType === 'percentage'
                ? $markupValue . '%'
                : $currency . ' ' . number_format($markupValue, 2) ?>
            </span>
          </div>

          <div class="d-flex justify-content-between align-items-center p-4 rounded-3 mb-3"
               style="background: linear-gradient(90deg, #e9f7ef, #eef4ff);">
            <span class="fw-medium text-secondary">Sell Price</span>
            <span class="fs-4 fw-bold text-success">
              <?= $currency ?> <?= number_format($sellPrice, 2) ?>
            </span>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="small text-muted">Profit Margin</span>
            <span class="fw-medium text-success">
              <?= number_format($profitMargin, 1) ?>%
            </span>
          </div>

          <hr class="my-3">

          <div class="d-flex justify-content-between align-items-center">
            <span class="text-secondary">Agent Commission</span>
            <span class="fw-semibold">
              <?= $commissionType === 'percentage'
                ? $commissionValue . '%'
                : $currency . ' ' . number_format($commissionValue, 2) ?>
            </span>
          </div>

        </div>
      </div>

      <!-- Details -->
      <div class="card">
        <div class="card-body">
          <h5>Details</h5>

          <div class="mb-3">
            <small class="text-muted">Group Size</small>
            <div>
              <?= $package['min_passengers'] ?> – <?= $package['max_passengers'] ?> passengers
            </div>
          </div>

          <div>
            <small class="text-muted">Validity</small>
            <div>
              <?= $package['valid_from'] ?> – <?= $package['valid_to'] ?>
            </div>
          </div>

        </div>
      </div>

    </div>

  </div>
</div>

<!-- ================= VIEW TAB JS ================= -->
<script>
document.querySelectorAll('.view-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.view-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.view-panel').forEach(p => p.classList.add('d-none'));

    btn.classList.add('active');
    document.getElementById('view-' + btn.dataset.view).classList.remove('d-none');
  });
});
</script>


<!-- ================= BASIC INFO ================= -->
<div class="tab-content-section d-none" id="tab-basic">
  <div class="card shadow-sm">
    <div class="card-body">

      <h5 class="fw-semibold mb-4">Basic Info</h5>

      <form id="basicForm">
        <input type="hidden" name="id" value="<?= $package_id ?>">

        <div class="row g-4">

          <!-- Package Name -->
          <div class="col-md-12">
            <label class="form-label fw-medium">Package Name *</label>
            <input type="text"
                   name="name"
                   class="form-control"
                   placeholder="e.g., Romantic Paris Getaway"
                   required
                   value="<?= htmlspecialchars($package['name']) ?>">
          </div>

          <!-- Destination -->
          <div class="col-md-6">
            <label class="form-label fw-medium">Destination</label>
            <input type="text"
                   name="destination"
                   class="form-control"
                   placeholder="e.g., Paris, France"
                   value="<?= htmlspecialchars($package['destination']) ?>">
          </div>

          <!-- Status -->
          <div class="col-md-6">
            <label class="form-label fw-medium">Status</label>
            <select name="status" class="form-select">
              <?php foreach (['draft','active','inactive','archived'] as $s): ?>
                <option value="<?= $s ?>" <?= $package['status']===$s?'selected':'' ?>>
                  <?= ucfirst($s) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Duration Days -->
          <div class="col-md-6">
            <label class="form-label fw-medium">Duration (Days)</label>
            <input type="number"
                   name="duration_days"
                   min="0"
                   class="form-control"
                   value="<?= (int)$package['duration_days'] ?>">
          </div>

          <!-- Duration Nights -->
          <div class="col-md-6">
            <label class="form-label fw-medium">Duration (Nights)</label>
            <input type="number"
                   name="duration_nights"
                   min="0"
                   class="form-control"
                   value="<?= (int)$package['duration_nights'] ?>">
          </div>

          <!-- Min Passengers -->
          <div class="col-md-6">
            <label class="form-label fw-medium">Min Passengers</label>
            <input type="number"
                   name="min_passengers"
                   min="1"
                   class="form-control"
                   value="<?= (int)$package['min_passengers'] ?>">
          </div>

          <!-- Max Passengers -->
          <div class="col-md-6">
            <label class="form-label fw-medium">Max Passengers</label>
            <input type="number"
                   name="max_passengers"
                   min="1"
                   class="form-control"
                   value="<?= (int)$package['max_passengers'] ?>">
          </div>

          <!-- Valid From -->
          <div class="col-md-6">
            <label class="form-label fw-medium">Valid From</label>
            <input type="date"
                   name="valid_from"
                   class="form-control"
                   value="<?= $package['valid_from'] ?>">
          </div>

          <!-- Valid To -->
          <div class="col-md-6">
            <label class="form-label fw-medium">Valid To</label>
            <input type="date"
                   name="valid_to"
                   class="form-control"
                   value="<?= $package['valid_to'] ?>">
          </div>

          <!-- Description -->
          <div class="col-md-12">
            <label class="form-label fw-medium">Description</label>
            <textarea name="description"
                      rows="4"
                      class="form-control"
                      placeholder="Describe the package..."><?= htmlspecialchars($package['description']) ?></textarea>
          </div>

          <!-- Highlights -->
          <div class="col-md-12">
            <label class="form-label fw-medium">Highlights</label>
            <div class="input-group">
              <input type="text"
                     class="form-control"
                     id="highlightInput"
                     placeholder="Add highlight...">
              <button class="btn btn-primary" type="button" onclick="addChip('highlight')">
                <i class="fa fa-plus"></i>
              </button>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3" id="highlightsBox"></div>
          </div>

          <!-- Inclusions -->
          <div class="col-md-6">
            <label class="form-label fw-medium">Inclusions</label>
            <div class="input-group">
              <input type="text"
                     class="form-control"
                     id="inclusionInput"
                     placeholder="Add inclusion...">
              <button class="btn btn-primary" type="button" onclick="addChip('inclusion')">
                <i class="fa fa-plus"></i>
              </button>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3" id="inclusionsBox"></div>
          </div>

          <!-- Exclusions -->
          <div class="col-md-6">
            <label class="form-label fw-medium">Exclusions</label>
            <div class="input-group">
              <input type="text"
                     class="form-control"
                     id="exclusionInput"
                     placeholder="Add exclusion...">
              <button class="btn btn-primary" type="button" onclick="addChip('exclusion')">
                <i class="fa fa-plus"></i>
              </button>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3" id="exclusionsBox"></div>
          </div>

        </div>

        <!-- Hidden JSON fields -->
        <input type="hidden" name="highlights">
        <input type="hidden" name="inclusions">
        <input type="hidden" name="exclusions">

        <div class="text-end mt-4">
          <button type="button" class="btn btn-primary" onclick="prepareBasicSave()">
            <i class="fa fa-save me-1"></i> Save Basic Info
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
<script>
const dataStore = {
  highlight: <?= $package['highlights'] ?: '[]' ?>,
  inclusion: <?= $package['inclusions'] ?: '[]' ?>,
  exclusion: <?= $package['exclusions'] ?: '[]' ?>
};

function renderChips(type, boxId, badgeClass) {
  const box = document.getElementById(boxId);
  box.innerHTML = '';
  dataStore[type].forEach((v, i) => {
    const chip = document.createElement('span');
    chip.className = `badge ${badgeClass} px-3 py-2`;
    chip.innerHTML = `${v} <i class="fa fa-times ms-2" style="cursor:pointer"></i>`;
    chip.onclick = () => {
      dataStore[type].splice(i,1);
      renderAll();
    };
    box.appendChild(chip);
  });
}

function addChip(type) {
  const input = document.getElementById(type + 'Input');
  if (!input.value.trim()) return;
  dataStore[type].push(input.value.trim());
  input.value = '';
  renderAll();
}

function renderAll() {
  renderChips('highlight','highlightsBox','bg-primary-subtle text-primary');
  renderChips('inclusion','inclusionsBox','bg-success-subtle text-success');
  renderChips('exclusion','exclusionsBox','bg-danger-subtle text-danger');
}

function prepareBasicSave() {
  document.querySelector('[name="highlights"]').value = JSON.stringify(dataStore.highlight);
  document.querySelector('[name="inclusions"]').value = JSON.stringify(dataStore.inclusion);
  document.querySelector('[name="exclusions"]').value = JSON.stringify(dataStore.exclusion);
  saveTab('basic');
}

renderAll();
</script>


<!-- ================= COMPONENTS ================= -->
<div class="tab-content-section d-none" id="tab-components">
  <div class="card shadow-sm">
    <div class="card-body">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h5 class="mb-1">Package Components</h5>
          <small class="text-muted">Add products from your catalog</small>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="addComponentRow()">
          <i class="fa fa-plus me-1"></i> Add Component
        </button>
      </div>

      <form id="componentsForm">
        <input type="hidden" name="id" value="<?= $package_id ?>">
        <input type="hidden" name="components">

        <div id="componentsContainer" class="d-flex flex-column gap-3"></div>

        <!-- TOTAL -->
        <div class="mt-4 p-3 rounded bg-light border">
          <div class="d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Total Component Cost</span>
            <span class="fs-5 fw-bold" id="componentsTotal">0.00</span>
          </div>
        </div>

        <div class="text-end mt-4">
          <button type="button" class="btn btn-primary" onclick="prepareComponentsSave()">
            <i class="fa fa-save me-1"></i> Save Components
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
<script>
let componentsData = <?= $package['components'] ?: '[]' ?>;

function addComponentRow(data = {}) {

  const row = document.createElement('div');
  row.className = 'border rounded p-3';

  row.innerHTML = `
    <div class="row g-3 align-items-end">

      <div class="col-md-4">
        <label class="form-label small text-muted">Product</label>
        <select class="form-select product">
          <?php foreach ($products as $p): ?>
            <option
              value="<?= $p['id'] ?>"
              data-cost="<?= $p['cost'] ?? 0 ?>"
            >
              <?= htmlspecialchars($p['name']) ?> - <?= $p['product_type'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label small text-muted">Day</label>
        <input type="number" min="1" class="form-control day" value="${data.day || 1}">
      </div>

      <div class="col-md-2">
        <label class="form-label small text-muted">Qty</label>
        <input type="number" min="1" class="form-control qty component-qty" value="${data.qty || 1}">
      </div>

      <div class="col-md-3">
        <label class="form-label small text-muted">Unit Cost</label>
        <input type="number" step="0.01" class="form-control cost component-cost" value="${data.cost || 0}">
      </div>

      <div class="col-md-1 text-end">
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeComponentRow(this)">
          <i class="fa fa-trash"></i>
        </button>
      </div>

    </div>
  `;

  const productSelect = row.querySelector('.product');
  const costInput     = row.querySelector('.cost');
  const qtyInput      = row.querySelector('.qty');

  /* Preselect product if editing */
  if (data.product_id) {
    productSelect.value = data.product_id;
  }

  /* Load cost from selected product */
  function loadProductCost() {
    const selected = productSelect.options[productSelect.selectedIndex];
    const price = parseFloat(selected.dataset.cost || 0);
    costInput.value = price.toFixed(2);
    calculateComponentTotal();
  }

  /* EVENTS */
  productSelect.addEventListener('change', loadProductCost);
  qtyInput.addEventListener('input', calculateComponentTotal);
  costInput.addEventListener('input', calculateComponentTotal);

  /* Init cost (edit mode) */
  if (!data.cost) {
    loadProductCost();
  }

  document.getElementById('componentsContainer').appendChild(row);
  calculateComponentTotal();
  calculateSellPrice();
}

function removeComponentRow(btn) {
  btn.closest('.border').remove();
  calculateComponentTotal();
  calculateSellPrice();
}

function calculateComponentTotal() {
  let total = 0;

  document.querySelectorAll('#componentsContainer .border').forEach(row => {
    const qty  = parseFloat(row.querySelector('.qty')?.value) || 0;
    const cost = parseFloat(row.querySelector('.cost')?.value) || 0;
    total += qty * cost;
  });

  // Update components tab UI (if visible)
  const compTotal = document.getElementById('componentsTotal');
  if (compTotal) {
    compTotal.innerText = formatCurrency(total);
  }

  // Update pricing hidden field
  const hidden = document.getElementById('totalComponentCost');
  if (hidden) {
    hidden.value = total;
  }

  return total;
}

function prepareComponentsSave() {
  const rows = [];
  document.querySelectorAll('#componentsContainer .border').forEach(r => {
    rows.push({
      product_id: r.querySelector('.product').value,
      day: r.querySelector('.day').value,
      qty: r.querySelector('.qty').value,
      cost: r.querySelector('.cost').value
    });
  });

  document.querySelector('[name="components"]').value = JSON.stringify(rows);
  saveTab('components');
}

/* INIT */
componentsData.forEach(c => addComponentRow(c));
</script>


<!-- ================= ITINERARY ================= -->
<div class="tab-content-section d-none" id="tab-itinerary">
  <div class="card shadow-sm">
    <div class="card-body">

      <!-- HEADER -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h5 class="mb-1">Day-by-Day Itinerary</h5>
          <small class="text-muted">Plan each day of the trip</small>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="addItineraryDay()">
          <i class="fa fa-plus me-1"></i> Add Day
        </button>
      </div>

      <form id="itineraryForm">
        <input type="hidden" name="id" value="<?= $package_id ?>">
        <input type="hidden" name="itinerary">

        <div id="itineraryContainer" class="d-flex flex-column gap-3"></div>

        <div class="text-end mt-4">
          <button type="button" class="btn btn-primary" onclick="prepareItinerarySave()">
            <i class="fa fa-save me-1"></i> Save Itinerary
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
<script>
let itineraryData = <?= $package['itinerary'] ?: '[]' ?>;

function addItineraryDay(data = {}) {

  const dayNo = document.querySelectorAll('#itineraryContainer .itinerary-day').length + 1;

  const card = document.createElement('div');
  card.className = 'border rounded p-3 itinerary-day';

  card.innerHTML = `
    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="badge bg-primary-subtle text-primary">
        Day ${dayNo}
      </span>
      <button type="button"
              class="btn btn-sm btn-outline-danger"
              onclick="removeItineraryDay(this)">
        <i class="fa fa-trash"></i>
      </button>
    </div>

    <div class="row g-3">

      <div class="col-md-12">
        <label class="form-label small text-muted">Title</label>
        <input type="text"
               class="form-control title"
               placeholder="e.g., Arrival & City Tour"
               value="${data.title || ''}">
      </div>

      <div class="col-md-12">
        <label class="form-label small text-muted">Description</label>
        <textarea class="form-control description"
                  rows="3"
                  placeholder="Describe the day's activities...">${data.description || ''}</textarea>
      </div>

      <div class="col-md-6">
        <label class="form-label small text-muted">Meals</label>
        <input type="text"
               class="form-control meals"
               placeholder="e.g., Breakfast, Lunch"
               value="${data.meals || ''}">
      </div>

      <div class="col-md-6">
        <label class="form-label small text-muted">Accommodation</label>
        <input type="text"
               class="form-control accommodation"
               placeholder="e.g., Hotel XYZ"
               value="${data.accommodation || ''}">
      </div>

    </div>
  `;

  document.getElementById('itineraryContainer').appendChild(card);
}

function removeItineraryDay(btn) {
  btn.closest('.itinerary-day').remove();
  renumberItineraryDays();
}

function renumberItineraryDays() {
  document.querySelectorAll('#itineraryContainer .itinerary-day').forEach((c, i) => {
    c.querySelector('.badge').innerText = 'Day ' + (i + 1);
  });
}

function prepareItinerarySave() {

  const days = [];

  document.querySelectorAll('#itineraryContainer .itinerary-day').forEach((c, i) => {
    days.push({
      day: i + 1,
      title: c.querySelector('.title').value,
      description: c.querySelector('.description').value,
      meals: c.querySelector('.meals').value,
      accommodation: c.querySelector('.accommodation').value
    });
  });

  document.querySelector('[name="itinerary"]').value = JSON.stringify(days);
  saveTab('itinerary');
}

/* INIT EXISTING DATA */
if (Array.isArray(itineraryData) && itineraryData.length) {
  itineraryData.forEach(d => addItineraryDay(d));
}
</script>


<!-- ================= PRICING ================= -->
<div class="tab-content-section d-none" id="tab-pricing">
  <div class="card shadow-sm">
    <div class="card-body">

      <form id="pricingForm">
        <input type="hidden" name="id" value="<?= $package_id ?>">

        <!-- ================= MARKUP & PRICING ================= -->
        <div class="card mb-4 border">
          <div class="card-body">

            <h5 class="mb-4 d-flex align-items-center gap-2">
              <i class="fa fa-calculator text-success"></i>
              Markup & Pricing
            </h5>

            <div class="row g-3">

              <input type="hidden" id="totalComponentCost" name="total_cost" value="0">
              <input type="hidden" name="sell_price" value="0">

              <div class="col-md-4">
                <label class="form-label">Markup Type</label>
                <select name="markup_type" class="form-select">
                  <option value="percentage"
                    <?= ($pricing['markup_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>
                    Percentage (%)
                  </option>
                  <option value="fixed"
                    <?= ($pricing['markup_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>
                    Fixed Amount
                  </option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">
                  Markup Value
                  <span class="text-muted small">(%, if percentage)</span>
                </label>
                <input type="number"
                       step="0.01"
                       name="markup_value"
                       class="form-control"
                       value="<?= $pricing['markup_value'] ?? 0 ?>">
              </div>

              <div class="col-md-4">
                <label class="form-label">Currency</label>
                <select name="currency" class="form-select">
                  <?php
                  foreach (['USD','EUR','GBP','AED','INR','SGD','AUD','JPY','CNY','THB'] as $cur):
                  ?>
                    <option value="<?= $cur ?>"
                      <?= ($pricing['currency'] ?? 'GBP') === $cur ? 'selected' : '' ?>>
                      <?= $cur ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

            </div>

            <!-- PRICE SUMMARY -->
            <div class="mt-4 p-4 rounded bg-light border">
              <div class="row text-center">
                <div class="col-md-6">
                  <small class="text-muted">Total Cost</small>
                  <div class="fs-4 fw-bold" id="uiTotalCost">—</div>
                </div>
                <div class="col-md-6">
                  <small class="text-muted">Sell Price</small>
                  <div class="fs-4 fw-bold text-success" id="uiSellPrice">—</div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- ================= AGENT COMMISSION ================= -->
        <div class="card mb-4 border">
          <div class="card-body">

            <h5 class="mb-4">Agent Commission</h5>

            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label">Commission Type</label>
                <select name="commission_type" class="form-select">
                  <option value="percentage"
                    <?= ($pricing['commission_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>
                    Percentage (%)
                  </option>
                  <option value="fixed"
                    <?= ($pricing['commission_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>
                    Fixed Amount
                  </option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">
                  Commission Value
                  <span class="text-muted small">(%, if percentage)</span>
                </label>
                <input type="number"
                       step="0.01"
                       name="commission_value"
                       class="form-control"
                       value="<?= $pricing['commission_value'] ?? 0 ?>">
              </div>

            </div>

          </div>
        </div>

        <!-- ================= VALIDITY PERIOD ================= -->
        <div class="card border">
          <div class="card-body">

            <h5 class="mb-4">Validity Period</h5>

            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label">Valid From</label>
                <input type="date"
                       name="valid_from"
                       class="form-control"
                       value="<?= $pricing['valid_from'] ?? $package['valid_from'] ?? '' ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label">Valid To</label>
                <input type="date"
                       name="valid_to"
                       class="form-control"
                       value="<?= $pricing['valid_to'] ?? $package['valid_to'] ?? '' ?>">
              </div>

            </div>

          </div>
        </div>

        <!-- ACTION -->
        <div class="text-end mt-4">
          <button type="button" class="btn btn-primary" onclick="saveTab('pricing')">
            <i class="fa fa-save me-1"></i> Save Pricing
          </button>
        </div>

      </form>

    </div>
  </div>
</div>
<script>
document.addEventListener('input', function (e) {
  if (
    e.target.matches('[name="markup_type"], [name="markup_value"], [name="currency"]') ||
    e.target.classList.contains('component-cost') ||
    e.target.classList.contains('component-qty')
  ) {
    calculateSellPrice();
  }
});

/* Initial calculation on load */
document.addEventListener('DOMContentLoaded', calculateSellPrice);
</script>



<!-- ================= MEDIA ================= -->
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
    </style>

    <div class="tab-content-section d-none" id="tab-media">
      <div class="card shadow-sm">
        <div class="card-body">

          <h5 class="mb-3">Media</h5>

          <form id="mediaForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $package_id ?>">

            <div class="mb-3">
              <label class="form-label fw-semibold">Upload Images / Documents</label>
              <input type="file"
                     name="media[]"
                     id="mediaInput"
                     class="form-control"
                     accept="image/*"
                     multiple>
            </div>

            <!-- PREVIEW -->
            <div class="row g-2 mb-3" id="mediaPreview"></div>

            <div class="text-end">
              <button type="button"
                      class="btn btn-primary"
                      onclick="saveTab('media')">
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

      /* PREVIEW SELECTED FILES */
      mediaInput.addEventListener('change', () => {
        previewBox.innerHTML = '';
        [...mediaInput.files].forEach(file => {

          const col = document.createElement('div');
          col.className = 'col-3';

          if (file.type.startsWith('image')) {
            col.innerHTML = `
              <img src="${URL.createObjectURL(file)}"
                   class="img-fluid rounded border"
                   style="height:120px;object-fit:cover;">
            `;
          } else {
            col.innerHTML = `
              <div class="border rounded p-3 text-center small">
                <i class="fa fa-file fa-2x mb-1"></i><br>${file.name}
              </div>`;
          }

          previewBox.appendChild(col);
        });
      });

      /* LOAD EXISTING MEDIA */
      function loadMedia() {
        fetch('public/ajax/packages_media.php?action=list&id=<?= $package_id ?>')
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
                      : `<div class="p-3 text-center">
                           <i class="fa fa-file fa-2x"></i><br>${f.name}
                         </div>`}
                    <button class="btn btn-sm btn-danger remove-btn"
                            onclick="deleteMedia('${f.name}')">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </div>
              `;
            });
          })
          .catch(err => {
            console.error('Media load error:', err);
            uploadedBox.innerHTML = '<p class="text-danger">Failed to load media</p>';
          });
      }

      /* DELETE MEDIA */
      function deleteMedia(file) {
        if (!confirm('Delete this file?')) return;

        fetch('public/ajax/packages_media.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({
            id: '<?= $package_id ?>',
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
    const packageId = <?= (int)$package_id ?>;

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
        url: 'public/ajax/packages_recent_actions.php',
        type: 'POST',
        dataType: 'json',
        data: {
          package_id: packageId,
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
  
  <style>
  .quick-actions .action-btn {
    min-height: 78px;
  }
  .quick-actions .action-btn:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
  }
  </style>
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm rounded-4 mb-3 quick-actions">
      <div class="card-body">

        <h6 class="fw-semibold text-primary mb-3 d-flex align-items-center gap-2">
          <i class="fa fa-layer-group"></i> Quick Navigate
        </h6>

        <div class="row g-2">

          <?php foreach ($tabs as $k => $v): ?>
            <div class="col-3">
              <button
                type="button"
                class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn"
                onclick="document.getElementById('<?= $k ?>Btn').click();"
              >
                <i class="fa fa-<?= $v[1] ?> text-primary mb-1"></i>
                <span class="fw-semibold small text-nowrap">
                  <?= $v[0] ?>
                </span>
              </button>
            </div>
          <?php endforeach; ?>

        </div>

      </div>
    </div>

    <style>
      .package-timeline {
        position: relative;
        padding-left: 22px;
        border-left: 2px solid #0d6efd33;
      }
      .package-timeline-date {
        font-size: .75rem;
        font-weight: 700;
        color: #0d6efd;
        margin: 10px 0 6px 4px;
      }
      .package-timeline-item {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
      }
      .package-timeline-bullet {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #f1f5ff;
        border: 2px solid #d1e2ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .75rem;
      }
      .timeline-success { background:#e7f9ee; border-color:#b8ecc6; }
      .timeline-warning { background:#fff6dd; border-color:#ffe8b0; }
      .timeline-danger  { background:#fde2e4; border-color:#f8b6b9; }
      .timeline-info    { background:#e0f3ff; border-color:#b4e2fc; }

      .package-timeline-text {
        font-size: .9rem;
        color: #444;
      }
      .package-timeline-time {
        font-size: .75rem;
        color: #6c757d;
      }
    </style>
    <div class="card border-0 shadow-sm rounded-4 mb-3">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Recent Actions</strong>
        <button class="btn btn-sm btn-outline-secondary" id="btn-refresh-actions">
          <i class="fa fa-refresh"></i>
        </button>
      </div>

      <div class="card-body p-3" style="max-height:420px; overflow-y:auto;">
        <div id="actions-container">
          <div class="text-muted text-center py-3">
            <i class="fa fa-spinner fa-spin"></i> Loading activity...
          </div>
        </div>
      </div>

      <div class="card-footer text-center d-none" id="actionsLoadMoreWrapper">
        <button class="btn btn-outline-primary btn-sm rounded-pill" id="btn-load-more-actions">
          <i class="fa fa-chevron-down"></i> Load More
        </button>
      </div>
    </div>
    <script>
      let actionsStart = 0;
      const actionsLimit = 15;
      let actionsEnded = false;
      let actionsLoading = false;

      function renderActions(logs) {
        let html = '';
        let lastDate = '';

        logs.forEach(item => {

          const dateObj = new Date(item.date.replace(/(\d{2}) (\w{3}) (\d{4})/, '$2 $1 $3'));
          let dateLabel = dateObj.toDateString();

          const today = new Date();
          const yesterday = new Date();
          yesterday.setDate(today.getDate() - 1);

          if (dateObj.toDateString() === today.toDateString()) dateLabel = 'Today';
          else if (dateObj.toDateString() === yesterday.toDateString()) dateLabel = 'Yesterday';

          if (dateLabel !== lastDate) {
            html += `<div class="package-timeline-date">${dateLabel}</div>`;
            lastDate = dateLabel;
          }

          let icon = 'fa-info-circle';
          let cls  = 'timeline-info';
          const txt = item.action.toLowerCase();

          if (txt.includes('delete')) { icon='fa-trash'; cls='timeline-danger'; }
          else if (txt.includes('update')) icon='fa-edit';
          else if (txt.includes('add') || txt.includes('create')) icon='fa-plus';
          else if (txt.includes('price') || txt.includes('payment')) { icon='fa-wallet'; cls='timeline-success'; }

          html += `
            <div class="package-timeline-item">
              <div class="package-timeline-bullet ${cls}">
                <i class="fa ${icon}"></i>
              </div>
              <div>
                <div class="package-timeline-text">${item.action}</div>
                <div class="package-timeline-time">${item.date} • ${item.by || 'System'}</div>
              </div>
            </div>
          `;
        });

        return html;
      }

      function loadActions(reset = false) {
        if (actionsLoading || (actionsEnded && !reset)) return;
        actionsLoading = true;

        if (reset) {
          actionsStart = 0;
          actionsEnded = false;
          $('#actions-container').html('<div class="package-timeline"></div>');
          $('#actionsLoadMoreWrapper').addClass('d-none');
        }

        $.ajax({
          url: 'public/ajax/packages_recent_actions.php',
          type: 'POST',
          dataType: 'json',
          data: {
            package_id: packageId,
            type: 'timeline',
            start: 0,
            length: 5
          },
          success(res) {
            const logs = res.data || [];

            if (!logs.length) {
              if (!actionsStart) {
                $('#actions-container').html('<div class="text-muted text-center py-3">No activity found</div>');
              }
              actionsEnded = true;
              $('#actionsLoadMoreWrapper').addClass('d-none');
              actionsLoading = false;
              return;
            }

            $('#actions-container .package-timeline')
              .append(renderActions(logs));

            actionsStart += logs.length;

            logs.length < actionsLimit
              ? $('#actionsLoadMoreWrapper').addClass('d-none')
              : $('#actionsLoadMoreWrapper').removeClass('d-none');

            actionsLoading = false;
          },
          error() {
            $('#actions-container').html('<div class="text-danger text-center py-3">Failed to load activity</div>');
            actionsLoading = false;
          }
        });
      }

      $('#btn-load-more-actions').on('click', () => loadActions());
      $('#btn-refresh-actions').on('click', () => loadActions(true));

      loadActions(true);
    </script>


  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- ================= SCRIPTS ================= -->
<script>
$('.tab-btn').on('click', function(){
  $('.tab-btn').removeClass('active');
  $(this).addClass('active');
  $('.tab-content-section').addClass('d-none');
  $('#tab-' + $(this).data('tab')).removeClass('d-none');
});
</script>

<script>
function chipInput(boxId, data = []) {
  const box = document.getElementById(boxId);
  const input = box.querySelector('input');
  let items = [...data];

  function render() {
    box.querySelectorAll('.chip').forEach(c => c.remove());
    items.forEach((t,i)=>{
      const s = document.createElement('span');
      s.className='badge bg-primary chip';
      s.innerHTML = t+' <i class="fa fa-times ms-1"></i>';
      s.onclick=()=>{items.splice(i,1);render();};
      box.insertBefore(s,input);
    });
  }

  input.onkeydown=e=>{
    if(e.key==='Enter' && input.value.trim()){
      e.preventDefault();
      items.push(input.value.trim());
      input.value='';
      render();
    }
  };
  render();
  return ()=>items;
}

// const getComponents = chipInput('componentsBox', <?= json_encode($components) ?>);
// const getItinerary  = chipInput('itineraryBox', <?= json_encode($itinerary) ?>);

function prepareAndSave(tab){
  document.querySelector(`[name="${tab}"]`).value =
    JSON.stringify(tab==='components'?getComponents():getItinerary());
  saveTab(tab);
}

function saveTab(tab){
  const form = document.getElementById(tab+'Form');
  const fd = new FormData(form);
  fd.append('tab', tab);

  fetch('public/ajax/packages_save.php', {method:'POST', body:fd})
    .then(r=>r.json())
    .then(res=>{
      if(res.success) {
        alert('Saved successfully');
        window.location.reload();
      }
      else alert(res.error || 'Save failed');
    });
}
</script>