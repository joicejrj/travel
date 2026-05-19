<?php
// agent/dashboard.php — stylish dashboard with modal snooze and company fallback
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';


// Agent context: current logged-in user is the agent
$uid = $CURRENT_USER_ID; // from _auth.php
$current_agent_id = $uid;
$current_agent_name = isset($CURRENT_USER_NAME) ? $CURRENT_USER_NAME : (isset($current_agent_name) ? $current_agent_name : 'Agent');

// Default form values
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$name = $company = $phones = $phone = $whatsapp = $email = $address = $city = $state = $country = $services = $google_rating = $website = $type = $source = $photo = $photo1 = $agent_id = $user_name = $fil_emails = $fil_domains = '';

$is_edit = false; //($id > 0);

// If editing, fetch existing supplier
if ($id > 0) {
    if ($stmt = $mysqli->prepare("SELECT id, name, company, phones, phone, whatsapp, email, address, city, state, country, services, google_rating, website, type, source, photo, photo1, agent_id, fil_domains, fil_emails FROM contacts WHERE id = ? and agent_id= ?")) {
        $stmt->bind_param("ii", $id, $uid);
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
            $photo1  = $row['photo1'];
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

}

// prevent add here
if(!$is_edit) {
    $site->redirect('index.php?page=contacts');
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
    <style>
        #info label {
            font-size: 0.9em;
            font-weight: 500;
        }
    </style>
    <style>
        .btn-outline-secondary.btn-primary {
            color: #fff !important;
            background: #6c757d !important;
        }
        .form-check-input[type=radio] {
          border-color: #000;
        }
    </style>
  <div class="page-container" style="margin-top: -13px;">

    <!-- Header -->
<div class="header border-bottom pb-2 mb-3">
  <div class="d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">

    <!-- Left: Company Info -->
    <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
      <h4 class="mb-0 fw-bold" id="named"><?= htmlspecialchars($name) ?></h4>

      <button class="btn btn-xs btn-outline-primary text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#viewIdModal">
        <i class="fa fa-eye"></i> View Card
      </button>
      <button class="btn btn-xs btn-outline-secondary text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#settingsModal">
        <i class="fa fa-cog"></i> Settings
      </button>

      <button class="btn btn-outline-primary active btn-sm tab-btn fw-bold" style="margin-left: 20px;" data-tab="emails">Emails</button>
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" data-tab="reminders">Reminders</button>
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" data-tab="notes">Notes</button>
      
      <!-- Status & Contact -->
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="fw-semibold">Status:</span>
        <span class="badge 
          bg-<?=
            isset($type) && $type == 'Won' ? 'success' :
            (isset($type) && $type == 'Opportunity' ? 'primary' :
            (isset($type) && $type == 'Lead (Active)' ? 'warning' :
            (isset($type) && $type == 'Suspect' ? 'warning' :
            (isset($type) && $type == 'Archive' ? 'secondary' : 'light text-dark'))))
          ?>" id="typed" onclick="editStatus()" style="cursor: pointer;">
          <?= $type ? ucwords($type) : '' ?>
        </span>
        <span class="badge text-dark" id="emaild">
          <i class="fa fa-envelope me-1"></i><?= htmlspecialchars($email ?? '') ?>
        </span>
        <span class="badge text-dark" id="phoned">
          <?=$phone ??'<i class="fa fa-phone me-1"></i>'.htmlspecialchars($phone)??'' ?>
        </span>
      </div>


    </div>

    <!-- Right: Tabs -->
    <!-- <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 flex-shrink-0"> -->
    <!-- </div> -->

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
                      <input type="hidden" name="uname" value="<?=isset($_SESSION['person_name']) ? $_SESSION['person_name'] : 'Aleena'?>">
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
                        <label class="form-label mb-1 small">Reminder Type</label>
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

                      <!-- Quick Select Reminder Time -->
                      <div class="mb-3">
                        <label class="form-label mb-1 small">Reminder At</label>
                        <div class="d-flex flex-wrap gap-1">
                          <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill quick-reminder" data-days="1">Tomorrow</button>
                          <button type="button" class="btn btn-outline-secondary btn-primary btn-xs rounded-pill quick-reminder" data-days="7">1 Week Later</button>
                          <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill quick-reminder" data-days="30">1 Month Later</button>
                          <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill quick-reminder" data-days="90">3 Months Later</button>
                          <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill quick-reminder" data-days="custom">Other</button>
                        </div>
                      </div>

                      <!-- Date and Time -->
                      <div id="customDateTime" class="row g-2 mb-3" style="display:none;">
                        <div class="col-md-6">
                          <label class="small mb-1">Date</label>
                          <input type="date" class="form-control form-control-xs rounded-pill" name="reminder_date" id="reminder-date" value="<?=date("Y-m-d",strtotime("+7 days",strtotime($date)))?>" required>
                        </div>
                        <div class="col-md-6">
                          <label class="small mb-1">Time</label>
                          <input type="time" class="form-control form-control-xs rounded-pill" name="reminder_time" id="reminder-time" value="10:00" required>
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

          </div>

        </div>

      </div>
    </div>
  </div>


<!-- Contact Info Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" id="settingsModaldg">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="settingsModalLabel">
          <i class="fa fa-user-edit me-2"></i>Edit Contact Information
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body text-dark " id="info">
        <form id="info-wrapper" class="row g-3 text-black">

          <!-- Name -->
          <div class="col-md-4 settinginp">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control form-control-sm rounded-pill" 
                   name="name" id="name" placeholder="Rahul Dev" 
                   value="<?= htmlspecialchars($name ?? '') ?>">
          </div>

          <!-- Email -->
          <div class="col-md-4 settinginp">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control form-control-sm rounded-pill" 
                   name="email" id="email" placeholder="rahul@example.com"
                   value="<?= htmlspecialchars($email ?? '') ?>">
          </div>

          <!-- Phone -->
          <div class="col-md-4 settinginp">
            <label for="phone" class="form-label">Mobile</label>
            <input type="text" class="form-control form-control-sm rounded-pill" 
                   name="phone" id="phone" placeholder="+91 9876543210" 
                   value="<?= htmlspecialchars($phone ?? '') ?>">
          </div>

          <!-- Status -->
          <!-- <div class="col-md-6">
            <label for="type" class="form-label">Status</label>
            <select class="form-select form-select-sm rounded-pill" name="type" id="type">
              <option value="Suspect" <?= ($type ?? '') === 'Suspect' ? 'selected' : '' ?>>Suspect — not verified</option>
              <option value="Lead (Active)" <?= ($type ?? '') === 'Lead (Active)' ? 'selected' : '' ?>>Lead (Active)</option>
              <option value="Opportunity" <?= ($type ?? '') === 'Opportunity' ? 'selected' : '' ?>>Opportunity</option>
              <option value="Won" <?= ($type ?? '') === 'Won' ? 'selected' : '' ?>>Won</option>
              <option value="Archive" <?= ($type ?? '') === 'Archive' ? 'selected' : '' ?>>Archive</option>
            </select>
          </div> -->

          <!-- Status -->
          <div class="col-12 settinginp settinginpStatus">
            <label class="form-label fw-semibold mb-1 settinginp">Status</label>
            <div class="d-flex flex-column gap-1">
              <?php
              $statuses = [
                'Suspect' => 'Suspect — not verified',
                'Lead (Active)' => 'Lead (Active) — you’ve reached out / they responded',
                'Opportunity' => 'Opportunity — quote/proposal/demo sent; decision pending',
                'Won' => 'Won — became a customer',
                'Archive' => 'Archive — not a fit or chose someone else'
              ];
              foreach($statuses as $value => $label): ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="type" id="status_<?= str_replace([' ', '(', ')'],['_','', ''], $value) ?>" 
                         value="<?= $value ?>" <?= ($type ?? '') === $value ? 'checked' : '' ?>>
                  <label class="form-check-label text-black" for="status_<?= str_replace([' ', '(', ')'],['_','', ''], $value) ?>">
                    <?= $label ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Photos -->
          <div class="col-md-6 settinginp settinginpImg">
            <label class="form-label">Upload Image 1</label>
            <input type="file" class="form-control form-control-sm rounded-pill" 
                   name="photo" id="photo" accept="image/*">
            <div id="photo1Preview" class="mt-2">
              <?php if(!empty($photo)): ?>
                <img src="uploads/contacts/<?= htmlspecialchars($photo) ?>" alt="Photo 1" class="img-thumbnail" style="max-width: 100px;">
              <?php endif; ?>
            </div>
          </div>

          <div class="col-md-6 settinginp settinginpImg">
            <label class="form-label">Upload Image 2</label>
            <input type="file" class="form-control form-control-sm rounded-pill" 
                   name="photo1" id="photo1" accept="image/*">
            <div id="photo2Preview" class="mt-2">
              <?php if(!empty($photo1)): ?>
                <img src="uploads/contacts/<?= htmlspecialchars($photo1) ?>" alt="Photo 2" class="img-thumbnail" style="max-width: 100px;">
              <?php endif; ?>
            </div>
          </div>

          <!-- Country -->
          <!-- <div class="col-md-6 settinginp">
            <label for="country" class="form-label">Country</label>
            <input type="text" class="form-control form-control-sm rounded-pill" 
                   name="country" id="country" placeholder="Country Name" 
                   value="<?= htmlspecialchars($country ?? '') ?>">
          </div> -->

        </form>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" id="saveContactInfo" class="btn btn-primary btn-sm">
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

      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="viewIdModalLabel"><i class="fa fa-id-card me-2"></i>View Card</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-3 text-center" style="min-height: 83vh;">

          <!-- Tabs for Image 1 and Image 2 -->
          <ul class="nav nav-tabs mb-3" id="idTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="image1-tab" data-bs-toggle="tab" data-bs-target="#image1" type="button" role="tab" aria-controls="image1" aria-selected="true">Image 1</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="image2-tab" data-bs-toggle="tab" data-bs-target="#image2" type="button" role="tab" aria-controls="image2" aria-selected="false">Image 2</button>
            </li>
          </ul>

          <div class="tab-content">
            <div class="tab-pane fade show active" id="image1" role="tabpanel" aria-labelledby="image1-tab" style="min-height: 40vh; align-content: center;">
              <?php if (!empty($photo)): ?>
                <img src="uploads/contacts/<?= htmlspecialchars($photo) ?>" 
                     alt="ID Image 1" class="img-fluid rounded shadow-sm" 
                     style="max-height: 70vh; object-fit: contain;" id="photodimg">
              <?php else: ?>
                <div class="text-muted">No Image 1 uploaded</div>
                <span class="btn btn-primary btn-xs" onclick="setPhotoId()">Upload Card</span>
              <?php endif; ?>
            </div>
            <div class="tab-pane fade" id="image2" role="tabpanel" aria-labelledby="image2-tab" style="min-height: 40vh; align-content: center;">
              <?php if (!empty($photo1)): ?>
                <img src="uploads/contacts/<?= htmlspecialchars($photo1) ?>" 
                     alt="ID Image 2" class="img-fluid rounded shadow-sm" 
                     style="max-height: 70vh; object-fit: contain;" id="photo1dimg">
              <?php else: ?>
                <div class="text-muted">No Image 2 uploaded</div>
                <span class="btn btn-primary btn-xs" onclick="setPhotoId()">Upload Card</span>
              <?php endif; ?>
            </div>
          </div>

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

          <!-- Quick Select Reminder Time -->
          <div class="mb-3">
            <label class="form-label mb-1 small fw-bold">Reminder At</label>
            <div class="d-flex flex-wrap gap-1">
              <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill edit-quick-reminder" data-days="1">Tomorrow</button>
              <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill edit-quick-reminder" data-days="7">1 Week Later</button>
              <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill edit-quick-reminder" data-days="30">1 Month Later</button>
              <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill edit-quick-reminder" data-days="90">3 Months Later</button>
              <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill edit-quick-reminder" data-days="custom">Other</button>
            </div>
          </div>

          <!-- Date and Time -->
          <div id="editCustomDateTime" class="row g-2 mb-3" style="display:none;">
            <div class="col-md-6">
              <label class="small mb-1">Date</label>
              <input type="date" class="form-control form-control-xs rounded-pill" name="reminder_date" id="edit-reminder-date" required>
            </div>
            <div class="col-md-6">
              <label class="small mb-1">Time</label>
              <input type="time" class="form-control form-control-xs rounded-pill" name="reminder_time" id="edit-reminder-time" required>
            </div>
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

<script>
document.addEventListener("DOMContentLoaded", () => {
  const quickBtns = document.querySelectorAll('.edit-quick-reminder');
  const dateInput = document.getElementById('edit-reminder-date');
  const timeInput = document.getElementById('edit-reminder-time');
  const customDiv = document.getElementById('editCustomDateTime');

  quickBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const days = btn.dataset.days;
      const now = new Date();

      quickBtns.forEach(b => b.classList.remove('btn-primary'));
      btn.classList.add('btn-primary');

      if (days === 'custom') {
        customDiv.style.display = 'flex';
        dateInput.required = true;
        timeInput.required = true;
      } else {
        customDiv.style.display = 'none';
        dateInput.required = false;
        timeInput.required = false;

        now.setDate(now.getDate() + parseInt(days));
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const dd = String(now.getDate()).padStart(2, '0');
        dateInput.value = `${yyyy}-${mm}-${dd}`;
        timeInput.value = '10:00';
      }
    });
  });
});

