<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

$uid = $CURRENT_USER_ID; // Logged-in agent

// Default form values
$name = $company = $phone = $whatsapp = $email = $address = $city = $state = $country = $services = $google_rating = $website = '';
$source = 'from excel';
$type = 'warm';
$country = "India";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company'], $_POST['srand']) && $_SESSION['srand'] == $_POST['srand']) {
    $company = trim($_POST['company'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $services = trim($_POST['services'] ?? '');
    $google_rating = trim($_POST['google_rating'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $source = trim($_POST['source'] ?? 'from excel');
    $type = trim($_POST['type'] ?? 'warm');

    // Basic validation
    if ($company === '') {
        echo "<div class='alert alert-danger text-center'>Company name is required.</div>";
    } else {
        // Normalize phones
        $phonesa = [];
        $phonex = preg_replace('/\D/', '', $phone);
        if ($phonex != '' && strlen($phonex) >= 8) $phonesa[] = $phonex;
        $whatsappx = preg_replace('/\D/', '', $whatsapp);
        if ($whatsappx != '' && $whatsappx != $phonex && strlen($whatsappx) >= 8) $phonesa[] = $whatsappx;
        $phones = json_encode($phonesa);

        // File upload
        $imagePath = null;
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/carriers/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg','jpeg','png','gif','pdf'];

            if (in_array($ext, $allowedExts)) {
                $safeName = 'carrier_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                $target = $uploadDir . $safeName;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                    $imagePath = 'uploads/carriers/' . $safeName;
                } else {
                    echo "<div class='alert alert-danger text-center'>Failed to upload file.</div>";
                }
            } else {
                echo "<div class='alert alert-danger text-center'>Invalid file type. Only JPG, PNG, GIF, or PDF allowed.</div>";
            }
        }

        // Insert
        $stmt = $mysqli->prepare("
            INSERT INTO carriers
              (agent_id, name, company, phone, phones, whatsapp, email, address, city, state, country, services, google_rating, website, source, type, photo, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())
        ");
        if ($stmt) {
            $stmt->bind_param(
                "issssssssssssssss",
                $uid, $name, $company, $phone, $phones, $whatsapp, $email, $address,
                $city, $state, $country, $services, $google_rating, $website,
                $source, $type, $imagePath
            );
            $ok = $stmt->execute();
            $msg = $ok ? "Carrier added successfully." : "Failed to add carrier: " . $stmt->error;
            $stmt->close();

            if ($ok) {
                echo "<div class='alert alert-success text-center'>$msg</div>";
                header("refresh:1;url=index.php?page=carriers_view&id=" . $mysqli->insert_id);
                exit;
            } else {
                echo "<div class='alert alert-danger text-center'>$msg</div>";
            }
        } else {
            echo "<div class='alert alert-danger text-center'>Database error: " . htmlspecialchars($mysqli->error) . "</div>";
        }
    }
}

$srand = $_SESSION['srand'] = rand();
?>

<div class="container" style="max-width:700px;">
  <h2 class="mb-4 text-center">Add Carrier</h2>

  <form method="post" enctype="multipart/form-data" class="card shadow-sm p-4">
    <input type="hidden" name="srand" value="<?= $srand ?>">

    <div class="mb-3">
      <label class="form-label">Company Name *</label>
      <input type="text" name="company" class="form-control" required>
    </div>

    <!-- Upload moved here -->
    <div class="mb-3">
      <label class="form-label">Upload Image (Address / Visiting Card)</label>
      <input type="file" name="photo" id="photoInput" class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf">
      <div class="mt-2" id="photoPreview"></div>
    </div>

    <div class="mb-3">
      <label class="form-label">Primary Contact Name</label>
      <input type="text" name="name" class="form-control">
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">WhatsApp</label>
        <input type="text" name="whatsapp" class="form-control">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Address</label>
      <textarea name="address" class="form-control" rows="2"></textarea>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">City</label>
        <input type="text" name="city" class="form-control">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">State</label>
        <input type="text" name="state" class="form-control">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Country *</label>
        <input type="text" name="country" class="form-control" value="India" required>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Source</label>
        <select name="source" class="form-select">
          <option value="from excel">From Excel</option>
          <option value="from website">From Website</option>
          <option value="from call">From Call</option>
          <option value="from leads">From Leads</option>
          <option value="from whatsapp">From Whatsapp</option>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-select">
          <option value="warm">Warm</option>
          <option value="hot">Hot</option>
          <option value="cold">Cold</option>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Services</label>
      <input type="text" name="services" class="form-control">
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Google Rating</label>
        <input type="text" name="google_rating" class="form-control">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Website</label>
        <input type="text" name="website" class="form-control">
      </div>
    </div>

    <button type="submit" class="btn btn-primary w-100">Add Carrier</button>
  </form>
</div>

<script>
document.getElementById('photoInput').addEventListener('change', function(e) {
  const file = e.target.files[0];
  const preview = document.getElementById('photoPreview');
  preview.innerHTML = '';

  if (!file) return;

  const allowed = ['image/jpeg','image/png','image/gif','application/pdf'];
  if (!allowed.includes(file.type)) {
    preview.innerHTML = "<div class='text-danger small mt-1'>Invalid file type (only JPG, PNG, GIF, PDF allowed)</div>";
    e.target.value = '';
    return;
  }

  if (file.type === 'application/pdf') {
    preview.innerHTML = "<i class='fas fa-file-pdf text-danger fs-4'></i> " + file.name;
  } else {
    const img = document.createElement('img');
    img.src = URL.createObjectURL(file);
    img.className = 'img-thumbnail mt-2';
    img.style.maxWidth = '180px';
    img.onload = () => URL.revokeObjectURL(img.src);
    preview.appendChild(img);
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
