<?php
// Email_fetch/index.php — front controller & router for subfolder deployment
// Place this file in: http://localhost/Email_fetch/index.php
// Project structure expected under the same folder: config/, lib/, public/, inbound/

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../lib/helpers.php';

// Helpers
function base_href(){
  // e.g. /Email_fetch/
  $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
  return ($dir ? $dir : '') . '/';
}
function route($p){ return base_href().'index.php?page='.rawurlencode($p); }

// Replace absolute paths from included pages so they work in subfolder
function rewrite_for_subdir($html){
  $base = base_href();
  $map = [
    // nav pages
    'href="/dashboard.php"'   => 'href="'.route('dashboard').'"',
    'href="/compose_mail.php"'       => 'href="'.route('compose').'"',
    'href="/mailboxes.php"'   => 'href="'.route('mailboxes').'"',
    'href="/download_attachment.php"'      => 'href="'.route('download_attachment').'"',
    'href="/email_log.php"'   => 'href="'.route('email_log').'"',
    'href="/logout.php"'      => 'href="'.route('logout').'"',
    'href="/index.php"'       => 'href="'.route('login').'"',
    'href="/email_view.php?' => 'href="'.route('email_view').'&"',
    'href="/email_view.php"' => 'href="'.route('email_view').'"',
    'href="/carriers.php"' => 'href="'.route('suppliers').'"',
    'href="/carriers_add.php"' => 'href="'.route('suppliers_add').'"',
    'href="/carriers_view.php"' => 'href="'.route('suppliers_view').'"',
    'href="/carriers_archived.php"' => 'href="'.route('suppliers_archived').'"',
    'href="/people.php"' => 'href="'.route('users').'"',
    'href="/people_logs.php"' => 'href="'.route('users_logs').'"',
    'href="/email_report.php"' => 'href="'.route('reports').'"',
    'href="/settings.php"' => 'href="'.route('settings').'"',
    'href="/works.php"' => 'href="'.route('works').'"',
    'href="/works_add.php"' => 'href="'.route('works_add').'"',
    'href="/document_templates.php"' => 'href="'.route('document_templates').'"',
    
   'href="/payment_settings.php"' => 'href="'.route('payment_settings').'"',

    // forms (login/setup)
    'action="/login.php"'       => 'action="'.route('login_action').'"',
    'action="/setup_admin.php"' => 'action="'.route('setup_admin_action').'"',

    // assets
    'href="/assets/'  => 'href="'.$base.'public/assets/',
    'src="/assets/'   => 'src="'.$base.'public/assets/',
    // includes (edge)
    '"/includes/'     => '"'.$base.'public/includes/',
  ];
  return strtr($html, $map);
}

// Simple user checks
function has_users(mysqli $db){
  $r = $db->query("SELECT COUNT(*) c FROM users");
  if ($r && ($row=$r->fetch_assoc())) return (int)$row['c'] > 0;
  return false;
}
function is_logged_in(){ return !empty($_SESSION['user']); }

// Routing
$page = strtolower(trim($_GET['page'] ?? ''));

