<?php
// bookings.php  — full updated page (version: ver1.2 + modal fixes)
// ... (header / endpoint block unchanged from your version)
if (isset($_GET['list_entities'])) {
    $type = $_GET['list_entities'];
    $map = [
        'customers'           => "customers",
        'employees'           => "employees",
        'recruiters'          => "recruiters",
        'suppliers'           => "suppliers",
        'contacts'            => "contacts",
        'customers_contacts'  => "customers_contacts",
        'customer_contacts'   => "customers_contacts"
    ];

    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/../config/db.php';

    if (!isset($map[$type])) {
        echo json_encode(['success' => false, 'error' => 'unknown_type']);
        exit;
    }

    $table = $map[$type];

    if ($type === 'customers_contacts' || $type === 'customer_contacts') {
        $customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
        if (!$customer_id) {
            echo json_encode(['success' => false, 'error' => 'missing_customer_id']);
            exit;
        }
        $sql = "SELECT id, COALESCE(name,'') AS name, COALESCE(email,'') AS email, COALESCE(phone,'') AS phone, COALESCE(designation,'') AS designation
                FROM `customers_contacts`
                WHERE customer_id = {$customer_id}
                ORDER BY name
                LIMIT 1000";
        $out = ['success' => false];
        if ($res = $mysqli->query($sql)) {
            $items = [];
            while ($r = $res->fetch_assoc()) $items[] = $r;
            $res->free();
            $out = ['success' => true, 'items' => $items];
        } else {
            $out = ['success' => false, 'error' => 'db_error', 'sql_error' => $mysqli->error];
        }
        echo json_encode($out);
        exit;
    }

    $cols = "id, COALESCE(name, '') AS name";
    $extraCols = [];
    $possible = ['phone','mobile','contact_no','email','company','company_name'];
    $resCols = $mysqli->query("SHOW COLUMNS FROM `{$table}`");
    if ($resCols) {
        $present = [];
        while ($c = $resCols->fetch_assoc()) $present[] = $c['Field'];
        foreach ($possible as $p) {
            if (in_array($p, $present)) $extraCols[] = $p;
        }
        $resCols->free();
    }
    if (!empty($extraCols)) {
        foreach ($extraCols as $ec) {
            if (in_array($ec, ['mobile','contact_no'])) {
                $cols .= ", COALESCE(`{$ec}`,'') AS phone";
            } elseif (in_array($ec, ['company','company_name'])) {
                $cols .= ", COALESCE(`{$ec}`,'') AS company";
            } elseif ($ec === 'email') {
                $cols .= ", COALESCE(`{$ec}`,'') AS email";
            } elseif ($ec === 'phone') {
                $cols .= ", COALESCE(`{$ec}`,'') AS phone";
            }
        }
    }

    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000;
    $limit = ($limit > 0 && $limit <= 5000) ? $limit : 1000;

    $sql = "SELECT {$cols} FROM `{$table}` ORDER BY name LIMIT {$limit}";
    $out = ['success' => false];
    if ($res = $mysqli->query($sql)) {
        $items = [];
        while ($r = $res->fetch_assoc()) $items[] = $r;
        $res->free();
        $out = ['success' => true, 'items' => $items];
    } else {
        $out = ['success' => false, 'error' => 'db_error', 'sql_error' => $mysqli->error];
    }
    echo json_encode($out);
    exit;
}

// --- normal page boot (no list_entities in query) ---
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

$people = [];
$res = $mysqli->query("SELECT id, name FROM people ORDER BY name");
if ($res) { while($r = $res->fetch_assoc()) $people[] = $r; $res->free(); }

$channels = [];
$res = $mysqli->query("SELECT id, name FROM channels ORDER BY name");
if ($res) { while($r = $res->fetch_assoc()) $channels[] = $r; $res->free(); }

$contact_types = [];
$res = $mysqli->query("SELECT id, name FROM contact_types ORDER BY name");
if ($res) { while($r = $res->fetch_assoc()) $contact_types[] = $r; $res->free(); }

$scenarios = [];
$res = $mysqli->query("SELECT id, name FROM bookings_types ORDER BY name");
if ($res) { while($r = $res->fetch_assoc()) $scenarios[] = $r; $res->free(); }

$self = basename($_SERVER['PHP_SELF']);
?>

