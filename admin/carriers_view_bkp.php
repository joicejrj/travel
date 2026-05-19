<?php
// agent/dashboard.php — stylish dashboard with modal snooze and company fallback
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

$uid = $CURRENT_USER_ID;

// short esc helper if not present
if (!function_exists('esc')) {
    function esc($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
}

// safe human date helper (fallback instead of human_dt)
if (!function_exists('safe_human_dt')) {
    function safe_human_dt($dt) {
        if (empty($dt)) return '-';
        $ts = strtotime($dt);
        if ($ts === false) return esc($dt);
        return date('d M Y, h:i A', $ts);
    }
}

/* -------------------------
   Main logic (original file content)
--------------------------*/

// Default form values
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$name = $company = $phones = $phone = $whatsapp = $email = $address = $city = $state = $country = $services = $google_rating = $website = $type = $source = $photo = $agent_id = $user_name = $fil_emails = $fil_domains = '';

$is_edit = false; //($id > 0);

// If editing, fetch existing supplier
if ($id > 0) {
    if ($stmt = $mysqli->prepare("SELECT id, name, company, phones, phone, whatsapp, email, address, city, state, country, services, google_rating, website, type, source, photo, agent_id, fil_domains, fil_emails, created_at FROM carriers WHERE id = ?")) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $is_edit  = true;
            $name     = $row['name'];
            $company  = $row['company'];
            $phones   = $row['phones'];
            $phone    = $row['phone'];
            $whatsapp = $row['whatsapp'];
            $email    = $row['email'];
            $address  = $row['address'];
            $city     = $row['city'];
            $state    = $row['state'];
            $country  = $row['country'];
            $services = $row['services'];
            $google_rating = $row['google_rating'];
            $website  = $row['website'];
            $type     = $row['type'];
            $source   = $row['source'];
            $photo    = $row['photo'];
            $agent_id = $row['agent_id'];
            if($agent_id!='') {
                // safe get user name; guard usage of $db
                if (isset($db) && method_exists($db, 'get')) {
                    $getu = $db->get('people',array('id'=>$agent_id),'name');
                    $user_name = $getu ? ($getu->name ?? '') : '';
                } else {
                    $user_name = '';
                }
            }
            $fil_domains  = $row['fil_domains'];
            $fil_emails  = $row['fil_emails'];
            $created_at = $row['created_at'] ?? '';
        }
        $stmt->close();
    }

    if (isset($db) && method_exists($db, 'get')) {
        $getc = $db->get('carriers_contacts',array('#all'=>1,'supplier_id'=>$id));
    } else {
        $getc = (object)['data' => []];
    }
}

// prevent add here
if(!$is_edit) {
    // Agent portal: redirect back to carriers list
    header('Location: index.php?page=carriers');
    exit;
}
?>
  <style>
    html, body {
  height: 100%;
  margin: 0;
}

/* use min-height so footer can appear after content */
.page-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* content grows and becomes scrollable if long */
.content {
  flex: 1 1 auto;
  overflow: auto;   /* allow scrolling inside content area */
}

/* keep .section-scroll internal scrollbars as before */
.section-scroll {
  overflow-y: auto;
}

