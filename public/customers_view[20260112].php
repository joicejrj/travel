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
$id = $customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$name = $company = $industry = $trn = $phones = $phone = $whatsapp = $email = $enable_email = $enable_whatsapp = $address = $city = $state = $country = $services = $google_rating = $website = $type = $source = $photo = $photo1 = $agent_id = $user_name = $fil_emails = $fil_domains = $dob = '';

$is_edit = false; //($id > 0);

// If editing, fetch existing supplier
if ($id > 0) {
    if ($stmt = $mysqli->prepare("SELECT id, name, company, industry, trn, phones, phone, whatsapp, email, enable_email, enable_whatsapp, address, city, state, country, services, google_rating, website, type, source, photo, photo1, agent_id, fil_domains, fil_emails, dob FROM customers WHERE id = ?")) { // and agent_id= ?
        $stmt->bind_param("i", $id); // i, $uid
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $is_edit  = true;
            $name     = $row['name'];
            $company  = $row['company'];
            $industry  = $row['industry'];
            $trn  = $row['trn'];
            $phones    = $row['phones'];
            $phone    = $row['phone'];
            $whatsapp    = $row['whatsapp'];
            $email    = $row['email'];
            $enable_email    = $row['enable_email'];
            $enable_whatsapp    = $row['enable_whatsapp'];
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
            $dob  = $row['dob'];
        }
        $stmt->close();
    }

}

