<?php
require_once __DIR__ . '/includes/header.php';
require_login();

// Default form values
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$name = $company = $phones = $phone = $whatsapp = $email = $address = $city = $state = $country = $services = $google_rating = $website = $type = $source = $photo = $agent_id = $user_name = $fil_emails = $fil_domains = '';

$is_edit = false; //($id > 0);

// If editing, fetch existing supplier
if ($id > 0) {
    if ($stmt = $mysqli->prepare("SELECT id, name, company, phones, phone, whatsapp, email, address, city, state, country, services, google_rating, website, type, source, photo, agent_id, fil_domains, fil_emails FROM carriers WHERE id = ?")) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $is_edit  = true;
            $name     = $row['name'];
            $company  = $row['company'];
            $phones    = $row['phones'];
            $phone    = $row['phone'];
            $whatsapp    = $row['whatsapp'];
            $email    = $row['email'];
            $address  = $row['address'];
            $city  = $row['city'];
            $state  = $row['state'];
            $country  = $row['country'];
            $services  = $row['services'];
            $google_rating  = $row['google_rating'];
            $website  = $row['website'];
            $type  = $row['type'];
            $source  = $row['source'];
            $photo  = $row['photo'];
            $agent_id  = $row['agent_id'];
            if($agent_id!='') {
                $getu = $db->get('people',array('id'=>$agent_id),'name');
                $user_name = $getu?$getu->name:'';
            }
            $fil_domains  = $row['fil_domains'];
            $fil_emails  = $row['fil_emails'];
        }
        $stmt->close();
    }

    $getc = $db->get('carriers_contacts',array('#all'=>1,'supplier_id'=>$id));
}

// prevent add here
if(!$is_edit) {
    $site->redirect('index.php?page=carriers');
}

?>
  <style>
    body, html {
      height: 100%;
      overflow: hidden;
    }
    .page-container {
      height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .header {
      padding: 15px;
      background: #f2f2f2;
      /*color: #fff;*/
    }
    .content {
      flex: 1;
      overflow: hidden;
    }
    .col-section {
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .section-scroll {
      flex: 1;
      overflow-y: auto;
      padding: 10px;

      /* Firefox */
      scrollbar-width: thin;
      scrollbar-color: #ccc transparent;
    }
    /* Chrome, Edge, Safari */
    .section-scroll::-webkit-scrollbar {
      width: 6px;   /* slim */
    }
    .section-scroll::-webkit-scrollbar-track {
      background: transparent;
    }
    .section-scroll::-webkit-scrollbar-thumb {
      background-color: #adb5bd;  /* Bootstrap gray */
      border-radius: 10px;
    }
    .section-scroll::-webkit-scrollbar-thumb:hover {
      background-color: #6c757d;  /* darker on hover */
    }
    .card {
      margin-bottom: 15px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      border-radius: 8px;
    }
    .card-header {
      background: #f8f9fa;
      cursor: pointer;
    }
    .card-header:hover {
      background: #e9ecef;
    }
  </style>

  <!-- new styles -->
  <style>
      .jrow {
        padding: 0.8em;
      }
      .content {
        padding: 0;
      }
      .jleft {
        padding-right: 0;
        height: 92%;
      }
      .jcenter {
        padding: 0;
      }
      .jright {
        padding-left: 0;
        height: 92%;
      }
    /* Extra small form control */
    .form-control-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        height: calc(1.5em + 0.5rem + 2px);
    }
    .btn-xs {
      padding: 0.2rem .6rem;
      font-size: .75rem;
      border-radius: .2rem;
    }
    .card-header {
        padding: 0.4em 0.6em;
    }
    .card-body {
        padding: 0.6em 0.6em;
        font-size: 0.9em;
    }
  </style>
  <style>
    #notes-container .note-card, #reminders-container .note-card {
        background-color: #f8f9fa;
        border-radius: 0.35rem;
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
        line-height: 1.2;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    #notes-container .note-header, #reminders-container .note-header {
        display: flex;
        justify-content: space-between;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
    }

    #notes-container .note-text, #reminders-container .note-text {
        white-space: pre-wrap;
        color: #343a40;
        font-size: 0.85rem;
    }
  </style>
    <style>
    .call-entry {
      font-size: 12px;
      line-height: 1.4;
    }
    .call-entry audio {
      width: 100%;
      height: 28px;
    }

    </style>
    <style>
      .rtype {
        padding: 0.1em 0.4em !important;
      }
      #first_rem {
        font-size: 0.8em !important;
      }
    </style>
    <style>
        .list-group-item.active {
            background-color: #eef2ff !important;
            border-color: #eef2ff !important;
            color: #202020 !important;
        }
        .section-emailscroll {
            flex: 1;
            overflow-y: auto;
            /*overflow: hidden;*/
            padding: 10px;
            height: 66vh;
            overflow-x: hidden;

            /* Firefox */
            scrollbar-width: thin;
            scrollbar-color: #ccc transparent;
        }
        .overflow-autoy {
            overflow-y: scroll;
            overflow-x: hidden;
            /* Firefox */
            scrollbar-width: thin;
            scrollbar-color: #ccc transparent;
        }
    </style>
  <div class="page-container">

    <!-- Header -->
<div class="header border-bottom pb-2 mb-3">
  <div class="d-flex justify-content-between align-items-start flex-wrap">
    <!-- Left: Company Info -->
    <div>
      <div class="d-flex align-items-center gap-2">
        <h4 class="mb-0 fw-bold" id="companyd"><?= htmlspecialchars($company) ?></h4>
        <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewIdModal">
          <i class="fa fa-eye"></i> View ID
        </button>
        <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#settingsModal">
          <i class="fa fa-cog"></i> Settings
        </button>
      </div>

      <!-- Status Row -->
      <div class="mt-1 d-flex align-items-center flex-wrap gap-2">
        <span class="fw-semibold">Status:</span>
        <span class="badge 
          bg-<?=
            isset($type) && $type == 'Won' ? 'success' :
            (isset($type) && $type == 'Opportunity' ? 'primary' :
            (isset($type) && $type == 'Lead (Active)' ? 'warning' :
            (isset($type) && $type == 'Suspect' ? 'warning' :
            (isset($type) && $type == 'Archive' ? 'secondary' : 'light text-dark'))))
          ?>" id="typed">
          <?= $type ? ucwords($type) : '' ?>
        </span>
        <span class="badge bg-info" id="emaild">Email: <?=htmlspecialchars($email ?? '') ?></span>
        <span class="badge bg-primary" id="phoned">Phone: <?=htmlspecialchars($phone ?? '') ?></span>
      </div>
    </div>

    <!-- Right: Contact Info -->
    <!-- <div class="text-end mt-2 mt-md-0">
      <div><i class="fa fa-phone me-1 text-muted"></i> <span id="jhsdbfj"></span></div>
      <div><i class="fa fa-envelope me-1 text-muted"></i> <span id="jhdfgbd"><</span></div>
    </div> -->
  </div>

  <!-- Tabs -->
  <div class="mt-3 d-flex flex-wrap gap-2">
    <button class="btn btn-outline-primary active btn-sm tab-btn" data-tab="emails">Emails</button>
    <button class="btn btn-outline-primary btn-sm tab-btn" data-tab="notes">Notes</button>
    <button class="btn btn-outline-primary btn-sm tab-btn" data-tab="reminders">Reminders</button>
    <button class="btn btn-outline-primary btn-sm tab-btn" data-tab="contacts">Contacts</button>
  </div>
