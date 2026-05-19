<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../lib/helpers.php';
if (current_user()) { header("Location: ?page=users"); exit; }
$has_users = false;
$res = $mysqli->query("SELECT COUNT(*) c FROM users");
if ($res && ($row=$res->fetch_assoc())) $has_users = ($row['c']>0);
$err = $_GET['err'] ?? null;
?><!DOCTYPE html><html><head><meta charset="utf-8"><title>Email CRM — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css"></head><body>
<?php if (!$has_users): ?>
<div class="formbox">
  <h2>Set up Admin</h2>
  <div class="notice">No users found. Create the first admin account.</div>
  <?php if ($err): ?><div class="notice">Couldn’t create admin. Try again.</div><?php endif; ?>

  <form method="post" action="<?php echo route('setup_admin_action'); ?>" class="formgrid">
    <div class="row">
      <label>Name</label>
      <input type="text" name="name" placeholder="Your name" required>
    </div>
    <div class="row">
      <label>Email</label>
      <input type="email" name="email" placeholder="you@company.com" required>
    </div>
    <div class="row">
      <label>Password</label>
      <input type="password" name="password" placeholder="Create a strong password" required>
    </div>
    <div style="margin-top:6px">
      <button class="button" type="submit">Create Admin</button>
    </div>
  </form>
</div>
<?php else: ?>
<div class="formbox">
  <h2>Login</h2>
  <?php if ($err): ?><div class="notice">Invalid email or password.</div><?php endif; ?>

  <form method="post" action="<?php echo route('login_action'); ?>" class="formgrid">
    <div class="row">
      <label>Email</label>
      <input type="email" name="email" placeholder="you@company.com" required>
    </div>
    <div class="row">
      <label>Password</label>
      <input type="password" name="password" placeholder="Your password" required>
    </div>
    <div style="margin-top:6px">
      <button class="button" type="submit">Login</button>
    </div>
  </form>
</div>
<?php endif; ?></body></html>