// Handle actions that must not include the old files (to avoid absolute Location headers)
if ($page === 'login_action') {
  $email = trim($_POST['email'] ?? '');
  $pass  = (string)($_POST['password'] ?? '');
  if ($email && $pass){
    $stmt = $mysqli->prepare("SELECT id,name,email,password_hash,role FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email); $stmt->execute();
    if ($u = $stmt->get_result()->fetch_assoc()){
      if (password_verify($pass, $u['password_hash'])){
        $mysqli->query("update users set login_at='".date("Y-m-d H:i:s")."' where id='".$u['id']."'");
        $_SESSION['user'] = ['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];
        header('Location: '.route('users')); exit;
      }
    }
  }
  header('Location: '.route('login').'&err=1'); exit;
}

if ($page === 'autologin'&& isset($_GET['token'])) {
  $login_token = trim($_GET['token'] ?? '');
  if ($login_token!=''){
    $stmt = $mysqli->prepare("SELECT id,name,email,password_hash,role FROM users WHERE login_token=? LIMIT 1");
    $stmt->bind_param("s", $login_token); $stmt->execute();
    if ($u = $stmt->get_result()->fetch_assoc()){
      $mysqli->query("update users set login_at='".date("Y-m-d H:i:s")."' where id='".$u['id']."'");
      $_SESSION['user'] = ['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];
      header('Location: '.route('users')); exit;
    }
  }
  header('Location: '.route('login').'&err=1'); exit;
}

if ($page === 'setup_admin_action'&&1==2){
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass = (string)($_POST['password'] ?? '');
  if ($name && $email && $pass){
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,'admin')");
    $stmt->bind_param("sss", $name, $email, $hash);
    if ($stmt->execute()){
      $_SESSION['user'] = ['id'=>$stmt->insert_id, 'name'=>$name, 'email'=>$email, 'role'=>'admin'];
      header('Location: '.route('dashboard')); exit;
    }
  }
  header('Location: '.route('setup_admin').'&err=1'); exit;
}

if ($page === 'logout'){
  session_destroy();
  header('Location: '.route('login')); exit;
}

// Decide which page to show
$want_login = (!$page || $page==='login');
$first_run = !has_users($mysqli);

if (!is_logged_in()){
  if ($first_run){
    // First-time setup screen
    $err = !empty($_GET['err']);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
      <title>CRM — Login</title>
      <link rel="stylesheet" href="<?php echo base_href(); ?>../public/assets/css/style.css">
    </head>
    <body class="login-page">
      <div class="login-box">
        <h2>Set up Admin</h2>
          <div class="notice">No users found. Create the first admin account.</div>
          <?php if ($err): ?><div class="error">Invalid email or password.</div><?php endif; ?>
        <form method="post" action="<?php echo route('setup_admin_action'); ?>">
          <label>Name</label>
          <input type="text" name="name" placeholder="Your name" placeholder="Your name" required>
          <label>Email</label>
          <input type="email" name="email" placeholder="you@company.com" required>
          <label>Password</label>
          <input type="password" name="password" placeholder="Create a strong password" required>
          <button type="submit">Login</button>
        </form>
      </div>
    </body>
    </html>
    <?php
    exit;
  }

  // Login screen
  $err = !empty($_GET['err']);
  ?>
  <!DOCTYPE html>
  <html>
  <head>
    <title>CRM — Login</title>
    <link rel="stylesheet" href="<?php echo base_href(); ?>../public/assets/css/style.css">
  </head>
  <body class="login-page">
    <div class="login-box">
      <h2>CRM — Login</h2>
        <?php if ($err): ?><div class="error">Invalid email or password.</div><?php endif; ?>
        <form method="post" action="<?php echo route('login_action'); ?>">
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
      </form>
    </div>
  </body>
  </html>
  <?php
  exit;
}

// Logged in: include target app page and rewrite links/assets for subfolder
$map = [
  'dashboard'  => __DIR__.'/dashboard.php',
  'compose'      => __DIR__.'/compose_mail.php',
  'mailboxes'  => __DIR__.'/mailboxes.php',
  'download_attachment'     => __DIR__.'/download_attachment.php',
  'email_log'  => __DIR__.'/email_log.php',
  'email_view' => __DIR__.'/email_view.php',
  'ajax_sync'  => __DIR__.'/ajax_sync.php',
  'carriers'  => __DIR__.'/suppliers.php',
  'carriers_add'  => __DIR__.'/suppliers_add.php',
  'carriers_view'  => __DIR__.'/suppliers_view.php',
  'carriers_archived'  => __DIR__.'/suppliers_archived.php',
  'users'  => __DIR__.'/people.php',
  'users_logs'  => __DIR__.'/people_logs.php',
  'reports'  => __DIR__.'/email_report.php',
  'job_categories_fav'  => __DIR__.'/job_categories_fav.php',
  'settings'  => __DIR__.'/settings.php',
  'jobs_add'  => __DIR__.'/jobs_add.php',
  'works'  => __DIR__.'/works.php',
  'works_add'  => __DIR__.'/works_add.php',
  'document_templates'  => __DIR__.'/document_templates.php',
  'payment_settings'  => __DIR__.'/payment_settings.php',
];

if (!isset($map[$page])) { $page = 'users'; }

ob_start();
include $map[$page];
$html = ob_get_clean();

// Rewrite absolute URLs from the included pages to our router routes and subdir assets
echo rewrite_for_subdir($html);
?>