</div>

    <!-- Content -->
    <div class="content container-fluid">
      <div class="row h-100 jrow">
        <!-- Left Column -->
        <div class="col-md-8 col-section jleft">

            <!-- Emails Tab Content -->
            <div id="emails-section" class="tab-section">
                <div class="section-scroll section-emailscroll">
                    
                    <div class="card shadow-sm rounded-3 mb-3">
                      <div class="card-header bg-white border-0 d-flex align-items-center">
                        <i class="fa fa-envelope me-2 text-primary"></i>
                        <strong>Emails</strong>
                      </div>
                      <div class="card-body">
                        <div class="row g-3">
                          <!-- Email List -->
                          <div class="col-lg-5">
                            <div class="border rounded-3 overflow-autoy" style="max-height: 520px;">
                              <div id="noEmailsMessage" class="p-3 text-muted small d-none">
                                No emails found.
                              </div>

                              <div id="emailList" class="list-group list-group-flush"></div>

                              <div class="text-center p-2">
                                <button id="loadMoreBtn" class="btn btn-outline-primary btn-sm d-none">
                                  <i class="fa fa-refresh"></i> Load More
                                </button>
                              </div>
                            </div>
                          </div>

                          <!-- Reading Pane -->
                          <div class="col-lg-7">
                            <div class="border rounded-3 p-3 overflow-auto" style="max-height: 520px;">
                              <div id="emailReadingPane">
                                <div class="text-muted small">Select an email to preview.</div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>


                </div>
            </div>

            <!-- Notes Tab Content -->
            <div id="notes-section" class="tab-section" style="display:none;">
                <div class="section-scroll">

              <!-- Notes Form -->
              <div class="card">
                <div class="card-header bg-white border-0 d-flex align-items-center">
                  <i class="fa fa-sticky-note me-2"></i>
                  <strong>Add Notes</strong>
                </div>
                <div class="card-body">
                  <form id="supplier-note-form">
                    <div class="mb-2">
                      <input type="hidden" name="uname" value="<?=isset($_SESSION['user']) ? $_SESSION['user']['name'] : 'aleena'?>">
                      <textarea class="form-control form-control-sm" name="notes" rows="4" placeholder="Write your notes..." required></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                      <span id="note-success-msg" class="text-success small" style="display:none;">
                        <i class="fa fa-check"></i> Note Added!
                      </span>
                      <button type="submit" class="btn btn-primary btn-xs rounded-pill ms-auto">
                        <i class="fa fa-save"></i> Save Note
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <!-- Previous Notes -->
              <div class="card shadow-sm mb-3 mt-3">
                <div class="card-header d-flex justify-content-between align-items-center bg-white" 
                     data-bs-toggle="collapse" data-bs-target="#previousNotes" style="cursor:pointer;">
                  <span><i class="fa fa-sticky-note me-1"></i> <strong>Previous Notes</strong></span>
                  <i class="fa fa-chevron-down"></i>
                </div>

                <div id="previousNotes" class="collapse show">
                  <div class="card-body p-1" style="max-height: 300px; scrollbar-width: thin; overflow-y:auto;" id="notes-container">
                    <!-- Notes loaded dynamically here -->
                  </div>
                  <div class="card-footer text-center p-1 bg-white">
                    <button id="load-more-notes" class="btn btn-light btn-xs w-100 mt-2">
                      Load More
                    </button>
                  </div>
                </div>
              </div>

                </div>
            </div>

            <!-- Reminders Tab Content -->
            <div id="reminders-section" class="tab-section section-scroll" style="display:none;">
                <div class="">

              <!-- New Reminder Form -->
              <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-white border-0 d-flex align-items-center">
                  <i class="fa fa-clock me-2"></i>
                  <strong>Add New Reminder</strong>
                </div>

                <div class="card-body">
                  <form id="new-reminder-form">
                    <!-- Reminder Type -->
                    <div class="mb-2">
                      <label class="form-label mb-1 small fw-bold">Reminder Type</label>
                      <div class="d-flex flex-wrap gap-1">
                        <input type="radio" class="btn-check" name="type" id="type-callback" value="Callback" autocomplete="off" checked>
                        <label class="btn btn-outline-primary btn-xs rtype rounded-pill px-2 py-1" for="type-callback">Callback</label>

                        <input type="radio" class="btn-check" name="type" id="type-followup" value="Follow-up" autocomplete="off">
                        <label class="btn btn-outline-primary btn-xs rtype rounded-pill px-2 py-1" for="type-followup">Follow-up</label>

                        <input type="radio" class="btn-check" name="type" id="type-sendemail" value="Send Email" autocomplete="off">
                        <label class="btn btn-outline-primary btn-xs rtype rounded-pill px-2 py-1" for="type-sendemail">Send Email</label>

                        <input type="radio" class="btn-check" name="type" id="type-other" value="Other" autocomplete="off">
                        <label class="btn btn-outline-primary btn-xs rtype rounded-pill px-2 py-1" for="type-other">Other</label>
                      </div>
                    </div>

                    <!-- Date and Time -->
                    <div class="row g-2 mb-3">
                      <div class="col-md-6">
                        <label class="small mb-1">Date</label>
                        <input type="date" class="form-control form-control-xs rounded-pill" name="reminder_date" id="reminder-date" required>
                      </div>
                      <div class="col-md-6 position-relative">
                        <label class="small mb-1">Time</label>
                        <div class="input-group">
                          <input type="time" class="form-control form-control-xs rounded-pill" name="reminder_time" id="reminder-time" placeholder="Select time" required>
                        </div>
                      </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                      <textarea class="form-control form-control-xs rounded-3 mt-2" name="notes" id="notes-field" rows="2" placeholder="Write notes here..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                      <span id="reminder-success-msg" class="text-success small" style="display:none;">
                        <i class="fa fa-check"></i> Reminder added!
                      </span>
                      <button type="submit" class="btn btn-success btn-xs rounded-pill ms-auto">
                        <i class="fa fa-plus"></i> Set Reminder
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <!-- Previous Reminders -->
              <div class="card shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center bg-white"
                     data-bs-toggle="collapse" data-bs-target="#previousRemindersl" style="cursor:pointer;">
                  <span><i class="fa fa-clock me-1"></i> <strong>Previous Reminders</strong></span>
                  <i class="fa fa-chevron-down"></i>
                </div>

                <div id="previousRemindersl" class="collapse show">
                  <div class="card-body p-1" style="max-height: 300px; scrollbar-width: thin; overflow-y:auto;" id="reminders-containerl">
                    <!-- Reminders loaded dynamically here -->
                  </div>
                  <div class="card-footer text-center p-1 bg-white">
                    <button id="load-more-reminders" class="btn btn-light btn-xs w-100 mt-2">
                      <i class="fa fa-arrow-down me-1"></i> Load More
                    </button>
                  </div>
                </div>
              </div>

                </div>
            </div>


            <!-- Contacts Tab Section -->
            <div id="contacts-section" class="tab-section" style="display:none;">
                <div class="section-scroll">

              <!-- New Contact Form -->
              <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-white border-0 d-flex align-items-center">
                  <i class="fa fa-user-plus me-2"></i>
                  <strong>Add New Contact</strong>
                </div>
                <div class="card-body">
                  <form id="new-contact-form">
                    <div class="row g-2">
                      <div class="col-md-6">
                        <input type="text" class="form-control form-control-xs rounded-pill" name="name" placeholder="Name">
                      </div>
                      <div class="col-md-6">
                        <input type="text" class="form-control form-control-xs rounded-pill" name="phone" placeholder="12 Digit Phone Number">
                      </div>
                      <div class="col-md-6">
                        <input type="text" class="form-control form-control-xs rounded-pill" name="whatsapp" placeholder="12 Digit Whatsapp Number">
                      </div>
                      <div class="col-md-6">
                        <input type="email" class="form-control form-control-xs rounded-pill" name="email" placeholder="Email">
                      </div>
                      <div class="col-md-6">
                        <input type="text" class="form-control form-control-xs rounded-pill" name="designation" placeholder="Designation">
                      </div>
                      <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check ms-2">
                          <input class="form-check-input" type="checkbox" name="main" value="1" id="contact_primary">
                          <label class="form-check-label ms-1" for="contact_primary">Primary</label>
                        </div>
                      </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                      <span id="contact-success-msg" class="text-success small" style="display:none;">
                        <i class="fa fa-check"></i> Contact added!
                      </span>
                      <button type="submit" class="btn btn-success btn-xs rounded-pill ms-auto">
                        <i class="fa fa-plus"></i> Add Contact
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <!-- Multiple Contacts -->
              <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-white border-0 d-flex align-items-center">
                  <i class="fa fa-user me-2"></i>
                  <strong>Contacts</strong>
                </div>
                <div class="card-body p-2">

                  <div id="contacts-wrapper" class="section-scroll" style="max-height: 31vh; padding: 0;">

                    <?php if (!empty($getc->data)) : ?>
                      <?php foreach ($getc->data as $key => $cont) : ?>
                        <!-- Contact Block -->
                        <div class="contact-block rounded border p-1 mb-2">
                          <div class="row g-1">
                            <div class="col-6">
                              <input type="text" class="form-control form-control-xs rounded-pill" 
                                     name="contact_name[]" placeholder="Name" data-id="<?= $cont->id ?? 0 ?>" 
                                     data-field="name" value="<?= htmlspecialchars($cont->name) ?>">
                            </div>
                            <div class="col-6">
                              <input type="text" class="form-control form-control-xs rounded-pill" 
                                     name="contact_phone[]" placeholder="Phone" data-id="<?= $cont->id ?? 0 ?>" 
                                     data-field="phone" value="<?= htmlspecialchars($cont->phone ?? '') ?>">
                            </div>
                          </div>

                          <div class="row g-1 mt-1">
                            <div class="col-6">
                              <input type="text" class="form-control form-control-xs rounded-pill" 
                                     name="contact_whatsapp[]" placeholder="Whatsapp" data-id="<?= $cont->id ?? 0 ?>" 
                                     data-field="whatsapp" value="<?= htmlspecialchars($cont->whatsapp ?? '') ?>">
                            </div>
                            <div class="col-6">
                              <input type="email" class="form-control form-control-xs rounded-pill" 
                                     name="contact_email[]" placeholder="Email" data-id="<?= $cont->id ?? 0 ?>" 
                                     data-field="email" value="<?= htmlspecialchars($cont->email ?? '') ?>">
                            </div>
                          </div>

                          <div class="row g-1 mt-1 align-items-center">
                            <div class="col-6">
                              <input type="text" class="form-control form-control-xs rounded-pill" 
                                     name="contact_designation[]" placeholder="Designation" data-id="<?= $cont->id ?? 0 ?>" 
                                     data-field="designation" value="<?= htmlspecialchars($cont->designation ?? '') ?>">
                            </div>
                            <div class="col-6 d-flex justify-content-between align-items-center">
                              <div class="form-check">
                                <input type="hidden" class="primary-hidden" name="contact_primary[]" value="0">
                                <input class="form-check-input primary-contact" type="checkbox" value="1" 
                                       data-id="<?= $cont->id ?? 0 ?>" data-field="main" 
                                       id="contact_primary_<?=$key?>" <?= isset($cont->main) && $cont->main==1 ? 'checked' : '' ?>>
                                <label class="form-check-label ms-1" for="contact_primary_<?=$key?>">Primary</label>
                              </div>
                              <button type="button" class="btn btn-xs btn-outline-danger remove-row p-1">
                                <i class="fa fa-trash"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                        <!-- End Contact Block -->
                      <?php endforeach; ?>
                    <?php else : ?>
                      <!-- Empty message -->
                      <div id="no-contacts-message" class="text-center text-muted small">
                        No contacts available.
                      </div>
                    <?php endif; ?>

                  </div>
                </div>
              </div>

                </div>
            </div>


        </div>

        <!-- Right Column -->
        <div class="col-md-4 col-section jright">
          <div class="section-scroll">

            <!-- Previous Reminders -->
            <div class="card shadow-sm mb-3">
              <div class="card-header d-flex justify-content-between align-items-center bg-white" 
                   data-bs-toggle="collapse" data-bs-target="#previousReminders" style="cursor:pointer;">
                <span><i class="fa fa-clock me-1"></i> <strong>Next Action</strong></span>
                <i class="fa fa-chevron-down"></i>
              </div>

              <div id="previousReminders" class="collapse show">
                <div class="card-body p-1" style="max-height: 300px; scrollbar-width: thin; overflow-y:auto;" id="reminders-container">
                  <!-- Reminders loaded dynamically here -->
                </div>
              </div>
            </div>

            <!-- Email Filters Card -->
            <div class="card shadow-sm rounded-3 mb-3">
              <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between flex-wrap">
                <div>
                  <i class="fa fa-at me-2"></i>
                  <strong>Email Filters</strong>
                  <!-- Display current domains and emails -->
                  <div class="small text-muted mt-1">
                    Domains: <?= htmlspecialchars($fil_domains ?? 'None') ?><br>
                    Emails: <?= htmlspecialchars($fil_emails ?? 'None') ?>
                  </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-xs mt-2 mt-sm-0" data-bs-toggle="modal" data-bs-target="#editEmailFiltersModal">
                  <i class="fa fa-edit"></i> Edit
                </button>
              </div>
            </div>

          </div>

        </div>

      </div>
    </div>
  </div>