/* (Optional) keep the footer visible visually: */
footer {
  flex-shrink: 0;
}
    .header {
      padding: 15px;
      background: #0d6efd;
      color: #fff;
    }
    .col-section {
      height: 100%;
      display: flex;
      flex-direction: column;
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
  <div class="page-container">

    <!-- Content -->
    <div class="content container-fluid">
      <div class="row h-100 jrow">
        <!-- Left Column -->
        <div class="col-md-3 col-section jleft">
          <div class="section-scroll">

            <!-- Carrier Info -->
            <div class="card shadow-sm rounded-3 mb-3">
              <div class="card-header bg-white border-0 d-flex align-items-center">
                <i class="fa fa-user me-2"></i>
                <strong>Carrier Info</strong>
                &nbsp;
                <span class="badge bg-info" id="infoEdit" style="float: right;"><i class="fa fa-edit"></i> Edit</span>
                <span class="badge bg-success" id="infoSave" style="float: right; display: none;"><i class="fa fa-save"></i> Save</span>
              </div>
              <div class="card-body" id="info">

                <!-- Form Wrapper -->
                <div id="info-wrapper" class="section-scroll" style="display: none; max-height: 40vh; padding: 0;">
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
                    if (isset($db) && method_exists($db, 'get')) {
                        $geta = $db->get('people',array('#all'=>1),'id,name');
                        foreach ($geta->data as $key => $usa) {
                ?>
                      <option value="<?=$usa->id?>"  <?= ($agent_id ?? '') == $usa->id  ? 'selected' : '' ?>><?=$usa->name?></option>
                <?php
                        }
                    }
                ?>
                    </select>
                  </div>
                  <div class="mb-2">
                      <input type="file" class="form-control form-control-xs rounded-pill" 
                             name="photo" id="photo" accept="image/*">
                      <!-- Image preview -->
                      <div id="photoPreview" style="margin-top: 5px;">
                        <img id="previewImg" src="" alt="Preview" style="max-width: 100px; max-height: 100px; border-radius: 6px; display: none; object-fit: cover;">
                      </div>
                    </div>
                  <div class="mb-2">
                    <textarea class="form-control form-control-xs" style="border-radius: 6px !important; height: 45px;" name="address" id="address" placeholder="Address"><?= htmlspecialchars($address ?? '') ?></textarea>
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
                      <option value="active"  <?= ($type ?? '') === 'active'  ? 'selected' : '' ?>>Active</option>
                      <option value="inactive"    <?= ($type ?? '') === 'inactive'    ? 'selected' : '' ?>>Inactive</option>
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
                </div>

                <!-- Display Saved Details (PHP) -->
                <div id="infoDisplay" style="padding: 10px; max-height: 40vh; border: 1px solid #ddd; border-radius: 6px; margin-top: 10px;" class="section-scroll">
                    <!-- Show uploaded photo if available -->
                    <div class="mb-2 text-center" id="photod" style="<?=(!empty($photo) && file_exists("uploads/carriers/" . $photo))?'':'display:none;'?>">
                      <img src="<?= "uploads/carriers/".$photo ?>" id="photodimg" alt="Carrier Photo" style="max-width: 100px; max-height: 100px; border-radius: 50%; object-fit: cover;">
                    </div>
                  <div class="mb-2">
                    <input type="text" class="form-control form-control-xs rounded-pill" id="companyd" placeholder="Name.." value="<?= htmlspecialchars($company ?? '') ?>" readonly>
                  </div>
                  <div class="mb-2">
                    <input type="text" class="form-control form-control-xs rounded-pill" id="named" placeholder="Primary Contact Name.." value="<?= htmlspecialchars($name ?? '') ?>" readonly>
                  </div>
                  <div class="mb-2">
                    <input type="text" class="form-control form-control-xs rounded-pill" id="phoned" placeholder="Primary Contact Phone.." value="<?= htmlspecialchars($phone ?? '') ?>" readonly>
                  </div>
                  <div class="mb-2">
                    <input type="text" class="form-control form-control-xs rounded-pill" id="whatsappd" placeholder="Primary Contact Whatsapp.." value="<?= htmlspecialchars($whatsapp ?? '') ?>" readonly>
                  </div>
                  <div class="mb-2">
                    <input type="email" class="form-control form-control-xs rounded-pill" id="emaild" placeholder="Primary Contact Email.." value="<?= htmlspecialchars($email ?? '') ?>" readonly>
                  </div>
                  <div class="mb-2">
                    <input type="text" class="form-control form-control-xs rounded-pill" id="agent_id" placeholder="Attached to.." value="<?= htmlspecialchars($user_name ?? '') ?>" readonly>
                  </div>
                  <div class="mb-2">
                    <textarea class="form-control form-control-xs" placeholder="Address.." id="addressd" style="border-radius: 6px !important; height: 45px;" readonly><?= htmlspecialchars($address ?? '') ?></textarea>
                  </div>
                  <div class="mb-2">
                    <input type="text" class="form-control form-control-xs rounded-pill" id="sourced" placeholder="Source.." value="<?= htmlspecialchars($source ?? '') ?>" readonly>
                  </div>
                  <div class="mb-2">
                    <input type="text" class="form-control form-control-xs rounded-pill" id="typed" placeholder="Status.." value="<?= htmlspecialchars($type ?? '') ?>" readonly>
                  </div>
                  <div class="mb-2">
                    <input type="text" class="form-control form-control-xs rounded-pill" id="cityd" placeholder="City.." value="<?= htmlspecialchars($city ?? '') ?>" readonly>
                  </div>
                  <div class="mb-2">
                    <input type="text" class="form-control form-control-xs rounded-pill" id="stated" placeholder="State.." value="<?= htmlspecialchars($state ?? '') ?>" readonly>
                  </div>
                  <div class="mb-2">
                    <input type="text" class="form-control form-control-xs rounded-pill" id="countryd" placeholder="Country.." value="<?= htmlspecialchars($country ?? '') ?>" readonly>
                  </div>
                  <div class="mb-2">
                    <input type="text" class="form-control form-control-xs rounded-pill" id="websited" placeholder="Website.." value="<?= htmlspecialchars($website ?? '') ?>" readonly>
                  </div>
                  <div class="mb-2">
                    <input type="text" class="form-control form-control-xs rounded-pill" id="servicesd" placeholder="Services.." value="<?= htmlspecialchars($services ?? '') ?>" readonly>
                  </div>
                </div>

              </div>
            </div>

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
                  <div class="d-flex justify-content-between align-items-center">
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
                                           name="contact_name[]" placeholder="Name" data-id="<?= $cont->id ?? 0 ?>" data-field="name" value="<?= htmlspecialchars($cont->name) ?>">
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-xs rounded-pill" 
                                           name="contact_phone[]" placeholder="Phone" data-id="<?= $cont->id ?? 0 ?>" data-field="phone" value="<?= htmlspecialchars($cont->phone ?? '') ?>">
                                </div>
                            </div>

                            <div class="row g-1 mt-1">
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-xs rounded-pill" 
                                           name="contact_whatsapp[]" placeholder="Whatsapp" data-id="<?= $cont->id ?? 0 ?>" data-field="whatsapp" value="<?= htmlspecialchars($cont->whatsapp ?? '') ?>">
                                </div>
                                <div class="col-6">
                                    <input type="email" class="form-control form-control-xs rounded-pill" 
                                           name="contact_email[]" placeholder="Email" data-id="<?= $cont->id ?? 0 ?>" data-field="email" value="<?= htmlspecialchars($cont->email ?? '') ?>">
                                </div>
                            </div>

                            <div class="row g-1 mt-1 align-items-center">
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-xs rounded-pill" 
                                           name="contact_designation[]" placeholder="Designation" data-id="<?= $cont->id ?? 0 ?>" data-field="designation" value="<?= htmlspecialchars($cont->designation ?? '') ?>">
                                </div>
                                <div class="col-6 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input type="hidden" class="primary-hidden" name="contact_primary[]" value="0">
                                        <input class="form-check-input primary-contact" type="checkbox" value="1" data-id="<?= $cont->id ?? 0 ?>" data-field="main" id="contact_primary_<?=$key?>" <?= isset($cont->main) && $cont->main==1 ? 'checked' : '' ?>>
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

        <!-- Center Column -->
        <div class="col-md-6 col-section jcenter">
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
                    <input type="hidden" name="uname" value="<?=isset($_SESSION['user'])?$_SESSION['user']['name']:'aleena'?>">
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
                        <input type="text" class="form-control form-control-xs rounded-pill" name="reminder_time" id="reminder-time" placeholder="Select time" readonly required>
                        <button type="button" id="open-timepicker" class="btn btn-outline-secondary btn-xs rounded-pill">
                          <i class="fa fa-clock"></i>
                        </button>
                      </div>

                      <!-- Custom Time Picker -->
                      <div id="timepicker-popup" class="timepicker-popup shadow">
                        <div class="timepicker-clock">
                          <div class="timepicker-center"></div>
                          <div class="timepicker-hand" id="clock-hand"></div>
                          <div id="clock-numbers" class="timepicker-numbers"></div>
                        </div>
                        <div class="text-center mt-2">
                          <button type="button" id="timepicker-ok" class="btn btn-success btn-xs rounded-pill px-3 py-1">OK</button>
                          <button type="button" id="timepicker-cancel" class="btn btn-light btn-xs rounded-pill px-3 py-1">Cancel</button>
                        </div>
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

            <style>
            .timepicker-popup {
              position: absolute;
              top: 100%;
              right: 0;
              z-index: 10;
              background: #fff;
              border-radius: 10px;
              padding: 10px 15px;
              display: none;
            }
            .timepicker-clock {
              position: relative;
              width: 150px;
              height: 150px;
              border: 2px solid #007bff;
              border-radius: 50%;
              margin: 0 auto;
            }
            .timepicker-center {
              position: absolute;
              top: 50%;
              left: 50%;
              width: 10px;
              height: 10px;
              background: #007bff;
              border-radius: 50%;
              transform: translate(-50%, -50%);
            }
            .timepicker-hand {
              position: absolute;
              top: 50%;
              left: 50%;
              width: 2px;
              height: 60px;
              background: #007bff;
              transform-origin: bottom center;
              transform: rotate(0deg) translateX(-50%);
              transition: 0.2s ease;
            }
            .timepicker-numbers {
              position: absolute;
              width: 100%;
              height: 100%;
              top: 0;
              left: 0;
            }
            .timepicker-number {
              position: absolute;
              width: 30px;
              height: 30px;
              text-align: center;
              line-height: 30px;
              border-radius: 50%;
              font-size: 12px;
              cursor: pointer;
              color: #007bff;
            }
            .timepicker-number.active {
              background: #007bff;
              color: #fff;
            }
            </style>


            <!-- Email filters Form -->
            <div class="card shadow-sm rounded-3 mb-3">
              <div class="card-header bg-white border-0 d-flex align-items-center">
                <i class="fa fa-at me-2"></i>
                <strong>Add Email Filters</strong>
              </div>
              <div class="card-body">
                <form id="new-contact-form">
                  <div class="row g-2" id="email_filters">
                    <div class="col-md-6">
                        <label for="domains">Filter Domains</label>
                      <input type="text" class="form-control form-control-xs rounded-pill" name="fil_domains" placeholder="swebsite.com,site.com (comma seperated)" value="<?=$fil_domains?>">
                    </div>
                    <div class="col-md-6">
                        <label for="domains">Filter Emails</label>
                      <input type="text" class="form-control form-control-xs rounded-pill" name="fil_emails" placeholder="abcd123@website.com,xyz354@site.com (comma seperated)" value="<?=$fil_emails?>">
                    </div>
                  </div>
                </form>
              </div>
            </div>


          </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-3 col-section jright">
          <div class="section-scroll">
            
  
            <!-- Previous Notes -->
            <div class="card shadow-sm mb-3">
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
                  <button id="load-more-notes" class="btn btn-light btn-xs w-100 mt-2">Load More</button>
                </div>
              </div>
            </div>

            <!-- Previous Reminders -->
            <div class="card shadow-sm mb-3">
              <div class="card-header d-flex justify-content-between align-items-center bg-white" 
                   data-bs-toggle="collapse" data-bs-target="#previousReminders" style="cursor:pointer;">
                <span><i class="fa fa-clock me-1"></i> <strong>Previous Reminders</strong></span>
                <i class="fa fa-chevron-down"></i>
              </div>

              <div id="previousReminders" class="collapse show">
                <div class="" id="first-reminder-form-container"></div>
                <div class="card-body p-1" style="max-height: 300px; scrollbar-width: thin; overflow-y:auto;" id="reminders-container">
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
                    });

                    setCarrier();
                } else {
                    alert("Couldn't refrsh recruiter info");   
                }
              }, "json");
        }
        function setCarrier() {
            // placeholder for future state updates
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

        const wrapper = document.getElementById('contacts-wrapper');
        wrapper.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-row');
            if (!removeBtn) return;

            if (!confirm("Are you sure you want to delete this contact?")) return;

            const block = removeBtn.closest('.contact-block');
            const inputWithId = block.querySelector('input[data-id], textarea[data-id]');
            const contactId = inputWithId?.dataset.id || 0;

            if (contactId > 0) {
                $.post("public/ajax/carriers_contacts_delete.php", { contact_id: contactId }, function(res) {
                    if (res.success) {
                        block.remove();
                        alert("Deleted contact");
                        reloadContacts();
                    } else {
                        alert(res.msg || "Failed to delete contact");
                    }
                }, "json");
            } else {
                block.remove();
                if (!wrapper.querySelector(".contact-block")) {
                    wrapper.innerHTML = `
                      <div id="no-contacts-message" class="text-center text-muted small">
                        No contacts available.
                      </div>`;
                }
            }
        });

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
                        $input.addClass("is-valid");
                        setTimeout(() => $input.removeClass("is-valid"), 1500);

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
                        $msg.fadeIn(200).delay(1500).fadeOut(500);
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
        const callsLimit = 2; // fetch 2 calls at a time

        function formatCallHtml(call) {
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
                        callsOffset += res.logs.length;

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

        // Initial load (optional)
        // loadCalls(true);

    });