// prevent add here
if(!$is_edit) {
    $site->redirect('index.php?page=customers');
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

        /* Chat-like layout: left / right halves */
  #whatsappMessages { width:100%; display:flex; flex-direction:column; gap:10px; }

  .msg-row { display:flex; width:100%; }
  .msg-row.incoming { justify-content:flex-start; }
  .msg-row.outgoing { justify-content:flex-end; }

  /* Each bubble container occupies half width (minus gap) */
  .bubble-wrap {
    display:block;
    width:50%;
    box-sizing:border-box;
  }

  /* Use inner bubble for visuals */
  .chat-bubble {
    padding: 10px 12px;
    border-radius: 12px;
    font-size:.95rem;
    line-height:1.25;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    word-break: break-word;
  }
  .chat-bubble.incoming {
    background: #f1f0f0;
    color: #111;
    border-top-left-radius: 6px;
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
    border-bottom-left-radius: 12px;
    margin-right: 10px;
  }
  .chat-bubble.outgoing {
    background: #dcf8c6; /* pale green */
    color: #111;
    border-top-right-radius: 6px;
    border-top-left-radius: 12px;
    border-bottom-right-radius: 12px;
    border-bottom-left-radius: 12px;
    margin-left: 10px;
  }

  .chat-meta { font-size:.75rem; color:#666; margin-top:6px; }
  .interactive-title { font-weight:700; margin-top:8px; display:block; }
  .interactive-desc { font-size:.85rem; color:#495057; margin-top:4px; }

  /* Ensure small screens don't force half-width - stack full width */
  @media (max-width: 520px) {
    .bubble-wrap { width:100%; }
  }

  /* Minimal spacing for "No older messages" state */
  #whats-load-older[disabled] { opacity: .9; cursor: default; }

  .template-btn-pill {
    border-radius: 20px !important;
    padding: 2px 10px !important;
    font-size: 0.78rem !important;
}

#sessionBanner {
    display: none;
    margin-bottom: 8px;
    padding: 8px 12px;
    border-radius: 6px;
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
    font-size: 0.92rem;
  }
  #whatsappMessageInput[disabled] { background:#f8f9fa; opacity:0.9; }

  .chat-media { margin-bottom: 6px; }
.chat-media img { box-shadow: 0 1px 3px rgba(0,0,0,0.12); }
.chat-media-caption { font-size: 0.85rem; color: #444; margin-top: 6px; }
.chat-doc-link { display:inline-block; padding:6px 10px; border-radius:6px; background:#f5f5f5; text-decoration:none; color:#333; }

.chat-media img { max-width: 240px; max-height: 320px; border-radius: 8px; display:block; }
.attach-item { display:flex; gap:10px; align-items:flex-start; padding:8px; border-radius:6px; background:#f8f9fa; }
.attach-thumb { width:80px; height:80px; object-fit:cover; border-radius:6px; background:#eee; flex:0 0 80px; }
.attach-meta { flex:1; display:flex; flex-direction:column; gap:6px; }
.attach-filename { font-weight:600; font-size:0.95rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.attach-actions { display:flex; gap:6px; align-items:center; }
.attach-caption { width:100%; min-height:44px; }
    </style>

<script src="public/assets/js/jselect1.js?jv=<?=time()?>"></script>
<script src="public/assets/js/sweetalert.js?jv=<?=time()?>"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

  <div class="page-container" style="margin-top: -13px;">

    <!-- Header -->
<div class="header border-bottom pb-2 mb-3x">
  <div class="d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">

    <!-- Left: Company Info -->
    <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
      <h4 class="mb-0 fw-bold" id="named"><?= htmlspecialchars($company) ?> / <?= htmlspecialchars($name) ?></h4>

      <button class="btn btn-xs btn-outline-primary text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#viewIdModal" title="View Card"><i class="fa fa-eye"></i></button>
      <button class="btn btn-xs btn-outline-secondary text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#settingsModal" title="Settings"><i class="fa fa-cog"></i></button>
      <!-- <button class="btn btn-xs btn-outline-primary text-dark fw-bold" onclick="openSitesModal(<?=$customer_id?>);" title="View Sites"><i class="fa fa-map"></i></button> -->

      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" style="<?=$enable_whatsapp=='1'?'':'display:none'?>" id="whatsappbtn" data-tab="whatsapp">WhatsApp</button>
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" style="<?=$enable_email=='1'?'':'display:none'?>" id="emailbtn" data-tab="emails">Emails</button>
      <!-- <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" data-tab="quotations">Quotes</button> -->
      <!-- <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" data-tab="requirements">Jobs</button> -->
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" data-tab="documents">Docs</button>
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" id="notesBtn" data-tab="notes">Notes</button>
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" id="remindersBtn" data-tab="reminders">Reminders</button>
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" data-tab="contacts">Contacts</button>
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" data-tab="invoices">Invoices</button>
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" data-tab="payments">Payments</button>
      <button class="btn btn-outline-primary btn-sm tab-btn fw-bold active" data-tab="timeline">Timeline</button>
      <!-- <button class="btn btn-outline-primary btn-sm tab-btn fw-bold" data-tab="rates">Rates</button> -->
      
      <!-- Status & Contact -->
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="fw-semibold">Customer Status:</span>
        <span class="badge 
                bg-<?=
                  isset($type) && $type == 'Active' ? 'success' :
                  (isset($type) && $type == 'Prospect' ? 'primary' :
                  (isset($type) && $type == 'Work in progress' ? 'warning' :
                  (isset($type) && $type == 'Suspect' ? 'warning' :
                  (isset($type) && $type == 'Inactive' ? 'warning' :
                  (isset($type) && $type == 'Dead' ? 'secondary' : 'light text-dark')))))
                ?>" id="typed" onclick="editStatus()" style="cursor: pointer;">
                <?= $type ? ucwords($type) : '' ?>
              </span>
        
        <span class="badge text-dark" id="emaild">
          <i class="fa fa-envelope me-1"></i><?= htmlspecialchars($email ?? '') ?>
        </span>
        <span class="badge text-dark" id="phoned">
          <?=$phone!=''?'<i class="fa fa-phone me-1"></i>'.htmlspecialchars($phone):'' ?>
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

            <!-- Whatsapp Tab Content -->
            <div id="whatsapp-section" class="tab-section" style="display:none;">
                <div class="section-scroll section-emailscroll">
                    
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center bg-white">
                          <div><i class="fa fa-whatsapp me-2 text-success"></i> <strong>WhatsApp</strong></div>
                          <div class="d-flex align-items-center gap-2">
                      <button id="whatsappRefreshBtn" class="btn btn-sm btn-outline-secondary" title="Refresh messages">
                        <i class="fa fa-sync" aria-hidden="true"></i>
                        <span class="ms-1">Refresh</span>
                      </button>

                      <!-- optional: small auto-refresh toggle (comment out if not wanted) -->
                      <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="whatsappAutoRefreshToggle" title="Auto refresh" />
                        <label class="form-check-label small text-muted" for="whatsappAutoRefreshToggle">Auto</label>
                      </div>
                    </div>
                        </div>

                        <div class="card-body d-flex flex-column" style="max-height:360px;">
                          <!-- chat container (scrollable) -->
                          <div id="whatsappChatContainer" class="section-scroll p-2" style="flex:1; max-height:60vh; overflow:auto; display:flex; flex-direction:column;">
                            <div id="whats-load-older-container" 
                             style="width:100%; text-align:center; margin-bottom:10px;">
                            <button id="whats-load-older" class="btn btn-sm btn-outline-secondary">
                                Load older
                            </button>
                        </div>
                            <!-- messages injected here (old at top, new at bottom) -->
                            <div id="whatsappMessages" style="display:flex; flex-direction:column; gap:10px;"></div>
                          </div>


                          <div id="whatsappComposer" class="mt-2 p-2 border-top bg-white">
                      <form id="whatsapp-send-form" class="d-flex flex-column gap-2" enctype="multipart/form-data">
                        <div class="d-flex gap-2 align-items-start">
                          <textarea id="whatsappMessageInput" name="message" class="form-control form-control-sm" placeholder="Write a message..." rows="5" required></textarea>

                          <div class="d-flex flex-column gap-2" style="min-width:160px;">
                            <label class="btn btn-outline-secondary btn-sm mb-0" style="cursor:pointer;">
                              <i class="fa fa-paperclip"></i> Attach
                              <input type="file" id="whatsappFiles" name="media[]" multiple style="display:none" accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip,text/plain">
                            </label>

                            <!-- Template button (uses same button style pattern as the page) -->
                            <button type="button" id="whatsappTemplateBtn" class="btn btn-outline-secondary btn-sm mb-0" title="Use template">
                              <i class="fa fa-file-alt"></i> Template
                            </button>

                            <button type="submit" id="whatsappSendBtn" class="btn btn-primary btn-sm"><i class="fa fa-paper-plane me-1"></i> Send</button>
                          </div>

                        </div>

                        <div id="whatsappAttachPreview" class="d-flex flex-wrap gap-2 small"></div>

                      </form>
                    </div>

                          
                        </div>
                      </div>


                </div>
            </div>
            <!-- Template modal (Bootstrap-style) -->
            <div class="modal fade" id="whatsappTemplateModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Select WhatsApp Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <div id="templateLoading" class="small text-muted">Loading templates…</div>

                    <div id="templateList" class="my-2 d-flex flex-wrap gap-1" style="max-height:220px; overflow:auto;"></div>

                    <div id="templatePreviewWrap" style="display:none;">
                      <h6 class="mt-3">Preview</h6>
                      <!-- read-only preview area (no editing allowed) -->
                      <div id="templatePreview" class="p-2 border rounded small" style="background:#f8f9fa; min-height:80px; white-space:pre-wrap;"></div>

                    </div>
                  </div>

                  <div class="modal-footer">
                    <button type="button" id="templateCancelBtn" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="templateSendBtn" class="btn btn-primary btn-sm">Send Template</button>
                  </div>
                </div>
              </div>
            </div>
            <!-- Attachment caption modal (WhatsApp-style) -->
            <div class="modal fade" id="whatsappAttachModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-paperclip"></i> Add caption & send</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div id="attachList" class="d-flex flex-column gap-2">
                      <!-- JS will populate list of selected files -->
                    </div>
                    <div class="small text-muted mt-2">You can send images or documents. Add caption for each file before sending.</div>
                  </div>
                  <div class="modal-footer">
                    <button id="attachModalCancel" type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="attachModalSend" type="button" class="btn btn-primary">Send</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Emails Tab Content -->
            <div id="emails-section" class="tab-section" style="display:none;">
                <div class="section-scroll section-emailscroll">
                    
                    <div class="card shadow-sm rounded-3 mb-3">
                      <div class="card-header bg-white border-0 d-flex align-items-center">
                        <i class="fa fa-envelope me-2 text-primary" style="font-size: 1.2em;"></i>
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

            <!-- Documents Section -->
            <div id="documents-section" class="tab-section" style="display:none;">
              <div class="section-scroll">

                <div class="card shadow-sm border-0 mb-3">
                  <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">
                      <i class="fa fa-file me-2 text-primary"></i> Documents
                    </h5>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                      <i class="fa fa-plus me-1"></i> Add Document
                    </button>
                  </div>
                </div>

                <!-- Document List -->
                <div id="documents-list" class="table-responsive">
                  <table class="table table-xs table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Label</th>
                        <th>Type</th>
                        <th>Expiry Date</th>
                        <th>File</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="documents-body">
                      <tr><td colspan="5" class="text-center small text-muted">Loading documents...</td></tr>
                    </tbody>
                  </table>
                </div>

              </div>
            </div>

            <!-- Requirements Tab Content -->
            <div id="requirements-section" class="tab-section" style="display:none;">
              <div class="section-scroll1">

                <!-- Header -->
                <div class="card shadow-sm border-0 mb-3">
                  <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">
                      <i class="fa fa-clipboard-list me-2 text-primary"></i> Job Requirements
                    </h5>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRequirementModal">
                      <i class="fa fa-plus me-1"></i> New Requirement
                    </button>
                  </div>
                </div>

                <!-- Requirements List -->
                <div id="requirements-list" class="section-scroll" style="max-height: 520px;"></div>
              </div>
            </div>

            <!-- Contacts Section -->
            <div id="contacts-section" class="tab-section" style="display:none;">
              <div class="section-scroll">

                <div class="card shadow-sm border-0 mb-3">
                  <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">
                      <i class="fa fa-address-book me-2 text-primary"></i> Contacts
                    </h5>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addContactModal">
                      <i class="fa fa-plus me-1"></i> New Contact
                    </button>
                  </div>
                </div>

                <!-- Contacts List -->
                <div id="contacts-list">
                  <p class="text-muted text-center small">Loading contacts...</p>
                </div>

              </div>
            </div>

            <!-- Notes Tab Content -->
            <div id="notes-section" class="tab-section" style="display:none;">
                <div class="section-scroll"  style="max-height: 520px;">

                <!-- Notes Form -->
                <div class="card">
                  <div class="card-header bg-white border-0 d-flex align-items-center">
                    <i class="fa fa-sticky-note me-2"></i>
                    <strong>Add Notes</strong>
                  </div>

                  <div class="card-body section-scroll">
                    <form id="supplier-note-form">
                      <input type="hidden" name="uname" value="<?=isset($_SESSION['person_name']) ? $_SESSION['person_name'] : 'Aleena'?>">

                      <!-- Note Type -->
                      <div class="mb-2">
                        <label class="form-label mb-1 small fw-bold">Note Type</label>
                        <div class="d-flex flex-wrap gap-1" id="noteTypeButtons">
                          <button type="button" class="btn btn-outline-primary btn-xs rounded-pill note-type-btn" data-type="Call">
                            <i class="fa fa-phone me-1"></i> Call
                          </button>
                          <button type="button" class="btn btn-outline-primary btn-xs rounded-pill note-type-btn" data-type="Email">
                            <i class="fa fa-envelope me-1"></i> Email
                          </button>
                          <button type="button" class="btn btn-outline-primary btn-xs rounded-pill note-type-btn" data-type="Meeting">
                            <i class="fa fa-handshake me-1"></i> Meeting
                          </button>
                          <button type="button" class="btn btn-outline-primary btn-xs rounded-pill note-type-btn" data-type="General">
                            <i class="fa fa-sticky-note me-1"></i> General
                          </button>
                        </div>
                        <input type="hidden" name="type" id="note_type" value="General">
                      </div>

                      <!-- Visibility -->
                      <div class="mb-2">
                        <label class="form-label mb-1 small fw-bold">Visibility</label>
                        <div class="d-flex flex-wrap gap-1" id="noteVisibilityButtons">
                          <button type="button" class="btn btn-outline-success btn-xs rounded-pill note-visibility-btn" data-vis="Public">
                            <i class="fa fa-globe me-1"></i> Public
                          </button>
                          <button type="button" class="btn btn-outline-success btn-xs rounded-pill note-visibility-btn" data-vis="Private">
                            <i class="fa fa-lock me-1"></i> Private
                          </button>
                        </div>
                        <input type="hidden" name="visibility" id="note_visibility" value="Public">
                      </div>

                      <!-- Notes -->
                      <div class="mb-2">
                        <textarea class="form-control form-control-sm" name="notes" rows="3" placeholder="Write your notes..." required></textarea>
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

                  <div id="previousNotes" class="collapse show section-scroll">
                    <div class="card-body p-1"
                         style="max-height: 300px; overflow-y:auto; scrollbar-width: thin;"
                         id="notes-container">
                      <!-- Notes loaded dynamically here -->
                    </div>
                    <div class="card-footer text-center p-1 bg-white">
                      <button id="load-more-notes" class="btn btn-light btn-xs w-100 mt-2">
                        Load More
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Edit Note Modal -->
                <div class="modal fade" id="editNoteModal" tabindex="-1" aria-labelledby="editNoteModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header py-2">
                        <h6 class="modal-title fw-semibold" id="editNoteModalLabel">
                          <i class="fa fa-edit me-2 text-primary"></i>Edit Note
                        </h6>
                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
                      </div>

                      <div class="modal-body py-2 px-3">
                        <form id="edit-note-form">
                          <input type="hidden" name="id" id="edit_note_id">

                          <!-- Type -->
                          <div class="mb-2">
                            <label class="form-label mb-1 small fw-bold">Note Type</label>
                            <div class="d-flex flex-wrap gap-1" id="editNoteTypeButtons">
                              <button type="button" class="btn btn-outline-primary btn-xs rounded-pill edit-note-type" data-type="Call"><i class="fa fa-phone me-1"></i> Call</button>
                              <button type="button" class="btn btn-outline-primary btn-xs rounded-pill edit-note-type" data-type="Email"><i class="fa fa-envelope me-1"></i> Email</button>
                              <button type="button" class="btn btn-outline-primary btn-xs rounded-pill edit-note-type" data-type="Meeting"><i class="fa fa-handshake me-1"></i> Meeting</button>
                              <button type="button" class="btn btn-outline-primary btn-xs rounded-pill edit-note-type" data-type="General"><i class="fa fa-sticky-note me-1"></i> General</button>
                            </div>
                            <input type="hidden" name="type" id="edit_note_type" value="General">
                          </div>

                          <!-- Visibility -->
                          <div class="mb-2">
                            <label class="form-label mb-1 small fw-bold">Visibility</label>
                            <div class="d-flex flex-wrap gap-1" id="editNoteVisibilityButtons">
                              <button type="button" class="btn btn-outline-success btn-xs rounded-pill edit-note-vis" data-vis="Public"><i class="fa fa-globe me-1"></i> Public</button>
                              <button type="button" class="btn btn-outline-success btn-xs rounded-pill edit-note-vis" data-vis="Private"><i class="fa fa-lock me-1"></i> Private</button>
                            </div>
                            <input type="hidden" name="visibility" id="edit_note_visibility" value="Public">
                          </div>

                          <!-- Notes -->
                          <div class="mb-2">
                            <textarea class="form-control form-control-sm rounded-3" name="notes" id="edit_note_text" rows="3" placeholder="Update your note..." required></textarea>
                          </div>

                          <!-- Save -->
                          <div class="d-flex justify-content-end mt-2">
                            <button type="submit" class="btn btn-success btn-xs rounded-pill">
                              <i class="fa fa-save me-1"></i> Update Note
                            </button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>


                </div>
            </div>

            <!-- Quotations Tab Content -->
            <style>
              .text-2xl { font-size: 1.5rem !important; }
              .bg-primary-subtle { background-color: rgba(13, 110, 253, 0.08) !important; }
              .border-primary-subtle { border-color: rgba(13, 110, 253, 0.15) !important; }
            </style>

            <div id="quotations-section" class="tab-section section-scroll" style="display:none;">
              <div class="">
                <!-- Quotations Section -->
                <div class="card border-0 shadow-sm rounded-3">
                  <div class="card-header bg-white border-0 pb-0">
                    <h3 class="fw-semibold text-2xl d-flex align-items-center gap-2 mb-0">
                      <i class="fa fa-file-text text-primary fs-5"></i> Quotation
                    </h3>
                  </div>

                  <div class="card-body pt-3 pb-4 px-4">

                    <!-- Latest Quotation Highlight -->
                    <div id="latestQuotation" class="p-3 bg-light border border-primary-subtle rounded-3 mb-3">
                      <!-- Latest quotation dynamically loaded here -->
                      <div class="text-center text-muted small py-2">
                        <i class="fa fa-spinner fa-spin"></i> Loading latest quotation...
                      </div>
                    </div>

                    <!-- Older Versions -->
                    <div class="table-responsive">
                      <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                          <tr>
                            <th>Ref</th>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Created By</th>
                            <th class="text-end">Actions</th>
                          </tr>
                        </thead>
                        <tbody id="quotationList">
                          <tr>
                            <td colspan="5" class="text-center text-muted small">Loading quotations...</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <div class="text-end mt-3">
                      <button class="btn btn-primary btn-sm" id="addQuotationBtn">
                        <i class="fa fa-plus"></i> New Quotation
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
                        <div class="mb-3">
                          <label class="form-label mb-1 small">Reminder Type</label>
                          <div class="d-flex flex-wrap gap-1" id="reminderTypeButtons">
                            <button type="button" class="btn btn-outline-primary btn-xs rounded-pill reminder-type" data-type="Call">
                              <i class="fa fa-phone me-1"></i> Call
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-xs rounded-pill reminder-type" data-type="Email">
                              <i class="fa fa-envelope me-1"></i> Email
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-xs rounded-pill reminder-type" data-type="Meeting">
                              <i class="fa fa-handshake me-1"></i> Meeting
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-xs rounded-pill reminder-type" data-type="General">
                              <i class="fa fa-sticky-note me-1"></i> General
                            </button>
                          </div>
                          <input type="hidden" name="type" id="reminder_type" value="Call">
                        </div>

                        <!-- Optional Contact Selection -->
                        <div class="mb-3" id="reminderContacts">
                          <label class="form-label mb-1 small">Select Contact (Optional)</label>
                          <div id="contactSelectButtons" class="d-flex flex-wrap gap-1">
                            <span class="text-muted small">Loading contacts...</span>
                          </div>
                          <input type="hidden" name="contact_id" id="contact_selection" value="">
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
                        <textarea class="form-control form-control-xs rounded-3 mt-2" name="notes" id="notes-field" rows="2" placeholder="Write notes here..." required></textarea>
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

            <!-- Invoices Section -->
            <style>
              .jsumbtn {
                line-height: 1em !important;
                font-weight: bold;
              }

              /* Invoice Row */
              .invoice-row {
                line-height: 1.15;
              }

              .invoice-line-1 {
                line-height: 1.15;
                gap: 0.4rem;
              }

              /* Notes */
              .invoice-note {
                max-width: 520px;
                display: inline-block;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                vertical-align: bottom;
              }

              /* Dates */
              .invoice-dates strong {
                font-size: 0.7rem;
                font-weight: 600;
              }

              .invoice-dates i {
                font-size: 0.7rem;
              }

              .date-sep {
                opacity: 0.6;
              }

              /* Badges */
              .badge {
                font-size: 0.68rem;
              }

              /* Buttons */
              .btn-group-sm .btn {
                padding: 0.22rem 0.38rem;
              }

              /* Mobile Optimization */
              @media (max-width: 768px) {
                .invoice-note {
                  max-width: 100%;
                }

                .btn-group {
                  margin-left: 0;
                }

                .date-sep {
                  display: none;
                }
              }
            </style>

            <div id="invoices-section" class="tab-section" style="display:none;">
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-white">

                        <div class="card-header1 bg-white">
                          <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <!-- LEFT: Title -->
                            <h6 class="fw-semibold mb-2 mb-md-0">
                              <i class="fa fa-file-invoice me-2 text-primary"></i> Invoices
                            </h6>
                            <!-- CENTER: Filter + Summary -->
                            <div class="d-flex flex-wrap align-items-center mb-2 mb-md-0">
                              <div class="me-3">
                                <span class="btn btn-xs btn-outline-success bg-success1 jsumbtn" id="totalReceived">
                                  Received: 0
                                </span>
                                <span class="btn btn-xs btn-outline-danger bg-danger1 jsumbtn" id="totalSent">
                                  Sent: 0
                                </span>
                              </div>
                              <input type="text" id="idaterange"
                                     class="form-control form-control-sm rounded-pill"
                                     style="width: 200px;" placeholder="Select Date Range">
                                <button class="btn btn-outline-secondary btn-sm ms-2" id="clearInvoiceFilter">
                                  <i class="fa fa-times"></i>
                                </button>
                            </div>
                            <!-- RIGHT: Add Invoice -->
                            <button class="btn btn-sm btn-success rounded-pill" id="addInvoiceBtn">
                              <i class="fa fa-plus"></i> Add Invoice
                            </button>
                          </div>
                        </div>

                    </div>
                  <div class="card-body p-2 section-scroll" id="invoice-list" style="max-height: 480px;">
                    <p class="text-muted text-center small">Loading invoices...</p>
                  </div>
                </div>
            </div>
            <!-- Invoice Modal -->
            <div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">

                  <div class="modal-header py-2">
                    <h6 class="modal-title fw-semibold" id="invoiceModalLabel">
                      <i class="fa fa-file-invoice me-2 text-primary"></i>
                      <span id="invoiceModalTitle">Add Invoice</span>
                    </h6>
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
                  </div>

                  <form id="invoiceForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="invoice_id">
                    <input type="hidden" name="customer_id" value="<?= $customer_id ?? 0 ?>">

                    <div class="modal-body py-2 px-3">

                      <!-- DATE BUTTONS -->
                      <div class="mb-3">
                        <label class="form-label small fw-semibold">Invoice Date</label>

                        <div class="btn-group w-100 mb-2" id="dateButtons">
                          <button type="button" class="btn btn-outline-primary btn-xs date-btn" data-value="<?=date('Y-m-d')?>">Today</button>
                          <button type="button" class="btn btn-outline-primary btn-xs date-btn" data-value="<?=date('Y-m-d', strtotime('-1 day'))?>">Yesterday</button>
                          <button type="button" class="btn btn-outline-primary btn-xs date-btn" data-value="other">Other</button>
                        </div>

                        <input type="date" class="form-control form-control-xs rounded-pill d-none"
                               id="invoice_date_other"
                               placeholder="Select date...">

                        <input type="hidden" name="invoice_date" id="invoice_date">
                      </div>

                      <div class="mb-3 row">
                        <div class="col-md-4">
                          <!-- TYPE -->
                          <label for="invoice_amount" class="form-label small fw-semibold">Invoice Amount</label>
                          <input type="number" class="form-control form-control-xs rounded-pill"
                                 name="invoice_amount" id="invoice_amount"
                                 step="0.01" placeholder="Enter amount">
                          <small class="badge bg-info" id="invoice_sum" style="display: none !important;">Total: 0.00</small>
                        </div>
                        <div class="col-md-4">
                          <label for="vat_amount" class="form-label small fw-semibold">VAT Amount</label>
                          <input type="number" class="form-control form-control-xs rounded-pill"
                                 name="vat_amount" id="vat_amount"
                                 step="0.01" placeholder="Enter VAT amount">
                        </div>
                        <div class="col-md-4">
                          <!-- INVOICE AMOUNT -->
                          <label class="form-label small fw-semibold">Invoice Type</label>
                          <select name="invoice_type" id="invoice_type"
                                  class="form-control form-control-xs rounded-pill jselect" data-class="outline-primary btn-xs">
                            <option value="Received">Received</option>
                            <option value="Sent">Sent</option>
                          </select>
                        </div>
                      </div>

                      <!-- CATEGORY (LOADED BY AJAX BASED ON TYPE) -->
                      <div class="mb-3">
                        <label class="form-label small fw-semibold">Invoice Category
                          <button type="button" class="btn btn-outline-success rounded-pill btn-xs"
                                  style="padding:0em 1em;"
                                  id="addCategoryBtni">
                            <i class="fa fa-plus"></i> New
                          </button>
                        </label>
                        <select name="invoice_category" id="invoice_category"
                          class="form-control form-control-xs rounded-pill jselect" data-class="outline-success btn-xs">
                          <!-- <option value="">Loading...</option> -->
                        </select>
                      </div>

                      <!-- DOCUMENT UPLOAD -->
                      <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control form-control-sm rounded-pill" value="<?=date("Y-m-d",strtotime("+7 days"))?>">
                      </div>

                      <!-- DOCUMENT UPLOAD -->
                      <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">Upload Document (Image / PDF)</label>
                        <input type="file" name="document" id="document" accept=".pdf,image/*"
                               class="form-control form-control-sm rounded-pill">
                        <div id="documentPreview" class="mt-2"></div>
                      </div>

                      <!-- NOTES -->
                      <div class="mb-2">
                        <label class="form-label small fw-semibold">Notes</label>
                        <textarea name="notes" id="notesi"
                          class="form-control form-control-xs rounded-3" rows="2"></textarea>
                      </div>

                    </div>

                    <div class="modal-footer py-2">
                      <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save me-1"></i> Save Invoice
                      </button>
                    </div>

                  </form>
                </div>
              </div>
            </div>

            <!-- 🟢 ADD CATEGORY MODAL -->
            <div class="modal fade" id="addCategoryModali" tabindex="-1" aria-labelledby="addCategoryLabeli" aria-hidden="true" data-bs-backdrop="static">
              <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content rounded-3 shadow">
                  <div class="modal-header py-2">
                    <h6 class="modal-title" id="addCategoryLabeli">
                      <i class="fa fa-plus-circle me-1"></i> Add New Category
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <form id="addCategoryFormi" class="p-3">
                    <div class="mb-2">
                      <label for="new_category_name" class="form-label small mb-1">Category Name</label>
                      <input type="text" class="form-control form-control-sm" id="new_category_namei" name="category" placeholder="Enter category name" required>
                    </div>
                    <div class="text-end">
                      <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save me-1"></i> Save
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <script>
                $(function () {
                  const addCatModal = new bootstrap.Modal('#addCategoryModali');

                  // 🟢 Show add-category modal
                  $("#addCategoryBtni").on("click", function () {
                    $("#new_category_namei").val('');
                    addCatModal.show();
                    setTimeout(() => $("#new_category_namei").trigger("focus"), 300);
                  });

                  // 🟢 Submit new category
                  $("#addCategoryFormi").on("submit", function (e) {
                    e.preventDefault();

                    const category = $("#new_category_namei").val().trim();
                    let itype = $("#invoice_type").val()?.trim() || 'Expense';

                    if (!category) {
                      alert("Please enter a category name.");
                      return;
                    }

                    $.ajax({
                      url: "public/ajax/invoices_categories1.php",
                      type: "POST",
                      dataType: "json",
                      data: {
                        action: "add",   // 🟢 tells PHP switch which action to run
                        category: category,
                        type: itype
                      },
                      beforeSend: function () {
                        $("#addCategoryFormi button[type='submit']")
                          .prop("disabled", true)
                          .html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                      },
                      success: function (res) {
                        $("#addCategoryFormi button[type='submit']")
                          .prop("disabled", false)
                          .html('<i class="fa fa-save me-1"></i> Save');

                        if (res.success) {
                          addCatModal.hide();

                            // ✅ Add to dropdown dynamically
                            var $select = $("#invoice_category");
                            $select.html(''); // prevent loading other category
                            if ($select.find(`option[value="${category}"]`).length === 0) {
                              $select.append(`<option value="${category}">${category}</option>`);
                              const newOpt = $select.find(`option[value="${category}"]`)[0];
                              newOpt.dataset.class = "outline-success"; // optional
                              $select[0].addNewOptionButton(newOpt);
                            }
                            $("#invoice_category").attr("name", "invoice_category");
                            refreshJSelect("invoice_category");
                            $("#invoice_category").val(category).trigger("change");

                            $("input[type='hidden'][name='invoice_category']").val(category);

                            generateAutoNotesinvoice();

                          // Optional alert/toast (your custom function)
                          salert("Category Added", `"${category}" was added successfully.`, "success");
                        } else {
                          salert("Failed", res.error || "Failed to add category.", "error");
                        }
                      },
                      error: function () {
                        $("#addCategoryFormi button[type='submit']")
                          .prop("disabled", false)
                          .html('<i class="fa fa-save me-1"></i> Save');
                        alert("Error while adding category.");
                      }
                    });
                  });
                });
            </script>


            <!-- Payments Section -->
            <div id="payments-section" class="tab-section" style="display:none;">
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-white d-flex align-items-center gap-2 flex-wrap">
                        <h6 class="fw-semibold mb-0 me-auto">
                            <i class="fa fa-file-payment me-2 text-primary"></i> 
                            Payments
                        </h6>
                        <!-- Date Range -->
                        <input type="text" id="p_daterange" class="form-control form-control-sm" style="width:200px;">
                        <!-- Clear Filter -->
                        <button class="btn btn-outline-secondary btn-sm" id="clearPaymentFilter">
                          <i class="fa fa-times"></i>
                        </button>
                        <!-- Add New -->
                        <button class="btn btn-sm btn-success rounded-pill" id="addpaymentBtn">
                          <i class="fa fa-plus"></i> Add Payment
                        </button>
                    </div>

                    <!-- SUMMARY AREA -->
                    <div id="payment-summary" class="px-3 pb-2"></div>


                  <div class="card-body p-2 section-scroll" id="payment-list" style="max-height: 420px;">
                    <p class="text-muted text-center small">Loading payments...</p>
                  </div>
                </div>
            </div>
            <!-- payment Modal -->
            <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header py-2">
                    <h6 class="modal-title fw-semibold" id="paymentModalLabel">
                      <i class="fa fa-file-payment me-2 text-primary"></i>
                      <span id="paymentModalTitle">Add Payment for Customer <?=$name?></span>
                    </h6>
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
                  </div>

                  <form id="paymentForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="payment_id">
                    <input type="hidden" name="customer_id" value="<?= $customer_id ?? 0 ?>">

                    <div class="modal-body py-2 px-3">

                      <!-- Date + Type -->
                      <div class="mb-3 row">
                        <div class="col-md-4">
                          <label for="payment_date" class="form-label fw-semibold small mb-1">Date</label>
                          <select name="payment_date" id="payment_date"
                                  class="form-control form-control-xs rounded-pill jselect"
                                  data-class="outline-primary btn-xs" data-type="date" required>
                            <option value="<?=date("Y-m-d",strtotime($date))?>">Today</option>
                            <option value="<?=date("Y-m-d",strtotime("+1 day",strtotime($date)))?>">Tomorrow</option>
                            <option value="Other" data-other="true">Other</option>
                          </select>
                        </div>

                        <div class="col-md-4">
                          <label for="payment_amount" class="form-label fw-semibold small mb-1">Amount</label>
                          <input type="number" name="payment_amount" id="payment_amount"
                                 step="0.01" class="form-control form-control-xs rounded-pill"
                                 value="0" placeholder="Payment Amount..">
                        </div>

                        <div class="col-md-4">
                          <label for="payment_type" class="form-label fw-semibold small mb-1 d-flex align-items-center">
                            Type
                            <i class="fa fa-info-circle ms-1 text-primary"
                               data-bs-toggle="tooltip"
                               style="cursor:pointer;"
                               title="Income = Money RECEIVED from customer.&#10;Expense = Money SPENT for customer."></i>
                          </label>
                          <select name="payment_type" id="payment_type"
                                  class="form-control form-control-xs rounded-pill jselect"
                                  data-class="outline-primary btn-xs" required>
                            <option value="Income">Income</option>
                            <option value="Expense">Expense</option>
                          </select>
                        </div>

                        <div class="col-md-4" style="display:none;">
                          <label for="reclaim_by" class="form-label fw-semibold small mb-1">Reclaim By</label>
                          <select name="reclaim_by" id="reclaim_by"
                                  class="form-control form-control-xs rounded-pill jselect"
                                  data-class="outline-primary btn-xs">
                            <option value="Company">Company</option>
                            <option value="Employee">Employee</option>
                          </select>
                        </div>
                      </div>

                      <!-- ◼️ CATEGORY (UNCHANGED – AS YOU REQUESTED) -->
                      <div class="mb-3">
                        <label for="payment_category" class="form-label fw-semibold small mb-1">
                          Category
                          <button type="button" class="btn btn-outline-success rounded-pill btn-xs"
                                  style="padding:0em 1em;"
                                  id="addCategoryBtn">
                            <i class="fa fa-plus"></i> New
                          </button>
                        </label>

                        <select name="payment_category" id="payment_category"
                                class="form-control form-control-xs jselect"
                                data-class="outline-success btn-xs">

                          <?php
                            $getpcats = $db->get('payment_categories',array('#all'=>1));
                            foreach ($getpcats->data as $pk => $pcat) {
                          ?>
                              <option value="<?=$pcat->category?>"><?=$pcat->category?></option>
                          <?php } ?>

                        </select>
                      </div>

                      <!-- AMOUNT FIRST -->
                      <div class="row">
                        <!-- PAYMENT STATUS -->
                        <div class="col-md-4">
                          <label class="form-label fw-semibold small">Payment Status</label>
                          <select name="payment_status" id="payment_status"
                                  class="form-control form-control-xs jselect"
                                  data-class="outline-primary btn-xs" required>
                            <option value="Unpaid">Unpaid</option>
                            <option value="Paid">Paid</option>
                            <option value="Partial Paid">Partial Paid</option>
                          </select>
                        </div>

                        <div class="col-md-4" id="payment_partiald" style="display:none;">
                          <label for="payment_partial" class="form-label fw-semibold small mb-1">Partial Amount</label>
                          <input type="number" name="payment_partial" id="payment_partial"
                                 step="0.01" class="form-control form-control-xs rounded-pill"
                                 value="0" placeholder="Partial Amount..">
                        </div>
                          
                          <!-- PAYMENT METHOD -->
                          <div class="col-md-8" id="payment_payment_methodd">
                            <label for="payment_payment_method" class="form-label fw-semibold small mb-1">Payment Method</label>
                            <select name="payment_payment_method" id="payment_payment_method"
                                    class="form-control form-control-xs jselect"
                                    data-class="outline-success btn-xs">
                              <option value="Cash">Cash</option>
                              <option value="Card">Card</option>
                              <option value="Bank Transfer">Bank Transfer</option>
                              <option value="Cheque">Cheque</option>
                            </select>
                          </div>

                      </div>

                      <!-- CARD LAST 4 -->
                      <div class="col-md-4 d-none" id="card_last4_field">
                        <label class="form-label fw-semibold small mb-1">Last 4 digits of card</label>
                        <input type="text" maxlength="4"
                               class="form-control form-control-xs rounded-pill"
                               name="card_last4" id="card_last4">
                      </div>

                      <!-- CHEQUE FIELDS -->
                      <div class="row d-none" id="cheque_fields">
                        <div class="col-md-6">
                          <label class="form-label fw-semibold small mb-1">Bank Name</label>
                          <input type="text" class="form-control form-control-xs rounded-pill"
                                 name="cheque_bank" id="cheque_bank">
                        </div>

                        <div class="col-md-6">
                          <label class="form-label fw-semibold small mb-1">Issuer Name</label>
                          <input type="text" class="form-control form-control-xs rounded-pill"
                                 name="cheque_issuer" id="cheque_issuer">
                        </div>
                      </div>

                      <!-- REIMBURSABLE -->
                      <div class="row d-none" id="reimbursable_section">
                        <div class="col-md-4">
                          <label class="form-label fw-semibold small mb-1">Reimbursable?</label>
                          <select name="reimbursable" id="reimbursable" data-class="outline-success btn-xs"
                                  class="form-control form-control-xs rounded-pill jselect">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                          </select>
                        </div>

                        <div class="col-md-4 d-none" id="reimbursement_amount_field">
                          <label class="form-label fw-semibold small mb-1">Reimbursement Amount</label>
                          <input type="number" step="0.01"
                                 class="form-control form-control-xs rounded-pill"
                                 name="reimbursement_amount" id="reimbursement_amount">
                        </div>
                      </div>

                      <!-- DOCUMENT UPLOAD -->
                      <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">Upload Document(s) (Image / PDF)</label>
                        <input type="file" name="document[]" id="documentp"
                               accept=".pdf,image/*"
                               class="form-control form-control-sm rounded-pill" multiple>

                        <div id="documentPreviewp" class="mt-2 d-flex flex-wrap gap-2"></div>
                        <div id="existingDocuments" class="mt-2"></div>
                      </div>

                      <!-- Notes -->
                      <div class="mb-2">
                        <label for="notes" class="form-label fw-semibold small mb-1">Notes</label>
                        <textarea name="notes" id="notesp"
                                  class="form-control form-control-xs rounded-3"
                                  rows="2"></textarea>
                      </div>

                    </div>

                    <div class="modal-footer py-2">
                      <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save me-1"></i> Save payment
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- 🟢 ADD CATEGORY MODAL -->
            <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryLabel" aria-hidden="true" data-bs-backdrop="static">
              <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content rounded-3 shadow">
                  <div class="modal-header py-2">
                    <h6 class="modal-title" id="addCategoryLabel">
                      <i class="fa fa-plus-circle me-1"></i> Add New Category
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <form id="addCategoryForm" class="p-3">
                    <div class="mb-2">
                      <label for="new_category_name" class="form-label small mb-1">Category Name</label>
                      <input type="text" class="form-control form-control-sm" id="new_category_name" name="category" placeholder="Enter category name" required>
                    </div>
                    <div class="text-end">
                      <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save me-1"></i> Save
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <script>
                $(function () {
                  const addCatModal = new bootstrap.Modal('#addCategoryModal');

                  // 🟢 Show add-category modal
                  $("#addCategoryBtn").on("click", function () {
                    $("#new_category_name").val('');
                    addCatModal.show();
                    setTimeout(() => $("#new_category_name").trigger("focus"), 300);
                  });

                  // 🟢 Submit new category
                  $("#addCategoryForm").on("submit", function (e) {
                    e.preventDefault();

                    const category = $("#new_category_name").val().trim();
                    let itype = $("#payment_type").val()?.trim() || 'Expense';

                    if (!category) {
                      alert("Please enter a category name.");
                      return;
                    }

                    $.ajax({
                      url: "public/ajax/invoices_categories.php",
                      type: "POST",
                      dataType: "json",
                      data: {
                        action: "add",   // 🟢 tells PHP switch which action to run
                        category: category,
                        type: itype
                      },
                      beforeSend: function () {
                        $("#addCategoryForm button[type='submit']")
                          .prop("disabled", true)
                          .html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                      },
                      success: function (res) {
                        $("#addCategoryForm button[type='submit']")
                          .prop("disabled", false)
                          .html('<i class="fa fa-save me-1"></i> Save');

                        if (res.success) {
                          addCatModal.hide();

                            // ✅ Add to dropdown dynamically
                            var $select = $("#payment_category");
                            if ($select.find(`option[value="${category}"]`).length === 0) {
                              $select.append(`<option value="${category}">${category}</option>`);
                              const newOpt = $select.find(`option[value="${category}"]`)[0];
                              newOpt.dataset.class = "outline-success"; // optional
                              $select[0].addNewOptionButton(newOpt);
                            }
                            $select.val(category).trigger("change");
                            $("input[type='hidden'][name='payment_category']").val(category);

                            generateAutoNotes();

                          // Optional alert/toast (your custom function)
                          salert("Category Added", `"${category}" was added successfully.`, "success");
                        } else {
                          salert("Failed", res.error || "Failed to add category.", "error");
                        }
                      },
                      error: function () {
                        $("#addCategoryForm button[type='submit']")
                          .prop("disabled", false)
                          .html('<i class="fa fa-save me-1"></i> Save');
                        alert("Error while adding category.");
                      }
                    });
                  });

                  // 🟢 When payment type changes, reload category options dynamically
                  // $("#payment_type").on("change", function () {
                  $("#payment_type").on("change", function (event, selectedCategory = null) {
                    const type = $(this).val()?.trim() || 'Expense';
                    var $select = $("#payment_category");
                    var ecat = selectedCategory || $("#payment_category").val();

                    // Show loading state
                    $select.html('<option value="">Loading...</option>').prop("disabled", true);

                    $.ajax({
                      url: "public/ajax/invoices_categories.php",
                      type: "POST",
                      dataType: "json",
                      data: { action: "fetch", type: type },
                      success: function (res) {
                        $select.empty(); // clear old options

                        if (res.success && res.data && res.data.length > 0) {
                          // Populate dropdown
                          res.data.forEach(cat => {
                            $select.append(`<option value="${cat.category}" ${ecat==cat.category?'selected':''}>${cat.category}</option>`);
                          });

                            // Apply FIRST to select
                            $select.val(ecat);

                            // Rebuild UI
                            $("#payment_category").attr("name", "payment_category");
                            refreshJSelect("payment_category");
                            // Re-apply to hidden input
                            // const wrap  = $select.closest(".jselect-wrapper");
                            // wrap.find("input[type=hidden]").val(ecat);
                            // Re-highlight button
                            // wrap.find(".jselect-btn").each(function () {
                                // $(this).toggleClass("active", $(this).data("value") == ecat);
                            // });

                            // 🔥 Rebuild jSelect UI (buttons, etc.)
                            // $("#payment_category").attr("name", "payment_category").trigger("change");
                            // alert(ecat);
                            // alert($("#payment_category").val());
                            // $select.val(ecat);
                            // $("#payment_category").attr("name", "payment_category");

                            // Force-select category AFTER jSelect is rebuilt
                            // $("#payment_category").val(ecat);
                            // Update the hidden input created by jSelect
                            // const wrap = $("#payment_category").closest(".jselect-wrapper");
                            // const hidden = wrap.find("input[type=hidden]");
                            // if (hidden.length) hidden.val(ecat);
                            // $("#payment_category").trigger("change");
                            // refreshJSelect("payment_category");
                            // $("#invoice_type").attr("name", "invoice_type");
                            // refreshJSelect("invoice_type");


                          // ✅ Select first option automatically
                          // $select.prop("selectedIndex", 0).trigger("change");
                        } else {
                          // If no categories found
                          $select.html('<option value="">No categories found</option>');
                          $("#payment_category").attr("name", "payment_category").trigger("change");
                          refreshJSelect("payment_category");
                        }

                        generateAutoNotes();

                        $select.prop("disabled", false);


                            // Force-select category AFTER jSelect is rebuilt
                            // $("#payment_category").val(ecat);
                            // Update the hidden input created by jSelect
                            // const wrap = $("#payment_category").closest(".jselect-wrapper");
                            // const hidden = wrap.find("input[type=hidden]");
                            // if (hidden.length) hidden.val(ecat);
                            // $("#payment_category").trigger("change");
                            // $("#payment_type").attr("name", "payment_type");
                            // refreshJSelect("payment_type");

                      },
                      error: function (xhr, status, err) {
                        console.error("Category load failed:", err);
                        $select.html('<option value="">Error loading categories</option>').prop("disabled", false);
                      }
                    });
                  });

                });
                </script>


                <!-- Timeline Tab Content -->
                <div id="timeline-section" class="tab-section section-scroll">
                  <style>
                    #customerTimelineSection .timeline {
                        position: relative;
                        padding-left: 25px;
                        border-left: 2px solid #0d6efd33;
                    }
                    #customerTimelineSection .timeline-date-header {
                        font-size: 0.78rem;
                        font-weight: 700;
                        color: #0d6efd;
                        margin: 12px 0 4px 4px;
                        opacity: 0.9;
                    }
                    #customerTimelineSection .timeline-item {
                        position: relative;
                        display: flex;
                        gap: 12px;
                        margin-bottom: 18px;
                    }
                    .timeline-bullet {
                        width: 32px;
                        height: 32px;
                        flex: 0 0 32px;       /* 🔥 prevents shrinking */
                        border-radius: 50%;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        font-size: 0.75rem;
                        background: #f1f5ff;
                        border: 2px solid #d1e2ff;
                    }
                    .timeline-icon-success { background: #e7f9ee; border-color: #b8ecc6; }
                    .timeline-icon-warning { background: #fff6dd; border-color: #ffe8b0; }
                    .timeline-icon-danger  { background: #fde2e4; border-color: #f8b6b9; }
                    .timeline-icon-info    { background: #e0f3ff; border-color: #b4e2fc; }
                    .timeline-content { flex-grow: 1; }
                    #customerTimelineSection .timeline-date,
                    .timeline-time {
                        font-size: 0.8rem;
                        font-weight: 600;
                        color: #6c757d;
                    }
                    #customerTimelineSection .timeline-text {
                        font-size: 0.95rem;
                        color: #444;
                        line-height: 1.35rem;
                    }
                    #customerTimelineSection .timeline-agent {
                        font-size: 0.75rem;
                        color: #0d6efd;
                        margin-top: 2px;
                        display: block;
                        opacity: 0.85;
                    }

                  </style>
                    <div id="customerTimelineSection" class="card border-0 shadow-sm rounded-3 mt-2">
                      <div class="card-header bg-white border-bottom-1 d-flex align-items-center justify-content-between">
                        <h5 class="fw-semibold mb-0 small text-primary">
                          <i class="fa fa-stream me-1"></i> Customer Timeline
                        </h5>
                        <button class="btn btn-xs btn-outline-secondary" id="refreshTimeline">
                          <i class="fa fa-sync"></i> Refresh
                        </button>
                      </div>

                      <div class="card-body py-2" id="customerTimelineBox" style="max-height:450px; overflow-y:auto;">
                        
                        <!-- ⭐ This wrapper matches your JS selector -->
                        <div id="customerTimeline">
                          <div class="text-center text-muted small py-3">Loading timeline...</div>
                        </div>

                      </div>

                      <div class="card-footer bg-white text-center py-2 d-none" id="timelineLoadMoreWrapper">
                        <button class="btn btn-outline-primary btn-xs rounded-pill" id="loadMoreTimeline">
                          <i class="fa fa-chevron-down"></i> Load More
                        </button>
                      </div>
                    </div>
                </div>
                <script>
                  $(function() {
                    const customerId = <?= $customer_id ?? 0 ?>;
                    const logURL = "public/ajax/customers_recent_actions.php";
                    const $timeline = $('#customerTimeline');
                    const $loadMoreWrapper = $("#timelineLoadMoreWrapper");
                    const $loadMoreBtn = $("#loadMoreTimeline");

                    let offset = 0;
                    const limit = 20;
                    let isLoading = false;

                  function renderTimeline(logs) {
                      if (!logs || !logs.length) {
                          return `<div class="text-muted small text-center py-3">No timeline events available.</div>`;
                      }

                      let html = `<div class="timeline">`;
                      let lastDateGroup = '';

                      logs.forEach(item => {
                          const fullDate = item.date.split(",")[0];
                          const timeOnly = item.date.split(",")[1]?.trim() || "";
                          const today = new Date().toDateString();
                          const logDateObj = new Date(item.date);

                          let dateLabel = fullDate;
                          if (logDateObj.toDateString() === today) {
                              dateLabel = "Today";
                          } else {
                              const yesterday = new Date();
                              yesterday.setDate(yesterday.getDate() - 1);
                              if (logDateObj.toDateString() === yesterday.toDateString()) {
                                  dateLabel = "Yesterday";
                              }
                          }

                          if (dateLabel !== lastDateGroup) {
                              html += `<div class="timeline-date-header">${dateLabel}</div>`;
                              lastDateGroup = dateLabel;
                          }

                          let icon = "fa-info-circle";
                          let bulletClass = "timeline-icon-info";
                          const log = item.action.toLowerCase();

                          if (log.includes("payment") || log.includes("paid")) { icon="fa-wallet"; bulletClass="timeline-icon-success"; }
                          else if (log.includes("reminder") || log.includes("expire")) { icon="fa-bell"; bulletClass="timeline-icon-warning"; }
                          else if (log.includes("deleted") || log.includes("rejected")) { icon="fa-times-circle"; bulletClass="timeline-icon-danger"; }
                          else if (log.includes("update")) { icon="fa-edit"; bulletClass="timeline-icon-info"; }

                          const agent = item.by || "System";

                          html += `
                              <div class="timeline-item">
                                  <div class="timeline-bullet ${bulletClass}">
                                      <i class="fa ${icon}"></i>
                                  </div>

                                  <div class="timeline-content">
                                      <div class="timeline-time">
                                          ${fullDate} ${timeOnly}
                                      </div>

                                      <div class="timeline-text">${item.action}</div>
                                      <span class="timeline-agent">${agent}</span>
                                  </div>
                              </div>
                          `;
                      });

                      html += `</div>`;
                      return html;
                  }


                    // ⭐ Load timeline (with load more logic)
                    function loadTimeline(isLoadMore = false) {
                        if (isLoading) return;
                        isLoading = true;

                        if (!isLoadMore) {
                            offset = 0;
                            $timeline.html(`<div class="text-muted small text-center py-3"><i class="fa fa-spinner fa-spin"></i> Loading timeline...</div>`);
                        }

                        $.ajax({
                            url: logURL,
                            type: "POST",
                            dataType: "json",
                            data: {
                                customer_id: customerId,
                                type: "timeline",
                                start: offset,
                                length: limit
                            },
                            success: function(res) {
                                const logs = res.data || [];

                                if (!isLoadMore) $timeline.html("");

                                if (logs.length === 0) {
                                    if (!isLoadMore) {
                                        $timeline.html(`<div class="text-muted small text-center py-3">No timeline found.</div>`);
                                    }
                                    $loadMoreWrapper.addClass("d-none");
                                    isLoading = false;
                                    return;
                                }

                                $timeline.append(renderTimeline(logs));

                                if (logs.length < limit) {
                                    $loadMoreWrapper.addClass("d-none");
                                } else {
                                    $loadMoreWrapper.removeClass("d-none");
                                }

                                offset += logs.length;
                                isLoading = false;
                            },
                            error: function() {
                                $timeline.html(`<div class="text-danger small text-center py-3">Error loading timeline.</div>`);
                                $loadMoreWrapper.addClass("d-none");
                                isLoading = false;
                            }
                        });
                    }

                    // ⭐ Load More button
                    $loadMoreBtn.on("click", function() {
                        loadTimeline(true);
                    });

                    // ⭐ Refresh button
                    $('#refreshTimeline').on('click', function() {
                        loadTimeline(false);
                    });

                    // ⭐ Initial load
                    loadTimeline(false);
                  });
                </script>
                <!-- timeline section end -->


                <!-- rates section start -->
                <style>
                  #rateTrades .trade-btn {
                    border:1px solid #d1d5db;
                    padding:5px 12px;
                    border-radius:20px;
                    font-size:0.8rem;
                    cursor:pointer;
                    background:#fff;
                  }
                  #rateTrades .trade-btn.active {
                    background:#2563eb;
                    color:#fff;
                    border-color:#2563eb;
                  }
                  #rateSites .site-btn {
                    border:1px solid #d1d5db;
                    padding:6px 14px;
                    border-radius:20px;
                    font-size:0.8rem;
                    cursor:pointer;
                    background:#fff;
                  }
                  #rateSites .site-btn.active {
                    background:#0d6efd;
                    color:#fff;
                    border-color:#0d6efd;
                  }
                  #addTradeBtn {
                    border-style: dashed;
                  }
                </style>
                <!-- Rates Section -->
                <div id="rates-section" class="tab-section" style="display:none;">
                  <div class="section-scroll" style="max-height: 70vh;">

                    <div class="card shadow-sm border-0 mb-3">
                      <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">
                          <i class="fa fa-money-bill-wave me-2 text-success"></i> Rates
                        </h5>
                        <button id="editRatesBtn" class="btn btn-outline-primary btn-xs d-none">
                          <i class="fa fa-pen me-1"></i> Modify Rates
                        </button>
                      </div>

                      <div class="card-body">

                        <!-- Site Selection (Button Type) -->
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Site</label>
                          <div id="rateSites" class="d-flex flex-wrap gap-2">
                            <span class="text-muted small">Loading sites...</span>
                          </div>
                        </div>

                        <!-- VIEW MODE -->
                        <div id="ratesView" class="d-none">
                          <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                              <thead class="table-light">
                                <tr>
                                  <th>Trade</th>
                                  <th>Normal Rate</th>
                                  <th>Overtime Eligible</th>
                                  <th>Working Hours</th>
                                  <th>NOT Rate /Hour</th>
                                  <th>HOT Rate /Hour</th>
                                  <th>PHOT Rate /Hour</th>
                                </tr>
                              </thead>
                              <tbody id="ratesViewBody">
                                <tr>
                                  <td colspan="7" class="text-center text-muted small">
                                    No rates available
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>

                        <!-- EDIT MODE -->
                        <div id="ratesEdit" class="d-none">

                          <div class="mb-3">
                            <label class="form-label fw-semibold">Trades</label>
                            <div id="rateTrades" class="d-flex flex-wrap gap-2"></div>
                            <div id="addTradeBox" class="mt-2 d-none">
                              <div class="input-group input-group-sm" style="max-width:300px;">
                                <input type="text" id="newTradeName" class="form-control" placeholder="New trade name">
                                <button class="btn btn-primary" id="saveNewTradeBtn">
                                  Add
                                </button>
                              </div>
                            </div>
                            <button type="button"
                              id="addTradeBtn"
                              class="btn btn-outline-secondary btn-sm mt-2">
                              <i class="fa fa-plus me-1"></i> Add Trade
                            </button>

                          </div>

                          <div class="row g-3 mb-3 align-items-end">
                            <!-- Normal Rate -->
                            <div class="col-md-2">
                              <label class="form-label fw-semibold">Normal Rate</label>

                              <input type="number"
                                     step="0.01"
                                     id="rateHour"
                                     class="form-control form-control-sm mb-1">

                              <!-- Fixed Rate Toggle -->
                              <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_fixed_rate">
                                <label class="form-check-label small fw-semibold ms-1"
                                       for="is_fixed_rate">
                                  Is Fixed Rate/Month
                                </label>
                              </div>
                            </div>

                            <!-- Allow Overtime Toggle -->
                            <div class="col-md-2 d-flex align-items-center">
                              <div class="form-check mt-4">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="allowOvertime"
                                       checked>
                                <label class="form-check-label fw-semibold ms-1"
                                       for="allowOvertime">
                                  Allow OverTime
                                </label>
                              </div>
                            </div>
                            <!-- Default Working Hours -->
                            <div class="col-md-2 overtime-fields">
                              <label class="form-label fw-semibold">Working Hours /Day</label>
                              <input type="number"
                                     step="0.01"
                                     id="default_hours"
                                     class="form-control form-control-sm">
                            </div>
                            <!-- NOT Rate -->
                            <div class="col-md-2 overtime-fields">
                              <label class="form-label fw-semibold">NOT Rate /Hour</label>
                              <input type="number"
                                     step="0.01"
                                     id="rateNOT" placeholder="Normal OT Rate" 
                                     class="form-control form-control-sm">
                            </div>
                            <!-- HOT Rate -->
                            <div class="col-md-2 overtime-fields">
                              <label class="form-label fw-semibold">HOT Rate /Hour</label>
                              <input type="number"
                                     step="0.01"
                                     id="rateHOT" placeholder="Holiday Overtime Rate" 
                                     class="form-control form-control-sm">
                            </div>
                            <!-- PHOT Rate -->
                            <div class="col-md-2 overtime-fields">
                              <label class="form-label fw-semibold">PHOT Rate /Hour</label>
                              <input type="number"
                                     step="0.01"
                                     id="ratePHOT" placeholder="Public Holiday OT Rate" 
                                     class="form-control form-control-sm">
                            </div>
                          </div>

                          <!-- ALLOWANCES -->
                          <div class="border rounded p-2 mb-3 bg-light">

                            <div class="fw-semibold mb-2 small">
                              <i class="fa fa-wallet me-1 text-success"></i>
                              Allowances
                            </div>

                            <div class="row g-2">

                              <div class="col-md-4">
                                <div class="form-check">
                                  <input class="form-check-input" type="checkbox" id="allowFood">
                                  <label class="form-check-label fw-semibold" for="allowFood">
                                    Food
                                  </label>
                                </div>
                              </div>

                              <div class="col-md-4">
                                <div class="form-check">
                                  <input class="form-check-input" type="checkbox" id="allowTravel">
                                  <label class="form-check-label fw-semibold" for="allowTravel">
                                    Travel
                                  </label>
                                </div>
                              </div>

                              <div class="col-md-4">
                                <div class="form-check">
                                  <input class="form-check-input" type="checkbox" id="allowAccommodation">
                                  <label class="form-check-label fw-semibold" for="allowAccommodation">
                                    Accommodation
                                  </label>
                                </div>
                              </div>

                            </div>
                          </div>

                          <div class="d-flex justify-content-end gap-2">
                            <button id="cancelRatesBtn" class="btn btn-light btn-sm">
                              Cancel
                            </button>
                            <button id="saveRatesBtn" class="btn btn-success btn-sm">
                              <i class="fa fa-save me-1"></i> Apply Rates
                            </button>
                          </div>

                        </div>

                      </div>
                    </div>

                  </div>
                </div>
                <script>
                  let siteRates = {};
                  let tradesList = [];
                  let allTrades = [];
                  let selectedSiteId = null;

                  const CUSTOMER_ID = <?= isset($customer_id) ? (int)$customer_id : 0 ?>;

                  function toggleOvertimeFields() {
                    const allowed = $('#allowOvertime').is(':checked');
                    if (allowed) {
                      $('.overtime-fields').removeClass('d-none');
                    } else {
                      $('.overtime-fields').addClass('d-none');
                      $('#rateNOT,#rateHOT,#ratePHOT').val('');
                      $('#default_hours').val(8);
                    }
                  }
                  $('#allowOvertime').on('change', toggleOvertimeFields);

                  /* ===============================
                     ADD TRADE
                  =============================== */
                  $('#addTradeBtn').on('click', function(){
                    $('#addTradeBox').removeClass('d-none');
                    $('#newTradeName').focus();
                  });

                  $('#saveNewTradeBtn').on('click', function(){

                    const name = $('#newTradeName').val().trim();
                    if (!name) {
                      alert('Enter trade name');
                      return;
                    }

                    $.post('public/ajax/customers_rates.php', {
                      action:'add_trade',
                      trade_name: name
                    }, function(res){

                      if (!res.success) {
                        alert(res.error || 'Failed to add trade');
                        return;
                      }

                      tradesList.push(res.trade);

                      // add to edit list immediately
                      $('#rateTrades').append(`
                        <button type="button"
                          class="trade-btn active"
                          data-id="${res.trade.id}">
                          ${res.trade.trade_name}
                        </button>
                      `);

                      siteRates[res.trade.id] = {};

                      $('#newTradeName').val('');
                      $('#addTradeBox').addClass('d-none');

                      $('.trade-btn').off('click').on('click', function(){
                        $(this).toggleClass('active');
                        updateRateInputs();
                      });

                    });
                  });

                  /* ===============================
                     LOAD SITES (BUTTON TYPE)
                  =============================== */
                  function loadRateSites(autoSelect = true) {

                    $.post('public/ajax/customers_sites.php', {
                      action: 'fetch',
                      customer_id: CUSTOMER_ID
                    }, function (res) {

                      const $wrap = $('#rateSites');
                      $wrap.empty();

                      // No sites
                      if (!res || !res.length) {
                        selectedSiteId = null;
                        $('#ratesView').addClass('d-none');
                        $('#ratesEdit').addClass('d-none');
                        $('#editRatesBtn').addClass('d-none');
                        $wrap.html('<span class="text-muted small">No sites added</span>');
                        return;
                      }

                      let firstSiteId = res[0].id;
                      let foundSelected = false;

                      // render buttons
                      res.forEach(s => {

                        if (s.id == selectedSiteId) {
                          foundSelected = true;
                        }

                        $wrap.append(`
                          <button type="button"
                            class="site-btn ${s.id == selectedSiteId ? 'active' : ''}"
                            data-id="${s.id}">
                            ${s.site_name || 'Site ' + s.id}
                          </button>
                        `);
                      });

                      // auto-select first site only if needed
                      if (autoSelect && (!selectedSiteId || !foundSelected)) {
                        selectedSiteId = firstSiteId;
                        $wrap.find(`.site-btn[data-id="${firstSiteId}"]`).addClass('active');
                        loadRatesForSite(firstSiteId);
                      }

                      // click handler
                      $('.site-btn').off('click').on('click', function () {
                        $('.site-btn').removeClass('active');
                        $(this).addClass('active');
                        selectedSiteId = $(this).data('id');
                        loadRatesForSite(selectedSiteId);
                      });

                    });
                  }

                  /* ===============================
                     LOAD RATES + ALL TRADES
                  =============================== */
                  function loadRatesForSite(siteId) {

                    $('#ratesView').addClass('d-none');
                    $('#ratesEdit').addClass('d-none');
                    $('#editRatesBtn').addClass('d-none');

                    siteRates = {};
                    tradesList = [];

                    if (!siteId) return;

                    // 1️⃣ fetch existing rates for site
                    $.post('public/ajax/customers_rates.php', {
                      action:'fetch_rates',
                      customer_id: CUSTOMER_ID,
                      site_id: siteId
                    }, function(rates){

                      siteRates = rates || {};

                      // fetch ALL trades (for EDIT mode)
                      $.post('public/ajax/customers_rates.php', {
                        action:'fetch_all_trades'
                      }, function(trades){
                        allTrades = trades || [];
                      });

                      // fetch trades linked to this site (VIEW MODE)
                      $.post('public/ajax/customers_rates.php', { // fetch_all_trades, fetch_site_trades
                        action:'fetch_trades',
                        site_id: siteId
                      }, function(trades){

                        tradesList = trades || [];
                        renderRatesView();
                        $('#ratesView').removeClass('d-none');
                        $('#editRatesBtn').removeClass('d-none');

                      });


                    });
                  }

                  /* ===============================
                     VIEW MODE
                  =============================== */
                  function renderRatesView() {
                    const $body = $('#ratesViewBody');
                    $body.empty();

                    if (!tradesList.length) {
                      $body.html(`
                        <tr>
                          <td colspan="7" class="text-center text-muted small">
                            No trades linked to this site
                          </td>
                        </tr>
                      `);
                      return;
                    }

                    tradesList.forEach(t => {
                      const r = siteRates[t.id] || {};
                      $body.append(`
                        <tr>
                          <td>
                            <div class="fw-semibold">${t.trade_name}</div>
                            <div class="mt-1 d-flex flex-wrap gap-1 small">
                              ${r.food_allowance == 1
                                ? '<span class="badge bg-success-subtle text-success border">Food</span>'
                                : ''}
                              ${r.travel_allowance == 1
                                ? '<span class="badge bg-primary-subtle text-primary border">Travel</span>'
                                : ''}
                              ${r.accommodation_allowance == 1
                                ? '<span class="badge bg-warning-subtle text-warning border">Accommodation</span>'
                                : ''}
                            </div>
                          </td>
                          <td>${r.rate_per_hour ?? '-'} ${r.is_fixed_rate=='1'?'/Month':'/Hour'}</td>
                          <td>${r.allow_overtime=='1'?'Yes':'No'}</td>
                          <td>${r.default_hours ?? '-'}</td>
                          <td>${r.not_rate ?? '-'}</td>
                          <td>${r.hot_rate ?? '-'}</td>
                          <td>${r.phot_rate ?? '-'}</td>
                        </tr>
                      `);
                    });
                  }

                  /* ===============================
                     EDIT MODE
                  =============================== */
                  $('#editRatesBtn').on('click', function(){

                    $('#ratesEdit').removeClass('d-none');
                    $('#ratesView').addClass('d-none');
                    $('#rateTrades').empty();
                    $('#rateHour,#rateNOT,#rateHOT,#ratePHOT,#default_hours').val('');

                    // default = allowed
                    $('#allowOvertime').prop('checked', true);
                    $('#is_fixed_rate').prop('checked', false);
                    // detect from existing rates (if any trade disallows OT)
                    const overtimeValues = Object.values(siteRates || {})
                      .map(r => r.allow_overtime)
                      .filter(v => v !== undefined);

                    if (overtimeValues.length && overtimeValues.every(v => v == 0)) {
                      $('#allowOvertime').prop('checked', false);
                    }

                    const fixedratevalues = Object.values(siteRates || {})
                      .map(r => r.is_fixed_rate)
                      .filter(v => v !== undefined);

                    if (fixedratevalues.length && fixedratevalues.every(v => v == 0)) {
                      $('#is_fixed_rate').prop('checked', false);
                    }

                    toggleOvertimeFields();

                    // 🔹 SHOW ALL TRADES HERE
                    allTrades.forEach(t => {

                      const hasRate = siteRates[t.id]; // already has rate for this site?
                      const active = hasRate ? 'active' : '';

                      $('#rateTrades').append(`
                        <button type="button"
                          class="trade-btn ${active}"
                          data-id="${t.id}">
                          ${t.trade_name}
                        </button>
                      `);
                    });

                    updateRateInputs();

                    $('.trade-btn').on('click', function(){
                      $(this).toggleClass('active');
                      updateRateInputs();
                    });

                  });


                  /* ===============================
                     UPDATE INPUTS
                  =============================== */
                  function updateRateInputs() {

                    const overtimeAllowed = $('#allowOvertime').is(':checked');

                    const selected = $('#rateTrades .trade-btn.active')
                      .map(function(){ return $(this).data('id'); }).get();

                    if (!selected.length) {
                      $('#rateHour,#rateNOT,#rateHOT,#ratePHOT,#default_hours').val('');
                      return;
                    }

                    $('#allowFood,#allowTravel,#allowAccommodation').prop('checked', false);

                    const values = selected.map(id => siteRates[id]).filter(Boolean);

                    function same(key) {
                      if (!values.length) return '';
                      const v = values[0][key];
                      return values.every(x => x[key] == v) ? v : '';
                    }

                    $('#rateHour').val(same('rate_per_hour'));
                    $('#allowFood').prop('checked', same('food_allowance') == 1);
                    $('#allowTravel').prop('checked', same('travel_allowance') == 1);
                    $('#allowAccommodation').prop('checked', same('accommodation_allowance') == 1);
                    // $('#rateNOT').val(same('not_rate'));
                    // $('#rateHOT').val(same('hot_rate'));
                    if (overtimeAllowed) {
                      $('#rateNOT').val(same('not_rate'));
                      $('#rateHOT').val(same('hot_rate'));
                      $('#ratePHOT').val(same('phot_rate'));
                      $('#default_hours').val(same('default_hours'));
                    } else {
                      $('#rateNOT,#rateHOT,#ratePHOT,#default_hours').val('');
                    }
                  }

                  /* ===============================
                     SAVE
                  =============================== */
                  $('#saveRatesBtn').on('click', function(){

                    const trades = $('#rateTrades .trade-btn.active')
                      .map(function(){ return $(this).data('id'); }).get();

                    if (!selectedSiteId || !trades.length) {
                      alert('Select site and trade(s)');
                      return;
                    }

                    $.post('public/ajax/customers_rates.php', {
                      action:'save',
                      customer_id: CUSTOMER_ID,
                      site_id: selectedSiteId,
                      trades: trades,
                      rate_hour: $('#rateHour').val() || null,
                      not_rate: $('#allowOvertime').is(':checked') ? $('#rateNOT').val() : null,
                      hot_rate: $('#allowOvertime').is(':checked') ? $('#rateHOT').val() : null,
                      phot_rate: $('#allowOvertime').is(':checked') ? $('#ratePHOT').val() : null,
                      default_hours: $('#allowOvertime').is(':checked') ? $('#default_hours').val() : 8,
                      allow_overtime: $('#allowOvertime').is(':checked') ? 1 : 0,
                      is_fixed_rate: $('#is_fixed_rate').is(':checked') ? 1 : 0,
                      food_allowance: $('#allowFood').is(':checked') ? 1 : 0,
                      travel_allowance: $('#allowTravel').is(':checked') ? 1 : 0,
                      accommodation_allowance: $('#allowAccommodation').is(':checked') ? 1 : 0
                    }, function(){
                      alert('Rates updated');
                      $('#ratesEdit').addClass('d-none');
                      $('#ratesView').removeClass('d-none');
                      loadRatesForSite(selectedSiteId);
                    });

                  });

                  /* ===============================
                     CANCEL
                  =============================== */
                  $('#cancelRatesBtn').on('click', function(){
                    $('#ratesEdit').addClass('d-none');
                    $('#ratesView').removeClass('d-none');
                  });

                  /* ===============================
                     INIT
                  =============================== */
                  loadRateSites(true);
                  $(document).on('site:updated', function(){
                    loadRateSites(true);
                  });
                </script>
                <!-- rates section ends -->

        </div>

        <!-- Right Column -->
        <div class="col-md-4 col-section jright">

            <div class="section-scroll">

            <!-- COMPACT ACTION BUTTON PANEL -->
            <style>
              .quick-actions .action-btn {
                transition: all 0.15s ease-in-out;
                min-height: 50px;
                padding: 6px 0;
                font-size: 0.8rem;
              }
              .quick-actions .action-btn:hover {
                background-color: #f8faff;
                border-color: #cdd6ff;
                transform: translateY(-2px);
                box-shadow: 0 3px 6px rgba(0, 0, 0, 0.05);
              }
              .quick-actions .action-btn i {
                font-size: 0.9rem;
                margin-bottom: 2px;
                transition: transform 0.15s ease;
              }
              .quick-actions .action-btn:hover i {
                transform: scale(1.15);
              }
              .quick-actions .card-body {
                padding: 0.8rem 0.8rem 1rem;
              }
              .quick-actions h6 {
                font-size: 0.9rem;
              }
            </style>
            <div class="card border-0 shadow-sm rounded-4 mb-3 quick-actions">
              <div class="card-body">
                <h6 class="fw-semibold text-primary mb-2 d-flex align-items-center gap-2">
                  <i class="fa fa-bolt"></i> Quick Add
                </h6>

                <div class="row g-1">
                  <div class="col-3">
                    <button id="quickCreateReminderBtn" class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn" onclick="document.getElementById('remindersBtn').click();">
                      <i class="fa fa-bell text-danger"></i>
                      <span class="fw-semibold text-nowrap">Reminder</span>
                    </button>
                  </div>

                  <div class="col-3">
                    <button id="quickCreateNoteBtn" class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn" onclick="document.getElementById('notesBtn').click();">
                      <i class="fa fa-sticky-note text-warning"></i>
                      <span class="fw-semibold text-nowrap">Note</span>
                    </button>
                  </div>

                  <!-- <div class="col-3">
                    <button id="quickCreateQuotationBtn" class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn">
                      <i class="fa fa-file-text text-primary"></i>
                      <span class="fw-semibold text-nowrap">Quote</span>
                    </button>
                  </div> -->

                  <!-- <div class="col-3">
                    <button data-bs-toggle="modal" data-bs-target="#addRequirementModal" class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn">
                      <i class="fa fa-briefcase text-success"></i>
                      <span class="fw-semibold text-nowrap">Job</span>
                    </button>
                  </div> -->

                  <div class="col-3">
                    <button data-bs-toggle="modal" data-bs-target="#invoiceModal" class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn">
                      <i class="fa fa-file-invoice text-primary"></i>
                      <span class="fw-semibold text-nowrap">Invoice</span>
                    </button>
                  </div>

                  <div class="col-3">
                    <button data-bs-toggle="modal" data-bs-target="#paymentModal" class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn">
                      <i class="fa fa-wallet text-primary"></i>
                      <span class="fw-semibold text-nowrap">Payment</span>
                    </button>
                  </div>

                  <div class="col-3">
                    <button data-bs-toggle="modal" data-bs-target="#addContactModal" class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn">
                      <i class="fa fa-users text-secondary"></i>
                      <span class="fw-semibold text-nowrap">Contact</span>
                    </button>
                  </div>

                  <div class="col-3">
                    <button data-bs-toggle="modal" data-bs-target="#addDocumentModal" class="btn btn-light border w-100 rounded-3 d-flex flex-column align-items-center justify-content-center action-btn">
                      <i class="fa fa-upload text-info"></i>
                      <span class="fw-semibold text-nowrap">Doc</span>
                    </button>
                  </div>

                </div>
              </div>
            </div>

            <!-- Previous Reminders -->
            <div class="card shadow-sm mb-2">
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

            <style>
                #recentActionsSection .small{
                    font-size: 0.95em !important;
                }
            </style>
            <div id="recentActionsSection" class="card border-0 shadow-sm rounded-3 mt-2">
              <div class="card-header bg-white border-bottom-1 d-flex align-items-center justify-content-between">
                <h5 class="fw-semibold mb-0 d-flex align-items-center small gap-2 text-primary">
                  <i class="fa fa-clock"></i> Recent Actions
                </h5>
                <button class="btn btn-xs btn-outline-secondary" id="refreshLogs">
                  <i class="fa fa-sync"></i> Refresh
                </button>
              </div>
              <div class="card-body py-2" id="customerLogs" style="max-height: 300px; scrollbar-width: thin; overflow-y:auto;">
                <div class="text-center text-muted small py-3">Loading...</div>
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
        <div class="modal-body text-dark" id="info">
          <form id="info-wrapper" class="row g-3 text-black">

            <!-- Company -->
            <!-- <div class="col-md-4 settinginp">
              <label for="company" class="form-label">Company</label>
              <input type="text" class="form-control form-control-sm rounded-pill"
                     name="company" id="company" placeholder="ABC Company"
                     value="<?= htmlspecialchars($company ?? '') ?>">
            </div> -->

            <!-- Name -->
            <div class="col-md-4 settinginp">
              <label for="name" class="form-label">Contact Name</label>
              <input type="text" class="form-control form-control-sm rounded-pill"
                     name="name" id="name" placeholder="Rahul Dev"
                     value="<?= htmlspecialchars($name ?? '') ?>">
            </div>

            <!-- Industry -->
            <!-- <div class="col-md-4 settinginp">
              <label for="industry" class="form-label">Industry</label>
              <input type="text" class="form-control form-control-sm rounded-pill"
                     name="industry" id="industry" placeholder="Piped and Fitting"
                     value="<?= htmlspecialchars($industry ?? '') ?>">
            </div> -->

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

            <!-- Dob -->
            <div class="col-md-4 settinginp">
              <label for="industry" class="form-label">DOB</label>
              <input type="date" class="form-control form-control-sm rounded-pill"
                     name="dob" id="dob" placeholder="Date of Birth"
                     value="<?= htmlspecialchars($dob ?? '') ?>">
            </div>

            <!-- Enable Email -->
            <div class="col-md-2 d-flex1 align-items-center settinginp">
              <div class="form-check form-switch settinginp">
                <input class="form-check-input" type="checkbox" role="switch"
                       id="enable_email" name="enable_email"
                       <?= !empty($enable_email) ? 'checked' : '' ?>>
                <label class="form-check-label small ms-1" for="enable_email">
                  Enable Email
                </label>
              </div>
            </div>

            <!-- Enable WhatsApp -->
            <div class="col-md-2 d-flex1 align-items-center settinginp">
              <div class="form-check form-switch settinginp">
                <input class="form-check-input" type="checkbox" role="switch"
                       id="enable_whatsapp" name="enable_whatsapp"
                       <?= !empty($enable_whatsapp) ? 'checked' : '' ?>>
                <label class="form-check-label small ms-1" for="enable_whatsapp">
                  Enable WhatsApp
                </label>
              </div>
            </div>
            
            <div class="col-md-4 settinginp settinginpImg">
            </div>
            <!-- Photos -->
            <div class="col-md-4 settinginp settinginpImg">
              <label class="form-label">Passport Side 1</label>
              <input type="file" class="form-control form-control-sm rounded-pill"
                     name="photo" id="photo" accept="image/*">
              <div id="photo1Preview" class="mt-2">
                <?php if (!empty($photo)): ?>
                  <img src="uploads/customers/<?= htmlspecialchars($photo) ?>" alt="Photo 1"
                       class="img-thumbnail" style="max-width: 100px;">
                <?php endif; ?>
              </div>
            </div>


            <div class="col-md-4 settinginp settinginpImg">
              <label class="form-label">Passport Side 2</label>
              <input type="file" class="form-control form-control-sm rounded-pill"
                     name="photo1" id="photo1" accept="image/*">
              <div id="photo2Preview" class="mt-2">
                <?php if (!empty($photo1)): ?>
                  <img src="uploads/customers/<?= htmlspecialchars($photo1) ?>" alt="Photo 2"
                       class="img-thumbnail" style="max-width: 100px;">
                <?php endif; ?>
              </div>
            </div>

          </form>
        </div>
        <style>
          .settinginp .form-check-input {
            width: 2.5em;
            height: 1.2em;
            cursor: pointer;
          }

          .settinginp .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
          }

          .settinginp .form-check-label {
            font-size: 0.9rem;
            color: #333;
          }
        </style>



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