<!-- ===== page styles (kept and extended) ===== -->
<style>
:root{--bg:#f4f5f7;--card:#fff;--muted:#6b7280;--accent:#2563eb;--line:#e5e7eb}
body { background: var(--bg); }

/* layout centers the single right-panel */
.layout {
  display: flex;
  justify-content: center;
  padding: 32px 16px;
  box-sizing: border-box;
}

/* main panel */
.right-panel {
  background: var(--card);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 6px 20px rgba(0,0,0,.06);
  width: 100%;
  max-width: 880px;
  box-sizing: border-box;
  margin: 0 auto;
}

/* control styles */
.filters,.summary-bar{background:var(--card);border-radius:6px;padding:10px 12px;box-shadow:0 1px 2px rgba(0,0,0,.06)}
.filters form{display:flex;flex-wrap:wrap;gap:8px 12px;align-items:flex-end}
.filters .field{display:flex;flex-direction:column;gap:4px;min-width:140px}
.filters label{font-size:12px;font-weight:600;color:var(--muted)}
.filters select,.filters input{padding:6px 8px;border-radius:4px;border:1px solid #d1d5db;font-size:13px}
.btn-primary{background:var(--accent);color:#fff;border:none;padding:8px 12px;border-radius:6px;cursor:pointer}
.btn-secondary{background:#e5e7eb;color:#111827;border:none;padding:8px 12px;border-radius:6px;cursor:pointer}
.btn-danger{background:#b91c1c;color:#fff;border:none;padding:8px 10px;border-radius:4px}
.interaction-list{background:var(--card);border-radius:6px;box-shadow:0 1px 2px rgba(0,0,0,.06);overflow:hidden}
.interaction-list-header{padding:8px 10px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;font-size:13px}
table{width:100%;border-collapse:collapse;font-size:13px;background:var(--card)}
thead{background:#f9fafb}
th,td{padding:8px 10px;border-bottom:1px solid #f3f4f6;text-align:left}
tr:hover td{background:#f3f4ff;cursor:pointer}
.chip{display:inline-block;padding:4px 8px;border-radius:999px;background:#f3f4f6;font-size:12px;color:#111827}
.right-panel h2{font-size:18px;margin:0 0 8px 0}
.right-panel .sub{font-size:13px;color:var(--muted);margin-bottom:8px}
.line{padding:8px 0;border-bottom:1px solid var(--line);display:block}
.field-group{display:flex;flex-direction:column;gap:8px}
.field-group label{font-size:13px;color:var(--muted);font-weight:600}
input[type="date"], input[type="time"], input[type="text"], textarea, select{font-size:13px;padding:10px;border-radius:8px;border:1px solid #d1d5db;box-sizing:border-box;width:100%}
textarea{min-height:100px;resize:vertical}
.btn-group{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.btn-as-select{padding:8px 12px;border-radius:8px;border:1px solid #d1d5db;background:var(--card);cursor:pointer;font-size:13px}
.btn-as-select.active{background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 0 0 4px rgba(37,99,235,.06)}
.btn-as-select.add{background:var(--card);color:var(--accent);border-color:var(--accent);font-weight:700}
.meta{font-size:12px;color:var(--muted);margin-top:6px}

/* modal baseline (kept) */
.modal-backdrop { position: fixed !important; inset: 0 !important; background: rgba(0,0,0,.45) !important; display: none; align-items: center; justify-content: center; z-index: 99999 !important; pointer-events: none; }
.modal-backdrop .modal { background: var(--card); width: calc(100% - 40px); max-width: 520px; border-radius: 8px; padding: 16px; box-shadow: 0 8px 30px rgba(0,0,0,.3); position: relative; z-index: 100000 !important; box-sizing: border-box; max-height: 70vh; overflow: auto; pointer-events: auto; }

/* customer picker modal styles */
.modal.customer-modal { width: 920px; max-width: calc(100% - 40px); min-width: 700px; height: 70vh; padding: 16px; border-radius: 10px; box-sizing: border-box; background:#fff; box-shadow: 0 12px 40px rgba(0,0,0,.35); }
#custList .cust-item { display:block; padding:10px; margin-bottom:8px; border-radius:8px; border:1px solid #eef2ff; background:#fff; cursor:pointer; text-align:left; }
#custList .cust-item .meta { font-size:13px; color:var(--muted); margin-top:6px; }
#custList .cust-item.active .meta { font-size:13px; color:#fff; margin-top:6px; }
#custList .cust-item.active { background:var(--accent); color:#fff; border-color:var(--accent); box-shadow:0 0 0 4px rgba(37,99,235,.06); }

.cust-item.active {
  background: #2563eb;
  color: #fff;
  border-color: #2563eb;
  box-shadow: 0 0 0 4px rgba(37,99,235,.06);
}

/* Ensure the meta line is readable on active (white text) */
.cust-item.active .meta {
  color: #fff !important;
}

/* Maintain spacing of meta and active font weight */
.cust-item .meta { font-size:13px; color:var(--muted); margin-top:6px; }

.customer-contact-row { padding:10px; border-radius:8px; border:1px solid #eef2ff; background:#fff; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; }
.customer-contact-row .meta { font-size:13px; color:var(--muted); margin-top:6px; }
@media(max-width:900px){ .modal.customer-modal{ width: calc(100% - 32px); min-width:auto; } }

.related-chip { background:#f3f4f6; padding:6px 10px; border-radius:999px; font-size:13px; }
.entity-picker-list { max-height:50vh; overflow:auto; padding:8px; }
.entity-item { padding:10px; border-radius:8px; border:1px solid #eef2ff; margin-bottom:8px; display:flex;align-items:center;justify-content:space-between; }
.entity-item .info { display:flex;flex-direction:column; }

.entity-picker-list { max-height:52vh; overflow:auto; padding:8px; }
.entity-item { padding:10px; border-radius:8px; border:1px solid #eef2ff; margin-bottom:8px; display:flex;align-items:center;justify-content:space-between; }
.entity-item .info { display:flex;flex-direction:column; }
.related-chip { background:#f3f4f6; padding:6px 10px; border-radius:999px; font-size:13px; display:inline-flex; align-items:center; gap:8px; }
.related-chip .remove { cursor:pointer; color:#ef4444; font-weight:700; margin-left:6px; }

/* ensure visible pointer events when backdrop shown */
.modal-backdrop[data-visible="true"] { display:flex !important; pointer-events: auto !important; }


/* ee modal scoped styles - keep namespaced to avoid conflicts */
.ee-modal-backdrop {
  position: fixed;
  inset: 0;
  display: none; /* shown by JS */
  align-items: center;
  justify-content: center;
  background: rgba(0,0,0,0.45);
  z-index: 260000;
}

.ee-modal {
  width: 920px;
  max-width: calc(100% - 40px);
  height: 76vh;
  max-height: 86vh;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 16px 48px rgba(11, 22, 40, 0.35);
  display: flex;
  overflow: hidden;
  font-family: inherit;
  box-sizing: border-box;
}

/* left column (employees) */
.ee-left {
  width: 48%;
  min-width: 280px;
  border-right: 1px solid #eef2ff;
  display: flex;
  flex-direction: column;
  padding: 16px;
  gap: 12px;
  box-sizing: border-box;
}

.ee-left .ee-search-row { display:flex; gap:8px; align-items:center;}
.ee-search-input {
  flex:1; padding:8px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;
}
.ee-search-btn { padding:8px 12px; border-radius:6px; cursor:pointer; }

/* employee list */
.ee-customer-list {
  flex:1; overflow:auto; padding-right:6px;
}
.ee-customer-item {
  padding:12px;
  border-radius:8px;
  border:1px solid transparent;
  cursor:pointer;
  margin-bottom:8px;
}
.ee-customer-item:hover { background:#fbfdff; }
.ee-customer-item.selected { background:#eef7ff; border-color:#cfefff; }

/* right column (scenarios + actions) */
.ee-right {
  width: 52%;
  min-width: 260px;
  display:flex;
  flex-direction:column;
  gap:12px;
  padding:16px;
  box-sizing:border-box;
}
.ee-right h3 { margin:0; font-size:15px; }
.ee-scenario-list { flex:1; overflow:auto; padding-top:6px; }

.ee-scenario-item {
  padding:10px;
  border-radius:8px;
  /*border:1px solid transparent;*/
  border:1px solid #eef2ff;
  margin-bottom:8px;
  cursor:pointer;
  font-weight:600;
}
.ee-scenario-item:hover { background:#fffaf0; }
.ee-scenario-item.selected { background:#fff3d6; border-color:#ffdca8; }

/* add scenario row */
.ee-add-row { display:flex; gap:8px; align-items:center; }
.ee-add-row input { flex:1; padding:8px; border-radius:6px; border:1px solid #d1d5db; }

/* footer actions */
.ee-footer {
  display:flex; gap:8px; justify-content:flex-end; align-items:center;
  margin-top:6px;
}
/* use project classes for consistent style */
.btn-primary.ee-btn { min-width:110px; }
.btn-secondary.ee-btn { min-width:90px; }

/* responsive */
@media (max-width: 800px) {
  .ee-modal { flex-direction: column; height: auto; max-height: 92vh; }
  .ee-left, .ee-right { width: 100%; min-width: auto; }
}

#existingCustomerModalBackdrop {
    position: fixed !important;
    inset: 0 !important;         /* top:0, left:0, right:0, bottom:0 */
    background: rgba(0,0,0,0.45); /* same as other modals */
    display: none;               /* will be shown by JS */
    align-items: center !important;
    justify-content: center !important;
    z-index: 999999 !important;
}

#existingCustomerModal {
    background: #fff;
    border-radius: 10px;
    max-width: 900px;
    width: 95%;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.25);
    position: relative;
}

#modalBackdrop .modal {
    max-width: 520px;
    width: calc(100% - 40px);
    max-height: calc(100vh - 80px);
    overflow-y: auto;
    height: auto;
}

</style>

<style>
  #incoming_calls_list {
    border: 1px solid #0088cc;
      border-radius: 4px;
      padding: 5px 10px 10px;
      padding-left: 15px;
      margin: 1em;
  }
  #incoming_calls {
    border-top: 1px solid #f5f5f5;
  }
  #missed_calls_list {
    border: 1px solid #0088cc;
      border-radius: 4px;
      padding: 5px 10px 10px;
      padding-left: 15px;
      margin: 1em;
  }
  #missed_calls {
    border-top: 1px solid #f5f5f5;
  }
  .call_items {
    margin-top: 0.4em;
  }
  .calls_list {
    display: none;
  }
  /*#calls_list li a, #myTabContent {
    padding: 0.5em 0.6em !important;
  }*/
  #calls_list .nav-link,
  #myTabContent {
    padding: 0.5em 0.6em;
  }

  /* Tabs container — force horizontal layout */
  #calls_list {
    /*display: flex !important;*/
    flex-wrap: nowrap;
    margin: 0;
  }

  /* Remove legacy block behavior */
  #calls_list .nav-item {
    display: inline-block;
  }

  /* Correct padding target for BS5 buttons */
  #calls_list .nav-link {
    padding: 0.5em 0.6em;
    width: auto;
    white-space: nowrap;
  }

  /* Hide content panels by default (not tabs) */
  .calls_list.tab-content {
    display: block;
  }

  .btn-xs {
    padding: 0.15em 0.30em;
    font-size: 0.8em;
  }
  .call-section {
    /*border: 1px solid #dcdcdc;*/
    border-radius: 6px;
  }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<div class="layout">
  <aside class="right-panel">
    <h2 id="Btitle">Add Booking</h2>

    <form id="detailForm" data-current-id="" novalidate>
      <!-- Step 1 -->
      <div class="wizard-step" id="wiz-step-1" aria-hidden="false">

        <div class="line" style="display: none;">
        <div style="display:flex;gap:12px">
          <div class="field-group" style="flex:1"><label for="detail-date">Date</label><input type="date" id="detail-date" name="date" required></div>
          <div class="field-group" style="flex:1"><label for="detail-time">Time</label><input type="time" id="detail-time" name="time" required></div>
        </div>
      </div>

        <div class="line1 mt-3 mb-2 row">

          <div class="col-md-12 call-section">
            <ul class="nav nav-tabs calls_list" id="calls_list" role="tablist" style="display: none;">
              <li class="nav-item">
                <button class="nav-link active tabs-div" id="li1" data-bs-toggle="tab" data-bs-target="#incoming_calls_list1" type="button" role="tab" aria-selected="true" onclick="tabClick('li1')">Recent Incoming Calls</button>
              </li>
              <li class="nav-item">
                <button class="nav-link tabs-div" id="li2" data-bs-toggle="tab" data-bs-target="#missed_calls_list1" type="button" role="tab" aria-selected="false" onclick="tabClick('li2')">Recent Missed Calls</button>
              </li>
            </ul>

            <div class="tab-content calls_list" id="myTabContent" style="margin-bottom:.4em">
              <div class="tab-pane fade show active" id="incoming_calls_list1" role="tabpanel">
                <div id="incoming_calls">No calls to show</div>
              </div>
              <div class="tab-pane fade" id="missed_calls_list1" role="tabpanel">
                <div id="missed_calls">No calls to show</div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="field-group"><label>Channel</label>
              <div class="btn-group" id="channelGroup"><input type="hidden" id="detail-channel" name="channel_id">
                <?php foreach($channels as $c) echo '<button type="button" class="btn-as-select" data-id="'.(int)$c['id'].'">'.htmlspecialchars($c['name']).'</button>'; ?>
                <button type="button" class="btn-as-select add" id="addChannelBtn">+ Add New</button>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="field-group"><label>Type</label>
              <div class="btn-group" id="itypeGroup"><input type="hidden" id="itype" name="itype" value="IN">
                <button type="button" class="btn-as-select active" data-id="IN">IN</button>
                <button type="button" class="btn-as-select" data-id="OUT">OUT</button>
              </div>
            </div>
          </div>
        </div>

        <div class="line">
          <div class="field-group"><label>Contact Type</label>
            <div class="btn-group" id="contactTypeGroup">
              <input type="hidden" id="detail-contact-type" name="contact_type_id">
              <button type="button" class="btn-as-select" data-id="1">Existing Customer</button>
              <button type="button" class="btn-as-select" data-id="2">New Customer</button>
              <?php //foreach($contact_types as $ct) echo '<button type="button" class="btn-as-select" data-id="'.(int)$ct['id'].'">'.htmlspecialchars($ct['name']).'</button>'; ?>
              <!--<button type="button" class="btn-as-select add" id="addContactRight">+ Add New</button>-->
            </div>
          </div>
        </div>

        <div class="line" id="manualContactWrap" style="display:none;">
          <div class="field-group">
            <label for="detail-contact-name">Contact / Company</label>
            <input type="text" id="detail-contact-name" name="contact_name" placeholder="Name or company">
          </div>

          <div style="display:flex; gap:12px; margin-top:10px;">
            <div class="field-group" style="flex:1">
              <label for="detail-contact-phone">Phone</label>
              <input type="text" id="detail-contact-phone" name="contact_phone" placeholder="Mobile number">
            </div>

            <div class="field-group" style="flex:1">
              <label for="detail-contact-email">Email</label>
              <input type="text" id="detail-contact-email" name="contact_email" placeholder="Email Address">
            </div>
          </div>

          <label for="detail-contact-email">Booking Type</label>
          <div style="display:flex;gap:8px;  margin-top:10px;" id="ee-scenario-listnew" class="ee-scenario-listnew">
            <div class="ee-scenario-item" data-ee-id="1" data-ee-title="Flights">Flights</div>
            <div class="ee-scenario-item" data-ee-id="2" data-ee-title="Tours">Tours</div>
          </div>

        </div>

        <!-- START: Customer + Contact summary (read-only) -->
        <div class="line" id="customerSummaryWrap" style="display:none;">
          <div class="field-group">
            <label>Selected Details (read-only)</label>
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
              <div style="flex:1;min-width:200px">
                <div style="font-size:13px;font-weight:700" id="summaryCompany">—</div>
                <div class="meta" id="summaryCompanyMeta" style="margin-top:4px">Company</div>
              </div>

              <div style="flex:1;min-width:200px">
                <div style="font-size:13px;font-weight:700" id="summaryContact">—</div>
                <div class="meta" id="summaryContactMeta" style="margin-top:4px">Contact</div>
              </div>

              <div style="min-width:160px">
                <div style="font-size:13px;font-weight:700" id="summaryPhone">—</div>
                <div class="meta" style="margin-top:4px">Phone</div>
              </div>

              <div style="min-width:200px">
                <div style="font-size:13px;font-weight:700" id="summaryEmail">—</div>
                <div class="meta" style="margin-top:4px">Email</div>
              </div>

              <!-- <div style="min-width:160px" style="display: none !important;">
                <div style="font-size:13px;font-weight:700" id="summaryDestination">—</div>
                <div class="meta" style="margin-top:4px">Destination</div>
              </div> -->
            </div>

            <!-- hidden fields to be used when saving -->
            <input type="hidden" id="detail-customer-id" name="customer_id" value="">
            <input type="hidden" id="detail-customer-contact-id" name="customer_contact_id" value="">
          </div>
        </div>
        <!-- END: Customer + Contact summary (read-only) -->

        <div style="display:flex;gap:8px;margin-top:12px;justify-content:flex-end">
          <button type="button" class="btn-primary" id="wiz-next-1" style="min-width:120px">Next →</button>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="wizard-step" id="wiz-step-2" aria-hidden="true" style="display:none;">
        <div class="line" style="display: none;">
          <div class="field-group">
            <!-- <label>Type</label> -->
            <div class="btn-group" id="scenarioGroup"><input type="hidden" id="detail-scenario" name="scenario_id">
              <?php foreach($scenarios as $s) echo '<button type="button" class="btn-as-select" data-id="'.(int)$s['id'].'">'.htmlspecialchars($s['name']).'</button>'; ?>
              <button type="button" class="btn-as-select add" id="addScenarioBtn">+ Add New</button>
            </div>
          </div>
        </div>

        <div id="flightDiv" class="border rounded-3 p-3 mt-3 bg-light" style="display:none;">
          
          <div class="flight-bar card shadow-sm">
            <div class="card-body p-2">

              <!-- hidden -->
              <input type="hidden" name="selected_offer_json" id="selected_offer_json">
              <input type="hidden" name="selected_price" id="selected_price">
              <input type="hidden" name="tripType" id="tripType" value="ONE_WAY">

              <!-- trip type -->
              <div class="flight-tabs mb-2">
                <button type="button" class="active" data-type="ONE_WAY">One Way</button>
                <button type="button" data-type="ROUND_TRIP">Round Trip</button>
              </div>

              <!-- search row -->
              <div class="flight-row">

                <div class="flight-field">
                  <span>FROM</span>
                  <select id="origin" name="origin">
                    <option value="">City or Airport</option>
                  </select>
                </div>

                <button type="button" class="swap-btn" id="swapAirports">⇄</button>

                <div class="flight-field">
                  <span>TO</span>
                  <select id="destination" name="destination">
                    <option value="">City or Airport</option>
                  </select>
                </div>

                <div class="flight-field date">
                  <span>DEPART</span>
                  <input type="date" id="departureDate" name="departureDate">
                </div>

                <div class="flight-field date">
                  <span>RETURN</span>
                  <input type="date" id="returnDate" name="returnDate" disabled>
                </div>

                <div class="flight-field small">
                  <span>PAX</span>
                  <select name="adults">
                    <option>1</option><option>2</option><option>3</option>
                    <option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option>
                  </select>
                </div>

                <div class="flight-field small">
                  <span>CLASS</span>
                  <select name="travelClass">
                    <option value="ECONOMY">Economy</option>
                    <option value="PREMIUM_ECONOMY">Premium</option>
                    <option value="BUSINESS">Bisiness</option>
                    <option value="FIRST">First</option>
                  </select>
                </div>

                <div class="flight-check">
                  <input type="checkbox" name="nonStop" id="nonStop">
                  <label for="nonStop">Non-stop</label>
                </div>

                <button type="button" class="search-btn" id="SearchFlightsBtn">
                  <span class="spinner-border spinner-border-sm d-none" id="flightSpinner"></span>
                  SEARCH
                </button>

              </div>
            </div>
          </div>
          <style>
            .flight-bar {
            border-radius: 12px;
          }

          .flight-tabs {
            display: flex;
            gap: 6px;
          }

          .flight-tabs button {
            border: 1px solid #dbeafe;
            background: #fff;
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 20px;
            cursor: pointer;
          }

          .flight-tabs button.active {
            background: #2563eb;
            color: #fff;
          }

          .flight-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
          }

          .flight-field {
            display: flex;
            flex-direction: column;
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            min-width: 140px;
            background: #fff;
          }

          .flight-field.small { min-width: 90px; }
          .flight-field.date { min-width: 130px; }

          .flight-field span {
            font-size: 10px;
            font-weight: 600;
            color: #6b7280;
          }

          .flight-field select,
          .flight-field input {
            border: none;
            outline: none;
            font-size: 13px;
            padding: 0;
          }

          .swap-btn {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
          }

          .flight-check {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            margin-top: 16px;
          }

          .search-btn {
            margin-left: auto;
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
          }
          </style>
          <script>
          document.addEventListener('DOMContentLoaded', function () {

            const tripTypeInput = document.getElementById('tripType');
            const returnDate    = document.getElementById('returnDate');
            const origin        = document.getElementById('origin');
            const destination   = document.getElementById('destination');
            const swapBtn       = document.getElementById('swapAirports');

            /* Trip type toggle */
            document.querySelectorAll('.flight-tabs button').forEach(btn => {
              btn.addEventListener('click', function () {

                document.querySelectorAll('.flight-tabs button')
                  .forEach(b => b.classList.remove('active'));

                this.classList.add('active');

                tripTypeInput.value = this.dataset.type;
                returnDate.disabled = this.dataset.type === 'ONE_WAY';
              });
            });

            /* Swap airports */
            if (swapBtn) {
              swapBtn.addEventListener('click', function () {
                const o = origin.value;
                const d = destination.value;
                origin.value = d;
                destination.value = o;
              });
            }

          });
          </script>
 

          <div id="selectedFlightWrap"
               class="mt-4 d-none">

            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">Selected Flight</h6>
              <button type="button"
                      class="btn btn-sm btn-outline-secondary"
                      id="changeFlightBtn">
                Change Flight
              </button>
            </div>

            <div id="selectedFlightCard"></div>
          </div>

          <div id="flightResultsWrap" class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0" id="fresultTitle" style="display: none;">Available Flights</h6>
              <button type="button"
                      class="btn btn-sm btn-outline-secondary d-none"
                      id="changeFlightBtn">
                Change Flight
              </button>
            </div>

            <div id="flightResults"></div>
          </div>

        </div>
        <style>
          .offer.card {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 16px;
            padding: 16px;
            margin-bottom: 16px;
            align-items: center;
          }

          .offer .segment {
            display: flex;
            flex-direction: column;
            gap: 4px;
          }

          .segment-chip {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            background: #eef2ff;
            font-size: 12px;
            font-weight: 600;
          }

          .price {
            font-size: 20px;
            font-weight: 700;
          }

          .muted {
            font-size: 12px;
            color: #6b7280;
          }

          .offer-details.within {
            grid-column: 1 / -1;
            margin-top: 10px;
          }

          .offer-details table {
            width: 100%;
            border-collapse: collapse;
          }

          .offer-details th,
          .offer-details td {
            padding: 8px;
            font-size: 13px;
            border-bottom: 1px solid #e5e7eb;
          }

          .offer-details th {
            text-align: left;
            font-weight: 600;
            color: #374151;
          }
        </style>
        <style>
          .offer.card.selected {
            border: 2px solid #0d6efd;
            background: #f0f7ff;
            box-shadow: 0 0 0 3px rgba(13,110,253,.15);
          }

          #flightResultsWrap.collapsed {
            opacity: 0.6;
            pointer-events: none;
          }

          #travellerDiv {
            animation: fadeSlideIn .35s ease;
          }

          @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
          }
        </style>
        <div id="travellerDiv" class="border rounded-3 p-3 mt-4" style="display:none">

          <h5 class="mb-3">Traveller Details</h5>

          <div id="travellerForms"></div>

          <hr class="my-3">

          <h6>Price Summary</h6>
          <div class="d-flex justify-content-between">
            <span>Total</span>
            <strong id="priceSummary">—</strong>
          </div>
        </div>

        <script>
          function renderTravellers(count) {
            const wrap = document.getElementById('travellerForms');
            wrap.innerHTML = '';

            for (let i = 1; i <= count; i++) {
              wrap.insertAdjacentHTML('beforeend', `
                <div class="card mb-3 traveller-block" data-traveller="${i}">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <h6 class="mb-0">Traveller ${i}</h6>
                      <button type="button"
                              class="btn btn-sm btn-outline-primary"
                              onclick="openContactModal(${i})">
                        Select Existing Contact
                      </button>
                    </div>

                    <div class="row g-3">
                      <div class="col-md-4">
                        <label class="form-label">Name</label>
                        <input type="text"
                               name="travellers[${i}][name]"
                               class="form-control traveller-name" data-idx="${i}">
                      </div>

                      <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email"
                               name="travellers[${i}][email]"
                               class="form-control traveller-email" data-idx="${i}">
                      </div>

                      <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text"
                               name="travellers[${i}][phone]"
                               class="form-control traveller-phone" data-idx="${i}">
                      </div>

                      <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="date"
                               name="travellers[${i}][dob]"
                               class="form-control traveller-dob" data-idx="${i}">
                      </div>
                    </div>
                  </div>
                </div>
              `);
            }
          }
        </script>
        <script>
          document.querySelector('[name="adults"]').addEventListener('change', function () {
            const count = parseInt(this.value, 10) || 1;
            renderTravellers(count);
          });
        </script>

        <script>
          let FLIGHT_OFFERS = [];
          function renderFlightResults(offers) {
            const wrap = document.getElementById('flightResults');
            document.getElementById('fresultTitle').style.display = '';
            wrap.innerHTML = '';

            if (!offers || !offers.length) {
              wrap.innerHTML = `<div class="alert alert-warning">No flights found</div>`;
              return;
            }

            offers.forEach((o, idx) => {
              const itinOut = o.itineraries[0];
              const itinRet = o.itineraries[1] || null;

              const seg0 = itinOut.segments[0];
              const segL = itinOut.segments[itinOut.segments.length - 1];

              const price = o.price.grandTotal || o.price.total;

              const card = document.createElement('div');
              card.className = 'offer card';

              card.innerHTML = `
                <div class="segment">
                  <strong>${seg0.departure.iataCode} → ${segL.arrival.iataCode}</strong>
                  <span class="small">
                    ${fmtDT(seg0.departure.at)} → ${fmtDT(segL.arrival.at)}
                  </span>

                  <div class="small">
                    Outbound duration:
                    <span class="segment-chip">${fmtDur(itinOut.duration)}</span>
                    · Stops:
                    <span class="segment-chip">${itinOut.segments.length - 1}</span>
                  </div>

                  ${
                    itinRet
                    ? `<div class="small">
                         Return duration:
                         <span class="segment-chip">${fmtDur(itinRet.duration)}</span>
                         · Stops:
                         <span class="segment-chip">${itinRet.segments.length - 1}</span>
                       </div>`
                    : ''
                  }
                </div>

                <div>
                  <div class="price">£ ${price}</div>
                  <div class="muted">Per Amadeus offer</div>
                </div>

                <div>
                  <button type="button"
                    class="btn btn-primary"
                    data-flight-idx="${idx}"
                    onclick="selectFlight(${idx}, this)">
                    Select &amp; Price
                  </button>
                </div>

                <details class="offer-details within">
                  <summary>Flight details (carrier, terminals, aircraft)</summary>
                  ${renderSegmentsTable(o.itineraries)}
                </details>
              `;

              wrap.appendChild(card);
            });
          }

          /* -------- helpers -------- */

          function renderSegmentsTable(itineraries) {
            let rows = '';

            itineraries.forEach(itin => {
              itin.segments.forEach(s => {
                rows += `
                  <tr>
                    <td>${s.carrierCode} ${s.number}</td>
                    <td>${s.departure.iataCode}${s.departure.terminal ? ' <span class="muted">T'+s.departure.terminal+'</span>' : ''}</td>
                    <td>${s.arrival.iataCode}${s.arrival.terminal ? ' <span class="muted">T'+s.arrival.terminal+'</span>' : ''}</td>
                    <td>${fmtDT(s.departure.at)}</td>
                    <td>${fmtDT(s.arrival.at)}</td>
                    <td>${fmtDur(s.duration)}</td>
                    <td>${s.aircraft?.code || '—'}</td>
                  </tr>
                `;
              });
            });

            return `
              <table>
                <thead>
                  <tr>
                    <th>Flight</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Departs</th>
                    <th>Arrives</th>
                    <th>Duration</th>
                    <th>Aircraft</th>
                  </tr>
                </thead>
                <tbody>${rows}</tbody>
              </table>
            `;
          }

          function fmtDT(iso) {
            return new Date(iso).toLocaleString('en-GB', {
              day: '2-digit',
              month: 'short',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit'
            });
          }

          function fmtDur(iso) {
            if (!iso) return '—';
            const m = iso.match(/PT(?:(\d+)H)?(?:(\d+)M)?/);
            return [
              m?.[1] ? `${m[1]}h` : '',
              m?.[2] ? `${m[2]}m` : ''
            ].join(' ').trim();
          }

          function renderItinerary(itin, label) {
            const seg0 = itin.segments[0];
            const segL = itin.segments[itin.segments.length - 1];

            let rows = itin.segments.map(s => `
              <div class="small text-muted">
                ${s.carrierCode}${s.number} ·
                ${s.departure.iataCode} ${formatDT(s.departure.at)}
                →
                ${s.arrival.iataCode} ${formatDT(s.arrival.at)}
              </div>
            `).join('');

            return `
              <div class="mb-2">
                <span class="badge bg-secondary mb-1">${label}</span>
                <div class="fw-semibold">
                  ${seg0.departure.iataCode} → ${segL.arrival.iataCode}
                </div>
                <div class="small text-muted">
                  Stops: ${itin.segments.length - 1}
                </div>
                <details class="mt-1">
                  <summary class="small">View segments</summary>
                  ${rows}
                </details>
              </div>
            `;
          }

          function formatDT(iso) {
            return new Date(iso).toLocaleString('en-GB', {
              day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
            });
          }

          /* ✅ Button-based AJAX search */
          document.getElementById('SearchFlightsBtn').addEventListener('click', function () {

            const spinner = document.getElementById('flightSpinner');
            spinner.classList.remove('d-none');

            /* IMPORTANT: grab the parent form */
            const form = this.closest('form');
            if (!form) {
              spinner.classList.add('d-none');
              alert('Form not found');
              return;
            }

            const fd = new FormData(form);
            fd.append('action', 'search'); // if backend expects it

            fetch('public/ajax/ajax_search_flights.php', {
              method: 'POST',
              body: fd
            })
            .then(r => r.json())
            .then(json => {
              spinner.classList.add('d-none');

              if (!json.success) {
                alert(json.error || 'Search failed');
                return;
              }

              FLIGHT_OFFERS = json.offers;
              renderFlightResults(json.offers);
            }) 
            .catch(() => {
              spinner.classList.add('d-none');
              alert('Network error');
            });
          });
        </script>
        <script>
          function selectFlight(idx, btn) {

            const offer = FLIGHT_OFFERS[idx];
            if (!offer) {
              alert('Invalid flight selection');
              return;
            }

            const price =
              offer.price.grandTotal || offer.price.total || '0.00';

            /* -------------------------
               1. Store booking data
            -------------------------- */
            document.getElementById('selected_offer_json').value =
              JSON.stringify(offer);

            document.getElementById('selected_price').value = price;

            /* -------------------------
               2. Move selected card
            -------------------------- */
            const card = btn.closest('.offer.card');
            if (!card) return;

            card.classList.add('selected');

            const selectedWrap = document.getElementById('selectedFlightCard');
            selectedWrap.innerHTML = '';
            selectedWrap.appendChild(card);

            /* -------------------------
               3. Hide results section
            -------------------------- */
            document.getElementById('flightResultsWrap').style.display = 'none';
            document.getElementById('fresultTitle').style.display = 'none';

            /* -------------------------
               4. Show selected section
            -------------------------- */
            document.getElementById('selectedFlightWrap')
                    .classList.remove('d-none');

            /* -------------------------
               5. Generate travellers
            -------------------------- */
            const adults = parseInt(
              document.querySelector('[name="adults"]').value || '1',
              10
            );

            renderTravellers(adults);

            /* Autofill Traveller 1 from selected customer */
            document.querySelector('.traveller-name[data-idx="1"]').value =
              document.getElementById('ee-selected-customer-name').value || '';

            document.querySelector('.traveller-email[data-idx="1"]').value =
              document.getElementById('ee-selected-customer-email').value || '';

            document.querySelector('.traveller-phone[data-idx="1"]').value =
              document.getElementById('ee-selected-customer-phone').value || '';

            document.getElementById('priceSummary').textContent = '£ ' + price;

            /* -------------------------
               6. Show traveller section
            -------------------------- */
            const travellerDiv = document.getElementById('travellerDiv');
            travellerDiv.style.display = '';
            travellerDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }

          </script>
          <script>
            document.getElementById('changeFlightBtn')
              .addEventListener('click', function () {

                const selectedWrap = document.getElementById('selectedFlightCard');
                const resultsWrap  = document.getElementById('flightResults');

                // Move card back into results
                const card = selectedWrap.querySelector('.offer.card');
                if (card) {
                  card.classList.remove('selected');
                  resultsWrap.prepend(card);
                }

                // Show results again
                document.getElementById('flightResultsWrap').style.display = '';
                document.getElementById('fresultTitle').style.display = '';

                // Hide selected section
                document.getElementById('selectedFlightWrap').classList.add('d-none');

                // Hide traveller section
                document.getElementById('travellerDiv').style.display = 'none';

                // Scroll back
                document.getElementById('flightResultsWrap')
                        .scrollIntoView({ behavior: 'smooth' });
            });
          </script>
          <script>
            $(document).ready(function() {
              get_missed_calls();
              get_incoming_calls();
              setInterval(function() {
                get_missed_calls();
                get_incoming_calls();
              },5000);
            });
            var icall = 0;
            var mcall = 0;
            function get_incoming_calls() {
              $.post('public/ajax/get_incoming_calls.php',{ calls: 1},
              function(data, status){
                if(data.success==1) {
                  if(icall==0 || mcall==0) {
                    tabClick('li1');
                  }
                  icall = 1;
                  // $("#incoming_calls_list").show();
                  $("#incoming_calls").html('');
                  for(i=0;i<data.nos.length;i++) {
                    $("#incoming_calls").append('<div class="call_items">'+ data.nos[i].number + ' (' + data.nos[i].source + ')'+ '<span class="btn btn-sm btn-xs btn-info float-end" onclick="select_call(`' + data.nos[i].number + '`,`' + data.nos[i].source + '`)">Choose</span>'+ '</div>');
                  }
                }
                else {
                  // $("#incoming_calls_list").hide();
                  $("#incoming_calls").html('No calls to show');
                  icall = 0;
                  $(".calls_list").hide();
                }
                if(icall==1 || mcall==1) {
                  $(".calls_list").show();
                }
              });
            }
            function get_missed_calls() {
              $.post('public/ajax/get_missed_calls.php',{ calls: 1},
              function(data, status){
                if(data.success==1) {
                  if(icall==0 || mcall==0) {
                    tabClick('li2');
                  }
                  mcall = 1;
                  // $("#missed_calls_list").show();
                  $("#missed_calls").html('');
                  for(i=0;i<data.nos.length;i++) {
                    $("#missed_calls").append('<div class="call_items">'+data.nos[i].number+' <span class="btn btn-sm btn-xs btn-info float-end" onclick="select_call(`'+data.nos[i].number+'`,`Phone`)">Choose</span></div>');
                  }
                }
                else {
                  // $("#missed_calls_list").hide();
                  $("#missed_calls").html('No calls to show');
                  mcall = 0;
                  $(".calls_list").hide();
                }
                if(icall==1 || mcall==1) {
                  $(".calls_list").show();
                }
              });
            }
            // function tabClick(liid) {
            //   var target1 = $("#"+liid).find('a').data("target") // activated tab
            //   $(".tabs-div").removeClass('active');
            //   $(".tab1").removeClass('active');
            //   $(".tab1").removeClass('in');
            //   $("#"+liid).addClass('active');
            //   $("#"+target1).addClass('active');
            //   $("#"+target1).addClass('in');
            // }
            function tabClick(btnId) {
              const btn = document.getElementById(btnId);
              if (!btn) return;

              const tab = new bootstrap.Tab(btn);
              tab.show();
            }

            function selectContactType(id) {
              const group = document.querySelector('#contactTypeGroup');
              if (!group) return;

              group.querySelectorAll('.btn-as-select')
                .forEach(b => b.classList.remove('active', 'selected'));

              const btn = group.querySelector('.btn-as-select[data-id="' + id + '"]');
              if (btn) {
                btn.classList.add('active', 'selected');
                document.querySelector('#detail-contact-type').value = id;
              }
            }

            // function select_call(phon) {
            //   if(phon!='') {
            //     $("#phoneSelect").select2("trigger", "select", {
            //         data: { id: phon, text: phon }
            //     });
            //   }
            // }
            function select_call(phone, source) {

              /* -----------------------------
                 1. Select Channel based on source
              ------------------------------ */
              const btnGroup = document.querySelector('#channelGroup');
              if (btnGroup) {

                // clear previous selection
                btnGroup.querySelectorAll('.btn-as-select')
                  .forEach(b => b.classList.remove('active', 'selected'));

                let channelId = "1"; // default = Phone

                if (source) {
                  const s = source.toLowerCase();
                  if (s.includes('whatsapp') || s.includes('wa')) {
                    channelId = "2"; // WhatsApp
                  }
                }

                const channelBtn = btnGroup.querySelector(`.btn-as-select[data-id="${channelId}"]`);
                if (channelBtn) {
                  channelBtn.classList.add('active', 'selected');
                  document.querySelector('#detail-channel').value = channelId;
                }
              }

              /* -----------------------------
                2. AJAX: Check Existing Records
              ------------------------------ */
              $.post(
                'public/ajax/check_phone_exists.php',
                { phone: phone },
                function (res) {

                  if (!res.success) return;

                  if (res.type === 'customer') {

                    /* -----------------------------
                       1. Open Existing Customer Modal
                    ------------------------------ */
                    $('#existingCustomerModalBackdrop').addClass('show').attr('aria-hidden', 'false');

                    /* -----------------------------
                       2. Load customers (no search)
                    ------------------------------ */
                    // ee_fetchEmployees('', 50);
                    // $('#ee-search-input').val(phone).trigger('keyup');
                    ee_openExistingCustomerModal();
                    selectContactType(1);

                    /* -----------------------------
                       3. Auto-select matched customer
                    ------------------------------ */
                    const selectWhenReady = setInterval(function () {
                      const $item = $('#ee-customer-list .ee-customer-item[data-ee-id="' + res.id + '"]');

                      if ($item.length) {
                        clearInterval(selectWhenReady);

                        $item.trigger('click'); // reuse existing click logic

                        /* -----------------------------
                           4. Enable Select button
                        ------------------------------ */
                        $('#ee-select-confirm').prop('disabled', false);
                      }
                    }, 100);

                    $('#ee-search-input').val(phone).trigger('keyup');
                  }

                },
                'json'
              );

            }
          </script>

          <style>
            .ee-scenario-listnew {
              display: flex;
              gap: 8px;
            }

            .ee-scenario-item {
              padding: 6px 12px;
              border: 1px solid #d1d5db;
              border-radius: 6px;
              cursor: pointer;
              font-size: 14px;
              background: #fff;
            }

            .ee-scenario-item.selected {
              background: #eef2ff;
              border-color: #6366f1;
              font-weight: 600;
            }
          </style>

        <!-- START: Contextual related entity option (insert after Scenario, before Short Summary) -->
        <div class="line" id="relatedEntityWrap1" style="display:none;">
          <div class="field-group">
            <label id="relatedEntityQuestionLabel" style="margin-bottom:6px">Related</label>

            <div style="display:flex;align-items:center;gap:12px;">
              <div style="display:flex;gap:8px;" id="relatedEntityRadioGroup">
                <button type="button" class="btn-as-select" data-related="no" id="relatedNoBtn">No</button>
                <button type="button" class="btn-as-select" data-related="yes" id="relatedYesBtn">Yes</button>
              </div>
              <div id="relatedEntityActions" style="margin-left:12px;"></div>
            </div>

            <div id="relatedSelectedList" style="margin-top:10px;display:none;">
              <label style="font-size:13px;color:var(--muted);margin-bottom:6px">Selected</label>
              <div id="relatedSelectedNames" style="display:flex;gap:8px;flex-wrap:wrap"></div>
            </div>

            <!-- Hidden fields to persist selections -->
            <input type="hidden" id="related_employee_ids" name="related_employee_ids" value="">
            <input type="hidden" id="related_customer_id" name="related_customer_id" value="">
          </div>
        </div>
        <!-- END: Contextual related entity option -->



        <div class="line" id="summaryDiv" style="display: none;"><div class="field-group"><label for="detail-subject">Short Summary</label><input type="text" id="detail-subject" name="subject" placeholder="Short summary" value="Flight Booking"></div></div>
        <div class="line"><div class="field-group"><label for="detail-notes">Notes</label><textarea id="detail-notes" name="notes"></textarea></div></div>
        <div class="line" style="display: none;">
          <div class="field-group"><label>Attach Document Type</label>
        <?php
          $dlabels = $db->get('document_labels',array('#all'=>1));
        ?>
            <div class="btn-group" id="doctypeGroup"><input type="hidden" id="document_type" name="document_type">
              <?php 
                $di=0; 
                foreach($dlabels->data as $s) {
                echo '<button type="button" class="btn-as-select" data-id="'.htmlspecialchars($s->label).'">'.htmlspecialchars($s->label).'</button>'; 
                $di++; 
                }
              ?>
                <button type="button" class="btn-as-select add1" id="addNewLabelBtn">+ Add New</button>
            </div>
          </div>
        </div>
        <!-- <div class="line"><div class="field-group"><label for="detail-subject">Attach Document (Optional)</label><input type="file" id="document_file" name="document_file"></div></div> -->
        <style>
          .drop-zone {
            position: relative;
            border: 2px dashed #cbd5e1;
            padding: 18px;
            text-align: center;
            border-radius: 6px;
            cursor: pointer;
          }
          .drop-zone.dragover {
            background: #f1f5f9;
            border-color: #0d6efd;
          }
          .drop-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
          }
          .file-name {
            margin-top: 6px;
            font-size: 13px;
            color: #0d6efd;
            font-weight: 500;
          }
          .file-name.error {
            color: #dc3545;
          }
          .d-none {
            display: none;
          }
        </style>
        <div class="line" style="display: none;">
          <div class="field-group">
            <label>Attach Document (Optional)</label>
            <div id="dropZone" class="drop-zone">
              <span id="dropText">Drop file here or paste (Ctrl+V)</span>
              <input type="file"
                     id="document_file"
                     name="document_file"
                     accept="image/*,application/pdf,video/*">
            </div>
            <div id="fileName" class="file-name d-none"></div>
          </div>
        </div>

        <div style="display:flex;gap:8px;margin-top:12px;justify-content:flex-end">
          <button type="button" class="btn-secondary" id="wiz-back-2" style="min-width:120px">← Back</button>
          <button type="button" class="btn-primary" id="wiz-next-2" style="min-width:120px"><span class="spinner-border spinner-border-sm d-none" id="bookingSpinner"></span> Book Now</button>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="wizard-step" id="wiz-step-3" aria-hidden="true" style="display:none;">
        <input type="hidden" id="detail-contact-entity-type" name="contact_entity_type" value="">
        <input type="hidden" id="detail-contact-entity-id" name="contact_entity_id" value="">

        <div class="line">
          <div class="field-group"><label>Assign To</label>
            <div class="btn-group" id="ownerGroup"><input type="hidden" id="detail-owner" name="owner_id">
              <?php
                $loggedId   = $_SESSION['person_id'] ?? 0;
                $loggedName = $_SESSION['person_name'] ?? 'Self';
                echo '<button type="button" class="btn-as-select" data-id="'.(int)$loggedId.'" data-self="1">Self ('.$loggedName.')</button>';
                foreach ($people as $p) {
                    if ((int)$p['id'] !== (int)$loggedId) {
                        echo '<button type="button" class="btn-as-select" data-id="'.(int)$p['id'].'">'.htmlspecialchars($p['name']).'</button>';
                    }
                }
                ?>
            </div>
          </div>
        </div>

        <div class="line"><div class="field-group"><label>Priority</label>
          <div class="btn-group" id="priorityGroup"><input type="hidden" id="detail-priority" name="priority">
            <button type="button" class="btn-as-select" data-value="normal">Normal</button>
            <button type="button" class="btn-as-select" data-value="low">Low</button>
            <button type="button" class="btn-as-select" data-value="high">High</button>
          </div>
        </div></div>

        <div class="line">
        <div style="display:flex;gap:12px">
          <div class="field-group" style="flex:1">
            <label for="detail-follow-date">Deadline Date <span class="meta" style="font-size:12px;color:var(--muted);font-weight:400"> (optional)</span></label>
            <input type="date" id="detail-follow-date" name="follow_date" placeholder="Optional">
          </div>
          <div class="field-group" style="flex:1">
            <label for="detail-follow-time">Deadline Time <span class="meta" style="font-size:12px;color:var(--muted);font-weight:400"> (optional)</span></label>
            <input type="time" id="detail-follow-time" name="follow_time" placeholder="Optional">
          </div>
        </div>
      </div>

        <div style="display:flex;gap:8px;margin-top:12px">
          <button type="button" class="btn-secondary" id="wiz-back-3" style="min-width:120px">← Back</button>
          <button type="button" class="btn-primary" id="wiz-save" style="min-width:120px">Save</button>
        </div>
      </div>
    </form>
  </aside>
</div>

<div class="modal fade" id="contactSelectModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Select Existing Contact</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div id="contactList" class="list-group">
          <div class="text-muted">Loading contacts…</div>
        </div>
      </div>

    </div>
  </div>
</div>
<script>
let activeTravellerIndex = null;
const contactModal = new bootstrap.Modal(
  document.getElementById('contactSelectModal')
);

function openContactModal(travellerIndex) {
  activeTravellerIndex = travellerIndex;

  const customerId = document.getElementById('ee-selected-customer-id')?.value;
  if (!customerId) {
    alert('Please select a customer first');
    return;
  }

  $('#contactList').html('<div class="text-muted">Loading contacts…</div>');
  contactModal.show();

  // $.getJSON(
  //   'public/ajax/customers_contacts.php',
  //   { action: 'get_existing_contacts', customer_id: customerId },
  //   function (res) {
  $.post(
  'public/ajax/customers_contacts.php',
  {
    action: 'get_existing_contacts',
    customer_id: customerId
  },
  function (res) {
      // if res is string, parse it
      if (typeof res === 'string') {
        res = JSON.parse(res);
      }
      if (!res.success || !res.items.length) {
        $('#contactList').html('<div class="text-muted">No contacts found</div>');
        return;
      }

      let html = '';
      res.items.forEach(c => {
        html += `
          <button type="button"
                  class="list-group-item list-group-item-action"
                  onclick='selectContact(${JSON.stringify(c)})'>
            <strong>${c.name}</strong><br>
            <small>${c.phone || ''} ${c.email ? '• ' + c.email : ''}</small>
          </button>
        `;
      });

      $('#contactList').html(html);
    }
  );
}

function selectContact(c) {
  if (!activeTravellerIndex) return;

  const block = document.querySelector(
    '.traveller-block[data-traveller="' + activeTravellerIndex + '"]'
  );

  block.querySelector('.traveller-name').value  = c.name || '';
  block.querySelector('.traveller-email').value = c.email || '';
  block.querySelector('.traveller-phone').value = c.phone || '';
  block.querySelector('.traveller-dob').value   = c.dob || '';

  contactModal.hide();
}
</script>


<!-- Add New Document Label Modal -->
<div class="modal fade" id="addLabelModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header py-2">
        <h6 class="modal-title fw-semibold">
          <i class="fa fa-plus text-success me-1"></i> Add New Label
        </h6>
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body py-2 px-3">

        <div class="mb-2">
          <label class="form-label small fw-semibold">Label Name *</label>
          <input type="text" id="newLabelInput" class="form-control form-control-sm" placeholder="Enter label name">
        </div>

      </div>

      <div class="modal-footer py-2">
        <button type="button" class="btn btn-success btn-sm" id="saveLabelBtn">
          <i class="fa fa-save me-1"></i> Save
        </button>
      </div>

    </div>
  </div>
</div>

<!-- small modal used for add-new options (Channels, Scenario, Contact Type, Staff) -->
<div id="modalBackdrop" class="modal-backdrop" role="dialog" aria-hidden="true" data-visible="false">
  <div class="modal" role="document" aria-modal="true" aria-labelledby="modalTitle">
    <h3 id="modalTitle">Add New</h3>
    <form id="modalForm">
      <div class="modal-row" style="flex-direction:column;margin-top:8px">
        <input type="text" id="modalInput" name="value" placeholder="Enter name or label">
        <div id="modalEntityList" style="display:none;margin-top:8px;gap:8px;flex-wrap:wrap"></div>
      </div>
      <div class="modal-actions" style="margin-top:12px;display:flex;justify-content:flex-end;gap:8px">
        <button type="button" id="modalCancel" class="btn-secondary">Cancel</button>
        <button type="submit" id="modalSave" class="btn-primary">Add</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== NEW: customer picker modal (left: customers, right: contacts + add) ===== -->
<div id="customerPickerModal" class="modal-backdrop" role="dialog" aria-hidden="true" data-visible="false" style="display:none;">
  <div class="modal customer-modal" role="document" aria-modal="true" aria-labelledby="customerModalTitle">
    <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <h4 id="customerModalTitle" style="margin:0">Select Customer</h4>
      <div>
        <button type="button" id="customerModalClose" class="btn-secondary">Close</button>
      </div>
    </header>

    <div style="display:flex;gap:12px; height:85%;">
      <!-- LEFT: search + customers list -->
      <div style="width:50%; display:flex; flex-direction:column; gap:12px; min-width:260px;">
        <div style="display:flex;align-items:center">
        <input type="search" id="custSearch" placeholder="Search..." 
          style="flex:1;padding:8px;border-radius:6px;border:1px solid #d1d5db;font-size:14px">
      </div>

        <div id="custList" style="flex:1; overflow:auto; padding:8px; border-radius:6px; border:1px solid #eef2ff; background:#fff;">
          <div class="muted">Loading…</div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:6px;">
          <button type="button" id="custClearSelection" class="btn-secondary">Clear</button>
          <button type="button" id="custSelectBtn" class="btn-primary" style="min-width:180px; display: none;">
            Select & Continue →
          </button>
        </div>
      </div>

      <!-- RIGHT: contacts for selected customer -->
      <aside style="width:50%; box-sizing:border-box; display:flex; flex-direction:column; gap:12px; padding:6px;">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <h4 id="custContactsTitle" style="margin:0 0 6px 0">Contacts</h4>
            <div id="custSelectedLabel" style="font-size:13px;color:var(--muted)">Pick a customer on the left</div>
          </div>
          <div>
            <button type="button" id="custAddContactBtn" class="btn-primary" style="display:none">+ Add Contact</button>
          </div>
        </div>

        <div id="custContactsWrap" style="flex:1; overflow:auto; padding:12px; border-radius:6px; border:1px solid #eef2ff; background:#fff;">
          <div class="muted">No customer selected.</div>
        </div>
      </aside>
    </div>
  </div>
</div>

<!-- ===== Related Employees modal (separate backdrop) ===== -->
<div id="relatedEmployeesModal" class="modal-backdrop" role="dialog" aria-hidden="true" data-visible="false" style="display:none;">
  <div class="modal" role="document" aria-modal="true" aria-labelledby="relatedEmployeesTitle" style="max-width:720px;">
    <header style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px">
      <div style="display:flex;flex-direction:column;flex:1;min-width:220px">
        <h4 id="relatedEmployeesTitle" style="margin:0">Select Employees</h4>
        <input id="relatedEmployeesSearch" type="search" placeholder="Search employees (name, company, email, phone)" 
               style="margin-top:8px;padding:8px;border-radius:6px;border:1px solid #d1d5db;font-size:14px;">
      </div>
      <div style="margin-left:12px">
        <button type="button" class="btn-secondary" id="relatedEmployeesClose">Close</button>
      </div>
    </header>

    <div style="display:flex;flex-direction:column;gap:12px;">
      <div style="font-size:13px;color:var(--muted)">Pick one or more employees</div>
      <div id="relatedEmployeesList" class="entity-picker-list" style="min-height:220px;">
        <div class="muted">Loading…</div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px">
        <button type="button" class="btn-secondary" id="relatedEmployeesCancel">Cancel</button>
        <button type="button" class="btn-primary" id="relatedEmployeesSave">Add Selected</button>
      </div>
    </div>
  </div>
</div>

<!-- ===== Related Customer modal (separate backdrop) ===== -->
<div id="relatedCustomerModal" class="modal-backdrop" role="dialog" aria-hidden="true" data-visible="false" style="display:none;">
  <div class="modal" role="document" aria-modal="true" aria-labelledby="relatedCustomerTitle" style="max-width:720px;">
    <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <h4 id="relatedCustomerTitle" style="margin:0">Select Customer</h4>
      <div>
        <button type="button" class="btn-secondary" id="relatedCustomerClose">Close</button>
      </div>
    </header>

    <div style="display:flex;flex-direction:column;gap:12px;">
      <div style="font-size:13px;color:var(--muted)">Pick a single customer</div>
      <div id="relatedCustomerList" class="entity-picker-list" style="min-height:220px;">
        <div class="muted">Loading…</div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px">
        <button type="button" class="btn-secondary" id="relatedCustomerCancel">Cancel</button>
      </div>
    </div>
  </div>
</div>



<!-- ===== Contacts picker modal ===== -->
<div id="contactsPickerModal" class="modal-backdrop" role="dialog" aria-hidden="true" data-visible="false" style="display:none;">
  <div class="modal customer-modal" role="document" aria-modal="true" aria-labelledby="contactsModalTitle">
    <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <h4 id="contactsModalTitle" style="margin:0">Select Contact Source</h4>
      <div><button type="button" id="contactsModalClose" class="btn-secondary">Close</button></div>
    </header>
    <div style="display:flex;gap:12px; height:85%;">
      <div style="width:50%; display:flex; flex-direction:column; gap:12px; min-width:260px;">
        <div style="display:flex;align-items:center">
          <input type="search" id="contSearch" placeholder="Search..." style="flex:1;padding:8px;border-radius:6px;border:1px solid #d1d5db;font-size:14px">
        </div>
        <div id="contList" style="flex:1; overflow:auto; padding:8px; border-radius:6px; border:1px solid #eef2ff; background:#fff;">
          <div class="muted">Loading…</div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:6px;">
          <button type="button" id="contClearSelection" class="btn-secondary">Clear</button>
          <button type="button" id="contSelectBtn" class="btn-primary" style="min-width:180px; display: none;">Select & Continue →</button>
        </div>
      </div>

      <aside style="width:50%; box-sizing:border-box; display:flex; flex-direction:column; gap:12px; padding:6px;">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <h4 id="contContactsTitle" style="margin:0 0 6px 0">Contacts</h4>
            <div id="contSelectedLabel" style="font-size:13px;color:var(--muted)">Pick a source on the left</div>
          </div>
          <div><button type="button" id="contAddContactBtn" class="btn-primary" style="display:none">+ Add Contact</button></div>
        </div>

        <div id="contContactsWrap" style="flex:1; overflow:auto; padding:12px; border-radius:6px; border:1px solid #eef2ff; background:#fff;">
          <div class="muted">No source selected.</div>
        </div>
      </aside>
    </div>
  </div>
</div>

<!-- ===== Recruiters picker modal ===== -->
<div id="recruitersPickerModal" class="modal-backdrop" role="dialog" aria-hidden="true" data-visible="false" style="display:none;">
  <div class="modal customer-modal" role="document" aria-modal="true" aria-labelledby="recruitersModalTitle">
    <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <h4 id="recruitersModalTitle" style="margin:0">Select Recruiter</h4>
      <div><button type="button" id="recruitersModalClose" class="btn-secondary">Close</button></div>
    </header>
    <div style="display:flex;gap:12px; height:85%;">
      <div style="width:50%; display:flex; flex-direction:column; gap:12px; min-width:260px;">
        <div style="display:flex;align-items:center">
          <input type="search" id="recSearch" placeholder="Search..." style="flex:1;padding:8px;border-radius:6px;border:1px solid #d1d5db;font-size:14px">
        </div>
        <div id="recList" style="flex:1; overflow:auto; padding:8px; border-radius:6px; border:1px solid #eef2ff; background:#fff;">
          <div class="muted">Loading…</div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:6px;">
          <button type="button" id="recClearSelection" class="btn-secondary">Clear</button>
          <button type="button" id="recSelectBtn" class="btn-primary" style="min-width:180px; display: none;">Select & Continue →</button>
        </div>
      </div>

      <aside style="width:50%; box-sizing:border-box; display:flex; flex-direction:column; gap:12px; padding:6px;">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <h4 id="recContactsTitle" style="margin:0 0 6px 0">Contacts</h4>
            <div id="recSelectedLabel" style="font-size:13px;color:var(--muted)">Pick a recruiter on the left</div>
          </div>
          <div><button type="button" id="recAddContactBtn" class="btn-primary" style="display:none">+ Add Contact</button></div>
        </div>

        <div id="recContactsWrap" style="flex:1; overflow:auto; padding:12px; border-radius:6px; border:1px solid #eef2ff; background:#fff;">
          <div class="muted">No recruiter selected.</div>
        </div>
      </aside>
    </div>
  </div>
</div>

<!-- ===== Suppliers picker modal ===== -->
<div id="suppliersPickerModal" class="modal-backdrop" role="dialog" aria-hidden="true" data-visible="false" style="display:none;">
  <div class="modal customer-modal" role="document" aria-modal="true" aria-labelledby="suppliersModalTitle">
    <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <h4 id="suppliersModalTitle" style="margin:0">Select Supplier</h4>
      <div><button type="button" id="suppliersModalClose" class="btn-secondary">Close</button></div>
    </header>
    <div style="display:flex;gap:12px; height:85%;">
      <div style="width:50%; display:flex; flex-direction:column; gap:12px; min-width:260px;">
        <div style="display:flex;align-items:center">
          <input type="search" id="supSearch" placeholder="Search..." style="flex:1;padding:8px;border-radius:6px;border:1px solid #d1d5db;font-size:14px">
        </div>
        <div id="supList" style="flex:1; overflow:auto; padding:8px; border-radius:6px; border:1px solid #eef2ff; background:#fff;">
          <div class="muted">Loading…</div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:6px;">
          <button type="button" id="supClearSelection" class="btn-secondary">Clear</button>
          <button type="button" id="supSelectBtn" class="btn-primary" style="min-width:180px; display: none;">Select & Continue →</button>
        </div>
      </div>

      <aside style="width:50%; box-sizing:border-box; display:flex; flex-direction:column; gap:12px; padding:6px;">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <h4 id="supContactsTitle" style="margin:0 0 6px 0">Contacts</h4>
            <div id="supSelectedLabel" style="font-size:13px;color:var(--muted)">Pick a supplier on the left</div>
          </div>
          <div><button type="button" id="supAddContactBtn" class="btn-primary" style="display:none">+ Add Contact</button></div>
        </div>

        <div id="supContactsWrap" style="flex:1; overflow:auto; padding:12px; border-radius:6px; border:1px solid #eef2ff; background:#fff;">
          <div class="muted">No supplier selected.</div>
        </div>
      </aside>
    </div>
  </div>
</div>

<!-- old employee modal -->
<div id="existingCustomerModalBackdrop" class="ee-modal-backdrop" aria-hidden="true">
  <div id="existingCustomerModal" class="ee-modal" role="dialog" aria-modal="true" aria-labelledby="ee-modal-title">
    <!-- LEFT: customers -->
    <div class="ee-left">
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <strong id="ee-modal-title">Select Existing Customer</strong>
        <button type="button" id="ee-close-btn" style="border:none;background:transparent;cursor:pointer;font-size:18px;">✕</button>
      </div>

      <div class="ee-search-row">
        <input id="ee-search-input" class="ee-search-input" placeholder="Search customer (name / email / id)..." />
        <!-- <button id="ee-search-btn" class="ee-search-btn btn-secondary ee-btn">Search</button> -->
      </div>

      <div id="ee-customer-list" class="ee-customer-list" tabindex="0">
        <div class="muted">Loading…</div>
      </div>
    </div>

    <!-- RIGHT: scenarios only -->
    <div class="ee-right">
      <!-- <h3>Scenarios (customer)</h3> -->
      <div style="display:flex;justify-content:space-between;align-items:center">
        <div>
          <h4 id="empContactsTitle" style="margin:0 0 6px 0">Type</h4>
          <div id="empSelectedLabel" style="font-size:13px;color:var(--muted)">Pick a type</div>
        </div>
        <!-- <div>
          <button type="button" id="empAddScenarioBtn" class="btn-primary">+ Add Scenario</button>
        </div> -->
      </div>
      <div class="ee-add-row" id="ee-add-row" style="display: none">
        <input id="ee-new-scenario-input" placeholder="Add new scenario (short title)" />
        <button id="ee-add-scenario-btn-close" class="btn-secondary ee-btn">Cancel</button>
        <button id="ee-add-scenario-btn" class="btn-primary ee-btn">Add</button>
      </div>
      <div id="ee-scenario-list" class="ee-scenario-list">
        <div class="muted">Loading types…</div>
      </div>


      <div class="ee-footer">
        <input type="hidden" id="ee-selected-customer-id" name="ee_selected_customer_id" value="" />
        <input type="hidden" id="ee-selected-scenario-id" name="ee_selected_scenario_id" value="" />
        <input type="hidden" id="ee-selected-customer-name" value="">
<input type="hidden" id="ee-selected-customer-phone" value="">
<input type="hidden" id="ee-selected-customer-email" value="">
        <button id="ee-cancel" type="button" class="btn-secondary ee-btn">Cancel</button>
        <button id="ee-select-confirm" type="button" class="btn-primary ee-btn" disabled>Select</button>
      </div>
    </div>
  </div>
</div>


<!-- TAG SELECTION MODAL -->
<div class="modal fade modal-backdrop1" id="tagSelectModal" style="background: rgba(0, 0, 0, .45) !important;" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">

      <div class="modal-header">
        <h5 class="modal-title fw-semibold">
          <i class="fa fa-tags me-2 text-primary"></i>Select a Tag (Required)
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- TAG BUTTONS -->
        <div id="tagButtons" class="d-flex flex-wrap gap-2 mb-3">
          <button type="button" class="btn btn-outline-primary tag-btn" data-value="customer">Customer</button>
          <button type="button" class="btn btn-outline-primary tag-btn" data-value="job_enquiry">Job Enquiry</button>
          <button type="button" class="btn btn-outline-primary tag-btn" data-value="marketing">Marketing</button>
          <button type="button" class="btn btn-outline-primary tag-btn" data-value="employee">Employee</button>
          <button type="button" class="btn btn-outline-primary tag-btn" data-value="enquiry">Enquiry</button>
          <button type="button" class="btn btn-outline-primary tag-btn" data-value="seminar_event_exhibition">
            Seminar / Event / Exhibition
          </button>
        </div>

        <!-- HIDDEN INPUT -->
        <input type="hidden" id="selected_tag">

        <!-- ERROR -->
        <div id="tagError" class="text-danger small d-none">
          Please select one tag to continue.
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          Cancel
        </button>
        <button type="button" class="btn btn-primary" id="confirmTagBtn">
          Continue & Save
        </button>
      </div>

    </div>
  </div>
</div>


<script>
// Open modal when Save is clicked
$('#wiz-save').on('click', function (e) {
  e.preventDefault();
  $('#tagSelectModal').modal('show');
});

// Handle tag selection
$(document).on('click', '.tag-btn', function () {
  $('.tag-btn')
    .removeClass('active btn-primary')
    .addClass('btn-outline-primary');

  $(this)
    .addClass('active btn-primary')
    .removeClass('btn-outline-primary');

  $('#selected_tag').val($(this).data('value'));
  $('#tagError').addClass('d-none');
});

// Validate & submit form
$('#confirmTagBtn').on('click', function () {
  const tag = $('#selected_tag').val();

  if (!tag) {
    $('#tagError').removeClass('d-none');
    return;
  }

  $('#tagSelectModal').modal('hide');
  // $('#detailForm')[0].submit();
  saveDetail();
});
</script>

<!-- ===== JavaScript: behavior & AJAX (modified for modal fixes) ===== -->
<script>
const ajaxBase = ''; // base (kept for legacy)

/* small helpers */
function qs(s, el=document){ try { return (el||document).querySelector(s); } catch(e){ return null; } }
function qsa(s, el=document){ try { return Array.from((el||document).querySelectorAll(s)); } catch(e){ return []; } }
function pad2(n){return String(n).padStart(2,'0');}
function fmtYMD(d){return d.getFullYear()+'-'+pad2(d.getMonth()+1)+'-'+pad2(d.getDate());}
function fmtHM(d){return pad2(d.getHours())+':'+pad2(d.getMinutes());}

/* timezone helper */
function nowInTZ(tz){
  try {
    const parts = new Intl.DateTimeFormat('en-GB', {
      timeZone: tz,
      year:'numeric', month:'2-digit', day:'2-digit',
      hour:'2-digit', minute:'2-digit', hour12:false
    }).formatToParts(new Date()).reduce((a,p)=>{a[p.type]=p.value; return a;},{});
    return new Date(parts.year+'-'+parts.month+'-'+parts.day+'T'+parts.hour+':'+parts.minute+':00');
  } catch(e) {
    return new Date();
  }
}

function ee_setEmployee(emp) {
  if (typeof ee_state === 'undefined') return;

  ee_state.employee = emp;

  $('#ee-selected-customer-id').val(emp.id || '');
  $('#ee-selected-customer-name').val(emp.name || '');
  $('#ee-selected-customer-phone').val(emp.phone || '');
  $('#ee-selected-customer-email').val(emp.email || '');

  /* Default scenario = Flight (id 1) */
  if (!ee_state.scenario) {
    ee_state.scenario = { id: 1, title: 'Flights' };
    $('#ee-selected-scenario-id').val(1);

    ensureHidden('existing_employee_scenario_id','existing_employee_scenario_id', 1);
    ensureHidden('existing_employee_scenario_title','existing_employee_scenario_title', 'Flights');

    document.querySelector('#detail-scenario').value = 1;

    $('#ee-scenario-listnew .ee-scenario-item')
      .removeClass('selected')
      .filter('[data-ee-id="1"]')
      .addClass('selected');
  }

  $('#ee-select-confirm')
    .prop('disabled', !(ee_state.employee && ee_state.scenario));
}

window.ee_selectScenarioById = function(id, title) {
  const $list = $('#ee-scenario-listnew');
  const $selectedScenarioId = $('#ee-selected-scenario-id');
  const $confirmBtn = $('#ee-select-confirm');

  const $item = $list.find('.ee-scenario-item[data-ee-id="' + id + '"]');
  if (!$item.length) return;

  $list.find('.ee-scenario-item.selected').removeClass('selected');
  $item.addClass('selected');

  const sc = { id: id, title: title };

  ee_state.scenario = sc;
  $selectedScenarioId.val(id);

  if (ee_state.employee && ee_state.scenario) {
    $confirmBtn.prop('disabled', false);
  }
}

function showManualContact(show) {
  const wrap = qs('#manualContactWrap');
  if (!wrap) return;
  wrap.style.display = show ? '' : 'none';

  if (show) {
    // clear any previously selected entity so saveDetail treats this as manual contact
    const eid = qs('#detail-contact-entity-id'); if (eid) eid.value = '';
    const etype = qs('#detail-contact-entity-type'); if (etype) etype.value = '';
    const customerId = qs('#detail-customer-id'); if (customerId) customerId.value = '';
    const customerContactId = qs('#detail-customer-contact-id'); if (customerContactId) customerContactId.value = '';
    const relatedEmployeeIds = qs('#related_employee_ids'); if (relatedEmployeeIds) relatedEmployeeIds.value = '';
    const relatedCustomerId = qs('#related_customer_id'); if (relatedCustomerId) relatedCustomerId.value = '';

    window.ee_selectScenarioById(1, 'Flights');

    ensureHidden('existing_employee_scenario_id','existing_employee_scenario_id', 1);
    ensureHidden('existing_employee_scenario_title','existing_employee_scenario_title', 'Flights');

    document.querySelector('#detail-scenario').value = 1;
  }
}

function clearPickedEntityUI() {
  // clear hidden inputs that represent picked entities
  ['#detail-contact-entity-id','#detail-contact-entity-type','#detail-customer-id','#detail-customer-contact-id','#related_employee_ids','#related_customer_id'].forEach(id=>{
    const el = qs(id);
    if (el) el.value = '';
  });

  // clear any visible read-only summary or selected-entity areas if present
  // common IDs/classes used in the page: adjust if your template uses different IDs
  const maybeIds = ['#selectedCustomerDisplay','#selectedContactDisplay','#selectedEntitySummary','#pickedEntitySummary'];
  maybeIds.forEach(id=>{
    const el = qs(id);
    if (el) el.innerHTML = '';
  });

  // clear textual places commonly used by pickers (safer to target generic selectors)
  qsa('.selected-entity, .selected-customer, .selected-contact').forEach(el=>{
    el.innerHTML = '';
  });
}

function clearCustomerSummaryAndUnlockManual() {
  // hide/clear the summary block
  const sumWrap = qs('#customerSummaryWrap');
  if (sumWrap) {
    sumWrap.style.display = 'none';
    // clear summary text nodes
    ['#summaryCompany','#summaryCompanyMeta','#summaryContact','#summaryContactMeta','#summaryPhone','#summaryEmail','#summaryDestination'].forEach(sel=>{
      const el = qs(sel);
      if (el) { el.textContent = (sel === '#summaryCompanyMeta' || sel === '#summaryContactMeta') ? el.textContent : '—'; }
    });
  }

  // clear the hidden customer fields used by pickers
  const fieldsToClear = ['#detail-customer-id','#detail-customer-contact-id','#detail-contact-entity-id','#detail-contact-entity-type'];
  fieldsToClear.forEach(sel => { const el = qs(sel); if (el) el.value = ''; });

  // remove readonly on manual inputs and clear their values so new entry starts fresh
  const manualInputs = ['#detail-contact-name','#detail-contact-phone','#detail-contact-email'];
  manualInputs.forEach(sel => {
    const el = qs(sel);
    if (!el) return;
    el.removeAttribute('readonly');
    el.value = '';            // clear previous value
    try { el.dispatchEvent(new Event('input', { bubbles:true })); } catch(e){} // notify listeners
  });

  // also clear any UI that shows a selected customer element (if your pickers use other containers)
  qsa('.selected-entity, .selected-customer, .selected-contact, #selectedCustomerDisplay').forEach(el=>{
    try { el.innerHTML = ''; } catch(e){}
  });
}

function setInterTypeForModal(interType) {
  const modalBackdrop = qs('#modalBackdrop');
  if (modalBackdrop) {
    modalBackdrop.dataset.inter_type = interType;
  }
}


/* --- initialise document-level UI --- */
document.addEventListener('DOMContentLoaded', function(){
  try {
    const now = nowInTZ('Europe/London');
    const dateEl = qs('#detail-date');
    const timeEl = qs('#detail-time');
    if (dateEl) dateEl.value = fmtYMD(now);
    if (timeEl) timeEl.value = fmtHM(now);
    const fd = qs('#detail-follow-date');
    const ft = qs('#detail-follow-time');
    if (fd) fd.value = '';
    if (ft) ft.value = '';
  } catch(e){ console.error(e); }

  // ensure all our modals are direct children of body (avoids being hidden by parent stacking contexts)
  ['#modalBackdrop','#customerPickerModal','#relatedEmployeesModal','#relatedCustomerModal'].forEach(id=>{
    try {
      const el = document.querySelector(id);
      if (el && el.parentElement !== document.body) document.body.appendChild(el);
    } catch(e){}
  });

  // establish button groups
  ['channelGroup','contactTypeGroup','scenarioGroup','ownerGroup','statusGroup','priorityGroup','doctypeGroup','itypeGroup'].forEach(initBtnGroup);

  // modal small form controls
  const modalCancel = qs('#modalCancel');
  if (modalCancel) modalCancel.addEventListener('click', function(){ closeModalSmall(); });

  const modalForm = qs('#modalForm');
  if (modalForm) modalForm.addEventListener('submit', handleModalSubmit);

  // Add form submission
  const detailForm = qs('#detailForm');
  if (detailForm) detailForm.addEventListener('submit', function(e){ e.preventDefault(); saveDetail(); });

  // wire "add" buttons to open small modal
  qsa('.btn-as-select.add').forEach(b => b.addEventListener('click', function(){
    const group = b.closest('.btn-group');
    const id = group ? group.id : '';
    openModalForGroup(id);
  }));

  // wizard navigation
  const next1 = qs('#wiz-next-1');
  if (next1) next1.addEventListener('click', function(){
    const name = qs('#detail-contact-name') ? qs('#detail-contact-name').value.trim() : '';
    const phone = qs('#detail-contact-phone') ? qs('#detail-contact-phone').value.trim() : '';
    const email = qs('#detail-contact-email') ? qs('#detail-contact-email').value.trim() : '';
    if (!name && !phone && !email) {
      // if (!confirm('No contact details entered. Continue anyway?')) return;
      alert('No Customer details entered.');

      return;
    }
    showStep(2);
  });

  const back2 = qs('#wiz-back-2');
  if (back2) back2.addEventListener('click', function(){ showStep(1); });

  const next2 = qs('#wiz-next-2');
  if (next2) next2.addEventListener('click', function(){ saveDetail(); /* showStep(3); */ });

  const back3 = qs('#wiz-back-3');
  if (back3) back3.addEventListener('click', function(){ showStep(2); });

  // set initial step
  showStep(1);

  const selfBtn = qs('#ownerGroup .btn-as-select[data-self="1"]');
  if (selfBtn) {
    clearActiveButtons('ownerGroup');
    selfBtn.classList.add('active');
    const hid = qs('#ownerGroup input[type="hidden"]');
    if (hid) hid.value = selfBtn.dataset.id;
  }

  try {
    setActiveButtonByValue('priorityGroup', 'normal');
    const hidPriority = qs('#priorityGroup input[type="hidden"]');
    if (hidPriority && hidPriority.value === '') {
      const b = qs('#priorityGroup .btn-as-select.active');
      if (b) hidPriority.value = b.dataset.value || b.dataset.id || 'normal';
    }
  } catch(e) { console.error('priority default error', e); }
});


//document label
// Open "Add Label" modal
document.getElementById("addNewLabelBtn").onclick = function () {
    document.getElementById("newLabelInput").value = "";
    let mdl = new bootstrap.Modal(document.getElementById("addLabelModal"));
    mdl.show();
};
// Save new label
document.getElementById("saveLabelBtn").addEventListener("click", function () {
    let label = document.getElementById("newLabelInput").value.trim();

    if (label === "") {
        alert("Please enter a label name.");
        return;
    }

    let fd = new FormData();
    fd.append("action", "add");
    fd.append("label", label);

    fetch("public/ajax/document_labels.php", {
        method: "POST",
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (data.status) {
            // hide modal
            bootstrap.Modal.getInstance(document.getElementById("addLabelModal")).hide();

            $("#doctypeGroup").find('.btn-as-select').removeClass('active');
            // $("#doctypeGroup").append('<button type="button" class="btn-as-select active" data-id="'+label+'">'+label+'</button>');
            $('<button type="button" class="btn-as-select active" data-id="'+label+'">'+label+'</button>').insertBefore('#addNewLabelBtn');
            $("#document_type").val(label);

            initBtnGroup("#doctypeGroup");
            // reload labels and auto-select new label
            // loadDocumentLabels(data.label);
        } else {
            alert(data.msg);
        }
    });
});


/* ------ small modal (Add New) handling ------ */
(function(){
  if (!window._modalState) {
    window._modalState = { count: 0, prev: { bodyOverflow:null, htmlOverflow:null, bodyPaddingRight:null } };
  }
  function getScrollbarWidth(){ return window.innerWidth - document.documentElement.clientWidth; }
  function ensureBackdropInBody(){ const b = qs('#modalBackdrop'); if (!b) return null; if (b.parentElement !== document.body) document.body.appendChild(b); return b; }

  window.openModalForGroup = function(groupId){
    const map = { channelGroup:'Add Channel', contactTypeGroup:'Add Contact Type', scenarioGroup:'Add Scenario', ownerGroup:'Add Staff' };
    const title = map[groupId] || 'Add New';
    const backdrop = ensureBackdropInBody();
    if (!backdrop) return;
    const modalTitleEl = qs('#modalTitle');
    const modalInput = qs('#modalInput');
    const modalEntityList = qs('#modalEntityList');

    if (modalTitleEl) modalTitleEl.textContent = title;
    if (modalInput) { modalInput.value=''; modalInput.style.display='block'; }
    if (modalEntityList) { modalEntityList.innerHTML=''; modalEntityList.style.display='none'; }
    backdrop.dataset.mode = 'add';
    backdrop.dataset.target = groupId;
    backdrop.dataset.visible = 'true';
    backdrop.style.display = 'flex';
    backdrop.style.alignItems = 'center';
    backdrop.style.justifyContent = 'center';
    backdrop.style.position = 'fixed';
    backdrop.style.inset = '0';
    backdrop.style.zIndex = '99999';
    const modal = qs('#modalBackdrop .modal');
    if (modal) { modal.style.position='relative'; modal.style.zIndex='100000'; modal.style.maxWidth='520px'; modal.style.width='calc(100% - 40px)'; modal.style.display='block'; }
    const st = window._modalState;
    if (st.count === 0) {
      st.prev.bodyOverflow = document.body.style.overflow || '';
      st.prev.htmlOverflow = document.documentElement.style.overflow || '';
      st.prev.bodyPaddingRight = document.body.style.paddingRight || '';
      const sbw = getScrollbarWidth();
      if (sbw > 0) {
        const prev = parseFloat(st.prev.bodyPaddingRight) || 0;
        document.body.style.paddingRight = (prev + sbw) + 'px';
      }
      document.body.style.overflow = 'hidden'; document.documentElement.style.overflow = 'hidden';
    }
    st.count += 1;
    setTimeout(()=>{ if (modalInput) modalInput.focus(); }, 50);
  };

  window.closeModalSmall = function(){
    const backdrop = qs('#modalBackdrop');
    if (!backdrop) return;

    backdrop.dataset.visible = 'false';
    backdrop.style.display = 'none';
    backdrop.dataset.target = '';
    backdrop.dataset.mode = '';

    // Force restore — no counter, no conditions
    document.body.style.overflow = '';
    document.documentElement.style.overflow = '';
    document.body.style.paddingRight = '';

    // Reset state completely
    window._modalState = { count: 0, prev: { bodyOverflow:'', htmlOverflow:'', bodyPaddingRight:'' } };
};

  document.addEventListener('click', function(e){
    const backdrop = qs('#modalBackdrop');
    if (!backdrop) return;
    if (backdrop.dataset.visible !== 'true') return;
    if (e.target === backdrop) closeModalSmall();
  }, true);

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') {
      const backdrop = qs('#modalBackdrop');
      if (backdrop && backdrop.dataset.visible === 'true') closeModalSmall();
    }
  });
})();

/* small modal submit handler (sends to public/ajax/ajax_add_option.php) */
function handleModalSubmit(e){
  e.preventDefault();
  const backdrop = qs('#modalBackdrop'); if (!backdrop) return;
  const mode = backdrop.dataset.mode || 'add';
  const groupId = backdrop.dataset.target || '';
  const modalInput = qs('#modalInput');
  const modalEntityList = qs('#modalEntityList');

  if (mode === 'entity') {
    const sel = modalEntityList ? modalEntityList.querySelector('.btn-as-select.active') : null;
    if (!sel) { alert('Please choose an entry from the list (or Cancel).'); return; }
    const eid = sel.dataset.entityId;
    const etype = sel.dataset.entityType;
    if (qs('#detail-contact-entity-id')) qs('#detail-contact-entity-id').value = eid || '';
    if (qs('#detail-contact-entity-type')) qs('#detail-contact-entity-type').value = etype || '';
    if (qs('#detail-contact-name')) qs('#detail-contact-name').value = sel.textContent.trim();
    closeModalSmall();
    return;
  }

  const val = modalInput ? modalInput.value.trim() : '';
  if (!val) { if (modalInput) modalInput.focus(); return; }
  const typeMap = { channelGroup:'channel', contactTypeGroup:'contact_type', scenarioGroup:'scenario', ownerGroup:'owner' };
  const type = typeMap[groupId] || 'other';
  const inter_type = (qs('#modalBackdrop') && qs('#modalBackdrop').dataset.inter_type) ? qs('#modalBackdrop').dataset.inter_type : '';

  const body = { type: type, name: val };
  if (type === 'scenario' && inter_type) body.inter_type = inter_type;

  fetch('public/ajax/ajax_add_option.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(body)
  }).then(r=>r.json()).then(res=>{
    if (!res || !res.success) { alert('Add failed: '+(res && res.error ? res.error : 'unknown')); return; }
    const group = qs('#' + groupId);
    if (!group) return;
    const btn = document.createElement('button'); btn.type='button'; btn.className='btn-as-select';
    if (res.id) btn.dataset.id = res.id;
    if (res.value) btn.dataset.value = res.value;
    btn.textContent = res.name;
    const addBtn = group.querySelector('.btn-as-select.add');
    if (addBtn) group.insertBefore(btn, addBtn); else group.appendChild(btn);
    clearActiveButtons(groupId);
    btn.classList.add('active');
    const hid = group.querySelector('input[type="hidden"]');
    if (hid) hid.value = res.id ? res.id : (res.value || '');
    closeModalSmall();
  }).catch(e=>{ console.error(e); alert('Add failed'); });
}

function appendTravellersToFD(fd) {

  const travellerBlocks = document.querySelectorAll('.traveller-block');

  if (!travellerBlocks.length) {
    throw new Error('No travellers found');
  }

  travellerBlocks.forEach((block, index) => {

    const name  = block.querySelector('[name$="[name]"]')?.value || '';
    const email = block.querySelector('[name$="[email]"]')?.value || '';
    const phone = block.querySelector('[name$="[phone]"]')?.value || '';
    const dob = block.querySelector('[name$="[dob]"]')?.value || '';

    if (!name || !email) {
      throw new Error(`Traveller ${index + 1} name & email required`);
    }

    fd.append(`travellers[${index}][name]`, name);
    fd.append(`travellers[${index}][email]`, email);
    fd.append(`travellers[${index}][phone]`, phone);
    fd.append(`travellers[${index}][dob]`, dob);

    /* ---- optional passport ---- */
    const passport = block.querySelector('[name$="[passport_number]"]');
    if (passport && passport.value) {
      fd.append(`travellers[${index}][passport_number]`, passport.value);
      fd.append(`travellers[${index}][passport_expiry]`,
        block.querySelector('[name$="[passport_expiry]"]')?.value || '');
      fd.append(`travellers[${index}][passport_nationality]`,
        block.querySelector('[name$="[passport_nationality]"]')?.value || '');
    }
  });

  fd.append('traveller_count', travellerBlocks.length);
}

/* --- saving interaction (AJAX) --- */
function saveDetail(){
  const contactTypeBtn = qs('#contactTypeGroup .btn-as-select.active');
  const contactTypeName = contactTypeBtn ? contactTypeBtn.textContent.trim().toLowerCase() : '';
  const requiresEntity = /existing\s+(customer|employee|recruiter|supplier|contacts?|contact)/i.test(contactTypeName);

  if (requiresEntity) {
    const eid = qs('#detail-contact-entity-id') ? qs('#detail-contact-entity-id').value : '';
    if (!eid) {
      alert('Please pick an existing entity (customer / contact / supplier) before saving.');
      return;
    }
  }

  const spinner = document.getElementById('bookingSpinner');
  spinner.classList.remove('d-none');

  const id = qs('#detailForm').dataset.currentId || '';

  // const scenario_idform = qs('#detail-scenario-id')?.value || qs('#scenario_id')?.value || getActiveDataId('scenarioGroup');
  const scenario_idform = document.querySelector('#detail-scenario').value;

  /* ---------- use FormData ---------- */
  const fd = new FormData();

  fd.append('action', id ? 'update' : 'create');
  if (id) fd.append('id', id);

  fd.append('date', qs('#detail-date')?.value || '');
  fd.append('time', qs('#detail-time')?.value || '');
  fd.append('contact_name', qs('#detail-contact-name')?.value || '');
  fd.append('subject', qs('#detail-subject')?.value || '');
  fd.append('notes', qs('#detail-notes')?.value || '');

  fd.append('channel_id', getActiveDataId('channelGroup'));
  fd.append('itype', getActiveDataId('itypeGroup'));
  fd.append('contact_type_id', getActiveDataId('contactTypeGroup'));

  fd.append(
    'scenario_id',
    scenario_idform
  );


  // flight booking data
  if(scenario_idform=="1") {
    const offerJson = document.getElementById('selected_offer_json').value;
    const price     = document.getElementById('selected_price').value;
    fd.append('selected_offer_json', offerJson);
    fd.append('selected_price', price);
    try {
      appendTravellersToFD(fd);
    } catch (e) {
      alert(e.message);
      return;
    }
    const passNum = document.querySelector('[name="passport_number"]');
    if (passNum && passNum.value) {
      fd.append('passport_number', passNum.value);
      fd.append('passport_expiry',
        document.querySelector('[name="passport_expiry"]').value);
      fd.append('passport_nationality',
        document.querySelector('[name="passport_nationality"]').value);
      fd.append('passport_issuing_country',
        document.querySelector('[name="passport_issuing_country"]').value);
    }
  }

  fd.append('owner_id', getActiveDataId('ownerGroup'));
  fd.append('status', getActiveDataValue('statusGroup'));
  fd.append('priority', getActiveDataValue('priorityGroup'));

  fd.append('follow_date', qs('#detail-follow-date')?.value || '');
  fd.append('follow_time', qs('#detail-follow-time')?.value || '');

  fd.append('contact_entity_type', qs('#detail-contact-entity-type')?.value || '');
  fd.append('contact_entity_id', qs('#detail-contact-entity-id')?.value || '');

  fd.append('contact_phone', qs('#detail-contact-phone')?.value || '');
  fd.append('contact_email', qs('#detail-contact-email')?.value || '');

  fd.append('customer_id', qs('#detail-customer-id')?.value || '');
  fd.append('customer_contact_id', qs('#detail-customer-contact-id')?.value || '');

  fd.append('related_employee_ids', qs('#related_employee_ids')?.value || '');
  fd.append('related_customer_id', qs('#related_customer_id')?.value || '');

  fd.append('document_type', qs('#document_type')?.value || '');
  fd.append('nature', qs('#selected_tag')?.value || '');

  /* ---------- attach file ---------- */
  const fileInput = qs('#document_file');
  if (fileInput && fileInput.files.length > 0) {
    fd.append('document_file', fileInput.files[0]);
  }

  fetch('public/ajax/ajax_create_booking.php', { //ajax_save_interaction.php
    method: 'POST',
    body: fd   // ❗ no headers needed
  })
  .then(r => r.json())
  .then(res => {
    if (res && res.success) {
      // alert('Saved');
      spinner.classList.add('d-none');
      alert('Booked successfully!');
      if(res.rurl) {
        window.location.href = './?page='+res.rurl;
      }
      // location.reload();
    } else {
      spinner.classList.add('d-none');
      alert('Save failed: ' + (res?.error || 'unknown'));
    }
  })
  .catch(e => {
    console.error(e);
    spinner.classList.add('d-none');
    alert('Save error');
  });
}

/* ===== button-group helpers (modified to trigger related UI reliably) ===== */
function initBtnGroup(groupId){
  const group = qs('#' + groupId);
  if (!group) return;
  group.addEventListener('click', function(e){
    const btn = e.target.closest('.btn-as-select');
    if (!btn) return;
    if (btn.classList.contains('add')) { openModalForGroup(groupId); return; }

    // Special-case: contactTypeGroup has several "existing X" options that open pickers
    if (groupId === 'contactTypeGroup') {
      const txt = (btn.textContent || '').trim().toLowerCase();

      // New Contact -> show manual entry fields on the page
      if (/new/i.test(txt) && /customer/i.test(txt)) {
        console.log('[debug] selected -> New Customer');
        // mark selection active and set hidden input
        qsa('#' + groupId + ' .btn-as-select').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        const hid = group.querySelector('input[type="hidden"]');
        if (hid) hid.value = btn.dataset.id || btn.dataset.value || '';

        setInterTypeForModal('contact'); 

        // Clear everything the pickers may have set and unlock manual inputs
        clearCustomerSummaryAndUnlockManual();

        // Show manual inputs
        showManualContact(true);

        // hide related-entity UI if present
        if (typeof showRelatedEntityOptionForType === 'function') {
          try { showRelatedEntityOptionForType(''); } catch(e){}
        }
        return;
      }

      // Existing Contacts -> contacts picker (left: contacts, right: contacts_contacts)
      if (/existing\s+contacts?/i.test(txt)) {
        // set active, set hidden, clear manual inputs & existing picks
        qsa('#' + groupId + ' .btn-as-select').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        const hid = group.querySelector('input[type="hidden"]');
        if (hid) hid.value = btn.dataset.id || btn.dataset.value || '';

        showManualContact(false);
        clearPickedEntityUI();
        setInterTypeForModal('contact');
        openContactsPicker();
        return;
      }

      // Existing Suppliers -> suppliers picker
      if (/existing\s+suppliers?/i.test(txt)) {
        qsa('#' + groupId + ' .btn-as-select').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        const hid = group.querySelector('input[type="hidden"]');
        if (hid) hid.value = btn.dataset.id || btn.dataset.value || '';

        showManualContact(false);
        clearPickedEntityUI();
        setInterTypeForModal('supplier');
        openSuppliersPicker();
        return;
      }

      // Existing Employee -> existing employee flow (keeps previous behavior)
      if (/existing\s+customer/i.test(txt)) {
        // mark the contact-type selection as active (same as before)
        qsa('#' + groupId + ' .btn-as-select').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        const hid = group.querySelector('input[type="hidden"]');
        if (hid) hid.value = btn.dataset.id || btn.dataset.value || '';

        // hide manual block and clear manual fields
        showManualContact(false);
        clearPickedEntityUI();

        // Determine if we are currently on Step 1 (use same heuristic as showStep)
        let step1Visible = false;
        const s1 = document.querySelector('#wiz-step-1, #step1, .step-1, [data-step="1"]');
        if (s1) {
          try {
            const r = s1.getBoundingClientRect();
            const cs = window.getComputedStyle(s1);
            step1Visible = (r.width > 0 && r.height > 0 && cs.display !== 'none' && cs.visibility !== 'hidden');
          } catch(e) { step1Visible = true; } // safe default
        } else {
          // fallback: if step2 isn't visible, assume step1 is active
          const s2 = document.querySelector('#wiz-step-2, #step2, .step-2, [data-step="2"]');
          if (!s2) step1Visible = true;
          else {
            try {
              const r2 = s2.getBoundingClientRect();
              const cs2 = window.getComputedStyle(s2);
              step1Visible = !(r2.width > 0 && r2.height > 0 && cs2.display !== 'none' && cs2.visibility !== 'hidden');
            } catch(e) { step1Visible = true; }
          }
        }

        if (step1Visible) {
          // Open the new Existing Employee modal if present (preferred)
          if (typeof window.ee_openExistingCustomerModal === 'function') {
            try {
              window.ee_openExistingCustomerModal();
              // do not call legacy pickers in Step1
              return;
            } catch(err) {
              console.error('ee_openExistingCustomerModal error', err);
              // fall-through to legacy behavior below
            }
          }
          // if ee modal not available, fall back to legacy behavior below
        }

        // LEGACY BEHAVIOR (kept for Step 2 or when ee modal missing)
        applyScenarioForType('employee');
        openEntityPickerFallback('employees');
        showRelatedEntityOptionForType('employee');
        return;
      }
    }

    // Default selection behavior for generic groups (non-contactTypeGroup)
    qsa('#' + groupId + ' .btn-as-select').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const hid = group.querySelector('input[type="hidden"]');
    if (hid) hid.value = btn.dataset.id || btn.dataset.value || '';
  });
}

function clearActiveButtons(groupId){
  qsa('#' + groupId + ' .btn-as-select').forEach(b=>b.classList.remove('active'));
  const hid = qs('#' + groupId + ' input[type="hidden"]');
  if (hid) hid.value = '';
}
function getActiveDataId(groupId){
  const b = qs('#' + groupId + ' .btn-as-select.active');
  return b ? (b.dataset.id || '') : '';
}
function getActiveDataValue(groupId){
  const b = qs('#' + groupId + ' .btn-as-select.active');
  return b ? (b.dataset.value || '') : '';
}
function setActiveButtonByDataId(groupId, id){
  const btn = qs('#' + groupId + ' .btn-as-select[data-id="'+id+'"]');
  if (btn) { clearActiveButtons(groupId); btn.classList.add('active'); const hid = qs('#' + groupId + ' input[type="hidden"]'); if (hid) hid.value = id; }
}
function setActiveButtonByValue(groupId, val){
  const btn = qs('#' + groupId + ' .btn-as-select[data-value="'+val+'"]');
  if (btn) { clearActiveButtons(groupId); btn.classList.add('active'); const hid = qs('#' + groupId + ' input[type="hidden"]'); if (hid) hid.value = val; }
}

/* ===== wizard step helper ===== */
function showStep(n){
  const total = 3;
  for(let i=1;i<=total;i++){
    const el = qs('#wiz-step-' + i);
    if (!el) continue;
    if (i === n) {
      el.style.display = '';
      el.setAttribute('aria-hidden','false');
    } else {
      try {
        const ae = document.activeElement;
        if (ae && el.contains(ae)) {
          try { ae.blur(); } catch(e){ }
          try { document.body.focus(); } catch(e){ }
        }
      } catch(e){ }
      el.style.display = 'none';
      el.setAttribute('aria-hidden','true');
    }
  }

  if(n==1) {
    $("#Btitle").text('Add Booking');
  }

  setTimeout(()=>{ const dest = qs('#wiz-step-' + n); if (!dest) return; const f = dest.querySelector('input, textarea, select, button'); if (f) try { f.focus(); } catch(e){} },20);
}

/* ===== Customer picker implementation (unchanged flow) ===== */
function openCustomerPicker(){
  try {
    const pick = qs('#customerPickerModal');
    if (!pick) { console.error('openCustomerPicker: no #customerPickerModal'); return; }
    if (pick.parentElement !== document.body) document.body.appendChild(pick);
    try { const ae = document.activeElement; if (ae) { try { ae.blur(); } catch(e){} } try { document.body.focus(); } catch(e){} } catch(e){}
    pick.setAttribute('aria-hidden','false');
    pick.dataset.visible = 'true';
    pick.style.display = 'flex';
    pick.style.position = 'fixed';
    pick.style.inset = '0';
    pick.style.alignItems = 'center';
    pick.style.justifyContent = 'center';
    pick.style.background = 'rgba(0,0,0,0.45)';
    pick.style.zIndex = '220000';
    const dialog = qs('#customerPickerModal .customer-modal');
    if (dialog) {
      dialog.style.display = 'block';
      dialog.style.position = 'relative';
      dialog.style.zIndex = '220001';
      dialog.style.maxWidth = 'calc(100% - 40px)';
      dialog.style.boxSizing = 'border-box';
      dialog.style.width = dialog.style.width || '920px';
      dialog.style.minWidth = dialog.style.minWidth || '700px';
      dialog.style.height = dialog.style.height || '90vh';
      const focusTarget = dialog.querySelector('#custSearch') || dialog.querySelector('.cust-item, button, input');
      if (focusTarget) try { focusTarget.focus(); } catch(e){}
    }
    try { document.body.style.overflow = 'hidden'; } catch(e){}
    const listEl = qs('#custList');
    if (listEl) listEl.innerHTML = '<div class="muted">Loading…</div>';
    const wrapEl = qs('#custContactsWrap');
    if (wrapEl) wrapEl.innerHTML = '<div class="muted">No customer selected.</div>';
    const selLabel = qs('#custSelectedLabel'); if (selLabel) selLabel.textContent = 'Pick a customer on the left';
    const addBtn = qs('#custAddContactBtn'); if (addBtn) addBtn.style.display = 'none';
    fetchCustomers();
  } catch (err) {
    console.error('openCustomerPicker error', err);
    try { const pick = qs('#customerPickerModal'); if (pick) { pick.style.display = 'none'; pick.setAttribute('aria-hidden','true'); document.body.style.overflow = ''; } } catch(e){}
    alert('An error occurred while opening the customer picker. Check console for details.');
  }
}

function closeCustomerPicker(){
  try {
    const pick = qs('#customerPickerModal');
    if (!pick) return;
    pick.setAttribute('aria-hidden','true');
    pick.dataset.visible = 'false';
    pick.style.display = 'none';
    try { document.body.style.overflow = ''; } catch(e){}
    const list = qs('#custList'); if (list) list.innerHTML = '';
    const wrap = qs('#custContactsWrap'); if (wrap) wrap.innerHTML = '';
    const lbl = qs('#custSelectedLabel'); if (lbl) lbl.textContent = '';
    const fallbackFocus = qs('#wiz-next-1') || qs('#wiz-next-2') || document.body;
    try { if (fallbackFocus) fallbackFocus.focus(); } catch(e){}
  } catch (err) {
    console.error('closeCustomerPicker error', err);
  }
}

/* Wire the customer modal controls */
(function wireCustomerModalControls() {

  try {
    const pick = qs('#customerPickerModal');
    if (!pick) return;

    // Close
    const closeBtn = qs('#customerModalClose');
    if (closeBtn) closeBtn.onclick = closeCustomerPicker;

    // Clear selection
    const clearBtn = qs('#custClearSelection');
    if (clearBtn) {
      clearBtn.onclick = function() {
        const list = qs('#custList');
        if (list) list.querySelectorAll('.cust-item').forEach(x => x.classList.remove('active'));
        qs('#custContactsWrap').innerHTML = '<div class="muted">No customer selected.</div>';
        qs('#custSelectedLabel').textContent = 'Pick a customer on the left';
        qs('#custAddContactBtn').style.display = 'none';
      };
    }

    // Select & Continue
    const selectBtn = qs('#custSelectBtn');
    if (selectBtn) {
      selectBtn.onclick = function() {
        const active = qs('#custList .cust-item.active');
        if (!active) return alert('Please select a customer');

        if (qs('#detail-contact-name')) qs('#detail-contact-name').value = active.dataset.name || '';
        if (qs('#detail-contact-entity-id')) qs('#detail-contact-entity-id').value = active.dataset.id || '';
        if (qs('#detail-contact-entity-type')) qs('#detail-contact-entity-type').value = 'customers';

        closeCustomerPicker();
      };
    }

    // Add Contact (FIXED — previously missing!)
    const addBtn = qs('#custAddContactBtn');
    if (addBtn) {
      addBtn.onclick = function() {
        const active = qs('#custList .cust-item.active');
        if (!active) return alert('Select a customer first');

        openAddContactInline(
          active.dataset.id,
          'customers_contacts',
          'cust',
          'public/ajax/ajax_add_customer_contact.php'
        );
      };
    }

    // Search
    const search = qs('#custSearch');
    if (search) {
      let timer = null;
      search.addEventListener('input', function() {
        clearTimeout(timer);
        const q = (this.value || '').trim().toLowerCase();

        timer = setTimeout(() => {
          const list = qs('#custList');
          if (!list) return;

          list.querySelectorAll('.cust-item').forEach(it => {
            const txt = (it.dataset.name || it.textContent).toLowerCase();
            it.style.display = (!q || txt.indexOf(q) !== -1) ? '' : 'none';
          });

        }, 160);
      });
    }

  } catch (e) {
    console.error('Customer modal wiring error:', e);
  }

})();

function fetchCustomers(){
  const ps = qs('#custPageSize') ? qs('#custPageSize').value : '100';
  const url = 'public/ajax/ajax_list_customers.php?limit=' + encodeURIComponent(ps);
  fetch(url).then(r=>r.json()).then(json=>{
    const list = qs('#custList');
    if (!json || !json.success) { list.innerHTML = '<div class="muted">Failed to load customers.</div>'; return; }
    list.innerHTML = '';
    json.items.forEach(it => {
        const b = document.createElement('div');
        b.className = 'cust-item';
        b.dataset.id = it.id;
        b.dataset.name = it.name || '';
        b.dataset.company = it.company || '';
        b.dataset.interType = it.inter_type || 'customer';
        b.innerHTML = '<div style="font-weight:600">' + escapeHtml(it.name || ('#'+it.id)) + '</div>'
                    + '<div class="meta">' + (it.company ? escapeHtml(it.company) : (it.email||it.phone ? (escapeHtml(it.email||'') + (it.phone ? ' • ' + escapeHtml(it.phone) : '')) : '')) + '</div>';
        b.addEventListener('click', function(){
          list.querySelectorAll('.cust-item').forEach(x=>x.classList.remove('active'));
          b.classList.add('active');
          qs('#custSelectedLabel').textContent = 'Contacts for ' + (it.name || ('#'+it.id));
          qs('#custAddContactBtn').style.display = '';
          fetchCustomerContacts(it.id);
          const interType = b.dataset.interType || 'customer';
          applyScenarioForType(interType);
        });
        list.appendChild(b);
      });
    if (json.items.length === 0) list.innerHTML = '<div class="muted">No customers</div>';
  }).catch(err=>{
    console.error('fetchCustomers', err);
    qs('#custList').innerHTML = '<div class="muted">Failed to load customers.</div>';
  });
}

function fetchCustomerContacts(customerId) {
  const wrap = qs('#custContactsWrap');
  if (!wrap) return;
  wrap.innerHTML = '<div class="muted">Loading contacts…</div>';
  const url = 'public/ajax/ajax_list_customer_contacts.php?customer_id=' + encodeURIComponent(customerId);
  fetch(url).then(r=>r.json()).then(json=>{
    if (!json || !json.success) { wrap.innerHTML = '<div class="muted">Failed to load contacts.</div>'; return; }
    renderContactsList(customerId, json.items || []);
  }).catch(err=>{
    console.error('fetchCustomerContacts', err);
    wrap.innerHTML = '<div class="muted">Failed to load contacts.</div>';
  });
}

function renderContactsList(customerId, contacts) {
  const wrap = qs('#custContactsWrap');
  wrap.innerHTML = '';
  if (!contacts || contacts.length === 0) {
    wrap.innerHTML = '<div class="muted">No contacts for this customer. Use "Add Contact" to create one.</div>';
    return;
  }
  contacts.forEach(c => {
    const row = document.createElement('div');
    row.className = 'customer-contact-row';
    row.innerHTML = '<div><div style="font-weight:700">' + escapeHtml(c.name || '') + '</div>'
              + '<div class="meta">' + (escapeHtml(c.designation || '') ? escapeHtml(c.designation)+' • ' : '') + escapeHtml(c.phone || '—') + (c.email ? ' • '+escapeHtml(c.email) : '') + '</div></div>'
              + '<div><button type="button" class="btn-as-select select-contact-btn"'
              + ' data-id="' + c.id + '"'
              + ' data-name="' + escapeHtml(c.name||'') + '"'
              + ' data-phone="' + escapeHtml(c.phone||'') + '"'
              + ' data-email="' + escapeHtml(c.email||'') + '"'
              + ' data-designation="' + escapeHtml(c.designation||'') + '"'
              + '>Select</button></div>';
    wrap.appendChild(row);
  });

  qsa('.select-contact-btn').forEach(b => b.addEventListener('click', function(){
    const id = this.dataset.id;
    const name = this.dataset.name || '';
    const phone = this.dataset.phone || '';
    const email = this.dataset.email || '';
    const designation = this.dataset.designation || '';
    if (qs('#detail-contact-entity-id')) qs('#detail-contact-entity-id').value = id;
    if (qs('#detail-contact-entity-type')) qs('#detail-contact-entity-type').value = 'customers_contacts';
    if (qs('#detail-contact-name')) qs('#detail-contact-name').value = name;
    if (qs('#detail-contact-phone')) qs('#detail-contact-phone').value = phone;
    if (qs('#detail-contact-email')) qs('#detail-contact-email').value = email;
    const activeCust = qs('#custList .cust-item.active');
    const custId = activeCust ? activeCust.dataset.id : '';
    const custCompany = activeCust ? (activeCust.dataset.company || '') : '';
    if (qs('#detail-customer-id')) qs('#detail-customer-id').value = custId;
    if (qs('#detail-customer-contact-id')) qs('#detail-customer-contact-id').value = id;
    if (qs('#summaryCompany')) qs('#summaryCompany').textContent = custCompany || '—';
    if (qs('#summaryContact')) qs('#summaryContact').textContent = name || '—';
    if (qs('#summaryPhone')) qs('#summaryPhone').textContent = phone || '—';
    if (qs('#summaryEmail')) qs('#summaryEmail').textContent = email || '—';
    if (qs('#summaryDestination')) qs('#summaryDestination').textContent = designation || '—';
    const wrap = qs('#customerSummaryWrap');
    if (wrap) wrap.style.display = '';
    if (qs('#detail-contact-name')) qs('#detail-contact-name').setAttribute('readonly','readonly');
    if (qs('#detail-contact-phone')) qs('#detail-contact-phone').setAttribute('readonly','readonly');
    if (qs('#detail-contact-email')) qs('#detail-contact-email').setAttribute('readonly','readonly');
    closeCustomerPicker();
  }));
}

/**
 * Open inline add-contact form in the right pane for the given parent entity.
 *
 * @param {string|number} parentId - id of customer/recruiter/supplier etc.
 * @param {string} contactsTable - e.g. 'customers_contacts'
 * @param {string} shortPrefix - short prefix used in ids (e.g. 'cust', 'rec', 'sup')
 * @param {string} ajaxUrl - endpoint to POST JSON to
 */
function openAddContactInline(parentId, contactsTable, shortPrefix, ajaxUrl) {
  const wrap = qs('#' + shortPrefix + 'ContactsWrap') || qs('#custContactsWrap') || qs('#recContactsWrap') || qs('#supContactsWrap');
  if (!wrap) return;

  // render form (use a stable ID we can target)
  wrap.innerHTML = `
    <div id="inlineAddContactBox" style="padding:8px;border-radius:6px;border:1px solid #eef2ff;background:#fff">
      <form id="addCustContactInline" style="display:flex;flex-direction:column;gap:8px">
        <div style="display:flex;gap:8px">
          <input name="name" placeholder="Name" required style="flex:1;padding:8px;border-radius:6px;border:1px solid #d1d5db"/>
          <input name="designation" placeholder="Designation" style="flex:1;padding:8px;border-radius:6px;border:1px solid #d1d5db"/>
        </div>
        <div style="display:flex;gap:8px">
          <input name="phone" placeholder="Phone" style="flex:1;padding:8px;border-radius:6px;border:1px solid #d1d5db"/>
          <input name="email" placeholder="Email" style="flex:1;padding:8px;border-radius:6px;border:1px solid #d1d5db"/>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px">
          <button type="button" id="cancelAddCust" class="btn-secondary">Cancel</button>
          <button type="submit" class="btn-primary">Save contact</button>
        </div>
      </form>
    </div>
  `;

  // Defensive: detach any previous handlers on these nodes (if they exist)
  const prevCancel = qs('#cancelAddCust');
  if (prevCancel) {
    try { prevCancel.onclick = null; } catch (e) {}
  }
  const prevForm = qs('#addCustContactInline');
  if (prevForm) {
    try { prevForm.onsubmit = null; } catch (e) {}
  }

  // Cancel handler: restore contacts list for the parent and remove the form
  const cancelBtn = qs('#cancelAddCust');
  if (cancelBtn) {
    cancelBtn.addEventListener('click', function (ev) {
      ev.preventDefault();
      ev.stopPropagation();
      // reload the contacts for this parent
      if (typeof fetchCustomerContacts === 'function' && contactsTable === 'customers_contacts') {
        fetchCustomerContacts(parentId);
      } else if (typeof fetchRecruiterContacts === 'function' && contactsTable === 'recruiters_contacts') {
        fetchRecruiterContacts(parentId);
      } else if (typeof fetchSupplierContacts === 'function' && contactsTable === 'suppliers_contacts') {
        fetchSupplierContacts(parentId);
      } else {
        // Fallback: clear and show placeholder
        const wrapBox = qs('#' + shortPrefix + 'ContactsWrap');
        if (wrapBox) wrapBox.innerHTML = '<div class="muted">No contacts for this entry. Use "Add Contact" to create one.</div>';
      }
    });
  }

  // Save handler: submit to AJAX endpoint and refresh list on success
  const form = qs('#addCustContactInline');
  if (form) {
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      ev.stopPropagation();

      const fd = new FormData(form);
      const payload = {
        // choose the correct field name expected by server:
        // customers_contacts endpoint expects "customer_id", recruiters -> "recruiter_id", suppliers -> "supplier_id"
        [contactsTable === 'customers_contacts' ? 'customer_id' : (contactsTable === 'recruiters_contacts' ? 'recruiter_id' : 'supplier_id')]:
          parentId,
        name: (fd.get('name') || '').trim(),
        designation: (fd.get('designation') || '').trim(),
        phone: (fd.get('phone') || '').trim(),
        email: (fd.get('email') || '').trim()
      };

      if (!payload.name) {
        alert('Name required');
        return;
      }

      // POST JSON
      fetch(ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
        .then(r => r.json())
        .then(json => {
          if (!json || !json.success) {
            alert('Add failed: ' + (json && json.error ? json.error : 'unknown'));
            return;
          }

          // Success: refresh the right pane list for this parent
          if (contactsTable === 'customers_contacts' && typeof fetchCustomerContacts === 'function') {
            fetchCustomerContacts(parentId);
          } else if (contactsTable === 'recruiters_contacts' && typeof fetchRecruiterContacts === 'function') {
            fetchRecruiterContacts(parentId);
          } else if (contactsTable === 'suppliers_contacts' && typeof fetchSupplierContacts === 'function') {
            fetchSupplierContacts(parentId);
          } else if (contactsTable === 'contacts_contacts' && typeof fetchSupplierContacts === 'function') {
            fetchContactContacts(parentId);
          } else {
            // fallback: clear and show message
            const wrapBox = qs('#' + shortPrefix + 'ContactsWrap');
            if (wrapBox) wrapBox.innerHTML = '<div class="muted">Contact added.</div>';
          }

          // remove/hide the inline form (clean UX)
          const formBox = qs('#inlineAddContactBox');
          if (formBox) formBox.remove();

          // Optionally show toast / alert
          // alert('Contact saved successfully.');
        })
        .catch(err => {
          console.error('addContact', err);
          alert('Failed to add contact (network).');
        });
    });
  }
}

/* small utility to escape text for safe insertion into HTML */
function escapeHtml(s) {
  if (s == null) return '';
  return String(s).replace(/[&<>"']/g, function (m) {
    switch (m) {
      case '&': return '&amp;';
      case '<': return '&lt;';
      case '>': return '&gt;';
      case '"': return '&quot;';
      case "'": return '&#39;';
      default: return m;
    }
  });
}

/* Legacy fallback entity picker used for non-customer types (limited) */
function openEntityPickerFallback(entityType) {
  const backdrop = qs('#modalBackdrop');
  if (!backdrop) return;
  const modalTitleEl = qs('#modalTitle');
  const modalInput = qs('#modalInput');
  const modalEntityList = qs('#modalEntityList');
  const modalSave = qs('#modalSave');

  if (modalTitleEl) modalTitleEl.textContent = 'Pick existing ' + entityType.replace(/s$/,'');
  backdrop.dataset.mode = 'entity';
  backdrop.dataset.target = 'contactTypeGroup';
  if (modalInput) modalInput.style.display = 'none';
  if (modalEntityList) { modalEntityList.style.display = 'flex'; modalEntityList.style.flexWrap = 'wrap'; modalEntityList.innerHTML = 'Loading...'; }
  if (modalSave) modalSave.textContent = 'Select';
  backdrop.dataset.visible = 'true';
  backdrop.style.display = 'flex';
  backdrop.style.alignItems = 'center';
  backdrop.style.justifyContent = 'center';
  backdrop.style.position = 'fixed';
  backdrop.style.inset = '0';
  backdrop.style.zIndex = '99999';
  const modal = qs('#modalBackdrop .modal');
  if (modal) { modal.style.position='relative'; modal.style.zIndex='100000'; modal.style.maxWidth='520px'; modal.style.width='calc(100% - 40px)'; modal.style.display='block'; }

  fetch('?page=bookings&list_entities=' + encodeURIComponent(entityType))
    .then(r=>r.json()).then(json=>{
      if (!json || !json.success) { if (modalEntityList) modalEntityList.innerHTML = '<div class="muted">Failed to load list.</div>'; return; }
      if (!modalEntityList) return;
      modalEntityList.innerHTML = '';
      json.items.forEach(it=>{
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'btn-as-select';
        b.style.margin = '4px';
        b.dataset.entityId = it.id;
        b.dataset.entityType = entityType;
        b._rawItem = it;
        b.textContent = it.name || ('#' + it.id);
        b.addEventListener('click', function(){
          modalEntityList.querySelectorAll('.btn-as-select').forEach(x=>x.classList.remove('active'));
          this.classList.add('active');
        });
        modalEntityList.appendChild(b);
      });
      if (json.items.length === 0) modalEntityList.innerHTML = '<div class="muted">No entries</div>';
    }).catch(e=>{
      console.error(e);
      if (modalEntityList) modalEntityList.innerHTML = '<div class="muted">Failed to load list</div>';
    });
}

/* Scenario group rebuild (unchanged) */
function rebuildScenarioGroup(scenarios, interType) {
  const group = qs('#scenarioGroup');
  if (!group) return;
  const existingAdd = group.querySelector('.btn-as-select.add');
  group.querySelectorAll('.btn-as-select').forEach(b => b.remove());
  scenarios.forEach(s => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-as-select';
    btn.dataset.id = s.id;
    btn.dataset.interType = s.inter_type || interType || 'customer';
    btn.textContent = s.name;
    group.appendChild(btn);
  });
  if (existingAdd) { group.appendChild(existingAdd); }
  else {
    const addBtn = document.createElement('button');
    addBtn.type = 'button';
    addBtn.className = 'btn-as-select add';
    addBtn.id = 'addScenarioBtn';
    addBtn.textContent = '+ Add New';
    group.appendChild(addBtn);
    addBtn.addEventListener('click', function(e){ openModalForGroup('scenarioGroup'); });
  }
  if (!group._initWired) {
    group.addEventListener('click', function(e){
      const btn = e.target.closest('.btn-as-select');
      if (!btn) return;
      if (btn.classList.contains('add')) { openModalForGroup('scenarioGroup'); return; }
      qsa('#scenarioGroup .btn-as-select').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      const hid = qs('#scenarioGroup input[type="hidden"]');
      if (hid) hid.value = btn.dataset.id || btn.dataset.value || '';
    });
    group._initWired = true;
  }
}

/* Apply scenario selection for the provided inter_type. (unchanged) */
function applyScenarioForType(interType) {
  if (!interType) interType = 'customer';
  fetch('public/ajax/ajax_list_scenarios.php?inter_type=' + encodeURIComponent(interType))
    .then(r => r.json())
    .then(json => {
      if (!json || !json.success) {
        clearActiveButtons('scenarioGroup');
        return;
      }
      const items = json.items || [];
      rebuildScenarioGroup(items, interType);
      if (items.length > 0) {
        const firstId = items[0].id;
        setActiveButtonByDataId('scenarioGroup', firstId);
      } else {
        clearActiveButtons('scenarioGroup');
      }
    })
    .catch(err => {
      console.error('applyScenarioForType error', err);
      clearActiveButtons('scenarioGroup');
    });
}

/* ===== Related entity UI (fixed): no "Choose" button; Yes opens picker immediately ===== */
function showRelatedEntityOptionForType(entityType) { 
  const wrap = qs('#relatedEntityWrap');
  if (!wrap) return;
  const label = qs('#relatedEntityQuestionLabel');
  const radioGroup = qs('#relatedEntityRadioGroup');
  const actions = qs('#relatedEntityActions');
  const selectedWrap = qs('#relatedSelectedList');
  const selectedNames = qs('#relatedSelectedNames');

  // reset UI
  radioGroup.querySelectorAll('.btn-as-select').forEach(b => b.classList.remove('active'));
  selectedNames.innerHTML = '';
  selectedWrap.style.display = 'none';
  if (qs('#related_employee_ids')) qs('#related_employee_ids').value = '';
  if (qs('#related_customer_id')) qs('#related_customer_id').value = '';

  if (entityType === 'customer') {
    label.textContent = 'Is this related with employees?';
    actions.innerHTML = ''; // no choose button; yes will open picker immediately
    wrap.style.display = '';
    const yesBtn = qs('#relatedYesBtn'), noBtn = qs('#relatedNoBtn');
    // default to No (clear)
    noBtn.classList.add('active'); yesBtn.classList.remove('active');
    // remove previous listeners by replacing nodes (defensive)
    const yesNew = yesBtn.cloneNode(true); yesBtn.parentNode.replaceChild(yesNew, yesBtn);
    const noNew = noBtn.cloneNode(true); noNew.parentNode.replaceChild(noNew, noBtn);
    noNew.addEventListener('click', function(){ setRelatedNo(); });
    yesNew.addEventListener('click', function(){ setRelatedYes_immediate('customer'); });
  } else if (entityType === 'employee') {
    label.textContent = 'Is this related with a customer?';
    actions.innerHTML = '';
    wrap.style.display = '';
    const yesBtn = qs('#relatedYesBtn'), noBtn = qs('#relatedNoBtn');
    noBtn.classList.add('active');
    const yesNew = yesBtn.cloneNode(true); yesBtn.parentNode.replaceChild(yesNew, yesBtn);
    const noNew = noBtn.cloneNode(true); noNew.parentNode.replaceChild(noNew, noBtn);
    noNew.addEventListener('click', function(){ setRelatedNo(); });
    yesNew.addEventListener('click', function(){ setRelatedYes_immediate('employee'); });
  } else {
    wrap.style.display = 'none';
  }
}

function setRelatedNo() {
  const yes = qs('#relatedYesBtn'), no = qs('#relatedNoBtn');
  if (yes) yes.classList.remove('active');
  if (no) no.classList.add('active');
  const actionsBtn = qs('#openRelatedPickerBtn');
  if (actionsBtn) actionsBtn.style.display = 'none';
  const selWrap = qs('#relatedSelectedList'); if (selWrap) selWrap.style.display = 'none';
  if (qs('#related_employee_ids')) qs('#related_employee_ids').value = '';
  if (qs('#related_customer_id')) qs('#related_customer_id').value = '';
}

/* Immediately open the appropriate modal when user clicks Yes */
function setRelatedYes_immediate(entityType) {
  safeBlurActiveElement();

  if (entityType === 'customer') {
    // open related employees modal (multi)
    openRelatedEmployeesModal();
  } else if (entityType === 'employee') {
    // open related customer modal (single)
    openRelatedCustomerModal();
  }
}

/* Open related pickers — robust: append to body, ensure high z-index, hide other backdrops */
function showModalById(modalBackdropId, dialogMaxWidth='720px') {
  const backdrop = qs('#' + modalBackdropId);
  if (!backdrop) return console.error('showModalById: missing', modalBackdropId);
  if (backdrop.parentElement !== document.body) document.body.appendChild(backdrop);
  if (document.activeElement) try { document.activeElement.blur(); } catch(e){}
  backdrop.setAttribute('aria-hidden', 'false');
  backdrop.dataset.visible = 'true';
  // hide other modal backdrops to ensure pointer-events and clicks go to this dialog
  document.querySelectorAll('.modal-backdrop').forEach(el=>{
    if (el === backdrop) return;
    try { el.dataset.prevDisplay = el.style.display || ''; el.style.display = 'none'; el.dataset.visible = 'false'; } catch(e){}
  });
  backdrop.style.display = 'flex';
  backdrop.style.position = 'fixed';
  backdrop.style.inset = '0';
  backdrop.style.alignItems = 'center';
  backdrop.style.justifyContent = 'center';
  backdrop.style.background = 'rgba(0,0,0,0.45)';
  backdrop.style.zIndex = '240000';
  const dialog = backdrop.querySelector('.modal');
  if (!dialog) return;
  dialog.style.display = 'block';
  dialog.style.position = 'relative';
  dialog.style.zIndex = '240001';
  dialog.style.boxSizing = 'border-box';
  dialog.style.maxWidth = dialog.style.maxWidth || dialogMaxWidth;
  dialog.style.width = dialog.style.width || 'min(' + dialogMaxWidth + ', calc(100% - 40px))';
  dialog.style.minWidth = dialog.style.minWidth || '360px';
  dialog.style.maxHeight = dialog.style.maxHeight || '85vh';
  dialog.style.overflow = 'auto';
  // ensure pointer events for the backdrop/dialog
  backdrop.style.pointerEvents = 'auto';
  dialog.style.pointerEvents = 'auto';
  setTimeout(()=> {
    const focusTarget = dialog.querySelector('button, input, [tabindex]:not([tabindex="-1"])');
    try { if (focusTarget) focusTarget.focus(); else dialog.focus(); } catch(e){}
  }, 40);
}

function openRelatedEmployeesModal() {
  showModalById('relatedEmployeesModal', '720px');

  // load content if not loaded already
  const listWrap = qs('#relatedEmployeesList');
  if (!listWrap) return;
  listWrap.innerHTML = '<div class="muted">Loading…</div>';

  // clear and focus search input (if present)
  const searchEl = qs('#relatedEmployeesSearch');
  if (searchEl) { searchEl.value = ''; searchEl.setAttribute('aria-label','Search employees'); }

  // Request employees (server supports emp_id searching as q)
  fetch('public/ajax/ajax_list_employees.php?limit=500')
    .then(r => r.json())
    .then(json => {
      if (!json || !json.success) { listWrap.innerHTML = '<div class="muted">Failed to load employees.</div>'; return; }
      listWrap.innerHTML = '';

      json.items.forEach(it => {
        const row = document.createElement('div');
        row.className = 'entity-item';
        // include emp_id in searchable text and dataset
        const empId = it.emp_id || '';
        const searchable = ((it.name || '') + ' ' + (it.company || '') + ' ' + (it.email || '') + ' ' + (it.phone || '') + ' ' + empId).toLowerCase();
        row.dataset.search = searchable;

        // build inner HTML showing emp_id visually and keep layout consistent
        row.innerHTML = `
          <div class="info" style="flex:1">
            <div style="font-weight:700">${escapeHtml(it.name || ('#' + it.id))}</div>
            <div class="meta">
              ${ empId ? ('EmpID: ' + escapeHtml(empId) + ' • ') : '' }
              ${escapeHtml((it.email || '') + (it.phone ? ' • ' + it.phone : ''))}
            </div>
          </div>
          <div style="flex-shrink:0">
            <input type="checkbox" data-id="${it.id}" data-empid="${escapeHtml(empId)}" data-name="${escapeHtml(it.name || '')}">
          </div>
        `;
        listWrap.appendChild(row);
      });

      if (json.items.length === 0) listWrap.innerHTML = '<div class="muted">No employees found.</div>';

      // wire search filtering (debounced)
      if (searchEl) {
        let searchTimer = null;
        const doFilter = () => {
          const q = (searchEl.value || '').trim().toLowerCase();
          const rows = listWrap.querySelectorAll('.entity-item');
          rows.forEach(r => {
            if (!q || (r.dataset.search && r.dataset.search.indexOf(q) !== -1)) {
              r.style.display = '';
            } else {
              r.style.display = 'none';
            }
          });
        };
        // remove previous handler if present
        searchEl.removeEventListener('input', searchEl._relatedSearchHandler || function(){});
        const handler = function() {
          clearTimeout(searchTimer);
          searchTimer = setTimeout(doFilter, 160);
        };
        searchEl._relatedSearchHandler = handler;
        searchEl.addEventListener('input', handler);
        try { searchEl.focus(); } catch(e){ /* ignore */ }
      }
    }).catch(err => {
      console.error('fetch employees', err);
      listWrap.innerHTML = '<div class="muted">Failed to load employees.</div>';
    });

  // wire close/save handlers (idempotent)
  qs('#relatedEmployeesClose').onclick = closeRelatedEmployeesModal;
  qs('#relatedEmployeesCancel').onclick = closeRelatedEmployeesModal;
  qs('#relatedEmployeesSave').onclick = function(){
    // collect checked items from the listWrap (only visible rows will be considered)
    const checks = listWrap.querySelectorAll('input[type="checkbox"]:checked');
    const picked = Array.from(checks).map(c => ({
      id: c.dataset.id,
      emp_id: c.dataset.empid || '',
      name: c.dataset.name || ''
    }));
    finishRelatedEmployeesSelection(picked);
    closeRelatedEmployeesModal();
  };
}

function closeRelatedEmployeesModal() {
  const backdrop = qs('#relatedEmployeesModal');
  if (!backdrop) return;
  backdrop.setAttribute('aria-hidden','true');
  backdrop.dataset.visible = 'false';
  backdrop.style.display = 'none';
  // restore other modal backdrops previous display (if any)
  document.querySelectorAll('.modal-backdrop').forEach(el=>{
    if (el.dataset.prevDisplay !== undefined) { el.style.display = el.dataset.prevDisplay || ''; el.dataset.prevDisplay = undefined; }
  });
  const listWrap = qs('#relatedEmployeesList'); if (listWrap) listWrap.innerHTML = '';
}

function openRelatedCustomerModal() {
  showModalById('relatedCustomerModal', '720px');
  const listWrap = qs('#relatedCustomerList');
  if (!listWrap) return;
  listWrap.innerHTML = '<div class="muted">Loading…</div>';
  fetch('public/ajax/ajax_list_customers.php?limit=500')
    .then(r=>r.json())
    .then(json=>{
      if (!json || !json.success) { listWrap.innerHTML = '<div class="muted">Failed to load customers.</div>'; return; }
      listWrap.innerHTML = '';
      json.items.forEach(it=>{
        const row = document.createElement('div');
        row.className = 'entity-item';
        const info = document.createElement('div'); info.className='info';
        const title = document.createElement('div'); title.style.fontWeight='700'; title.innerHTML = escapeHtml(it.name || ('#'+it.id));
        const meta = document.createElement('div'); meta.className='meta'; meta.style.fontSize='13px'; meta.style.color='var(--muted)';
        meta.textContent = it.company ? it.company : (it.email || it.phone ? (it.email || '') + (it.phone ? ' • ' + it.phone : '') : '');
        info.appendChild(title); info.appendChild(meta);
        const ctrl = document.createElement('div');
        const selBtn = document.createElement('button'); selBtn.type='button'; selBtn.className='btn-as-select'; selBtn.textContent='Select';
        selBtn.dataset.id = it.id; selBtn.dataset.name = it.name || '';
        selBtn.addEventListener('click', function(){ finishRelatedCustomerSelection({ id: selBtn.dataset.id, name: selBtn.dataset.name }); closeRelatedCustomerModal(); });
        ctrl.appendChild(selBtn);
        row.appendChild(info); row.appendChild(ctrl);
        listWrap.appendChild(row);
      });
      if (json.items.length === 0) listWrap.innerHTML = '<div class="muted">No customers found.</div>';
    }).catch(err=>{
      console.error('fetch customers', err);
      listWrap.innerHTML = '<div class="muted">Failed to load customers.</div>';
    });

  const closeBtn = qs('#relatedCustomerClose'), cancelBtn = qs('#relatedCustomerCancel');
  if (closeBtn) closeBtn.onclick = closeRelatedCustomerModal;
  if (cancelBtn) cancelBtn.onclick = closeRelatedCustomerModal;
}

function closeRelatedCustomerModal() {
  const backdrop = qs('#relatedCustomerModal');
  if (!backdrop) return;
  backdrop.setAttribute('aria-hidden','true');
  backdrop.dataset.visible = 'false';
  backdrop.style.display = 'none';
  document.querySelectorAll('.modal-backdrop').forEach(el=>{
    if (el.dataset.prevDisplay !== undefined) { el.style.display = el.dataset.prevDisplay || ''; el.dataset.prevDisplay = undefined; }
  });
  const listWrap = qs('#relatedCustomerList'); if (listWrap) listWrap.innerHTML = '';
}

/* called when user selects items from modal */
function finishRelatedSelection(type, multi, items) {
  if (type === 'employees') {
    const ids = items.map(i => i.id).join(',');
    qs('#related_employee_ids').value = ids;
    const wrap = qs('#relatedSelectedNames'); wrap.innerHTML = '';
    items.forEach(i => {
      const c = document.createElement('div'); c.className='related-chip'; c.textContent = i.name;
      const rem = document.createElement('span'); rem.className='remove'; rem.textContent=' ×';
      rem.addEventListener('click', function(){
        const cur = (qs('#related_employee_ids').value||'').split(',').filter(x=>x);
        const newIds = cur.filter(x=>x !== i.id);
        qs('#related_employee_ids').value = newIds.join(',');
        c.remove();
        if ((qs('#related_employee_ids').value||'') === '') qs('#relatedSelectedList').style.display = 'none';
      });
      c.appendChild(rem);
      wrap.appendChild(c);
    });
    qs('#relatedSelectedList').style.display = items.length ? '' : 'none';
  } else if (type === 'customers') {
    const sel = items[0];
    if (!sel) return;
    qs('#related_customer_id').value = sel.id;
    const wrap = qs('#relatedSelectedNames'); wrap.innerHTML = '';
    const c = document.createElement('div'); c.className='related-chip'; c.textContent = sel.name;
    const rem = document.createElement('span'); rem.className='remove'; rem.textContent=' ×';
    rem.addEventListener('click', function(){
      qs('#related_customer_id').value = '';
      c.remove();
      if (!qs('#related_customer_id').value) qs('#relatedSelectedList').style.display = 'none';
    });
    c.appendChild(rem);
    wrap.appendChild(c);
    qs('#relatedSelectedList').style.display = '';
  }
}

/* Hook close behaviour for modalBackdrop when used as related selector */
(function(){
  const origClose = qs('#modalCancel');
  if (origClose) origClose.addEventListener('click', function(){
    const backdrop = qs('#modalBackdrop');
    if (backdrop && backdrop.dataset.mode === 'related') { closeRelatedModal(); return; }
    closeModalSmall();
  });
})();

/* Integration: show related option based on contactTypeGroup selection (redundant but safe) */
(function wireRelatedIntegration(){
  const ctGroup = qs('#contactTypeGroup');
  if (!ctGroup) return;
  ctGroup.addEventListener('click', function(e){
    const btn = e.target.closest('.btn-as-select');
    if (!btn) return;
    const txt = (btn.textContent || '').toLowerCase();
    if (/existing\s+customer/i.test(txt)) {
      showRelatedEntityOptionForType('customer');
    } else if (/existing\s+employee/i.test(txt)) {
      showRelatedEntityOptionForType('employee');
    } else {
      const wrap = qs('#relatedEntityWrap'); if (wrap) wrap.style.display = 'none';
    }
  });
  const activeCt = qs('#contactTypeGroup .btn-as-select.active');
  if (activeCt) {
    const txt = (activeCt.textContent || '').toLowerCase();
    if (/existing\s+customer/i.test(txt)) showRelatedEntityOptionForType('customer');
    else if (/existing\s+employee/i.test(txt)) showRelatedEntityOptionForType('employee');
  }
})();

/* helper used by legacy related modal code - kept but simplified */
function closeRelatedModal() {
  const backdrop = qs('#modalBackdrop'); if (!backdrop) return; backdrop.style.display = 'none'; backdrop.dataset.mode = ''; backdrop.dataset.relatedType = '';
  const entityList = qs('#modalEntityList'); if (entityList) entityList.innerHTML = '';
}

/* finishRelatedEmployeesSelection and finishRelatedCustomerSelection reuse previous logic */
function finishRelatedEmployeesSelection(items) {
  const ids = items.map(i => i.id).join(',');
  qs('#related_employee_ids').value = ids;
  const wrap = qs('#relatedSelectedNames'); wrap.innerHTML = '';
  items.forEach(i => {
    const chip = document.createElement('div'); chip.className = 'related-chip'; chip.textContent = i.name;
    const rem = document.createElement('span'); rem.className = 'remove'; rem.textContent = '×';
    rem.addEventListener('click', function(){
      const cur = (qs('#related_employee_ids').value||'').split(',').filter(x=>x);
      const newIds = cur.filter(x=>x !== i.id);
      qs('#related_employee_ids').value = newIds.join(',');
      chip.remove();
      if ((qs('#related_employee_ids').value||'') === '') qs('#relatedSelectedList').style.display = 'none';
    });
    chip.appendChild(rem);
    wrap.appendChild(chip);
  });
  qs('#relatedSelectedList').style.display = items.length ? '' : 'none';
}

function finishRelatedCustomerSelection(item) {
  if (!item) return;
  qs('#related_customer_id').value = item.id;
  const wrap = qs('#relatedSelectedNames'); wrap.innerHTML = '';
  const chip = document.createElement('div'); chip.className = 'related-chip'; chip.textContent = item.name;
  const rem = document.createElement('span'); rem.className = 'remove'; rem.textContent = '×';
  rem.addEventListener('click', function(){
    qs('#related_customer_id').value = '';
    chip.remove();
    if (!qs('#related_customer_id').value) qs('#relatedSelectedList').style.display = 'none';
  });
  chip.appendChild(rem);
  wrap.appendChild(chip);
  qs('#relatedSelectedList').style.display = '';
}

/* Defensive: ensure related modals are direct children of body and aria state consistent */
(function ensureRelatedModalsAreBodyChildren(){
  try {
    document.querySelectorAll('#relatedEmployeesModal,#relatedCustomerModal,#customerPickerModal,#modalBackdrop').forEach(el => {
      if (!el) return;
      if (el.parentElement !== document.body) document.body.appendChild(el);
      if (el.style.display && el.style.display !== 'none') el.setAttribute('aria-hidden','false'); else el.setAttribute('aria-hidden','true');
    });
  } catch(e){ console.error('ensureRelatedModalsAreBodyChildren', e); }
})();

/* Utility: safely remove focus from any focused element */
function safeBlurActiveElement(){
  try {
    const ae = document.activeElement;
    if (!ae) return;
    try { ae.blur(); } catch(e){ }
    try { document.body.focus(); } catch(e){ }
  } catch(e){ console.error('safeBlurActiveElement error', e); }
}

/* Hide any leftover "Choose ..." buttons just in case */
(function hideChooseButtonsIfAny(){
  try {
    const chooseBtns = document.querySelectorAll('#openRelatedPickerBtn, .choose-related-btn, .related-choose');
    chooseBtns.forEach(b => { try { b.style.display = 'none'; } catch(e){} });
  } catch(e){ }
})();

/* Loaders for related modals (kept) */
function loadRelatedEmployeesList() {
    const list = qs('#relatedEmployeesList');
    if (!list) return;
    list.innerHTML = '<div class="muted">Loading…</div>';
    fetch('public/ajax/ajax_list_employees.php?limit=500')
        .then(r=>r.json())
        .then(json=>{
            if (!json || !json.success) { list.innerHTML = '<div class="muted">Failed to load employees.</div>'; return; }
            list.innerHTML = '';
            json.items.forEach(it=>{
                const row = document.createElement('div');
                row.className = 'entity-item';
                row.innerHTML = `
                    <div class="info">
                        <div style="font-weight:700">${escapeHtml(it.name)}</div>
                        <div class="meta">${escapeHtml(it.company || it.email || it.phone || '')}</div>
                    </div>
                    <div><input type="checkbox" data-id="${it.id}" data-name="${escapeHtml(it.name)}"></div>
                `;
                list.appendChild(row);
            });
            if (!json.items.length) list.innerHTML = '<div class="muted">No employees found.</div>';
        });
}

function loadRelatedCustomerList() {
    const list = qs('#relatedCustomerList');
    if (!list) return;
    list.innerHTML = '<div class="muted">Loading…</div>';
    fetch('public/ajax/ajax_list_customers.php?limit=500')
        .then(r=>r.json())
        .then(json=>{
            if (!json || !json.success) { list.innerHTML = '<div class="muted">Failed to load customers.</div>'; return; }
            list.innerHTML = '';
            json.items.forEach(it=>{
                const row = document.createElement('div');
                row.className = 'entity-item';
                row.innerHTML = `
                    <div class="info">
                        <div style="font-weight:700">${escapeHtml(it.name)}</div>
                        <div class="meta">${escapeHtml(it.company || it.email || it.phone || '')}</div>
                    </div>
                    <div><button class="btn-as-select" data-id="${it.id}" data-name="${escapeHtml(it.name)}">Select</button></div>
                `;
                row.querySelector('button').onclick = () => {
                    finishRelatedCustomerSelection({
                        id: it.id,
                        name: it.name
                    });
                    closeRelatedCustomerModal();
                };
                list.appendChild(row);
            });
            if (!json.items.length) list.innerHTML = '<div class="muted">No customers found.</div>';
        });
}

function showRelatedEntityOptionForType(entityType) {
  const wrap = qs('#relatedEntityWrap');
  if (!wrap) return;
  const label = qs('#relatedEntityQuestionLabel');
  const radioGroup = qs('#relatedEntityRadioGroup');
  const actions = qs('#relatedEntityActions');
  const selectedWrap = qs('#relatedSelectedList');
  const selectedNames = qs('#relatedSelectedNames');

  // defensive: ensure required DOM nodes exist
  if (!radioGroup) {
    console.warn('showRelatedEntityOptionForType: missing #relatedEntityRadioGroup');
    return;
  }
  const yesBtn = qs('#relatedYesBtn');
  const noBtn  = qs('#relatedNoBtn');

  // reset UI
  radioGroup.querySelectorAll('.btn-as-select').forEach(b => b.classList.remove('active'));
  selectedNames.innerHTML = '';
  if (selectedWrap) selectedWrap.style.display = 'none';
  if (qs('#related_employee_ids')) qs('#related_employee_ids').value = '';
  if (qs('#related_customer_id')) qs('#related_customer_id').value = '';

  // helper to safely replace a node, or append new if parent missing
  function safeReplace(oldNode, newNode) {
    if (!oldNode || !newNode) return;
    const parent = oldNode.parentNode;
    if (parent) {
      try { parent.replaceChild(newNode, oldNode); return; } catch(e){ console.warn('safeReplace replaceChild failed', e); }
    }
    // fallback: ensure new node is present inside radioGroup
    if (radioGroup && !radioGroup.contains(newNode)) radioGroup.appendChild(newNode);
  }

  // helper to create fresh button with given id/text and click handler
  function makeToggleBtn(id, text, handler) {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'btn-as-select';
    b.id = id;
    b.textContent = text;
    b.addEventListener('click', handler);
    return b;
  }

  if (entityType === 'customer') {
    if (label) label.textContent = 'Is this related with employees?';
    actions.innerHTML = ''; // remove any "Choose" button – we open the picker directly
    wrap.style.display = '';

    // create new nodes and wire handlers (safe, no reliance on existing parent)
    const yesNew = makeToggleBtn('relatedYesBtn', 'Yes', function() { 
      // mark states
      const noEl = qs('#relatedNoBtn'); if (noEl) noEl.classList.remove('active');
      this.classList.add('active');
      setRelatedYes_immediate('customer');
    });
    const noNew = makeToggleBtn('relatedNoBtn', 'No', function() {
      const y = qs('#relatedYesBtn'); if (y) y.classList.remove('active');
      this.classList.add('active');
      setRelatedNo();
    });

    // attempt to replace existing nodes; fallback to appending if not possible
    safeReplace(yesBtn, yesNew);
    safeReplace(noBtn, noNew);

    // default to No selected
    const curNo = qs('#relatedNoBtn');
    if (curNo) curNo.classList.add('active');

  } else if (entityType === 'employee') {
    if (label) label.textContent = 'Is this related with a customer?';
    actions.innerHTML = '';
    wrap.style.display = '';

    const yesNew = makeToggleBtn('relatedYesBtn', 'Yes', function() {
      const noEl = qs('#relatedNoBtn'); if (noEl) noEl.classList.remove('active');
      this.classList.add('active');
      setRelatedYes_immediate('employee');
    });
    const noNew = makeToggleBtn('relatedNoBtn', 'No', function() {
      const y = qs('#relatedYesBtn'); if (y) y.classList.remove('active');
      this.classList.add('active');
      setRelatedNo();
    });

    safeReplace(yesBtn, yesNew);
    safeReplace(noBtn, noNew);

    const curNo = qs('#relatedNoBtn');
    if (curNo) curNo.classList.add('active');

  } else {
    wrap.style.display = 'none';
  }
}

/* ===== Contacts picker: open/close/fetch/render (mirrors customer picker) ===== */
function openContactsPicker(){
  const pick = qs('#contactsPickerModal');
  if (!pick) return console.error('openContactsPicker: no #contactsPickerModal');
  if (pick.parentElement !== document.body) document.body.appendChild(pick);
  pick.setAttribute('aria-hidden','false'); pick.dataset.visible='true'; pick.style.display='flex';
  pick.style.position='fixed'; pick.style.inset='0'; pick.style.alignItems='center'; pick.style.justifyContent='center';
  pick.style.background='rgba(0,0,0,0.45)'; pick.style.zIndex='220000';
  const dialog = qs('#contactsPickerModal .customer-modal'); if (dialog) { dialog.style.display='block'; dialog.style.zIndex='220001'; }
  qs('#contList').innerHTML = '<div class="muted">Loading…</div>';
  qs('#contContactsWrap').innerHTML = '<div class="muted">No source selected.</div>';
  qs('#contSelectedLabel').textContent = 'Pick a source on the left';
  qs('#contAddContactBtn').style.display = 'none';
  fetchContacts();
}
function closeContactsPicker(){
  const pick = qs('#contactsPickerModal'); if (!pick) return;
  pick.setAttribute('aria-hidden','true'); pick.dataset.visible='false'; pick.style.display='none';
  qs('#contList').innerHTML=''; qs('#contContactsWrap').innerHTML=''; qs('#contSelectedLabel').textContent='';
  const fallback = qs('#wiz-next-1') || qs('#wiz-next-2') || document.body; try { if (fallback) fallback.focus(); } catch(e){}
}

(function wireContactsModalControls(){
  function attach(){
    const pick = qs('#contactsPickerModal'); if (!pick) return;
    const closeBtn = qs('#contactsModalClose'); if (closeBtn) closeBtn.addEventListener('click', closeContactsPicker);
    const clearBtn = qs('#contClearSelection'); if (clearBtn) clearBtn.addEventListener('click', function(){
      const list = qs('#contList'); if (list) list.querySelectorAll('.cust-item').forEach(x=>x.classList.remove('active'));
      qs('#contContactsWrap').innerHTML = '<div class="muted">No source selected.</div>';
      qs('#contSelectedLabel').textContent = 'Pick a source on the left'; qs('#contAddContactBtn').style.display='none';
    });
    const selectBtn = qs('#contSelectBtn'); if (selectBtn) selectBtn.addEventListener('click', function(){
      const active = qs('#contList .cust-item.active'); if (!active) return alert('Please select an entry');
      const name = active.dataset.name || active.textContent.trim();
      if (qs('#detail-contact-name')) qs('#detail-contact-name').value = name;
      if (qs('#detail-contact-entity-id')) qs('#detail-contact-entity-id').value = active.dataset.id || '';
      if (qs('#detail-contact-entity-type')) qs('#detail-contact-entity-type').value = 'contacts';
      closeContactsPicker();
    });
    const addContactBtn = qs('#contAddContactBtn'); if (addContactBtn) addContactBtn.addEventListener('click', function(){
      const active = qs('#contList .cust-item.active'); if (!active) return alert('Select a source first');
      openAddContactInline(active.dataset.id, 'contacts_contacts', 'cont', 'public/ajax/ajax_add_contact_contact.php');
    });
    const search = qs('#contSearch'); if (search) {
      let timer = null;
      search.addEventListener('input', function(){
        clearTimeout(timer); const q = (this.value||'').trim().toLowerCase();
        timer = setTimeout(()=> { const list = qs('#contList'); if (!list) return;
          list.querySelectorAll('.cust-item').forEach(it => {
            const txt = (it.dataset.name || it.textContent).toLowerCase();
            it.style.display = (q === '' || txt.indexOf(q) !== -1) ? '' : 'none';
          });
        }, 160);
      });
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', attach); else attach();
})();

function fetchContacts(){
  const list = qs('#contList'); if (!list) return;
  list.innerHTML = '<div class="muted">Loading…</div>';
  fetch('public/ajax/ajax_list_contacts.php?limit=500').then(r=>r.json()).then(json=>{
    if (!json || !json.success) { list.innerHTML='<div class="muted">Failed to load.</div>'; return; }
    list.innerHTML='';
    json.items.forEach(it=>{
      const b = document.createElement('div');
      b.className = 'cust-item';
      b.style.cursor = 'pointer';
      b.style.padding = '12px';
      b.style.borderRadius = '8px';
      b.style.border = '1px solid #eef2ff';
      b.style.marginBottom = '8px';

      b.dataset.id = it.id;
      b.dataset.name = it.name || '';
      // IMPORTANT: store interType so we can load appropriate scenarios when selected
      // Server should ideally return inter_type for items (e.g. 'customer','contacts','employee', etc.)
      b.dataset.interType = it.inter_type || 'contact';

      b.innerHTML =
        `<div style="font-weight:600; margin-bottom:4px;">${escapeHtml(it.name || ('#'+it.id))}</div>
         <div class="meta" style="font-size:13px; color:#6b7280;">
            ${escapeHtml((it.email? it.email : '') + (it.phone ? ' • ' + it.phone : ''))}
         </div>`;

      b.addEventListener('click', function(){
        list.querySelectorAll('.cust-item').forEach(x => x.classList.remove('active'));
        b.classList.add('active');

        qs('#contSelectedLabel').textContent = 'Contacts for ' + (it.name || ('#'+it.id));
        qs('#contAddContactBtn').style.display = '';

        // fetch contact-specific contacts (existing behavior)
        fetchContactContacts(it.id);

        // new behaviour: use interType to load scenario set for this entity type
        const interType = (b.dataset.interType || 'contact').toString();
        try {
          applyScenarioForType(interType);
        } catch (err) {
          console.error('applyScenarioForType error for contacts', err);
        }
      });
      list.appendChild(b);
    });
    if (!json.items.length) list.innerHTML = '<div class="muted">No entries</div>';
  }).catch(err=>{
    console.error('fetchContacts',err);
    if (list) list.innerHTML = '<div class="muted">Failed to load.</div>';
  });
}

function fetchContactContacts(sourceId) {
  const wrap = qs('#contContactsWrap'); if (!wrap) return; wrap.innerHTML = '<div class="muted">Loading contacts…</div>';
  fetch('public/ajax/ajax_list_contact_contacts.php?contact_id=' + encodeURIComponent(sourceId)).then(r=>r.json()).then(json=>{
    if (!json || !json.success) { wrap.innerHTML = '<div class="muted">Failed to load contacts.</div>'; return; }
    renderRightContactsList(sourceId, json.items || [], 'contacts_contacts', 'cont');
  }).catch(err=>{ console.error(err); wrap.innerHTML = '<div class="muted">Failed to load contacts.</div>'; });
}

/* ------- Recruiters (mirror contacts functions, endpoints adjusted) ------- */

function openRecruitersPicker() {
  // same as openContactsPicker but targets #recruitersPickerModal and fetchRecruiters
  if (qs('#recList')) {
    fetchRecruiters();
  }
  showModalById('recruitersPickerModal', '720px');
}

function closeRecruitersPicker() {
  const pick = qs('#recruitersPickerModal');
  if (!pick) return;
  pick.setAttribute('aria-hidden', 'true');
  pick.dataset.visible = 'false';
  pick.style.display = 'none';

  const list = qs('#recList');
  if (list) list.innerHTML = '';

  const wrap = qs('#recContactsWrap');
  if (wrap) wrap.innerHTML = '';
}

(function wireRecruitersModalControls() {
  try {
    const pick = qs('#recruitersPickerModal');
    if (!pick) return;

    const closeBtn = qs('#recruitersModalClose');
    if (closeBtn) closeBtn.onclick = closeRecruitersPicker;

    const clearBtn = qs('#recClearSelection');
    if (clearBtn) {
      clearBtn.onclick = function () {
        const list = qs('#recList');
        if (list) list.querySelectorAll('.cust-item').forEach(x => x.classList.remove('active'));
        qs('#recContactsWrap').innerHTML = '<div class="muted">No recruiter selected.</div>';
        qs('#recSelectedLabel').textContent = 'Pick a recruiter on the left';
        qs('#recAddContactBtn').style.display = 'none';
      };
    }

    const selectBtn = qs('#recSelectBtn');
    if (selectBtn) {
      selectBtn.onclick = function () {
        const active = qs('#recList .cust-item.active');
        if (!active) return alert('Please select a recruiter');
        if (qs('#detail-contact-name')) qs('#detail-contact-name').value = active.dataset.name || '';
        if (qs('#detail-contact-entity-id')) qs('#detail-contact-entity-id').value = active.dataset.id || '';
        if (qs('#detail-contact-entity-type')) qs('#detail-contact-entity-type').value = 'recruiters';
        closeRecruitersPicker();
      };
    }

    const addBtn = qs('#recAddContactBtn');
    if (addBtn) {
      addBtn.onclick = function () {
        const active = qs('#recList .cust-item.active');
        if (!active) return alert('Select a recruiter first');
        openAddContactInline(active.dataset.id, 'recruiters_contacts', 'rec', 'public/ajax/ajax_add_recruiter_contact.php');
      };
    }

    const search = qs('#recSearch');
    if (search) {
      let timer = null;
      search.addEventListener('input', function () {
        clearTimeout(timer);
        const q = (this.value || '').trim().toLowerCase();
        timer = setTimeout(() => {
          const list = qs('#recList');
          if (!list) return;
          list.querySelectorAll('.cust-item').forEach(it => {
            const txt = (it.dataset.name || it.textContent).toLowerCase();
            it.style.display = (!q || txt.indexOf(q) !== -1) ? '' : 'none';
          });
        }, 160);
      });
    }
  } catch (e) {
    console.error(e);
  }
})();


function fetchRecruiters() {
  const list = qs('#recList');
  if (!list) return;

  list.innerHTML = '<div class="muted">Loading…</div>';

  fetch('public/ajax/ajax_list_recruiters.php?limit=500')
    .then(r => r.json())
    .then(json => {

      if (!json || !json.success) {
        list.innerHTML = '<div class="muted">Failed to load.</div>';
        return;
      }

      list.innerHTML = '';

      json.items.forEach(it => {

        const b = document.createElement('div');

        // -----------------------------------------
        // FULL CUSTOMER-STYLING APPLIED HERE
        // -----------------------------------------
        b.className = 'cust-item';
        b.style.cursor = 'pointer';
        b.style.padding = '12px';
        b.style.borderRadius = '8px';
        b.style.border = '1px solid #eef2ff';
        b.style.marginBottom = '8px';
        b.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
        // -----------------------------------------

        b.dataset.id = it.id;
        b.dataset.name = it.name || '';
        // store interType so scenario set can be loaded for this entity
        b.dataset.interType = it.inter_type || 'recruiter';

        b.innerHTML =
          `<div style="font-weight:600; margin-bottom:4px;">
              ${escapeHtml(it.name || ('#' + it.id))}
           </div>
           <div class="meta" style="font-size:13px; color:#6b7280;">
              ${
                it.company
                ? escapeHtml(it.company)
                : (it.email || it.phone
                    ? escapeHtml(it.email || '') + (it.phone ? ' • ' + escapeHtml(it.phone) : '')
                    : '')
              }
           </div>`;

        b.addEventListener('click', () => {
          list.querySelectorAll('.cust-item').forEach(x => x.classList.remove('active'));
          b.classList.add('active');
          qs('#recSelectedLabel').textContent = 'Contacts for ' + (it.name || ('#' + it.id));
          qs('#recAddContactBtn').style.display = '';
          fetchRecruiterContacts(it.id);

          // New: apply scenario set for this entity type
          const interType = (b.dataset.interType || 'recruiter').toString();
          try { applyScenarioForType(interType); } catch (err) { console.error('applyScenarioForType error for recruiters', err); }
        });

        list.appendChild(b);
      });

      if (!json.items.length) list.innerHTML = '<div class="muted">No entries</div>';

    })
    .catch(err => {
      console.error('fetchRecruiters', err);
      list.innerHTML = '<div class="muted">Failed to load.</div>';
    });
}

function fetchRecruiterContacts(id) {
  const wrap = qs('#recContactsWrap');
  if (!wrap) return;

  wrap.innerHTML = '<div class="muted">Loading contacts…</div>';

  fetch('public/ajax/ajax_list_recruiters_contacts.php?recruiter_id=' + encodeURIComponent(id))
    .then(r => r.json())
    .then(json => {
      if (!json || !json.success) {
        wrap.innerHTML = '<div class="muted">Failed to load contacts.</div>';
        return;
      }
      renderRightContactsList(id, json.items || [], 'recruiters_contacts', 'rec');
    })
    .catch(err => {
      console.error(err);
      wrap.innerHTML = '<div class="muted">Failed to load contacts.</div>';
    });
}


/* ------- Suppliers (mirror recruiters) ------- */

function openSuppliersPicker() {
  showModalById('suppliersPickerModal', '720px');
  fetchSuppliers();
}

function closeSuppliersPicker() {
  const pick = qs('#suppliersPickerModal');
  if (!pick) return;
  pick.setAttribute('aria-hidden', 'true');
  pick.dataset.visible = 'false';
  pick.style.display = 'none';

  const list = qs('#supList');
  if (list) list.innerHTML = '';

  const wrap = qs('#supContactsWrap');
  if (wrap) wrap.innerHTML = '';
}

(function wireSuppliersModalControls() {
  try {
    const pick = qs('#suppliersPickerModal');
    if (!pick) return;

    const closeBtn = qs('#suppliersModalClose');
    if (closeBtn) closeBtn.onclick = closeSuppliersPicker;

    const clearBtn = qs('#supClearSelection');
    if (clearBtn) {
      clearBtn.onclick = function () {
        const list = qs('#supList');
        if (list) list.querySelectorAll('.cust-item').forEach(x => x.classList.remove('active'));
        qs('#supContactsWrap').innerHTML = '<div class="muted">No supplier selected.</div>';
        qs('#supSelectedLabel').textContent = 'Pick a supplier on the left';
        qs('#supAddContactBtn').style.display = 'none';
      };
    }

    const selectBtn = qs('#supSelectBtn');
    if (selectBtn) {
      selectBtn.onclick = function () {
        const active = qs('#supList .cust-item.active');
        if (!active) return alert('Please select a supplier');
        if (qs('#detail-contact-name')) qs('#detail-contact-name').value = active.dataset.name || '';
        if (qs('#detail-contact-entity-id')) qs('#detail-contact-entity-id').value = active.dataset.id || '';
        if (qs('#detail-contact-entity-type')) qs('#detail-contact-entity-type').value = 'suppliers';
        closeSuppliersPicker();
      };
    }

    const addBtn = qs('#supAddContactBtn');
    if (addBtn) {
      addBtn.onclick = function () {
        const active = qs('#supList .cust-item.active');
        if (!active) return alert('Select a supplier first');
        openAddContactInline(active.dataset.id, 'suppliers_contacts', 'sup', 'public/ajax/ajax_add_supplier_contact.php');
      };
    }

    const search = qs('#supSearch');
    if (search) {
      let timer = null;
      search.addEventListener('input', function () {
        clearTimeout(timer);
        const q = (this.value || '').trim().toLowerCase();
        timer = setTimeout(() => {
          const list = qs('#supList');
          if (!list) return;
          list.querySelectorAll('.cust-item').forEach(it => {
            const txt = (it.dataset.name || it.textContent).toLowerCase();
            it.style.display = (!q || txt.indexOf(q) !== -1) ? '' : 'none';
          });
        }, 160);
      });
    }
  } catch (e) {
    console.error(e);
  }
})();


function fetchSuppliers() {
  const list = qs('#supList');
  if (!list) return;

  list.innerHTML = '<div class="muted">Loading…</div>';

  fetch('public/ajax/ajax_list_suppliers.php?limit=500')
    .then(r => r.json())
    .then(json => {

      if (!json || !json.success) {
        list.innerHTML = '<div class="muted">Failed to load.</div>';
        return;
      }

      list.innerHTML = '';

      json.items.forEach(it => {

        const b = document.createElement('div');

        // -----------------------------------------
        // FULL CUSTOMER-STYLING APPLIED HERE
        // -----------------------------------------
        b.className = 'cust-item';
        b.style.cursor = 'pointer';
        b.style.padding = '12px';
        b.style.borderRadius = '8px';
        b.style.border = '1px solid #eef2ff';
        b.style.marginBottom = '8px';
        b.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
        // -----------------------------------------

        b.dataset.id = it.id;
        b.dataset.name = it.name || '';
        // store interType so scenario set can be loaded for this entity
        b.dataset.interType = it.inter_type || 'supplier';

        b.innerHTML =
          `<div style="font-weight:600; margin-bottom:4px;">
              ${escapeHtml(it.name || ('#' + it.id))}
           </div>
           <div class="meta" style="font-size:13px; color:#6b7280;">
              ${
                it.company
                ? escapeHtml(it.company)
                : (it.email || it.phone
                    ? escapeHtml(it.email || '') + (it.phone ? ' • ' + escapeHtml(it.phone) : '')
                    : '')
              }
           </div>`;

        b.addEventListener('click', () => {
          list.querySelectorAll('.cust-item').forEach(x => x.classList.remove('active'));
          b.classList.add('active');
          qs('#supSelectedLabel').textContent = 'Contacts for ' + (it.name || ('#' + it.id));
          qs('#supAddContactBtn').style.display = '';
          fetchSupplierContacts(it.id);

          // New: apply scenario set for this entity type
          const interType = (b.dataset.interType || 'supplier').toString();
          try { applyScenarioForType(interType); } catch (err) { console.error('applyScenarioForType error for suppliers', err); }
        });

        list.appendChild(b);
      });

      if (!json.items.length) list.innerHTML = '<div class="muted">No entries</div>';

    })
    .catch(err => {
      console.error('fetchSuppliers', err);
      list.innerHTML = '<div class="muted">Failed to load.</div>';
    });
}


function fetchSupplierContacts(id) {
  const wrap = qs('#supContactsWrap');
  if (!wrap) return;

  wrap.innerHTML = '<div class="muted">Loading contacts…</div>';

  fetch('public/ajax/ajax_list_suppliers_contacts.php?supplier_id=' + encodeURIComponent(id))
    .then(r => r.json())
    .then(json => {
      if (!json || !json.success) {
        wrap.innerHTML = '<div class="muted">Failed to load contacts.</div>';
        return;
      }
      renderRightContactsList(id, json.items || [], 'suppliers_contacts', 'sup');
    })
    .catch(err => {
      console.error(err);
      wrap.innerHTML = '<div class="muted">Failed to load contacts.</div>';
    });
}



function renderRightContactsList(sourceId, contacts, entityType, shortPrefix) {
  const wrap = qs('#' + shortPrefix + 'ContactsWrap');
  if (!wrap) return;
  wrap.innerHTML = '';
  if (!contacts || contacts.length === 0) {
    wrap.innerHTML = '<div class="muted">No contacts for this entry. Use "Add Contact" to create one.</div>';
    return;
  }
  contacts.forEach(c => {
    const row = document.createElement('div');
    row.className = 'customer-contact-row';
    row.innerHTML = '<div><div style="font-weight:700">' + escapeHtml(c.name || '') + '</div>'
              + '<div class="meta">' + (escapeHtml(c.designation || '') ? escapeHtml(c.designation)+' • ' : '') + escapeHtml(c.phone || '—') + (c.email ? ' • '+escapeHtml(c.email) : '') + '</div></div>'
              + '<div><button type="button" class="btn-as-select select-' + shortPrefix + '-contact-btn"'
              + ' data-id="' + c.id + '"'
              + ' data-name="' + escapeHtml(c.name||'') + '"'
              + ' data-phone="' + escapeHtml(c.phone||'') + '"'
              + ' data-email="' + escapeHtml(c.email||'') + '"'
              + ' data-designation="' + escapeHtml(c.designation||'') + '"'
              + '>Select</button></div>';
    wrap.appendChild(row);
  });

  qsa('.select-' + shortPrefix + '-contact-btn').forEach(b => b.addEventListener('click', function(){
    const id = this.dataset.id;
    const name = this.dataset.name || '';
    const phone = this.dataset.phone || '';
    const email = this.dataset.email || '';
    const designation = this.dataset.designation || '';
    if (qs('#detail-contact-entity-id')) qs('#detail-contact-entity-id').value = id;
    if (qs('#detail-contact-entity-type')) qs('#detail-contact-entity-type').value = entityType;
    if (qs('#detail-contact-name')) qs('#detail-contact-name').value = name;
    if (qs('#detail-contact-phone')) qs('#detail-contact-phone').value = phone;
    if (qs('#detail-contact-email')) qs('#detail-contact-email').value = email;
    // If modal contains an owning entity id (like customer), set the hidden 'customer' equivalent for form compatibility
    if (qs('#detail-customer-id')) {
      // Only set to sourceId if sourceId has a value
      qs('#detail-customer-id').value = sourceId || '';
    }
    if (qs('#detail-customer-contact-id')) {
      qs('#detail-customer-contact-id').value = id;
    }
    if (qs('#summaryCompany')) qs('#summaryCompany').textContent = ''; // optional: you can populate a summary if desired
    if (qs('#summaryContact')) qs('#summaryContact').textContent = name || '—';
    if (qs('#summaryPhone')) qs('#summaryPhone').textContent = phone || '—';
    if (qs('#summaryEmail')) qs('#summaryEmail').textContent = email || '—';
    if (qs('#summaryDestination')) qs('#summaryDestination').textContent = designation || '—';
    const wrapSummary = qs('#customerSummaryWrap'); if (wrapSummary) wrapSummary.style.display = '';
    if (qs('#detail-contact-name')) qs('#detail-contact-name').setAttribute('readonly','readonly');
    if (qs('#detail-contact-phone')) qs('#detail-contact-phone').setAttribute('readonly','readonly');
    if (qs('#detail-contact-email')) qs('#detail-contact-email').setAttribute('readonly','readonly');

    // Close the corresponding modal
    if (shortPrefix === 'cont') closeContactsPicker();
    else if (shortPrefix === 'rec') closeRecruitersPicker();
    else if (shortPrefix === 'sup') closeSuppliersPicker();
  }));
}

</script>

<script>
/*
 * Existing Employee modal JS v2
 * Scenarios are global: load where inter_type = 'employee'
 *
 * Expected AJAX endpoints (adapt to your URLs if different):
 *  - POST /public/ajax/ajax_list_employees.php { q }
 *    -> { success:true, items:[{id, name, email, phone}, ...] }
 *
 *  - POST /public/ajax/ajax_list_scenarios.php { inter_type: 'employee' }
 *    -> { success:true, items:[{id, name/title, slug, created_at}, ...] }
 *
 *  - POST /public/ajax/ajax_add_scenario.php { inter_type: 'employee', title }
 *    -> { success:true, item:{id, title (or name), slug, inter_type } }
 *
 * All functions and IDs are ee- namespaced to avoid collisions.
 */

window.ee_state = { employee: null, scenario: null };

// create/ensure hidden fields inside the modal footer so main page can read them
function ensureHidden(name, id, val) {
  var $f = $('#' + (id||'')).length ? $('#' + id) : $('input[name="'+name+'"]');
  if (!$f.length) {
    $f = $('<input>').attr({ type: 'hidden', name: name, id: id || name }).appendTo($('form').first() || $('body'));
  }
  $f.val(val||'');
}

(function($){
  if (!$) return console.warn('ee-modal requires jQuery');

  var $backdrop = $('#existingCustomerModalBackdrop');
  var $modal = $('#existingCustomerModal');
  var $searchInput = $('#ee-search-input');
  var $searchBtn = $('#ee-search-btn');
  var $customerList = $('#ee-customer-list');
  var $scenarioList = $('#ee-scenario-list');
  var $empAddScenarioBtn = $('#empAddScenarioBtn'); // new
  var $empAddScenarioform = $('#ee-add-row'); // new
  var $empAddScenarioClose = $('#ee-add-scenario-btn-close'); // new
  var $eeFooter = $(".ee-footer") // new
  var $newScenarioInput = $('#ee-new-scenario-input');
  var $addScenarioBtn = $('#ee-add-scenario-btn');
  var $closeBtn = $('#ee-close-btn');
  var $cancelBtn = $('#ee-cancel');
  var $confirmBtn = $('#ee-select-confirm');
  var $selectedEmployeeId = $('#ee-selected-customer-id');
  var $selectedScenarioId = $('#ee-selected-scenario-id');

  var ee_scenarios_cache = null;

  function bindInlineTypeSelection() {

    const $list = $('#ee-scenario-listnew');
    const $selectedScenarioId = $('#ee-selected-scenario-id');
    const $confirmBtn = $('#ee-select-confirm'); // or save button if different

    $list.find('.ee-scenario-item').off('click').on('click', function () {

      // UI selection
      $list.find('.ee-scenario-item.selected').removeClass('selected');
      $(this).addClass('selected');

      // scenario object (same structure as existing)
      const sc = {
        id: $(this).data('ee-id'),
        title: $(this).data('ee-title')
      };

      // update state
      ee_state.scenario = sc;

      ensureHidden('existing_employee_scenario_id','existing_employee_scenario_id', sc.id);
      ensureHidden('existing_employee_scenario_title','existing_employee_scenario_title', sc.title);

      document.querySelector('#detail-scenario').value = sc.id;
      // $selectedScenarioId.val(sc.id || '');

      // enable confirm/save if employee/customer already selected
      if (typeof ee_state !== 'undefined') {
        $confirmBtn.prop('disabled', !(ee_state.employee && ee_state.scenario));
      }
    });
  }
  bindInlineTypeSelection();

  function centerAndShow() {
    // ensure backdrop is direct child of body and shown centered
    if ($backdrop.parent().length === 0 || $backdrop.parent()[0] !== document.body) {
      $(document.body).append($backdrop);
    }
    $backdrop.css('display','flex').attr('aria-hidden','false');
    // focus search
    setTimeout(function(){ $searchInput.focus(); }, 60);
  }

  window.ee_openExistingCustomerModal = function() {
    // reset selections
    ee_state.employee = null; ee_state.scenario = null;
    $selectedEmployeeId.val(''); $selectedScenarioId.val('');
    $confirmBtn.prop('disabled', true);
    // load employees (first page) and scenarios (once)
    ee_fetchEmployees('', 50);
    ee_loadScenariosOnce(renderScenarioList);
    // show modal
    centerAndShow();
  };

  function ee_close() {
    $backdrop.hide().attr('aria-hidden','true');
  }

  $closeBtn.on('click', ee_close);
  $cancelBtn.on('click', ee_close);
  $backdrop.on('click', function(e){ if (e.target === this) ee_close(); });

  //new
  $empAddScenarioBtn.on('click',function() {
    $scenarioList.toggle();
    $empAddScenarioform.toggle();
    $empAddScenarioBtn.toggle();
    $eeFooter.toggle();
  });
  $empAddScenarioClose.on('click',function() {
    $scenarioList.toggle();
    $empAddScenarioform.toggle();
    $empAddScenarioBtn.toggle();
    $eeFooter.toggle();
  });

  // initial load / search
  $searchBtn.on('click', function(){ ee_fetchEmployees($searchInput.val(), 200); });
  // $searchInput.on('keyup', function(e){ if (e.key === 'Enter') ee_fetchEmployees($searchInput.val(), 200); });
  $searchInput.on('keyup', function(e){ ee_fetchEmployees($searchInput.val(), 200); });

  // load employees (GET for compatibility)
  // function ee_fetchEmployees(q, limit) {
  window.ee_fetchEmployees = function (q, limit) {
    limit = limit || 50;
    $customerList.html('<div class="muted">Loading…</div>');
    var url = 'public/ajax/ajax_list_customers.php?limit=' + encodeURIComponent(limit);
    if (q && q.trim() !== '') url += '&q=' + encodeURIComponent(q.trim());
    $.getJSON(url).done(function(res){
      if (!res || !res.success) { $customerList.html('<div class="muted">Failed to load employees.</div>'); return; }
      var items = res.items || [];
      if (items.length === 0) { $customerList.html('<div class="muted">No employees found.</div>'); return; }
      var html = '';
items.forEach(function(it){
    // Define variables FIRST
    var displayName = it.name || ('#' + it.id);
    var meta       = it.email ? it.email : '';
    var empId      = it.emp_id || ''; // backend now returns this

    html += '<div class="ee-customer-item"'
         +   ' data-ee-id="' + it.id + '"'
         +   ' data-ee-name="' + escapeHtml(displayName) + '"'
         +   ' data-ee-email="' + escapeHtml(it.email || '') + '"'
         +   ' data-ee-phone="' + escapeHtml(it.phone || '') + '"'
         +   ' data-ee-empid="' + escapeHtml(empId) + '"'
         + '>'
         +   '<div style="font-weight:700;">' + escapeHtml(displayName) + '</div>'
         +   '<div style="font-size:12px;color:#6b7280;">'
         +       (it.phone ? '' + escapeHtml(it.phone) + ' ' : '')
         +       (meta ? ' • ' + escapeHtml(meta) : '')
         +   '</div>'
         + '</div>';
});
$customerList.html(html);

// Click handler (KEEP AS IS, but update to read empid)
$customerList.find('.ee-customer-item').off('click').on('click', function(){
    $customerList.find('.ee-customer-item.selected').removeClass('selected');
    $(this).addClass('selected');

    var emp = {
        id:    $(this).data('ee-id'),
        name:  $(this).data('ee-name'),
        email: $(this).data('ee-email'),
        phone: $(this).data('ee-phone'),
        emp_id: $(this).data('ee-empid')
    };

    ee_setEmployee(emp);
});

    }).fail(function(){ $customerList.html('<div class="muted">Failed to load employees.</div>'); });
  }

//   function ee_setEmployee(emp) {
//     // emp contains id, name, email, phone from ajax_list_employees.php
//     ee_state.employee = emp;

//     // Store in modal hidden fields
//     $('#ee-selected-customer-id').val(emp.id);
//     $('#ee-selected-customer-name').val(emp.name || '');
//     $('#ee-selected-customer-phone').val(emp.phone || '');
//     $('#ee-selected-customer-email').val(emp.email || '');

//     if ($('#ee-selected-customer-empid').length === 0) {
//     $('<input>').attr({ type: 'hidden', id: 'ee-selected-customer-empid', name: 'ee_selected_customer_empid' }).appendTo($('form').first() || $('body'));
//   }
//   $('#ee-selected-customer-empid').val(emp.emp_id || '');
//   $confirmBtn.prop('disabled', !(ee_state.employee && ee_state.scenario));
// }

  function ee_setEmployee(emp) {
    if (typeof ee_state === 'undefined') return;

    ee_state.employee = emp;

    $('#ee-selected-customer-id').val(emp.id || '');
    $('#ee-selected-customer-name').val(emp.name || '');
    $('#ee-selected-customer-phone').val(emp.phone || '');
    $('#ee-selected-customer-email').val(emp.email || '');

    /* Default scenario = Flight (id 1) */
    if (!ee_state.scenario) {
      ee_state.scenario = { id: 1, title: 'Flights' };
      $('#ee-selected-scenario-id').val(1);

      ensureHidden('existing_employee_scenario_id','existing_employee_scenario_id', 1);
      ensureHidden('existing_employee_scenario_title','existing_employee_scenario_title', 'Flights');
      document.querySelector('#detail-scenario').value = 1;

      $('#ee-scenario-listnew .ee-scenario-item')
        .removeClass('selected')
        .filter('[data-ee-id="1"]')
        .addClass('selected');
    }

    $('#ee-select-confirm')
      .prop('disabled', !(ee_state.employee && ee_state.scenario));
  }


  // scenarios: load once with GET for compatibility
  function ee_loadScenariosOnce(cb) {
    if (ee_scenarios_cache) { if (typeof cb === 'function') cb(ee_scenarios_cache); return; }
    $scenarioList.html('<div class="muted">Loading types</div>');
    $.getJSON('public/ajax/ajax_list_bookings_types.php?inter_type=employee&limit=500').done(function(res){
      ee_scenarios_cache = (res && res.success) ? (res.items || []) : [];
      if (typeof cb === 'function') cb(ee_scenarios_cache);
    }).fail(function(){ ee_scenarios_cache = []; if (typeof cb === 'function') cb([]); });
  }

  function renderScenarioList(list) {
    var items = list || ee_scenarios_cache || [];
    if (!items.length) { $scenarioList.html('<div class="muted">No types found.</div>'); return; }
    var html = '';
    items.forEach(function(s){
      var title = s.title || s.name || s.slug || 'Scenario ' + s.id;
      html += '<div class="ee-scenario-item" data-ee-id="' + s.id + '" data-ee-title="' + escapeHtml(title) + '">' +
              escapeHtml(title) + (s.created_at ? '<div style="font-size:12px;color:#6b7280;margin-top:6px;">' + escapeHtml(s.created_at) + '</div>' : '') +
              '</div>';
    });
    $scenarioList.html(html);
    $scenarioList.find('.ee-scenario-item').off('click').on('click', function(){
      $scenarioList.find('.ee-scenario-item.selected').removeClass('selected');
      $(this).addClass('selected');
      var sc = { id: $(this).data('ee-id'), title: $(this).data('ee-title') };
      ee_setScenario(sc);
    });
    // re-evaluate confirm enable
    $confirmBtn.prop('disabled', !(ee_state.employee && ee_state.scenario));
  }

  $addScenarioBtn.on('click', function(){
    var title = $newScenarioInput.val().trim();
    if (!title) { $newScenarioInput.focus(); return; }
    $addScenarioBtn.prop('disabled', true).text('Adding…');
    $.ajax({
      url: 'public/ajax/ajax_add_scenario.php',
      method: 'POST',
      data: { inter_type: 'employee', title: title },
      dataType: 'json'
    }).done(function(res){
      if (!res || !res.success) { alert('Failed to add scenario'); return; }
      var item = res.item;
      // normalise returned object keys
      if (!item.name && item.title) item.name = item.title;
      ee_scenarios_cache = ee_scenarios_cache || [];
      ee_scenarios_cache.unshift(item);
      renderScenarioList(ee_scenarios_cache);
      // auto-select the new scenario
      var sel = $scenarioList.find('.ee-scenario-item').first();
      if (sel.length) sel.trigger('click');
      $empAddScenarioClose.click();
      $newScenarioInput.val('');
    }).fail(function(){ alert('Request failed'); }).always(function(){ $addScenarioBtn.prop('disabled', false).text('Add'); });
  });

  function ee_setScenario(sc) {
    ee_state.scenario = sc;
    $selectedScenarioId.val(sc.id || '');
    $confirmBtn.prop('disabled', !(ee_state.employee && ee_state.scenario));
  }

  $confirmBtn.on('click', function(){
  if (!ee_state.employee || !ee_state.scenario) { alert('Select a customer and a type.'); return; }

  var eventDetail = {
    employee_id: ee_state.employee.id,
    employee_empid: ee_state.employee.emp_id || '',
    employee_name: ee_state.employee.name,
    employee_phone: ee_state.employee.phone || '',
    employee_email: ee_state.employee.email || '',
    scenario_id: ee_state.scenario.id,
    scenario_title: ee_state.scenario.title || ee_state.scenario.name || ''
  };

  ensureHidden('existing_employee_id','existing_employee_id', eventDetail.employee_id);
  ensureHidden('existing_employee_empid','existing_employee_empid', eventDetail.employee_empid);
  ensureHidden('existing_employee_name','existing_employee_name', eventDetail.employee_name);
  ensureHidden('existing_employee_phone','existing_employee_phone', eventDetail.employee_phone);
  ensureHidden('existing_employee_email','existing_employee_email', eventDetail.employee_email);
  ensureHidden('existing_employee_scenario_id','existing_employee_scenario_id', eventDetail.scenario_id);
  ensureHidden('existing_employee_scenario_title','existing_employee_scenario_title', eventDetail.scenario_title);

  document.querySelector('#detail-scenario').value = eventDetail.scenario_id;

  // dispatch the event (other code listens to this)
  document.dispatchEvent(new CustomEvent('ee.employeeSelected', { detail: eventDetail }));

  // close modal
  ee_close();
});

  // small utility escape (same as other code)
  function escapeHtml(s){ if (s == null) return ''; return String(s).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]; }); }

  // keyboard: Esc closes
  $(document).on('keydown.eeModal', function(e){ if (e.key === 'Escape') { if ($backdrop.is(':visible')) ee_close(); } });

  // cleanup on unload
  window.ee_getSelection = function(){ return { employee: ee_state.employee, scenario: ee_state.scenario }; };

})(window.jQuery);
</script>

