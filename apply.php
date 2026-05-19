<?php
// apply.php
$source = $_GET['source'] ?? 'Website';
$allowedSources = ['WhatsApp','Email','Phone','Walk-in','Website','Other'];
if (!in_array($source, $allowedSources, true)) $source = 'Website';
?>
<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

$error = $_SESSION['form_error'] ?? null;
$old   = $_SESSION['old'] ?? [];

$_SESSION['apprand'] = $apprand = rand();
unset($_SESSION['form_error'], $_SESSION['old']);
function old($key, $default = '') {
  global $old;
  return htmlspecialchars($old[$key] ?? $default);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Candidate Registration</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
:root{
  --bg:#f3f4f6;
  --card:#ffffff;
  --primary:#2563eb;
  --primary-soft:#e0e7ff;
  --text:#111827;
  --muted:#6b7280;
  --border:#e5e7eb;
  --radius:14px;
}

*{box-sizing:border-box}

body{
  margin:0;
  font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;
  background:var(--bg);
  color:var(--text);
}

.container{
  max-width:880px;
  margin:24px auto;
  padding:16px;
}

.card{
  background:var(--card);
  border-radius:18px;
  padding:24px;
  box-shadow:0 10px 28px rgba(0,0,0,.08);
}

h1{
  margin:0 0 6px;
  font-size:22px;
}
.subtitle{
  font-size:14px;
  color:var(--muted);
  margin-bottom:18px;
}

.section{
  margin-top:30px;
}
.section h3{
  margin:0 0 14px;
  font-size:16px;
  display:flex;
  align-items:center;
  gap:8px;
}
.section h3::before{
  content:"";
  width:4px;
  height:18px;
  background:var(--primary);
  border-radius:2px;
}

.grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:14px;
}
.grid > div{
  margin-bottom:2px;
}

@media(max-width:720px){
  .grid{grid-template-columns:1fr}
}

