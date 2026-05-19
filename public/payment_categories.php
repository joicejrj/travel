<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

/* ------------------------------------
   ADD CATEGORY
------------------------------------ */
if (isset($_POST['add_cat'])) {
    $type = $_POST['type'];
    $category = trim($_POST['category']);

    if ($category !== "") {
        $stmt = $mysqli->prepare("INSERT INTO payment_categories (type, category, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $type, $category);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?page=payment_categories");
    exit;
}

/* ------------------------------------
   EDIT CATEGORY
------------------------------------ */
if (isset($_POST['edit_cat'])) {
    $id = intval($_POST['id']);
    $type = $_POST['type'];
    $category = trim($_POST['category']);

    if ($category !== "") {
        $stmt = $mysqli->prepare("UPDATE payment_categories 
                                  SET type = ?, category = ?, updated_at = NOW() 
                                  WHERE id = ?");
        $stmt->bind_param("ssi", $type, $category, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?page=payment_categories");
    exit;
}

/* ------------------------------------
   DELETE CATEGORY
------------------------------------ */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $mysqli->query("DELETE FROM payment_categories WHERE id=$id");
    header("Location: index.php?page=payment_categories");
    exit;
}

/* ------------------------------------
   FETCH ALL CATEGORIES
------------------------------------ */
$result = $mysqli->query("SELECT * FROM payment_categories ORDER BY id DESC");

require_once __DIR__ . '/includes/header.php';
?>

<!-- HEADER BAR -->
<div class="card mb-0">
  <div class="card-body py-2 d-flex justify-content-between align-items-center">
    <h5 class="m-0">Company Payment Categories</h5>

    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addModal">
      + Add Category
    </button>
  </div>
</div>

<!-- MAIN CARD -->
<div class="card shadow-sm mt-0">
  <div class="card-body">

    <div class="table-responsive">
      <table id="paymentCatsTable" class="table table-striped table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th width="70">ID</th>
            <th>Type</th>
            <th>Category</th>
            <th width="120" class="text-center">Actions</th>
          </tr>
        </thead>

        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>

            <td class="text-center">

              <button class="btn btn-sm btn-outline-secondary me-1"
                      onclick="openEditModal(
                        <?= $row['id'] ?>,
                        '<?= htmlspecialchars($row['type'], ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($row['category'], ENT_QUOTES) ?>'
                      )">
                <i class="fas fa-edit"></i>
              </button>

              <a href="?page=payment_categories&delete=<?= $row['id'] ?>"
                 onclick="return confirm('Delete this category?')"
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
          <h5 class="modal-title">Add Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <label class="form-label">Type</label>
          <select name="type" class="form-select" required>
            <option value="Income">Income</option>
            <option value="Expense">Expense</option>
          </select>

          <label class="form-label mt-3">Category</label>
          <input type="text" name="category" class="form-control" required>
        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-dark" name="add_cat">Save</button>
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
          <h5 class="modal-title">Edit Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <label class="form-label">Type</label>
          <select id="edit_type" name="type" class="form-select" required>
            <option value="Income">Income</option>
            <option value="Expense">Expense</option>
          </select>

          <label class="form-label mt-3">Category</label>
          <input type="text" id="edit_category" name="category" class="form-control" required>

        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-dark" name="edit_cat">Update</button>
        </div>

      </form>

    </div>
  </div>
</div>


<script>
function openEditModal(id, type, category) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_type").value = type;
    document.getElementById("edit_category").value = category;

    new bootstrap.Modal(document.getElementById("editModal")).show();
}
</script>

<!-- DATATABLE INIT -->
<script>
$(document).ready(function(){
  $('#paymentCatsTable').DataTable({
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],
    ordering: true,
    searching: true,
    language: { 
        search: "_INPUT_", 
        searchPlaceholder: "Search categories..." 
    }
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>