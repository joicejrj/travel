<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// var_dump($_POST);

/* ------------------------------------
   ADD DISCOUNT
------------------------------------ */
if (isset($_POST['add_discount'])) {

    $discount_name  = trim($_POST['discount_name']);
    $discount_code  = trim($_POST['discount_code']);
    $description    = trim($_POST['description']);

    $discount_type  = $_POST['discount_type'];
    $discount_value = floatval($_POST['discount_value']);
    $max_discount   = $_POST['max_discount_amount'] !== "" ? floatval($_POST['max_discount_amount']) : null;

    $scope_type     = $_POST['scope_type'];
    $min_amount     = $_POST['min_amount'] !== "" ? floatval($_POST['min_amount']) : null;

    $valid_from     = $_POST['valid_from'] ?: null;
    $valid_to       = $_POST['valid_to'] ?: null;

    $expiry_date    = $_POST['expiry_date'];
    $status         = $_POST['status'];

    /* ------------------------------------
       Scope validation
    ------------------------------------ */
    if ($scope_type === 'date_range') {

        if (!$valid_from || !$valid_to) {
            die('Date range required for date_range scope');
        }
        $min_amount = null;

    }
    elseif ($scope_type === 'amount_based') {

        if (!$min_amount || $min_amount <= 0) {
            die('Minimum amount required for amount_based scope');
        }
        $valid_from = $valid_to = null;

    }
    elseif ($scope_type === 'date_and_amount') {

        if (
            !$valid_from || !$valid_to ||
            !$min_amount || $min_amount <= 0
        ) {
            die('Date range and minimum amount required');
        }

    }
    else {
        die('Invalid scope type');
    }

    $stmt = $mysqli->prepare("
        INSERT INTO discounts
        (discount_name, discount_code, description,
         discount_type, discount_value, max_discount_amount,
         scope_type, min_amount, valid_from, valid_to,
         expiry_date, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssddsdssss",
        $discount_name, $discount_code, $description,
        $discount_type, $discount_value, $max_discount,
        $scope_type, $min_amount, $valid_from, $valid_to,
        $expiry_date, $status
    );

    $stmt->execute();
    $stmt->close();

    $site->agent_log("Discount [$discount_name] created");

    header("Location: index.php?page=discounts");
    exit;
}

/* ------------------------------------
   EDIT DISCOUNT
------------------------------------ */
if (isset($_POST['edit_discount'])) {

    $id              = intval($_POST['id']);
    $discount_name   = trim($_POST['discount_name']);
    $description     = trim($_POST['description']);

    $discount_value  = floatval($_POST['discount_value']);
    $max_discount    = $_POST['max_discount_amount'] !== "" ? floatval($_POST['max_discount_amount']) : null;

    $scope_type      = $_POST['scope_type'];
    $min_amount      = $_POST['min_amount'] !== "" ? floatval($_POST['min_amount']) : null;

    $valid_from      = $_POST['valid_from'] ?: null;
    $valid_to        = $_POST['valid_to'] ?: null;

    $expiry_date     = $_POST['expiry_date'];
    $status          = $_POST['status'];

    /* ------------------------------------
       Scope validation
    ------------------------------------ */
    if ($scope_type === 'date_range') {

        if (!$valid_from || !$valid_to) {
            die('Date range required for date_range scope');
        }
        $min_amount = null;

    }
    elseif ($scope_type === 'amount_based') {

        if (!$min_amount || $min_amount <= 0) {
            die('Minimum amount required for amount_based scope');
        }
        $valid_from = $valid_to = null;

    }
    elseif ($scope_type === 'date_and_amount') {

        if (
            !$valid_from || !$valid_to ||
            !$min_amount || $min_amount <= 0
        ) {
            die('Date range and minimum amount required');
        }

    }
    else {
        die('Invalid scope type');
    }

    $stmt = $mysqli->prepare("
        UPDATE discounts SET
            discount_name=?,
            description=?,
            discount_value=?,
            max_discount_amount=?,
            scope_type=?,
            min_amount=?,
            valid_from=?,
            valid_to=?,
            expiry_date=?,
            status=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "ssddsdssssi",
        $discount_name, $description,
        $discount_value, $max_discount,
        $scope_type, $min_amount,
        $valid_from, $valid_to,
        $expiry_date, $status,
        $id
    );

    $stmt->execute();
    $stmt->close();

    $site->agent_log("Discount [$discount_name] updated");

    header("Location: index.php?page=discounts");
    exit;
}

/* ------------------------------------
   DELETE DISCOUNT
------------------------------------ */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);
    $mysqli->query("DELETE FROM discounts WHERE id=$id");

    $site->agent_log("Discount [$id] deleted");

    header("Location: index.php?page=discounts");
    exit;
}

