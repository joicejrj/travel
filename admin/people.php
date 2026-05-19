<?php
// public/people.php
// Full people management page with modal create/edit, delete, and Auto-login button per row.
// Requires: includes/header.php to provide $mysqli, require_login(), Bootstrap CSS/JS, etc.

// Ensure session available before using $_SESSION
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/includes/header.php';
require_login();

// esc() helper fallback
if (!function_exists('esc')) {
    function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
}

// CSRF token (persisted in session)
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf_token'];

// messages
$msg = ''; $err = '';
$action = $_POST['action'] ?? null;

/*
  NOTE: Mailbox management lives on mailboxes.php?person={id} (kept as a row action).
  This page handles people create/update/delete and stores a password hash for login.
*/

// POST handling (create/update/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf) {
        $err = "Invalid CSRF token.";
    } else {
        // sanitize inputs a little; further validation can be added
        $person_id = (int)($_POST['person_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = trim($_POST['role'] ?? 'sales person');
        $password = trim($_POST['password'] ?? '');

        if ($action === 'create' || $action === 'update') {
            if ($name === '') {
                $err = "Name is required.";
            } else {
                // On create we require a password (users must login) and email (per request)
                if ($action === 'create') {
                    if ($password === '') {
                        $err = "Password is required when creating a user.";
                    }
                    if ($email === '') {
                        $err = "Email is required when creating a user.";
                    }
                }
            }

            if (empty($err)) {
                if ($action === 'create') {
                    // hash password
                    $pw_hash = password_hash($password, PASSWORD_DEFAULT);

                    // insert person with password_hash
                    $stmt = $mysqli->prepare("INSERT INTO people (name, email, phone, role, password_hash, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    if (!$stmt) {
                        $err = "DB prepare error: " . $mysqli->error;
                    } else {
                        $stmt->bind_param('sssss', $name, $email, $phone, $role, $pw_hash);
                        if ($stmt->execute()) {
                            $new_person_id = $stmt->insert_id;
                            $msg = "User created.";
                        } else {
                            $err = "DB error: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                } else { // update
                    // If password is provided, update hash; otherwise keep existing
                    if ($password !== '') {
                        $pw_hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $mysqli->prepare("UPDATE people SET name=?, email=?, phone=?, role=?, password_hash=? WHERE id=?");
                        if (!$stmt) {
                            $err = "DB prepare error: " . $mysqli->error;
                        } else {
                            $stmt->bind_param('sssssi', $name, $email, $phone, $role, $pw_hash, $person_id);
                            if ($stmt->execute()) {
                                $msg = "User updated (password changed).";
                            } else {
                                $err = "DB error: " . $stmt->error;
                            }
                            $stmt->close();
                        }
                    } else {
                        $stmt = $mysqli->prepare("UPDATE people SET name=?, email=?, phone=?, role=? WHERE id=?");
                        if (!$stmt) {
                            $err = "DB prepare error: " . $mysqli->error;
                        } else {
                            $stmt->bind_param('ssssi', $name, $email, $phone, $role, $person_id);
                            if ($stmt->execute()) {
                                $msg = "User updated.";
                            } else {
                                $err = "DB error: " . $stmt->error;
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        } elseif ($action === 'delete') {
            $person_id = (int)($_POST['person_id'] ?? 0);
            if ($person_id) {
                // delete mailboxes for this person first (optional/kept)
                $dmb = $mysqli->prepare("DELETE FROM mailboxes WHERE person_id=?");
                if ($dmb) {
                    $dmb->bind_param('i', $person_id);
                    $dmb->execute();
                    $dmb->close();
                }
                $d = $mysqli->prepare("DELETE FROM people WHERE id=?");
                if ($d) {
                    $d->bind_param('i', $person_id);
                    if ($d->execute()) {
                        $msg = "User and mailbox(es) deleted.";
                    } else {
                        $err = "DB error: " . $d->error;
                    }
                    $d->close();
                } else {
                    $err = "DB prepare error: " . $mysqli->error;
                }
            } else {
                $err = "Invalid user id.";
            }
        } else {
            $err = "Unknown action.";
        }
    }
}

// GET: if someone directly requested edit via GET (we will still support for non-JS fallback)
$editing = null;
$edit_id = (int)($_GET['edit'] ?? 0);
if ($edit_id) {
    $stmt = $mysqli->prepare("SELECT id, name, email, phone, role, created_at FROM people WHERE id=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $edit_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $editing = $res->fetch_assoc();
        $stmt->close();
    }
}

// list people
$list = [];
$res = $mysqli->query("SELECT id, name, email, phone, role, created_at FROM people ORDER BY id DESC");
if ($res) {
    while ($r = $res->fetch_assoc()) $list[] = $r;
}
?>
<!-- DataTables CSS (simple) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Users</h1>
    <!-- Add Person button opens modal -->
    <button id="btnAddPerson" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#personModal">Add User</button>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?=esc($msg)?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger"><?=esc($err)?></div><?php endif; ?>

  <div class="row">
    <div class="col-12">
      <div class="table-responsive">
        <table id="peopleTable" class="table table-sm table-striped" style="width:100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($list as $p): ?>
              <?php
                // Prepare JSON for data attribute (escape)
                $person_json = json_encode([
                  'id' => (int)$p['id'],
                  'name' => $p['name'],
                  'email' => $p['email'],
                  'phone' => $p['phone'],
                  'role' => $p['role'],
                ], JSON_HEX_APOS | JSON_HEX_QUOT);
              ?>
              <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= esc($p['name']) ?></td>
                <td><?= esc($p['email']) ?></td>
                <td><?= esc($p['phone']) ?></td>
                <td><?= esc($p['role']) ?></td>
                <td><?= esc($p['created_at']) ?></td>
                <td>
                  <!-- Edit opens modal and populates fields via JS. Data is kept in data-person attr. -->
                  <button
                    class="btn btn-sm btn-outline-primary btn-edit-person"
                    type="button"
                    data-person='<?= $person_json ?>'
                    data-bs-toggle="modal"
                    data-bs-target="#personModal"
                  >Edit</button>

                  <!-- AUTO-LOGIN button (visible for every person) -->
                  <button class="btn btn-sm btn-outline-success btn-autologin" type="button" data-person-id="<?= (int)$p['id'] ?>">
                    Auto-login
                  </button>

                  <form method="post" style="display:inline" onsubmit="return confirm('Delete person <?= esc(addslashes($p['name'])) ?>? This will remove their mailbox(s).');">
                    <input type="hidden" name="csrf_token" value="<?=esc($csrf)?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="person_id" value="<?= (int)$p['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                  </form>

                  <!-- Mailbox action button (kept in list only) -->
                  <a class="btn btn-sm btn-outline-secondary" href="?page=mailboxes&person=<?= (int)$p['id'] ?>">Mailbox</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($list)): ?>
              <tr><td colspan="7" class="text-muted">No users found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Person Modal (used for both create and edit) -->
<div class="modal fade" id="personModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered"> <!-- xl width -->
    <div class="modal-content">
      <form method="post" id="personForm">
        <div class="modal-header">
          <h5 class="modal-title" id="personModalTitle">Add User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?=esc($csrf)?>">
          <input type="hidden" name="action" id="formAction" value="create">
          <input type="hidden" name="person_id" id="personId" value="0">

          <div class="container-fluid">
            <!-- Responsive grid: 3 cols on lg, 2 on md, 1 on sm -->
            <div class="row g-3">
              <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label">Name <span id="reqName" class="text-danger d-none">*</span></label>
                <input name="name" id="inputName" class="form-control" >
              </div>

              <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label">Email <span id="reqEmail" class="text-danger d-none">*</span></label>
                <input name="email" id="inputEmail" class="form-control" type="email">
              </div>

              <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label">Phone</label>
                <input name="phone" id="inputPhone" class="form-control">
              </div>

              <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label">Role</label>
                <select name="role" id="inputRole" class="form-select">
                  <option value="sales person">sales person</option>
                  <option value="staff">staff</option>
                  <option value="admin">admin</option>
                  <option value="manager">manager</option>
                </select>
              </div>

              <div class="col-12 col-md-6 col-lg-4">
  <label class="form-label" id="passwordLabel">Password <span id="reqPassword" class="text-danger d-none">*</span></label>

  <!-- input group: password input with inline icon buttons -->
  <div class="position-relative">
    <div class="input-group">
      <input
        name="password"
        id="inputPassword"
        type="password"
        class="form-control"
        value=""
        aria-describedby="passwordHelp"
        autocomplete="new-password"
        style="padding-right: 4.5rem;" 
      >

      <!-- inline icon group (kept visually attached to field) -->
      <span class="input-group-text bg-white border-start-0" id="passwordIcons" style="position: absolute; right: 0.5rem; top: 0.35rem; border-left: 0;">
        <!-- Toggle show/hide -->
        <button type="button" id="btnTogglePass" class="btn btn-sm btn-link p-0 m-0" aria-label="Show password" title="Show password" style="line-height:1;">
          <!-- eye open SVG -->
          <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
          <!-- eye closed (hidden by default) -->
          <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a22.45 22.45 0 0 1 5.88-5.88"/><path d="M1 1l22 22"/></svg>
        </button>

        <!-- copy icon -->
        <button type="button" id="btnCopyPass" class="btn btn-sm btn-link p-0 m-0 ms-2" aria-label="Copy password" title="Copy password" style="line-height:1;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
        </button>
      </span>
    </div>
  </div>

  <!-- small generate text below field -->
  <div class="mt-1">
    <button type="button" id="btnGenPassText" class="btn btn-sm btn-link p-0" style="font-weight:600;">Generate</button>
  </div>

  <div class="form-text" id="passwordHelp">Leave blank to keep current password when editing.</div>
</div>

            </div><!-- /.row -->
          </div><!-- /.container-fluid -->
        </div><!-- /.modal-body -->

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" id="modalSubmitBtn" class="btn btn-primary">Create user</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
/* Ensure inline icon area doesn't look broken and buttons are compact */
#personModal .input-group { position: relative; }
#personModal #passwordIcons { display:flex; align-items:center; gap:0.5rem; pointer-events: none; }
/* make the actual buttons clickable while the wrapper stays absolute */
#personModal #passwordIcons button { pointer-events: auto; color: #495057; background: transparent; border: none; }
#personModal #passwordIcons button:focus { outline: none; box-shadow: none; }
#personModal #passwordIcons svg { display:block; }
#personModal input#inputPassword { transition: padding 0.15s ease; }

/* Slight responsive tweak so icons sit nicely on small widths */
@media (max-width: 576px) {
  #personModal #passwordIcons { top: 0.4rem; right: 0.4rem; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- jQuery fallback: assume header/footer may include jQuery; if not, we include it -->
<script>
if (typeof jQuery === 'undefined') {
  document.write('<script src="https://code.jquery.com/jquery-3.7.1.min.js"><\/script>');
}
</script>

<!-- DataTables JS (simple) -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Cache modal elements
  const personModal = document.getElementById('personModal');
  const modalTitle = document.getElementById('personModalTitle');
  const formAction = document.getElementById('formAction');
  const personId = document.getElementById('personId');
  const inputName = document.getElementById('inputName');
  const inputEmail = document.getElementById('inputEmail');
  const inputPhone = document.getElementById('inputPhone');
  const inputRole = document.getElementById('inputRole');
  const inputPassword = document.getElementById('inputPassword');
  const passwordLabel = document.getElementById('passwordLabel');
  const passwordHelp = document.getElementById('passwordHelp');
  const modalSubmitBtn = document.getElementById('modalSubmitBtn');

  // When Add Person button clicked (clear form)
  document.getElementById('btnAddPerson').addEventListener('click', function() {
    modalTitle.textContent = 'Add User';
    formAction.value = 'create';
    personId.value = 0;
    inputName.value = '';
    inputEmail.value = '';
    inputPhone.value = '';
    inputRole.value = 'sales person';
    inputPassword.value = '';

    // Make Name, Email and Password required for Add
    inputName.required = true;
    inputEmail.required = true;
    inputPassword.required = true;

    // show asterisks
    document.getElementById('reqName').classList.remove('d-none');
    document.getElementById('reqEmail').classList.remove('d-none');
    document.getElementById('reqPassword').classList.remove('d-none');

    // ALWAYS use plain "Password" label for Add
    passwordLabel.textContent = 'Password';
    passwordHelp.style.display = 'block';
    modalSubmitBtn.textContent = 'Create user';
  });

  // When Edit buttons clicked, populate modal using data-person attr
  document.querySelectorAll('.btn-edit-person').forEach(function(btn) {
    btn.addEventListener('click', function(ev) {
      const raw = btn.getAttribute('data-person') || '{}';
      let data;
      try {
        data = JSON.parse(raw);
      } catch(e) {
        data = {};
      }

      modalTitle.textContent = 'Edit User';
      formAction.value = 'update';
      personId.value = data.id || 0;
      inputName.value = data.name || '';
      inputEmail.value = data.email || '';
      inputPhone.value = data.phone || '';
      inputRole.value = data.role || 'sales person';
      inputPassword.value = '';

      // For Edit: password optional on client
      inputName.required = false;
      inputEmail.required = false;
      inputPassword.required = false;

      // hide asterisks for edit
      document.getElementById('reqName').classList.add('d-none');
      document.getElementById('reqEmail').classList.add('d-none');
      document.getElementById('reqPassword').classList.add('d-none');

      // KEEP simple label (remove "(leave blank...)" helper)
      passwordLabel.textContent = 'Password';
      passwordHelp.style.display = 'none';

      modalSubmitBtn.textContent = 'Update user';
    });
  });

  // If the page was loaded with ?edit=ID (non-JS fallback) open modal with server-provided $editing values
  <?php if ($editing): ?>
    (function(){
      var editData = {
        id: <?= (int)$editing['id'] ?>,
        name: <?= json_encode($editing['name'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,
        email: <?= json_encode($editing['email'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,
        phone: <?= json_encode($editing['phone'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,
        role: <?= json_encode($editing['role'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,
      };
      // Fill fields
      modalTitle.textContent = 'Edit User';
      formAction.value = 'update';
      personId.value = editData.id || 0;
      inputName.value = editData.name || '';
      inputEmail.value = editData.email || '';
      inputPhone.value = editData.phone || '';
      inputRole.value = editData.role || 'sales person';
      inputPassword.value = '';

      // For Edit: remove client required and hide asterisks
      inputName.required = false;
      inputEmail.required = false;
      inputPassword.required = false;
      document.getElementById('reqName').classList.add('d-none');
      document.getElementById('reqEmail').classList.add('d-none');
      document.getElementById('reqPassword').classList.add('d-none');

      // KEEP simple label, hide helper
      passwordLabel.textContent = 'Password';
      passwordHelp.style.display = 'none';
      modalSubmitBtn.textContent = 'Update user';

      // Show the modal programmatically (bootstrap 5)
      var bsModal = new bootstrap.Modal(personModal);
      bsModal.show();
    })();
  <?php endif; ?>

  // Simple DataTable init (global search + pagination)
  (function initSimpleDt(){
    function ready(fn) {
      var tries = 0;
      var t = setInterval(function(){
        tries++;
        if (typeof $ !== 'undefined' && $.fn && $.fn.dataTable) {
          clearInterval(t);
          fn();
          return;
        }
        if (tries > 60) clearInterval(t);
      }, 80);
    }

    ready(function(){
      $('#peopleTable').DataTable({
        pageLength: 25,
        lengthMenu: [10,25,50,100],
        columnDefs: [
          { orderable: false, targets: -1 } // actions column not sortable
        ],
        order: [[0, 'desc']]
      });
    });
  })();

  // Auto-login button handler (delegated) -- visible for every person now
  document.addEventListener('click', function(ev) {
    var btn = ev.target.closest('.btn-autologin');
    if (!btn) return;

    var personIdVal = btn.getAttribute('data-person-id');
    if (!personIdVal) return;

    if (!confirm('Open an agent session as this user? This will open a new tab.')) return;

    // UI feedback
    var originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Creating link...';

    // IMPORTANT: use absolute path to your endpoint (adjust if your file is elsewhere)
    var createUrl = 'create_autologin.php';

    var payload = 'person_id=' + encodeURIComponent(personIdVal) + '&csrf_token=' + encodeURIComponent('<?= esc($csrf) ?>');

    fetch(createUrl, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: payload,
      credentials: 'same-origin'
    })
    .then(function(r){
      // if server returned non-JSON (eg 404), try to show useful message
      if (!r.ok) return r.text().then(function(txt){
        throw new Error('Server error: ' + r.status + ' - ' + txt.trim().split('\n')[0]);
      });
      return r.json();
    })
    .then(function(json){
      if (!json || json.error) {
        alert('Auto-login error: ' + (json && json.error ? json.error : 'Unknown error'));
        btn.textContent = originalText;
        btn.disabled = false;
        return;
      }
      // open in new tab
      window.open(json.url, '_blank');
      btn.textContent = 'Opened';
      setTimeout(function(){ btn.textContent = originalText; btn.disabled = false; }, 1400);
    })
    .catch(function(err){
      console.error(err);
      alert('Network / server error creating autologin link:\n' + err.message);
      btn.textContent = originalText;
      btn.disabled = false;
    });
  });

   // --- password helpers (inline icons + generate text) ---
  const btnGenPassText = document.getElementById('btnGenPassText');
  const btnTogglePass = document.getElementById('btnTogglePass');
  const btnCopyPass = document.getElementById('btnCopyPass');
  const eyeOpen = document.getElementById('eyeOpen');
  const eyeClosed = document.getElementById('eyeClosed');

  // defensive checks
  function safeEl(id){ return document.getElementById(id); }
  if (!inputPassword) {
    console.warn('inputPassword not found; password helpers disabled');
  } else {

    function generatePassword(length = 12) {
      const lowers = 'abcdefghijklmnopqrstuvwxyz';
      const uppers = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
      const nums = '0123456789';
      const syms = '!@#$%^&*()-_=+[]{};:,.<>/?';
      const all = lowers + uppers + nums + syms;
      let pw = '';
      pw += lowers[Math.floor(Math.random() * lowers.length)];
      pw += uppers[Math.floor(Math.random() * uppers.length)];
      pw += nums[Math.floor(Math.random() * nums.length)];
      pw += syms[Math.floor(Math.random() * syms.length)];
      for (let i = pw.length; i < length; i++) pw += all[Math.floor(Math.random() * all.length)];
      return pw.split('').sort(() => 0.5 - Math.random()).join('');
    }

    // Generate: set value and briefly show visual feedback
    if (btnGenPassText) {
      btnGenPassText.addEventListener('click', function(e) {
        e.preventDefault();
        const pw = generatePassword(12);
        inputPassword.value = pw;

        // briefly show the generated password (optional)
        const prevType = inputPassword.type;
        inputPassword.type = 'text';
        // switch eye icons to 'open' state
        if (eyeOpen) eyeOpen.style.display = '';
        if (eyeClosed) eyeClosed.style.display = 'none';
        btnTogglePass && btnTogglePass.setAttribute('aria-pressed', 'true');

        setTimeout(() => {
          // return to previous type (keep it visible if user toggled)
          if (prevType === 'password') {
            inputPassword.type = 'password';
            if (eyeOpen) eyeOpen.style.display = '';
            if (eyeClosed) eyeClosed.style.display = 'none';
            btnTogglePass && btnTogglePass.setAttribute('aria-pressed', 'false');
          }
        }, 3500); // show for 3.5s
        // tiny feedback on button
        const old = btnGenPassText.textContent;
        btnGenPassText.textContent = 'Generated';
        setTimeout(()=> btnGenPassText.textContent = old, 1200);
      });
    }

    // Toggle show/hide
    if (btnTogglePass) {
      btnTogglePass.addEventListener('click', function(e) {
        e.preventDefault();
        if (inputPassword.type === 'password') {
          inputPassword.type = 'text';
          if (eyeOpen) eyeOpen.style.display = 'none';
          if (eyeClosed) eyeClosed.style.display = '';
          btnTogglePass.setAttribute('aria-pressed','true');
          btnTogglePass.title = 'Hide password';
        } else {
          inputPassword.type = 'password';
          if (eyeOpen) eyeOpen.style.display = '';
          if (eyeClosed) eyeClosed.style.display = 'none';
          btnTogglePass.setAttribute('aria-pressed','false');
          btnTogglePass.title = 'Show password';
        }
      });
    }

    // Copy to clipboard
    if (btnCopyPass) {
      btnCopyPass.addEventListener('click', function(e) {
        e.preventDefault();
        const val = inputPassword.value || '';
        if (!val) {
          // quick feedback
          const old = btnCopyPass.innerHTML;
          btnCopyPass.innerHTML = 'Empty';
          setTimeout(()=> btnCopyPass.innerHTML = old, 900);
          return;
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(val).then(function() {
            const old = btnCopyPass.innerHTML;
            btnCopyPass.innerHTML = 'Copied';
            setTimeout(()=> btnCopyPass.innerHTML = old, 1200);
          }).catch(function(err){
            console.error('copy failed', err);
            alert('Unable to copy to clipboard.');
          });
        } else {
          // fallback
          const ta = document.createElement('textarea');
          ta.value = val;
          document.body.appendChild(ta);
          ta.select();
          try {
            document.execCommand('copy');
            const old = btnCopyPass.innerHTML;
            btnCopyPass.innerHTML = 'Copied';
            setTimeout(()=> btnCopyPass.innerHTML = old, 1200);
          } catch(e) {
            alert('Copy failed — please copy manually.');
          }
          document.body.removeChild(ta);
        }
      });
    }
  } // end if inputPassword

});
</script>
