<?php 
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
// require_once __DIR__ . '/../config/functions.php';
require_once 'config/functions.php';

$uid = $CURRENT_USER_ID; // Logged-in agent

// Default form values
$company = $name = $industry = $phone = $email = $address = $city = $state = $services = $google_rating = $website = '';
$status = 'Suspect';

// Helper: sanitize POST text
function post_trim($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

$errors = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['srand']) && isset($_SESSION['srand']) && $_SESSION['srand'] == $_POST['srand']) {
    // Common fields
    $company = post_trim('company');
    $name    = post_trim('name');
    $industry    = post_trim('industry');
    $email   = post_trim('email');
    $phone   = post_trim('phone');
    $status  = post_trim('status', 'Suspect');

    // Reminder-specific fields (may be empty)
    $reminder_date = post_trim('reminder_date', '');
    $reminder_time = post_trim('reminder_time', '10:00');
    $notes         = post_trim('notes', '');
    $reminder_choice = post_trim('reminder_choice', 'set'); // 'set' or 'none'

    // Which button pressed (kept for compatibility but we use reminder_choice to decide)
    $submit_pressed = isset($_POST['submit_with_reminder']);

    // Determine whether to add reminder
    $add_with_reminder = ($reminder_choice === 'set');

    // Basic validation
    if ($name === '') {
        $errors[] = "Name is required.";
    }

    // If adding with reminder, ensure date present
    if ($add_with_reminder && $reminder_date === '') {
        $errors[] = "Please select a reminder date when adding a reminder.";
    }

    if (empty($errors)) {
        // Normalize phone(s)
        $phonesa = [];
        $phonex = preg_replace('/\D/', '', $phone);
        if ($phonex !== '' && strlen($phonex) >= 8) $phonesa[] = $phonex;
        $phones = json_encode($phonesa);

        // File uploads
        $imagePath1 = null;
        if (!empty($_FILES['photo1']['name'])) {
            $imgt1 = $_FILES['photo1'];
            $img1 = $site->upload_img($imgt1,'uploads/customers','random',800,"auto","");
            if($img1!='') $imagePath1 = $img1;
        }
        $imagePath2 = null;
        if (!empty($_FILES['photo2']['name'])) {
            $imgt2 = $_FILES['photo2'];
            $img2 = $site->upload_img($imgt2,'uploads/customers','random',800,"auto","");
            if($img2!='') $imagePath2 = $img2;
        }

        // Insert Customer
        $stmt = $mysqli->prepare("
            INSERT INTO customers
              (agent_id, name, company, industry, phone, phones, email, type, photo, photo1, fil_emails, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())
        ");
        if ($stmt) {
            // type column used earlier for 'type' (Suspect etc.). I map $status -> type
            $stmt->bind_param(
                "issssssssss",
                $uid, $name, $company, $industry, $phone, $phones, $email, $status, $imagePath1, $imagePath2, $email
            );
            $ok = $stmt->execute();
            $new_customer_id = $mysqli->insert_id;
            $stmt->close();

            if ($ok) {
                $site->agent_log("Added new Customer ".$name);

                $db->insert('customers_sites',array('customer_id'=>$new_customer_id, 'site_name'=>'Site 1', 'site_contact'=>'', 'site_address'=>'', 'site_location'=>'', 'created_by'=>$datetime));
                $db->insert('customers_sites',array('customer_id'=>$new_customer_id, 'site_name'=>'Site 2', 'site_contact'=>'', 'site_address'=>'', 'site_location'=>'', 'created_by'=>$datetime));
                $site->agent_log("Added Site 1 and Site 2 for the customer automatically");

                // If Add With Reminder: insert reminder row
                if ($add_with_reminder) {
                    // normalize datetime
                    $datetime1 = $reminder_date . ' ' . $reminder_time . ':00';
                    $rstmt = $mysqli->prepare("INSERT INTO customers_reminders (customer_id, type, reminder_at, note, created_at) VALUES (?, ?, ?, ?, NOW())");
                    if ($rstmt) {
                        $default_type = 'Callback';
                        $rstmt->bind_param("isss", $new_customer_id, $default_type, $datetime1, $notes);
                        $rok = $rstmt->execute();
                        $rstmt->close();
                        if (!$rok) {
                            $errors[] = "Customer added but failed to add reminder: " . htmlspecialchars($mysqli->error);
                        }
                        else {
                          $site->agent_log("Added a reminder for new Customer ".$name);
                        }
                    } else {
                        $errors[] = "Customer added but failed to add reminder (DB error): " . htmlspecialchars($mysqli->error);
                    }
                }

                // If no errors so far, redirect to customers list
                if (empty($errors)) {
                    // Redirect in JS to avoid "headers already sent"
                    echo '<script>window.location.href="index.php?page=customers"</script>';
                    exit;
                }
            } else {
                $errors[] = "Failed to add Customer: " . htmlspecialchars($mysqli->error);
            }
        } else {
            $errors[] = "Database error: " . htmlspecialchars($mysqli->error);
        }
    }
}

// CSRF token
$srand = $_SESSION['srand'] = rand();
$default_reminder_date = date("Y-m-d", strtotime("+7 days"));
?>

<!-- Styles: small tweaks -->
<style>
  .status-btn { cursor:pointer; }
  .status-btn.active { background-color:#0d6efd; color:#fff; border-color:#0d6efd; }
  .quick-reminder.btn-primary { background-color:#6c757d !important; color:#fff !important; border-color:#6c757d !important; }
  .form-top-frame { margin-top:8px; } /* bring to top */
</style>

<div class="container form-top-frame" style="max-width:700px;">
  <!-- No title as requested, frame brought to top -->
  <form method="post" enctype="multipart/form-data" class="card shadow-sm p-4">
    <input type="hidden" name="srand" value="<?= $srand ?>">
    <input type="hidden" name="status" id="status-input" value="<?= htmlspecialchars($status) ?>">
    <input type="hidden" name="reminder_choice" id="reminder-choice" value="set">

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
      </div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>

    <!-- Company + Name (2-column) -->
    <div class="row g-2 mb-3">
      <div class="col-md-6">
        <label class="form-label">Company *</label>
        <input type="text" name="company" class="form-control" placeholder="ABC Company" value="<?= htmlspecialchars($company) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Industry </label>
        <input type="text" name="industry" class="form-control" placeholder="Pipes and Fittings" value="<?= htmlspecialchars($industry) ?>">
      </div>
    </div>
    <div class="row g-2 mb-3">
      <div class="col-md-6">
        <label class="form-label">Customer Name *</label>
        <input type="text" name="name" class="form-control" placeholder="Rahul Dev" value="<?= htmlspecialchars($name) ?>" required>
      </div>
    </div>

    <!-- Uploads (side-by-side) -->
    <div class="row g-2 mb-3">
      <div class="col-md-6">
        <label class="form-label">Upload Image 1 (Address / Visiting Card)</label>
        <input type="file" name="photo1" id="photoInput1" class="form-control" accept=".jpg,.jpeg,.png,.gif">
        <div class="mt-2" id="photoPreview1"></div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Upload Image 2 (Address / Visiting Card)</label>
        <input type="file" name="photo2" id="photoInput2" class="form-control" accept=".jpg,.jpeg,.png,.gif">
        <div class="mt-2" id="photoPreview2"></div>
      </div>
    </div>

    <!-- Email + Mobile -->
    <div class="row g-2 mb-3">
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" placeholder="rahul@example.com" value="<?= htmlspecialchars($email) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Mobile</label>
        <input type="text" name="phone" class="form-control" placeholder="+91 9876543210" value="<?= htmlspecialchars($phone) ?>">
      </div>
    </div>

    <!-- Status buttons (acts like radio) -->
    <div class="mb-3">
      <label class="form-label mb-1 small fw-bold">Status</label>
      <div class="d-flex flex-wrap gap-2">
        <?php
        foreach ($customers_statuses as $s) {
            $cls = ($s === $status) ? 'status-btn btn btn-outline-primary active' : 'status-btn btn btn-outline-primary';
            echo '<button type="button" data-status="'.htmlspecialchars($s).'" class="'.$cls.' btn-xs rounded-pill px-3">'.htmlspecialchars($s).'</button>';
        }
        ?>
      </div>
    </div>

    <!-- Reminder block (embedded; no Reminder Type) -->
    <div class="mb-3">
      <label class="form-label mb-1 small fw-bold">Remind Me At</label>
      <div class="d-flex flex-wrap gap-1 mb-2">
        <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill quick-reminder" data-days="7">1 Week Later</button>
        <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill quick-reminder" data-days="30">1 Month Later</button>
        <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill quick-reminder" data-days="90">3 Months Later</button>
        <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill quick-reminder" data-days="custom">Other</button>
        <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill quick-reminder" data-days="none">No Reminder</button>
      </div>

      <!-- Date and Time (hidden until needed) -->
      <div id="customDateTime" class="row g-2 mb-3" style="display:none;">
        <div class="col-md-6">
          <label class="small mb-1">Date</label>
          <input type="date" class="form-control form-control-xs rounded-pill" name="reminder_date" id="reminder-date" value="<?= $default_reminder_date ?>">
        </div>
        <div class="col-md-6">
          <label class="small mb-1">Time</label>
          <input type="time" class="form-control form-control-xs rounded-pill" name="reminder_time" id="reminder-time" value="10:00">
        </div>
      </div>

      <!-- Notes -->
      <div class="mb-3">
        <label class="small mb-1 fw-bold">Notes</label>
        <textarea class="form-control form-control-xs rounded-3" name="notes" id="notes-field" rows="3" placeholder="Write notes here..."><?= isset($notes) ? htmlspecialchars($notes) : '' ?></textarea>
      </div>
    </div>

    <!-- Single Save button -->
    <div class="d-flex gap-2">
      <button type="submit" name="submit_with_reminder" class="btn btn-success flex-fill rounded-pill">
        <i class="fa fa-save"></i> Save
      </button>
    </div>

  </form>
</div>

<!-- JS: image previews, status toggle, quick reminder -->
<script>
  // Image previews
  function setupImagePreview(inputId, previewId) {
    const el = document.getElementById(inputId);
    if (!el) return;
    el.addEventListener('change', function(e) {
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
  setupImagePreview('photoInput1','photoPreview1');
  setupImagePreview('photoInput2','photoPreview2');

  // Status buttons (store value in hidden input)
  document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const statusVal = btn.dataset.status || btn.getAttribute('data-status');
      document.getElementById('status-input').value = statusVal;
    });
  });

  // Quick reminder buttons
  document.addEventListener("DOMContentLoaded", () => {
    const quickBtns = document.querySelectorAll('.quick-reminder');
    const dateInput = document.getElementById('reminder-date');
    const timeInput = document.getElementById('reminder-time');
    const customDiv = document.getElementById('customDateTime');
    const reminderChoiceInput = document.getElementById('reminder-choice');

    function clearQuickActive() {
      quickBtns.forEach(b => b.classList.remove('btn-primary'));
      // also remove the custom-style we used earlier
      quickBtns.forEach(b => b.classList.remove('active'));
    }

    quickBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const days = btn.dataset.days;
        clearQuickActive();
        btn.classList.add('btn-primary');

        if (days === 'custom') {
          customDiv.style.display = 'flex';
          dateInput.required = true;
          timeInput.required = true;
          reminderChoiceInput.value = 'set';
        } else if (days === 'none') {
          customDiv.style.display = 'none';
          dateInput.required = false;
          timeInput.required = false;
          // clear date/time (optional)
          // dateInput.value = '';
          // timeInput.value = '10:00';
          reminderChoiceInput.value = 'none';
        } else {
          customDiv.style.display = 'none';
          dateInput.required = false;
          timeInput.required = false;
          reminderChoiceInput.value = 'set';

          const now = new Date();
          now.setDate(now.getDate() + parseInt(days));
          const yyyy = now.getFullYear();
          const mm = String(now.getMonth() + 1).padStart(2, '0');
          const dd = String(now.getDate()).padStart(2, '0');
          dateInput.value = `${yyyy}-${mm}-${dd}`;
          timeInput.value = '10:00';
        }
      });
    });

    // initialize default quick selection to 1 Week Later
    const first = document.querySelector('.quick-reminder[data-days="7"]');
    if (first) first.click();
  });
</script>

<!-- Alpine removed since timezone removed; keep footer -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
