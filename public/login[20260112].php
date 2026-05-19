<?php
// agent/login.php (top)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!empty($_SESSION['person_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// optional: set small page title
$page_title = 'Agent sign in';
require_once __DIR__ . '/includes/header_public.php';

// Handle POST login after includes/header (safe because session is active)
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../config/functions.php';

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $msg = 'Email and password required.';
    } else {
        $stmt = $mysqli->prepare('SELECT id, name, password_hash FROM people WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($r && password_verify($password, $r['password_hash'])) {
          $kip = $_SERVER['REMOTE_ADDR'];
          if($email=="kavitha@nasruae.com" && $kip!="2.50.17.9") { // kavitha@nasruae.com
            $msg = 'Access Denied, Please contact Admin';
          }
          else {
            // regenerate and set session
            session_regenerate_id(true);
            $_SESSION['person_id'] = (int)$r['id'];
            $_SESSION['person_name'] = $r['name'];

            $stmt1 = $mysqli->prepare("SELECT id FROM mailboxes WHERE person_id = ?");
            $stmt1->bind_param("i", $r['id']);
            $stmt1->execute();
            $stmt1->bind_result($mailbox_id);
            $stmt1->fetch();
            $stmt1->close();

            $site->agent_log("Logged in");

            $_SESSION['person_mailbox_id'] = $mailbox_id;

            // redirect to dashboard (use the same target as above)
            header('Location: index.php?page=daily_interactions');
            exit;
          }
        } else {
            $msg = 'Invalid credentials.';
        }
    }
}
?>
<div class='row justify-content-center'>
  <div class='col-md-5'>
    <div class='card shadow-sm'>
      <div class='card-body'>
        <h4 class='mb-3'>Agent sign in</h4>
        <?php if($msg): ?>
          <div class='alert alert-danger'><?=htmlspecialchars($msg)?></div>
        <?php endif; ?>
        <form method='post' autocomplete="on">
          <div class='mb-2'>
            <label class='form-label'>Email</label>
            <input name='email' type='email' class='form-control' required>
          </div>
          <div class='mb-2'>
            <label class='form-label'>Password</label>
            <input name='password' type='password' class='form-control' required>
          </div>
          <div class='d-grid'>
            <button class='btn btn-primary'>Sign in</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer_public.php'; ?>