<!-- View ID / Passport Modal -->
<div class="modal fade" id="viewIdModal" tabindex="-1" aria-labelledby="viewIdModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">

      <!-- Header -->
      <div class="modal-header py-2">
        <h5 class="modal-title fw-semibold d-flex align-items-center" id="viewIdModalLabel">
          <i class="fa fa-id-card me-2"></i> Passport Preview
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Body -->
      <div class="modal-body bg-light text-center p-4" style="min-height: 75vh;">

        <!-- Tabs -->
        <ul class="nav nav-tabs justify-content-center mb-3 border-0" id="cardTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 py-1 fw-semibold" 
                    id="side1-tab" data-bs-toggle="tab" data-bs-target="#side1" 
                    type="button" role="tab" aria-controls="side1" aria-selected="true">
              Side 1
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-1 fw-semibold" 
                    id="side2-tab" data-bs-toggle="tab" data-bs-target="#side2" 
                    type="button" role="tab" aria-controls="side2" aria-selected="false">
              Side 2
            </button>
          </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-3" id="cardTabContent">

          <!-- Side 1 -->
          <div class="tab-pane fade show active" id="side1" role="tabpanel" aria-labelledby="side1-tab">
            <?php if (!empty($photo)): ?>
              <img src="uploads/customers/<?= htmlspecialchars($photo) ?>" 
                   alt="Card Side 1" class="img-fluid rounded-3 shadow" 
                   style="max-height: 70vh; object-fit: contain;" id="photoSide1">
            <?php else: ?>
              <div class="text-muted mt-4">
                <i class="fa fa-image fa-2x mb-2 d-block"></i>
                No Side 1 uploaded
              </div>
              <button class="btn btn-sm btn-primary mt-2" onclick="setPhotoId(1)">
                <i class="fa fa-upload me-1"></i> Passport Side 1
              </button>
            <?php endif; ?>
          </div>

          <!-- Side 2 -->
          <div class="tab-pane fade" id="side2" role="tabpanel" aria-labelledby="side2-tab">
            <?php if (!empty($photo1)): ?>
              <img src="uploads/customers/<?= htmlspecialchars($photo1) ?>" 
                   alt="Card Side 2" class="img-fluid rounded-3 shadow" 
                   style="max-height: 70vh; object-fit: contain;" id="photoSide2">
            <?php else: ?>
              <div class="text-muted mt-4">
                <i class="fa fa-image fa-2x mb-2 d-block"></i>
                No Side 2 uploaded
              </div>
              <button class="btn btn-sm btn-primary mt-2" onclick="setPhotoId(2)">
                <i class="fa fa-upload me-1"></i> Passport Side 2
              </button>
            <?php endif; ?>
          </div>

        </div>

      </div>
    </div>
  </div>
</div>

<!-- View Sites Modal (improved) -->
<div class="modal fade" id="viewSitesModal" tabindex="-1" aria-labelledby="viewSitesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content rounded-4 shadow-sm">

      <!-- Header -->
      <div class="modal-header bg-white border-bottom">
        <h5 class="modal-title fw-semibold text-primary d-flex align-items-center gap-2" id="viewSitesModalLabel">
          <i class="fa fa-map-marker-alt"></i> Customer Sites
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <!-- Body -->
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <small class="text-muted">Manage customer sites — add multiple sites, update details or remove them.</small>
          </div>
          <div>
            <button id="addSiteBtn" class="btn btn-outline-primary btn-sm">
              <i class="fa fa-plus me-1"></i> Add Site
            </button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <!-- vertical tab list -->
            <div class="list-group" id="sitesTabList" role="tablist" style="max-height:70vh; overflow:auto;">
              <!-- tabs added dynamically -->
            </div>
          </div>
          <div class="col-md-8">
            <div id="sitesContent" class="tab-content" style="max-height:70vh; overflow:auto;">
              <!-- content added dynamically -->
            </div>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <div class="modal-footer bg-white border-top d-none">
        <button type="button" class="btn btn-light btn-sm border" data-bs-dismiss="modal">
          <i class="fa fa-times me-1"></i> Close
        </button>
        <!-- <button type="button" id="saveAllSitesBtn" class="btn btn-primary btn-sm">
          <i class="fa fa-save me-1"></i> Save All
        </button> -->
      </div>

    </div>
  </div>
</div>
<!-- small extra styling -->
<style>
  #viewSitesModal .site-card { background:#fff; border:1px solid #eef2ff; border-radius:10px; padding:1rem; }
  #viewSitesModal .list-group-item { cursor:pointer; }
  #viewSitesModal .list-group-item.active { background: #eef3ff; border-color: #d6e2ff; color:#0b3a9b; }
  #viewSitesModal .form-label { font-weight:600; font-size:0.85rem; }
  #viewSitesModal .muted-small { font-size:0.85rem; color:#6b7280; }
</style>
<style>
  #viewSitesModal .trade-btn {
    border:1px solid #c7d2fe;
    background:#fff;
    color:#1e3a8a;
    padding:4px 10px;
    font-size:0.8rem;
    border-radius:20px;
    cursor:pointer;
  }
  #viewSitesModal .trade-btn.active {
    background:#1e3a8a;
    color:#fff;
  }
