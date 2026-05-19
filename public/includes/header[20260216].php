<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../_auth.php';

$role = $_SESSION['person_role'] ?? '';

$is_admin_or_manager = in_array($role, ['admin', 'manager']);
$is_staff_or_sales   = in_array($role, ['staff', 'sales person']);

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
    /*.nav-link.active { font-weight: 600; color: #fff !important; }*/
  </style>
  <style>
    /* Keep active nav link text dark on light navbar */
    .navbar-light .navbar-nav .nav-link.active {
      color: #0d6efd !important; /* Bootstrap primary blue */
      /*background-color: rgba(13, 110, 253, 0.1); /* light blue highlight */*/
      /*border-radius: 0.375rem;*/
      font-weight: 600;
    }
    .navbar-light .navbar-nav .nav-link.active i {
      color: #0d6efd !important;
    }
    </style>
    <style>
        /* General pill-style for all Bootstrap bg classes */
        .bg-warning,
        .bg-primary,
        .bg-info,
        .bg-secondary,
        .bg-success,
        .bg-danger {
          display: inline-block !important;
          padding: 5px 12px !important;
          font-size: 12px !important;
          font-weight: 600 !important;
          border-radius: 20px !important;
          margin-right: 8px !important;
          background: rgba(0, 0, 0, 0.05) !important;
        }

        /* Light background + dark text for each Bootstrap color */
        .bg-warning {
          background-color: #fff3cd !important;
          color: #000 !important;
        }

        .bg-primary {
          background-color: #cfe2ff !important;
          color: #084298 !important;
        }

        .bg-info {
          background-color: #cff4fc !important;
          color: #055160 !important;
        }

        .bg-secondary {
          background-color: #e2e3e5 !important;
          color: #41464b !important;
        }

        .bg-success {
          background-color: #d1e7dd !important;
          color: #0f5132 !important;
        }

        .bg-danger {
          background-color: #f8d7da !important;
          color: #842029 !important;
        }

        .btn-check:checked+.btn {
            background-color: #307ef5 !important;
            /*color: #084298 !important;*/
        }

        .btn-outline-primary.text-dark:hover, .btn-outline-secondary.text-dark:hover {
          color: #fff !important;         /* Make text white */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm mb-4">
  <div class="container-fluid">

    <!-- Brand -->
    <a class="navbar-brand fw-semibold text-primary1" href="index.php?page=bookings">
      <i class="fas fa-paper-plane me-2 text-primary1"></i>Travel Portal
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
      aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <!-- <li class="nav-item">
          <a class="nav-link<?= ($_GET['page'] ?? '') === 'dashboard' ? ' active fw-semibold text-primary' : '' ?>" href="?page=dashboard">
            <i class="fas fa-chart-line me-1 text-secondary"></i> Dashboard
          </a>
        </li> -->
        <?php
          $current_page = $_GET['page'] ?? '';
        ?>
        <li class="nav-item">
          <a class="nav-link<?= ($_GET['page'] ?? '') === 'bookings' ? ' active fw-semibold text-primary' : '' ?>" href="?page=bookings">
            <i class="fas fa-paper-plane me-1 text-secondary"></i> Bookings
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= (in_array($current_page, ['customers','customer_view'])) ? ' active fw-semibold text-primary' : '' ?>" href="?page=customers">
            <i class="fas fa-users me-1 text-secondary"></i> Customers
          </a>
        </li>

      <?php if ($is_admin_or_manager): ?>

        <!-- <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-dark d-flex align-items-center
                    <?= in_array($current_page, ['customers','customer_view','customer_site_rates']) 
                          ? ' active fw-semibold text-primary' 
                          : '' ?>"
             href="#"
             id="cusDropdown"
             role="button"
             data-bs-toggle="dropdown"
             aria-expanded="false">

            <i class="fas fa-users me-2 text-secondary"></i> Customers
          </a>
          <ul class="dropdown-menu shadow-sm" aria-labelledby="custDropdown">
            <li>
              <a class="dropdown-item <?= $current_page=='customers' ? ' active1 fw-semibold text-primary' : '' ?>" href="?page=customers">
                <i class="fas fa-users me-1 text-secondary"></i> Customers
              </a>
            </li>
            <li>
              <a class="dropdown-item <?= $current_page=='customer_site_rates' ? ' active1 fw-semibold text-primary' : '' ?>" href="?page=customer_site_rates">
                <i class="fas fa-money-bill-wave me-1 text-secondary"></i> Customer Rates
              </a>
            </li>
            <li>
              <a class="dropdown-item <?= $current_page=='customer_invoices' ? ' active1 fw-semibold text-primary' : '' ?>" href="?page=customer_invoices">
                <i class="fas fa-file-invoice me-1 text-secondary"></i> Timesheet Invoices
              </a>
            </li>
          </ul>
        </li> -->
        <li class="nav-item">
          <a class="nav-link<?= ($_GET['page'] ?? '') === 'suppliers' ? ' active fw-semibold text-primary' : '' ?>" href="?page=suppliers">
            <i class="fas fa-truck me-1 text-secondary"></i> Suppliers
          </a>
        </li>

        <li class="nav-item d-none">
          <a class="nav-link<?= ($_GET['page'] ?? '') === 'tour_packages' ? ' active fw-semibold text-primary' : '' ?>" href="?page=tour_packages">
            <i class="fas fa-plane me-1 text-secondary"></i> Tour Packages
          </a>
        </li>
 
        <li class="nav-item">
          <a class="nav-link<?= ($_GET['page'] ?? '') === 'products' ? ' active fw-semibold text-primary' : '' ?>" href="?page=products">
            <i class="fas fa-boxes me-1 text-secondary"></i> Products
          </a>
        </li>

      <?php endif; ?>
        
        <li class="nav-item">
          <a class="nav-link<?= ($_GET['page'] ?? '') === 'packages' ? ' active fw-semibold text-primary' : '' ?>" href="?page=packages">
            <i class="fas fa-plane me-1 text-secondary"></i> Packages
          </a>
        </li>
      
      <?php if ($is_admin_or_manager): ?>

        <?php
          $finance_pages = ['payments', 'payment_categories', 'invoices', 'expenses'];
          $is_finance_active = in_array($current_page, $finance_pages);
        ?>
        <!-- FINANCE (with submenu) -->
        <?php /*<li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-dark d-flex align-items-center 
                    <?= in_array($current_page, ['payments','invoices']) 
                          ? ' active fw-semibold text-primary' 
                          : '' ?>"
             href="#" 
             id="financeDropdown" 
             role="button" 
             data-bs-toggle="dropdown" 
             aria-expanded="false">

            <i class="fas fa-dollar-sign me-2 text-secondary"></i> Finance
          </a>
          <ul class="dropdown-menu shadow-sm" aria-labelledby="financeDropdown">
            <li>
              <a class="dropdown-item <?= $current_page=='payments' ? ' active1 fw-semibold text-primary' : '' ?>"
                 href="?page=payments">
                <i class="fas fa-money-bill-wave me-2 text-muted"></i> Payments
              </a>
            </li>
            <li>
              <a class="dropdown-item <?= $current_page=='invoices' ? ' active1 fw-semibold text-primary' : '' ?>"
                 href="?page=invoices">
                <i class="fas fa-file-invoice me-2 text-muted"></i> Invoices
              </a>
            </li>
          </ul>
        </li> */ ?>

        <!-- <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-dark d-flex align-items-center
                    <?= in_array($current_page, ['job_titles','job_requirements','job_list']) 
                          ? ' active fw-semibold text-primary' 
                          : '' ?>"
             href="#"
             id="jobsDropdown"
             role="button"
             data-bs-toggle="dropdown"
             aria-expanded="false">

            <i class="fas fa-briefcase me-2 text-secondary"></i> Jobs
          </a>
          <ul class="dropdown-menu shadow-sm" aria-labelledby="jobsDropdown">
            <li>
              <a class="dropdown-item <?= $current_page=='job_titles' ? ' active1 fw-semibold text-primary' : '' ?>"
                 href="?page=job_titles">
                <i class="fas fa-list me-2 text-muted"></i> Job Titles
              </a>
            </li>
          </ul>
        </li> -->

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-dark d-flex align-items-center
                    <?= in_array($current_page, ['payment_categories','general_sites','incident_categories','bank_accounts','document_labels','document_templates','documents_created']) 
                          ? ' active fw-semibold text-primary' 
                          : '' ?>"
             href="#"
             id="configDropdown"
             role="button"
             data-bs-toggle="dropdown"
             aria-expanded="false">

            <i class="fas fa-cog me-2 text-secondary"></i> Config
          </a>
          <ul class="dropdown-menu shadow-sm" aria-labelledby="configDropdown">
            <li>
              <a class="dropdown-item <?= $current_page=='zones' ? ' active1 fw-semibold text-primary' : '' ?>"
                 href="?page=zones">
                <i class="fas fa-map me-2 text-muted"></i> Area Zones
              </a>
            </li>
            <li>
              <a class="dropdown-item <?= $current_page=='discounts' ? ' active1 fw-semibold text-primary' : '' ?>"
                 href="?page=discounts">
                <i class="fas fa-percentage me-2 text-muted"></i> Discounts
              </a>
            </li>
            <li>
              <a class="dropdown-item <?= $current_page=='payment_categories' ? ' active1 fw-semibold text-primary' : '' ?>"
                 href="?page=payment_categories">
                <i class="fas fa-tags me-2 text-muted"></i> Company Payment Categories
              </a>
            </li>
            <li>
              <a class="dropdown-item <?= $current_page=='bank_accounts' ? ' active1 fw-semibold text-primary' : '' ?>"
                 href="?page=bank_accounts">
                <i class="fas fa-university me-2 text-muted"></i> Bank Accounts
              </a>
            </li>
            <li>
              <a class="dropdown-item <?= $current_page=='document_labels' ? ' active1 fw-semibold text-primary' : '' ?>"
                 href="?page=document_labels">
                <i class="fas fa-file me-2 text-muted"></i> Document Labels
              </a>
            </li>
            <li>
              <a class="dropdown-item <?= $current_page=='document_templates' ? ' active1 fw-semibold text-primary' : '' ?>"
                 href="?page=document_templates">
                <i class="fas fa-file-pen me-2 text-muted"></i> Document Templates Manager
              </a>
            </li>
            <li>
              <a class="dropdown-item <?= $current_page=='documents_created' ? ' active1 fw-semibold text-primary' : '' ?>"
                 href="?page=documents_created">
                <i class="fas fa-file-pdf me-2 text-muted"></i> Documents Created
              </a>
            </li>
            
          </ul>
        </li>

        <!-- <li class="nav-item">
          <a class="nav-link<?= ($_GET['page'] ?? '') === 'whatsapp_contacts_list' ? ' active fw-semibold text-primary' : '' ?>" href="?page=whatsapp_contacts_list">
            <i class="fas fa-users me-1 text-secondary"></i> Contacts
          </a>
        </li> 
        <li class="nav-item">
          <a class="nav-link<?= ($_GET['page'] ?? '') === 'contacts' ? ' active fw-semibold text-primary' : '' ?>" href="?page=contacts">
            <i class="fas fa-users me-1 text-secondary"></i> Contacts
          </a>
        </li>-->

      <?php endif; ?>

      </ul>


      <!-- RIGHT SIDE NAV -->
      <ul class="navbar-nav ms-auto align-items-center">

      <?php if ($is_admin_or_manager): ?>
        <style>
          /* Notification dropdown */
          .notification-dropdown {
            width: 420px;
            max-width: 95vw;
          }

          /* Scroll body */
          .notification-body {
            max-height: 420px;
            overflow-y: auto;
          }

          /* Notification row */
          .notification-item {
            padding: 12px;
          }

          /* Icon bubble */
          .notif-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
          }

          /* Subtle hover */
          .notification-item:hover {
            background-color: #f8f9fa;
          }
          .notification-item.opacity-50 {
            opacity: 0.6;
          }
        </style>
        <!-- NOTIFICATIONS -->
        <li class="nav-item dropdown me-2">
          <a class="nav-link position-relative"
             href="#"
             id="notificationDropdown"
             role="button"
             data-bs-toggle="dropdown"
             data-bs-auto-close="outside"
             aria-expanded="false">

            <i class="fa-solid fa-bell fs-5 text-secondary"></i>

            <!-- unread count -->
            <span class="position-absolute top-2 start-100 translate-middle badge rounded-pill bg-danger"
                  style="font-size:10px !!important; padding:3px 6px !important;">
              0
            </span>
          </a>

          <div class="dropdown-menu dropdown-menu-end shadow-lg p-0 notification-dropdown"
               aria-labelledby="notificationDropdown">

            <!-- HEADER -->
            <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
              <strong class="small">Notifications</strong>
              <!-- <span class="badge bg-light text-dark">Today</span> -->
              <button id="notifMarkAll" class="btn btn-sm btn-outline-secondary d-none1" style="padding: 0.3em; font-size: 0.8em;">
                Mark all as read
              </button>
            </div>

            <!-- TABS -->
            <ul class="nav nav-tabs nav-justified small px-2 pt-2" role="tablist">
              <li class="nav-item">
                <button class="nav-link active" type="button"
                        data-bs-toggle="tab" data-notif-tab="reminders" data-bs-target="#nt-reminders">
                  Reminder
                  <span class="badge bg-secondary ms-1" id="cnt-reminders">0</span>
                </button>
              </li>
              <li class="nav-item d-none">
                <button class="nav-link" type="button"
                        data-bs-toggle="tab" data-notif-tab="requirements" data-bs-target="#nt-requirements">
                  Job Expiry
                  <span class="badge bg-secondary ms-1" id="cnt-requirements">0</span>
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" type="button"
                        data-bs-toggle="tab" data-notif-tab="bookings" data-bs-target="#nt-bookings">
                  Bookings
                  <span class="badge bg-secondary ms-1" id="cnt-bookings">0</span>
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" type="button"
                        data-bs-toggle="tab" data-notif-tab="documents" data-bs-target="#nt-documents">
                  Docs
                  <span class="badge bg-secondary ms-1" id="cnt-documents">0</span>
                </button>
              </li>
            </ul>

            <!-- BODY -->
            <div class="tab-content notification-body">

              <!-- REMINDERS -->
              <div class="tab-pane fade show active" id="nt-reminders">
                <div class="list-group list-group-flush" id="list-reminders">

                  <!-- <div class="list-group-item notification-item">
                    <div class="d-flex gap-2">
                      <div class="notif-icon bg-warning-subtle text-warning">
                        <i class="fa fa-phone"></i>
                      </div>

                      <div class="flex-grow-1">
                        <div class="fw-semibold small">Customer 2</div>
                        <div class="text-muted small">Follow up call</div>
                        <div class="text-muted small mt-1">
                          <i class="fa fa-clock me-1"></i> Today
                        </div>
                      </div>

                      <a href="?page=daily_interactions"
                         class="btn btn-sm btn-outline-primary align-self-start">
                        Open
                      </a>
                    </div>
                  </div> -->

                </div>
              </div>

              <!-- JOB REQUIREMENTS -->
              <div class="tab-pane fade" id="nt-requirements">
                <div class="list-group list-group-flush" id="list-requirements">

                  <!-- <div class="list-group-item notification-item">
                    <div class="d-flex gap-2">
                      <div class="notif-icon bg-danger-subtle text-danger">
                        <i class="fa fa-briefcase"></i>
                      </div>

                      <div class="flex-grow-1">
                        <div class="fw-semibold small">Electrician</div>
                        <div class="text-muted small">ABC Contracting</div>
                        <div class="text-danger small mt-1">
                          <i class="fa fa-triangle-exclamation me-1"></i>
                          Expires in 3 days
                        </div>
                      </div>

                      <a href="?page=customers_view&id=12"
                         class="btn btn-sm btn-outline-primary align-self-start">
                        View
                      </a>
                    </div>
                  </div> -->

                </div>
              </div>

              <!-- Bookings -->
              <div class="tab-pane fade" id="nt-bookings">
                <div class="list-group list-group-flush" id="list-bookings">

                </div>
              </div>

              <!-- DOCUMENTS -->
              <div class="tab-pane fade" id="nt-documents">
                <div class="list-group list-group-flush" id="list-documents">

                  <!-- <div class="list-group-item notification-item">
                    <div class="d-flex gap-2">
                      <div class="notif-icon bg-danger-subtle text-danger">
                        <i class="fa fa-file"></i>
                      </div>

                      <div class="flex-grow-1">
                        <div class="fw-semibold small">Passport</div>
                        <div class="text-muted small">John Mathew (Employee)</div>
                        <div class="text-danger small mt-1">
                          <i class="fa fa-calendar-xmark me-1"></i>
                          Expires on 25 Dec
                        </div>
                      </div>

                      <a href="?page=employees_view&id=7"
                         class="btn btn-sm btn-outline-primary align-self-start">
                        Open
                      </a>
                    </div>
                  </div> -->

                </div>
              </div>

            </div>

            <!-- FOOTER -->
            <div class="border-top text-center py-2 bg-light">
              <a href="?page=dashboard"
                 class="small fw-semibold text-decoration-none">
                View all notifications
              </a>
            </div>

          </div>
        </li>
      <?php endif; ?>

        <!-- USER DROPDOWN -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-dark d-flex align-items-center"
             href="#"
             id="userDropdown"
             role="button"
             data-bs-toggle="dropdown"
             aria-expanded="false">
            <i class="fas fa-user-circle me-1 text-primary"></i>
            <?= htmlspecialchars($CURRENT_USER_NAME ?? 'Agent') ?>
          </a>

          <ul class="dropdown-menu dropdown-menu-end shadow-sm"
              aria-labelledby="userDropdown">
            <li>
              <a class="dropdown-item text-danger"
                 href="index.php?page=logout">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
              </a>
            </li>
          </ul>
        </li>

      </ul>

    </div>
  </div>
</nav>



<div class="container-fluid">


