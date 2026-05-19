<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

$uid = $CURRENT_USER_ID;

if(isset($_GET['id']) && $_GET['id']!='') {
    $contact_id = $site->esc($_GET['id']);
}
else {
    header("url=index.php?page=contacts");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['srand']) && $_SESSION['srand'] == $_POST['srand']) {
    $type = trim($_POST['type'] ?? 'Callback');
    $reminder_date = trim($_POST['reminder_date'] ?? '');
    $reminder_time = trim($_POST['reminder_time'] ?? '10:00');
    $notes = trim($_POST['notes'] ?? '');

    if ($reminder_date === '') {
        echo "<div class='alert alert-danger text-center'>Please select a date.</div>";
    } else {
        $datetime = "$reminder_date $reminder_time:00";
        $stmt = $mysqli->prepare("INSERT INTO contacts_reminders (contact_id, type, reminder_at, note, created_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("isss", $contact_id, $type, $datetime, $notes);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                // echo "<div class='alert alert-success text-center'>Reminder added successfully!</div>";
                // header("location: index.php?page=contacts");
                echo '<script>window.location.href="index.php?page=contacts"</script>';
                exit;
            } else {
                echo "<div class='alert alert-danger text-center'>Failed to add reminder.</div>";
            }
        }
    }
}
$srand = $_SESSION['srand'] = rand();
?>
<style>
    .btn-outline-secondary.btn-primary {
        color: #fff !important;
        background: #6c757d !important;
    }
</style>
<div class="container" style="max-width:600px;">
  <h2 class="mb-4 text-center">Add Reminder</h2>

  <form method="post" id="new-reminder-form" class="card shadow-sm p-4">
    <input type="hidden" name="srand" value="<?= $srand ?>">

    <!-- Reminder Type -->
    <div class="mb-3">
      <label class="form-label mb-1 small fw-bold">Reminder Type</label>
      <div class="d-flex flex-wrap gap-1">
        <input type="radio" class="btn-check" name="type" id="type-callback" value="Callback" autocomplete="off" checked>
        <label class="btn btn-outline-primary btn-xs rounded-pill px-3" for="type-callback">Callback</label>

        <input type="radio" class="btn-check" name="type" id="type-followup" value="Follow-up" autocomplete="off">
        <label class="btn btn-outline-primary btn-xs rounded-pill px-3" for="type-followup">Follow-up</label>

        <input type="radio" class="btn-check" name="type" id="type-sendemail" value="Send Email" autocomplete="off">
        <label class="btn btn-outline-primary btn-xs rounded-pill px-3" for="type-sendemail">Send Email</label>

        <input type="radio" class="btn-check" name="type" id="type-other" value="Other" autocomplete="off">
        <label class="btn btn-outline-primary btn-xs rounded-pill px-3" for="type-other">Other</label>
      </div>
    </div>

    <!-- Quick Select Reminder Time -->
    <div class="mb-3">
      <label class="form-label mb-1 small fw-bold">Remind Me At</label>
      <div class="d-flex flex-wrap gap-1">
        <button type="button" class="btn btn-outline-secondary btn-primary btn-xs rounded-pill quick-reminder" data-days="7">1 Week Later</button>
        <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill quick-reminder" data-days="30">1 Month Later</button>
        <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill quick-reminder" data-days="90">3 Months Later</button>
        <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill quick-reminder" data-days="custom">Other</button>
      </div>
    </div>

    <!-- Date and Time (hidden until needed) -->
    <div id="customDateTime" class="row g-2 mb-3" style="display:none;">
      <div class="col-md-6">
        <label class="small mb-1">Date</label>
        <input type="date" class="form-control form-control-xs rounded-pill" name="reminder_date" id="reminder-date" value="<?=date("Y-m-d",strtotime("+7 days",strtotime($date)))?>" required>
      </div>
      <div class="col-md-6">
        <label class="small mb-1">Time</label>
        <input type="time" class="form-control form-control-xs rounded-pill" name="reminder_time" id="reminder-time" value="10:00" required>
      </div>
    </div>

    <!-- Notes -->
    <div class="mb-3">
      <label class="small mb-1 fw-bold">Notes</label>
      <textarea class="form-control form-control-xs rounded-3" name="notes" id="notes-field" rows="3" placeholder="Write notes here..."></textarea>
    </div>

    <div class="d-flex gap-2">
      <!-- Skip -->
      <a href="index.php?page=contacts" class="btn btn-primary flex-fill rounded-pill">
        <i class="fa fa-arrow-right"></i> Skip
      </a>

      <!-- Submit -->
      <button type="submit" class="btn btn-success flex-fill rounded-pill">
        <i class="fa fa-plus"></i> Set Reminder
      </button>
    </div>

  </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const quickBtns = document.querySelectorAll('.quick-reminder');
  const dateInput = document.getElementById('reminder-date');
  const timeInput = document.getElementById('reminder-time');
  const customDiv = document.getElementById('customDateTime');

  quickBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const days = btn.dataset.days;
      const now = new Date();

      quickBtns.forEach(b => b.classList.remove('btn-primary'));
      btn.classList.add('btn-primary');

      if (days === 'custom') {
        customDiv.style.display = 'flex';
        dateInput.required = true;
        timeInput.required = true;
      } else {
        customDiv.style.display = 'none';
        dateInput.required = false;
        timeInput.required = false;

        now.setDate(now.getDate() + parseInt(days));
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const dd = String(now.getDate()).padStart(2, '0');
        dateInput.value = `${yyyy}-${mm}-${dd}`;
        timeInput.value = '10:00';
      }
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>