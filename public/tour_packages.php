<?php
require_once '_auth.php';
require_once 'config/db.php';

/* ---------------------------------
   Handle Add / Edit
---------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_package'])) {

    $id          = $_POST['id'] ?? '';
    $name        = $_POST['name'];
    $description = $_POST['description'];
    $duration    = $_POST['duration'];
    $amount      = $_POST['amount'];

    if ($id) {
        $stmt = $mysqli->prepare(
            "UPDATE tour_packages 
             SET name=?, description=?, duration=?, amount=?, updated_at=? 
             WHERE id=?"
        );
        $stmt->bind_param("sssdsi", $name, $description, $duration, $amount, date("Y-m-d H:i:s"), $id);
    } else {
        $stmt = $mysqli->prepare(
            "INSERT INTO tour_packages (name, description, duration, amount, created_at) 
             VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param("sssds", $name, $description, $duration, $amount,date("Y-m-d H:i:s"));
    }

    $stmt->execute();
    header("Location: ./?page=tour_packages");
    exit;
}

/* ---------------------------------
   Handle Delete
---------------------------------- */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM tour_packages WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: ./?page=tour_packages");
    exit;
}

/* ---------------------------------
   Fetch Packages
---------------------------------- */
$packages = $mysqli->query("SELECT * FROM tour_packages ORDER BY id DESC");

/* ---------------------------------
   Fetch Single Package (View/Edit)
---------------------------------- */
$viewPackage = $editPackage = null;

if (isset($_GET['view'])) {
    $stmt = $mysqli->prepare("SELECT * FROM tour_packages WHERE id=?");
    $stmt->bind_param("i", $_GET['view']);
    $stmt->execute();
    $viewPackage = $stmt->get_result()->fetch_assoc();
}

if (isset($_GET['edit'])) {
    $stmt = $mysqli->prepare("SELECT * FROM tour_packages WHERE id=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editPackage = $stmt->get_result()->fetch_assoc();
}

require_once 'includes/header.php';
?>

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Tour Packages</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#packageModal">
            Add Package
        </button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th width="50">#</th>
                    <th>Name</th>
                    <th>Duration</th>
                    <th>Amount</th>
                    <th width="220">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($packages->num_rows): $i=1; while ($row=$packages->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['duration']) ?></td>
                        <td><?=$currency_symbol?><?= number_format($row['amount'],2) ?></td>
                        <td>
                            <a href="?page=tour_packages&view=<?= $row['id'] ?>" class="btn btn-sm btn-info">View</a>
                            <a href="?page=tour_packages&edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="?page=tour_packages&delete=<?= $row['id'] ?>"
                               onclick="return confirm('Delete this package?')"
                               class="btn btn-sm btn-danger">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-3">No packages found</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ADD / EDIT MODAL -->
<div class="modal fade" id="packageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <?= $editPackage ? 'Edit Package' : 'Add Package' ?>
                </h5>
                <a href="./?page=tour_packages" class="btn-close"></a>
            </div>

            <div class="modal-body">
                <input type="hidden" name="id" value="<?= $editPackage['id'] ?? '' ?>">

                <div class="mb-3">
                    <label class="form-label">Package Name</label>
                    <input type="text" name="name" class="form-control" required
                           value="<?= htmlspecialchars($editPackage['name'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($editPackage['description'] ?? '') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Duration</label>
                        <input type="text" name="duration" class="form-control" required
                               value="<?= htmlspecialchars($editPackage['duration'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required
                               value="<?= htmlspecialchars($editPackage['amount'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button name="save_package" class="btn btn-success">Save</button>
                <a href="./?page=tour_packages" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Package Details</h5>
                <a href="./?page=tour_packages" class="btn-close"></a>
            </div>
            <div class="modal-body">
                <?php if ($viewPackage): ?>
                    <h5><?= htmlspecialchars($viewPackage['name']) ?></h5>
                    <p><?= nl2br(htmlspecialchars($viewPackage['description'])) ?></p>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Duration:</strong> <?= htmlspecialchars($viewPackage['duration']) ?></li>
                        <li class="list-group-item"><strong>Amount:</strong> <?=$currency_symbol?><?= number_format($viewPackage['amount'],2) ?></li>
                        <li class="list-group-item"><strong>Created:</strong> <?= date("d M Y h:i A", strtotime($viewPackage['created_at'])) ?></li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php if ($editPackage): ?>
<script>
    new bootstrap.Modal(document.getElementById('packageModal')).show();
</script>
<?php endif; ?>

<?php if ($viewPackage): ?>
<script>
    new bootstrap.Modal(document.getElementById('viewModal')).show();
</script>
<?php endif; ?>