</script>


<script>
(function(){
  const openBtn = document.getElementById('open-timepicker');
  const popup = document.getElementById('timepicker-popup');
  const clockNumbers = document.getElementById('clock-numbers');
  const hand = document.getElementById('clock-hand');
  const okBtn = document.getElementById('timepicker-ok');
  const cancelBtn = document.getElementById('timepicker-cancel');
  const input = document.getElementById('reminder-time');

  let selectedHour = null;
  let selectedMinute = null;
  let selectingHour = true;

  // Render hour numbers (1–12)
  for (let i = 1; i <= 12; i++) {
    const el = document.createElement('div');
    el.className = 'timepicker-number';
    const angle = (i / 12) * 2 * Math.PI;
    const x = 65 + 60 * Math.sin(angle);
    const y = 65 - 60 * Math.cos(angle);
    el.style.left = x + 'px';
    el.style.top = y + 'px';
    el.textContent = i;
    el.addEventListener('click', () => {
      document.querySelectorAll('.timepicker-number').forEach(n => n.classList.remove('active'));
      el.classList.add('active');
      if (selectingHour) {
        selectedHour = i;
        selectingHour = false;
        renderMinutes();
      } else {
        selectedMinute = i * 5;
        updateTime();
      }
    });
    clockNumbers.appendChild(el);
  }

  function renderMinutes() {
    clockNumbers.innerHTML = '';
    for (let i = 0; i < 60; i += 5) {
      const el = document.createElement('div');
      el.className = 'timepicker-number';
      const angle = (i / 60) * 2 * Math.PI;
      const x = 65 + 60 * Math.sin(angle);
      const y = 65 - 60 * Math.cos(angle);
      el.style.left = x + 'px';
      el.style.top = y + 'px';
      el.textContent = i.toString().padStart(2, '0');
      el.addEventListener('click', () => {
        document.querySelectorAll('.timepicker-number').forEach(n => n.classList.remove('active'));
        el.classList.add('active');
        selectedMinute = i;
        updateTime();
      });
      clockNumbers.appendChild(el);
    }
  }

  function updateTime() {
    const hour = selectedHour ? selectedHour.toString().padStart(2, '0') : '00';
    const minute = selectedMinute !== null ? selectedMinute.toString().padStart(2, '0') : '00';
    input.value = `${hour}:${minute}`;
    closePicker();
  }

  function closePicker() {
    popup.style.display = 'none';
    selectingHour = true;
    clockNumbers.innerHTML = '';
    // Re-render hours when reopening
    for (let i = 1; i <= 12; i++) {
      const el = document.createElement('div');
      el.className = 'timepicker-number';
      const angle = (i / 12) * 2 * Math.PI;
      const x = 65 + 60 * Math.sin(angle);
      const y = 65 - 60 * Math.cos(angle);
      el.style.left = x + 'px';
      el.style.top = y + 'px';
      el.textContent = i;
      el.addEventListener('click', () => {
        document.querySelectorAll('.timepicker-number').forEach(n => n.classList.remove('active'));
        el.classList.add('active');
        if (selectingHour) {
          selectedHour = i;
          selectingHour = false;
          renderMinutes();
        } else {
          selectedMinute = i * 5;
          updateTime();
        }
      });
      clockNumbers.appendChild(el);
    }
  }

  openBtn.addEventListener('click', () => popup.style.display = 'block');
  cancelBtn.addEventListener('click', closePicker);
  okBtn.addEventListener('click', () => {
    if (selectedHour !== null && selectedMinute !== null) updateTime();
    else closePicker();
  });

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
$(document).ready(function() {
    let remindersOffset = 0;
    const remindersLimit = 10;

    const supplierId = <?= $id ?? 0 ?>; // current supplier ID

    function loadReminders(refresh = false) {
            remindersOffset = 0;
            $("#reminders-container").empty();
            $("#first-reminder-form-container").empty();
        $.post("public/ajax/carriers_reminder_fetch.php",
            { supplier_id: supplierId, offset: remindersOffset, limit: remindersLimit },
            function(res) {
                if (res.reminders && res.reminders.length > 0) {
                    const container = $("#reminders-container");

                    res.reminders.forEach((reminder, index) => {
                        if (remindersOffset === 0 && index === 0) {
                            const firstFormHtml = `
                                <form id="first-reminder-form" class="mb-3 p-3 border rounded" data-id="${reminder.id}">
                                    <div class="mb-2">
                                        <div class="d-flex flex-wrap gap-1">
                                          <input type="radio" class="btn-check" name="type" id="type1-callback" value="Callback" autocomplete="off" ${reminder.type == "Callback" ? "checked" : ""}>
                                          <label class="btn btn-outline-primary btn-xs rtype rounded-pill px-2 py-1" for="type1-callback">Callback</label>

                                          <input type="radio" class="btn-check" name="type" id="type1-followup" value="Follow-up" autocomplete="off" ${reminder.type == "Follow-up" ? "checked" : ""}>
                                          <label class="btn btn-outline-primary btn-xs rtype rounded-pill px-2 py-1" for="type1-followup">Follow-up</label>

                                          <input type="radio" class="btn-check" name="type" id="type1-sendemail" value="Send Email" autocomplete="off" ${reminder.type == "Send Quote" ? "checked" : ""}>
                                          <label class="btn btn-outline-primary btn-xs rtype rounded-pill px-2 py-1" for="type1-sendemail">Send Email</label>

                                          <input type="radio" class="btn-check" name="type" id="type1-other" value="Other" autocomplete="off" ${reminder.type == "Other" ? "checked" : ""}>
                                          <label class="btn btn-outline-primary btn-xs rtype rounded-pill px-2 py-1" for="type1-other">Other</label>
                                        </div>
                                      </div>

                                    <div class="mb-2">
                                        <input type="date" class="form-control form-control-xs rounded-pill" name="reminder_date"
                                               value="${reminder.reminder_at.split(' ')[0]}">
                                    </div>

                                    <div class="mb-2">
                                        <input type="time" class="form-control form-control-xs rounded-pill" name="reminder_time"
                                               value="${reminder.reminder_at.split(' ')[1].slice(0,5)}">
                                    </div>

                                    <div class="mb-2">
                                        <textarea class="form-control form-control-xs rounded-3" name="notes" rows="2"
                                                  placeholder="Write notes...">${reminder.note || ''}</textarea>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <button type="submit" class="btn btn-success btn-xs rounded-pill">Update Reminder</button>
                                    </div>
                                    <div id="first_rem"></div>
                                </form>
                                `;


                            $("#first-reminder-form-container").append(firstFormHtml);
                        } else {
                            container.append(formatReminderList(reminder));
                        }
                    });

                    remindersOffset += res.reminders.length;

                    if (res.reminders.length < remindersLimit) {
                        $("#load-more-reminders").prop("disabled", true).hide();
                    }
                } else {
                    $("#load-more-reminders").prop("disabled", true).text("No reminders");
                }
            }, "json"
        );
    }

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
               loadReminders(); // refresh reminders list
           } else {
               $msg.text(res.error || "Failed to add reminder")
                   .removeClass("text-success")
                   .addClass("text-danger")
                   .fadeIn(200).delay(2000).fadeOut(500);
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
$(document).on("submit", "#first-reminder-form", function(e) {
    e.preventDefault();

    const $form = $(this);
    const reminderId = $form.data("id"); // Make sure you set data-id="${reminder.id}" when generating the form
    const supplierId = <?= $id ?? 0 ?>; // your global supplierId variable

    const formData = {
        reminder_id: reminderId,
        supplier_id: supplierId,
        type: $form.find("input[name='type']:checked").val(),
        reminder_date: $form.find("input[name='reminder_date']").val(),
        reminder_time: $form.find("input[name='reminder_time']").val(),
        notes: $form.find("textarea[name='notes']").val()
    };

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
                
                loadReminders();
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

            // setCarrier();
        } else {
            alert("Couldn't refrsh recruiter info");   
        }
      }, "json");
}
document.addEventListener('DOMContentLoaded', () => {
  const editBtn = document.getElementById('infoEdit');
  const saveBtn = document.getElementById('infoSave');
  const infoWrapper = document.getElementById('info-wrapper');
  const infoDisplay = document.getElementById('infoDisplay');

  // Show the edit form and hide the display div
  editBtn.addEventListener('click', () => {
    infoWrapper.style.display = 'block';
    infoDisplay.style.display = 'none';
    editBtn.style.display = 'none';
    saveBtn.style.display = 'inline-block';
  });

  // Show the display div and hide the edit form
  saveBtn.addEventListener('click', () => {
    infoWrapper.style.display = 'none';
    infoDisplay.style.display = 'block';
    saveBtn.style.display = 'none';
    editBtn.style.display = 'inline-block';

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
                $input.addClass("is-valid");
                setTimeout(()=> $input.removeClass("is-valid"), 1500);
            },
            error: function(){
                console.error("Error updating recuiter info.");
                $input.addClass("is-invalid");
            }
        });
    }, 500); // adjust debounce delay as needed
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