</style>
<script>
$(function() {
  const modalSelector = '#viewSitesModal';
  const customerIdGlobal = <?= isset($customer_id) ? (int)$customer_id : 0 ?>;
  let sites = []; // local cache of site objects
  let nextTempId = -1; // for new client-only rows
  const $tabList = $('#sitesTabList');
  const $content = $('#sitesContent');
  const sitesModal = new bootstrap.Modal(document.querySelector(modalSelector));

  let tradesMaster = [];
  function fetchTrades() {
    return $.post('public/ajax/customers_sites.php', { action:'fetch_trades' })
      .then(resp => {
        const j = (typeof resp === 'string') ? JSON.parse(resp) : resp;
        tradesMaster = Array.isArray(j) ? j : [];
      });
  }

  function renderTrades($wrap, selected = []) {
    $wrap.empty();
    tradesMaster.forEach(t => {
      const active = selected.includes(String(t.id)) ? 'active' : '';
      $wrap.append(
        `<button type="button"
          class="trade-btn ${active}"
          data-id="${t.id}">${escapeHtml(t.trade_name)}</button>`
      );
    });

    // toggle logic
    $wrap.find('.trade-btn').off('click').on('click', function(){
      $(this).toggleClass('active');
    });
  }


  /* ------- TEMPLATES ------- */
  function tabListItemTemplate(idx, site) {
    const name = site.site_name ? escapeHtml(site.site_name) : `Site ${idx}`;
    const active = idx === 1 ? 'active' : '';
    return `<a class="list-group-item list-group-item-action ${active}" data-index="${idx}" id="site-tab-${idx}">
              <div class="d-flex justify-content-between align-items-start">
                <div><strong class="me-1">${name}</strong><div class="muted-small">${site.site_contact || ''}</div></div>
                <small class="text-muted">${site.id ? 'ID:'+site.id : 'New'}</small>
              </div>
            </a>`;
  }
  function siteContentTemplate(idx, site) {
    // site.id may be null for new
    const id = site.id ? site.id : '';
    return `
    <div class="tab-pane ${idx === 1 ? 'active' : ''}" id="site-pane-${idx}" data-index="${idx}">
      <div class="site-card">
        <input type="hidden" class="site-field site-id" value="${id}">
        <div class="row g-3">
          <div class="col-md-12">
            <label class="form-label">Site Name</label>
            <input class="form-control form-control-sm site-field site-name" value="${escapeAttr(site.site_name||'')}" placeholder="Name or short label">
          </div>
          <div class="col-md-6">
            <label class="form-label">Site Contact</label>
            <input class="form-control form-control-sm site-field site-contact" value="${escapeAttr(site.site_contact||'')}" placeholder="Contact person & phone">
          </div>
          <div class="col-md-6">
            <label class="form-label">Location</label>
            <input class="form-control form-control-sm site-field site-location" value="${escapeAttr(site.site_location||'')}" placeholder="Google Maps link / Landmark">
          </div>
          <div class="col-12">
            <label class="form-label">Address</label>
            <textarea class="form-control form-control-sm site-field site-address" rows="3" placeholder="Full address...">${escapeAttr(site.site_address||'')}</textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Trades</label>
            <div class="d-flex flex-wrap gap-2 site-trades" data-selected="">
              <!-- trade buttons injected dynamically -->
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
          <button class="btn btn-sm btn-danger delete-site-btn" data-index="${idx}">
            <i class="fa fa-trash me-1"></i> Delete
          </button>
          <button class="btn btn-sm btn-outline-primary save-site-btn" data-index="${idx}">
            <i class="fa fa-save me-1"></i> Save
          </button>
        </div>
      </div>
    </div>`;
  }
  /* ------- helpers ------- */
  function escapeHtml(s){ return (s+'').replace(/[&<>"']/g, function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]; }); }
  function escapeAttr(s){ return s==null ? '' : escapeHtml(s); }
  /* ------- render UI ------- */
  function renderAll() {
    $tabList.empty();
    $content.empty();

    if (!sites.length) {
      // one blank site
      sites = [ { id:null, site_name:'', site_contact:'', site_address:'', site_location:'' } ];
    }

    sites.forEach((s, i) => {
      const idx = i+1;
      $tabList.append(tabListItemTemplate(idx, s));
      $content.append(siteContentTemplate(idx, s));

      const selectedTrades = (s.trades || []).map(String);
      renderTrades(
        $content.find(`#site-pane-${idx} .site-trades`),
        selectedTrades
      );

    });

    // attach click handlers for the tab list
    $tabList.find('.list-group-item').off('click').on('click', function() {
      const idx = $(this).data('index');
      $tabList.find('.list-group-item').removeClass('active');
      $(this).addClass('active');
      $content.find('.tab-pane').removeClass('active');
      $content.find(`#site-pane-${idx}`).addClass('active');
    });
    // attach save/delete handlers
    attachSiteButtons();

  }
  function attachSiteButtons() {
    // Save single site
    $('.save-site-btn').off('click').on('click', function(){
      const idx = $(this).data('index');
      saveSingleSite(idx);
    });
    // Delete single site (client-side / or server-side if persisted)
    $('.delete-site-btn').off('click').on('click', function(){
      const idx = $(this).data('index');
      const site = sites[idx-1];
      if (site && site.id) {
        if (!confirm('Delete this site permanently?')) return;
        $.post('public/ajax/customers_sites.php', { action:'delete', id: site.id, customer_id: customerIdGlobal }, function(resp){
          try { 
            // const j = JSON.parse(resp);
            const j = (typeof resp === 'string') ? JSON.parse(resp) : resp;
            if (j.success) { sites.splice(idx-1,1); renderAll(); $(document).trigger('site:updated'); } else alert(j.error||'Delete failed');
          } catch(e){ alert('Invalid response'); }
        });
      } else {
        // just remove unsaved
        sites.splice(idx-1,1);
        renderAll();
      }
    });
  }
  /* ------- Fetch from server ------- */
  function fetchSites(customerId) {
    $tabList.html(`<div class="text-center py-3"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</div>`);
    $content.html('');
    $.post('public/ajax/customers_sites.php', { action:'fetch', customer_id: customerId }, function(resp) {
      try {
        // const j = JSON.parse(resp);
        const j = (typeof resp === 'string') ? JSON.parse(resp) : resp;
        if (Array.isArray(j)) {
          sites = j.map(s => ({
            id: s.id,
            site_name: s.site_name || '',
            site_contact: s.site_contact || '',
            site_address: s.site_address || '',
            site_location: s.site_location || '',
            trades: s.trades || []   // ⭐ IMPORTANT
          }));
          renderAll();
        } else {
          throw 'not-array';
        }
      } catch (e) {
        // fallback: single empty
        sites = [ { id:null, site_name:'', site_contact:'', site_address:'', site_location:'' } ];
        renderAll();
      }
    }).fail(function(){ sites = [ { id:null, site_name:'', site_contact:'', site_address:'', site_location:'' } ]; renderAll(); });
  }
  /* ------- Save single site (create or update) ------- */
  function saveSingleSite(idx) {
    const $pane = $content.find(`#site-pane-${idx}`);
    const id = $pane.find('.site-id').val() || '';
    const payload = {
      action: id ? 'update' : 'create',
      customer_id: customerIdGlobal,
      id: id,
      site_name: $pane.find('.site-name').val().trim(),
      site_contact: $pane.find('.site-contact').val().trim(),
      site_address: $pane.find('.site-address').val().trim(),
      site_location: $pane.find('.site-location').val().trim(),
      site_trades: $pane.find('.trade-btn.active').map(function(){ return $(this).data('id'); }).get()
    };

    const $btn = $pane.find('.save-site-btn');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving');

    $.post('public/ajax/customers_sites.php', payload, function(resp) {
      $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
      try {
        // const j = JSON.parse(resp);
        const j = (typeof resp === 'string') ? JSON.parse(resp) : resp;
        if (j.success) {
          // replace local object with returned object
          sites[idx-1] = {
            id: j.site.id,
            site_name: j.site.site_name,
            site_contact: j.site.site_contact,
            site_address: j.site.site_address,
            site_location: j.site.site_location,
            trades: payload.site_trades || []   // ⭐ preserve trades
          };
          renderAll();

          $(document).trigger('site:updated');
        } else {
          alert(j.error || 'Save failed');
        }
      } catch (e) { alert('Invalid response from server'); }
    }).fail(function(){ $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save'); alert('Network error'); });
  }
  /* ------- Save all (bulk) ------- */
  $('#saveAllSitesBtn').on('click', function(){
    // gather arrays
    const payload = { action: 'save_bulk', customer_id: customerIdGlobal, sites: [] };
    $content.find('.tab-pane').each(function(i){
      const $p = $(this);
      const item = {
        id: $p.find('.site-id').val() || '',
        site_name: $p.find('.site-name').val().trim(),
        site_contact: $p.find('.site-contact').val().trim(),
        site_address: $p.find('.site-address').val().trim(),
        site_location: $p.find('.site-location').val().trim()
      };
      payload.sites.push(item);
    });

    const $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving All');
    $.ajax({
      url: 'public/ajax/customers_sites.php',
      method: 'POST',
      data: { action: payload.action, customer_id: payload.customer_id, sites: JSON.stringify(payload.sites) },
    }).done(function(resp){
      $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save All');
      try {
        // const j = JSON.parse(resp);
        const j = (typeof resp === 'string') ? JSON.parse(resp) : resp;
        if (j.success) {
          // refresh from server
          fetchSites(customerIdGlobal);

          $(document).trigger('site:updated');
        } else {
          alert(j.error || 'Save all failed');
        }
      } catch(e){ alert('Invalid response'); }
    }).fail(function(){ $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save All'); alert('Network error'); });
  });
  /* ------- Add new site (client-side) ------- */
  $('#addSiteBtn').on('click', function(){
    sites.push({ id:null, site_name:'', site_contact:'', site_address:'', site_location:'' });
    renderAll();

    // scroll to last tab and focus name
    setTimeout(()=> {
      const lastIdx = sites.length;
      $tabList.find(`[data-index='${lastIdx}']`).trigger('click');
      $content.find(`#site-pane-${lastIdx} .site-name`).focus();
    }, 60);
  });
  /* ------- public opener ------- */
  window.openSitesModal = function(customerId) {
    const cid = customerId || customerIdGlobal;

    fetchTrades().then(() => {
      fetchSites(cid);
      sitesModal.show();
    });
  };

  // auto open if desired:
  // openSitesModal(customerIdGlobal);
});
</script>

<!-- Edit Reminder Modal -->
<div class="modal fade" id="editReminderModal" tabindex="-1" aria-labelledby="editReminderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow-lg">

      <!-- Header -->
      <div class="modal-header rounded-top-4 py-2">
        <h6 class="modal-title fw-semibold" id="editReminderModalLabel">
          <i class="fa fa-edit me-2"></i> Edit Reminder
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Body -->
      <div class="modal-body p-3 text-dark">
        <form id="edit-reminder-form">

          <input type="hidden" name="reminder_id" id="edit_reminder_id" value="">

          <!-- Reminder Type -->
          <div class="mb-3">
            <label class="form-label mb-1 small fw-bold">Reminder Type</label>
            <div class="d-flex flex-wrap gap-1" id="editReminderTypeGroup">
              <button type="button" class="btn btn-outline-primary btn-xs rounded-pill edit-type" data-type="Call">
                <i class="fa fa-phone me-1"></i> Call
              </button>
              <button type="button" class="btn btn-outline-primary btn-xs rounded-pill edit-type" data-type="Email">
                <i class="fa fa-envelope me-1"></i> Email
              </button>
              <button type="button" class="btn btn-outline-primary btn-xs rounded-pill edit-type" data-type="Meeting">
                <i class="fa fa-handshake me-1"></i> Meeting
              </button>
              <button type="button" class="btn btn-outline-primary btn-xs rounded-pill edit-type" data-type="General">
                <i class="fa fa-sticky-note me-1"></i> General
              </button>
            </div>
            <input type="hidden" name="type" id="edit_reminder_type" value="Call">
          </div>

            <!-- Contact Selection -->
            <div class="mb-3" id="editContactSection">
              <label class="form-label mb-1 small fw-bold">Select Contact</label>
              <div class="d-flex flex-wrap gap-1" id="editContactButtons">
                <span class="text-muted small">Loading contacts...</span>
              </div>
              <input type="hidden" name="contact_name" id="edit_contact_name" value="">
              <input type="hidden" name="edit_contact_id" id="edit_contact_id" value="">
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
            <textarea class="form-control form-control-xs rounded-3" name="notes" id="edit-reminder-notes" rows="2" placeholder="Write notes..." required></textarea>
          </div>

          <!-- Submit Button -->
          <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-success btn-xs rounded-pill">
              <i class="fa fa-save me-1"></i> Update Reminder
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>


<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="statusModalLabel"><i class="fa fa-user-edit me-2"></i>Change Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-2">
          <div class="d-flex flex-wrap gap-2" id="statusButtonsContainer">
            <?php
              foreach($customers_statuses as $val => $label): ?>
                <button type="button" class="btn btn-outline-secondary btn-sm status-btn" data-value="<?=htmlspecialchars($val)?>">
                  <?=htmlspecialchars($label)?>
                </button>
              <?php endforeach; ?>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold">Note <span class="text-danger">*</span></label>
          <textarea id="status-note" class="form-control form-control-sm" rows="3" placeholder="Explain why you changed the status..." required></textarea>
          <div class="text-danger small mt-1 d-none" id="status-note-error">A note is required to change status.</div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="saveStatusBtn" class="btn btn-primary btn-sm">
          <i class="fa fa-save me-1"></i> Save Status
        </button>
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
</script>
<script>
$(function() {
  // Reminder Type Selection
  $(document).on("click", ".reminder-type", function () {
    const $btn = $(this);
    const type = $btn.data("type");

    // update hidden field
    $("#reminder_type").val(type);

    // toggle style
    $(".reminder-type").removeClass("btn-primary text-white").addClass("btn-outline-secondary");
    $btn.removeClass("btn-outline-secondary").addClass("btn-primary text-white");
  });

  const customerId = <?= $customer_id ?? 0 ?>;
  const $contactSection = $("#reminderContacts");
  const $contactButtons = $("#contactSelectButtons");
  const $contactSelection = $("#contact_selection");

  function loadContactButtons() {
    $.ajax({
      url: "public/ajax/customers_contacts.php",
      type: "POST",
      dataType: "json",
      data: { action: "list", customer_id: customerId },
      beforeSend: function() {
        $contactButtons.html('<span class="text-muted small">Loading contacts...</span>');
        $contactSection.show();
      },
      success: function(data) {
        // 🧠 Hide entire section if no contacts
        if (!data || data.length === 0) {
          $contactSection.hide();
          return;
        }

        let html = "";
        data.forEach(contact => {
          html += `
            <button type="button"
              class="btn btn-outline-success btn-xs rounded-pill contact-select"
              data-id="${contact.id}"
              data-name="${contact.name}">
              <i class="fa fa-user me-1"></i> ${contact.name}
            </button>`;
        });

        // Optional "None" button
        html += `
          <button type="button"
            class="btn btn-outline-secondary btn-xs rounded-pill contact-select"
            data-id=""
            data-name="">
            <i class="fa fa-ban me-1"></i> None
          </button>`;

        $contactButtons.html(html);
        $contactSection.show(); // show section if contacts exist
      },
      error: function() {
        $contactButtons.html('<span class="text-danger small">Error loading contacts.</span>');
      }
    });
  }

  // Handle selection
  $(document).on("click", ".contact-select", function () {
    const $btn = $(this);
    const id = $btn.data("id");
    const name = $btn.data("name");

    $contactSelection.val(id); // or .val(name) if you prefer

    $(".contact-select").removeClass("btn-success text-white").addClass("btn-outline-success");
    $btn.removeClass("btn-outline-success").addClass("btn-success text-white");
  });

  // Initial load
  loadContactButtons();

});
</script>
<script>
$(function() {
  const editModal = new bootstrap.Modal('#editReminderModal');
  const customerId = <?= $customer_id ?? 0 ?>;

  // 🟢 Open Edit Modal
  window.openEditReminderModal = function(reminder) {
    $("#edit_reminder_id").val(reminder.id);
    $("#edit-reminder-notes").val(reminder.note || '');
    $("#edit_reminder_type").val(reminder.type || 'Call');
    highlightTypeButton(reminder.type || 'Call');

    // Reset quick reminders & custom date/time visibility
    $(".edit-quick-reminder").removeClass("btn-primary text-white").addClass("btn-outline-secondary");
    $("#editCustomDateTime").hide();

    // Set reminder date/time and highlight quick button
    if (reminder.reminder_at) {
      const reminderDate = new Date(reminder.reminder_at.replace(/-/g, '/'));
      const today = new Date();
      const diffDays = Math.round((reminderDate - today) / (1000 * 60 * 60 * 24));

      // set date/time
      $("#edit-reminder-date").val(reminderDate.toISOString().split("T")[0]);
      $("#edit-reminder-time").val(reminderDate.toTimeString().slice(0, 5));

      // highlight quick reminder button
      highlightQuickReminder(diffDays);
    }

    // Load contacts dynamically (button style)
    loadContactButtons(reminder.contact_id || '');

    editModal.show();
  };

  // 🧩 Load contacts as button group
  function loadContactButtons(selectedId = '') {
    $.ajax({
      url: "public/ajax/customers_contacts.php",
      type: "POST",
      dataType: "json",
      data: { action: "list", customer_id: customerId },
      beforeSend: function() {
        $("#editContactButtons").html('<span class="text-muted small">Loading contacts...</span>');
      },
      success: function(data) {
        const $container = $("#editContactButtons");
        $container.empty();

        if (!data || !data.length) {
          $("#editContactSection").hide();
          return;
        }

        $("#editContactSection").show();
        let html = "";
        data.forEach(c => {
          const isActive = c.id == selectedId;
          html += `
            <button type="button" 
              class="btn ${isActive ? 'btn-success text-white' : 'btn-outline-success'} 
              btn-xs rounded-pill contact-select-btn" 
              data-id="${c.id}" 
              data-name="${c.name}">
              <i class="fa fa-user me-1"></i> ${c.name}
            </button>`;
        });

        html += `
          <button type="button" 
            class="btn btn-outline-secondary btn-xs rounded-pill contact-select-btn" 
            data-id="" data-name="">
            <i class="fa fa-ban me-1"></i> None
          </button>`;

        $container.html(html);

        if (selectedId) {
          const selected = data.find(c => c.id == selectedId);
          if (selected) {
            $("#edit_contact_id").val(selected.id);
            $("#edit_contact_name").val(selected.name);
          }
        } else {
          $("#edit_contact_id").val('');
          $("#edit_contact_name").val('');
        }
      },
      error: function() {
        $("#editContactButtons").html('<span class="text-danger small">Error loading contacts.</span>');
      }
    });
  }

  // 🟩 Contact button click
  $(document).on("click", ".contact-select-btn", function() {
    const $btn = $(this);
    const id = $btn.data("id");
    const name = $btn.data("name");

    $("#edit_contact_id").val(id);
    $("#edit_contact_name").val(name);

    $(".contact-select-btn").removeClass("btn-success text-white").addClass("btn-outline-success");
    $btn.removeClass("btn-outline-success").addClass("btn-success text-white");
  });

  // 🔹 Highlight selected type
  function highlightTypeButton(type) {
    $(".edit-type").removeClass("btn-primary text-white").addClass("btn-outline-primary");
    $(`.edit-type[data-type="${type}"]`).removeClass("btn-outline-primary").addClass("btn-primary text-white");
  }

  // 🟦 Type selection click
  $(document).on("click", ".edit-type", function() {
    const type = $(this).data("type");
    $("#edit_reminder_type").val(type);
    highlightTypeButton(type);
  });

  // ⏰ Quick Reminder Buttons
  $(document).on("click", ".edit-quick-reminder", function() {
    const days = $(this).data("days");
    const now = new Date();

    $(".edit-quick-reminder").removeClass("btn-primary text-white").addClass("btn-outline-secondary");

    if (days === "custom") {
      $("#editCustomDateTime").slideDown(200);
      $(this).removeClass("btn-outline-secondary").addClass("btn-primary text-white");
      return;
    }

    now.setDate(now.getDate() + parseInt(days));
    $("#edit-reminder-date").val(now.toISOString().split("T")[0]);
    $("#edit-reminder-time").val(now.toTimeString().slice(0, 5));

    $("#editCustomDateTime").slideUp(200);
    $(this).removeClass("btn-outline-secondary").addClass("btn-primary text-white");
  });

  // 🎯 Highlight correct quick button based on date difference
  function highlightQuickReminder(diffDays) {
    $(".edit-quick-reminder").removeClass("btn-primary text-white").addClass("btn-outline-secondary");
    $("#editCustomDateTime").hide();

    if (Math.abs(diffDays - 1) <= 1) {
      $(`.edit-quick-reminder[data-days="1"]`).removeClass("btn-outline-secondary").addClass("btn-primary text-white");
    } else if (Math.abs(diffDays - 7) <= 1) {
      $(`.edit-quick-reminder[data-days="7"]`).removeClass("btn-outline-secondary").addClass("btn-primary text-white");
    } else if (Math.abs(diffDays - 30) <= 3) {
      $(`.edit-quick-reminder[data-days="30"]`).removeClass("btn-outline-secondary").addClass("btn-primary text-white");
    } else if (Math.abs(diffDays - 90) <= 5) {
      $(`.edit-quick-reminder[data-days="90"]`).removeClass("btn-outline-secondary").addClass("btn-primary text-white");
    } else {
      // Not matching any preset -> custom
      $(`.edit-quick-reminder[data-days="custom"]`).removeClass("btn-outline-secondary").addClass("btn-primary text-white");
      $("#editCustomDateTime").show();
    }
  }

  // 💾 Save Update
  $("#edit-reminder-form").on("submit", function(e) {
    e.preventDefault();
    const id = $("#edit_reminder_id").val();
    const type = $("#edit_reminder_type").val();
    const contact_id = $("#edit_contact_id").val();
    const contact_name = $("#edit_contact_name").val();
    const date = $("#edit-reminder-date").val();
    const time = $("#edit-reminder-time").val();
    const note = $("#edit-reminder-notes").val();

    if (!date || !time) {
      alert("Please select a date and time.");
      return;
    }

    $.ajax({
      url: "public/ajax/customers_reminder_update.php",
      type: "POST",
      data: {
        action: "update",
        reminder_id: id,
        supplier_id: customerId,
        type: type,
        contact_id: contact_id,
        contact_name: contact_name,
        reminder_date: `${date}`,
        reminder_time: `${time}:00`,
        notes: note
      },
      beforeSend: function() {
        $("#edit-reminder-form button[type=submit]")
          .prop("disabled", true)
          .html('<i class="fa fa-spinner fa-spin me-1"></i> Updating...');
      },
      success: function(res) {
        $("#edit-reminder-form button[type=submit]")
          .prop("disabled", false)
          .html('<i class="fa fa-save me-1"></i> Update Reminder');

          if (res.success) {
                // $msg.text(res.msg)
                //     .removeClass("text-danger")
                //     .addClass("text-success")
                //     .fadeIn(200).delay(1500).fadeOut(500);
                // setTimeout(function() {
                    // $("#editReminderModal").modal('hide');
                // },500);
                editModal.hide();
                loadReminders(true); // reload list
            } else {
                // $msg.text(res.msg || "Failed to update reminder")
                //     .removeClass("text-success")
                //     .addClass("text-danger")
                //     .fadeIn(200).delay(2000).fadeOut(500);
                alert(res.msg || "Failed to update reminder");
            }
      },
      error: function() {
        alert("Failed to update reminder.");
      }
    });
  });

    // old scripts added start
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

        $.post("public/ajax/customers_reminder_fetch.php",
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
        $.post("public/ajax/customers_reminder_fetch.php",
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

    // Compact display (example for small note cards)
    function formatReminderList(reminder) {
      const formattedNote = (reminder.note || '').replace(/\n/g, "<br>");
      const contactName = reminder.contact_name ? `<span class="badge bg-success-subtle text-success border border-success small px-2 rounded-pill">${reminder.contact_name}</span>` : '';
      const typeBadge = reminder.type ? `<span class="badge bg-primary-subtle text-primary border border-primary small px-2 rounded-pill">${reminder.type}</span>` : '';

      return `
        <div class="note-card mb-2 p-2 border-bottom rounded-3 bg-light-subtle">
          <div class="note-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex flex-wrap align-items-center gap-1">
              ${typeBadge}
              ${contactName}
            </div>
            <span class="small text-muted">${reminder.reminder_at || ''}</span>
          </div>
          <div class="note-text mt-1 small text-dark">${formattedNote}</div>
        </div>
      `;
    }


    // Larger detailed version with edit button
    function formatReminderListL(reminder) {
      const formattedNote = (reminder.note || '').replace(/\n/g, "<br>");
      const contactName = reminder.contact_name ? `<span class="badge bg-success-subtle text-success border border-success small px-2 rounded-pill">${reminder.contact_name}</span>` : '';
      const typeBadge = reminder.type ? `<span class="badge bg-primary-subtle text-primary border border-primary small px-2 rounded-pill">${reminder.type}</span>` : '';

      return `
        <div class="note-card mb-2 p-2 border-bottom rounded-3 bg-white shadow-sm" data-id="${reminder.id}">
          <div class="note-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-1 flex-wrap">
              ${typeBadge}
              ${contactName}
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="text-muted small">${reminder.reminder_at || ''}</span>
              <button type="button" class="btn btn-xs btn-outline-secondary ms-2 edit-reminder-btn"
                data-id="${reminder.id}"
                onclick='openEditReminderModal(${JSON.stringify(reminder)})'
                title="Edit Reminder">
                <i class="fa fa-edit"></i>
              </button>
            </div>
          </div>
          <div class="note-text mt-1 small text-dark">${formattedNote}</div>
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
       let formData = $(this).serialize() + "&customer_id=<?= $id ?? 0 ?>";

       $.ajax({
           url: "public/ajax/customers_reminder_add.php",
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
    // old scripts added end

});
</script>


<script>
function setContact() {
  const type = $("#type").val() || '';
  const company = $("#company").val() || '';
  const name = $("#name").val() || '';
  const phone = $("#phone").val() || '';
  const email = $("#email").val() || '';

  // Email Button
  if ($("#enable_email").length && $("#emailbtn").length) {
    if ($("#enable_email").prop("checked")) {
      $("#emailbtn").show();
    } else {
      $("#emailbtn").hide();
      if (document.getElementById('notesBtn')) document.getElementById('notesBtn').click();
    }
  }

  // WhatsApp Button
  if ($("#enable_whatsapp").length && $("#whatsappbtn").length) {
    if ($("#enable_whatsapp").prop("checked")) {
      $("#whatsappbtn").show();
    } else {
      $("#whatsappbtn").hide();
      if (document.getElementById('notesBtn')) document.getElementById('notesBtn').click();
    }
  }

  // Text updates — only if elements exist
  $("#named").text(company + " / " + name);
  $("#typed").text(type);
  $("#emaild").html('<i class="fa fa-envelope me-1"></i> ' + email);
  $("#phoned").html('<i class="fa fa-phone me-1"></i> ' + phone);
}
</script>

<script>
$(function() {

  // ---------------- TYPE & VISIBILITY BUTTON HANDLERS ----------------
  $(document).on("click", ".note-type-btn", function() {
    $(".note-type-btn").removeClass("btn-primary text-white").addClass("btn-outline-primary");
    $(this).removeClass("btn-outline-primary").addClass("btn-primary text-white");
    $("#note_type").val($(this).data("type"));
  });

  $(document).on("click", ".note-visibility-btn", function() {
    $(".note-visibility-btn").removeClass("btn-success text-white").addClass("btn-outline-success");
    $(this).removeClass("btn-outline-success").addClass("btn-success text-white");
    $("#note_visibility").val($(this).data("vis"));
  });

  // ---------------- SAVE NOTE ----------------
  $("#supplier-note-form").on("submit", function(e){
    e.preventDefault();

    const $form = $(this);
    const $msg  = $("#note-success-msg");
    const notes = $form.find("textarea[name='notes']").val().trim();
    const uname = $form.find("input[name='uname']").val();
    const type = $("#note_type").val();
    const visibility = $("#note_visibility").val();

    if (notes === "") return;

    $.ajax({
      url: "public/ajax/customers_logs.php",
      type: "POST",
      dataType: "json",
      data: { 
        action: "save",
        notes: notes, 
        name: uname, 
        type: type,
        visibility: visibility,
        supplier_id: "<?= $id ?? 0 ?>"
      },
      success: function(res){
        if(res.success){
          $form[0].reset();
          $("#note_type").val("General");
          $("#note_visibility").val("Public");

          $(".note-type-btn, .note-visibility-btn")
            .removeClass("btn-primary text-white btn-success text-white")
            .addClass("btn-outline-primary btn-outline-success");

          $("#noteTypeButtons [data-type='General']").removeClass("btn-outline-primary").addClass("btn-primary text-white");
          $("#noteVisibilityButtons [data-vis='Public']").removeClass("btn-outline-success").addClass("btn-success text-white");

          $msg.fadeIn(200).delay(1500).fadeOut(500);
          loadNotes(true);
        } else {
          $msg.text(res.error || "Failed to save note")
              .removeClass("text-success").addClass("text-danger")
              .fadeIn(200).delay(2000).fadeOut(500);
        }
      },
      error: function(){
        $msg.text("Error: Could not save note")
            .removeClass("text-success").addClass("text-danger")
            .fadeIn(200).delay(2000).fadeOut(500);
      }
    });
  });

  // ---------------- NOTES HANDLING ----------------
  let notesOffset = 0;
  const notesLimit = 5;
  const supplierId = <?= $id ?? 0 ?>;
  const editModal = new bootstrap.Modal('#editNoteModal');

  // 🎨 Enhanced formatter with name shown first
    function formatNoteHtml(log) {
      const formattedNotes = (log.notes || "").replace(/\n/g, "<br>");
      const noteType = log.type || "General";
      const visibility = log.visibility || "Public";
      const createdAt = log.created_at || "";
      const createdBy = log.name || "Unknown";

      // Choose icon by note type
      let typeIcon = '<i class="fa fa-sticky-note text-secondary me-1"></i>';
      if (noteType === "Call") typeIcon = '<i class="fa fa-phone text-primary me-1"></i>';
      else if (noteType === "Email") typeIcon = '<i class="fa fa-envelope text-success me-1"></i>';
      else if (noteType === "Meeting") typeIcon = '<i class="fa fa-handshake text-warning me-1"></i>';

      // Visibility badge
      const visBadge = visibility === "Private"
        ? '<span class="badge bg-danger-subtle text-danger border border-danger rounded-pill ms-2"><i class="fa fa-lock me-1"></i>Private</span>'
        : '<span class="badge bg-success-subtle text-success border border-success rounded-pill ms-2"><i class="fa fa-globe me-1"></i>Public</span>';

      // Construct HTML (Name First)
      return `
        <div class="note-card border rounded-3 p-2 mb-2 bg-light-subtle shadow-sm" data-id="${log.id}">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="d-flex align-items-center small fw-semibold">
              <i class="fa fa-user text-secondary me-1"></i> ${createdBy}
              <small class="text-muted ms-2">${createdAt}</small>
            </div>
            <div class="btn-group btn-group-xs">
              <button class="btn btn-light btn-xs border edit-note-btn"
                data-id="${log.id}" data-type="${noteType}"
                data-vis="${visibility}" data-notes="${(log.notes || '').replace(/"/g, '&quot;')}">
                <i class="fa fa-pen"></i>
              </button>
              <button class="btn btn-light btn-xs border text-danger delete-note-btn" data-id="${log.id}">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </div>

          <div class="d-flex align-items-center small fw-semibold mb-1">
            ${typeIcon} ${noteType} ${visBadge}
          </div>

          <div class="note-text small text-dark mb-1">${formattedNotes}</div>
        </div>`;
    }

  // 🟢 Load notes
  function loadNotes(ref = false) {
    if (ref) {
      notesOffset = 0;
      $("#notes-container").empty();
      $("#load-more-notes").prop("disabled", false).text("Load More");
    }

    $.post("public/ajax/customers_logs.php", 
      { action: "fetch", supplier_id: supplierId, offset: notesOffset, limit: notesLimit },
      function (res) {
        if (res.logs && res.logs.length > 0) {
          const container = $("#notes-container");
          res.logs.forEach(log => container.append(formatNoteHtml(log)));
          notesOffset += res.logs.length;
        } else {
          $("#load-more-notes").prop("disabled", true).text("No more notes");
        }
      }, "json"
    );
  }

  loadNotes();
  $("#load-more-notes").on("click", function() { loadNotes(); });

  // ✏️ Open edit modal
  $(document).on("click", ".edit-note-btn", function() {
    const id = $(this).data("id");
    const type = $(this).data("type");
    const vis = $(this).data("vis");
    const notes = $(this).data("notes");

    $("#edit_note_id").val(id);
    $("#edit_note_text").val(notes);
    $("#edit_note_type").val(type);
    $("#edit_note_visibility").val(vis);

    highlightEditType(type);
    highlightEditVis(vis);
    editModal.show();
  });

  function highlightEditType(type) {
    $(".edit-note-type").removeClass("btn-primary text-white").addClass("btn-outline-primary");
    $(`.edit-note-type[data-type='${type}']`).removeClass("btn-outline-primary").addClass("btn-primary text-white");
  }

  function highlightEditVis(vis) {
    $(".edit-note-vis").removeClass("btn-success text-white").addClass("btn-outline-success");
    $(`.edit-note-vis[data-vis='${vis}']`).removeClass("btn-outline-success").addClass("btn-success text-white");
  }

  $(document).on("click", ".edit-note-type", function() {
    $("#edit_note_type").val($(this).data("type"));
    highlightEditType($(this).data("type"));
  });

  $(document).on("click", ".edit-note-vis", function() {
    $("#edit_note_visibility").val($(this).data("vis"));
    highlightEditVis($(this).data("vis"));
  });

  // 💾 Update note
  $("#edit-note-form").on("submit", function(e) {
    e.preventDefault();
    $.ajax({
      url: "public/ajax/customers_logs.php",
      type: "POST",
      dataType: "json",
      data: $(this).serialize() + "&action=update&supplier_id="+supplierId,
      success: function(res) {
        if (res.success) {
          editModal.hide();
          loadNotes(true);
        } else {
          alert(res.error || "Error updating note.");
        }
      },
      error: function() {
        alert("Failed to update note.");
      }
    });
  });

  // 🗑️ Delete note
  $(document).on("click", ".delete-note-btn", function() {
    const id = $(this).data("id");
    if (!confirm("Are you sure you want to delete this note?")) return;

    $.ajax({
      url: "public/ajax/customers_logs.php",
      type: "POST",
      dataType: "json",
      data: { action: "delete", id: id, supplier_id: supplierId },
      success: function(res) {
        if (res.success) {
          loadNotes(true);
        } else {
          alert(res.error || "Error deleting note.");
        }
      },
      error: function() {
        alert("Failed to delete note.");
      }
    });
  });

});
</script>


<script>
    $(document).ready(function(){
        
        // Supplier info save logic
        let lastSavedValues = {};
        $("#saveContactInfo").on("click", function () {
          let requests = []; // track all AJAX calls
          $("#info :input").each(function () {
            let $input = $(this);
            let fieldName = $input.attr("name");
            if (!fieldName) return; // skip unnamed fields
            // Handle checkbox values
            let fieldValue = $input.is(":checkbox")
              ? ($input.prop("checked") ? 1 : 0)
              : $input.val();
            // Validate required fields
            if ((fieldName === "name" || fieldName === "email") && fieldValue === "") {
              $input.addClass("is-invalid");
              return;
            } else {
              $input.removeClass("is-invalid");
            }
            // Skip unchanged
            if (lastSavedValues[fieldName] === fieldValue) {
              return;
            }
            // Save field via AJAX
            const req = $.ajax({
              url: "public/ajax/customers_save.php",
              type: "POST",
              data: {
                supplier_id: "<?= $id ?? 0 ?>",
                field: fieldName,
                value: fieldValue
              },
              success: function (response) {
                lastSavedValues[fieldName] = fieldValue;
                $input.addClass("is-valid");
                setTimeout(() => $input.removeClass("is-valid"), 1500);
              },
              error: function () {
                $input.addClass("is-invalid");
              }
            });
            requests.push(req);
          });
          // ✅ Always run setContact(), even if no AJAX requests
          if (requests.length === 0) {
            setContact();
          } else {
            $.when.apply($, requests).always(function () {
              $("#type").val(lastSavedValues['type'] || $("#type").val());
              $("#company").val(lastSavedValues['company'] || $("#company").val());
              $("#name").val(lastSavedValues['name'] || $("#name").val());
              $("#phone").val(lastSavedValues['phone'] || $("#phone").val());
              $("#email").val(lastSavedValues['email'] || $("#email").val());
              setContact();
            });
          }
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
                url: "public/ajax/customers_save.php",
                type: "POST",
                data: formData,
                processData: false,  // important
                contentType: false,  // important
                success: function(response){
                    // optional: show preview or a ✅ indicator
                    if(response.success && response.photo){
                        $("#photo1Preview").html('<img src="uploads/customers/'+ response.photo +'" alt="Photo 1" class="img-thumbnail" style="max-width: 100px;">');
                        $("#image1").html('<img id="idImage" src="uploads/customers/'+response.photo+'" alt="ID Image" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain;">');
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
                url: "public/ajax/customers_save.php",
                type: "POST",
                data: formData,
                processData: false,  // important
                contentType: false,  // important
                success: function(response){
                    // optional: show preview or a ✅ indicator
                    if(response.success && response.photo){
                        $("#photo2Preview").html('<img src="uploads/customers/'+ response.photo +'" alt="Photo 2" class="img-thumbnail" style="max-width: 100px;">');
                        $("#image2").html('<img id="idImage" src="uploads/customers/'+response.photo+'" alt="ID Image" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain;">');
                    }
                },
                error: function(){
                    console.error("Error uploading photo.");
                }
            });
        });

        // //notes ----------------------------------------------
        // // add notes
        // $("#supplier-note-form").on("submit", function(e){
        //     e.preventDefault();

        //     let $form = $(this);
        //     let $msg  = $("#note-success-msg");
        //     let notes = $form.find("textarea[name='notes']").val().trim();
        //     let uname = $form.find("input[name='uname']").val();

        //     if(notes === "") return;

        //     $.ajax({
        //         url: "public/ajax/customers_logs_save.php",
        //         type: "POST",
        //         data: { notes: notes, name: uname, supplier_id: "<?= $id ?? 0 ?>" },
        //         dataType: "json",
        //         success: function(res){
        //             if(res.success){
        //                 $form[0].reset();
        //                 // show success message briefly
        //                 $msg.fadeIn(200).delay(1500).fadeOut(500);
                        
        //                 // refresh notes list from start
        //                 loadNotes(true);
        //             } else {
        //                 $msg.text(res.error || "Failed to save note")
        //                     .removeClass("text-success")
        //                     .addClass("text-danger")
        //                     .fadeIn(200).delay(2000).fadeOut(500);
        //             }
        //         },
        //         error: function(){
        //             $msg.text("Error: Could not save note")
        //                 .removeClass("text-success")
        //                 .addClass("text-danger")
        //                 .fadeIn(200).delay(2000).fadeOut(500);
        //         }
        //     });
        // });

        // let notesOffset = 0;
        // const notesLimit = 5; // fetch 5 notes at a time
        // const supplierId = <?= $id ?? 0 ?>; // current supplier ID

        // function formatNoteHtml(log) {
        //     const formattedNotes = log.notes.replace(/\n/g, "<br>");
        //     return `
        //       <div class="note-card">
        //         <div class="note-header">
        //           <span>${log.created_at}</span>
        //           <span>${log.name}</span>
        //         </div>
        //         <div class="note-text">${formattedNotes}</div>
        //       </div>
        //     `;
        // }

        // function loadNotes(ref = false) {
        //     if (ref === true) {
        //         notesOffset = 0;
        //         $("#notes-container").empty();   // clear old notes
        //         $("#load-more-notes").prop("disabled", false).text("Load More"); // reset button
        //     }

        //     $.post("public/ajax/customers_logs_fetch.php", 
        //       { supplier_id: supplierId, offset: notesOffset, limit: notesLimit },
        //       function(res){
        //         if (res.logs && res.logs.length > 0) {
        //             const container = $("#notes-container");
        //             res.logs.forEach(log => {
        //                 container.append(formatNoteHtml(log));
        //             });
        //             notesOffset += res.logs.length;
        //         } else {
        //             $("#load-more-notes").prop("disabled", true).text("No more notes");
        //         }
        //       }, "json");
        // }

        // // Initial load
        // loadNotes();

        // // Load more button
        // $("#load-more-notes").on("click", function(){
        //     loadNotes();
        // });
 

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

            $.post("public/ajax/customers_call_logs.php", 
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

  // Split datetime (YYYY-MM-DD HH:MM:SS)
  // const [date, time] = (reminder.reminder_at || '').split(' ');
  // document.getElementById('edit-reminder-date').value = date || '';
  // document.getElementById('edit-reminder-time').value = (time || '').slice(0, 5) || '';
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
    

// Handle first reminder form submit
// $(document).on("submit", "#edit-reminder-form", function(e) {
//     e.preventDefault();

//     const $form = $(this);
//     const supplierId = <?= $id ?? 0 ?>; // your global supplierId variable

//     const formData = {
//         reminder_id: $form.find("input[name='reminder_id']").val(),
//         supplier_id: supplierId,
//         type: $form.find("input[name='type']").val(),
//         contact_id: $form.find("input[name='contact_id']").val(),
//         reminder_date: $form.find("input[name='reminder_date']").val(),
//         reminder_time: $form.find("input[name='reminder_time']").val(),
//         notes: $form.find("textarea[name='notes']").val()
//     };

//     // const $msg = $('<span class="small mt-1"></span>').insertAfter($form.find("button[type='submit']"));
//     const $msg = $('#first_rem');

//     $.ajax({
//         url: "public/ajax/customers_reminder_update.php",
//         type: "POST",
//         data: formData,
//         dataType: "json",
//         success: function(res) {
//             if (res.success) {
//                 $msg.text(res.msg)
//                     .removeClass("text-danger")
//                     .addClass("text-success")
//                     .fadeIn(200).delay(1500).fadeOut(500);

//                 setTimeout(function() {
//                     $("#editReminderModal").modal('hide');
//                 },500);
                
//                 // Optionally reload reminders list
//                 loadReminders(true);
//             } else {
//                 $msg.text(res.msg || "Failed to update reminder")
//                     .removeClass("text-success")
//                     .addClass("text-danger")
//                     .fadeIn(200).delay(2000).fadeOut(500);
//             }
//         },
//         error: function() {
//             $msg.text("Error: Could not update reminder")
//                 .removeClass("text-success")
//                 .addClass("text-danger")
//                 .fadeIn(200).delay(2000).fadeOut(500);
//         }
//     });
// });

});
</script>
<script>
function reloadContact() {
    $.post("public/ajax/customers_fetch.php", 
      { supplier_id: <?=$id??0?>},
      function(res){
        if(res.success==1){
            Object.entries(res.fields).forEach(([key, val],ind) => {
                if(key=='photo'&&val!='') {
                    // $("#photod").show();
                    $("#photodimg").attr("src","uploads/customers/"+val);
                }
                else if(key=='photo1'&&val!='') {
                    // $("#photo1d").show();
                    $("#photo1dimg").attr("src","uploads/customers/"+val);
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
  // Preview Side 1
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

  // Preview Side 2
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

    if (tab === 'whatsapp') {
      if (typeof window.refreshWhatsAppChat === 'function') {
        // refreshWhatsAppChat now returns a promise
        const p = window.refreshWhatsAppChat();
        if (p && typeof p.then === 'function') {
          p.finally(() => {
            // small timeout to let display changes settle
            setTimeout(() => {
              if (typeof window.ensureScrollBottom === 'function') window.ensureScrollBottom();
            }, 1);
          });
        } else {
          // fallback if it doesn't return a promise
          setTimeout(() => { if (typeof window.ensureScrollBottom === 'function') window.ensureScrollBottom(); }, 1);
        }
      } else {
        // fallback: ensure scroll anyway
        setTimeout(() => { if (typeof window.ensureScrollBottom === 'function') window.ensureScrollBottom(); }, 1);
      }
    } 

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

  fetch(`public/ajax/customers_get_emails.php?id=<?=$id??0?>&page=${currentPage}&limit=${perPage}`)
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

  fetch(`public/ajax/customers_get_emails_details.php?id=${emailId}`)
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

/*function editStatus() {
    $(".settinginp").hide();
    $(".settinginpStatus").show();
    $("#settingsModaldg").removeClass('modal-lg');
    $("#settingsModaldg").addClass('modal-md');
    $("#settingsModalLabel").html('<i class="fa fa-user-edit me-2"></i>Edit Status');
    $("#settingsModal").modal('show');
}*/

$("#settingsModal").on('hidden.bs.modal', function () {
    $("#settingsModaldg").removeClass('modal-md');
    $("#settingsModaldg").addClass('modal-lg');
    $("#settingsModalLabel").html('<i class="fa fa-user-edit me-2"></i>Edit Contact Information');
    $(".settinginp").show();
    reloadContact();
});
</script>
<script>
/**
 * Normalize strings for comparison: trim, lowercase, collapse spaces, strip punctuation.
 * This helps match displayed badge text like "Lead (Active)" with button data-values.
 */
function normalizeStatus(s) {
  if (!s) return '';
  return s.toString()
          .trim()
          .toLowerCase()
          .replace(/[^\w\s]/g, '')   // remove punctuation
          .replace(/\s+/g, ' ');    // collapse spaces
}

/* Open the modal (keep calling this from your badge onclick) */
function editStatus() {
  // clear prior selection & note errors then show modal
  document.querySelectorAll('#statusButtonsContainer .status-btn').forEach(b=>{
    b.classList.remove('btn-primary');
    b.classList.add('btn-outline-secondary');
  });
  document.getElementById('status-note').value = '';
  document.getElementById('status-note-error').classList.add('d-none');

  const modalEl = document.getElementById('statusModal');
  const modal = new bootstrap.Modal(modalEl);
  modal.show();
}

/* When modal shown: read current badge text and select matching button */
document.getElementById('statusModal').addEventListener('shown.bs.modal', function () {
  const badge = document.getElementById('typed');
  const currentText = badge ? (badge.textContent || badge.innerText || '') : '';
  const normCurrent = normalizeStatus(currentText);

  let matched = false;
  document.querySelectorAll('#statusButtonsContainer .status-btn').forEach(btn => {
    const val = btn.dataset.value || btn.textContent;
    if (normalizeStatus(val) === normCurrent) {
      btn.classList.add('btn-primary');
      btn.classList.remove('btn-outline-secondary');
      matched = true;
    } else {
      btn.classList.remove('btn-primary');
      btn.classList.add('btn-outline-secondary');
    }
  });

  // optional: if nothing matches, you can choose to auto-select first button
  // if(!matched){ const first = document.querySelector('#statusButtonsContainer .status-btn'); if(first){ first.classList.add('btn-primary'); first.classList.remove('btn-outline-secondary'); } }
});

/* Single-select toggle for buttons */
document.addEventListener('click', function(e){
  if (e.target && e.target.classList && e.target.classList.contains('status-btn')) {
    document.querySelectorAll('#statusButtonsContainer .status-btn').forEach(b=>{
      b.classList.remove('btn-primary');
      b.classList.add('btn-outline-secondary');
    });
    e.target.classList.add('btn-primary');
    e.target.classList.remove('btn-outline-secondary');
  }
});

/* Save status: require note and post to backend */
document.getElementById('saveStatusBtn').addEventListener('click', function(){
  const selected = document.querySelector('#statusButtonsContainer .status-btn.btn-primary');
  const noteEl = document.getElementById('status-note');
  const note = noteEl.value.trim();
  const noteErr = document.getElementById('status-note-error');

  if (!selected) {
    alert('Please select a status before saving.');
    return;
  }
  if (!note) {
    noteErr.classList.remove('d-none');
    noteEl.focus();
    return;
  } else {
    noteErr.classList.add('d-none');
  }

  const statusValue = selected.dataset.value;
  const supplierId = <?= $id ?? 0 ?>;

  // disable button during save
  const $btn = $(this);
  $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

  $.ajax({
    url: "public/ajax/customers_save.php",
    type: "POST",
    dataType: "json",
    data: {
      supplier_id: supplierId,
      field: 'type',
      value: statusValue,
      status_note: note
    },
    success: function(res) {
      if (res && res.success) {
        // update badge text
        $('#typed').text(statusValue);

        // close modal
        $('#statusModal').modal('hide');

        // refresh full contact if available
        if (typeof reloadContact === 'function') reloadContact();
      } else {
        alert((res && res.msg) ? res.msg : 'Failed to update status');
      }
    },
    error: function() {
      alert('Error saving status — check server logs.');
    },
    complete: function() {
      $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Status');
    }
  });
});
</script>


<!-- Add / Edit Requirement Modal -->
<div class="modal fade" id="addRequirementModal" tabindex="-1" aria-labelledby="addRequirementLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addRequirementLabel"><i class="fa fa-briefcase me-2"></i> Add New Requirements</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="requirementForm" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="customer_id" id="customer_id" value="<?= $customer_id ?? 0 ?>">
          <div id="requirementRows"></div>

          <button type="button" id="addRowBtn" class="btn btn-outline-primary btn-sm w-100 mt-2">
            <i class="fa fa-plus"></i> Add Another Requirement
          </button>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success" id="reqsaveBtn"><i class="fa fa-save me-1"></i> Save All Requirements</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Job Title Modal -->
<div class="modal fade" id="addJobTitleModal" tabindex="-1" aria-labelledby="addJobTitleLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title" id="addJobTitleLabel">Add Job Title</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="jobTitleForm">
        <div class="modal-body">
          <input type="text" id="newJobTitle" class="form-control" placeholder="Enter new title" required>
        </div>
        <div class="modal-footer py-2">
          <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa fa-save me-1"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
let jobTitles = [];
$(function () {
  const customerID = <?= isset($customer_id) ? intval($customer_id) : 0 ?>;
  const ajaxURL = "public/ajax/customers_requirements.php";
  const jobTitlesURL = "public/ajax/job_titles.php";
  // const jobTitlesAddURL = "public/ajax/job_titles_add.php";
  let currentJobRow = null; // row reference when adding job title from modal

  // load job titles from server
  function loadJobTitles(cb){
    $.post(jobTitlesURL, { action: "fetch" }, res=>{
      jobTitles = (res && res.success && Array.isArray(res.data)) ? res.data : [];
      cb && cb();
    }, "json").fail(()=>{
      jobTitles = [];
      cb && cb();
    });

    $("#addRowBtn").hide();
    $("#reqsaveBtn").html('<i class="fa fa-save me-1"></i> Save Requirement');
  }

  // generate job title buttons + hidden input + custom input container
  function generateJobTitleButtons(selectedTitle=""){
    let html = '<div class="job-title-buttons d-flex flex-wrap gap-1 mb-1">';
    jobTitles.forEach(t=>{
      const active = (t.title === selectedTitle);
      html += `<button type="button" class="btn btn-sm ${active?"btn-primary text-white":"btn-outline-primary"} job-btn" data-title="${escapeHtml(t.title)}">${escapeHtml(t.title)}</button>`;
    });
    const exists = jobTitles.some(t => t.title === selectedTitle);
    const otherActive = (!exists && selectedTitle) ? "btn-primary text-white" : "btn-outline-secondary";
    html += `<button type="button" class="btn btn-sm ${otherActive} job-btn-other"><i class="fa fa-plus"></i> Other</button>`;
    html += `</div>`;
    const showCustom = (!exists && selectedTitle) ? "block" : "none";
    const customValue = (!exists ? selectedTitle : "");
    html += `<input type="hidden" name="job_title[]" class="job-title-input" value="${escapeHtml(selectedTitle)}">`;
    html += `<div class="custom-job-title mt-2" style="display:${showCustom};">
               <input type="text" class="form-control form-control-sm custom-job-input" placeholder="Enter custom job title" value="${escapeHtml(customValue)}">
             </div>`;
    return html;
  }

  // small helper to escape text for insertion into HTML
  function escapeHtml(s) {
    if (!s) return "";
    return String(s).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]); });
  }

  // add single requirement row
  function addRequirementRow(index = 0, data = {}) {
    const chosenTitle = data.job_title || "";
    const numEmployees = data.num_employees || 1;
    const ratePay = data.rate_pay || "";
    const startDate = data.start_date || "<?=date("Y-m-d",strtotime("next sunday",strtotime($date)))?>";
    const accommodationChecked = data.accommodation == 1 ? "checked" : "";
    const accommodationDetails = data.accommodation_details || "";
    const transportChecked = data.transport == 1 ? "checked" : "";
    const transportDetails = data.transport_details || "";
    const overtimeChecked = data.overtime == 1 ? "checked" : "";
    const overtimePoliciesHTML = (data.overtime_policies ? (function(){
      try { const pol = JSON.parse(data.overtime_policies); return pol.map(p=> `<div class="d-flex align-items-center gap-1 mt-1 border p-1 rounded bg-white"><input type="text" name="overtime_policy[]" class="form-control form-control-sm" value="${escapeHtml(p.policy)}" required><input type="number" name="overtime_rate[]" class="form-control form-control-sm" value="${escapeHtml(p.rate)}" required><button type="button" class="btn btn-sm btn-outline-danger remove-overtime"><i class="fa fa-times"></i></button></div>`).join(''); } catch(e){return "";}
    })() : "");
    const attachmentHint = data.attachment ? `<div class="small mt-1"><a href="uploads/requirements/${escapeHtml(data.attachment)}" target="_blank"><i class="fa fa-eye"></i> View Current File</a></div>` : "";
    const expiry = data.expiry || (function(){ let d=new Date(); d.setDate(d.getDate()+14); return d.toISOString().split('T')[0]; })();
    const expiry_alertChecked = data.expiry_alert == 1 ? "checked" : "";
    const typeVal = data.req_type || "Enquiry";

    if(index==0) {
      $("#addRequirementLabel").html('<i class="fa fa-briefcase me-2"></i> Add New Requirements');
    }

    const html = `
      <div class="requirement-item border rounded-3 p-3 mb-3 bg-light-subtle shadow-sm">
        ${data.id ? ``:`<div class="d-flex justify-content-between align-items-center mb-2">
          <strong>Requirement #${index + 1}</strong>
          <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fa fa-trash"></i></button>
        </div>`}

        <div class="row g-2">
          <div class="col-md-12">
            <label class="small fw-semibold">Job Title *</label>
            ${generateJobTitleButtons(chosenTitle)}
          </div>

          <div class="col-md-4">
            <label class="small fw-semibold">Employees *</label>
            <div class="input-group input-group-sm">
              <button type="button" class="btn btn-outline-secondary emp-minus">-</button>
              <input type="number" name="num_employees[]" class="form-control text-center" min="1" value="${escapeHtml(numEmployees)}" required>
              <button type="button" class="btn btn-outline-secondary emp-plus">+</button>
            </div>
          </div>

          <div class="col-md-4">
            <label class="small fw-semibold">Rate (per hr) *</label>
            <input type="number" name="rate_pay[]" class="form-control form-control-sm" min="0" step="0.01" value="${escapeHtml(ratePay)}" required>
          </div>

          <div class="col-md-4">
            <label class="small fw-semibold">Start Date *</label>
            <input type="date" name="start_date[]" class="form-control form-control-sm" value="${escapeHtml(startDate)}" required>
          </div>
        </div>

        <div class="row g-2 mt-3">
          <div class="col-md-3">
            <label class="small fw-semibold">Expiry</label>
            <input type="date" name="expiry[]" class="form-control form-control-sm expiry-field" value="${escapeHtml(expiry)}" required>
          </div>
          <div class="col-md-2">
              <label class="form-check-label small">Expiry alert</label>
            <div class="mt-1 form-check">
              <input class="form-check-input" type="checkbox" name="expiry_alert[]" id="expiry_alert" value="1" ${expiry_alertChecked}>
              <label for="expiry_alert">Enable</label>
            </div>
          </div>

          <div class="col-md-3">
            <label class="small fw-semibold">Type</label>
            <select name="req_type[]" class="form-control form-control-sm">
              <option value="Enquiry"${typeVal==="Enquiry"?" selected":""}>Enquiry</option>
              <option value="Active"${typeVal==="Active"?" selected":""}>Active</option>
              <option value="Expired"${typeVal==="Expired"?" selected":""}>Expired</option>
            </select>
          </div>

          <div class="col-md-4">
            <div class="form-check">
              <input class="form-check-input accommodation-check" type="checkbox" name="accommodation[]" value="1" ${accommodationChecked}>
              <label class="form-check-label small">Accommodation</label>
            </div>
            <div class="accommodation-details mt-1" style="display:${accommodationChecked ? 'block' : 'none'};">
              <input type="text" name="accommodation_details[]" class="form-control form-control-sm" placeholder="Enter accommodation details" value="${escapeHtml(accommodationDetails)}">
            </div>
          </div>
        </div>

        <div class="row g-2 mt-2">
          <div class="col-md-4">
            <div class="form-check">
              <input class="form-check-input transport-check" type="checkbox" name="transport[]" value="1" ${transportChecked}>
              <label class="form-check-label small">Transport</label>
            </div>
            <div class="transport-details mt-1" style="display:${transportChecked ? 'block' : 'none'};">
              <input type="text" name="transport_details[]" class="form-control form-control-sm" placeholder="Enter transport details" value="${escapeHtml(transportDetails)}">
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-check">
              <input class="form-check-input overtime-check" type="checkbox" name="overtime[]" value="1" ${overtimeChecked}>
              <label class="form-check-label small">Overtime</label>
            </div>
            <div class="overtime-section mt-2" style="display:${overtimeChecked ? 'block' : 'none'};">
              <div class="overtime-container">${overtimePoliciesHTML}</div>
              <button type="button" class="btn btn-outline-secondary btn-xs add-overtime mt-1"><i class="fa fa-plus"></i> Add Policy</button>
            </div>
          </div>

          <div class="col-md-4">
            <label class="small fw-semibold">Attachment (optional)</label>
            <input type="file" name="attachment[]" class="form-control form-control-sm" accept="image/*,.pdf">
            <!--${attachmentHint}-->
          </div>
        </div>

        ${data.id ? `<input type="hidden" name="edit_id[]" value="${data.id}">` : ''}
      </div>
    `;
    $("#requirementRows").append(html);
  }

  // load requirements list (server returns HTML for #requirements-list)
  function loadRequirements() {
    $.ajax({
      url: ajaxURL,
      type: "POST",
      data: { action: "fetch", customer_id: customerID },
      beforeSend: function () { $("#requirements-list").html('<p class="text-center text-muted small">Loading...</p>'); },
      success: function (res) { $("#requirements-list").html(res); },
      error: function () { $("#requirements-list").html('<p class="text-danger text-center">Error loading requirements.</p>'); }
    });
  }

  // initial job titles load
  loadJobTitles();

  // show modal: ensure at least one row
  $("#addRequirementModal").on("show.bs.modal", function () {
    if ($("#requirementRows").children().length === 0) addRequirementRow(0);
    $("#addRowBtn").show();
    $("#reqsaveBtn").html('<i class="fa fa-save me-1"></i> Save All Requirements');
  });

  // add row
  $("#addRowBtn").on("click", function () { const idx = $(".requirement-item").length; addRequirementRow(idx); });

  // remove row
  $(document).on("click", ".remove-row", function () { $(this).closest(".requirement-item").remove(); });

  // job button handlers (including Other)
  $(document).on("click", ".job-btn", function () {
    const $btn = $(this);
    const $wrap = $btn.closest(".job-title-buttons");
    $wrap.find(".job-btn").removeClass("btn-primary text-white").addClass("btn-outline-primary");
    $wrap.find(".job-btn-other").removeClass("btn-primary text-white").addClass("btn-outline-secondary");
    $btn.removeClass("btn-outline-primary").addClass("btn-primary text-white");
    $wrap.siblings(".job-title-input").val($btn.data("title"));
    $wrap.siblings(".custom-job-title").slideUp(120).find(".custom-job-input").val("");
  });

  $(document).on("click", ".job-btn-other", function () {
    // store row to update after new title created
    currentJobRow = $(this).closest(".requirement-item");
    $("#newJobTitle").val('').focus();
    $("#addJobTitleModal").modal("show");
  });

  // custom job input updates hidden field
  $(document).on("input", ".custom-job-input", function () {
    $(this).closest(".custom-job-title").siblings(".job-title-input").val($(this).val());
  });

  // plus / minus for employees
  $(document).on("click", ".emp-plus", function () {
    const $input = $(this).siblings("input");
    $input.val(Math.max(1, parseInt($input.val()||0) + 1));
  });
  $(document).on("click", ".emp-minus", function () {
    const $input = $(this).siblings("input");
    const v = Math.max(1, parseInt($input.val()||0) - 1);
    $input.val(v);
  });

  // conditional toggles
  $(document).on("change", ".accommodation-check", function () {
    const box = $(this).closest(".col-md-4").find(".accommodation-details"); this.checked ? box.slideDown(160) : box.slideUp(160).find("input").val("");
  });
  $(document).on("change", ".transport-check", function () {
    const box = $(this).closest(".col-md-4").find(".transport-details"); this.checked ? box.slideDown(160) : box.slideUp(160).find("input").val("");
  });
  $(document).on("change", ".overtime-check", function () {
    const sec = $(this).closest(".col-md-4").find(".overtime-section"); this.checked ? sec.slideDown(160) : sec.slideUp(160).find(".overtime-container").empty();
  });

  // overtime add/remove
  $(document).on("click", ".add-overtime", function () {
    const container = $(this).siblings(".overtime-container");
    container.append('<div class="d-flex align-items-center gap-1 mt-1 border p-1 rounded bg-white"><input type="text" name="overtime_policy[]" class="form-control form-control-sm" placeholder="Policy" required><input type="number" name="overtime_rate[]" class="form-control form-control-sm" placeholder="Rate" min="0" step="0.01" required><button type="button" class="btn btn-sm btn-outline-danger remove-overtime"><i class="fa fa-times"></i></button></div>');
  });
  $(document).on("click", ".remove-overtime", function () { $(this).closest("div.d-flex").remove(); });

  // submit add job title modal (create on server, append to local list and to current row)
  $("#jobTitleForm").on("submit", e=>{
    e.preventDefault();
    const title = $("#newJobTitle").val().trim();
    if(!title) return;
    $.post(jobTitlesURL,{action:"add", title}, res=>{
      if(res?.success){
        // push new object
        jobTitles.push({ id: res.id || 0, title: res.title });
        // update current requirement row
        if(currentJobRow){
          currentJobRow.find(".col-md-12:first").html(generateJobTitleButtons(res.title));
          currentJobRow.find(".job-title-input").val(res.title);
        }
        $("#addJobTitleModal").modal("hide");
      } else {
        alert(res?.error || "Failed to add job title.");
      }
    },"json").fail(()=>alert("Error adding job title."));
  });

  // save requirements
  $("#requirementForm").on("submit", function (e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);

    // detect edit mode: presence of any edit_id[] value
    const isEdit = !!formData.getAll("edit_id[]").length;
    formData.append("action", isEdit ? "save" : "multi_save");
    formData.append("customer_id", customerID);

    var saveTxt = isEdit?'Save Requirement':'Save All Requirements';

    // convert true/false for checkboxes (we use presence of field as indicator server-side)
    // send as-is; server will treat missing as 0.

    $.ajax({
      url: ajaxURL,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: function () { $("#reqsaveBtn").prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Saving...'); },
      success: function (res) {
        $("#reqsaveBtn").prop("disabled", false).html('<i class="fa fa-save me-1"></i> '+saveTxt);
        if ((typeof res === "string" && res.trim() === "success") || (typeof res === "object" && res.success)) {
          $("#addRequirementModal").modal("hide");
          $("#requirementForm")[0].reset();
          $("#requirementRows").empty();
          loadRequirements();
        } else {
          alert("Error: " + (typeof res === "object" ? (res.error||JSON.stringify(res)) : res));
        }
      },
      error: function (xhr) { console.error(xhr.responseText); alert("Error saving requirements."); $("#reqsaveBtn").prop("disabled", false).html('<i class="fa fa-save me-1"></i> '+saveTxt); }
    });
  });

  // fetch single requirement for edit
  $(document).on("click", ".edit-requirement", function () {
    const id = $(this).data("id");
    if (!id) return;
    $.post(ajaxURL, { action: "get", id: id }, function (res) {
      if (!res || !res.id) { alert("Failed to load"); return; }
      $("#requirementRows").empty();
      addRequirementRow(0, res);
      // hide add multiple button, switch save button label
      $("#addRowBtn").hide();
      $("#reqsaveBtn").html('<i class="fa fa-save me-1"></i> Save Requirement');
      $("#addRequirementLabel").html('<i class="fa fa-briefcase me-2"></i> Edit Requirement');
      $("#addRequirementModal").modal("show");
      // ensure local jobTitles list is up-to-date (if needed)
      loadJobTitles();
    }, "json").fail(function() { alert("Error loading requirement."); });
  });

  // delete requirement
  $(document).on("click", ".delete-requirement", function () {
    const id = $(this).data("id");
    if (!id || !confirm("Delete this requirement?")) return;
    $.post(ajaxURL, { action: "delete", id: id }, function (res) {
      if ((typeof res === "string" && res.trim() === "success") || (typeof res === "object" && res.success)) loadRequirements();
      else alert("Delete failed: " + (res.error || res));
    }).fail(function(){ alert("Error deleting requirement."); });
  });

  // reset modal on hide
  $("#addRequirementModal").on("hidden.bs.modal", function () { $("#requirementForm")[0].reset(); $("#requirementRows").empty(); });

  // initial load of current requirements
  loadRequirements();

});
</script>




<!-- Add / Edit Contact Modal -->
<div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">

      <!-- HEADER -->
      <div class="modal-header py-2 rounded-top-4">
        <h6 class="modal-title fw-semibold d-flex align-items-center" id="addContactLabel">
          <i class="fa fa-user me-2"></i>
          <span id="contactModalTitle">Add New Contact</span>
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- FORM -->
      <form id="contactForm" enctype="multipart/form-data">
        <div class="modal-body p-4">

          <!-- Hidden Fields -->
          <input type="hidden" name="id" id="contact_id">
          <input type="hidden" name="customer_id" id="contact_customer_id" value="<?= $customer_id ?? 0 ?>">

          <!-- Contact Info -->
          <div class="row g-3">
            
            <div class="col-md-6">
              <label for="contact_name" class="form-label fw-semibold small mb-1">Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="contact_name" class="form-control form-control-sm rounded-pill" placeholder="John Doe" required>
            </div>

            <div class="col-md-6 d-none">
              <label for="contact_designation" class="form-label fw-semibold small mb-1">Designation</label>
              <input type="text" name="designation" id="contact_designation" class="form-control form-control-sm rounded-pill" placeholder="Sales Manager">
            </div>

            <div class="col-md-6">
              <label for="contact_phone" class="form-label fw-semibold small mb-1">Phone</label>
              <input type="text" name="phone" id="contact_phone" class="form-control form-control-sm rounded-pill" placeholder="+91 9876543210">
            </div>

            <div class="col-md-6">
              <label for="contact_email" class="form-label fw-semibold small mb-1">Email</label>
              <input type="email" name="email" id="contact_email" class="form-control form-control-sm rounded-pill" placeholder="john@example.com">
            </div>

            <div class="col-md-6">
              <label for="contact_dob" class="form-label fw-semibold small mb-1">Date of Birth</label>
              <input type="date" name="dob" id="contact_dob" class="form-control form-control-sm rounded-pill" placeholder="">
            </div>

          </div>

          <hr class="my-3">

          <!-- Passport Uploads -->
          <div class="row g-3 align-items-center">
            <div class="col-md-6">
              <label class="form-label fw-semibold small mb-1">Passport - Side 1</label>
              <input type="file" name="photo1" id="card_front" accept="image/*" class="form-control form-control-sm rounded-pill">
              <div id="cardFrontPreview" class="mt-2 text-center"></div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small mb-1">Passport - Side 2</label>
              <input type="file" name="photo2" id="card_back" accept="image/*" class="form-control form-control-sm rounded-pill">
              <div id="cardBackPreview" class="mt-2 text-center"></div>
            </div>
          </div>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer bg-light rounded-bottom-4 py-2">
          <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">
            <i class="fa fa-times me-1"></i> Cancel
          </button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa fa-save me-1"></i> Save Contact
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
<!-- View Contact Card Modal -->
<div class="modal fade" id="viewContactCardModal" tabindex="-1" aria-labelledby="viewContactCardLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">

      <!-- Header -->
      <div class="modal-header py-2">
        <h5 class="modal-title fw-semibold d-flex align-items-center" id="viewContactCardLabel">
          <i class="fa fa-id-card me-2"></i> Passport
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Body -->
      <div class="modal-body bg-light text-center p-4" style="min-height: 75vh;">
        <!-- Tabs -->
        <ul class="nav nav-tabs justify-content-center mb-3 border-0" id="contactCardTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 py-1 fw-semibold"
                    id="contactSide1-tab" data-bs-toggle="tab" data-bs-target="#contactSide1"
                    type="button" role="tab" aria-controls="contactSide1" aria-selected="true">
              Side 1
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-1 fw-semibold"
                    id="contactSide2-tab" data-bs-toggle="tab" data-bs-target="#contactSide2"
                    type="button" role="tab" aria-controls="contactSide2" aria-selected="false">
              Side 2
            </button>
          </li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content mt-3" id="contactCardTabContent">
          <div class="tab-pane fade show active" id="contactSide1" role="tabpanel">
            <div id="contactCardImg1" class="text-center mt-4 text-muted">
              <i class="fa fa-spinner fa-spin fa-2x"></i>
              <p class="small mt-2">Loading Side 1...</p>
            </div>
          </div>

          <div class="tab-pane fade" id="contactSide2" role="tabpanel">
            <div id="contactCardImg2" class="text-center mt-4 text-muted">
              <i class="fa fa-spinner fa-spin fa-2x"></i>
              <p class="small mt-2">Loading Side 2...</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<style>
/*  #addContactModal .form-label {
    font-size: 0.85rem;
    margin-bottom: 0.3rem;
  }

  #addContactModal .form-control-sm {
    font-size: 0.9rem;
    padding: 0.35rem 0.75rem;
  }

  #addContactModal .modal-body {
    background-color: #f8f9fa;
  }*/

  #addContactModal .img-thumbnail {
    border-radius: 0.5rem;
    max-width: 100px;
    transition: all 0.2s ease-in-out;
  }

  #addContactModal .img-thumbnail:hover {
    transform: scale(1.05);
  }

  #addContactModal hr {
    border-top: 1px dashed #ccc;
  }

  #addContactModal .modal-footer {
    border-top: 1px solid #eaeaea;
  }

  #cardFrontPreview, #cardBackPreview {
    min-height: 100px;
  }
</style>
<script>
$(document).ready(function(){
  const customerID = <?= isset($customer_id) ? $customer_id : 0 ?>;
  const ajaxURL = "public/ajax/customers_contacts.php";
  // Load all contacts
  function loadContacts() {
    $.ajax({
      url: ajaxURL,
      type: "POST",
      data: { action: "fetch", customer_id: customerID },
      beforeSend: function(){
        $("#contacts-list").html('<p class="text-center text-muted small">Loading...</p>');
      },
      success: function(res){
        $("#contacts-list").html(res);
      },
      error: function(){
        $("#contacts-list").html('<p class="text-danger text-center">Error loading contacts.</p>');
      }
    });
  }
  loadContacts();
  // 🧩 File preview
  $("#card_front").on("change", function(){
    previewImage(this, "#cardFrontPreview");
  });
  $("#card_back").on("change", function(){
    previewImage(this, "#cardBackPreview");
  });

  function previewImage(input, previewContainer){
    const file = input.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e){
        $(previewContainer).html(
          `<img src="${e.target.result}" class="img-thumbnail shadow-sm" style="max-width:100px;">`
        );
      }
      reader.readAsDataURL(file);
    }
  }
  // Save (Add / Edit)
  $("#contactForm").on("submit", function(e){
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("action", "save");
    $.ajax({
      url: ajaxURL,
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      beforeSend: function(){
        $("#contactForm button[type=submit]").prop("disabled", true)
          .html('<i class="fa fa-spinner fa-spin"></i> Saving...');
      },
      success: function(res){
        $("#contactForm button[type=submit]").prop("disabled", false)
          .html('<i class="fa fa-save me-1"></i> Save Contact');

        if (res.trim() === "success") {
          $("#addContactModal").modal("hide");
          $("#contactForm")[0].reset();
          $("#contact_id").val('');
          $("#cardFrontPreview, #cardBackPreview").empty();
          loadContacts();
        } else {
          alert("Error: " + res);
        }
      }
    });
  });
  // Edit Contact
  $(document).on("click", ".edit-contact", function(){
    const id = $(this).data("id");
    $.ajax({
      url: ajaxURL,
      type: "POST",
      dataType: "json",
      data: { action: "get", id: id },
      success: function(data){
        $("#contact_id").val(data.id);
        $("#contact_name").val(data.name);
        $("#contact_phone").val(data.phone);
        $("#contact_email").val(data.email);
        $("#contact_dob").val(data.dob);
        // $("#contact_designation").val(data.designation);
        $("#contactModalTitle").text("Edit Contact");

        // Previews (if files exist)
        let cardFront = data.photo1 ? `<img src="uploads/customers/contacts/${data.photo1}" class="img-thumbnail" style="max-width:100px;">` : "";
        let cardBack  = data.photo2  ? `<img src="uploads/customers/contacts/${data.photo2}" class="img-thumbnail" style="max-width:100px;">` : "";
        $("#cardFrontPreview").html(cardFront);
        $("#cardBackPreview").html(cardBack);

        $("#addContactModal").modal("show");
      }
    });
  });
  // Delete Contact
  $(document).on("click", ".delete-contact", function(){
    if (!confirm("Are you sure you want to delete this contact?")) return;
    const id = $(this).data("id");
    $.ajax({
      url: ajaxURL,
      type: "POST",
      data: { action: "delete", id: id },
      success: function(res){
        if (res.trim() === "success") {
          loadContacts();
        } else {
          alert("Error deleting contact: " + res);
        }
      }
    });
  });
  // Reset modal
  $('#addContactModal').on('hidden.bs.modal', function(){
    $("#contactForm")[0].reset();
    $("#contact_id").val('');
    $("#contactModalTitle").text("Add New Contact");
    $("#cardFrontPreview, #cardBackPreview").empty();
  });

});
</script>
<script>
function viewContactCard(contactId) {
  const modal = new bootstrap.Modal('#viewContactCardModal');
  $("#contactCardImg1").html(`<i class="fa fa-spinner fa-spin fa-2x"></i><p class="small mt-2">Loading Side 1...</p>`);
  $("#contactCardImg2").html(`<i class="fa fa-spinner fa-spin fa-2x"></i><p class="small mt-2">Loading Side 2...</p>`);
  modal.show();
  $.ajax({
    url: "public/ajax/customers_contacts.php",
    type: "POST",
    dataType: "json",
    data: { action: "get", id: contactId },
    success: function(data) {
      if (data) {
        const side1 = data.photo1 ? `uploads/customers/contacts/${data.photo1}` : "";
        const side2 = data.photo2 ? `uploads/customers/contacts/${data.photo2}` : "";

        if (side1) {
          $("#contactCardImg1").html(`<img src="${side1}" alt="Side 1" class="img-fluid shadow">`);
        } else {
          $("#contactCardImg1").html(`<div class="text-muted mt-4"><i class="fa fa-image fa-2x mb-2 d-block"></i>No Side 1 uploaded</div>`);
        }

        if (side2) {
          $("#contactCardImg2").html(`<img src="${side2}" alt="Side 2" class="img-fluid shadow">`);
        } else {
          $("#contactCardImg2").html(`<div class="text-muted mt-4"><i class="fa fa-image fa-2x mb-2 d-block"></i>No Side 2 uploaded</div>`);
        }
      } else {
        $("#contactCardImg1, #contactCardImg2").html(`<div class="text-danger mt-4">Error loading images.</div>`);
      }
    },
    error: function() {
      $("#contactCardImg1, #contactCardImg2").html(`<div class="text-danger mt-4">Failed to load card images.</div>`);
    }
  });
}
</script>



<!-- Add Document Modal -->
<div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header py-2">
        <h6 class="modal-title fw-semibold" id="addDocumentLabel">
          <i class="fa fa-file me-2 text-primary"></i> <span id="documentModalTitle">Add New Document</span>
        </h6>
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
      </div>

      <form id="documentForm" enctype="multipart/form-data">
        <div class="modal-body py-2 px-3">

          <input type="hidden" name="id" id="doc_id">
          <input type="hidden" name="customer_id" id="doc_customer_id" value="<?= $customer_id ?? 0 ?>">

          <div class="mb-2">
            <label class="form-label fw-semibold small mb-1">Document Label *</label>
            <input type="text" name="label" id="doc_label" class="form-control form-control-xs" required>
          </div>

          <div class="mb-2">
            <label class="form-label fw-semibold small mb-1">Expiry Date</label>
            <input type="date" name="expiry_date" id="doc_expiry_date" class="form-control form-control-xs">
          </div>

          <div class="mb-2">
            <label class="form-label fw-semibold small mb-1">Upload File (PDF/Image) *</label>
            <input type="file" name="file" id="doc_file" class="form-control form-control-xs" accept=".pdf,image/*" required>
          </div>

        </div>

        <div class="modal-footer py-2">
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa fa-save me-1"></i> Save Document
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- DOCUMENT GALLERY VIEWER MODAL (NEW IMPROVED UI) -->
<style>
#prevDocBtn:hover, #nextDocBtn:hover {
    background: #0d6efd;
    color: white !important;
    border-color: #0d6efd;
}

#galleryCounter {
    font-size: 14px;
    font-weight: 600;
}
#zoomControls button:hover {
    background: #0d6efd;
    color: #fff !important;
    border-color: #0d6efd;
}

.zoom-image {
    transition: transform 0.15s ease;
    cursor: grab;
}
</style>
<div class="modal fade" id="documentGalleryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden" style="max-height: 92vh;">
      <!-- HEADER -->
      <div class="modal-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <i class="fa fa-file-text text-primary"></i>
          <h6 class="modal-title fw-semibold text-primary mb-0" id="galleryDocumentTitle">
            Document Preview
          </h6>
        </div>
        <!-- Navigation + Counter -->
        <div class="d-flex align-items-center gap-2">
            <!-- Image Zoom Controls -->
            <div id="zoomControls" class="d-none me-2">
              <button class="btn btn-outline-secondary btn-sm px-2" id="zoomOutBtn">
                <i class="fa fa-search-minus"></i>
              </button>
              <button class="btn btn-outline-secondary btn-sm px-2" id="zoomInBtn">
                <i class="fa fa-search-plus"></i>
              </button>
              <button class="btn btn-outline-secondary btn-sm px-2" id="zoomResetBtn">
                <i class="fa fa-sync"></i>
              </button>
            </div>
            <!-- Counter -->
            <span id="galleryCounter" class="text-muted small me-2">1 of 1</span>
            <!-- Prev / Next -->
            <button id="prevDocBtn" class="btn btn-outline-primary btn-sm rounded-pill px-2">
              <i class="fa fa-chevron-left"></i>
            </button>
            <button id="nextDocBtn" class="btn btn-outline-primary btn-sm rounded-pill px-2">
              <i class="fa fa-chevron-right"></i>
            </button>
            <!-- Close -->
            <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <!-- VIEWER BODY -->
      <div class="modal-body p-0 bg-light" id="galleryViewer"
           style="height: 82vh; display:flex; justify-content:center; align-items:center;">
        <div class="text-center text-muted small">Loading...</div>
      </div>
    </div>
  </div>
</div>
<script>
// =====================================================
// DOCUMENT GALLERY VIEWER - ENHANCED WITH ZOOM + KEYBOARD NAV
// =====================================================
let docGallery = [];
let currentIndex = 0;

let zoomLevel = 1;
let imageElement = null;

// Build gallery dataset
function buildDocumentGallery() {
    docGallery = [];

    $(".view-document-gallery").each(function () {
        docGallery.push({
            file: $(this).data("file"),
            type: $(this).data("type"),
            label: $(this).data("label")
        });
    });
}
$(document).ajaxComplete(() => buildDocumentGallery());
// Keyboard navigation
document.addEventListener("keydown", function (e) {
    const galleryOpen = $("#documentGalleryModal").hasClass("show");
    if (!galleryOpen) return;

    if (e.key === "ArrowRight") $("#nextDocBtn").click();
    if (e.key === "ArrowLeft") $("#prevDocBtn").click();
    if (e.key === "Escape") $("#documentGalleryModal").modal("hide");
});
// Open gallery viewer
$(document).on("click", ".view-document-gallery", function () {
    const file = $(this).data("file");
    currentIndex = docGallery.findIndex(d => d.file === file);
    openGalleryDocument(currentIndex);
});
// Render selected document
function openGalleryDocument(index) {
    if (index < 0 || index >= docGallery.length) return;
    const doc = docGallery[index];
    $("#galleryDocumentTitle").text(doc.label);
    $("#galleryCounter").text(`${index + 1} of ${docGallery.length}`);
    zoomLevel = 1;
    $("#zoomControls").addClass("d-none");
    imageElement = null;
    let html = "";
    if (doc.type === "pdf") {
        html = `<iframe src="${doc.file}" width="100%" height="100%" 
                 style="border:none;background:#fff;"></iframe>`;
    } else {
        html = `<img src="${doc.file}" id="galleryImage" class="img-fluid rounded shadow-sm zoom-image" 
                 style="max-height:82vh; object-fit:contain;">`;
        // enable zoom controls for images
        setTimeout(() => {
            imageElement = document.getElementById("galleryImage");
            $("#zoomControls").removeClass("d-none");
        }, 50);
    }
    $("#galleryViewer").html(html);
    // const modal = new bootstrap.Modal(document.getElementById("documentGalleryModal"));
    // modal.show();
    $("#documentGalleryModal").modal("show");
}
// Navigation
$("#nextDocBtn").click(() => {
    currentIndex = (currentIndex + 1) % docGallery.length;
    openGalleryDocument(currentIndex);
});
$("#prevDocBtn").click(() => {
    currentIndex = (currentIndex - 1 + docGallery.length) % docGallery.length;
    openGalleryDocument(currentIndex);
});
// Zoom controls
$("#zoomInBtn").click(() => adjustZoom(0.1));
$("#zoomOutBtn").click(() => adjustZoom(-0.1));
$("#zoomResetBtn").click(() => {
    zoomLevel = 1;
    applyZoom();
});
function adjustZoom(delta) {
    zoomLevel += delta;
    if (zoomLevel < 0.4) zoomLevel = 0.4;
    if (zoomLevel > 4) zoomLevel = 4;
    applyZoom();
}
function applyZoom() {
    if (imageElement) {
        imageElement.style.transform = `scale(${zoomLevel})`;
    }
}
</script>

<!-- DOCUMENT VIEWER MODAL -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-sm rounded-3 overflow-hidden" style="max-height: 90vh;">
      
      <!-- HEADER -->
      <div class="modal-header bg-white border-bottom py-2 px-3">
        <h6 class="modal-title fw-semibold text-primary mb-0 d-flex align-items-center gap-2">
          <i class="fa fa-file-text"></i> <span id="documentModalLabel">View Document</span>
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body p-0 bg-light" id="documentViewer" style="height: 80vh; display: flex; justify-content: center; align-items: center;">
        <div class="text-center text-muted small">Loading document...</div>
      </div>

      <!-- FOOTER -->
      <div class="modal-footer bg-white py-2 px-3">
        <button type="button" class="btn btn-light btn-sm border" data-bs-dismiss="modal">
          <i class="fa fa-times me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>
<script>
$(document).on('click', '.view-document', function () {
  const file = $(this).data('file');
  const type = $(this).data('type');
  const label = $(this).data('label');

  $('#documentModalLabel').text(label);
  const viewer = $('#documentViewer');

  if (!file) {
    viewer.html('<div class="text-center text-muted small py-5">File not found.</div>');
    new bootstrap.Modal('#documentModal').show();
    return;
  }

  // Choose rendering mode
  let html = '';
  if (type === 'pdf') {
    html = `
      <iframe src="${file}" frameborder="0" width="100%" height="100%" 
              style="border:none; background:#fff;"></iframe>`;
    // html = `
    //     <iframe src="https://docs.google.com/gview?url=${file}&embedded=true" 
    //       frameborder="0" width="100%" height="100%" 
    //       style="border:none; background:#fff;"></iframe>`;

  } else {
    html = `
      <img src="${file}" alt="Document Preview" class="img-fluid rounded shadow-sm"
           style="max-height: 80vh; object-fit: contain;">`;
  }

  //quotation
  $('#versionsModal').modal('hide');

  viewer.html(html);
  new bootstrap.Modal('#documentModal').show();
});
</script>
<script>
$(document).ready(function(){

  const customerID = <?= isset($customer_id) ? $customer_id : 0 ?>;
  const ajaxURL = "public/ajax/customers_documents.php";

  // Load all documents
  function loadDocuments() {
    $.ajax({
      url: ajaxURL,
      type: "POST",
      data: { action: "fetch", customer_id: customerID },
      success: function(res){
        $("#documents-body").html(res);
      },
      error: function(){
        $("#documents-body").html("<tr><td colspan='5' class='text-danger text-center'>Error loading documents.</td></tr>");
      }
    });
  }

  loadDocuments();

  // Upload / Save document
  $("#documentForm").on("submit", function(e){
    e.preventDefault();

    var formData = new FormData(this);
    formData.append("action", "save");

    $.ajax({
      url: ajaxURL,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: function(){
        $("#documentForm button[type=submit]").prop("disabled", true)
          .html('<i class="fa fa-spinner fa-spin"></i> Uploading...');
      },
      success: function(res){
        $("#documentForm button[type=submit]").prop("disabled", false)
          .html('<i class="fa fa-save me-1"></i> Save Document');

        if (res.trim() === "success") {
          $("#addDocumentModal").modal("hide");
          $("#documentForm")[0].reset();
          loadDocuments();
        } else {
          alert("Error: " + res);
        }
      }
    });
  });

  // Delete document
  $(document).on("click", ".delete-document", function(){
    if(!confirm("Delete this document?")) return;
    const id = $(this).data("id");
    $.ajax({
      url: ajaxURL,
      type: "POST",
      data: { action: "delete", id: id, customer_id: customerID },
      success: function(res){
        if(res.trim() === "success") loadDocuments();
        else alert("Error deleting: " + res);
      }
    });
  });

  // Reset modal
  $('#addDocumentModal').on('hidden.bs.modal', function(){
    $("#documentForm")[0].reset();
    $("#doc_id").val('');
    $("#documentModalTitle").text("Add New Document");
  });

});
</script>


<!-- QUOTATION MODAL -->
<div class="modal fade" id="quotationModal" tabindex="-1" aria-labelledby="quotationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xxl modal-dialog-centered" style="max-width: 95vw;">
    <div class="modal-content" style="max-height: 92vh; display: flex; flex-direction: column; border-radius: 10px; overflow: hidden;">

      <!-- HEADER -->
      <div class="modal-header sticky-top bg-white shadow-sm" style="z-index: 1055;">
        <h5 class="modal-title fw-semibold text-primary d-flex align-items-center gap-2">
          <i class="fa fa-file-text"></i>
          <span id="quotationModalTitle">Add Quotation</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- FORM -->
      <form id="quotationForm" class="d-flex flex-column flex-grow-1" style="overflow: hidden;">
        
        <!-- SCROLLABLE BODY -->
        <div class="modal-body flex-grow-1" style="overflow-y: auto; padding-bottom: 100px;">

          <!-- Hidden Inputs -->
          <input type="hidden" name="id" id="quotation_id">
          <input type="hidden" name="customer_id" id="customer_id" value="<?= $customer_id ?? 0 ?>">

          <!-- NAV TABS -->
          <ul class="nav nav-tabs sticky-top bg-white border-bottom" id="quotationTabs" role="tablist" style="top: 0; z-index: 100;">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-basic"><i class="fa fa-info-circle me-1"></i> Basic Info</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-jobs"><i class="fa fa-briefcase me-1"></i> Job Requirements</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-terms"><i class="fa fa-list me-1"></i> Terms & Conditions</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-closing"><i class="fa fa-envelope-open me-1"></i> Closing</a></li>
          </ul>

          <!-- TAB CONTENT -->
          <div class="tab-content pt-3" style="min-height: 65vh; overflow: visible !important;">

            <!-- BASIC INFO -->
            <div class="tab-pane fade show active" id="tab-basic" style="overflow: visible !important;">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label small fw-semibold">Quotation Name</label>
                  <input type="text" name="quotation_name" class="form-control form-control-sm" placeholder="Quotation Name">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-semibold">Date</label>
                  <input type="date" name="quotation_date" id="quotation_date" class="form-control form-control-sm" value="<?=$date?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-semibold">Attention</label>
                  <input type="text" name="attention" class="form-control form-control-sm" placeholder="Attention">
                </div>
                <div class="col-12">
                  <label class="form-label small fw-semibold">Subject</label>
                  <input type="text" name="subject" class="form-control form-control-sm" placeholder="Subject">
                </div>
                <div class="col-12">
                  <label class="form-label small fw-semibold">Message</label>
                  <textarea name="message" class="form-control form-control-sm" rows="4" placeholder="Message"></textarea>
                </div>
              </div>
            </div>

            <!-- JOB REQUIREMENTS -->
            <div class="tab-pane fade" id="tab-jobs" style="overflow: visible !important;">
              <div id="jobRequirements" class="pb-3"></div>
              <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addJobBtn">
                <i class="fa fa-plus"></i> Add Job
              </button>
            </div>

            <!-- TERMS & CONDITIONS -->
            <div class="tab-pane fade" id="tab-terms" style="overflow: visible !important;">
              <div id="termsList" class="pb-3" style="max-height: unset !important;"></div>
              <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="addTermBtn">
                <i class="fa fa-plus"></i> Add Term
              </button>
            </div>

            <!-- CLOSING -->
            <div class="tab-pane fade" id="tab-closing" style="overflow: visible !important;">
              <label class="form-label small fw-semibold">Closing Message</label>
              <textarea name="closing" class="form-control form-control-sm" rows="6" placeholder="Closing Message.."></textarea>
            </div>
          </div>
        </div>

        <!-- STICKY FOOTER BUTTONS -->
        <div class="modal-footer bg-white border-top sticky-bottom py-2 d-flex justify-content-between align-items-center" style="z-index: 1055;">
          <button type="button" class="btn btn-secondary btn-sm prev-tab-btn d-none">
            <i class="fa fa-arrow-left me-1"></i> Back
          </button>
          <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm next-tab-btn">
              Next <i class="fa fa-arrow-right ms-1"></i>
            </button>
            <button type="submit" class="btn btn-success btn-sm d-none" id="saveQuotationBtn">
              <i class="fa fa-save me-1"></i> Save Quotation
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- VIEW VERSIONS MODAL -->
<div class="modal fade" id="versionsModal" tabindex="-1" aria-labelledby="versionsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm rounded-3">

      <!-- HEADER -->
      <div class="modal-header bg-white border-bottom d-flex align-items-center justify-content-between py-2 px-3">
        <h6 class="modal-title fw-semibold text-primary mb-0 d-flex align-items-center gap-2" id="versionsModalLabel">
          <i class="fa fa-history"></i> Quotation Versions
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body px-3 pb-2 pt-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light small text-secondary">
              <tr>
                <th class="fw-semibold">Version</th>
                <th class="fw-semibold">Quotation Name</th>
                <th class="fw-semibold">Date</th>
                <th class="fw-semibold">Created By</th>
                <th class="text-end fw-semibold">Actions</th>
              </tr>
            </thead>
            <tbody id="versionsTableBody">
              <tr>
                <td colspan="5" class="text-center text-muted py-3 small">
                  <i class="fa fa-spinner fa-spin text-primary me-2"></i> Loading versions...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- FOOTER -->
      <div class="modal-footer bg-white border-top py-2 px-3">
        <button type="button" class="btn btn-light btn-sm border" data-bs-dismiss="modal">
          <i class="fa fa-times me-1"></i> Close
        </button>
      </div>

    </div>
  </div>
</div>

<!-- Clean Styling -->
<style>
  #versionsModal .modal-content {
    animation: fadeInScale 0.25s ease-in-out;
  }

  #versionsModal .table {
    margin-bottom: 0;
  }

  #versionsModal .table-hover tbody tr:hover {
    background-color: #f8faff;
  }

  #versionsModal .badge {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: capitalize;
  }

  #versionsModal .btn-sm {
    padding: 0.25rem 0.4rem;
  }

  @keyframes fadeInScale {
    from { transform: scale(0.97); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
  }
</style>

<script>
$(function() {
  const $tabs = $('#quotationTabs a');
  const $nextBtn = $('.next-tab-btn');
  const $prevBtn = $('.prev-tab-btn');
  const $saveBtn = $('#saveQuotationBtn');
  const $modalBody = $('#quotationModal .modal-body');

  function updateButtons() {
    const activeIndex = $tabs.index($('#quotationTabs a.active'));
    const lastIndex = $tabs.length - 1;

    $prevBtn.toggleClass('d-none', activeIndex === 0);
    $nextBtn.toggleClass('d-none', activeIndex === lastIndex);
    $saveBtn.toggleClass('d-none', activeIndex !== lastIndex);

    // Smooth scroll to top for each tab
    $modalBody.animate({ scrollTop: 0 }, 200);
  }

  // Always show first tab on modal open
  $('#quotationModal').on('shown.bs.modal', function() {
    $('#quotationTabs a:first').tab('show');
    updateButtons();
  });

  // Change button visibility when switching tabs
  $tabs.on('shown.bs.tab', updateButtons);

  // Navigation logic
  $nextBtn.on('click', function() {
    const activeIndex = $tabs.index($('#quotationTabs a.active'));
    if (activeIndex < $tabs.length - 1) $tabs.eq(activeIndex + 1).tab('show');
  });

  $prevBtn.on('click', function() {
    const activeIndex = $tabs.index($('#quotationTabs a.active'));
    if (activeIndex > 0) $tabs.eq(activeIndex - 1).tab('show');
  });
});
</script>

<script>
/* ---------- JOB & TERM TEMPLATE FUNCTIONS ---------- */
function jobRowTemplate(index = 1, job = {}) {
  var startDate = job.start_date || "<?=date("Y-m-d",strtotime("next sunday",strtotime($date)))?>";
  return `
  <div class="card border shadow-sm p-3 mb-3 job-item rounded-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="fw-semibold mb-0 text-secondary">Job ${index}</h6>
      <button type="button" class="btn btn-sm btn-outline-danger remove-job">
        <i class="fa fa-trash"></i>
      </button>
    </div>
    <div class="row g-3 align-items-center">
      <div class="col-md-12">
        <label class="form-label small fw-semibold">Job Title</label>
        <select name="job_title[]" class="form-select form-select-sm jselect" id="jobselect${index}" required>
          ${jobTitles.map(opt => `<option value="${opt.title}" ${job.job_title === opt ? 'selected' : ''}>${opt.title}</option>`).join('')}
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Rate (AED)</label>
        <input type="number" step="0.01" name="rate_pay[]" value="${job.rate_pay || ''}" 
               class="form-control form-control-sm" placeholder="Hourly Rate" required>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Number of Employees</label>
        <div class="input-group input-group-sm">
          <button type="button" class="btn btn-outline-secondary emp-minus">-</button>
          <input type="number" name="num_employees[]" class="form-control form-control-sm text-center" min="1" value="${job.num_employees || '1'}" required>
          <button type="button" class="btn btn-outline-secondary emp-plus">+</button>
        </div>


      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Start Date</label>
        <input type="date" name="start_date[]" value="${startDate}" 
               class="form-control form-control-sm">
      </div>
    </div>
  </div>`;
}

function termTemplate(title = 'New Term', text = '') {
  return `
  <div class="card border p-3 mb-3 term-item rounded-3 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <input type="text" name="term_title[]" value="${title}" class="form-control form-control-sm me-2" placeholder="Term Title" style="max-width: 300px;">
      <button type="button" class="btn btn-sm btn-outline-danger remove-term"><i class="fa fa-trash"></i></button>
    </div>
    <textarea name="term_text[]" rows="2" class="form-control form-control-sm" placeholder="Term description...">${text}</textarea>
  </div>`;
}

/* ---------- MAIN SCRIPT ---------- */
$(function() {
  const ajaxURL = "public/ajax/customers_quotations.php";
  const customerId = <?= $customer_id ?? 0 ?>;

    /* --- LOAD QUOTATIONS --- */
    function loadQuotations() {
      $.ajax({
        url: ajaxURL,
        type: "POST",
        data: { action: "list", customer_id: customerId },
        dataType: "json",
        success: function(res) {
          if (!res || !res.latest) {
            $('#latestQuotation').html(`
              <div class="alert alert-light border text-center small mb-0">
                No quotations found for this customer.
              </div>
            `);
            $('#quotationList').html(`
              <tr><td colspan="6" class="text-center text-muted small">No older quotations.</td></tr>
            `);
            return;
          }

          /* ---------- LATEST QUOTATION ---------- */
          const q = res.latest;
          const versionBtn = q.has_versions
            ? `<button class="btn btn-outline-primary btn-sm view-versions" data-ref="${q.ref_no}">
                 <i class="fa fa-history"></i> ${q.version_count} Versions
               </button>`
            : '';

          $('#latestQuotation').html(`
            <div class="bg-light border border-0 rounded-3">
              <h5 class="fw-semibold text-primary mb-1">Latest Quotation ${q.quotation_name} (${q.version})</h5>
              <p class="text-secondary small mb-3">
                Ref: <strong>${q.ref_no}</strong> | Date: ${q.quotation_date}
              </p>
              <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-secondary btn-sm edit-quotation" data-id="${q.id}">
                  <i class="fa fa-pen"></i> Edit & Create New Version
                </button>
                ${versionBtn}
                <button type="button" data-file="./public/quote_pdf.php?id=${q.id}" data-type="pdf" data-label="${q.quotation_name} (${q.version})" class="btn btn-outline-secondary btn-sm view-document">
                  <i class="fa fa-file-pdf text-danger"></i> View PDF
                </button>
              </div>
            </div>
          `);

          /* ---------- OLDER QUOTATIONS ---------- */
          let rows = '';
          if (res.others?.length) {
            res.others.forEach(r => {
              const versionBadge = r.has_versions
                ? `<span class="badge bg-primary">${r.version_count} Versions</span>`
                : '';
              rows += `
                <tr>
                  <td>${r.ref_no} (${r.version}) ${versionBadge}</td>
                  <td>${r.quotation_name}</td>
                  <td>${r.quotation_date}</td>
                  <td>${r.created_by}</td>
                  <td class="text-end">
                    <div class="btn-group btn-group-sm">
                      ${r.has_versions ? `
                        <button class="btn btn-light border view-versions" data-ref="${r.ref_no}">
                          <i class="fa fa-history text-primary"></i>
                        </button>` : ''}
                        <button class="btn btn-light border edit-quotation" data-id="${r.id}">
                          <i class="fa fa-pen"></i>
                        </button>
                        <button type="button" data-file="./public/quote_pdf.php?id=${r.id}" data-type="pdf" data-label="${r.quotation_name} (${r.version})" class="btn btn-light border view-document">
                          <i class="fa fa-file-pdf text-danger"></i>
                        </button>
                    </div>
                  </td>
                </tr>`;
            });
          } else {
            rows = `<tr><td colspan="6" class="text-center text-muted small">No older quotations.</td></tr>`;
          }

          $('#quotationList').html(rows);
        },
        error: () => {
          $('#latestQuotation').html(`
            <div class="alert alert-danger small text-center mb-0">
              Error loading quotations. Please refresh.
            </div>
          `);
          $('#quotationList').html(`
            <tr><td colspan="6" class="text-center text-danger small">Error loading quotations.</td></tr>
          `);
        }
      });
    }

  loadQuotations();

    /* --- VIEW VERSIONS --- */
    $(document).on('click', '.view-versions', function() {
      const refNo = $(this).data('ref');
      $('#versionsModalLabel').html(`<i class="fa fa-history"></i> Quotation Versions — <span class="fw-normal">${refNo}</span>`);
      $('#versionsTableBody').html(`
        <tr>
          <td colspan="5" class="text-center text-muted small py-3">
            <i class="fa fa-spinner fa-spin text-primary me-2"></i> Loading versions...
          </td>
        </tr>
      `);
      $('#versionsModal').modal('show');

      $.post(ajaxURL, { action: 'get_versions', ref_no: refNo, customer_id: customerId }, function(res) {
        try {
          const data = JSON.parse(res);
          if (!data.versions?.length) {
            $('#versionsTableBody').html(`
              <tr><td colspan="6" class="text-center text-muted small py-3">No versions found.</td></tr>
            `);
            return;
          }

          let rows = '';
          data.versions.forEach(v => {
            const statusBadge = v.status === 'Final'
              ? `<span class="badge bg-success">${v.status}</span>`
              : `<span class="badge bg-warning text-dark">${v.status}</span>`;
            rows += `
              <tr>
                <td><span class="fw-semibold">${v.version}</span></td>
                <td>${v.quotation_name}</td>
                <td>${v.quotation_date}</td>
                <td>${v.created_by}</td>
                <td class="text-end">
                    <button class="btn btn-light btn-sm d-inline-flex border edit-quotation" data-id="${v.id}">
                        <i class="fa fa-pen"></i>
                      </button>
                    <button type="button" data-file="./public/quote_pdf.php?id=${v.id}" data-type="pdf" data-label="${v.quotation_name} (${v.version})" class="btn btn-light btn-sm border d-inline-flex align-items-center gap-1 view-document">
                      <i class="fa fa-file-pdf text-danger"></i>
                    </button>
                </td>
              </tr>`;
          });

          $('#versionsTableBody').html(rows);
        } catch (err) {
          console.error('Error parsing versions:', res);
          $('#versionsTableBody').html(`
            <tr><td colspan="5" class="text-center text-danger small py-3">Error loading versions.</td></tr>
          `);
        }
      }).fail(() => {
        $('#versionsTableBody').html(`
          <tr><td colspan="5" class="text-center text-danger small py-3">Server error loading versions.</td></tr>
        `);
      });
    });



  /* --- ADD NEW QUOTATION --- */
  $('#addQuotationBtn, #quickCreateQuotationBtn').on('click', function() {
    $('#quotationForm')[0].reset();
    $('#quotation_id').val('');
    $('#jobRequirements, #termsList').empty();
    $('#quotationModalTitle').text('Add New Quotation');
    $('#quotationModal').modal('show');

    $.post("public/ajax/customers_quotation_editor.php", { action: 'load_editor', customer_id: customerId }, function(res) {
      try {
        const data = JSON.parse(res);
        $('#jobRequirements').html(data.jobs_html || '');
        setTimeout(() => {
          for (let i = 1; i <= data.jobs_no; i++) {
            refreshJSelect("jobselect" + i);
          }
        }, 10);
        $('#termsList').html(data.terms_html || '');
      } catch (e) { console.error('Invalid JSON:', res); }
    });
  });

  /* --- EDIT EXISTING --- */
  $(document).on('click', '.edit-quotation', function() {
    const id = $(this).data('id');
    $('#versionsModal').modal('hide');
    $.post(ajaxURL, { action: 'get', id }, function(res) {
      try {
        const q = JSON.parse(res);
        for (const key in q) $(`[name="${key}"]`).val(q[key]);
        $('#jobRequirements').html(q.jobs_html || '');
        setTimeout(() => {
          for (let i = 1; i <= q.jobs_no; i++) {
            refreshJSelect("jobselect" + i);
          }
        }, 10);
        $('#termsList').html(q.terms_html || '');
        $('#quotation_id').val(q.id);
        $('#quotation_date').val('<?=date("Y-m-d",strtotime($date))?>');
        $('#quotationModalTitle').text('Edit Quotation -'+'Ref #'+q['ref_no']);
        $('#quotationModal').modal('show');
      } catch (err) {
        alert('Failed to load quotation data.');
      }
    });
  });

  /* --- ADD JOB / TERM --- */
  $('#addJobBtn').click(() => { 
    var jsi = $('#jobRequirements .job-item').length + 1;
    $('#jobRequirements').append(jobRowTemplate(jsi)); 
    refreshJSelect("jobselect"+jsi); 
  });
  $('#addTermBtn').click(() => $('#termsList').append(termTemplate()));

  $(document).on('click', '.remove-job', e => $(e.target).closest('.job-item').remove());
  $(document).on('click', '.remove-term', e => $(e.target).closest('.term-item').remove());

  /* --- SAVE --- */
  $('#quotationForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize() + '&action=save';
    $.ajax({
      url: ajaxURL,
      type: 'POST',
      data: formData,
      beforeSend: () => {
        $('#quotationForm button[type=submit]').html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
      },
      success: function(res) {
        $('#quotationForm button[type=submit]').html('<i class="fa fa-save me-1"></i> Save Quotation').prop('disabled', false);
        if (res.trim() === 'success') {
          $('#quotationModal').modal('hide');
          loadQuotations();
        } else alert("Error: " + res);
      },
      error: () => alert('Error saving quotation.')
    });
  });

  $('#quotationModal').on('hidden.bs.modal', () => {
    $('#quotationForm')[0].reset();
    $('#jobRequirements, #termsList').empty();
  });
});
</script>