<script>
document.addEventListener('ee.employeeSelected', function(e){
    const d = e.detail;

    // Hidden fields for saving
    $('#detail-contact-entity-type').val('employees');
    $('#detail-contact-entity-id').val(d.employee_id);

    // Fill read-only fields (same as customer)
    $('#detail-contact-name').val(d.employee_name).prop('readonly', true);
    $('#detail-contact-phone').val(d.employee_phone).prop('readonly', true);
    $('#detail-contact-email').val(d.employee_email).prop('readonly', true);

    // Summary UI (same layout as existing customer)
    $('#summaryCompany').text(''); // employees have no company
    $('#summaryContact').text(d.employee_name);
    $('#summaryPhone').text(d.employee_phone);
    $('#summaryEmail').text(d.employee_email);
    $('#summaryDestination').text(''); // or designation if you have it
    if (d.employee_empid) {
    // show it in summary (e.g., append to summaryContact or add new element)
    // example: append to summaryContactMeta
    const metaEl = qs('#summaryContactMeta');
    if (metaEl) metaEl.textContent = 'EmpID: ' + d.employee_empid;
  }

    $('#customerSummaryWrap').show();

    (function mirrorEeScenarioToMain(d){
  // d.scenario_id is provided by the modal
  if (!d || !d.scenario_id) return;

  // prefer an existing hidden #detail-scenario-id; create if missing
  var $s = $('#detail-scenario-id');
  if (!$s.length) {
    // append to the main form if present, else to body
    var $form = $('#detailForm');
    $s = $('<input>').attr({ type: 'hidden', id: 'detail-scenario-id', name: 'detail_scenario_id' });
    if ($form.length) $form.append($s); else $('body').append($s);
  }
  // set value (string/number)
  $s.val(d.scenario_id);

  // also, for safety, set a field named scenario_id that server might expect (optional)
  var $s2 = $('#scenario_id');
  if (!$s2.length) {
    var $form = $('#detailForm');
    $s2 = $('<input>').attr({ type: 'hidden', id: 'scenario_id', name: 'scenario_id' });
    if ($form.length) $form.append($s2); else $('body').append($s2);
  }
  $s2.val(d.scenario_id);

  console.log('[debug] mirrored modal scenario -> detail-scenario-id =', d.scenario_id);
})(d);

});
</script>