/* ------------------------------------
   FETCH ALL DISCOUNTS
------------------------------------ */
$result = $mysqli->query("
    SELECT *,
    CASE
        WHEN expiry_date < CURDATE() THEN 'expired'
        ELSE status
    END AS display_status
    FROM discounts
    ORDER BY id DESC
");

require_once __DIR__ . '/includes/header.php';
?>

<!-- HEADER BAR -->
<div class="card mb-0">
  <div class="card-body py-2 d-flex justify-content-between align-items-center">
    <h5 class="m-0">Discounts</h5>

    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#discountModal">
      + Add Discount
    </button>
  </div>
</div>

<!-- MAIN CARD -->
<div class="card shadow-sm mt-0">
  <div class="card-body">

    <div class="table-responsive">
      <table id="discountsTable" class="table table-striped table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Type</th>
            <th>Scope</th>
            <th>Value</th>
            <th>Condition</th>
            <th>Expiry</th>
            <th>Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>

        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr class="<?= $row['display_status']==='expired' ? 'table-danger' : '' ?>">
            <td>
              <strong><?= htmlspecialchars($row['discount_name']) ?></strong><br>
              <small class="text-muted"><?= htmlspecialchars($row['description']) ?></small>
            </td>

            <td><span class="badge bg-light text-dark"><?= $row['discount_code'] ?></span></td>

            <td><?= $row['discount_type']==='percentage' ? '%' : 'Amount' ?></td>

            <td><?= ucfirst(str_replace('_',' ', $row['scope_type'])) ?></td>

            <td>
              <?= $row['discount_type']==='percentage'
                ? $row['discount_value'].'%'
                : ''.$row['discount_value'] ?>
            </td>

            <td>
                <?php
                if ($row['scope_type'] === 'amount_based') {
                    echo '≥ ' . number_format($row['min_amount']);
                }
                elseif ($row['scope_type'] === 'date_range') {
                    echo date('d M', strtotime($row['valid_from'])) . ' – ' .
                         date('d M', strtotime($row['valid_to']));
                }
                elseif ($row['scope_type'] === 'date_and_amount') {
                    echo date('d M', strtotime($row['valid_from'])) . ' – ' .
                         date('d M', strtotime($row['valid_to'])) .
                         '<br><small class="text-muted">≥ ' .
                         number_format($row['min_amount']) . '</small>';
                }
                ?>
            </td>

            <td><?= date('d M Y', strtotime($row['expiry_date'])) ?></td>

            <td>
              <span class="badge
                <?= $row['display_status']==='active'?'bg-success':
                    ($row['display_status']==='expired'?'bg-danger':'bg-secondary') ?>">
                <?= ucfirst($row['display_status']) ?>
              </span>
            </td>

            <td class="text-center">
              <button class="btn btn-sm btn-outline-secondary me-1"
                onclick='openEditModal(<?= json_encode($row) ?>)'>
                <i class="fas fa-edit"></i>
              </button>

              <a href="?page=discounts&delete=<?= $row['id'] ?>"
                 onclick="return confirm('Delete this discount?')"
                 class="btn btn-sm btn-outline-danger">
                <i class="fas fa-trash-alt"></i>
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>

      </table>
    </div>

  </div>
</div>

<!-- ADD / EDIT MODAL -->
<div class="modal fade" id="discountModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 p-2">

      <form method="POST">
        <input type="hidden" name="id" id="id">

        <div class="modal-header border-0">
          <h5 class="modal-title" id="modalTitle">Add Discount</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Discount Name</label>
              <input type="text" name="discount_name" id="discount_name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Discount Code</label>
                <input type="text" name="discount_code" id="discount_code" class="form-control">
                <small id="codeHelp" class="text-danger d-none">
                  This discount code already exists
                </small>
            </div>
          </div>

          <label class="form-label mt-3">Description</label>
          <textarea name="description" id="description" class="form-control"></textarea>

          <div class="row mt-3">
            <div class="col-md-4">
              <label class="form-label">Type</label>
              <select name="discount_type" id="discount_type" class="form-select">
                <option value="percentage">Percentage</option>
                <option value="fixed">Fixed</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Value</label>
              <input type="number" step="0.01" name="discount_value" id="discount_value" class="form-control" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">Max Discount</label>
              <input type="number" step="0.01" name="max_discount_amount" id="max_discount_amount" class="form-control">
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-6">
              <label class="form-label">Scope</label>
                <select name="scope_type" id="scope_type" class="form-select">
                  <option value="date_range">Date Range Only</option>
                  <option value="amount_based">Amount Only</option>
                  <option value="date_and_amount">Date + Amount</option>
                </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Minimum Amount</label>
              <input type="number" step="0.01" name="min_amount" id="min_amount" class="form-control">
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-4">
              <label class="form-label">Valid From</label>
              <input type="date" name="valid_from" id="valid_from" class="form-control">
            </div>

            <div class="col-md-4">
              <label class="form-label">Valid To</label>
              <input type="date" name="valid_to" id="valid_to" class="form-control">
            </div>

            <div class="col-md-4">
              <label class="form-label">Expiry Date</label>
              <input type="date" name="expiry_date" id="expiry_date" class="form-control" required>
            </div>
          </div>

          <label class="form-label mt-3">Status</label>
          <select name="status" id="status" class="form-select">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>

        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-dark" name="add_discount" id="saveBtn">Save</button>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
function openEditModal(data) {

  document.getElementById('modalTitle').innerText = 'Edit Discount';
  document.getElementById('saveBtn').name = 'edit_discount';

  for (const key in data) {
    if (document.getElementById(key)) {
      document.getElementById(key).value = data[key];
    }
  }

  new bootstrap.Modal(document.getElementById('discountModal')).show();
}
</script>

<script>
$(document).ready(function(){
  $('#discountsTable').DataTable({
    pageLength: 10,
    ordering: true,
    searching: true,
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search discounts..."
    }
  });
});
</script>
<script>
/* ------------------------------------
   Discount Code Auto-generate + AJAX check
------------------------------------ */