<!-- RECENT ACTIONS -->
<script>
$(function() {
  const customerId = <?= $customer_id ?? 0 ?>;
  const logURL = "public/ajax/customers_recent_actions.php";
  const $logBox = $('#customerLogs');

  // 🔹 Convert JSON data to HTML (list format)
  function renderLogs(logs) {
    if (!logs || !logs.length) {
      return `<div class="text-muted small text-center py-3">No recent activity found for this customer.</div>`;
    }

    let html = `<div class="list-group list-group-flush">`;
    logs.forEach((item) => {
      html += `
        <div class="list-group-item border-bottom-1 px-0 py-2">
          <div class="d-flex align-items-start">
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0 small">${item.by}</h6>
                <small class="text-muted small">${item.date}</small>
              </div>
              <p class="text-secondary small mb-0">${item.action}</p>
            </div>
          </div>
        </div>`;
    });
    html += `</div>`;
    return html;
  }

  // 🔹 AJAX Loader
  function loadLogs() {
    if (!customerId) {
      $logBox.html(`<div class="text-muted small text-center py-3">No customer selected.</div>`);
      return;
    }

    $logBox.html(`
      <div class="text-center text-muted small py-3">
        <i class="fa fa-spinner fa-spin me-2"></i> Loading recent actions...
      </div>
    `);

    $.ajax({
      url: logURL,
      type: "POST",
      dataType: "json",
      data: { customer_id: customerId, start: 0, length: 10, draw: 1 },
      success: function(res) {
        if (!res.data || !Array.isArray(res.data) || res.data.length === 0) {
          $logBox.html(`<div class="text-muted small text-center py-3">No recent actions found for this customer.</div>`);
          return;
        }
        $logBox.html(renderLogs(res.data));
      },
      error: function() {
        $logBox.html(`<div class="text-center text-danger small py-3">Error loading logs.</div>`);
      }
    });
  }

  // 🔹 Refresh button event
  $('#refreshLogs').on('click', loadLogs);

  // 🔹 Initial load
  loadLogs();
});
</script>