<!-- Carrier Info Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="settingsModalLabel">
          <i class="fa fa-user-edit me-2"></i>Edit Carrier Information
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body" id="info">
        <form id="info-wrapper" class="section-scroll" style="max-height: 60vh; padding: 0;">
          
          <div class="mb-2">
            <input type="text" class="form-control form-control-xs rounded-pill companyInput" 
                   name="company" id="company" placeholder="Company Name" 
                   value="<?= htmlspecialchars($company ?? '') ?>">
          </div>

          <div class="mb-2">
            <input type="text" class="form-control form-control-xs rounded-pill" 
                   name="name" id="name" placeholder="Contact Name" 
                   value="<?= htmlspecialchars($name ?? '') ?>">
          </div>

          <div class="mb-2">
            <input type="text" class="form-control form-control-xs rounded-pill" 
                   name="phone" id="phone" placeholder="12 Digit Phone Number" 
                   value="<?= htmlspecialchars($phone ?? '') ?>">
          </div>

          <div class="mb-2">
            <input type="text" class="form-control form-control-xs rounded-pill" 
                   name="whatsapp" id="whatsapp" placeholder="12 Digit Whatsapp Number" 
                   value="<?= htmlspecialchars($whatsapp ?? '') ?>">
          </div>

          <div class="mb-2">
            <input type="email" class="form-control form-control-xs rounded-pill" 
                   name="email" id="email" placeholder="Email ID" 
                   value="<?= htmlspecialchars($email ?? '') ?>">
          </div>

          <div class="mb-2">
            <select class="form-select form-control-xs rounded-pill" name="agent_id" id="agent_id" placeholder="Source">
              <option value=""> - Select Agent - </option>
              <?php
                $geta = $db->get('people',array('#all'=>1),'id,name');
                foreach ($geta->data as $key => $usa) {
              ?>
                <option value="<?=$usa->id?>" <?= $agent_id== $usa->id ? 'selected' : '' ?>><?=$usa->name?></option>
              <?php } ?>
            </select>
          </div>

          <!-- Photo upload with preview -->
          <div class="mb-2">
            <input type="file" class="form-control form-control-xs rounded-pill" 
                   name="photo" id="photo" accept="image/*">
            <div id="photoPreview" style="margin-top: 5px;">
              <img id="previewImg" src="uploads/carriers/<?= !empty($photo) ? htmlspecialchars($photo) : '' ?>" 
                   alt="Preview" style="max-width: 100px; max-height: 100px; border-radius: 6px; object-fit: cover; <?= empty($photo) ? 'display:none;' : '' ?>">
            </div>
          </div>

          <div class="mb-2">
            <textarea class="form-control form-control-xs" 
                      style="border-radius: 6px !important; height: 45px;" 
                      name="address" id="address" placeholder="Address"><?= htmlspecialchars($address ?? '') ?></textarea>
          </div>

          <div class="mb-2">
            <select class="form-select form-control-xs rounded-pill" name="source" id="source" placeholder="Source">
              <option value="from website"  <?= ($source ?? '') === 'from website'  ? 'selected' : '' ?>>From Website</option>
              <option value="from call"     <?= ($source ?? '') === 'from call'     ? 'selected' : '' ?>>From Call</option>
              <option value="from leads"    <?= ($source ?? '') === 'from leads'    ? 'selected' : '' ?>>From Leads</option>
              <option value="from whatsapp" <?= ($source ?? '') === 'from whatsapp' ? 'selected' : '' ?>>From Whatsapp</option>
            </select>
          </div>

          <div class="mb-2">
            <select class="form-select form-control-xs rounded-pill" name="type" id="type" placeholder="Status">
              <option value="Suspect" <?= ($type ?? '') === 'Suspect' ? 'selected' : '' ?>>
                Suspect — you have a name, not verified yet.
              </option>
              <option value="Lead (Active)" <?= ($type ?? '') === 'Lead (Active)' ? 'selected' : '' ?>>
                Lead (Active) — you’ve reached out / they responded.
              </option>
              <option value="Opportunity" <?= ($type ?? '') === 'Opportunity' ? 'selected' : '' ?>>
                Opportunity — quote/proposal/demo sent; decision pending.
              </option>
              <option value="Won" <?= ($type ?? '') === 'Won' ? 'selected' : '' ?>>
                Won — became a customer.
              </option>
              <option value="Archive" <?= ($type ?? '') === 'Archive' ? 'selected' : '' ?>>
                Archive — not a fit or chose someone else (capture reason).
              </option>
            </select>
          </div>

          <div class="mb-2">
            <input type="text" class="form-control form-control-xs rounded-pill" 
                   name="city" id="city" placeholder="City" 
                   value="<?= htmlspecialchars($city ?? '') ?>">
          </div>

          <div class="mb-2">
            <input type="text" class="form-control form-control-xs rounded-pill" 
                   name="state" id="state" placeholder="State" 
                   value="<?= htmlspecialchars($state ?? '') ?>">
          </div>

          <div class="mb-2">
            <input type="text" class="form-control form-control-xs rounded-pill" 
                   name="country" id="country" placeholder="Country Name" 
                   value="<?= htmlspecialchars($country ?? '') ?>">
          </div>

          <div class="mb-2">
            <input type="text" class="form-control form-control-xs rounded-pill" 
                   name="website" id="website" placeholder="Website" 
                   value="<?= htmlspecialchars($website ?? '') ?>">
          </div>

          <div class="mb-2">
            <input type="text" class="form-control form-control-xs rounded-pill" 
                   name="services" id="services" placeholder="Services" 
                   value="<?= htmlspecialchars($services ?? '') ?>">
          </div>

        </form>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" id="saveCarrierInfo" class="btn btn-primary btn-sm">
          <i class="fa fa-save me-1"></i> Save Changes
        </button>
      </div>

    </div>
  </div>