function setContact() {
    // var source = $("#source").val();
    var type = $("#type").val();
    var name = $("#name").val();
    var phone = $("#phone").val();
    var email = $("#email").val();

    // source = source.toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
    // $("#sourced").text(source);
    $("#named").text(name);
    $("#typed").text(type);
    $("#emaild").html('<i class="fa fa-envelope me-1"></i> '+email);
    $("#phoned").html('<i class="fa fa-phone me-1"></i>'+phone);
}
</script>

<script>
    $(document).ready(function(){
        
        // supplier info --------------------------------------------
        let saveTimer;
        $("#info").on("keyup change", ":input", function(){
            clearTimeout(saveTimer); // reset timer on every keypress/change

            let $input = $(this);
            let fieldName = $input.attr("name");
            let fieldValue = $input.val();

            // Skip saving if name or email is empty
            if ((fieldName === "name" || fieldName === "email") && fieldValue === "") {
                $input.addClass("is-invalid");
                return; // stop here — don't send AJAX
            } else {
                $input.removeClass("is-invalid");
            }

            // debounce: wait 500ms after typing before sending
            saveTimer = setTimeout(function(){
                $.ajax({
                    url: "public/ajax/contacts_save.php",
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
                        setContact();
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
                url: "public/ajax/contacts_save.php",
                type: "POST",
                data: formData,
                processData: false,  // important
                contentType: false,  // important
                success: function(response){
                    // optional: show preview or a ✅ indicator
                    if(response.success && response.photo){
                        $("#photo1Preview").html('<img src="uploads/contacts/'+ response.photo +'" alt="Photo 1" class="img-thumbnail" style="max-width: 100px;">');
                        $("#image1").html('<img id="idImage" src="uploads/contacts/'+response.photo+'" alt="ID Image" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain;">');
                    }
                },
                error: function(){
                    console.error("Error uploading photo.");
                }
            });
        });
        // File upload for photo1
        $("#photo1").on("change", function() {
            let fileInput = this;
            if (fileInput.files.length === 0) return; // no file selected

            let formData = new FormData();
            formData.append("supplier_id", "<?= $id ?? 0 ?>");
            formData.append("field", "photo1");
            formData.append("photo1", fileInput.files[0]);

            $.ajax({
                url: "public/ajax/contacts_save.php",
                type: "POST",
                data: formData,
                processData: false,  // important
                contentType: false,  // important
                success: function(response){
                    // optional: show preview or a ✅ indicator
                    if(response.success && response.photo){
                        $("#photo2Preview").html('<img src="uploads/contacts/'+ response.photo +'" alt="Photo 2" class="img-thumbnail" style="max-width: 100px;">');
                        $("#image2").html('<img id="idImage" src="uploads/contacts/'+response.photo+'" alt="ID Image" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain;">');
                    }
                },
                error: function(){
                    console.error("Error uploading photo.");
                }
            });
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
                url: "public/ajax/contacts_logs_save.php",
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

            $.post("public/ajax/contacts_logs_fetch.php", 
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

            $.post("public/ajax/contacts_call_logs.php", 
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

  // Set reminder ID
  document.getElementById('edit_reminder_id').value = reminder.id || '';

  // Handle reminder_at like "05 Nov 2025 10:00 AM" or "2025-11-05 10:00:00"
  const input = reminder.reminder_at || '';
  let dateObj = new Date(input);
  if (isNaN(dateObj)) {
    // Try to manually parse formats like "05 Nov 2025 10:00 AM"
    const parts = input.match(/(\d{1,2}) (\w{3}) (\d{4}) (\d{1,2}):(\d{2}) (AM|PM)/i);
    if (parts) {
      const [, day, mon, year, hour, minute, ampm] = parts;
      const months = {
        Jan: 0, Feb: 1, Mar: 2, Apr: 3, May: 4, Jun: 5,
        Jul: 6, Aug: 7, Sep: 8, Oct: 9, Nov: 10, Dec: 11
      };
      let h = parseInt(hour, 10);
      if (ampm.toUpperCase() === 'PM' && h < 12) h += 12;
      if (ampm.toUpperCase() === 'AM' && h === 12) h = 0;
      dateObj = new Date(year, months[mon], day, h, minute);
    }
  }
  // Convert to input values
  if (!isNaN(dateObj)) {
    const date = dateObj.toISOString().slice(0, 10); // YYYY-MM-DD
    const time = dateObj.toTimeString().slice(0, 5); // HH:MM
    document.getElementById('edit-reminder-date').value = date;
    document.getElementById('edit-reminder-time').value = time;
  } else {
    document.getElementById('edit-reminder-date').value = '';
    document.getElementById('edit-reminder-time').value = '';
  }

  // Notes
  document.getElementById('edit-reminder-notes').value = reminder.note || '';

  // Set reminder type radio
  ['Callback', 'Follow-up', 'Send Email', 'Other'].forEach(type => {
    const radio = document.getElementById('edit-type1-' + type.toLowerCase().replace(' ', ''));
    if (radio) radio.checked = (reminder.type === type);
  });

  // Handle quick reminder buttons
  const quickBtns = modalEl.querySelectorAll('.edit-quick-reminder');
  const customDiv = document.getElementById('editCustomDateTime');
  const dateInput = document.getElementById('edit-reminder-date');
  const timeInput = document.getElementById('edit-reminder-time');

  quickBtns.forEach(b => b.classList.remove('btn-primary'));

  // Detect which button fits the date difference
  const now = new Date();
  const remindDate = new Date(reminder.reminder_at);
  const diffDays = Math.round((remindDate - now) / (1000 * 60 * 60 * 24));

  let matched = false;
  quickBtns.forEach(btn => {
    const days = btn.dataset.days;
    if (days !== 'custom' && Math.abs(diffDays - parseInt(days)) <= 1) {
      btn.classList.add('btn-primary');
      customDiv.style.display = 'none';
      matched = true;
    }
  });

  // If no matching quick button, show custom date/time
  if (!matched) {
    const customBtn = modalEl.querySelector('.edit-quick-reminder[data-days="custom"]');
    if (customBtn) customBtn.classList.add('btn-primary');
    customDiv.style.display = 'flex';
  }

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

        $.post("public/ajax/contacts_reminder_fetch.php",
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
        $.post("public/ajax/contacts_reminder_fetch.php",
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
            <div class="note-card mb-2 p-1 border-bottom" data-id="${reminder.id}">
                <div class="note-header d-flex justify-content-between align-items-center">
                    <span class="text-black fw-semibold">${reminder.reminder_at}</span>
                    <span class="badge bg-primary">${reminder.type}</span>
                    <button type="button" class="btn btn-xs btn-outline-secondary ms-2 edit-reminder-btn" 
                            data-id="${reminder.id}" 
                            onclick='openEditReminderModal(${JSON.stringify(reminder)})' 
                            title="Edit Reminder">
                        <i class="fa fa-edit"></i>
                    </button>
                </div>
                <div class="note-text">${formattedNote}</div>
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
   let formData = $(this).serialize() + "&contact_id=<?= $id ?? 0 ?>";

   $.ajax({
       url: "public/ajax/contacts_reminder_add.php",
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
        url: "public/ajax/contacts_reminder_update.php",
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
function reloadContact() {
    $.post("public/ajax/contacts_fetch.php", 
      { supplier_id: <?=$id??0?>},
      function(res){
        if(res.success==1){
            Object.entries(res.fields).forEach(([key, val],ind) => {
                if(key=='photo'&&val!='') {
                    // $("#photod").show();
                    $("#photodimg").attr("src","uploads/contacts/"+val);
                }
                else if(key=='photo1'&&val!='') {
                    // $("#photo1d").show();
                    $("#photo1dimg").attr("src","uploads/contacts/"+val);
                }
                else if(key=='type') {
                    $("#typed").text(val);
                }
                else {
                    $("#"+key).val(val);
                }
                // $("#"+key+"d").val(val);
                
                if(key=='tclass') {
                    $("#typed").removeClass('bg-info').removeClass('bg-warning').removeClass('bg-secondary').removeClass('bg-success').removeClass('bg-danger').removeClass('bg-primary').removeClass('bg-light');
                    $("#typed").addClass(val);
                }
            });

            // setContact();
        } else {
            alert("Couldn't refrsh recruiter info");   
        }
      }, "json");
}
document.addEventListener('DOMContentLoaded', () => {
  const saveBtn = document.getElementById('saveContactInfo');

  // Show the display div and hide the edit form
  saveBtn.addEventListener('click', () => {
    $("#settingsModal").modal('hide');
    reloadContact();
  });
});
</script>
<script>
  // Preview Image 1
  document.getElementById('photo').addEventListener('change', function(e){
    const file = e.target.files[0];
    const preview = document.getElementById('photo1Preview');
    preview.innerHTML = '';
    if(file){
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.className = 'img-thumbnail';
      img.style.maxWidth = '100px';
      preview.appendChild(img);
    }
  });

  // Preview Image 2
  document.getElementById('photo1').addEventListener('change', function(e){
    const file = e.target.files[0];
    const preview = document.getElementById('photo2Preview');
    preview.innerHTML = '';
    if(file){
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.className = 'img-thumbnail';
      img.style.maxWidth = '100px';
      preview.appendChild(img);
    }
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
function setPhotoId() {
    $("#viewIdModal").modal('hide');
    $(".settinginp").hide();
    $(".settinginpImg").show();
    $("#settingsModaldg").removeClass('modal-md');
    $("#settingsModaldg").addClass('modal-lg');
    $("#settingsModalLabel").html('<i class="fa fa-user-edit me-2"></i>Update Card');
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

  fetch(`public/ajax/contacts_get_emails.php?id=<?=$id??0?>&page=${currentPage}&limit=${perPage}`)
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
          <div class="me-auto" style="width: 100%;">
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

  fetch(`public/ajax/contacts_get_emails_details.php?id=${emailId}`)
    .then(response => response.json())
    .then(email => {
      const pane = document.getElementById('emailReadingPane');

      // Build attachments section if any
      let attachmentsHTML = '';
      if (email.attachments && email.attachments.length > 0) {
        attachmentsHTML = `
          <div class="mt-3">
            <div class="fw-bold small mb-1"><i class="fa fa-paperclip"></i> Attachments (${email.attachments.length})</div>
            <div class="d-flex flex-wrap gap-2">
              ${email.attachments.map(a => `
                <a href="${a.url}" target="_blank" class="btn btn-sm btn-outline-secondary">
                  <i class="fa fa-file"></i> ${a.filename} <small class="text-muted">(${a.size})</small>
                </a>
              `).join('')}
            </div>
          </div>
        `;
      }

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
        ${attachmentsHTML}
      `;
    })
    .catch(err => console.error('Error loading email content:', err));
}
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const quickBtns = document.querySelectorAll('.quick-reminder');
  const dateInput = document.getElementById('reminder-date');
  const timeInput = document.getElementById('reminder-time');
  const customDiv = document.getElementById('customDateTime');

  quickBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const days = btn.dataset.days;
      const now = new Date();

      quickBtns.forEach(b => b.classList.remove('btn-primary'));
      btn.classList.add('btn-primary');

      if (days === 'custom') {
        customDiv.style.display = 'flex';
        dateInput.required = true;
        timeInput.required = true;
      } else {
        customDiv.style.display = 'none';
        dateInput.required = false;
        timeInput.required = false;

        now.setDate(now.getDate() + parseInt(days));
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const dd = String(now.getDate()).padStart(2, '0');
        dateInput.value = `${yyyy}-${mm}-${dd}`;
        timeInput.value = '10:00';
      }
    });
  });
});

function editStatus() {
    $(".settinginp").hide();
    $(".settinginpStatus").show();
    $("#settingsModaldg").removeClass('modal-lg');
    $("#settingsModaldg").addClass('modal-md');
    $("#settingsModalLabel").html('<i class="fa fa-user-edit me-2"></i>Edit Status');
    $("#settingsModal").modal('show');
}

$("#settingsModal").on('hidden.bs.modal', function () {
    $("#settingsModaldg").removeClass('modal-md');
    $("#settingsModaldg").addClass('modal-lg');
    $("#settingsModalLabel").html('<i class="fa fa-user-edit me-2"></i>Edit Contact Information');
    $(".settinginp").show();
    reloadContact();
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>