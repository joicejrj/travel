<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

$uid = $CURRENT_USER_ID; // Logged-in agent

// Default form values
$name = $company = $phone = $whatsapp = $email = $address = $city = $state = $country = $services = $google_rating = $website = '';
$source = 'from excel';
$type = 'Suspect';
$country = "India";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'],$_POST['email'], $_POST['srand']) && $_SESSION['srand'] == $_POST['srand']) {
    $name = trim($_POST['name'] ?? '');
    // $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    // $whatsapp = trim($_POST['whatsapp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $timezone = trim($_POST['timezone'] ?? 'Europe/London');
    // $agent_id = trim($_POST['agent_id'] ?? '');
    // $city = trim($_POST['city'] ?? '');
    // $state = trim($_POST['state'] ?? '');
    // $country = trim($_POST['country'] ?? '');
    // $services = trim($_POST['services'] ?? '');
    // $google_rating = trim($_POST['google_rating'] ?? '');
    // $website = trim($_POST['website'] ?? '');
    // $source = trim($_POST['source'] ?? 'from excel');

    // Basic validation
    if ($name === '' || $email === '') {
        echo "<div class='alert alert-danger text-center'>Name and Email are required.</div>";
    } else {
        // Normalize phones
        $phonesa = [];
        $phonex = preg_replace('/\D/', '', $phone);
        if ($phonex != '' && strlen($phonex) >= 8) $phonesa[] = $phonex;
        $whatsappx = preg_replace('/\D/', '', $whatsapp);
        if ($whatsappx != '' && $whatsappx != $phonex && strlen($whatsappx) >= 8) $phonesa[] = $whatsappx;
        $phones = json_encode($phonesa);

        // File upload
        $imagePath1 = null;
        if (!empty($_FILES['photo1']['name'])) {
          $imgt1 = $_FILES['photo1'];
          $img1 = $site->upload_img($imgt1,'uploads/contacts','random',800);
          if($img1!='') {
            $imagePath1 = $img1;
          }
        }
        $imagePath2 = null;
        if (!empty($_FILES['photo2']['name'])) {
          $imgt2 = $_FILES['photo2'];
          $img2 = $site->upload_img($imgt2,'uploads/contacts','random',800);
          if($img2!='') {
            $imagePath2 = $img2;
          }
        }

        // Insert
        // agent_id, name, company, phone, phones, whatsapp, email, address, city, state, country, services, google_rating, website, source, type, photo, created_at
        $stmt = $mysqli->prepare("
            INSERT INTO contacts
              (agent_id, name, company, phone, email, country, type, photo, photo1, timezone, fil_emails, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())
        ");
        if ($stmt) {
            $stmt->bind_param(
                "issssssssss",
                $uid, $name, $name, $phone, $email, $country, $type, $imagePath1, $imagePath2, $timezone, $email
            );
            $ok = $stmt->execute();
            $msg = $ok ? "Contact added successfully." : "Failed to add contact: " . $stmt->error;
            $stmt->close();

            if ($ok) {
                // echo "<div class='alert alert-success text-center'>$msg</div>";
                // header("location: index.php?page=contacts_reminders_add&id=" . $mysqli->insert_id);
                echo '<script>window.location.href="index.php?page=contacts_reminders_add&id=' . $mysqli->insert_id.'"</script>';
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
  <h2 class="mb-4 text-center">Add Contact</h2>

  <form method="post" enctype="multipart/form-data" class="card shadow-sm p-4">
    <input type="hidden" name="srand" value="<?= $srand ?>">

    <div class="mb-3">
      <label class="form-label">Name *</label>
      <input type="text" name="name" class="form-control" placeholder="Rahul Dev" required>
    </div>

    <!-- Two Upload Inputs (Side by Side) -->
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Upload Image 1 (Address / Visiting Card)</label>
        <input type="file" name="photo1" id="photoInput1" class="form-control" accept=".jpg,.jpeg,.png,.gif">
        <div class="mt-2" id="photoPreview1"></div>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">Upload Image 2 (Address / Visiting Card)</label>
        <input type="file" name="photo2" id="photoInput2" class="form-control" accept=".jpg,.jpeg,.png,.gif">
        <div class="mt-2" id="photoPreview2"></div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Email *</label>
        <input type="email" name="email" class="form-control" placeholder="rahul@example.com" required>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Mobile</label>
        <input type="text" name="phone" class="form-control" placeholder="+91 9876543210">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Country</label>
      <input type="text" name="country" class="form-control" placeholder="India">
    </div>

    <div x-data="timezoneSelector()" x-init="init()" class="mb-3">
      <label class="form-label fw-semibold small text-muted">Time Zone</label>
      
      <!-- Hidden input that is submitted in form -->
      <input type="hidden" name="timezone" x-model="localZone">

      <!-- Search input -->
      <input type="text" placeholder="Search (e.g., Asia/Calcutta)" 
             class="form-control mb-2" x-model="search" @input="filterZones()">

      <!-- Select dropdown -->
      <select class="form-select" x-model="selectedZone" @change="localZone = selectedZone">
        <template x-for="zone in filteredZones" :key="zone">
          <option :value="zone" :selected="zone === selectedZone" x-text="zone"></option>
        </template>
      </select>
    </div>

    <button type="submit" class="btn btn-primary w-100">Next</button>
  </form>
</div>

<script>
// Reusable image preview handler
function setupImagePreview(inputId, previewId) {
  document.getElementById(inputId).addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';

    if (!file) return;

    const allowed = ['image/jpeg','image/png','image/gif'];
    if (!allowed.includes(file.type)) {
      preview.innerHTML = "<div class='text-danger small mt-1'>Invalid file type (only JPG, PNG, GIF allowed)</div>";
      e.target.value = '';
      return;
    }
    const img = document.createElement('img');
    img.src = URL.createObjectURL(file);
    img.className = 'img-thumbnail mt-2';
    img.style.maxWidth = '180px';
    img.onload = () => URL.revokeObjectURL(img.src);
    preview.appendChild(img);

  });
}

// Initialize both upload preview handlers
setupImagePreview('photoInput1', 'photoPreview1');
setupImagePreview('photoInput2', 'photoPreview2');
</script>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function timezoneSelector() {
  return {
    search: '',
    localZone: Intl.DateTimeFormat().resolvedOptions().timeZone, // hidden input
    selectedZone: '', // for dropdown
    zones: Intl.supportedValuesOf ? Intl.supportedValuesOf('timeZone') : [
      'UTC', 'Asia/Kolkata', 'America/New_York', 'Europe/London', 'Asia/Dubai'
    ],
    filteredZones: [],
    init() {
      this.selectedZone = this.localZone; // default dropdown selection
      this.filteredZones = this.zones;
      this.localZone = this.selectedZone; // hidden input set
    },
    filterZones() {
      const q = this.search.toLowerCase();
      this.filteredZones = this.zones.filter(z => z.toLowerCase().includes(q));
    }
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
