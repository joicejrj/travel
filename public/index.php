<?php
// agent/index.php - simple whitelist router for the Agent Portal
declare(strict_types=1);

session_start();

// base dir for pages
$pagesDir = __DIR__;

// whitelist of allowed pages -> map to PHP file
$pages = [
  'login'            => 'login.php',
  'dashboard'        => 'dashboard.php',
  'email_log'        => 'email_log_user.php',
  'reminder_action'  => 'reminder_action.php',
  'logout'           => 'logout.php',
  // 'carriers'         => 'carriers.php',
  // 'carriers_add'     => 'carriers_add.php',
  // 'carriers_view'    => 'carriers_view.php',
  'contacts'         => 'contacts.php',
  'contacts_add'     => 'contacts_add.php',
  'contacts_reminders_add'     => 'contacts_reminders_add.php',
  'contacts_view'    => 'contacts_view.php',
  'download_attachment' => 'download_attachment.php',
  'email_view'       => 'email_view.php',
  'whatsapp_contacts_list'       => 'whatsapp_contacts_list.php',
  'daily_interactions'       => 'daily_interactions.php',
  'contacts'       => 'public/contacts.php',
  'contacts_view'       => 'public/contacts_view.php',
  'daily_interactions1'       => 'daily_interactions1.php',
  'attendance'       => 'attendance.php',
  'customer_timesheet'       => 'customer_timesheet.php',

  
  // add any other safe page keys here if needed
];

// if user is logged in, default page is dashboard, otherwise login
$defaultPage = !empty($_SESSION['person_id']) ? 'dashboard' : 'login';

// get requested page key (safe)
$request = $_GET['page'] ?? $defaultPage;
if (!is_string($request)) $request = $defaultPage;
$request = strtolower(trim($request, " \t\n\r\0\x0B/"));

// choose file by whitelist, fallback to default
$includeFile = $pages[$request] ?? $pages[$defaultPage];

// Simple access control: if page requires auth but user not logged in, redirect to login
$publicPages = ['login'];
$needsAuth = !in_array($request, $publicPages, true);
if ($needsAuth && empty($_SESSION['person_id'])) {
    // redirect to router login preserving return (optional)
    header('Location: index.php?page=login');
    exit;
}

// Finally include header/footer within pages will be responsible for HTML framing
$fullPath = $pagesDir . DIRECTORY_SEPARATOR . $includeFile;
if (!file_exists($fullPath)) {
    // graceful fallback — show 404 style message
    http_response_code(404);
    echo "<h1>Page not found</h1><p>Requested page '{$request}' not found.</p>";
    exit;
}

// include the page (pages themselves may include header/footer and rely on session)
require $fullPath;
?>