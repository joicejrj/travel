<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
$now = date("Y-m-d H:i:s");

/* ------------------------------------
   ADD DOCUMENT LABEL
------------------------------------ */
if (isset($_POST['add_label'])) {
    $label = trim($_POST['label']);
    if ($label !== "") {
        $stmt = $mysqli->prepare("INSERT INTO document_labels (label, created_at) VALUES (?, ?)");
        $stmt->bind_param("ss", $label, $now);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?page=document_labels");
    exit;
}

/* ------------------------------------
   EDIT DOCUMENT LABEL
------------------------------------ */
if (isset($_POST['edit_label'])) {
    $id = intval($_POST['id']);
    $label = trim($_POST['label']);
    if ($label !== "") {
        $stmt = $mysqli->prepare("UPDATE document_labels SET label=? WHERE id=?");
        $stmt->bind_param("si", $label, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?page=document_labels");
    exit;
}

/* ------------------------------------
   DELETE DOCUMENT LABEL
------------------------------------ */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $mysqli->query("DELETE FROM document_labels WHERE id=$id");
    header("Location: index.php?page=document_labels");
    exit;
}

/* ------------------------------------
   FETCH ALL LABELS
------------------------------------ */
$result = $mysqli->query("SELECT * FROM document_labels ORDER BY id DESC");

require_once __DIR__ . '/includes/header.php';
?>

<!-- HEADER BAR -->
<div class="card mb-0">
  <div class="card-body py-2 d-flex justify-content-between align-items-center">
    <h5 class="m-0">Document Labels</h5>

    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addModal">
      + Add Label
    </button>
  </div>
</div>

<!-- MAIN CARD -->
<div class="card shadow-sm mt-0">
  <div class="card-body">

    <div class="table-responsive">
      <table id="labelsTable" class="table table-striped table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th width="80">ID</th>
            <th>Label</th>
            <th width="160">Created At</th>
            <!-- <th width="120" class="text-center">Actions</th> -->
          </tr>
        </thead>

        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['label']) ?></td>
            <td><?= $row['created_at'] ?></td>

            <!-- <td class="text-center">

              <button class="btn btn-sm btn-outline-secondary me-1"
                      onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['label'], ENT_QUOTES) ?>')">
                <i class="fas fa-edit"></i>
              </button>

              <a href="?page=document_labels&delete=<?= $row['id'] ?>"
                 onclick="return confirm('Delete this label?')"
                 class="btn btn-sm btn-outline-danger">
                <i class="fas fa-trash-alt"></i>
              </a>

            </td> -->
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
          <h5 class="modal-title">Add Document Label</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <label class="form-label">Label</label>
          <input type="text" name="label" class="form-control" required>
        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-dark" name="add_label">Save</button>
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
          <h5 class="modal-title">Edit Document Label</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <label class="form-label">Label</label>
          <input type="text" id="edit_label" name="label" class="form-control" required>
        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-dark" name="edit_label">Update</button>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
function openEditModal(id, label) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_label").value = label;
    new bootstrap.Modal(document.getElementById("editModal")).show();
}
</script>

<!-- DATATABLE INIT -->
<script>
$(document).ready(function(){
  $('#labelsTable').DataTable({
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],
    ordering: true,
    searching: true,
    language: { 
        search: "_INPUT_", 
        searchPlaceholder: "Search labels..." 
    }
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>