<script>
(function(){

  // Run this every time Step 2 is shown
  function ee_applyScenarioIfEmployee() {
    var typeEl = document.querySelector('#detail-contact-entity-type');

    var scIdEl = document.querySelector('input[name="existing_employee_scenario_id"]');
    var scTitleEl = document.querySelector('input[name="existing_employee_scenario_title"]');

    if (!typeEl || !scIdEl || !scTitleEl) return;

    var isEmployee = (typeEl.value === 'employees');
    var isCustomer = (typeEl.value === 'customers');
    var hasScenario = (scIdEl.value && scTitleEl.value);

    var scenarioGroup = document.getElementById('scenarioGroup'); // your existing scenario picker container

    if (!scenarioGroup) return;

    // alert(typeEl.value+" - "+isEmployee+" "+scTitleEl.value+" - "+hasScenario);

    // if (isEmployee && hasScenario) {
    if (hasScenario) {
        // Hide the normal scenario options
        scenarioGroup.style.display = 'none';

        // Create OR update a read-only block
        var ro = document.getElementById('ee-readonly-scenario');
        if (!ro) {
            ro = document.createElement('div');
            ro.id = 'ee-readonly-scenario';
            ro.style.margin = '12px 0';
            ro.style.fontSize = '15px';
            scenarioGroup.parentNode.insertBefore(ro, scenarioGroup);
        }

        ro.innerHTML =
            '<strong>Selected Booking Type</strong><br>' +
            '<div style="padding:6px 10px;border:1px solid #ddd;border-radius:6px;background:#fafafa;font-weight:600;">'
            + scTitleEl.value +
            '</div>';

    } else {
        // For all other entity types → show normal scenario UI
        scenarioGroup.style.display = '';
        var ro2 = document.getElementById('ee-readonly-scenario');
        if (ro2) ro2.remove();
    }

    var flightdiv = document.getElementById("flightDiv");
    if(hasScenario) {
      if(scTitleEl.value=='Flights') {
        flightdiv.style.display = "block";
        $("#summaryDiv").hide();
      }
      else {
        flightdiv.style.display = "none";
        $("#summaryDiv").show();
      }
      $("#Btitle").text(scTitleEl.value+' Booking');
      $("#detail-subject").val(scTitleEl.value+' Booking');
    }
    else {
      $("#summaryDiv").show();
      $("#detail-subject").val('Booking');
      flightdiv.style.display = "none";
    }
  }

  // Patch your existing showStep function safely
  if (typeof window.showStep === 'function') {
    var _oldShowStep = window.showStep;
    window.showStep = function(n){
      _oldShowStep(n);
      if (parseInt(n,10) === 2) {
        ee_applyScenarioIfEmployee();
      }
    };
  }

})();
</script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const btnGroup = document.querySelector('#channelGroup');
    if (!btnGroup) return;

    const defaultPhoneBtn = btnGroup.querySelector('.btn-as-select[data-id="1"]');
    if (defaultPhoneBtn) {
        // Visually select the button
        defaultPhoneBtn.classList.add('selected', 'active');

        // Update hidden input
        document.querySelector('#detail-channel').value = "1";
    }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

  const dropZone  = document.getElementById("dropZone");
  const fileInput = document.getElementById("document_file");
  const fileName  = document.getElementById("fileName");

  const allowedTypes = [
    "image/",
    "application/pdf",
    "video/"
  ];

  function isAllowed(file) {
    return allowedTypes.some(t => file.type.startsWith(t));
  }

  function showFile(file) {
    if (!isAllowed(file)) {
      fileInput.value = "";
      fileName.className = "file-name error";
      fileName.textContent = "Invalid file type. Only image, PDF, or video allowed.";
      fileName.classList.remove("d-none");
      return false;
    }

    fileName.className = "file-name";
    fileName.textContent = "Selected: " + file.name;
    fileName.classList.remove("d-none");
    return true;
  }

  /* -------- File Select -------- */
  fileInput.addEventListener("change", function () {
    if (this.files.length) {
      showFile(this.files[0]);
    }
  });

  /* -------- Drag & Drop -------- */
  dropZone.addEventListener("dragover", e => {
    e.preventDefault();
    dropZone.classList.add("dragover");
  });

  dropZone.addEventListener("dragleave", () => {
    dropZone.classList.remove("dragover");
  });

  dropZone.addEventListener("drop", e => {
    e.preventDefault();
    dropZone.classList.remove("dragover");

    if (e.dataTransfer.files.length) {
      const file = e.dataTransfer.files[0];
      if (!isAllowed(file)) return showFile(file);

      const dt = new DataTransfer();
      dt.items.add(file);
      fileInput.files = dt.files;
      showFile(file);
    }
  });

  /* -------- Paste Support -------- */
  document.addEventListener("paste", e => {
    const items = e.clipboardData?.items || [];

    for (let item of items) {
      if (item.kind === "file") {
        const file = item.getAsFile();
        if (!file) continue;

        if (!isAllowed(file)) return showFile(file);

        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        showFile(file);
        break;
      }
    }
  });

});
</script>