<script>
$(function () {
  const modal = new bootstrap.Modal('#invoiceModal');
  const ajaxURL = "public/ajax/customers_invoices.php";
  const customerId = <?= $customer_id ?? 0 ?>;

    $('#idaterange').daterangepicker({
        locale: {
            format: 'DD-MMM-YYYY',
            separator: ' - ' // define the separator you prefer
        },
        startDate: "<?=date("d-M-Y",strtotime($date." - 6 days"))?>",
        endDate: "<?=date("d-M-Y",strtotime($date))?>",
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, function(start, end) {
      loadInvoices(start.format("YYYY-MM-DD"), end.format("YYYY-MM-DD"));
    });

    function calInvoiceSum() {
      $("#invoice_sum").attr("style","display: none !important;");
      var iamt = $("#invoice_amount").val();
      var ivamt = $("#vat_amount").val();
      if(parseFloat(iamt)>0 && parseFloat(ivamt)>0) {
        var isum = (parseFloat(iamt)+parseFloat(ivamt)).toFixed(2);
        if(isum>0) {
          $("#invoice_sum").html('Total: '+isum);
          $("#invoice_sum").show();
        }
      }
    }

    // $("#invoice_amount, #vat_amount").on("keyup,input",function() {
    //   calInvoiceSum();
    // });

    /* --------------------------
      LOAD INVOICES
    --------------------------- */
    function loadInvoices(startDate="",endDate="") {
        if(startDate==""||endDate=="") {
            const daterange = $("#idaterange").val() || "";
            const [startDate, endDate] = daterange.split(" - ");
        }
        if(startDate==""&&endDate=="") {
            // $("#clearInvoiceFilter").hide();
        }
        else {
            $("#clearInvoiceFilter").show();
        }
        $.post(ajaxURL, {
            action: "fetch",
            customer_id: customerId,
            start_date: startDate,
            end_date: endDate
        }, function (res) {
            if (!res.success || !res.data.length) {
                $("#totalReceived").html(`<?= $currency_symbol ?? '' ?>0.00<br><span class="small">Received</span>`);
                $("#totalSent").html(`<?= $currency_symbol ?? '' ?>0.00<br><span class="small">Sent</span>`);
                $("#invoice-list").html('<p class="text-muted text-center small">No invoices found.</p>');
                return;
            }
            let html = "";
            let totalReceived = 0;
            let totalSent = 0;
            res.data.forEach(inv => {
                /* SUMMARY CALCULATION */
                if (inv.type === "Received") {
                    totalReceived += parseFloat(inv.invoice_amount || 0);
                    totalReceived += parseFloat(inv.vat_amount || 0);
                } else {
                    totalSent += parseFloat(inv.invoice_amount || 0);
                    totalSent += parseFloat(inv.vat_amount || 0);
                }
                var itotal = (parseFloat(inv.invoice_amount)+parseFloat(inv.vat_amount)).toFixed(2);
                /* TYPE BADGE */
                const typeBadge =
                  inv.type === "Received"
                    ? `<span class="badge bg-primary-subtle border border-primary text-primary rounded-pill">${inv.type}</span>`
                    : `<span class="badge bg-warning-subtle border border-warning text-warning rounded-pill">${inv.type}</span>`;
                /* CATEGORY BADGE */
                const categoryBadge = `
                  <span class="badge bg-success-subtle border border-success text-success rounded-pill">
                    ${inv.category || "Uncategorized"}
                  </span>`;
                /* DOCUMENT BUTTON */
                const docBtn = inv.document
                  ? `<button class='btn btn-light border view-document'
                             data-file='uploads/customers/invoices/${inv.document}'
                             data-type='${inv.file_type}' 
                             data-label='Invoice'>
                        <i class="fa fa-eye"></i>
                     </button>`
                  : ``;
                
                var amount = parseFloat(inv.invoice_amount || 0);
                var vat    = parseFloat(inv.vat_amount || 0);
                var itotal = (amount + vat).toFixed(2);

                var amountHtml = vat > 0
                  ? `(${amount.toFixed(2)} + VAT ${vat.toFixed(2)})`
                  : ``;

                html += `
                <div class="card border-0 shadow-sm rounded-3 mb-1 invoice-row">
                  <div class="card-body py-2 px-3">

                    <!-- LINE 1 -->
                    <div class="d-flex flex-wrap align-items-center gap-2 invoice-line-1">

                      <!-- TOTAL -->
                      <div class="fw-bold text-primary">
                        <?= $currency_symbol ?? '' ?>${itotal}
                      </div>

                      <!-- BREAKUP -->
                      <div class="text-muted small">
                        ${amountHtml}
                      </div>

                      <!-- SEPARATOR -->
                      <span class="text-muted small">•</span>

                      <!-- TYPE + CATEGORY -->
                      ${typeBadge}
                      ${categoryBadge}

                      <!-- SEPARATOR -->
                      <span class="text-muted small">•</span>

                      <!-- DATES -->
                      <div class="text-muted small d-flex align-items-center gap-2 invoice-dates">
                        <span>
                          <strong class="text-secondary">Inv</strong>:
                          <i class="fa fa-calendar ms-1 me-1"></i>${inv.invoice_dated}
                        </span>
                        <span class="date-sep">→</span>
                        <span>
                          <strong class="text-secondary">Due</strong>:
                          <i class="fa fa-calendar-check ms-1 me-1"></i>${inv.due_dated}
                        </span>
                      </div>

                      <!-- ACTIONS -->
                      <div class="ms-auto btn-group btn-group-sm">
                        ${docBtn}
                        <button class="btn btn-light border edit-invoice" data-id="${inv.id}" title="Edit">
                          <i class="fa fa-pen"></i>
                        </button>
                        <button class="btn btn-light border text-danger delete-invoice" data-id="${inv.id}" title="Delete">
                          <i class="fa fa-trash"></i>
                        </button>
                      </div>

                    </div>

                    <!-- LINE 2 -->
                    <div class="d-flex align-items-center text-muted small mt-1 invoice-line-2">
                      ${
                        inv.notes
                          ? `
                            <i class="fa fa-sticky-note me-1"></i>
                            <span class="invoice-note"
                                  title="${inv.notes.replace(/"/g,'&quot;')}">
                              ${inv.notes}
                            </span>
                          `
                          : `<em>No notes</em>`
                      }
                    </div>

                  </div>
                </div>`;

            });
            $("#invoice-list").html(html);
            /* ===============================
               UPDATE SUMMARY BOX
            ================================ */
            $("#totalReceived").html(`<?= $currency_symbol ?? '' ?>${totalReceived.toFixed(2)}<br><span class="small">Received</span>`);
            $("#totalSent").html(`<?= $currency_symbol ?? '' ?>${totalSent.toFixed(2)}<br><span class="smal">Sent</span>`);
        }, "json");
    }

    loadInvoices();

    $("#clearInvoiceFilter").on("click", function () {
        $('#idaterange').val('');
        $("#clearInvoiceFilter").hide();
        loadInvoices();
    });


  /* --------------------------
      DATE BUTTON HANDLING
  --------------------------- */
  $(document).on("click", ".date-btn", function () {
    $(".date-btn").removeClass("btn-primary text-white").addClass("btn-outline-primary");
    $(this).removeClass("btn-outline-primary").addClass("btn-primary text-white");

    const value = $(this).data("value");

    if (value === "other") {
      $("#invoice_date_other").removeClass("d-none");
      $("#invoice_date").val("");
    } else {
      $("#invoice_date_other").addClass("d-none");
      $("#invoice_date").val(value);
    }
  });

  $("#invoice_date_other").on("change", function () {
    $("#invoice_date").val($(this).val());
  });


  /* --------------------------
        LOAD CATEGORY BY TYPE
  --------------------------- */
  function loadCategories(type, selected = null, cb = null) {
    $.post("public/ajax/invoices_categories1.php",
      { action: "fetch", type: type },
      function (res) {
          const $cat = $("#invoice_category");
          // $cat.empty();
          // if (res.success && res.data.length) {
          //     res.data.forEach(c =>
          //         $cat.append(`<option value="${c.category}" ${selected === c.category ? "selected" : ""}>${c.category}</option>`)
          //     );
          // } else {
          //     $cat.append('<option value="">No categories found</option>');
          // }
            $cat.empty();
          if(selected!=null&&selected!='') {
              $cat.append(`<option value="${selected}" selected">${selected}</option>`);
          }
          $("#invoice_category").attr("name", "invoice_category").trigger("change");
          refreshJSelect("invoice_category");

          if (typeof cb === "function") cb();
      }, "json");
  }

  $("#invoice_type").on("change", function () {
    loadCategories($(this).val(),$("#invoice_category").val());
  });


  /* --------------------------
      ADD INVOICE
  --------------------------- */
  $("#addInvoiceBtn").on("click", function () {
    $("#invoiceForm")[0].reset();
    $("#invoice_id").val('');
    $("#documentPreview").empty();

    $("#due_date").val("<?=date("Y-m-d",strtotime("+7 days"))?>");

    // Select "Today" button by default
    $(".date-btn[data-value='<?=date('Y-m-d')?>']")
      .trigger("click");

    loadCategories("Received");

    $("#invoiceModalTitle").text("Add Invoice");
    modal.show();
  });


  /* --------------------------
      SAVE INVOICE
  --------------------------- */
  $("#invoiceForm").on("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append("action", "save");
    // alert($("#invoice_type").val());

    $.ajax({
      url: ajaxURL,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (res) {
        if (res.success) {
          modal.hide();
          loadInvoices();
        } else {
          alert("Failed to save invoice.");
        }
      },
      error: () => alert("Error saving invoice.")
    });
  });

  /* --------------------------
        EDIT INVOICE
  --------------------------- */
  $(document).on("click", ".edit-invoice", function () {
      const id = $(this).data("id");

      $.post(ajaxURL, { action: "fetch", customer_id: customerId }, function (res) {
          const inv = res.data.find(r => r.id == id);
          if (!inv) return;

          $("#invoice_id").val(inv.id);
          $("#invoice_amount").val(inv.invoice_amount);
          $("#vat_amount").val(inv.vat_amount);
          $("#notesi").val(inv.notes || "");

          /* --------------------
              LOAD TYPE FIRST
          --------------------- */
          $("#invoice_type").val(inv.type);

          $("#due_date").val(inv.due_date || "<?=date("Y-m-d",strtotime("+7 days"))?>");

          /* --------------------
              LOAD CATEGORIES WITH CALLBACK
          --------------------- */
          loadCategories(inv.type, inv.category, function () {

              // Force-select category AFTER jSelect is rebuilt
              $("#invoice_category").val(inv.category);

              // Update the hidden input created by jSelect
              const wrap = $("#invoice_category").closest(".jselect-wrapper");
              const hidden = wrap.find("input[type=hidden]");
              if (hidden.length) hidden.val(inv.category);

              $("#invoice_category").trigger("change");

              // Category set — now refresh jSelect for TYPE also
              // setTimeout(() => {
                  // $("#invoice_type").trigger("change");   // highlight type button
              // }, 20); // small delay ensures jSelect is ready
              $("#invoice_type").attr("name", "invoice_type");
              refreshJSelect("invoice_type");
          });

          /* --------------------
              DATE SETTING
          --------------------- */

          $(".date-btn").removeClass("btn-primary text-white").addClass("btn-outline-primary");

          const today = "<?=date('Y-m-d')?>";
          const yesterday = "<?=date('Y-m-d', strtotime('-1 day'))?>";
          const invDate = inv.invoice_date;

          if (invDate === today) {
              $(".date-btn[data-value='" + today + "']")
                  .removeClass("btn-outline-primary")
                  .addClass("btn-primary text-white");
              $("#invoice_date_other").addClass("d-none");
              $("#invoice_date").val(today);
          }
          else if (invDate === yesterday) {
              $(".date-btn[data-value='" + yesterday + "']")
                  .removeClass("btn-outline-primary")
                  .addClass("btn-primary text-white");
              $("#invoice_date_other").addClass("d-none");
              $("#invoice_date").val(yesterday);
          }
          else {
              $(".date-btn[data-value='other']")
                  .removeClass("btn-outline-primary")
                  .addClass("btn-primary text-white");

              $("#invoice_date_other").removeClass("d-none");
              $("#invoice_date_other").val(invDate);  // correct date field
              $("#invoice_date").val(invDate);
          }

          /* --------------------
              TITLE + OPEN MODAL
          --------------------- */
          $("#invoiceModalTitle").text("Edit Invoice");
          modal.show();

          /* --------------------
              DOCUMENT PREVIEW
          --------------------- */
          if (inv.document) {
              $("#documentPreview").html(``);
              // $("#documentPreview").html(`
              //     <a href="uploads/customers/invoices/${inv.document}" target="_blank" class="btn btn-sm btn-outline-primary">
              //         <i class="fa fa-eye"></i> View Current
              //     </a>
              // `);
          } else {
              $("#documentPreview").empty();
          }

      }, "json");
  });



  /* --------------------------
      DELETE
  --------------------------- */
  $(document).on("click", ".delete-invoice", function () {
    const id = $(this).data("id");
    if (!confirm("Delete this invoice?")) return;

    $.post(ajaxURL, { action: "delete", id: id }, function (res) {
      if (res.success) loadInvoices();
      else alert("Failed to delete invoice.");
    }, "json");
  });

  // Auto-notes generator for NEW invoices only
  window.generateAutoNotesinvoice = function () {

    calInvoiceSum();

      const isNew = ($("#invoice_id").val().trim() === ""); 
      if (!isNew) return; // don't overwrite when editing

      const amount   = parseFloat($("#invoice_amount").val() || 0);
      const category = $("#invoice_category").val();

      if (!amount) return; // || !category

      // Simple neutral note (not paid / received)
      var catn = category?` - ${category}`:``;
      const text = `Invoice for AED ${amount}${catn}.`;

      $("#notesi").val(text);
  };

  // Trigger auto generation
  $("#invoice_type, #invoice_amount, #vat_amount, #invoice_category")
      .on("change keyup", function(e) {
        // If invoice_amount changed → calculate VAT (5%)
        if (this.id === "invoice_amount") {
          let amount = parseFloat($("#invoice_amount").val()) || 0;
          let vat = (amount * 5) / 100;
          $("#vat_amount").val(vat.toFixed(2));
        }
        generateAutoNotesinvoice();
      });
});
</script>


