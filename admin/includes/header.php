<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/auth.php';
$u = current_user();
$last_inbound = system_kv_get($mysqli, 'last_inbound_email_at');
?>
<!DOCTYPE html><html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">

<!DOCTYPE html>
<html>
<head>
  <title>CRM</title>
  <link rel="stylesheet" href="../public/assets/css/style.css?cv=<?=time()?>">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="…" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>


  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css"/>
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
  
  <script src="../public/assets/js/app.js?jv=<?=time()?>"></script>
<style>
  /* Navbar base */
  .custom-navbar {
    background: #1f3c88; /*linear-gradient(90deg, #0d6efd, #0a58ca);*/
    padding: 0.6rem 1rem;
  }

  .custom-navbar .navbar-brand {
    color: #fff !important;
    font-size: 1.25rem;
    letter-spacing: 0.5px;
  }

  .custom-navbar .nav-link {
    color: #f8f9fa !important;
    margin-right: 0.8rem;
    transition: color 0.2s ease-in-out;
  }

  .custom-navbar .nav-link:hover,
  .custom-navbar .nav-link.active {
    color: #ffd966 !important;
  }

  .custom-navbar .dropdown-menu {
    border-radius: 0.5rem;
    border: none;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
  }

  .custom-navbar .dropdown-item:hover {
    background-color: #f1f1f1;
  }

  .custom-navbar .navbar-toggler {
    border: none;
  }

  .custom-navbar .navbar-toggler:focus {
    box-shadow: none;
  }

</style>
</head>
<body>
<!-- <header class="main-header"> -->
  <!-- <div class="brand">CRM</div> -->

<!-- Navbar -->
<nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
  <div class="container-fluid">
    <!-- Brand -->
    <a class="navbar-brand fw-bold" href="">
      <i class="fa-solid fa-envelope me-2"></i>CRM
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Links -->
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/dashboard.php">Dashboard</a></li>
        <!-- <li class="nav-item"><a class="nav-link" href="/leads.php">Leads</a></li> -->
        <!-- <li class="nav-item"><a class="nav-link" href="/customers.php">Customers</a></li> -->
        <!-- <li class="nav-item"><a class="nav-link" href="/orders.php">Orders</a></li> -->
        <!-- <li class="nav-item"><a class="nav-link" href="./?page=email_log">Email Log</a></li> -->
        <!-- <li class="nav-item"><a class="nav-link" href="/?page=compose">Compose Email</a></li> -->

        <!-- Jobs Dropdown -->
        <!-- <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="jobsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Jobs
          </a>
          <ul class="dropdown-menu" aria-labelledby="jobsDropdown">
            <li><a class="dropdown-item" href="?page=jobs">Jobs</a></li>
            <li><a class="dropdown-item" href="?page=job_categories">Job Categories</a></li>
          </ul>
        </li> -->

        <!-- <li class="nav-item"><a class="nav-link" href="/?page=carriers">Carriers</a></li>
        <li class="nav-item"><a class="nav-link" href="/?page=carriers_add">Add Carriers</a></li>

        <li class="nav-item"><a class="nav-link" href="/?page=reports">Reports</a></li>-->

        <li class="nav-item"><a class="nav-link" href="./?page=users">Users</a></li>
        <li class="nav-item"><a class="nav-link" href="./?page=users_logs">Users Activities</a></li>
        <li class="nav-item"><a class="nav-link" href="./?page=document_templates">Document Templates</a></li>
        
        <li class="nav-item"><a class="nav-link" href="./?page=settings">Settings</a></li>
    <li class="nav-item"><a class="nav-link" href="./?page=payment_settings">Payment Settings</a></li>
        <!-- <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="jobsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Carriers
          </a>
          <ul class="dropdown-menu" aria-labelledby="jobsDropdown">
            <li><a class="dropdown-item" href="/carriers.php">Carriers</a></li>
            <li><a class="dropdown-item" href="?page=carriers_add">Add Carriers</a></li>
          </ul>
        </li> -->

        <!-- <li class="nav-item"><a class="nav-link" href="/works.php">Work</a></li> -->
      </ul>

      <!-- User Dropdown -->
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle fw-semibold" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-user me-1"></i> <?= htmlspecialchars($u['name']) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item text-danger" href="./?page=logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- </header> -->
<main class="content">
<?php $page!='carriers_view'?$site->show_msg():''; ?>