<script>
window.POPULAR_AIRPORTS = [
  { code:'LHR', name:'London Heathrow (LHR), UK' },
  { code:'LGW', name:'London Gatwick (LGW), UK' },
  { code:'MAN', name:'Manchester (MAN), UK' },
  { code:'EDI', name:'Edinburgh (EDI), UK' },
  { code:'DUB', name:'Dublin (DUB), Ireland' },
  { code:'CDG', name:'Paris Charles de Gaulle (CDG), France' },
  { code:'ORY', name:'Paris Orly (ORY), France' },
  { code:'AMS', name:'Amsterdam Schiphol (AMS), Netherlands' },
  { code:'FRA', name:'Frankfurt (FRA), Germany' },
  { code:'MUC', name:'Munich (MUC), Germany' },
  { code:'BCN', name:'Barcelona (BCN), Spain' },
  { code:'MAD', name:'Madrid (MAD), Spain' },
  { code:'IST', name:'Istanbul (IST), Türkiye' },
  { code:'DXB', name:'Dubai (DXB), UAE' },
  { code:'DOH', name:'Doha (DOH), Qatar' },
  { code:'JFK', name:'New York JFK (JFK), USA' },
  { code:'EWR', name:'Newark (EWR), USA' },
  { code:'LAX', name:'Los Angeles (LAX), USA' },
  { code:'SFO', name:'San Francisco (SFO), USA' },
  { code:'YYZ', name:'Toronto Pearson (YYZ), Canada' },
  { code:'SIN', name:'Singapore Changi (SIN), Singapore' },
  { code:'BOM', name:'Mumbai (BOM), India' },
  { code:'DEL', name:'Delhi (DEL), India' },
  { code:'COK', name:'Kochi (COK), India' },
  { code:'MAA', name:'Chennai (MAA), India' },
  { code:'BLR', name:'Bengaluru (BLR), India' },
  { code:'ICN', name:'Seoul Incheon (ICN), South Korea' },
  { code:'HND', name:'Tokyo Haneda (HND), Japan' },
  { code:'NRT', name:'Tokyo Narita (NRT), Japan' },
  { code:'SYD', name:'Sydney (SYD), Australia' }
];

$(function(){
  // Populate airport selects if present
  if ($('#origin').length) {
    const opts = POPULAR_AIRPORTS.map(a => `<option value="${a.code}">${a.name}</option>`).join('');
    $('#origin,#destination').append(opts);
  }
  // Default dates (tomorrow and +7 days)
  if ($('#departureDate').length) {
    const today = new Date();
    const dep = new Date(today.getFullYear(), today.getMonth(), today.getDate()+1).toISOString().slice(0,10);
    const ret = new Date(today.getFullYear(), today.getMonth(), today.getDate()+7).toISOString().slice(0,10);
    $('#departureDate').val(dep);
    $('#returnDate').val(ret);
  }

  // Datatables on bookings list if table exists
  if ($('#bookings-table').length) {
    $('#bookings-table').DataTable();
  }
});
</script>