<style>
    .btn-outline-warning {
        color: orange !important;
        border-color: orange !important;
    }
</style>
<script>
$(function() {
  const modal = new bootstrap.Modal('#paymentModal');
  const ajaxURL = "public/ajax/customers_payments.php";
  const customerId = <?= $customer_id ?? 0 ?>;

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el){ return new bootstrap.Tooltip(el); });


  // store existing documents when editing (array of {name, url, type})
  let existingDocs = [];

  // helper: render preview for selected FileList
  function renderSelectedPreviews(files) {
      const $preview = $("#documentPreviewp").empty();
      if (!files || files.length === 0) return;

      Array.from(files).forEach((file, index) => {
        const fileName = file.name;
        const fileType = file.type || '';
        const $item = $('<div class="border rounded p-2 bg-white small text-truncate" style="width:160px;"></div>');

        let fileHTML = '';
        if (fileType.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = function(e) {
            $item.html(`
              <img src="${e.target.result}" style="max-width:120px; max-height:90px; display:block; margin-bottom:6px; object-fit:contain;">
              <input type="text" name="document_name[]" class="form-control form-control-sm mt-1" placeholder="Document Name" data-index="${index}">
              <div class="small text-muted text-truncate">${fileName}</div>
            `);
          };
          reader.readAsDataURL(file);
        } else {
          fileHTML = `
            <div class="d-flex align-items-center gap-2" style="height:90px; justify-content:center; flex-direction:column;">
              <i class="fa fa-file-pdf fa-2x text-secondary"></i>
              <div class="text-truncate">${fileName}</div>
            </div>
            <input type="text" name="document_name[]" class="form-control form-control-sm mt-1" placeholder="Document Name" data-index="${index}">
          `;
          $item.html(fileHTML);
        }

        $preview.append($item);
      });
    }

  // helper: render existing docs (when editing). existingDocs should be array of {name,url,type,id(optional)}
  function renderExistingDocs() {
    const $box = $("#existingDocuments").empty();
    if (!existingDocs || !existingDocs.length) return;
    existingDocs.forEach(doc => {
      // doc.url expected to be full path to file, doc.name is filename, doc.type optional (image/pdf)
      const $el = $(`
        <div class="d-flex align-items-center gap-2 bg-white border rounded p-2 mb-1" style="max-width:100%;">
          <div class="me-2" style="width:56px; flex:0 0 56px;">
            ${ (doc.type && doc.type.startsWith('image')) ? `<img src="${doc.url}" style="max-width:56px; max-height:40px; object-fit:contain;">`
               : `<i class="fa fa-file fa-2x text-muted"></i>` }
          </div>
          <div class="flex-grow-1 small text-truncate">${doc.label}</div>
          <div class="btn-group btn-group-sm ms-2">
            <button data-file="${doc.url}" data-type="${ (doc.type && doc.type.startsWith('image'))?'image':'pdf'}" data-label="Payment" class="btn btn-light border view-document"><i class="fa fa-eye"></i></button>
            <button type="button" class="btn btn-light border text-danger remove-existing-doc" data-name="${doc.name}"><i class="fa fa-trash"></i></button>
          </div>
        </div>
      `);
      $box.append($el);
    });
  }

    // 🟢 Load payments with labeled multi-document support
    function loadPayments() {
        const daterange = $("#p_daterange").val() || "";
        const [startDate, endDate] = daterange.includes(" - ")
            ? daterange.split(" - ")
            : ["", ""];
        if(startDate!=""&&endDate!="") {
            $("#clearPaymentFilter").show();
        }
        $.post(ajaxURL, {
            action: "fetch",
            customer_id: customerId,
            start_date: startDate,
            end_date: endDate
        }, function (res) {
            if (!res.success || !res.data.length) {
                $("#payment-summary").html("");
                $("#payment-list").html('<p class="text-muted text-center small">No Payment records found.</p>');
                return;
            }
            let html = "";
            let totalIncome = 0;
            let totalExpense = 0;
            let paidAmount = 0;
            let unpaidAmount = 0;
            let partialAmount = 0;
            res.data.forEach((inv) => {
                // 🧮 SUMMARY COUNT
                const amount = parseFloat(inv.invoice_amount || 0);
                if (inv.type === "Income") {
                    totalIncome += amount;
                } else {
                    totalExpense += amount;
                }
                if (inv.payment_status === "Paid") {
                    paidAmount += amount;
                }
                else if (inv.payment_status === "Unpaid") {
                    unpaidAmount += amount;
                }
                else if (inv.payment_status === "Partial Paid") {
                    partialAmount += amount;
                }

          // 🧾 Handle structured / legacy document formats
          let docsArr = [];
          if (Array.isArray(inv.documents)) {
            docsArr = inv.documents;
          } else if (typeof inv.documents === "string" && inv.documents.trim() !== "") {
            try {
              docsArr = JSON.parse(inv.documents);
              if (!Array.isArray(docsArr)) docsArr = [inv.documents];
            } catch (e) {
              docsArr = inv.documents.split(",").map((s) => s.trim()).filter(Boolean);
            }
          } else if (inv.document) {
            docsArr = [inv.document];
          }

          // 🗂️ Document buttons (now supports labels)
          const docBtn = docsArr.length
            ? docsArr
                .map((d) => {
                  let file, url, type, name;
                  if (typeof d === "object") {
                    file = d.file;
                    url = d.url || `uploads/customers/payments/${file}`;
                    type = d.type || (file.toLowerCase().endsWith(".pdf") ? "pdf" : "image");
                    name = d.name || "";
                  } else {
                    file = d;
                    url = `uploads/customers/payments/${file}`;
                    type = file.toLowerCase().endsWith(".pdf") ? "pdf" : "image";
                    name = "";
                  }

                  const icon =
                    type === "pdf"
                      ? '<i class="fa fa-file-pdf text-danger"></i>'
                      : '<i class="fa fa-image text-primary"></i>';

                  // Tooltip shows both file + name
                  const title = name
                    ? `${name} (${file})`
                    : `View ${file}`;

                  return `
                    <button class='btn btn-light border view-document' 
                            data-file='${url}' 
                            data-type='${type}' 
                            data-label='${name || "Payment Attachment"}'
                            title='${title}'>
                      ${icon}
                    </button>`;
                })
                .join(" ")
            : "<!--span class='text-muted small'><i class='fa fa-ban me-1'></i>No Attachments</span-->";

          // 💰 Status badge
          let statusBtnClass = "btn-outline-danger";
          let statusIcon = '<i class="fa fa-times-circle me-1"></i>';
          if (inv.payment_status === "Paid") {
            statusBtnClass = "btn-outline-success";
            statusIcon = '<i class="fa fa-check-circle me-1"></i>';
          } else if (inv.payment_status === "Partial Paid") {
            statusBtnClass = "btn-outline-warning";
            statusIcon = '<i class="fa fa-adjust me-1"></i>';
          } else if (inv.payment_status === "Pending") {
            statusBtnClass = "btn-outline-secondary";
            statusIcon = '<i class="fa fa-clock me-1"></i>';
          }

          // 📋 Category & Type badges
          const typeBadge = `<span class="badge bg-primary-subtle border border-primary text-primary rounded-pill">${inv.type || "N/A"}</span>`;
          const catBadge = `<span class="badge bg-success-subtle border border-success text-success rounded-pill">${inv.category || "Uncategorized"}</span>`;
          // const claimBadge = `<span class="badge bg-primary-subtle border border-success text-primary rounded-pill">${inv.reclaim_by || "Company"}</span>`;

          // 🧾 Build Card Layout
          html += `
            <div class="card border-0 shadow-sm rounded-3 mb-2">
              <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                      <strong class="fs-6"><?=$currency_symbol?>${inv.invoice_amount}</strong>
                      <button type="button" class="btn btn-xs rounded-pill ${statusBtnClass} px-2 py-0" disabled>
                        ${statusIcon} ${inv.payment_status}
                      </button>
                      ${typeBadge} ${catBadge}
                    </div>` +
            (inv.payment_status == "Partial Paid"
              ? `<!--div class="small text-muted mb-1">
                        <i class="fa fa-wallet me-1 text-secondary"></i> Partial Paid
                        <?=$currency_symbol?>${inv.invoice_partial}
                      </div-->`
              : ``) +
            `<div class="small text-dark mt-1">
                        ${
                          inv.notes
                            ? `<i class="fa fa-sticky-note me-1 text-primary"></i>${inv.notes}`
                            : "<em class='text-muted'>No notes provided</em>"
                        }
                            - <i>${inv.invoice_dated}</i>
                      </div>
                  </div>
                  <div class="btn-group btn-group-xs">
                    ${docBtn}
                    <button class="btn btn-light border edit-payment" title="Edit Payment" data-id="${inv.id}">
                      <i class="fa fa-pen"></i>
                    </button>
                    <button class="btn btn-light border text-danger delete-payment" title="Delete Payment" data-id="${inv.id}">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>`;
        });

        $("#payment-list").html(html);

        // ================================
        // 🔥 PAYMENT SUMMARY BOX
        // ================================
        $("#payment-summary").html(`
            <div class="row g-2 mt-2">
                <div class="col-6 col-md-3">
                    <div class=" rounded bg-primary-subtle border border-primary text-center">
                        <div class="fw-bold small text-primary">Total Income</div>
                        <div class="fw-bold text-primary fs-6">
                            <?=$currency_symbol?>${totalIncome.toFixed(2)}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="rounded bg-warning-subtle border border-warning text-center">
                        <div class="fw-bold small text-warning">Total Expense</div>
                        <div class="fw-bold text-warning fs-6">
                            <?=$currency_symbol?>${totalExpense.toFixed(2)}
                        </div>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="rounded bg-success-subtle border border-success text-center">
                        <div class="fw-bold small text-success">Paid</div>
                        <div class="fw-bold text-success fs-6">
                            <?=$currency_symbol?>${paidAmount.toFixed(2)}
                        </div>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="rounded bg-secondary-subtle border border-secondary text-center">
                        <div class="fw-bold small text-secondary">Unpaid</div>
                        <div class="fw-bold text-secondary fs-6">
                            <?=$currency_symbol?>${unpaidAmount.toFixed(2)}
                        </div>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="rounded bg-info-subtle border border-info text-center">
                        <div class="fw-bold small text-info">Partial</div>
                        <div class="fw-bold text-info fs-6">
                            <?=$currency_symbol?>${partialAmount.toFixed(2)}
                        </div>
                    </div>
                </div>
            </div>
        `);


      }, "json");
    }

    function checkReimbursable() {
      const type = $("#payment_type").val();
      const status = $("#payment_status").val();
      const amount = $("#payment_amount").val();

      if (type === "Expense" && (status === "Paid" || status === "Partial Paid")) {
        $("#reimbursable_section").removeClass("d-none");
      } else {
        $("#reimbursable_section").addClass("d-none");
        $("#reimbursement_amount_field").addClass("d-none");
      }
    }
    $("#payment_type, #payment_status").on("change", checkReimbursable);

    $("#reimbursable").on("change", function() {
      if ($(this).val() === "Yes") {
        $("#reimbursement_amount_field").removeClass("d-none");
        $("#reimbursement_amount").val($("#payment_amount").val());
      } else {
        $("#reimbursement_amount_field").addClass("d-none");
      }
    });

    $("#payment_amount").on("keyup change", function() {
      if ($("#reimbursement_amount_field").is(":visible")) {
        $("#reimbursement_amount").val($(this).val());
      }
    });

    $('#p_daterange').daterangepicker({
        locale: {
            format: 'DD-MMM-YYYY',
            separator: ' - ' // define the separator you prefer
        },
        startDate: "<?=date("d-M-Y",strtotime($date." - 6 days"))?>",
        endDate: "<?=date("d-M-Y",strtotime($date))?>",
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });
    $('#p_daterange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format("DD MMM YYYY") + " - " + picker.endDate.format("DD MMM YYYY"));
        loadPayments();
    });
    loadPayments();

    $("#clearPaymentFilter").on("click", function () {
        $("#p_daterange").val("");
        $("#clearPaymentFilter").hide();
        loadPayments();
    });

    $("#payment_payment_method").on("change", function() {
      const v = $(this).val();

      $("#card_last4_field").addClass("d-none");
      $("#cheque_fields").addClass("d-none");

      if (v === "Card") {
        $("#card_last4_field").removeClass("d-none");
      }
      if (v === "Cheque") {
        $("#cheque_fields").removeClass("d-none");
      }
    });


  // clear state when adding new payment
  $("#addpaymentBtn").on("click", function() {
    $("#paymentForm")[0].reset();
    $("#payment_id").val('');
    existingDocs = [];
    $("#documentPreviewp").empty();
    $("#existingDocuments").empty();
    $("#payment_date").val('<?=date("Y-m-d",strtotime($date))?>').trigger('change');
    $("#paymentModalTitle").text("Add Payment for Customer <?=$name?>");

    $("#payment_status").val('Unpaid').trigger('change');
    // $("#payment_category").val('').trigger('change');
    $("#payment_category").prop("selectedIndex", 0).trigger("change");
    $("#payment_type").val('Income').trigger('change');
    $("#reclaim_by").val('Company').trigger('change');
    $("#payment_amount").val(0).trigger('change');
    $("#payment_partial").val(0).trigger('change');
    $("#payment_payment_method").val('').trigger('change');

    modal.show();
  });

  // when selecting new files, show previews
  $("#documentp").on("change", function() {
    const files = this.files;
    renderSelectedPreviews(files);
  });

  // payment status change
  $("#payment_status").on("change", function() {
    const payment_status = $(this).val();
    if(payment_status=='Partial Paid') {
        // $("#payment_partiald").show();
    }
    else {
        // $("#payment_partiald").hide();
    }
    if(payment_status=='Paid'||payment_status=='Partial Paid') {
        $("#payment_payment_methodd").show();
    }
    else {
        $("#payment_payment_methodd").hide();
    }
  });

    // allow deleting individual existing docs from UI before submit
    // $(document).on("click", ".remove-existing-doc", function() {
    // const name = $(this).data("name");
    // // remove from existingDocs array
    // existingDocs = existingDocs.filter(d => d.name !== name);
    // renderExistingDocs();
    // });
    // 🟢 Allow deleting individual existing docs from UI before submit
    $(document).on("click", ".remove-existing-doc", function () {
      const fileToRemove = $(this).data("name"); // should be actual file name
      existingDocs = existingDocs.filter(d => d.file !== fileToRemove && d.name !== fileToRemove);
      renderExistingDocs();
    });

  // Payment Status Button Selection
  // $(document).on("click", ".payment-status-btn", function () {
  //   const status = $(this).data("status");
  //   $("#payment_status").val(status).trigger('change');
  //   $(".payment-status-btn").removeClass("btn-primary text-white").addClass("btn-outline-primary");
  //   $(this).removeClass("btn-outline-primary").addClass("btn-primary text-white");
  // });

  // Save payment — append multiple files
  $("#paymentForm").on("submit", function(e) {
    e.preventDefault();

    // validation: must have at least one doc (existing OR newly selected)
    const newFiles = $("#documentp")[0].files || [];
    // if ((existingDocs.length + newFiles.length) === 0) {
      // alert("Please upload at least one document (image or PDF).");
      // return;
    // }

    const formData = new FormData(this);

    // 🟢 HANDLE "OTHER" DATE OPTION
    // let paymentDate = $("#payment_date").val();
    // if (paymentDate === "Other") {
    //     const wrapper     = $("#payment_date").closest(".jselect-wrapper");
    //     const customInput = wrapper.find(".jselect-custom");
    //     let otherDate = customInput.val().trim();
    //     if (otherDate === "") {
    //         alert("Please select a date.");
    //         return;
    //     }
    //     // Replace the date value in formData
    //     formData.append("payment_date", otherDate);
    // } else {
    //     // Use the selected date
    //     formData.append("payment_date", paymentDate);
    // }

    // collect document names (from the dynamically generated inputs)
    const docNames = [];
    $('input[name="document_name[]"]').each(function() {
      docNames.push($(this).val().trim());
    });

    formData.append("document_names", JSON.stringify(docNames));
    // formData.append("existing_documents", JSON.stringify(existingDocs.map(d => d.name || d.filename || d.id || d.url)));
    formData.append(
      "existing_documents",
      JSON.stringify(existingDocs.map(d => ({
        file: d.name || d.file || "",
        name: d.label || d.name || ""
      })))
    );
    formData.append("action", "save");

    for (let pair of formData.entries()) {
        console.log(pair[0] + ":", pair[1]);
    }
    // return;

    $.ajax({
      url: ajaxURL,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function(res) {
        if (res.success) {
          modal.hide();
          loadPayments();
        } else {
          alert(res.error || "Failed to save payment.");
        }
      },
      error: () => alert("Error saving payment.")
    });
  });

    // 🟢 Edit payment — load payment details including existing labeled documents
    $(document).on("click", ".edit-payment", function () {
      const id = $(this).data("id");

      $.post(ajaxURL, { action: "get", id: id }, function (res) {
        if (!res.success || !res.data) {
          alert("Failed to load payment.");
          return;
        }

        const inv = res.data;

        // Populate form fields
        $("#payment_id").val(inv.id);
        
        // $("#payment_date").val(inv.invoice_date).trigger("change");
                /* ----------------------------
           FIX DATE FOR EDIT PAYMENT
        -----------------------------*/
        const today    = "<?=date('Y-m-d',strtotime($date))?>";
        const tomorrow = "<?=date('Y-m-d',strtotime('+1 day',strtotime($date)))?>";
        const saved    = inv.payment_date || inv.invoice_date;
        // jSelect wrapper + custom input
        const wrapper      = $("#payment_date").closest(".jselect-wrapper");
        const customInput  = wrapper.find(".jselect-custom");
        // RESET UI
        customInput.addClass("d-none").val("");
        wrapper.find(".jselect-btn").removeClass("active");
        // CASE: Today
        if (saved === today) {
            $("#payment_date").val(today).trigger("change");
            $("#payment_date").attr("name", "payment_date");
            refreshJSelect("payment_date");
        }
        // CASE: Tomorrow
        else if (saved === tomorrow) {
            $("#payment_date").val(tomorrow).trigger("change");
            $("#payment_date").attr("name", "payment_date");
            refreshJSelect("payment_date");
        }
        // CASE: OTHER DATE (most important)
        else {
            // 1️⃣ Set hidden real date into select
            $("#payment_date").val(saved).trigger("change");
            // 2️⃣ Activate OTHER option
            $("#payment_date option[data-other='true']").prop("selected", true);
            $("#payment_date").attr("name", "payment_date").trigger("change");
            refreshJSelect("payment_date");
            // 3️⃣ After refresh, jSelect re-created, so re-grab elements
            const newWrapper     = $("#payment_date").closest(".jselect-wrapper");
            const newCustomInput = newWrapper.find(".jselect-custom");
            // 4️⃣ Show displayed date input & fill value
            newCustomInput.removeClass("d-none").val(saved);
            newWrapper.find("input[type=hidden]").val(saved);
        }


        $("#payment_status").val(inv.payment_status).trigger("change");
        $("#payment_status").attr("name", "payment_status"); //.trigger("change");
        refreshJSelect("payment_status");

        $("#payment_category").val(inv.category); //.trigger("change");
        
        $("#payment_type").val(inv.type).trigger("change", inv.category);
        // $("#payment_type").val(inv.type);
        // setTimeout(() => {
            $("#payment_type").attr("name", "payment_type"); //.trigger("change");
            refreshJSelect("payment_type");
            // $("#payment_type").val(inv.type).trigger("change", inv.category);
            // const wrap  = $("#payment_type").closest(".jselect-wrapper");
            // const hid   = wrap.find("input[type='hidden']");
            // if (hid.length) hid.val(inv.type);
        // }, 20);

        $("#reclaim_by").val(inv.reclaim_by).trigger("change");
        $("#payment_amount").val(inv.invoice_amount).trigger("change");
        $("#payment_partial").val(inv.invoice_partial).trigger("change");
        
        $("#payment_payment_method").val(inv.invoice_payment_method).trigger("change");
        $("#payment_payment_method").attr("name", "payment_payment_method"); //.trigger("change");
        refreshJSelect("payment_payment_method");
        
        $("#reimbursable").val(inv.reimbursable).trigger("change");
        $("#reimbursable").attr("name", "reimbursable");
        refreshJSelect("reimbursable");
        
        $("#card_last4").val(inv.card_last4).trigger("change");
        $("#cheque_bank").val(inv.cheque_bank).trigger("change");
        $("#cheque_issuer").val(inv.cheque_issuer).trigger("change");
        $("#reimbursement_amount").val(inv.reimbursement_amount).trigger("change");
        $("#notesp").val(inv.notes || "");
        $("#documentp").val(""); // clear file input

        // Prepare existingDocs array from server response
        existingDocs = [];

        // ✅ Handle structured documents or legacy strings
        if (Array.isArray(inv.documents) && inv.documents.length) {
          inv.documents.forEach((doc) => {
            let file, label, url, type;

            if (typeof doc === "object") {
              // structured format
              file = doc.file;
              label = doc.name || "";
              url = doc.url || `uploads/customers/payments/${file}`;
              type = doc.type || (file.toLowerCase().endsWith(".pdf") ? "application/pdf" : "image/*");
                console.log("case11");
            } else {
              // legacy string fallback
              file = doc;
              label = "";
              url = `uploads/customers/payments/${file}`;
              type = file.toLowerCase().endsWith(".pdf") ? "application/pdf" : "image/*";
                console.log("case12");
            }


            existingDocs.push({ name: file, label: label, url: url, type: type });
          });
        } else if (typeof inv.documents === "string" && inv.documents.trim() !== "") {
          // Legacy single-field support
          try {
            const parsed = JSON.parse(inv.documents);
            if (Array.isArray(parsed)) {
              parsed.forEach((f) => {
                const file = typeof f === "object" ? f.file : f;
                const label = typeof f === "object" ? f.name || "" : "";
                existingDocs.push({
                  name: file,
                  label: label,
                  url: `uploads/customers/payments/${file}`,
                  type: file.toLowerCase().endsWith(".pdf") ? "application/pdf" : "image/*",
                });
              });
                console.log("case2");
            } else {
              existingDocs.push({
                name: parsed,
                label: "",
                url: `uploads/customers/payments/${parsed}`,
                type: parsed.toLowerCase().endsWith(".pdf") ? "application/pdf" : "image/*",
              });
              console.log("case3");
            }
          } catch (e) {
            // comma separated fallback
            inv.documents
              .split(",")
              .map((s) => s.trim())
              .filter(Boolean)
              .forEach((f) =>
                existingDocs.push({
                  name: f,
                  label: "",
                  url: `uploads/customers/payments/${f}`,
                  type: f.toLowerCase().endsWith(".pdf") ? "application/pdf" : "image/*",
                })
              );
              console.log("case4");
          }
        }

        // ✅ Render existing documents (with labels)
        const $box = $("#existingDocuments").empty();
        existingDocs.forEach((doc) => {
          const isImage = doc.type.startsWith("image");
          const displayName = doc.label
            ? `<div class="small text-muted text-truncate">${doc.label}</div>`
            : "";

          const $el = $(`
            <div class="d-flex align-items-center gap-2 bg-white border rounded p-2 mb-1" style="max-width:100%;">
              <div class="me-2" style="width:56px; flex:0 0 56px;">
                ${
                  isImage
                    ? `<img src="${doc.url}" style="max-width:56px; max-height:40px; object-fit:contain;">`
                    : `<i class="fa fa-file fa-2x text-muted"></i>`
                }
              </div>
              <div class="flex-grow-1 small text-truncate" data-file="${doc.name}">
                ${doc.label}
              </div>
              <div class="btn-group btn-group-sm ms-2">
                <button data-file="${doc.url}" data-type="${isImage ? "image" : "pdf"}" data-label="${doc.label || "Document"}" class="btn btn-light border view-document">
                  <i class="fa fa-eye"></i>
                </button>
                <button type="button" class="btn btn-light border text-danger remove-existing-doc" data-name="${doc.name}">
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </div>
          `);

          $box.append($el);
        });

        $("#documentPreviewp").empty();
        $("#paymentModalTitle").text("Edit Payment of Customer <?=$name?>");
        modal.show();
      }, "json");
    });


  // Delete payment
  $(document).on("click", ".delete-payment", function() {
    const id = $(this).data("id");
    if (!confirm("Delete this payment?")) return;
    $.post(ajaxURL, { action: "delete", id: id, customer_id: customerId }, function(res) {
      if (res.success) loadPayments();
      else alert("Failed to delete payment.");
    }, "json");
  });


  // Auto-notes generator for NEW payments only
  window.generateAutoNotes = function () {
  // function generateAutoNotes() {
    const isNew = ($("#payment_id").val().trim() === ""); // new payment only
    if (!isNew) return; // prevent overwriting when editing
    const type       = $("#payment_type").val();
    const amount     = parseFloat($("#payment_amount").val() || 0);
    const category   = $("#payment_category").val();
    const method     = $("#payment_payment_method").val();
    const status     = $("#payment_status").val();
    if (!amount || !category) return;
    let text = "";
    // Income → Received
    const meth = method!=''&&method!=null?' using '+method:'';
    if (type === "Income") {
        if (status === "Paid")
            text = `Received AED ${amount} for ${category}`+meth+`.`;
        else if (status === "Partial Paid")
            text = `Partially received <?=$currency_symbol?>${amount} for ${category}`+meth+`.`;
        else
            text = `Pending receipt of <?=$currency_symbol?>${amount} for ${category}.`;
    }
    // Expense → Paid
    if (type === "Expense") {
        if (status === "Paid")
            text = `Paid AED ${amount} for ${category}`+meth+`.`;
        else if (status === "Partial Paid")
            text = `Partially paid <?=$currency_symbol?>${amount} for ${category}`+meth+`.`;
        else
            text = `Pending payment of <?=$currency_symbol?>${amount} for ${category}.`;
    }
    $("#notesp").val(text);
  }
  // Trigger auto generation when any of these change:
  $("#payment_type, #payment_amount, #payment_category, #payment_payment_method, #payment_status")
    .on("change keyup", generateAutoNotes);

});
</script>

<script>
  // make contact id available globally to all later scripts
  window.supplierId = <?= $id ?? 0 ?>;
</script>