label{
  font-size:13px;
  margin-bottom:8px;
  display:block;
}
.req{color:#dc2626}

input,select{
  width:100%;
  padding:12px 14px;
  border-radius:12px;
  border:1px solid var(--border);
  font-size:14px;
  background:#fff;
}

input::placeholder{
  color:#9ca3af;
}

input:focus,select:focus{
  outline:none;
  border-color:var(--primary);
  box-shadow:0 0 0 2px var(--primary-soft);
}

.note{
  font-size:12px;
  color:var(--muted);
  margin-top:4px;
}

.file{
  padding:16px;
  border:2px dashed var(--border);
  border-radius:16px;
  background:#fafafa;
  margin-top:6px;
}


.file input{
  border:none;
  padding:0;
}

.consent-card{
  display:flex;
  gap:12px;
  padding:14px;
  border:1px solid var(--border);
  border-radius:16px;
  background:#f9fafb;
  margin-top:20px;
  cursor:pointer;
}

.consent-card:hover{
  background:#f3f4f6;
}

.consent-card input{
  width:18px;
  height:18px;
  margin-top:2px;
}

.consent-text{
  font-size:13px;
  line-height:1.4;
}

.error{
  display:none;
  color:#b91c1c;
  font-size:14px;
  margin-top:12px;
}

.actions{
  margin-top:24px;
}

button{
  width:100%;
  padding:15px;
  border-radius:16px;
  border:0;
  background:var(--primary);
  color:#fff;
  font-size:15px;
  font-weight:600;
  cursor:pointer;
}

button:active{
  transform:translateY(1px);
}

.footer-note{
  font-size:12px;
  color:var(--muted);
  text-align:center;
  margin-top:14px;
}
</style>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>
<div class="container">
<div class="card">

<h1>Candidate Registration</h1>
<p class="subtitle">Fill your details and upload your CV to apply faster.</p>

<form id="appForm" method="post" action="applysubmit.php" enctype="multipart/form-data">

<input type="hidden" name="lead_source" value="<?= htmlspecialchars($source) ?>">

<?php if ($error): ?>
  <div style="
    background:#fee2e2;
    color:#991b1b;
    padding:14px;
    border-radius:12px;
    margin-bottom:16px;
    font-size:14px;">
    <strong>Submission failed:</strong><br>
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<!-- PERSONAL -->
<div class="section">
<h3>Personal Information</h3>

<div class="grid">
<div>
<label>Full Name <span class="req">*</span></label>
<input type="hidden" name="apprand" value="<?=$apprand?>">
<input name="full_name" required minlength="3"
       value="<?= old('full_name') ?>"
       placeholder="e.g. Mohammed Ali">
</div>

<div>
<label>Mobile (WhatsApp) <span class="req">*</span></label>
<input name="mobile" required
       value="<?= old('mobile') ?>"
       placeholder="+9715XXXXXXXX or 05XXXXXXXX">
<div class="note">UAE format preferred (+9715xxxxxxx)</div>
</div>
</div>

<div class="grid">
<div>
<label>Email</label>
<input type="email" name="email"
       value="<?= old('email') ?>"
       placeholder="name@example.com">
</div>

<div>
<label>Nationality <span class="req">*</span></label>
<input name="nationality" required
       value="<?= old('nationality') ?>"
       placeholder="e.g. India, Nepal">
</div>
</div>
</div>

<!-- LOCATION -->
<div class="section">
<h3>Location</h3>

<div class="grid">
<div>
<label>Current Location <span class="req">*</span></label>
<select name="current_location" id="current_location" required>
  <option value="">Select</option>
  <option value="UAE" <?= old('current_location')==='UAE'?'selected':'' ?>>UAE</option>
  <option value="Outside UAE" <?= old('current_location')==='Outside UAE'?'selected':'' ?>>Outside UAE</option>
</select>
</div>

<div>
<label>City <span class="req">*</span></label>
<input name="city" required
       value="<?= old('city') ?>"
       placeholder="e.g. Abu Dhabi">
</div>
</div>

<div class="grid" id="visa_block" style="<?= old('current_location')==='UAE'?'':'display:none;' ?>">
<div>
<label>Visa Status (UAE only)</label>
<select name="visa_status" id="visa_status">
  <option value="">Select</option>
  <?php
  $visas = ['Visit','Employment','Cancelled','Freelance','Dependent','Golden Visa','Student','Other'];
  foreach ($visas as $v):
  ?>
    <option value="<?= $v ?>" <?= old('visa_status')===$v?'selected':'' ?>><?= $v ?></option>
  <?php endforeach; ?>
</select>
</div>
</div>
</div>

<!-- JOB -->
<div class="section">
<h3>Job Details</h3>

<div class="grid">
<div>
<label>Applying For <span class="req">*</span></label>
<select name="position_category" id="position_category" required>
  <option value="">Select</option>
  <?php
  $positions = ['General Helper','Cleaner','Driver','Painter','Electrician','Plumber','Welder','Supervisor','Admin','Other'];
  foreach ($positions as $p):
  ?>
    <option value="<?= $p ?>" <?= old('position_category')===$p?'selected':'' ?>><?= $p ?></option>
  <?php endforeach; ?>
</select>
</div>

<div id="other_position_wrap" style="<?= old('position_category')==='Other'?'':'display:none;' ?>">
<label>Other Position <span class="req">*</span></label>
<input name="other_position"
       value="<?= old('other_position') ?>"
       placeholder="Specify position">
</div>
</div>

<div class="grid">
<div>
<label>Years of Experience</label>
<input type="number" name="years_experience"
       value="<?= old('years_experience') ?>"
       placeholder="e.g. 5">
</div>

<div>
<label>Preferred Work Location</label>
<select name="preferred_work_location">
  <option value="">Select</option>
  <?php
  $locs = ['Abu Dhabi','Dubai','Sharjah','Any UAE','Outside UAE'];
  foreach ($locs as $l):
  ?>
    <option value="<?= $l ?>" <?= old('preferred_work_location')===$l?'selected':'' ?>><?= $l ?></option>
  <?php endforeach; ?>
</select>
</div>
</div>
</div>

<!-- AVAILABILITY -->
<div class="section">
<h3>Availability</h3>

<div class="grid">
<div>
<label>Availability to Join <span class="req">*</span></label>
<select name="availability" required>
  <option value="">Select</option>
  <?php
  $av = ['Immediately','Within 7 days','Within 15 days','Within 30 days','More than 30 days'];
  foreach ($av as $a):
  ?>
    <option value="<?= $a ?>" <?= old('availability')===$a?'selected':'' ?>><?= $a ?></option>
  <?php endforeach; ?>
</select>
</div>

<div>
<label>Notice Period</label>
<select name="notice_period">
  <option value="">Select</option>
  <?php
  $np = ['0 days','7 days','15 days','30 days','60 days','Other'];
  foreach ($np as $n):
  ?>
    <option value="<?= $n ?>" <?= old('notice_period')===$n?'selected':'' ?>><?= $n ?></option>
  <?php endforeach; ?>
</select>
</div>
</div>

<div class="grid">
<div>
<label>Expected Salary (AED)</label>
<input type="number" name="expected_salary_aed"
       value="<?= old('expected_salary_aed') ?>"
       placeholder="e.g. 1500">
</div>

<div>
<label>Communication Preference</label>
<select name="communication_preference">
  <option value="">Select</option>
  <?php
  $cp = ['WhatsApp','Phone','Email'];
  foreach ($cp as $c):
  ?>
    <option value="<?= $c ?>" <?= old('communication_preference')===$c?'selected':'' ?>><?= $c ?></option>
  <?php endforeach; ?>
</select>
</div>
</div>
</div>

<!-- FILES -->
<div class="section">
<h3>Documents</h3>

<label>Upload CV <span class="req">*</span></label>
<div class="file">
<input type="file" name="cv_file" required accept=".pdf,.doc,.docx">
<div class="note">PDF/DOC/DOCX – max 10MB</div>
</div>

<label style="margin-top:12px;">Additional Documents</label>
<div class="file">
<input type="file" name="extra_docs[]" multiple accept=".pdf,.jpg,.jpeg,.png">
<div class="note">Passport / Visa / Certificates (optional)</div>
</div>
</div>

<label class="consent-card">
  <input type="checkbox" name="consent" value="1" <?= old('consent')?'checked':'' ?> required>
  <div class="consent-text">
    I confirm that the information provided is accurate and I agree that the
    company may store and process my personal data for recruitment purposes.
    <span class="req">*</span>
  </div>
</label>

<div class="actions">
  <div style="margin:20px 0 15px; display:flex; justify-content:center;">
    <div class="g-recaptcha" data-sitekey="6LfECkIsAAAAAICCLECklnZb2gf7sPzP-T14AaOg"></div>
  </div>
  <button type="submit">Submit Application</button>
</div>

<p class="footer-note">Your data is securely stored and used only for recruitment.</p>

</form>
</div>
</div>

<script>
const position=document.getElementById('position_category');
const otherWrap=document.getElementById('other_position_wrap');
const otherInput=document.getElementById('other_position');
const loc=document.getElementById('current_location');
const visaBlock=document.getElementById('visa_block');
const visaStatus=document.getElementById('visa_status');

function toggleOther(){
  const isOther=position.value==='Other';
  otherWrap.style.display=isOther?'block':'none';
  otherInput.required=isOther;
  if(!isOther) otherInput.value='';
}
function toggleVisa(){
  const isUAE=loc.value==='UAE';
  visaBlock.style.display=isUAE?'grid':'none';
  visaStatus.required=isUAE;
  if(!isUAE) visaStatus.value='';
}
position.addEventListener('change',toggleOther);
loc.addEventListener('change',toggleVisa);
toggleOther();toggleVisa();
</script>
<script>
const form = document.getElementById('appForm');
const formErr = document.getElementById('formErr');

form.addEventListener('submit', function (e) {
  formErr.style.display = 'none';

  if (!form.checkValidity()) {
    e.preventDefault();
    formErr.style.display = 'block';

    // highlight invalid fields
    form.querySelectorAll(':invalid').forEach(el => {
      el.style.borderColor = '#dc2626';
    });

    // remove highlight on input
    form.querySelectorAll('input,select').forEach(el => {
      el.addEventListener('input', () => {
        el.style.borderColor = '';
      }, { once:true });
    });

    return;
  }
});
</script>
<script>
form.addEventListener('submit', function(e){
  if (typeof grecaptcha !== 'undefined') {
    if (grecaptcha.getResponse().length === 0) {
      e.preventDefault();
      alert('Please verify that you are not a robot.');
    }
  }
});
</script>
</body>
</html>