let codeManuallyEdited = false;
let codeIsDuplicate    = false;

const nameInput = document.getElementById('discount_name');
const codeInput = document.getElementById('discount_code');
const idInput   = document.getElementById('edit_id');
const codeHelp  = document.getElementById('codeHelp');
const saveBtn   = document.getElementById('saveBtn');

/* Detect manual edits in code field */
codeInput.addEventListener('input', function () {
    codeManuallyEdited = true;
    checkDuplicateCode();
});

/* Auto-generate code from name */
nameInput.addEventListener('input', function () {

    if (codeManuallyEdited) return;

    let code = nameInput.value
        .toUpperCase()
        .replace(/[^A-Z0-9 ]/g, '')   // remove special chars
        .replace(/\s+/g, '')         // remove spaces
        .substring(0, 20);           // limit length

    codeInput.value = code;
    checkDuplicateCode();
});

/* AJAX duplicate check */
function checkDuplicateCode() {

    const code = codeInput.value.trim();
    const id   = idInput.value || 0;

    if (code === '') {
        codeHelp.classList.add('d-none');
        saveBtn.disabled = false;
        return;
    }

    fetch('public/ajax/discounts_check_code.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ code, id })
    })
    .then(res => res.json())
    .then(data => {

        if (data.exists) {
            codeIsDuplicate = true;
            codeHelp.classList.remove('d-none');
            saveBtn.disabled = true;
        } else {
            codeIsDuplicate = false;
            codeHelp.classList.add('d-none');
            saveBtn.disabled = false;
        }
    })
    .catch(() => {
        // Fail-safe: allow save if AJAX fails
        saveBtn.disabled = false;
        codeHelp.classList.add('d-none');
    });
}

/* Reset state when opening modal (Add Discount) */
document.getElementById('discountModal').addEventListener('show.bs.modal', function () {

    codeManuallyEdited = false;
    codeIsDuplicate    = false;

    if (codeHelp) codeHelp.classList.add('d-none');
    if (saveBtn)  saveBtn.disabled = false;
});
</script>
<script>
function toggleScopeFields() {

    const scope = document.getElementById('scope_type').value;

    document.getElementById('min_amount').closest('.col-md-6').style.display =
        (scope === 'amount_based' || scope === 'date_and_amount') ? '' : 'none';

    document.getElementById('valid_from').closest('.col-md-4').style.display =
    document.getElementById('valid_to').closest('.col-md-4').style.display =
        (scope === 'date_range' || scope === 'date_and_amount') ? '' : 'none';
}

document.getElementById('scope_type').addEventListener('change', toggleScopeFields);
document.getElementById('discountModal').addEventListener('shown.bs.modal', toggleScopeFields);
</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>