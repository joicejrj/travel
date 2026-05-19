<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

/* ------------------------------------
   ADD JOB TITLE
------------------------------------ */
if (isset($_POST['add_job'])) {
    $title = trim($_POST['title']);
    if ($title !== "") {
        $stmt = $mysqli->prepare("INSERT INTO job_titles (title) VALUES (?)");
        $stmt->bind_param("s", $title);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?page=job_titles");
    exit;
}

/* ------------------------------------
   EDIT JOB TITLE
------------------------------------ */
if (isset($_POST['edit_job'])) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    if ($title !== "") {
        $stmt = $mysqli->prepare("UPDATE job_titles SET title=? WHERE id=?");
        $stmt->bind_param("si", $title, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?page=job_titles");
    exit;
}

/* ------------------------------------
   DELETE JOB TITLE
------------------------------------ */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $mysqli->query("DELETE FROM job_titles WHERE id=$id");
    header("Location: index.php?page=job_titles");
    exit;
}

/* ------------------------------------
   FETCH ALL TITLES
------------------------------------ */
$result = $mysqli->query("SELECT * FROM job_titles ORDER BY id DESC");

require_once __DIR__ . '/includes/header.php';
?>

<!-- HEADER BAR -->
<div class="card mb-0">
  <div class="card-body py-2 d-flex justify-content-between align-items-center">
    <h5 class="m-0">Job Titles</h5>

    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addModal">
      + Add Job Title
    </button>
  </div>
</div>

<!-- MAIN CARD -->
<div class="card shadow-sm mt-0">
  <div class="card-body">

    <div class="table-responsive">
      <table id="jobTitlesTable" class="table table-striped table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th width="80">ID</th>
            <th>Title</th>
            <th width="120" class="text-center">Actions</th>
          </tr>
        </thead>

        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td class="text-center">

              <button class="btn btn-sm btn-outline-secondary me-1"
                      onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>')">
                <i class="fas fa-edit"></i>
              </button>

              <a href="?page=job_titles&delete=<?= $row['id'] ?>"
                 onclick="return confirm('Delete this job title?')"
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
          <h5 class="modal-title">Add Job Title</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <label class="form-label">Job Title</label>
          <input type="text" name="title" class="form-control" required>
        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-dark" name="add_job">Save</button>
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
          <h5 class="modal-title">Edit Job Title</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <label class="form-label">Job Title</label>
          <input type="text" id="edit_title" name="title" class="form-control" required>
        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-dark" name="edit_job">Update</button>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
function openEditModal(id, title) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_title").value = title;
    new bootstrap.Modal(document.getElementById("editModal")).show();
}
</script>

<!-- DATATABLE INIT -->
<script>
$(document).ready(function(){
  $('#jobTitlesTable').DataTable({
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],
    ordering: true,
    searching: true,
    language: { 
        search: "_INPUT_", 
        searchPlaceholder: "Search titles..." 
    }
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>