</div>

<!-- viewIdModal -->
<div class="modal fade" id="viewIdModal" tabindex="-1" aria-labelledby="viewIdModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="viewIdModalLabel"><i class="fa fa-id-card me-2"></i>View ID</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-3" id="idphotodiv">
        <?=$photo!=''?'<img id="idImage" src="uploads/carriers/'.$photo.'" alt="ID Image" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain;">':'No ID uploaded <span class="btn btn-primary btn-xs" onclick="setPhotoId()">Upload ID</span>'?>
      </div>
    </div>
  </div>
</div>


<!-- Edit Reminder Modal -->
<div class="modal fade" id="editReminderModal" tabindex="-1" aria-labelledby="editReminderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="editReminderModalLabel">Edit Reminder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body: Reminder Form -->
      <div class="modal-body">
        <form id="edit-reminder-form" class="mb-3 p-2">
          <!-- Hidden Reminder ID -->
          <input type="hidden" name="reminder_id" id="edit_reminder_id" value="">

          <!-- Reminder Type -->
          <div class="mb-2">
            <div class="d-flex flex-wrap gap-1">
              <input type="radio" class="btn-check" name="type" id="edit-type1-callback" value="Callback" autocomplete="off">
              <label class="btn btn-outline-primary btn-xs rtype rounded-pill px-2 py-1" for="edit-type1-callback">Callback</label>

              <input type="radio" class="btn-check" name="type" id="edit-type1-follow-up" value="Follow-up" autocomplete="off">
              <label class="btn btn-outline-primary btn-xs rtype rounded-pill px-2 py-1" for="edit-type1-follow-up">Follow-up</label>

              <input type="radio" class="btn-check" name="type" id="edit-type1-sendemail" value="Send Email" autocomplete="off">
              <label class="btn btn-outline-primary btn-xs rtype rounded-pill px-2 py-1" for="edit-type1-sendemail">Send Email</label>

              <input type="radio" class="btn-check" name="type" id="edit-type1-other" value="Other" autocomplete="off">
              <label class="btn btn-outline-primary btn-xs rtype rounded-pill px-2 py-1" for="edit-type1-other">Other</label>
            </div>
          </div>

          <!-- Date -->
          <div class="mb-2">
            <input type="date" class="form-control form-control-xs rounded-pill" name="reminder_date" id="edit-reminder-date" required>
          </div>

          <!-- Time -->
          <div class="mb-2">
            <input type="time" class="form-control form-control-xs rounded-pill" name="reminder_time" id="edit-reminder-time" required>
          </div>

          <!-- Notes -->
          <div class="mb-2">
            <textarea class="form-control form-control-xs rounded-3" name="notes" id="edit-reminder-notes" rows="2" placeholder="Write notes..."></textarea>
          </div>

          <div id="first_rem"></div>

          <!-- Submit Button -->
          <div class="d-flex justify-content-end mt-2">
            <button type="submit" class="btn btn-success btn-xs rounded-pill">Update Reminder</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<!-- Email Filters Modal -->