<script>
(function(){
  const supplierId = <?= $id ?? 0 ?>;
  const LIMIT = 20;
  let offset = 0;
  let hasMore = true;
  let loading = false;
  const loadedIds = new Set();

  const messagesContainer = document.getElementById('whatsappMessages');
  const scrollWrap = document.getElementById('whatsappChatContainer');
  const loadOlderBtn = document.getElementById('whats-load-older');

  function fmtDateTime(dtStr){
    if(!dtStr) return '';
    // try to normalize common formats
    const d = new Date(dtStr.replace(' ', 'T'));
    if (isNaN(d)) return dtStr;
    const pad = n => n < 10 ? '0'+n : n;
    return `${pad(d.getDate())}-${pad(d.getMonth()+1)}-${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }

  function createMessageNode(msg){
    const dirRaw = (msg.direction || '').toString().toLowerCase();
    const dir = (dirRaw.indexOf('out') !== -1 || dirRaw === 'outgoing' || dirRaw === 'sent') ? 'outgoing' : 'incoming';

    // wrapper row
    const row = document.createElement('div');
    row.className = 'msg-row ' + dir;
    row.dataset.id = msg.id;

    // bubble-wrap takes half width and aligns via parent row justification
    const wrap = document.createElement('div');
    wrap.className = 'bubble-wrap';

    // bubble element
    const bubble = document.createElement('div');
    bubble.className = 'chat-bubble ' + dir;

  // If this is an image or document, render media first
  const type = (msg.msg_type || msg.type || '').toString().toLowerCase();

  // Helper: safe text -> text node (avoid innerHTML when not needed)
  const makeTextDiv = (txt) => {
    const d = document.createElement('div');
    d.textContent = txt;
    return d;
  };

  if (type === 'image' && (msg.media_fileUrl || msg.document_fileUrl)) {
    // prefer media_fileUrl, fallback to document_fileUrl
    const src = msg.media_fileUrl || msg.document_fileUrl;
    const imgWrap = document.createElement('div');
    imgWrap.className = 'chat-media chat-media-image';

    const img = document.createElement('img');
    img.src = src;
    img.alt = msg.document_caption || msg.message_body || 'image';
    img.loading = 'lazy';
    img.style.maxWidth = '240px';
    img.style.maxHeight = '320px';
    img.style.display = 'block';
    img.style.borderRadius = '8px';
    imgWrap.appendChild(img);

    // caption under image if present (use document_caption first)
    const cap = (msg.document_caption || msg.message_body || '').toString().trim();
    if (cap) {
      const c = document.createElement('div');
      c.className = 'chat-media-caption';
      c.textContent = cap;
      imgWrap.appendChild(c);
    }

    bubble.appendChild(imgWrap);
  } else if (type === 'document' && (msg.document_fileUrl || msg.media_fileUrl)) {
    const docUrl = msg.document_fileUrl || msg.media_fileUrl;
    const docWrap = document.createElement('div');
    docWrap.className = 'chat-media chat-media-doc';

    const a = document.createElement('a');
    a.href = docUrl;
    a.target = '_blank';
    a.rel = 'noopener noreferrer';
    a.textContent = (msg.document_caption || docUrl.split('/').pop() || 'Download document');
    a.title = 'Open document in new tab';
    a.className = 'chat-doc-link';
    docWrap.appendChild(a);

    // optional small download icon (simple Unicode)
    const metaRow = document.createElement('div');
    metaRow.className = 'chat-media-meta';
    metaRow.textContent = '';

    // caption separate from link if message_body present
    const extraCap = (msg.message_body || '').toString().trim();
    if (extraCap) {
      const e = document.createElement('div');
      e.className = 'chat-media-caption';
      e.textContent = extraCap;
      docWrap.appendChild(e);
    }

    bubble.appendChild(docWrap);
  } else {
    // message body (text / interactive fallback)
    const body = document.createElement('div');
    body.innerHTML = (msg.message_body || '').replace(/\n/g, '<br>');
    bubble.appendChild(body);
  }

    // interactive fields (if present)
    const title = (msg.interactive_reply_title || msg.interactiveTitle || '').toString().trim();
    const desc = (msg.interactive_reply_description || msg.interactiveDesc || '').toString().trim();

    if (title) {
      const t = document.createElement('div');
      t.className = 'interactive-title';
      t.textContent = title;
      bubble.appendChild(t);
    }
    if (desc) {
      const d = document.createElement('div');
      d.className = 'interactive-desc';
      d.innerHTML = desc.replace(/\n/g, '<br>');
      bubble.appendChild(d);
    }

    // date meta
    const meta = document.createElement('div');
    meta.className = 'chat-meta';
    meta.textContent = fmtDateTime(msg.date_added || msg.date || msg.created_at || '');
    bubble.appendChild(meta);

    wrap.appendChild(bubble);
    row.appendChild(wrap);

    return row;
  }

  // render messages: if prependOlder true we insert at top but preserve scroll position
  function renderMessages(messages, prependOlder=false){
    if (!Array.isArray(messages) || messages.length === 0) return;

    // ensure chronological order (oldest first)
    messages.sort(function(a,b){
      const ta = new Date(a.date_added || a.date || a.created_at || 0).getTime() || 0;
      const tb = new Date(b.date_added || b.date || b.created_at || 0).getTime() || 0;
      return ta - tb;
    });

    if (prependOlder) {
      // keep scroll position stable
      const prevScrollHeight = scrollWrap.scrollHeight;
      messages.forEach(m => {
        if (loadedIds.has(String(m.id))) return;
        const node = createMessageNode(m);
        messagesContainer.insertBefore(node, messagesContainer.firstChild);
        loadedIds.add(String(m.id));
      });
      requestAnimationFrame(() => {
        const newScrollHeight = scrollWrap.scrollHeight;
        // keep view at the same message location
        scrollWrap.scrollTop = newScrollHeight - prevScrollHeight;
      });
    } else {
      messages.forEach(m => {
        if (loadedIds.has(String(m.id))) return;
        const node = createMessageNode(m);
        messagesContainer.appendChild(node);
        loadedIds.add(String(m.id));
      });
      // scroll to bottom so newest visible
      requestAnimationFrame(()=> { scrollWrap.scrollTop = scrollWrap.scrollHeight; });
    }
  }

  function fetchWhatsApp(prependOlder=false){
    if (loading) return;
    loading = true;
    loadOlderBtn.disabled = true;
    loadOlderBtn.innerText = 'Loading...';

    fetch('public/ajax/contacts_get_whatsapp.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `contact_id=${encodeURIComponent(supplierId)}&offset=${encodeURIComponent(offset)}&limit=${encodeURIComponent(LIMIT)}`
    }).then(r => r.json()).then(res => {
      const msgs = res && (res.messages || res.results || []) ? (res.messages || res.results || []) : [];
      if (msgs.length) {
        renderMessages(msgs, prependOlder);
        offset += msgs.length;
      }
      hasMore = (typeof res.hasMore !== 'undefined') ? !!res.hasMore : (msgs.length === LIMIT);
      if (hasMore) {
        loadOlderBtn.style.display = 'inline-block';
        loadOlderBtn.disabled = false;
        loadOlderBtn.innerText = 'Load older';
      } else {
        // If no more older messages, show disabled "No older messages" for clarity
        loadOlderBtn.style.display = 'inline-block';
        loadOlderBtn.disabled = true;
        loadOlderBtn.innerText = (offset > 0 ? 'No older messages' : 'No messages');
      }
    }).catch(err => {
      console.error('Failed to fetch whatsapp messages', err);
      loadOlderBtn.innerText = 'Error, retry';
      loadOlderBtn.disabled = false;
    }).finally(()=> { loading = false; });
  }

  loadOlderBtn.addEventListener('click', function(){
    if (!hasMore || loading) return;
    fetchWhatsApp(true);
  });

  // initial load
  document.addEventListener('DOMContentLoaded', function(){
    offset = 0;
    loadedIds.clear();
    messagesContainer.innerHTML = '';
    fetchWhatsApp(false);
  });

  // Optional public refresh
  window.refreshWhatsAppChat = function(){
    offset = 0;
    loadedIds.clear();
    messagesContainer.innerHTML = '';
    fetchWhatsApp(false);
  };

})();
</script>

<script>
(function(){
  // ensure the refresh function exists (from earlier code)
  const refreshFn = window.refreshWhatsAppChat;
  const refreshBtn = document.getElementById('whatsappRefreshBtn');
  const autoToggle = document.getElementById('whatsappAutoRefreshToggle');

  // Basic safety
  if (!refreshBtn) return;

  function setBtnLoading(on) {
    if (on) {
      refreshBtn.disabled = true;
      refreshBtn.innerHTML = '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i><span class="ms-1">Loading…</span>';
      refreshBtn.classList.add('disabled');
    } else {
      refreshBtn.disabled = false;
      refreshBtn.innerHTML = '<i class="fa fa-sync" aria-hidden="true"></i><span class="ms-1">Refresh</span>';
      refreshBtn.classList.remove('disabled');
    }
  }

  // click handler — call the global refresh function if present
  refreshBtn.addEventListener('click', function(e){
    e.preventDefault();
    if (typeof refreshFn !== 'function') {
      // fallback: try to call the internal fetch function if you named it differently
      console.warn('refreshWhatsAppChat not found. Make sure the function exists.');
      return;
    }
    setBtnLoading(true);
    try {
      // refreshWhatsAppChat is expected to repopulate the chat and return immediately.
      // If it performs async fetches, we re-enable button after a small delay or when idle.
      const maybePromise = refreshFn();
      if (maybePromise && typeof maybePromise.then === 'function') {
        maybePromise.finally(() => setBtnLoading(false));
      } else {
        // if not a promise, wait a short fixed time and re-enable (gives UX feel)
        setTimeout(() => setBtnLoading(false), 800);
      }
    } catch (err) {
      console.error('Refresh failed', err);
      setBtnLoading(false);
    }
  });

  // Optional: Auto-refresh when toggle checked
  // Default interval: 20 seconds (adjust below)
  let autoIntervalId = null;
  const AUTO_INTERVAL_MS = 10000; // 20s

  function startAutoRefresh() {
    if (autoIntervalId) return;
    autoIntervalId = setInterval(() => {
      // small guard: if already loading, skip this tick
      if (refreshBtn.disabled) return;
      // trigger refresh programmatically
      refreshBtn.click();
    }, AUTO_INTERVAL_MS);
  }
  function stopAutoRefresh() {
    if (!autoIntervalId) return;
    clearInterval(autoIntervalId);
    autoIntervalId = null;
  }

  if (autoToggle) {
    // Restore previous choice if you want (localStorage)
    const saved = localStorage.getItem('whatsapp_auto_refresh');
    if (saved === '1') { autoToggle.checked = true; startAutoRefresh(); }

    autoToggle.addEventListener('change', function(){
      if (this.checked) {
        localStorage.setItem('whatsapp_auto_refresh','1');
        startAutoRefresh();
      } else {
        localStorage.removeItem('whatsapp_auto_refresh');
        stopAutoRefresh();
      }
    });
    // stop on page unload
    window.addEventListener('beforeunload', stopAutoRefresh);
  }

})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // --- keep existing consts from your script ---
  const sendForm = document.getElementById('whatsapp-send-form');
  const msgInp = document.getElementById('whatsappMessageInput');
  const filesInput = document.getElementById('whatsappFiles');
  const previewWrap = document.getElementById('whatsappAttachPreview');
  const sendBtn = document.getElementById('whatsappSendBtn');
  const messagesContainer = document.getElementById('whatsappMessages');
  const chatWrap = document.getElementById('whatsappChatContainer');

  if (!sendForm || !msgInp || !sendBtn || !messagesContainer || !chatWrap) {
    console.error('One or more required elements not found, aborting send script.');
    return;
  }

  const WHATSAPP_SEND_ENDPOINT = '../Whatsapp/sendAgentMessageAPI.php';
  // contact id available on page as supplierId
  const contactId = (typeof supplierId !== 'undefined') ? supplierId : 0;

  // --- helper functions (same as your original) ---
  function setChatBubbleContent(node, text, isoDate) {
    const bubble = node.querySelector('.chat-bubble') || node;
    const bodyDiv = bubble.querySelector(':scope > div:not(.chat-meta)') || bubble.querySelector('div');
    if (bodyDiv) bodyDiv.textContent = text;
    const meta = bubble.querySelector('.chat-meta');
    if (meta) {
      const dt = new Date(isoDate || Date.now());
      meta.textContent = dt.toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
  }

  function buildOutgoingNode(msg, { temp=false } = {}) {
    let sample = messagesContainer.querySelector('.msg-row.outgoing');
    if (!sample) {
      const bubbleSample = messagesContainer.querySelector('.chat-bubble.outgoing');
      if (bubbleSample) sample = bubbleSample.closest('.msg-row') || bubbleSample;
    }

    if (sample) {
      const node = sample.cloneNode(true);
      node.removeAttribute('id');
      if (temp) node.dataset.tempId = msg.id; else node.dataset.id = msg.id;
      node.classList.add('outgoing');
      const bubble = node.querySelector('.chat-bubble');
      if (bubble) bubble.classList.add('outgoing');
      setChatBubbleContent(node, msg.message_body || msg.message || '', msg.date_added);
      node.classList.remove('failed', 'pending');
      return node;
    }

    const node = document.createElement('div');
    node.className = 'msg-row outgoing';
    if (temp) node.dataset.tempId = msg.id; else node.dataset.id = msg.id;

    const wrap = document.createElement('div'); wrap.className = 'bubble-wrap';
    const bubble = document.createElement('div'); bubble.className = 'chat-bubble outgoing';
    const body = document.createElement('div'); body.textContent = msg.message_body || msg.message || '';
    const meta = document.createElement('div'); meta.className = 'chat-meta';
    const dt = new Date(msg.date_added || Date.now());
    meta.textContent = dt.toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    bubble.appendChild(body); bubble.appendChild(meta);
    wrap.appendChild(bubble); node.appendChild(wrap);
    return node;
  }

  function appendOutgoingMessage(msg, opts = { temp: false }) {
    if (typeof createMessageNode === 'function') {
      try {
        const node = createMessageNode({
          id: msg.id || ('tmp-' + Date.now()),
          message_body: msg.message_body || msg.message || '',
          direction: 'outgoing',
          date_added: msg.date_added || (new Date()).toISOString(),
          meta: msg.meta || {}
        });
        if (opts.temp) { node.classList.add('temp','pending'); node.dataset.tempId = msg.id; } else node.dataset.id = msg.id;
        messagesContainer.appendChild(node);
        requestAnimationFrame(()=> { chatWrap.scrollTop = chatWrap.scrollHeight; });
        return node;
      } catch (err) {
        console.warn('createMessageNode threw — falling back to clone', err);
      }
    }

    const node = buildOutgoingNode(msg, opts);
    if (opts.temp) { node.classList.add('temp','pending'); node.dataset.tempId = msg.id; } else node.dataset.id = msg.id;
    messagesContainer.appendChild(node);
    requestAnimationFrame(()=> { chatWrap.scrollTop = chatWrap.scrollHeight; });
    return node;
  }

  function updateTempNodeWithServerResponse(tempId, serverId, serverMeta) {
    const sel = `[data-temp-id="${CSS.escape(tempId)}"]`;
    const tempNode = messagesContainer.querySelector(sel);
    if (!tempNode) return false;
    tempNode.dataset.id = serverId;
    delete tempNode.dataset.tempId;
    tempNode.classList.remove('temp','pending');
    if (serverMeta && serverMeta.status) tempNode.dataset.serverStatus = serverMeta.status;
    return true;
  }

  // --- original send logic extracted into a function so we call it only when allowed ---
  function doSend(message) {
    // prepare a temporary message object and append it immediately
    const tmpId = 'tmp-' + Date.now();
    const tempMsg = {
      id: tmpId,
      message_body: message,
      date_added: (new Date()).toISOString()
    };
    appendOutgoingMessage(tempMsg, { temp: true });

    // UI lock
    sendBtn.disabled = true;
    const origBtnHTML = sendBtn.innerHTML;
    sendBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';

    // prepare form data
    const fd = new FormData();
    fd.append('contact_id', contactId);
    fd.append('message', message);
    fd.append('contact_type', 'Customer');
    if (filesInput && filesInput.files) {
      Array.from(filesInput.files).forEach((f, i) => fd.append('media[]', f, f.name));
    }

    fetch(WHATSAPP_SEND_ENDPOINT, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    }).then(async r => {
      const text = await r.text();
      try { return JSON.parse(text); } catch (err) { return { error: 'Invalid JSON', raw: text }; }
    }).then(res => {
      const statusVal = res && (res.status ?? res.code ?? res.statusCode);
      const isSuccess = (() => {
        if (statusVal === undefined || statusVal === null) return !!res.success;
        const s = String(statusVal);
        return /^2\d\d$/.test(s);
      })();

      if (isSuccess) {
        const serverId = res.messageId || res.id || ('id-' + Date.now());
        const ok = updateTempNodeWithServerResponse(tmpId, serverId, res);
        if (!ok) {
          appendOutgoingMessage({
            id: serverId,
            message_body: message,
            date_added: res.date_added || new Date().toISOString()
          });
        }
        // clear inputs and preview
        msgInp.value = '';
        if (filesInput) filesInput.value = '';
        if (previewWrap) previewWrap.innerHTML = '';
      } else {
        const sel = `[data-temp-id="${CSS.escape(tmpId)}"]`;
        const tempNode = messagesContainer.querySelector(sel);
        if (tempNode) tempNode.classList.remove('pending'), tempNode.classList.add('failed');
        const errMsg = res && (res.error || res.message || res.raw) || 'Failed to send message';
        alert(errMsg);
        console.error('Send error response:', res);
      }
    }).catch(err => {
      console.error('Send failed', err);
      const sel = `[data-temp-id="${CSS.escape(tmpId)}"]`;
      const tempNode = messagesContainer.querySelector(sel);
      if (tempNode) tempNode.classList.remove('pending'), tempNode.classList.add('failed');
      alert('Error sending message. See console.');
    }).finally(() => {
      sendBtn.disabled = false;
      sendBtn.innerHTML = origBtnHTML;
    });
  }

  // ---------- attach submit handler that first checks session then calls doSend ----------
  sendForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const message = msgInp.value.trim();
    if (!message && (!filesInput || !filesInput.files || filesInput.files.length === 0)) {
      alert('Please enter a message or attach file(s).');
      return;
    }

    // make the session check call and only send if allowed
    // use contact_id (supplierId) for the check endpoint
    const cid = contactId || 0;
    if (cid === 0) {
      // no contact id — allow by default
      doSend(message);
      return;
    }

    fetch(
        'public/ajax/check_session.php?contact_id=' 
        + encodeURIComponent(cid)
        + '&contact_type=' 
        + encodeURIComponent('Customer'),
        { credentials: 'same-origin' }
      ).then(r => r.json())
      .then(data => {
        if (data && data.allow_normal) {
          doSend(message);
        } else {
          alert("Normal messages not allowed because customer session expired.\nPlease use a WhatsApp template.");
        }
      })
      .catch(err => {
        console.warn('Session check failed — allowing send by default', err);
        // allow send if session check fails (optional)
        doSend(message);
      });
  });

  window.appendOutgoingMessage = appendOutgoingMessage;
window.createMessageNode = createMessageNode;
window.updateTempNodeWithServerResponse = updateTempNodeWithServerResponse;

}); // DOMContentLoaded
</script>


<script>
/**
 * Template modal script (read-only preview, no editing)
 * - Waits for DOMContentLoaded
 * - Guards missing elements
 * - Uses bootstrap.Modal when available
 */
document.addEventListener('DOMContentLoaded', function () {
  const templateBtn = document.getElementById('whatsappTemplateBtn');
  const templateModalEl = document.getElementById('whatsappTemplateModal');
  const templateList = document.getElementById('templateList');
  const templateLoading = document.getElementById('templateLoading');
  const templatePreviewWrap = document.getElementById('templatePreviewWrap');
  const templatePreview = document.getElementById('templatePreview');
  const templateSendBtn = document.getElementById('templateSendBtn');
  const templateCancelBtn = document.getElementById('templateCancelBtn');

  if (!templateBtn) { console.warn('Template button not found'); return; }
  if (!templateModalEl) { console.warn('Template modal not found'); return; }
  if (!templateList || !templateLoading || !templatePreviewWrap || !templatePreview || !templateSendBtn) {
    console.warn('Template modal required elements missing'); return;
  }

  let bsModal = null;
  if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
    bsModal = new bootstrap.Modal(templateModalEl, { keyboard: true, focus: true });
  }

  const TEMPLATES_ENDPOINT = 'public/ajax/get_whatsapp_templates.php';
  const SEND_ENDPOINT = typeof WHATSAPP_SEND_ENDPOINT !== 'undefined' ? WHATSAPP_SEND_ENDPOINT : '../Whatsapp/sendAgentTemplateAPI.php';
  let currentTemplate = null;

  function openModal() {
    templateList.innerHTML = '';
    templatePreviewWrap.style.display = 'none';
    templateLoading.style.display = 'block';
    templateList.style.display = 'none';
    currentTemplate = null;
    if (bsModal) bsModal.show();
    else { templateModalEl.classList.add('show'); templateModalEl.style.display = 'block'; templateModalEl.removeAttribute('aria-hidden'); templateModalEl.setAttribute('tabindex','-1'); templateModalEl.focus(); }
    loadTemplates();
  }

  function closeModal() {
    if (bsModal) bsModal.hide();
    else { templateModalEl.classList.remove('show'); templateModalEl.style.display = 'none'; templateModalEl.setAttribute('aria-hidden','true'); }
  }

  async function loadTemplates() {
    try {
      templateLoading.textContent = 'Loading templates…';
      templateLoading.style.display = 'block';
      templateList.style.display = 'none';

      const res = await fetch(TEMPLATES_ENDPOINT, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('Network response not ok: ' + res.status);
      const data = await res.json();
      const templates = data && (data.templates || []);
      templateList.innerHTML = '';

      if (!templates.length) {
        templateList.innerHTML = '<div class="small text-muted p-2">No templates found.</div>';
      } else {
        templates.forEach(t => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn btn-outline-primary btn-sm template-btn-pill me-1 mb-1';
          btn.textContent = t.name || ('Template #' + (t.id || ''));
          btn.dataset.templateId = t.id || '';
          btn.dataset.templateContent = t.content || '';
          btn.addEventListener('click', function () {
            selectTemplate(t);
          });
          templateList.appendChild(btn);
        });
      }
    } catch (err) {
      console.error('Failed to load templates', err);
      templateList.innerHTML = '<div class="small text-danger p-2">Failed to load templates. See console for details.</div>';
    } finally {
      templateLoading.style.display = 'none';
      templateList.style.display = 'block';
    }
  }

  function selectTemplate(tpl) {
    currentTemplate = tpl;
    templatePreview.textContent = tpl.content || '';
    templatePreviewWrap.style.display = 'block';
    // move focus to the Send button for easy keyboard sending
    templateSendBtn.focus();
  }

  templateBtn.addEventListener('click', function (e) {
    e.preventDefault();
    openModal();
  });

  templateSendBtn.addEventListener('click', async function () {
  if (!currentTemplate) { alert('Please select a template first.'); return; }
  const finalText = (currentTemplate.content || '').trim();
  if (!finalText) { alert('Template content is empty.'); return; }

  // create tmp id and optimistic UI
  const tmpId = 'tmp-' + Date.now();

  const messagesContainerEl = document.getElementById('whatsappMessages');
const chatWrapEl = document.getElementById('whatsappChatContainer');

(function optimisticAppend() {
  try {
    // preferred: use your app's helper if available
    if (typeof appendOutgoingMessage === 'function') {
      appendOutgoingMessage({ id: tmpId, message_body: finalText, date_added: (new Date()).toISOString() }, { temp: true });
      return;
    }

    // next: try createMessageNode (your earlier code defines it)
    if (typeof createMessageNode === 'function') {
      const node = createMessageNode({
        id: tmpId,
        message_body: finalText,
        direction: 'outgoing',
        date_added: (new Date()).toISOString()
      });
      // mark as temporary/pending
      node.classList.add('temp', 'pending');
      node.dataset.tempId = tmpId;
      if (messagesContainerEl) {
        messagesContainerEl.appendChild(node);
        requestAnimationFrame(()=> { if (chatWrapEl) chatWrapEl.scrollTop = chatWrapEl.scrollHeight; });
      }
      return;
    }

    // final fallback: build a minimal outgoing node (safe & self-contained)
    function createFallbackOutgoingNode(msg, { temp=false } = {}) {
      const node = document.createElement('div');
      node.className = 'msg-row outgoing';
      if (temp) node.dataset.tempId = msg.id; else node.dataset.id = msg.id;

      const wrap = document.createElement('div');
      wrap.className = 'bubble-wrap';

      const bubble = document.createElement('div');
      bubble.className = 'chat-bubble outgoing';

      const body = document.createElement('div');
      body.textContent = msg.message_body || '';

      const meta = document.createElement('div');
      meta.className = 'chat-meta';
      try {
        meta.textContent = new Date(msg.date_added || Date.now()).toLocaleString('en-GB', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
      } catch (e) {
        meta.textContent = msg.date_added || '';
      }

      bubble.appendChild(body);
      bubble.appendChild(meta);
      wrap.appendChild(bubble);
      node.appendChild(wrap);
      // mark pending visually
      node.classList.add('pending');
      return node;
    }

    // append fallback node
    const fallbackNode = createFallbackOutgoingNode({ id: tmpId, message_body: finalText, date_added: (new Date()).toISOString() }, { temp: true });
    if (messagesContainerEl) {
      messagesContainerEl.appendChild(fallbackNode);
      requestAnimationFrame(()=> { if (chatWrapEl) chatWrapEl.scrollTop = chatWrapEl.scrollHeight; });
    }

  } catch (err) {
    console.warn('Optimistic append failed', err);
  }
})();

  // prepare form data
  const fd = new FormData();
  if (typeof supplierId !== 'undefined') fd.append('contact_id', supplierId);
  fd.append('message', finalText);
  fd.append('template_id', currentTemplate.tmp_id || '');
  fd.append('template_name', currentTemplate.name || '');
  fd.append('is_template', '1');
  fd.append('contact_type', 'Customer');

  templateSendBtn.disabled = true;
  const oldHTML = templateSendBtn.innerHTML;
  templateSendBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';

  try {
    const r = await fetch(SEND_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' });
    const text = await r.text();
    let res;
    try { res = JSON.parse(text); } catch (e) { res = { error: 'Invalid JSON', raw: text }; }

    const statusVal = res && (res.status ?? res.code ?? res.statusCode);
    const isSuccess = (statusVal === undefined || statusVal === null) ? !!res.success : /^2\d\d$/.test(String(statusVal));

    if (isSuccess) {
      const serverId = res.messageId || res.id || ('id-' + Date.now());

      // 1) update temp node if possible (preferred)
      let updated = false;
      try {
        if (typeof updateTempNodeWithServerResponse === 'function') {
          updated = updateTempNodeWithServerResponse(tmpId, serverId, res) || false;
        }
      } catch (e) { console.warn('updateTempNodeWithServerResponse threw', e); }

      // 2) fallback: if we couldn't find/update temp node, append new outgoing message
      if (!updated) {
    const sel = `[data-temp-id="${CSS.escape(tmpId)}"]`;
    const tempNode = document.getElementById('whatsappMessages')?.querySelector(sel);
    if (tempNode) {
        tempNode.classList.remove('pending');
        tempNode.dataset.id = serverId;
        tempNode.removeAttribute('data-temp-id');
    }
}

      // show success toast (use Bootstrap Toast if available)
      try {
        const toastEl = document.getElementById('globalToast');
        if (toastEl && window.bootstrap && typeof bootstrap.Toast === 'function') {
          // update toast text if your server returned something specific
          const body = toastEl.querySelector('.toast-body');
          if (body) body.textContent = res.message || 'Message sent successfully';
          // ensure it's visible
          toastEl.style.display = '';
          const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
          toast.show();
        } else if (toastEl) {
          // simple fallback: show and hide manually
          toastEl.style.display = '';
          const body = toastEl.querySelector('.toast-body');
          if (body) body.textContent = res.message || 'Message sent successfully';
          setTimeout(()=> { toastEl.style.display = 'none'; }, 3000);
        } else {
          // final fallback
          alert(res.message || 'Message sent successfully');
        }
      } catch (e) {
        console.warn('Toast display failed', e);
      }

      // close modal and reset selection
      if (typeof closeModal === 'function') closeModal();
    } else {
      // mark temp node failed if present
      try {
        const sel = `[data-temp-id="${CSS.escape(tmpId)}"]`;
        const tempNode = document.getElementById('whatsappMessages')?.querySelector(sel);
        if (tempNode) tempNode.classList.remove('pending'), tempNode.classList.add('failed');
      } catch (e) { /* ignore */ }
      const errMsg = res && (res.error || res.message || res.raw) || 'Failed to send template';
      alert(errMsg);
    }
  } catch (err) {
    console.error('Template send failed', err);
    try {
      const sel = `[data-temp-id="${CSS.escape(tmpId)}"]`;
      const tempNode = document.getElementById('whatsappMessages')?.querySelector(sel);
      if (tempNode) tempNode.classList.remove('pending'), tempNode.classList.add('failed');
    } catch (e){/*ignore*/}
    alert('Error sending template. See console.');
  } finally {
    templateSendBtn.disabled = false;
    templateSendBtn.innerHTML = oldHTML;
  }
});

  if (templateCancelBtn) {
    templateCancelBtn.addEventListener('click', function () {
      try { templateBtn.focus(); } catch (e) {}
    });
  }

  if (templateModalEl && bsModal) {
    templateModalEl.addEventListener('hidden.bs.modal', function () {
      templatePreviewWrap.style.display = 'none';
      currentTemplate = null;
      try { templateBtn.focus(); } catch (e) {}
    });
  }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Use supplierId (contact_id) from your page — read from window to be sure it's global
  const contactId = (typeof window.supplierId !== 'undefined') ? Number(window.supplierId) : 0;

  const input = document.getElementById('whatsappMessageInput');
  const sendBtn = document.getElementById('whatsappSendBtn');
  const sendFormEl = document.getElementById('whatsapp-send-form');
  const filesInputEl = document.getElementById('whatsappFiles');

  // create banner just above composer if not exists
  let banner = document.getElementById('sessionBanner');
  if (!banner) {
    banner = document.createElement('div');
    banner.id = 'sessionBanner';
    const composer = document.getElementById('whatsappComposer') || document.getElementById('whatsapp-send-form')?.parentNode;
    if (composer && composer.parentNode) composer.parentNode.insertBefore(banner, composer);
    else document.body.insertBefore(banner, document.body.firstChild);
  }

  // helper to toggle UI
  let normalAllowed = true;
  function setNormalAllowed(allow, reason) {
    normalAllowed = !!allow;
    if (!allow) {
      if (input) input.setAttribute('disabled','disabled');
      if (sendBtn) sendBtn.setAttribute('disabled','disabled');
      banner.textContent = 'Session expired — only template messages are allowed.';
      banner.style.display = 'block';
      banner.setAttribute('role','status');
      const attachLabel = document.querySelector('label[for="whatsappFiles"]');
      if (attachLabel) attachLabel.classList.add('disabled');
      if (filesInputEl) filesInputEl.setAttribute('disabled','disabled');
    } else {
      if (input) input.removeAttribute('disabled');
      if (sendBtn) sendBtn.removeAttribute('disabled');
      banner.style.display = 'none';
      const attachLabel = document.querySelector('label[for="whatsappFiles"]');
      if (attachLabel) attachLabel.classList.remove('disabled');
      if (filesInputEl) filesInputEl.removeAttribute('disabled');
    }
  }

  // initial check
  async function checkSessionAndToggle() {
  // read the *current* supplierId from global window (not stale closure)
  const cid = (typeof window.supplierId !== 'undefined') ? Number(window.supplierId) : 0;
  if (!cid) {
    setNormalAllowed(true);
    return;
  }

  // build query using URLSearchParams (avoids concat mistakes)
  const params = new URLSearchParams();
  params.set('contact_id', String(cid));
  // pass the contact type you want here
  params.set('contact_type', 'Customer');

  const url = 'public/ajax/check_session.php?' + params.toString();

  try {
    const res = await fetch(url, { credentials: 'same-origin' });
    if (!res.ok) throw new Error('Network');
    const data = await res.json();
    setNormalAllowed(!!data.allow_normal, data.reason || '');
  } catch (err) {
    console.error('Session check failed', err);
    // fail-open to avoid blocking users if check fails
    setNormalAllowed(true);
  }
}

  // block normal send when not allowed
  if (sendFormEl) {
    sendFormEl.addEventListener('submit', function(ev) {
      const message = input ? input.value.trim() : '';
      const hasFiles = filesInputEl && filesInputEl.files && filesInputEl.files.length > 0;

      // If message text exists and there are no files, treat as normal message.
      // If normalAllowed is false, block it.
      if (message && !hasFiles && !normalAllowed) {
        ev.preventDefault();
        alert('Normal messages are blocked because the session has expired. Please use a WhatsApp template.');
        return false;
      }
      // otherwise allow submitting (templates or files or allowed normal message)
    });
  }

  // run initial check (this runs on load)
  checkSessionAndToggle();

  // expose function so other code (tab click handler) can re-check when user opens the tab
  window.checkWhatsAppSession = checkSessionAndToggle;

  // optional: re-check session every X minutes (optional)
  // setInterval(checkSessionAndToggle, 5 * 60 * 1000); // every 5 minutes
});


document.addEventListener('DOMContentLoaded', function () {
  // Elements (existing on page)
  const filesInput = document.getElementById('whatsappFiles');
  const attachPreviewWrap = document.getElementById('whatsappAttachPreview');
  const sendEndpoint = typeof WHATSAPP_SEND_ENDPOINT !== 'undefined' ? WHATSAPP_SEND_ENDPOINT : '../Whatsapp/sendAgentAttachAPI.php';
  const contactId = (typeof supplierId !== 'undefined') ? supplierId : 0;

  // Modal elements (we added in HTML)
  const attachModalEl = document.getElementById('whatsappAttachModal');
  if (!attachModalEl) return; // modal not present
  // bootstrap modal instance if bootstrap available
  let attachModal = null;
  if (window.bootstrap && typeof bootstrap.Modal === 'function') {
    attachModal = new bootstrap.Modal(attachModalEl, { keyboard: true, backdrop: 'static' });
  }

  const attachList = document.getElementById('attachList');
  const attachSendBtn = document.getElementById('attachModalSend');
  const attachCancelBtn = document.getElementById('attachModalCancel');

  // Local selected files store (File objects)
  let selectedFiles = []; // array of { file: File, id: string }

  // Helper to create a small preview row in modal
  function createAttachRow(item) {
    const row = document.createElement('div');
    row.className = 'attach-item';
    row.dataset.tmpId = item.id;

    // thumb
    const thumb = document.createElement('img');
    thumb.className = 'attach-thumb';
    // try to create preview for images
    if (item.file.type && item.file.type.indexOf('image/') === 0) {
      const url = URL.createObjectURL(item.file);
      thumb.src = url;
      thumb.alt = item.file.name;
      // revoke objectURL when image loads to free memory
      thumb.onload = () => URL.revokeObjectURL(url);
    } else {
      // non-image: show placeholder icon
      thumb.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(
        `<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24"><rect width="100%" height="100%" fill="#f0f0f0"/><text x="50%" y="50%" font-size="10" text-anchor="middle" fill="#999" dy=".35em">DOC</text></svg>`
      );
      thumb.alt = 'doc';
    }
    row.appendChild(thumb);

    // meta + caption
    const meta = document.createElement('div');
    meta.className = 'attach-meta';

    const name = document.createElement('div');
    name.className = 'attach-filename';
    name.textContent = item.file.name;
    meta.appendChild(name);

    const caption = document.createElement('textarea');
    caption.className = 'form-control form-control-sm attach-caption';
    caption.placeholder = 'Add a caption (optional)';
    caption.value = item.caption || '';
    // keep caption in item
    caption.addEventListener('input', function () {
      item.caption = this.value;
    });
    meta.appendChild(caption);

    row.appendChild(meta);

    // remove button
    const actions = document.createElement('div');
    actions.className = 'attach-actions';
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-danger btn-sm';
    btn.title = 'Remove';
    btn.innerHTML = '<i class="fa fa-trash"></i>';
    btn.addEventListener('click', function () {
      // remove from selectedFiles
      selectedFiles = selectedFiles.filter(f => f.id !== item.id);
      // remove node
      row.remove();
      syncPreviewArea();
    });
    actions.appendChild(btn);
    row.appendChild(actions);

    return row;
  }

  // Keep a simple preview below composer (small filenames) - reuse attachPreviewWrap
  function syncPreviewArea() {
    if (!attachPreviewWrap) return;
    attachPreviewWrap.innerHTML = '';
    if (!selectedFiles.length) return;
    selectedFiles.forEach(it => {
      const pill = document.createElement('div');
      pill.className = 'badge bg-light text-dark border';
      pill.style.marginRight = '6px';
      pill.style.display = 'inline-flex';
      pill.style.alignItems = 'center';
      pill.style.gap = '6px';
      pill.innerHTML = `<span style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;">${it.file.name}</span>`;
      const rm = document.createElement('button');
      rm.type = 'button';
      rm.className = 'btn btn-sm btn-link text-danger p-0';
      rm.style.marginLeft = '6px';
      rm.innerHTML = '<i class="fa fa-times"></i>';
      rm.addEventListener('click', function () {
        selectedFiles = selectedFiles.filter(f => f.id !== it.id);
        // remove from modal list if visible
        const node = attachList.querySelector(`[data-tmp-id="${CSS.escape(it.id)}"]`);
        if (node) node.remove();
        syncPreviewArea();
      });
      pill.appendChild(rm);
      attachPreviewWrap.appendChild(pill);
    });
  }

  // When file input changes, capture files and open modal
  if (filesInput) {
    filesInput.addEventListener('change', function (e) {
      const files = Array.from(filesInput.files || []);
      if (!files.length) return;
      // add to selectedFiles and create IDs
      files.forEach(f => {
        const id = 'f' + Date.now().toString(36) + Math.floor(Math.random()*1000).toString(36);
        selectedFiles.push({ file: f, id: id, caption: '' });
      });
      // populate modal list
      attachList.innerHTML = '';
      selectedFiles.forEach(it => attachList.appendChild(createAttachRow(it)));
      syncPreviewArea();
      // show modal
      if (attachModal) attachModal.show();
      else attachModalEl.style.display = 'block';
      // clear file input to allow selecting same file later if needed
      filesInput.value = '';
    });
  }

  // Send handler: will post each file + caption in a single FormData (server accepts multiple)
  attachSendBtn.addEventListener('click', async function () {
    if (!selectedFiles.length) return;
    attachSendBtn.disabled = true;
    attachSendBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';

    // Build FormData following your existing send API expectations
    const fd = new FormData();
    if (contactId) fd.append('contact_id', contactId);
    fd.append('contact_type', 'Customer');
    // indicate this is a media-only send (server already accepts media[] previously)
    fd.append('is_media', '1');

    // append files and add captions as parallel fields (caption_<index>)
    selectedFiles.forEach((it, idx) => {
      fd.append('media[]', it.file, it.file.name);
      // server: caption[] or caption_<idx> — we add caption[] to be generic
      fd.append('caption[]', it.caption || '');
    });

    // optimistic UI: append temp outgoing nodes (one per file)
    const tmpIds = selectedFiles.map(it => 'tmp-' + it.id);
    selectedFiles.forEach((it, idx) => {
      const tmpMsg = {
        id: tmpIds[idx],
        message_body: it.caption || ('[' + it.file.name + ']'),
        date_added: (new Date()).toISOString()
      };
      appendOutgoingMessage(tmpMsg, { temp: true });
    });

    // send to server
    try {
      const resRaw = await fetch(sendEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' });
      const text = await resRaw.text();
      let res;
      try { res = JSON.parse(text); } catch (e) { res = { error: 'Invalid JSON', raw: text }; }

      // Res may return data for all files — your API may return message ids array or single id.
      // We attempt to update temp nodes if server returns messageId or ids[].
      // Common response shapes:
      // { status:200, messageIds: ['id1','id2'], ... } OR { status:200, id: 'id1' } (single)
      let messageIds = [];
      if (Array.isArray(res.messageIds)) messageIds = res.messageIds;
      else if (Array.isArray(res.ids)) messageIds = res.ids;
      else if (res.messageId) messageIds = [res.messageId];
      else if (res.id) messageIds = [res.id];

      // if server returned equal or more ids we map them to tmpIds
      if (messageIds.length >= tmpIds.length) {
        for (let i=0;i<tmpIds.length;i++) {
          try {
            updateTempNodeWithServerResponse(tmpIds[i], messageIds[i], res) || null;
          } catch (e) { console.warn('updateTempNodeWithServerResponse failed', e); }
        }
      } else {
        // fallback: server returned single id (or none) -> update first temp node, append others as final nodes
        if (messageIds.length === 1) {
          try { updateTempNodeWithServerResponse(tmpIds[0], messageIds[0], res); } catch(e){/*ignore*/}

          // append any remaining as normal nodes using server response date or now
          for (let j=1;j<tmpIds.length;j++) {
            appendOutgoingMessage({
              id: 'id-' + Date.now() + '-' + j,
              message_body: selectedFiles[j].caption || ('[' + selectedFiles[j].file.name + ']'),
              date_added: (new Date()).toISOString()
            }, { temp: false });
          }
        } else {
          // no ids returned: mark temp nodes as pending->failed or keep them; we'll mark failed
          tmpIds.forEach(tid => {
            const sel = `[data-temp-id="${CSS.escape(tid)}"]`;
            const el = document.querySelector(sel);
            if (el) el.classList.remove('pending'), el.classList.add('failed');
          });
          alert(res.error || res.message || 'Send reported error. See console.');
          console.error('Send response:', res);
        }
      }

      // clear selection and modal
      selectedFiles = [];
      attachList.innerHTML = '';
      if (attachPreviewWrap) attachPreviewWrap.innerHTML = '';
      if (attachModal) attachModal.hide();
      else attachModalEl.style.display = 'none';
    } catch (err) {
      console.error('Attachment send failed', err);
      tmpIds.forEach(tid => {
        const sel = `[data-temp-id="${CSS.escape(tid)}"]`;
        const el = document.querySelector(sel);
        if (el) el.classList.remove('pending'), el.classList.add('failed');
      });
      alert('Failed to send attachments. See console for details.');
    } finally {
      attachSendBtn.disabled = false;
      attachSendBtn.innerHTML = 'Send';
      syncPreviewArea();
    }
  });

  // Cancel button clears selection
  attachCancelBtn.addEventListener('click', function () {
    // just hide modal and keep selection in preview area (optional: clear)
    if (attachModal) attachModal.hide();
    // don't clear selectedFiles so user can re-open modal, but you can clear if desired:
    // selectedFiles = []; syncPreviewArea();
  });

}); // DOMContentLoaded
</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>