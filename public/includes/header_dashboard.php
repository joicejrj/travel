<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../_auth.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Agent Portal</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css"/>
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

  <style>
    body { background-color: #f8f9fa; }
    .navbar-brand { font-weight: 600; }
    .nav-link.active { font-weight: 600; color: #fff !important; }
    
    .btn-outline-secondary.btn-primary {
      color: #fff !important;
      background: #6c757d !important;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php?page=dashboard">
      <i class="fas fa-user-tie me-2"></i>Agent Portal
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
      aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link<?= ($_GET['page'] ?? '') === 'dashboard' ? ' active' : '' ?>" href="?page=dashboard"><i class="fas fa-chart-line me-1"></i> Dashboard</a></li>
        <!-- <li class="nav-item"><a class="nav-link<?= ($_GET['page'] ?? '') === 'carriers' ? ' active' : '' ?>" href="?page=carriers"><i class="fas fa-truck me-1"></i> Carriers</a></li> -->
        <li class="nav-item"><a class="nav-link<?= ($_GET['page'] ?? '') === 'contacts' ? ' active' : '' ?>" href="?page=contacts"><i class="fas fa-users me-1"></i> Contacts</a></li>
        <!-- <li class="nav-item"><a class="nav-link<?= ($_GET['page'] ?? '') === 'reminders_manage' ? ' active' : '' ?>" href="?page=reminders_manage"><i class="fas fa-bell me-1"></i> Reminders</a></li> -->
        <!-- <li class="nav-item"><a class="nav-link<?= ($_GET['page'] ?? '') === 'email_log' ? ' active' : '' ?>" href="?page=email_log"><i class="fas fa-envelope me-1"></i> Emails</a></li> -->
        <!-- <li class="nav-item"><a class="nav-link<?= ($_GET['page'] ?? '') === 'reports' ? ' active' : '' ?>" href="?page=reports"><i class="fas fa-chart-pie me-1"></i> Reports</a></li> -->
      </ul>

      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($CURRENT_USER_NAME ?? 'Agent') ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <!-- <li><a class="dropdown-item" href="index.php?page=profile"><i class="fas fa-id-badge me-2"></i>Profile</a></li> -->
            <!-- <li><hr class="dropdown-divider"></li> -->
            <li><a class="dropdown-item text-danger" href="index.php?page=logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid">