<div class="modal fade" id="editEmailFiltersModal" tabindex="-1" aria-labelledby="editEmailFiltersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editEmailFiltersModalLabel">Edit Email Filters</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="edit-email-filters-form">
          <div class="row g-2" id="email_filters">
            <div class="col-md-6">
                <label for="fil_domains">Filter Domains</label>
                <input type="text" class="form-control form-control-xs rounded-pill" name="fil_domains" id="fil_domains" placeholder="swebsite.com,site.com (comma separated)" value="<?= $fil_domains ?>">
            </div>
            <div class="col-md-6">
                <label for="fil_emails">Filter Emails</label>
                <input type="text" class="form-control form-control-xs rounded-pill" name="fil_emails" id="fil_emails" placeholder="abcd123@website.com,xyz354@site.com (comma separated)" value="<?= $fil_emails ?>">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-xs rounded-pill" data-bs-dismiss="modal">Close</button>
        <button type="button" form="edit-email-filters-form" id="save-email-filters" class="btn btn-primary btn-xs rounded-pill">Save Changes</button>
      </div>
    </div>
  </div>
</div>


<script>
    $(document).ready(function(){
        
        // supplier info --------------------------------------------
        let saveTimer;
        $("#info").on("keyup change", ":input", function(){
            clearTimeout(saveTimer); // reset timer on every keypress/change

            let $input = $(this);
            let fieldName = $input.attr("name");
            let fieldValue = $input.val();

            // debounce: wait 500ms after typing before sending
            saveTimer = setTimeout(function(){
                $.ajax({
                    url: "public/ajax/carriers_save.php",
                    type: "POST",
                    data: {
                        supplier_id: "<?= $id ?? 0 ?>",
                        field: fieldName,
                        value: fieldValue
                    },
                    success: function(response){
                        // console.log("Saved:", fieldName, "=", fieldValue, response);
                        // optional: show a small ✅ indicator
                        $input.addClass("is-valid");
                        setTimeout(()=> $input.removeClass("is-valid"), 1500);
                        // $("#"+fieldName).val(fieldValue);
                        setCarrier();
                    },
                    error: function(){
                        console.error("Error updating recuiter info.");
                        $input.addClass("is-invalid");
                    }
                });
            }, 500); // adjust debounce delay as needed
        });
        // File upload for photo
        $("#photo").on("change", function() {
            let fileInput = this;
            if (fileInput.files.length === 0) return; // no file selected

            let formData = new FormData();
            formData.append("supplier_id", "<?= $id ?? 0 ?>");
            formData.append("field", "photo");
            formData.append("photo", fileInput.files[0]);

            $.ajax({
                url: "public/ajax/carriers_save.php",
                type: "POST",
                data: formData,
                processData: false,  // important
                contentType: false,  // important
                success: function(response){
                    console.log("Photo uploaded:", response);
                    // optional: show preview or a ✅ indicator
                    if(response.success && response.photo){
                        $("#previewImg").attr("src", "uploads/carriers/" + response.photo).show();
                        $("#idphotodiv").html('<img id="idImage" src="uploads/carriers/'+response.photo+'" alt="ID Image" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain;">');
                    }
                },
                error: function(){
                    console.error("Error uploading photo.");
                }
            });
        });
        function reloadCarrier() {
            $.post("public/ajax/carriers_fetch.php", 
              { supplier_id: <?=$id??0?>, offset: notesOffset, limit: notesLimit },
              function(res){
                if(res.success==1){
                    Object.entries(res.fields).forEach(([key, val],ind) => {
                        $("#"+key).val(val);
                        if(key=='photo'&&val!='') {
                            $("#photod").show();
                            $("#photodimg").attr("src","uploads/carriers/"+val);
                        }
                        $("#"+key+"d").val(val);
                        
                        if(key=='tclass') {
                            $("#typed").removeClass('bg-info').removeClass('bg-warning').removeClass('bg-success').removeClass('bg-danger').removeClass('bg-primary');
                            $("#typed").addClass(val);
                        }
                    });


                    setCarrier();
                } else {
                    alert("Couldn't refrsh recruiter info");   
                }
              }, "json");
        }
        function setCarrier() {
            // var source = $("#source").val();
            var type = $("#type").val();
            var company = $("#company").val();
            var phone = $("#phone").val();
            var email = $("#email").val();

            // source = source.toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
            // $("#sourced").text(source);
            $("#companyd").text(company);
            $("#typed").text(type);
            $("#emaild").text('Email: '+email);
            $("#phoned").text('Phone: '+phone);
        }

        //contacts------------------------------
        function reloadContacts() {
            $.post("public/ajax/carriers_contacts_fetch.php", { supplier_id: <?=$id?> }, function(res) {
                let html = "";

                if (res.success && res.count > 0) {
                    $.each(res.data, function(i, cont) {
                        html += `
                        <div class="contact-block rounded border p-1 mb-2">
                            <div class="row g-1">
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-xs rounded-pill"
                                           name="contact_name[]" placeholder="Name"
                                           data-id="${cont.id || 0}" data-field="name"
                                           value="${cont.name ? cont.name : ''}">
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-xs rounded-pill"
                                           name="contact_phone[]" placeholder="12 Digit Phone"
                                           data-id="${cont.id || 0}" data-field="phone"
                                           value="${cont.phone ? cont.phone : ''}">
                                </div>
                            </div>

                            <div class="row g-1 mt-1">
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-xs rounded-pill"
                                           name="contact_whatsapp[]" placeholder="12 Digit Whatsapp"
                                           data-id="${cont.id || 0}" data-field="whatsapp"
                                           value="${cont.whatsapp ? cont.whatsapp : ''}">
                                </div>
                                <div class="col-6">
                                    <input type="email" class="form-control form-control-xs rounded-pill"
                                           name="contact_email[]" placeholder="Email"
                                           data-id="${cont.id || 0}" data-field="email"
                                           value="${cont.email ? cont.email : ''}">
                                </div>
                            </div>

                            <div class="row g-1 mt-1 align-items-center">
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-xs rounded-pill"
                                           name="contact_designation[]" placeholder="Designation"
                                           data-id="${cont.id || 0}" data-field="designation"
                                           value="${cont.designation ? cont.designation : ''}">
                                </div>
                                <div class="col-6 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input type="hidden" class="primary-hidden" name="contact_primary[]" value="0">
                                        <input class="form-check-input primary-contact" type="checkbox"
                                               value="1" data-id="${cont.id || 0}" data-field="main"
                                               id="contact_primary_${i}" ${cont.main == 1 ? "checked" : ""}>
                                        <label class="form-check-label ms-1" for="contact_primary_${i}">Primary</label>
                                    </div>
                                    <button type="button" class="btn btn-xs btn-outline-danger remove-row p-1">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    });
                } else {
                    html = `<div id="no-contacts-message" class="text-center text-muted small">
                                No contacts available.
                            </div>`;
                }

                $("#contacts-wrapper").html(html);
            }, "json");
        }
        // Handle Add New Contact form
        $("#new-contact-form").on("submit", function(e) {
            e.preventDefault();

            let $msg  = $("#contact-success-msg");
            let formData = $(this).serialize() + "&supplier_id=<?= $id ?? 0 ?>";

            $.ajax({
                url: "public/ajax/carriers_contacts_add.php",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(res) {
                    if (res.success) {
                        if ($('#contact_primary').prop('checked')) {
                            console.log('It is primary, reloading supplier info');
                            reloadCarrier();
                        }
                        $("#new-contact-form")[0].reset();
                        $msg.text('Contact added!')
                            .removeClass("text-danger")
                            .addClass("text-success")
                            .fadeIn(200).delay(1500).fadeOut(500);
                        reloadContacts(); // refresh contacts list
                    } else {
                        $msg.text(res.error || "Failed to add contact")
                            .removeClass("text-success")
                            .addClass("text-danger")
                            .fadeIn(200).delay(2000).fadeOut(500);
                        // alert(res.msg || "Failed to add contact");
                    }
                },
                error: function() {
                    $msg.text("Error: Could not save contact")
                        .removeClass("text-success")
                        .addClass("text-danger")
                        .fadeIn(200).delay(2000).fadeOut(500);
                }
            });
        });
        // function toggleNoContactsMessage() {
        //     const message = document.getElementById('no-contacts-message');
        //     if(message){
        //         message.style.display = wrapper.querySelectorAll('.contact-block').length === 0 ? 'block' : 'none';
        //     }
        // }
        // Delete with confirmation + AJAX + toggle empty message
        const wrapper = document.getElementById('contacts-wrapper');
        wrapper.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-row');
            if (!removeBtn) return;

            if (!confirm("Are you sure you want to delete this contact?")) return;

            const block = removeBtn.closest('.contact-block');
            // const contactId = block.querySelector(':input[data-id]')?.dataset.id || 0;
            const inputWithId = block.querySelector('input[data-id], textarea[data-id]');
            const contactId = inputWithId?.dataset.id || 0;

            if (contactId > 0) {
                // Saved contact → delete via AJAX
                $.post("public/ajax/carriers_contacts_delete.php", { contact_id: contactId }, function(res) {
                    if (res.success) {
                        block.remove();
                        alert("Deleted contact");
                        // console.log("Deleted contact:", res);
                        // Reload list from DB (fresh data)
                        reloadContacts();
                    } else {
                        alert(res.msg || "Failed to delete contact");
                    }
                }, "json");
            } else {
                // New/unsaved contact → just remove from DOM
                block.remove();
                if (!wrapper.querySelector(".contact-block")) {
                    wrapper.innerHTML = `
                      <div id="no-contacts-message" class="text-center text-muted small">
                        No contacts available.
                      </div>`;
                }
            }
        });
        // Initial check
        // toggleNoContactsMessage();

        let saveTimerc;
        // Auto-save contact fields on keyup/change
        $("#contacts-wrapper").on("keyup change", ":input", function() {
            let $input = $(this);
            let contactId = $input.data("id");     // existing contact ID
            let fieldName = $input.data("field");  // name, phone, email, etc.

            if (!fieldName) return; // skip if no mapping

            let fieldValue = $input.is(":checkbox") ? ($input.is(":checked") ? 1 : 0) : $input.val();

            clearTimeout(saveTimerc);
            saveTimerc = setTimeout(function() {
                $.ajax({
                    url: "public/ajax/carriers_contacts_save.php",
                    type: "POST",
                    data: {
                        supplier_id: "<?= $id ?? 0 ?>",
                        contact_id: contactId ?? 0,
                        field: fieldName,
                        value: fieldValue
                    },
                    success: function(res) {
                        // console.log("Contact saved", res);
                        $input.addClass("is-valid");
                        setTimeout(() => $input.removeClass("is-valid"), 1500);

                        // If setting primary contact (main = 1), uncheck all others
                        if (fieldName === 'main' && fieldValue == 1) {
                            $("#contacts-wrapper .primary-contact")
                                .not($input)
                                .prop("checked", false);

                            reloadCarrier();
                        }
                    },
                    error: function() {
                        console.error("Error saving contact");
                        $input.addClass("is-invalid");
                    }
                });
            }, 500);
        });

        //notes ----------------------------------------------
        // add notes
        $("#supplier-note-form").on("submit", function(e){
            e.preventDefault();

            let $form = $(this);
            let $msg  = $("#note-success-msg");
            let notes = $form.find("textarea[name='notes']").val().trim();
            let uname = $form.find("input[name='uname']").val();

            if(notes === "") return;

            $.ajax({
                url: "public/ajax/carriers_logs_save.php",
                type: "POST",
                data: { notes: notes, name: uname, supplier_id: "<?= $id ?? 0 ?>" },
                dataType: "json",
                success: function(res){
                    if(res.success){
                        $form[0].reset();
                        // show success message briefly
                        $msg.fadeIn(200).delay(1500).fadeOut(500);
                        
                        // refresh notes list from start
                        loadNotes(true);
                    } else {
                        $msg.text(res.error || "Failed to save note")
                            .removeClass("text-success")
                            .addClass("text-danger")
                            .fadeIn(200).delay(2000).fadeOut(500);
                    }
                },
                error: function(){
                    $msg.text("Error: Could not save note")
                        .removeClass("text-success")
                        .addClass("text-danger")
                        .fadeIn(200).delay(2000).fadeOut(500);
                }
            });
        });

        let notesOffset = 0;
        const notesLimit = 5; // fetch 5 notes at a time
        const supplierId = <?= $id ?? 0 ?>; // current supplier ID

        function formatNoteHtml(log) {
            const formattedNotes = log.notes.replace(/\n/g, "<br>");
            return `
              <div class="note-card">
                <div class="note-header">
                  <span>${log.created_at}</span>
                  <span>${log.name}</span>
                </div>
                <div class="note-text">${formattedNotes}</div>
              </div>
            `;
        }

        function loadNotes(ref = false) {
            if (ref === true) {
                notesOffset = 0;
                $("#notes-container").empty();   // clear old notes
                $("#load-more-notes").prop("disabled", false).text("Load More"); // reset button
            }

            $.post("public/ajax/carriers_logs_fetch.php", 
              { supplier_id: supplierId, offset: notesOffset, limit: notesLimit },
              function(res){
                if (res.logs && res.logs.length > 0) {
                    const container = $("#notes-container");
                    res.logs.forEach(log => {
                        container.append(formatNoteHtml(log));
                    });
                    notesOffset += res.logs.length;
                } else {
                    $("#load-more-notes").prop("disabled", true).text("No more notes");
                }
              }, "json");
        }

        // Initial load
        loadNotes();

        // Load more button
        $("#load-more-notes").on("click", function(){
            loadNotes();
        });
 

        //calls ----------------------------------------------------------
        let callsOffset = 0;
        const callsLimit = 2; // fetch 10 calls at a time
        // const supplierId = <?= $id ?? 0 ?>; // current supplier ID

        function formatCallHtml(call) {
            // Determine badge color
            const dispClass = call.disposition === 'ANSWERED' ? 'bg-success text-white' : 'bg-warning text-dark';

            return `
              <div class="call-entry mb-2 pb-2 border-bottom">
                <div class="d-flex justify-content-between small text-muted">
                  <span class="fw-bold">${call.start}</span>
                  <span class="badge ${dispClass}">${call.disposition}</span>
                </div>
                <div class="small text-truncate">
                  ${call.destination} &middot; ${call.talk_time} sec
                </div>
                ${call.recording && call.recording.trim() !== "" 
                    ? `<audio controls class="w-100 mt-1" style="height:28px;">
                         <source src="${call.recording}" type="audio/mpeg">
                       </audio>`
                    : ""}
              </div>
            `;
        }

        function loadCalls(reset = false) {
            if (reset) {
                callsOffset = 0;
                $("#calls-container").html('');
                $("#load-more-calls").prop("disabled", false).html('Load More');
            }

            const $btn = $("#load-more-calls");
            $btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');

            $.post("public/ajax/carriers_call_logs.php", 
                { supplier_id: supplierId, offset: callsOffset, limit: callsLimit }, 
                function(res) {
                    if (res.logs && res.logs.length > 0) {
                        const container = $("#calls-container");
                        res.logs.forEach(call => container.append(formatCallHtml(call)));

                        // Increment offset by number of rows returned
                        callsOffset += res.logs.length;

                        // If returned rows < limit → no more rows
                        if(res.logs.length < callsLimit){
                            $btn.prop("disabled", true).html('No more calls');
                        } else {
                            $btn.prop("disabled", false).html('Load More');
                        }
                    } else {
                        $btn.prop("disabled", true).html('No more calls');
                    }
                }, "json"
            ).fail(function() {
                $btn.prop("disabled", false).html('Load More');
                alert("Error loading calls");
            });
        }

        // Load more
        $("#load-more-calls").on("click", function() {
            loadCalls();
        });

        // Refresh from start
        $("#refresh-calls").on("click", function() {
            loadCalls(true);
        });

        // Initial load
        //loadCalls(true);




    });
</script>


<script>
(function(){
  // Prevent selecting past date/time
  document.getElementById('reminder-date').addEventListener('change', function(){
    const today = new Date().toISOString().split('T')[0];
    if (this.value < today) {
      alert('Past dates are not allowed.');
      this.value = today;
    }
  });
})();
</script>
<script>
function openEditReminderModal(reminder) {
  const modalEl = document.getElementById('editReminderModal');
  if (!modalEl) return;

  const modal = new bootstrap.Modal(modalEl);

  document.getElementById('edit_reminder_id').value = reminder.id || '';
  document.getElementById('edit-reminder-date').value = reminder.reminder_at.split(' ')[0] || '';
  document.getElementById('edit-reminder-time').value = reminder.reminder_at.split(' ')[1]?.slice(0,5) || '';
  document.getElementById('edit-reminder-notes').value = reminder.note || '';

  ['Callback','Follow-up','Send Email','Other'].forEach(type => {
    const radio = document.getElementById('edit-type1-' + type.toLowerCase().replace(' ',''));
    if (radio) radio.checked = (reminder.type === type);
  });

  modal.show();
}
$(document).ready(function() {
    var remindersOffset = 0;
    const remindersLimit = 10;

    const supplierId = <?= $id ?? 0 ?>; // current supplier ID

    function loadReminders(refresh = false) {
        loadRemindersside();
        if (refresh) {
            remindersOffset = 0;
            $("#reminders-containerl").empty();
            $("#load-more-reminders").prop("disabled", false).text("Load More").show();
        }

        // console.log("rem from "+remindersOffset);

        $.post("public/ajax/carriers_reminder_fetch.php",
            { supplier_id: supplierId, offset: remindersOffset, limit: remindersLimit },
            function(res) {
                if (res.reminders && res.reminders.length > 0) {
                    const containerl = $("#reminders-containerl");

                    res.reminders.forEach((reminder, index) => {
                        containerl.append(formatReminderListL(reminder));
                    });

                    // remindersOffset += res.reminders.length;

                    // if (res.reminders.length < remindersLimit) {
                        // $("#load-more-reminders").prop("disabled", true).hide();
                    // }
                    // console.log("rem page: "+res.pgno);
                    // console.log("rem offset: "+remindersOffset);

                    if(res.pgno==remindersOffset) {
                        $("#load-more-reminders").prop("disabled", true).hide();
                        // console.log("rem load more button hidden now: "+remindersOffset);
                    }
                    remindersOffset++;
                    // console.log("rem offset increment: "+remindersOffset);

                } else {
                    $("#load-more-reminders").prop("disabled", true).text("No reminders");
                }
            }, "json"
        );
    }

    function loadRemindersside() {
            $("#reminders-container").empty();
        $.post("public/ajax/carriers_reminder_fetch.php",
            { supplier_id: supplierId, offset: 0, limit: remindersLimit },
            function(res) {
                if (res.reminders && res.reminders.length > 0) {
                    const container = $("#reminders-container");

                    res.reminders.forEach((reminder, index) => {
                        
                        container.append(formatReminderList(reminder));
                    });
                }
                else {
                    $("#reminders-container").text("No reminders");
                }
            }, "json"
        );
    }


    // Example list formatter (already provided)
    function formatReminderList(reminder) {
        const formattedNote = reminder.note.replace(/\n/g, "<br>");
        return `
            <div class="note-card mb-2">
                <div class="note-header d-flex justify-content-between">
                    <span>${reminder.reminder_at}</span>
                    <span class="badge bg-primary">${reminder.type}</span>
                </div>
                <div class="note-text mt-1">${formattedNote}</div>
            </div>
        `;
    }

    function formatReminderListL(reminder) {
        const formattedNote = reminder.note.replace(/\n/g, "<br>");
        return `
            <div class="note-card mb-2" data-id="${reminder.id}">
                <div class="note-header d-flex justify-content-between align-items-center">
                    <span>${reminder.reminder_at}</span>
                    <span class="badge bg-primary">${reminder.type}</span>
                    <button type="button" class="btn btn-xs btn-outline-secondary ms-2 edit-reminder-btn" 
                            data-id="${reminder.id}" 
                            onclick='openEditReminderModal(${JSON.stringify(reminder)})' 
                            title="Edit Reminder">
                        <i class="fa fa-edit"></i>
                    </button>
                </div>
                <div class="note-text mt-1">${formattedNote}</div>
            </div>
        `;
    }

     // Example: load more button
     $("#load-more-reminders").on("click", function() {
         loadReminders();
     });

     // Initial load
     loadReminders(true);

// Handle Add New Reminder form
$("#new-reminder-form").on("submit", function(e) {
   e.preventDefault();

   let $msg  = $("#reminder-success-msg");
   let formData = $(this).serialize() + "&carrier_id=<?= $id ?? 0 ?>";

   $.ajax({
       url: "public/ajax/carriers_reminder_add.php",
       type: "POST",
       data: formData,
       dataType: "json",
       success: function(res) {
           if (res.success) {
               $("#new-reminder-form")[0].reset();
               $msg.text('Reminder added!')
                   .removeClass("text-danger")
                   .addClass("text-success")
                   .fadeIn(200).delay(1500).fadeOut(500);
               loadReminders(true);
           } else {
               $msg.text(res.error || "Failed to add reminder")
                   .removeClass("text-success")
                   .addClass("text-danger")
                   .fadeIn(200).delay(2000).fadeOut(500);
               // alert(res.msg || "Failed to add contact");
           }
       },
       error: function() {
           $msg.text("Error: Could not add reminder")
               .removeClass("text-success")
               .addClass("text-danger")
               .fadeIn(200).delay(2000).fadeOut(500);
       }
   });
});

// Handle first reminder form submit
$(document).on("submit", "#edit-reminder-form", function(e) {
    e.preventDefault();

    const $form = $(this);
    const supplierId = <?= $id ?? 0 ?>; // your global supplierId variable

    const formData = {
        reminder_id: $form.find("input[name='reminder_id']").val(),
        supplier_id: supplierId,
        type: $form.find("input[name='type']:checked").val(),
        reminder_date: $form.find("input[name='reminder_date']").val(),
        reminder_time: $form.find("input[name='reminder_time']").val(),
        notes: $form.find("textarea[name='notes']").val()
    };

    // const $msg = $('<span class="small mt-1"></span>').insertAfter($form.find("button[type='submit']"));
    const $msg = $('#first_rem');

    $.ajax({
        url: "public/ajax/carriers_reminder_update.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(res) {
            if (res.success) {
                $msg.text(res.msg)
                    .removeClass("text-danger")
                    .addClass("text-success")
                    .fadeIn(200).delay(1500).fadeOut(500);

                setTimeout(function() {
                    $("#editReminderModal").modal('hide');
                },500);
                
                // Optionally reload reminders list
                loadReminders(true);
            } else {
                $msg.text(res.msg || "Failed to update reminder")
                    .removeClass("text-success")
                    .addClass("text-danger")
                    .fadeIn(200).delay(2000).fadeOut(500);
            }
        },
        error: function() {
            $msg.text("Error: Could not update reminder")
                .removeClass("text-success")
                .addClass("text-danger")
                .fadeIn(200).delay(2000).fadeOut(500);
        }
    });
});

});
</script>
<script>
function reloadCarrier() {
    $.post("public/ajax/carriers_fetch.php", 
      { supplier_id: <?=$id??0?>, offset: 0, limit: 0 },
      function(res){
        if(res.success==1){
            Object.entries(res.fields).forEach(([key, val],ind) => {
                $("#"+key).val(val);
                if(key=='photo'&&val!='') {
                    $("#photod").show();
                    $("#photodimg").attr("src","uploads/carriers/"+val);
                }
                $("#"+key+"d").val(val);
            });

            setCarrier();
        } else {
            alert("Couldn't refrsh recruiter info");   
        }
      }, "json");
}
document.addEventListener('DOMContentLoaded', () => {
  const saveBtn = document.getElementById('saveCarrierInfo');

  // Show the display div and hide the edit form
  saveBtn.addEventListener('click', () => {
    $("#settingsModal").modal('hide');
    reloadCarrier();
  });
});
</script>
<script>
document.getElementById('photo').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const previewImg = document.getElementById('previewImg');

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      previewImg.src = e.target.result;
      previewImg.style.display = 'inline-block';
    }
    reader.readAsDataURL(file);
  } else {
    previewImg.src = '';
    previewImg.style.display = 'none';
  }
});

// email_filters --------------------------------------------
let saveTimer;
$("#email_filters").on("keyup change", ":input", function(){
    clearTimeout(saveTimer); // reset timer on every keypress/change

    let $input = $(this);
    let fieldName = $input.attr("name");
    let fieldValue = $input.val();

    // debounce: wait 500ms after typing before sending
    saveTimer = setTimeout(function(){
        $.ajax({
            url: "public/ajax/carriers_save.php",
            type: "POST",
            data: {
                supplier_id: "<?= $id ?? 0 ?>",
                field: fieldName,
                value: fieldValue
            },
            success: function(response){
                // console.log("Saved:", fieldName, "=", fieldValue, response);
                // optional: show a small ✅ indicator
                $input.addClass("is-valid");
                setTimeout(()=> $input.removeClass("is-valid"), 1500);
                // $("#"+fieldName).val(fieldValue);
                // setCarrier();
            },
            error: function(){
                console.error("Error updating recuiter info.");
                $input.addClass("is-invalid");
            }
        });
    }, 500); // adjust debounce delay as needed
});
</script>
<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const tab = this.getAttribute('data-tab');
    document.querySelectorAll('.tab-section').forEach(sec => sec.style.display = 'none');
    const active = document.getElementById(tab + '-section');
    if (active) active.style.display = 'block';

    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
});
</script>
<script>
    document.getElementById('save-email-filters').addEventListener('click', function() {
        // Get modal input values
        const domains = document.getElementById('fil_domains').value.trim();
        const emails = document.getElementById('fil_emails').value.trim();

        // Update the card display
        const cardHeader = document.querySelector('.card-header .small.text-muted');
        if(cardHeader) {
            cardHeader.innerHTML = `Domains: ${domains || 'None'}<br>Emails: ${emails || 'None'}`;
        }

        // Close the modal
        const modalEl = document.getElementById('editEmailFiltersModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
    });

function setPhotoId() {
    $("#viewIdModal").modal('hide');
    $("#settingsModal").modal('show');
}
</script>
<script>
let currentPage = 1;
const perPage = 10; // Emails per page
let allLoaded = false;

document.addEventListener('DOMContentLoaded', () => {
  loadEmailList();
  document.getElementById('loadMoreBtn').addEventListener('click', loadEmailList);
});

function loadEmailList() {
  if (allLoaded) return;

  fetch(`public/ajax/carriers_get_emails.php?id=<?=$id??0?>&page=${currentPage}&limit=${perPage}`)
    .then(response => response.json())
    .then(data => {
      const listContainer = document.getElementById('emailList');
      const noEmailsMsg = document.getElementById('noEmailsMessage');
      const loadMoreBtn = document.getElementById('loadMoreBtn');

      // If no emails
      if (data.emails.length === 0 && currentPage === 1) {
        noEmailsMsg.classList.remove('d-none');
        loadMoreBtn.classList.add('d-none');
        return;
      }
      noEmailsMsg.classList.add('d-none');

      // Append emails
      data.emails.forEach(email => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'list-group-item list-group-item-action email-item d-flex justify-content-between align-items-start';
        btn.dataset.id = email.id;
        btn.innerHTML = `
          <div class="me-auto">
            <div class="fw-semibold small text-truncate">${email.subject}</div>
            <div class="text-muted small text-truncate">${email.from} → ${email.to}</div>
            <div class="mt-1">
              <span class="badge bg-${email.direction === 'inbound' ? 'primary' : 'info'} text-light rounded-pill">${email.direction}</span>
              <small class="text-muted ms-2 float-end">${email.date}</small>
            </div>
          </div>
        `;
        btn.addEventListener('click', () => loadEmailContent(email.id, btn));
        listContainer.appendChild(btn);
      });

      // Load first email content automatically (only on first page)
      if (currentPage === 1 && data.emails.length > 0) {
        const firstBtn = listContainer.querySelector('.email-item');
        if (firstBtn) {
          firstBtn.classList.add('active');
          loadEmailContent(data.emails[0].id, firstBtn);
        }
      }

      // Pagination logic
      if (data.hasMore) {
        loadMoreBtn.classList.remove('d-none');
        currentPage++;
      } else {
        allLoaded = true;
        loadMoreBtn.classList.add('d-none');
      }
    })
    .catch(err => console.error('Error loading email list:', err));
}

function loadEmailContent(emailId, button) {
  document.querySelectorAll('.email-item').forEach(b => b.classList.remove('active'));
  button.classList.add('active');

  fetch(`public/ajax/carriers_get_emails_details.php?id=${emailId}`)
    .then(response => response.json())
    .then(email => {
      const pane = document.getElementById('emailReadingPane');
      pane.innerHTML = `
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <div class="fw-bold">${email.subject}</div>
            <div class="text-muted small">${email.from} → ${email.to}</div>
            <div class="text-muted small">${email.date}</div>
          </div>
          <!-- div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary">Reply</button>
            <button class="btn btn-sm btn-outline-secondary">Forward</button>
          </div -->
        </div>
        <pre class="small text-body" style="white-space: pre-wrap;">${email.body}</pre>
      `;
    })
    .catch(err => console.error('Error loading email content:', err));
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>