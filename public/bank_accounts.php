<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

/* ------------------------------------
   ADD ACCOUNT
------------------------------------ */
if (isset($_POST['add_account'])) {
    $bank = trim($_POST['bank_name']);
    $number = trim($_POST['account_number']);
    $branch = trim($_POST['branch_name']);
    $branch_id = trim($_POST['branch_id']);

    if ($bank !== "" && $number !== "") {
        $stmt = $mysqli->prepare("
            INSERT INTO bank_accounts (bank_name, account_number, branch_name, branch_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $bank, $number, $branch, $branch_id);
        $stmt->execute();
        $stmt->close();

        $site->agent_log("Bank Account $bank with $number is created");
    }
    header("Location: index.php?page=bank_accounts");
    exit;
}

/* ------------------------------------
   EDIT ACCOUNT
------------------------------------ */
if (isset($_POST['edit_account'])) {
    $id = intval($_POST['id']);
    $bank = trim($_POST['bank_name']);
    $number = trim($_POST['account_number']);
    $branch = trim($_POST['branch_name']);
    $branch_id = trim($_POST['branch_id']);

    if ($bank !== "" && $number !== "") {
        $stmt = $mysqli->prepare("
            UPDATE bank_accounts 
            SET bank_name=?, account_number=?, branch_name=?, branch_id=?
            WHERE id=?
        ");
        $stmt->bind_param("ssssi", $bank, $number, $branch, $branch_id, $id);
        $stmt->execute();
        $stmt->close();

        $site->agent_log("Bank Account $bank is updated");
    }
    header("Location: index.php?page=bank_accounts");
    exit;
}

/* ------------------------------------
   DELETE ACCOUNT
------------------------------------ */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $mysqli->query("DELETE FROM bank_accounts WHERE id=$id");
    $site->agent_log("Bank Account[$id] is deleted");
    header("Location: index.php?page=bank_accounts");
    exit;
}

/* ------------------------------------
   FETCH ALL
------------------------------------ */
$result = $mysqli->query("SELECT * FROM bank_accounts ORDER BY id DESC");

require_once __DIR__ . '/includes/header.php';
?>

<!-- HEADER BAR -->
<div class="card mb-0">
  <div class="card-body py-2 d-flex justify-content-between align-items-center">
    <h5 class="m-0">Bank Accounts</h5>

    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addModal">
      + Add Account
    </button>
  </div>
</div>

<!-- MAIN CARD -->
<div class="card shadow-sm mt-0">
  <div class="card-body">

    <div class="table-responsive">
      <table id="accountsTable" class="table table-striped table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Bank Name</th>
            <th>Account Number</th>
            <th>Branch</th>
            <th>Branch ID</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>

        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['bank_name']) ?></td>
            <td><?= htmlspecialchars($row['account_number']) ?></td>
            <td><?= htmlspecialchars($row['branch_name']) ?></td>
            <td><?= htmlspecialchars($row['branch_id']) ?></td>

            <td class="text-center">
              <button class="btn btn-sm btn-outline-secondary me-1"
                onclick="openEditModal(
                    <?= $row['id'] ?>,
                    '<?= htmlspecialchars($row['bank_name'], ENT_QUOTES) ?>',
                    '<?= htmlspecialchars($row['account_number'], ENT_QUOTES) ?>',
                    '<?= htmlspecialchars($row['branch_name'], ENT_QUOTES) ?>',
                    '<?= htmlspecialchars($row['branch_id'], ENT_QUOTES) ?>'
                )">
                <i class="fas fa-edit"></i>
              </button>

              <a href="?page=bank_accounts&delete=<?= $row['id'] ?>"
                 onclick="return confirm('Delete this account?')"
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

<!-- ADD MODAL -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 p-2">

      <form method="POST">

        <div class="modal-header border-0">
          <h5 class="modal-title">Add Bank Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <label class="form-label">Bank Name</label>
          <input type="text" name="bank_name" class="form-control" required>

          <label class="form-label mt-3">Account Number</label>
          <input type="text" name="account_number" class="form-control" required>

          <label class="form-label mt-3">Branch Name</label>
          <input type="text" name="branch_name" class="form-control">

          <label class="form-label mt-3">Branch ID (Swift/IFSC/UAE Code)</label>
          <input type="text" name="branch_id" class="form-control">

        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-dark" name="add_account">Save</button>
        </div>

      </form>

    </div>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 p-2">

      <form method="POST">
        <input type="hidden" id="edit_id" name="id">

        <div class="modal-header border-0">
          <h5 class="modal-title">Edit Bank Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <label class="form-label">Bank Name</label>
          <input type="text" id="edit_bank_name" name="bank_name" class="form-control" required>

          <label class="form-label mt-3">Account Number</label>
          <input type="text" id="edit_account_number" name="account_number" class="form-control" required>

          <label class="form-label mt-3">Branch Name</label>
          <input type="text" id="edit_branch_name" name="branch_name" class="form-control">

          <label class="form-label mt-3">Branch ID</label>
          <input type="text" id="edit_branch_id" name="branch_id" class="form-control">

        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-dark" name="edit_account">Update</button>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
function openEditModal(id, bank, number, branch, branch_id) {

    document.getElementById("edit_id").value = id;

    document.getElementById("edit_bank_name").value = bank;
    document.getElementById("edit_account_number").value = number;
    document.getElementById("edit_branch_name").value = branch;
    document.getElementById("edit_branch_id").value = branch_id;

    new bootstrap.Modal(document.getElementById("editModal")).show();
}
</script>

<script>
$(document).ready(function(){
  $('#accountsTable').DataTable({
    pageLength: 10,
    ordering: true,
    searching: true,
    language: { 
        search: "_INPUT_", 
        searchPlaceholder: "Search bank accounts..." 
    }
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>