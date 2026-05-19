<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

/* ------------------------------------
   ADD ZONE
------------------------------------ */
if (isset($_POST['add_zone'])) {

    $zone_name = trim($_POST['zone_name']);
    $country   = trim($_POST['country']);
    $region    = trim($_POST['region']);

    if ($zone_name !== "" && $country !== "" && $region !== "") {

        $stmt = $mysqli->prepare("
            INSERT INTO zones (zone_name, country, region)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sss", $zone_name, $country, $region);
        $stmt->execute();
        $stmt->close();

        $site->agent_log("Zone [$zone_name] created");
    }

    header("Location: index.php?page=zones");
    exit;
}

/* ------------------------------------
   EDIT ZONE
------------------------------------ */
if (isset($_POST['edit_zone'])) {

    $id        = intval($_POST['id']);
    $zone_name = trim($_POST['zone_name']);
    $country   = trim($_POST['country']);
    $region    = trim($_POST['region']);

    if ($zone_name !== "" && $country !== "" && $region !== "") {

        $stmt = $mysqli->prepare("
            UPDATE zones 
            SET zone_name=?, country=?, region=?
            WHERE id=?
        ");
        $stmt->bind_param("sssi", $zone_name, $country, $region, $id);
        $stmt->execute();
        $stmt->close();

        $site->agent_log("Zone [$zone_name] updated");
    }

    header("Location: index.php?page=zones");
    exit;
}

/* ------------------------------------
   DELETE ZONE
------------------------------------ */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);
    $mysqli->query("DELETE FROM zones WHERE id=$id");

    $site->agent_log("Zone [$id] deleted");

    header("Location: index.php?page=zones");
    exit;
}

/* ------------------------------------
   FETCH ALL
------------------------------------ */
$result = $mysqli->query("SELECT * FROM zones ORDER BY id DESC");

require_once __DIR__ . '/includes/header.php';
?>

<!-- HEADER BAR -->
<div class="card mb-0">
  <div class="card-body py-2 d-flex justify-content-between align-items-center">
    <h5 class="m-0">Zones</h5>

    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addModal">
      + Add Zone
    </button>
  </div>
</div>

<!-- MAIN CARD -->
<div class="card shadow-sm mt-0">
  <div class="card-body">

    <div class="table-responsive">
      <table id="zonesTable" class="table table-striped table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Zone Name</th>
            <th>Country</th>
            <th>Region</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>

        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['zone_name']) ?></td>
            <td><?= htmlspecialchars($row['country']) ?></td>
            <td><?= htmlspecialchars($row['region']) ?></td>

            <td class="text-center">
              <button class="btn btn-sm btn-outline-secondary me-1"
                onclick="openEditModal(
                    <?= $row['id'] ?>,
                    '<?= htmlspecialchars($row['zone_name'], ENT_QUOTES) ?>',
                    '<?= htmlspecialchars($row['country'], ENT_QUOTES) ?>',
                    '<?= htmlspecialchars($row['region'], ENT_QUOTES) ?>'
                )">
                <i class="fas fa-edit"></i>
              </button>

              <a href="?page=zones&delete=<?= $row['id'] ?>"
                 onclick="return confirm('Delete this zone?')"
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
          <h5 class="modal-title">Add Zone</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <label class="form-label">Zone Name</label>
          <input type="text" name="zone_name" class="form-control" required>

          <label class="form-label mt-3">Country</label>
          <select name="country" class="form-select" required>
            <option value="">Select Country</option>
            <option value="India">India</option>
            <option value="United Kingdom">United Kingdom</option>
            <option value="United Arab Emirates">United Arab Emirates</option>
            <option value="Qatar">Qatar</option>
            <option value="Saudi Arabia">Saudi Arabia</option>
            <option value="United States">United States</option>
            <option value="Canada">Canada</option>
            <option value="Australia">Australia</option>
            <option value="New Zealand">New Zealand</option>
            <option value="France">France</option>
            <option value="Germany">Germany</option>
            <option value="Italy">Italy</option>
            <option value="Spain">Spain</option>
            <option value="Thailand">Thailand</option>
            <option value="Malaysia">Malaysia</option>
            <option value="Singapore">Singapore</option>
          </select>

          <label class="form-label mt-3">Region</label>
          <input type="text" name="region" class="form-control" required>

        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-dark" name="add_zone">Save</button>
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
          <h5 class="modal-title">Edit Zone</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <label class="form-label">Zone Name</label>
          <input type="text" id="edit_zone_name" name="zone_name" class="form-control" required>

          <label class="form-label mt-3">Country</label>
          <select id="edit_country" name="country" class="form-select" required>
            <option value="">Select Country</option>
            <option value="India">India</option>
            <option value="United Kingdom">United Kingdom</option>
            <option value="United Arab Emirates">United Arab Emirates</option>
            <option value="Qatar">Qatar</option>
            <option value="Saudi Arabia">Saudi Arabia</option>
            <option value="United States">United States</option>
            <option value="Canada">Canada</option>
            <option value="Australia">Australia</option>
            <option value="New Zealand">New Zealand</option>
            <option value="France">France</option>
            <option value="Germany">Germany</option>
            <option value="Italy">Italy</option>
            <option value="Spain">Spain</option>
            <option value="Thailand">Thailand</option>
            <option value="Malaysia">Malaysia</option>
            <option value="Singapore">Singapore</option>
          </select>

          <label class="form-label mt-3">Region</label>
          <input type="text" id="edit_region" name="region" class="form-control" required>

        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-dark" name="edit_zone">Update</button>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
function openEditModal(id, zone, country, region) {

    document.getElementById("edit_id").value        = id;
    document.getElementById("edit_zone_name").value = zone;
    document.getElementById("edit_country").value   = country;
    document.getElementById("edit_region").value    = region;

    new bootstrap.Modal(document.getElementById("editModal")).show();
}
</script>

<script>
$(document).ready(function(){
  $('#zonesTable').DataTable({
    pageLength: 10,
    ordering: true,
    searching: true,
    language: { 
        search: "_INPUT_", 
        searchPlaceholder: "Search zones..." 
    }
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>