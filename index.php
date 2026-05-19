<?php
// agent/index.php - simple whitelist router for the Agent Portal
declare(strict_types=1);

session_start();

// base dir for pages
$pagesDir = __DIR__;

// whitelist of allowed pages -> map to PHP file
$pages = [
  'login'            => 'public/login.php',
  'dashboard'        => 'public/dashboard.php',
  'email_log'        => 'public/email_log_user.php',
  'reminder_action'  => 'public/reminder_action.php',
  'logout'           => 'public/logout.php',
  'customers'         => 'public/customers.php',
  'customers_add'     => 'public/customers_add.php',
  'customers_view'    => 'public/customers_view.php',
  'suppliers'         => 'public/suppliers.php',
  'suppliers_add'     => 'public/suppliers_add.php',
  'suppliers_view'    => 'public/suppliers_view.php',
  'download_attachment' => 'public/download_attachment.php',
  'email_view'       => 'public/email_view.php',
  'payments'       => 'public/payments.php',
  'invoices'       => 'public/invoices.php',
  'payment_categories'       => 'public/payment_categories.php',
  'job_titles'       => 'public/job_titles.php',
  'bank_accounts'       => 'public/bank_accounts.php',
  'document_labels'       => 'public/document_labels.php',
  'incident_categories'       => 'public/incident_categories.php',
  'whatsapp_contacts_list'       => 'public/whatsapp_contacts_list.php',
  'daily_interactions'       => 'public/daily_interactions.php',
  'document_templates'       => 'public/document_templates.php',
  'documents_created'       => 'public/documents_created.php',
  'contacts'       => 'public/contacts.php',
  'contacts_view'       => 'public/contacts_view.php',
  'daily_interactions1'       => 'public/daily_interactions1.php',
  'customer_timesheet'       => 'public/customer_timesheet.php',
  'admin_dashboard'       => 'public/admin_dashboard.php',
  'customer_site_rates'       => 'public/customer_site_rates.php',
  'customer_invoices'       => 'public/customer_invoices.php',
  'applicants'       => 'public/applicants.php',
  'customers_soa'       => 'public/customers_soa.php',
  'bookings'       => 'public/bookings.php',
  'bookings_add'       => 'public/bookings_add.php',
  'bookings_view'       => 'public/bookings_view.php',
  'bookings_payments'       => 'public/bookings_payments.php',
  'tour_packages'       => 'public/tour_packages.php',
  'products'       => 'public/products.php',
  'products_add'       => 'public/products_add.php',
  'products_view'       => 'public/products_view.php',
  'packages'       => 'public/packages.php',
  'packages_custom'       => 'public/packages_custom.php',
  'packages_website'       => 'public/packages_website.php',
  'packages_add'       => 'public/packages_add.php',
  'packages_view'       => 'public/packages_view.php',
  'zones'       => 'public/zones.php',
  'discounts'       => 'public/discounts.php',
  'airports_settings'       => 'public/airports_settings.php',
  
  // add any other safe page keys here if needed
];



// Role-based access control
$role = $_SESSION['person_role'] ?? '';
// Pages allowed for staff / sales person
$staffAllowedPages = [
    'dashboard',
    'bookings',
    'bookings_add',
    'customers',
    'customers_add',
    'customers_view',
    'packages',
    'packages_custom',
    'packages_website',
    'packages_add',
    'packages_view',
    'logout'
];
// Admin & Manager get full access (no restriction needed)
$isAdminOrManager = in_array($role, ['admin', 'manager'], true);
$isStaffOrSales   = in_array($role, ['staff', 'sales person'], true);


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


// Role restriction (after login check)
// if ($needsAuth && !$isAdminOrManager) {

//     if ($isStaffOrSales) {

//         if (!in_array($request, $staffAllowedPages, true)) {
//             http_response_code(403);
//             require_once __DIR__ . '/public/includes/header.php';
//             echo "<h1>403 - Access Denied</h1>
//                   <p>You do not have permission to access this page.</p>";
//             require_once __DIR__ . '/public/includes/footer.php';
//             exit;
//         }

//     } else {
//         // Unknown role → deny everything except logout
//         if ($request !== 'logout') {
//             http_response_code(403);
//             require_once __DIR__ . '/public/includes/header.php';
//             echo "<h1>403 - Access Denied</h1>";
//             require_once __DIR__ . '/public/includes/footer.php';
//             exit;
//         }
//     }
// }


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