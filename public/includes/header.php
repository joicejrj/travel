<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../_auth.php';

/* -----------------------
   MODE SWITCH (BUY/SELL)
------------------------ */

if (isset($_GET['mode']) && in_array($_GET['mode'], ['buy','sell'])) {
    $_SESSION['portal_mode'] = $_GET['mode'];
}

$portal_mode  = $_SESSION['portal_mode'] ?? 'sell';
$is_buy_mode  = $portal_mode === 'buy';
$is_sell_mode = $portal_mode === 'sell';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Travel Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css"/>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

<style>
body { background-color: #f8f9fa; }
.navbar-brand { font-weight: 600; }
.navbar-light .navbar-nav .nav-link.active {
  color: #0d6efd !important;
  font-weight: 600;
}
.navbar-light .navbar-nav .nav-link.active i {
  color: #0d6efd !important;
}

/* Existing pill styles preserved */
.bg-warning,.bg-primary,.bg-info,.bg-secondary,.bg-success,.bg-danger{
display:inline-block!important;padding:5px 12px!important;
font-size:12px!important;font-weight:600!important;
border-radius:20px!important;margin-right:8px!important;
background:rgba(0,0,0,0.05)!important;}
.bg-warning{background:#fff3cd!important;color:#000!important;}
.bg-primary{background:#cfe2ff!important;color:#084298!important;}
.bg-info{background:#cff4fc!important;color:#055160!important;}
.bg-secondary{background:#e2e3e5!important;color:#41464b!important;}
.bg-success{background:#d1e7dd!important;color:#0f5132!important;}
.bg-danger{background:#f8d7da!important;color:#842029!important;}

.notification-dropdown{width:420px;max-width:95vw;}
.notification-body{max-height:420px;overflow-y:auto;}
.notification-item:hover{background:#f8f9fa;}
.notification-item.opacity-50{opacity:0.6;}
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm mb-4 <?=isset($_GET['tabview'])?'d-none':''?>">
<div class="container-fluid">

<a class="navbar-brand fw-semibold text-primary1" href="index.php?page=bookings">
<i class="fas fa-paper-plane me-2 text-primary1"></i>Travel Portal
</a>

<button class="navbar-toggler border-0" type="button"
data-bs-toggle="collapse" data-bs-target="#mainNav">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="mainNav">

<?php $current_page = $_GET['page'] ?? ''; ?>

<!-- LEFT MENU -->
<ul class="navbar-nav me-auto mb-2 mb-lg-0">

<?php if ($is_sell_mode): ?>

<li class="nav-item">
<a class="nav-link<?= $current_page==='bookings'?' active fw-semibold text-primary':'' ?>" href="?page=bookings">
<i class="fas fa-paper-plane me-1 text-secondary"></i>Bookings
</a>
</li>

<li class="nav-item">
<a class="nav-link<?= $current_page==='bookings_add'?' active fw-semibold text-primary':'' ?>" href="?page=bookings_add">
<i class="fas fa-plus me-1 text-secondary"></i>Add Booking
</a>
</li>

<li class="nav-item">
<a class="nav-link<?= in_array($current_page,['customers','customer_view'])?' active fw-semibold text-primary':'' ?>" href="?page=customers">
<i class="fas fa-users me-1 text-secondary"></i>Customers
</a>
</li>

<!-- <li class="nav-item">
<a class="nav-link<?= $current_page==='packages'?' active fw-semibold text-primary':'' ?>" href="?page=packages">
<i class="fas fa-plane me-1 text-secondary"></i>Packages
</a>
</li> -->
<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle<?= in_array($current_page, ['packages','packages_custom','packages_website']) ? ' active fw-semibold text-primary' : '' ?>" 
     href="#" 
     role="button" 
     data-bs-toggle="dropdown" 
     aria-expanded="false">
    <i class="fas fa-plane me-1 text-secondary"></i>Packages
  </a>
  <ul class="dropdown-menu">
    <li>
      <a class="dropdown-item" 
         href="?page=packages">
        Packages
      </a>
    </li>
    <li>
      <a class="dropdown-item" 
         href="?page=packages_custom">
        Custom Packages
      </a>
    </li>
    <li>
      <a class="dropdown-item" 
         href="?page=packages_website">
        Website Packages
      </a>
    </li>
  </ul>
</li>

<li class="nav-item">
<a class="nav-link<?= $current_page==='bookings_payments'?' active fw-semibold text-primary':'' ?>" href="?page=bookings_payments">
<i class="fas fa-credit-card me-1 text-secondary"></i>Payments
</a>
</li>

<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle text-dark d-flex align-items-center
<?= in_array($current_page,['zones','discounts','bank_accounts','document_templates','documents_created'])?' active fw-semibold text-primary':'' ?>"
href="#" id="configDropdown" role="button" data-bs-toggle="dropdown">
<i class="fas fa-cog me-2 text-secondary"></i>Config
</a>
<ul class="dropdown-menu shadow-sm">
<li><a class="dropdown-item" href="?page=zones">Area Zones</a></li>
<li><a class="dropdown-item" href="?page=discounts">Discounts</a></li>
<li><a class="dropdown-item" href="?page=bank_accounts">Bank Accounts</a></li>
<li><a class="dropdown-item" href="?page=document_templates">Document Templates Manager</a></li>
<li><a class="dropdown-item" href="?page=documents_created">Documents Created</a></li>
<li><a class="dropdown-item" href="?page=airports_settings">Airports</a></li>
</ul>
</li>

<?php endif; ?>

<?php if ($is_buy_mode): ?>

<li class="nav-item">
<a class="nav-link<?= in_array($current_page,['customers','customer_view'])?' active fw-semibold text-primary':'' ?>" href="?page=customers">
<i class="fas fa-users me-1 text-secondary"></i>Customers
</a>
</li>

<li class="nav-item">
<a class="nav-link<?= $current_page==='suppliers'?' active fw-semibold text-primary':'' ?>" href="?page=suppliers">
<i class="fas fa-truck me-1 text-secondary"></i>Suppliers
</a>
</li>

<li class="nav-item">
<a class="nav-link<?= $current_page==='products'?' active fw-semibold text-primary':'' ?>" href="?page=products">
<i class="fas fa-boxes me-1 text-secondary"></i>Products
</a>
</li>

<!-- <li class="nav-item">
<a class="nav-link<?= $current_page==='packages'?' active fw-semibold text-primary':'' ?>" href="?page=packages">
<i class="fas fa-plane me-1 text-secondary"></i>Packages
</a>
</li> -->
<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle<?= in_array($current_page, ['packages','packages_custom','packages_website']) ? ' active fw-semibold text-primary' : '' ?>" 
     href="#" 
     role="button" 
     data-bs-toggle="dropdown" 
     aria-expanded="false">
    <i class="fas fa-plane me-1 text-secondary"></i>Packages
  </a>
  <ul class="dropdown-menu">
    <li>
      <a class="dropdown-item" 
         href="?page=packages">
        Packages
      </a>
    </li>
    <li>
      <a class="dropdown-item" 
         href="?page=packages_custom">
        Custom Packages
      </a>
    </li>
    <li>
      <a class="dropdown-item" 
         href="?page=packages_website">
        Website Packages
      </a>
    </li>
  </ul>
</li>

<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle text-dark d-flex align-items-center"
href="#" id="configDropdown" role="button" data-bs-toggle="dropdown">
<i class="fas fa-cog me-2 text-secondary"></i>Config
</a>
<ul class="dropdown-menu shadow-sm">
<li><a class="dropdown-item" href="?page=zones">Area Zones</a></li>
<li><a class="dropdown-item" href="?page=discounts">Discounts</a></li>
<li><a class="dropdown-item" href="?page=bank_accounts">Bank Accounts</a></li>
<li><a class="dropdown-item" href="?page=document_templates">Document Templates Manager</a></li>
<li><a class="dropdown-item" href="?page=documents_created">Documents Created</a></li>
<li><a class="dropdown-item" href="?page=airports_settings">Airports</a></li>
</ul>
</li>

<?php endif; ?>

</ul>

<!-- RIGHT SIDE -->
<ul class="navbar-nav ms-auto align-items-center">

<?php if ($is_buy_mode) { ?>
<!-- NOTIFICATIONS (UNCHANGED STRUCTURE) -->
<li class="nav-item dropdown me-2">
<a class="nav-link position-relative"
href="#" id="notificationDropdown"
role="button" data-bs-toggle="dropdown"
data-bs-auto-close="outside">

<i class="fa-solid fa-bell fs-5 text-secondary"></i>
<span class="position-absolute top-2 start-100 translate-middle badge rounded-pill bg-danger"
style="font-size:10px!important;padding:3px 6px!important;">0</span>
</a>

<div class="dropdown-menu dropdown-menu-end shadow-lg p-0 notification-dropdown">

<div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
<strong class="small">Notifications</strong>
<button id="notifMarkAll" class="btn btn-sm btn-outline-secondary d-none1"
style="padding:0.3em;font-size:0.8em;">Mark all as read</button>
</div>

<ul class="nav nav-tabs nav-justified small px-2 pt-2">
<li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nt-reminders">
Reminder <span class="badge bg-secondary ms-1" id="cnt-reminders">0</span></button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nt-bookings">
Bookings <span class="badge bg-secondary ms-1" id="cnt-bookings">0</span></button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nt-documents">
Docs <span class="badge bg-secondary ms-1" id="cnt-documents">0</span></button></li>
</ul>

<div class="tab-content notification-body">
<div class="tab-pane fade show active" id="nt-reminders">
<div class="list-group list-group-flush" id="list-reminders"></div>
</div>
<div class="tab-pane fade" id="nt-bookings">
<div class="list-group list-group-flush" id="list-bookings"></div>
</div>
<div class="tab-pane fade" id="nt-documents">
<div class="list-group list-group-flush" id="list-documents"></div>
</div>
<div class="tab-pane fade" id="nt-documents">
<div class="list-group list-group-flush" id="list-requirements"></div>
</div>
</div>

<div class="border-top text-center py-2 bg-light">
<a href="?page=dashboard" class="small fw-semibold text-decoration-none">
View all notifications
</a>
</div>

</div>
</li>
<?php } ?>

<!-- MODE SWITCH -->
<li class="nav-item me-3">
<div class="btn-group btn-group-sm">
<a href="?page=packages&mode=buy" class="btn <?= $is_buy_mode?'btn-primary':'btn-outline-primary' ?>">Buy</a>
<a href="?page=bookings&mode=sell" class="btn <?= $is_sell_mode?'btn-primary':'btn-outline-primary' ?>">Sell</a>
</div>
</li>

<!-- USER -->
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle text-dark d-flex align-items-center"
href="#" data-bs-toggle="dropdown">
<i class="fas fa-user-circle me-1 text-primary"></i>
<?= htmlspecialchars($CURRENT_USER_NAME ?? 'Agent') ?>
</a>
<ul class="dropdown-menu dropdown-menu-end shadow-sm">
<li>
<a class="dropdown-item text-danger" href="index.php?page=logout">
<i class="fas fa-sign-out-alt me-2"></i>Logout
</a>
</li>
</ul>
</li>

</ul>

</div>
</div>
</nav>

<div class="container-fluid <?=isset($_GET['tabview'])?'mt-4':''?>">
