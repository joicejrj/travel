<?php
// bookings_list.php
require_once __DIR__ . '/_auth.php'; // adjust if your auth path differs
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php'; // optional

require_once __DIR__ . '/includes/header.php'; // include your page header (nav, styles)

$uid = $_SESSION['person_id'] ?? 0;
$uname = $_SESSION['person_name'] ?? 'Agent';

?>
<!-- Page content: Filters on top -> DataTable below (Layout A) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<style>
/* Minimal styling to match your app */
.page-filters { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; margin:14px 0; }
.filter-field { display:flex; flex-direction:column; min-width:180px; }
.filter-field label { font-size:12px; color:#6b7280; margin-bottom:6px; font-weight:600; }
.filter-actions { display:flex; gap:8px; align-items:center; }
.dt-actions button { margin-right:6px; padding:6px 8px; border-radius:6px; border:1px solid #e5e7eb; background:#fff; cursor:pointer; }
#bookingsTable { width:100% !important; }
</style>

<div class="container" style="padding:18px;">
  <a href="./?page=bookings_add" class="btn btn-sm btn-primary float-end"><i class="fa fa-plus"></i> New</a>
  <!-- <a href="./?page=admin_dashboard" class="btn btn-sm btn-outline-success float-end me-2"><i class="fa fa-line-chart"></i> Admin Dashboard</a> -->
  <h4>Bookings</h4>
  <div class="page-filters">
    <div class="filter-field">
      <label for="filter-daterange">Date range</label>
      <input type="hidden" id="filter-daterange-raw">
      <input type="text" id="filter-daterange" placeholder="Pick date range" readonly style="padding:8px;border-radius:6px;border:1px solid #d1d5db;width:220px">
    </div>

    <div class="filter-field" style="min-width: 100px;">
      <label for="filter-channel">Channel</label>
      <select id="filter-channel" style="padding:8px;border-radius:6px;border:1px solid #d1d5db;">
        <option value="">All</option>
        <?php
        $rs = $mysqli->query("SELECT id,name FROM channels ORDER BY name");
        while ($r = $rs->fetch_assoc()) {
            echo '<option value="'.(int)$r['id'].'">'.htmlspecialchars($r['name']).'</option>';
        }
        $rs->free();
        ?>
      </select>
    </div>

    <div class="filter-field d-none">
      <label for="filter-contact-type">Contact Type</label>
      <select id="filter-contact-type" style="padding:8px;border-radius:6px;border:1px solid #d1d5db;">
        <option value="">All</option>
        <?php
        $rs = $mysqli->query("SELECT id,name FROM contact_types ORDER BY name");
        while ($r = $rs->fetch_assoc()) {
            echo '<option value="'.(int)$r['id'].'">'.htmlspecialchars($r['name']).'</option>';
        }
        $rs->free();
        ?>
      </select>
    </div>

    <div class="filter-field">
      <label for="filter-type">Type</label>
      <select id="filter-type" style="padding:8px;border-radius:6px;border:1px solid #d1d5db;">
        <option value="">All</option>
        <?php
        $rs = $mysqli->query("SELECT id,name FROM bookings_types ORDER BY name");
        while ($r = $rs->fetch_assoc()) {
            echo '<option value="'.(int)$r['id'].'">'.htmlspecialchars($r['name']).'</option>';
        }
        $rs->free();
        ?>
      </select>
    </div>

    <div class="filter-field d-none">
      <label for="filter-assigned">Assigned To</label>
      <select id="filter-assigned" style="padding:8px;border-radius:6px;border:1px solid #d1d5db;">
        <option value="">Anyone</option>
        <?php
        $rs = $mysqli->query("SELECT id,name FROM people ORDER BY name");
        while ($r = $rs->fetch_assoc()) {
            echo '<option value="'.(int)$r['id'].'">'.htmlspecialchars($r['name']).'</option>';
        }
        $rs->free();
        ?>
      </select>
    </div>

     <div class="filter-field" style="min-width: 100px;">
      <label for="filter-status">Status</label>
  <?php
    $booking_statuses = array('New','Updated','Issue request','Awaiting Payment','Issued','Expired');
  ?>
      <select id="filter-status" style="padding:8px;border-radius:6px;border:1px solid #d1d5db;">
        <option value="">All Status</option>
      <?php
        foreach ($booking_statuses as $bstat) {
      ?>
        <option value="<?=$bstat?>"><?=$bstat?></option>
      <?php
        }
      ?>
      </select>
    </div>

    <div class="filter-actions">
      <button id="applyFilters" class="btn-primary" style="padding:8px 12px;border-radius:6px;border:0;background:#2563eb;color:#fff;">Apply</button>
      <button id="clearFilters" class="btn-secondary" style="padding:8px 12px;border-radius:6px;border:1px solid #d1d5db;background:#fff;">Clear</button>
    </div>
  </div>

  <div class="row g-3 mb-3" id="summaryRow">
      <div class="col-md-3">
          <div class="card text-center shadow-sm">
              <div class="card-body">
                  <h5 class="card-title mb-1">Total Bookings</h5>
                  <h3 id="sum-total" class="text-primary mb-0">—</h3>
              </div>
          </div>
      </div>

      <div class="col-md-3">
          <div class="card text-center shadow-sm">
              <div class="card-body">
                  <h5 class="card-title mb-1">New</h5>
                  <h3 id="sum-new" class="text-success mb-0">—</h3>
              </div>
          </div>
      </div>

      <div class="col-md-3">
          <div class="card text-center shadow-sm">
              <div class="card-body">
                  <h5 class="card-title mb-1">Issued</h5>
                  <h3 id="sum-issued" class="text-danger mb-0">—</h3>
              </div>
          </div>
      </div>

      <div class="col-md-3">
          <div class="card text-center shadow-sm">
              <div class="card-body">
                  <h5 class="card-title mb-1">Awaiting Payment</h5>
                  <h3 id="sum-payment" class="text-warning mb-0">—</h3>
              </div>
          </div>
      </div>
  </div>


  <table id="bookingsTable" class="display cell-border compact">
    <thead>
      <tr>
        <th>Date / Time</th>
        <th>Name</th>
        <!-- <th>Summary</th> -->
        <th>Channel</th>
        <th>Contact Type</th>
        <th>Type</th>
        <th>PNR</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- View Modal -->
<!-- <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="viewTitle">Booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="viewBody">
        Loading...
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div> -->

<!-- VIEW / EDIT / FOLLOWUP MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <!-- HEADER WITH TABS -->
      <div class="modal-header p-0 border-bottom-0">

        <!-- Nav Tabs -->
        <ul class="nav nav-tabs w-100 px-3 pt-3" id="bookingTabs" role="tablist">
          <input type="hidden" id="tab_iid">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tabViewBtn" data-bs-toggle="tab" data-bs-target="#tabView" type="button">
              <i class="fa fa-eye me-1"></i> View
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabFollowup" type="button">
              <i class="fa fa-calendar-check me-1"></i> Follow-up
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabEdit" type="button">
              <i class="fa fa-edit me-1"></i> Edit
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDocs" type="button">
              <i class="fa fa-paperclip me-1"></i> Documents
            </button>
          </li>
          <!-- <li class="nav-item1 ms-auto1" role="presentation">
            <a class="nav-link1 text-primary fw-semibold" id="tab_openbtn" target="_blank" type="button">
              <i class="fa fa-external-link-alt me-1"></i> Open
            </a>
          </li> -->
          <li class="nav-item ms-auto1" role="presentation">
            <span
              class="nav-link tab-open-link"
              id="tab_openbtn"
              role="button"
              tabindex="0">
              <i class="fa fa-external-link-alt me-1"></i> Open
            </span>
          </li>

        </ul>
        <!-- Open button OUTSIDE tabs -->
        <!-- <div class="position-absolute end-0 top-0 mt-3 me-5">
          <a id="tab_openbtn"
             class="btn btn-sm btn-outline-primary"
             target="_blank">
            <i class="fa fa-external-link-alt me-1"></i> Open
          </a>
        </div> -->

        <!-- Close Button -->
        <button type="button"
                class="btn-close position-absolute end-0 top-0 mt-3 me-3"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body pt-3">

        <!-- TAB CONTENT -->
        <div class="tab-content p-3">

          <!-- VIEW TAB -->
          <div class="tab-pane fade show active" id="tabView">
            <div id="viewBody">Loading...</div>
          </div>

          <!-- FOLLOWUP TAB -->
          <div class="tab-pane fade" id="tabFollowup">
            <div class="modal-body p-0 d-flex flex-column" style="height:60vh;">
              <div>
                <h6 class="modal-title fw-semibold mb-0">
                  <small id="followup_booking_title" class="text-dark fw-bold"></small>
                </h6>
                <small id="followup_booking_note" class="text-muted"></small>
              </div>
              <div id="followups-list" class="p-3 overflow-auto" style="flex:1 1 auto; border-bottom:1px solid #eee;"></div>

              <div class="p-3" style="flex:0 0 auto;">
                <form id="add-followup-form" class="d-flex gap-2 align-items-start">
                  <input type="hidden" id="followup_booking_id" name="booking_id">
                  <input type="hidden" id="followup_employee_id" name="employee_id">
                  <textarea id="followup_note_text" name="note_text" class="form-control form-control-sm" rows="2" placeholder="Write a followup note..." required></textarea>
                  <div class="d-grid" style="width:110px;">
                    <button type="submit" class="btn btn-primary btn-sm">Add</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- EDIT TAB -->
          <div class="tab-pane fade" id="tabEdit">
            <form id="editForm">
              <input type="hidden" id="edit-id">

              <div class="row g-3">

                <div class="col-md-4">
                  <label class="form-label">Name</label>
                  <input type="text" id="edit-contact_name" class="form-control" disabled readonly>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Phone</label>
                  <input type="text" id="edit-contact_phone" class="form-control" disabled readonly>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Email</label>
                  <input type="text" id="edit-contact_email" class="form-control" disabled readonly>
                </div>

                <div class="col-md-12">
                  <label class="form-label">Summary</label>
                  <input type="text" id="edit-subject" class="form-control">
                </div>

                <div class="col-12">
                  <label class="form-label">Notes</label>
                  <textarea id="edit-notes" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Assign To</label>
                  <select id="edit-assigned_to" class="form-select">
                    <option value="">--</option>
                    <?php
                    $rs = $mysqli->query("SELECT id,name FROM people ORDER BY name");
                    while ($r = $rs->fetch_assoc()) echo '<option value="'.$r['id'].'">'.$r['name'].'</option>';
                    ?>
                  </select>
                </div>

                <!-- <div class="col-md-4">
                  <label class="form-label">Owner</label>
                  <select id="edit-owner_id" class="form-select" disabled readonly>
                    <option value="">--</option>
                    <?php
                    // $rs = $mysqli->query("SELECT id,name FROM people ORDER BY name");
                    // while ($r = $rs->fetch_assoc()) echo '<option value="'.$r['id'].'">'.$r['name'].'</option>';
                    ?>
                  </select>
                </div> -->
                <div class="col-md-4">
                  <label class="form-label">Status</label>
                  <select id="edit-status" class="form-select">
                  <?php
                    foreach ($booking_statuses as $bstat) {
                  ?>
                    <option value="<?=$bstat?>"><?=$bstat?></option>
                  <?php
                    }
                  ?>
                  </select>
                </div>

                <div class="col-md-4 d-none" id="natureWrapper">
                  <label class="form-label">Nature of Booking</label>
                  <input type="text"
                         id="edit-nature"
                         class="form-control"
                         placeholder="Enter nature of booking">
                </div>

                <div class="col-md-3">
                  <label class="form-label">Priority</label>
                  <select id="edit-priority" class="form-select">
                    <option value="normal">Normal</option>
                    <option value="low">Low</option>
                    <option value="high">High</option>
                  </select>
                </div>

                <div class="col-md-3 d-none">
                  <label class="form-label">Follow Date</label>
                  <input type="date" id="edit-follow_date" class="form-control" disabled readonly>
                </div>

                <div class="col-md-2 d-none">
                  <label class="form-label">Follow Time</label>
                  <input type="time" id="edit-follow_time" class="form-control" disabled readonly>
                </div>

                <div class="col-md-3 pt-2">
                  <button type="button" id="editSave" class="btn btn-primary mt-4">Save</button>
                </div>
              </div>
            </form>
          </div>

          <!-- DOCUMENTS TAB -->
          <div class="tab-pane fade" id="tabDocs">
            <!-- Add Document Toggle -->
            <div class="d-flex justify-content-between align-items-center mb-2">
              <small class="text-muted">Uploaded documents</small>
              <button class="btn btn-sm btn-outline-primary" id="btnShowAddDoc">
                <i class="fa fa-plus me-1"></i> Add Document
              </button>
            </div>

            <!-- Add Document Section -->
            <div id="addDocSection" class="border rounded p-3 mb-3 d-none bg-light">
              <form id="bookingDocForm" enctype="multipart/form-data">
                <input type="hidden" name="booking_id" id="doc_booking_id">

                <div class="row g-2">
                  <div class="col-md-12">
                    <label class="form-label small fw-semibold">Document Label *</label>

                    <!-- Selected label -->
                    <input type="hidden" name="label" id="selectedDocLabel" required>

                    <!-- Labels buttons -->
                    <div id="docLabelGroup" class="d-flex flex-wrap gap-2 mb-2"></div>

                    <!-- Add new label -->
                    <div class="input-group input-group-sm" style="max-width:300px;">
                      <input type="text" id="newDocLabelInput" class="form-control" placeholder="New label">
                      <button type="button" class="btn btn-outline-primary" id="addNewDocLabelBtn">
                        <i class="fa fa-plus"></i>
                      </button>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">File *</label>
                    <input type="file" name="file" class="form-control form-control-sm"
                           accept=".pdf,image/*,video/*" required>
                  </div>
                </div>

                <div class="mt-2 text-end">
                  <button type="button" class="btn btn-sm btn-secondary" id="btnHideAddDoc">Cancel</button>
                  <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa fa-upload me-1"></i> Upload
                  </button>
                </div>
              </form>
            </div>

            <!-- Documents Table -->
            <table class="table table-sm table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Label</th>
                  <th style="width:120px;">Type</th>
                  <th style="width:80px;">View</th>
                  <th style="width:80px;">Delete</th>
                </tr>
              </thead>
              <tbody id="booking-documents-body">
                <tr>
                  <td colspan="4" class="text-center text-muted py-3">Loading…</td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>

      <!-- FOOTER -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Close
        </button>
      </div>

    </div>
  </div>
</div>


<script>

const viewModalEl = document.getElementById('viewModal');

viewModalEl.addEventListener('shown.bs.modal', function () {

  // Activate View tab button
  const viewTabBtn = document.getElementById('tabViewBtn');
  if (!viewTabBtn) return;

  const tab = bootstrap.Tab.getOrCreateInstance(viewTabBtn);
  tab.show();

});

const statusSelect   = document.getElementById('edit-status');
const natureWrapper  = document.getElementById('natureWrapper');
const natureInput    = document.getElementById('edit-nature');
function toggleNatureField() {
  if (statusSelect.value === 'closed') {
    natureWrapper.classList.remove('d-none');
    natureInput.required = true;
  } else {
    natureWrapper.classList.add('d-none');
    natureInput.required = false;
    natureInput.value = '';
  }
}
// Run on change
// statusSelect.addEventListener('change', toggleNatureField);


let currentBookingId = 0;

/* Open documents modal */
function openBookingDocuments(bookingId) {
    currentBookingId = bookingId;
    $('#doc_booking_id').val(bookingId);
    $('#addDocSection').addClass('d-none');
    $('#selectedDocLabel').val('');
    loadDocumentLabels();
    loadBookingDocuments();
    // new bootstrap.Modal('#bookingDocumentsModal').show();
}

/* Toggle add section */
$('#btnShowAddDoc, #btnHideAddDoc').on('click', function () {
    $('#addDocSection').toggleClass('d-none');
});

/* Load documents */
function loadBookingDocuments() {
    $('#booking-documents-body').html(
        '<tr><td colspan="4" class="text-center text-muted py-3">Loading…</td></tr>'
    );

    $.getJSON(
        'public/ajax/bookings.php',
        { action: 'documents_list', booking_id: currentBookingId },
        function (res) {
            if (!res.success || !res.data.length) {
                $('#booking-documents-body').html(
                    '<tr><td colspan="4" class="text-center text-muted py-3">No documents</td></tr>'
                );
                return;
            }

            let rows = '';
            res.data.forEach(d => {
                rows += `
                <tr>
                  <td>${esc(d.label)}</td>
                  <td>
                    <span class="badge bg-secondary">${d.file_type.toUpperCase()}</span>
                  </td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary view-document-gallery"
                        data-label="${esc(d.label)}"
                        data-file="${d.file_url}"
                        data-type="${d.file_type}">
                      <i class="fa fa-eye"></i>
                    </button>
                  </td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-outline-danger btn-delete-doc"
                        data-id="${d.id}">
                      <i class="fa fa-trash"></i>
                    </button>
                  </td>
                </tr>`;
            });

            $('#booking-documents-body').html(rows);
        }
    );
}

/* Upload document */
$('#bookingDocForm').on('submit', function (e) {
    e.preventDefault();

    const fd = new FormData(this);
    fd.append('action', 'documents_add');

    $.ajax({
        url: 'public/ajax/bookings.php',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            if (!res.success) {
                alert(res.error || 'Upload failed');
                return;
            }
            $('#bookingDocForm')[0].reset();
            $('#addDocSection').addClass('d-none');
            loadBookingDocuments();
        }
    });
});

/* Delete document */
$(document).on('click', '.btn-delete-doc', function () {
    if (!confirm('Delete this document?')) return;

    $.post(
        'public/ajax/bookings.php',
        {
            action: 'documents_delete',
            id: $(this).data('id')
        },
        function (res) {
            if (res.success) loadBookingDocuments();
            else alert('Delete failed');
        },
        'json'
    );
});

/* ===============================
   DOCUMENT LABEL SELECTOR
================================ */

function loadDocumentLabels() {
    $('#docLabelGroup').html(
        '<span class="text-muted small">Loading labels…</span>'
    );

    $.getJSON(
        'public/ajax/document_labels.php?action=load',
        function (res) {
            if (!res.status || !res.labels || !res.labels.length) {
                $('#docLabelGroup').html(
                    '<span class="text-muted small">No labels</span>'
                );
                return;
            }

            let html = '';
            res.labels.forEach(l => {
                html += `
                  <button type="button"
                          class="btn btn-outline-secondary btn-sm btn-doc-label"
                          data-label="${l}">
                    ${l}
                  </button>`;
            });

            $('#docLabelGroup').html(html);
        }
    );
}

/* Select label */
$(document).on('click', '.btn-doc-label', function () {
    $('.btn-doc-label').removeClass('active btn-primary')
                       .addClass('btn-outline-secondary');

    $(this).addClass('active btn-primary')
           .removeClass('btn-outline-secondary');

    $('#selectedDocLabel').val($(this).data('label'));
});

/* Add new label */
$('#addNewDocLabelBtn').on('click', function () {
    const label = $('#newDocLabelInput').val().trim();
    if (!label) return;

    $.post(
        'public/ajax/document_labels.php',
        { action: 'add', label: label },
        function (res) {
            if (!res.status) {
                alert(res.error || 'Failed to add label');
                return;
            }

            $('#newDocLabelInput').val('');
            loadDocumentLabels();

            // auto-select newly added label
            setTimeout(() => {
                $(`.btn-doc-label[data-label="${label}"]`).trigger('click');
            }, 200);
        },
        'json'
    );
});
</script>


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
    }
    else if (doc.type === 'video') {
      html = `
        <video controls autoplay style="max-width:100%; max-height:80vh;" class="rounded shadow-sm">
          <source src="${doc.file}">
          Your browser does not support the video tag.
        </video>
      `;
    }
    else {
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


<!-- DOCUMENT VIEWER MODAL - used by multiple sections -->
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
  const file  = $(this).data('file');
  const type  = $(this).data('type'); // pdf | image | video
  const label = $(this).data('label');
  $('#documentModalLabel').text(label || 'View Document');
  const viewer = $('#documentViewer');
  if (!file) {
    viewer.html('<div class="text-center text-muted small py-5">File not found.</div>');
    // new bootstrap.Modal('#documentModal').show();
    new bootstrap.Modal(document.getElementById('documentModal')).show();
    return;
  }
  let html = '';
  /* ---------- PDF ---------- */
  if (type === 'pdf') {
    html = `
      <iframe src="${file}" frameborder="0" width="100%" height="100%"
              style="border:none;background:#fff;"></iframe>
    `;
  }
  /* ---------- VIDEO ---------- */
  else if (type === 'video') {
    html = `
      <video controls autoplay style="max-width:100%; max-height:80vh;" class="rounded shadow-sm">
        <source src="${file}">
        Your browser does not support the video tag.
      </video>
    `;
  }
  /* ---------- IMAGE (default) ---------- */
  else {
    html = `
      <img src="${file}" alt="Document Preview"
           class="img-fluid rounded shadow-sm"
           style="max-height:80vh; object-fit:contain;">
    `;
  }
  // close any other modal if needed
  $('#versionsModal').modal('hide');
  viewer.html(html);
  // new bootstrap.Modal('#documentModal').show();
  new bootstrap.Modal(document.getElementById('documentModal')).show();

});
</script>


<!-- Dependencies -->
<!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<?php
require_once __DIR__ . '/includes/footer.php'; // optional: your page footer
?>
<script>
function updateDateRangeLabel(start, end) {
    const today = moment().startOf('day');
    const yesterday = moment().subtract(1, 'day').startOf('day');

    if (start.isSame(today, 'day') && end.isSame(today, 'day')) {
        return 'Today';
    }
    if (start.isSame(yesterday, 'day') && end.isSame(yesterday, 'day')) {
        return 'Yesterday';
    }
    return start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY');
}
function updateRawDateRange(start, end) {
    // format used by backend
    $('#filter-daterange-raw').val(
        start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD')
    );
}
</script>
<script>
// (function(){
  // utilities
  function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
  function qsa(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

  // init daterangepicker
  // const start = moment().subtract(6, 'days');
  const start = moment();
  const end   = moment();
  let dtPicker = $('#filter-daterange');
  dtPicker.daterangepicker({
      startDate: start,
      endDate: end,
      autoUpdateInput: false,   // we control formatting ourselves
      locale: { 
          cancelLabel: 'Clear',
          format: 'DD-MM-YYYY'  // display format inside calendar
      },
      opens: 'right',
      ranges: {
          'Today': [moment(), moment()],
          'Yesterday': [moment().subtract(1,'days'), moment().subtract(1,'days')],
          'Last 7 Days': [moment().subtract(6,'days'), moment()],
          'Last 30 Days': [moment().subtract(29,'days'), moment()],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'Last Month': [
              moment().subtract(1,'month').startOf('month'),
              moment().subtract(1,'month').endOf('month')
          ]
      }
  });
  // set default text in input — DD-MM-YYYY
  // dtPicker.val(
  //     start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY')
  // );
  dtPicker.val(updateDateRangeLabel(start, end));
  updateRawDateRange(start, end);
  // update on apply
  // dtPicker.on('apply.daterangepicker', function(ev, picker){
  //     $(this).val(
  //         picker.startDate.format('DD-MM-YYYY') + ' - ' +
  //         picker.endDate.format('DD-MM-YYYY')
  //     );
  // });
  dtPicker.on('apply.daterangepicker', function (ev, picker) {
      $(this).val(updateDateRangeLabel(picker.startDate, picker.endDate));
      updateRawDateRange(picker.startDate, picker.endDate);
  });

  // clear on cancel
  dtPicker.on('cancel.daterangepicker', function () {
      $(this).val('');
      $('#filter-daterange-raw').val('');
  });

  // initialize DataTable
  const table = $('#bookingsTable').DataTable({
    serverSide: true,
    processing: true,
    ajax: {
      url: 'public/ajax/bookings.php?action=list',
      data: function(d){
        // append our filters
        d.date_range = $('#filter-daterange-raw').val();
        d.channel = $('#filter-channel').val();
        d.contact_type = $('#filter-contact-type').val();
        d.type = $('#filter-type').val();
        d.assigned_to = $('#filter-assigned').val();
        d.status = $('#filter-status').val();
      }
    },
    pageLength: 25,
    lengthMenu: [10,25,50,100],
    ordering: true,
    order: [[7, 'desc']],
    columns: [
      { data: 0 }, // datetime
      { data: 1 }, // contact
      // { data: 2 }, // summary
      { data: 3 }, // channel
      { data: 4 }, // contact type
      { data: 5 }, // type
      { data: 9 }, // tag/nature
      { data: 8 }, // status
      { data: 7, orderable: false, searchable: false } // actions
    ],
    createdRow: function(row, data, dataIndex){
      // keep data-id on row if server provided attribute (optional)
      const id = $(row).find('td').first().closest('tr').attr('data-id');
    }
  });

  // Apply / Clear handlers
  $('#applyFilters').on('click', function(){ table.ajax.reload(); loadSummaryBoxes(); });
  $('#clearFilters').on('click', function(){
    $('#filter-daterange').val('');
    $('#filter-daterange-raw').val('')
    $('#filter-channel').val('');
    $('#filter-contact-type').val('');
    $('#filter-type').val('');
    $('#filter-assigned').val('');
    $('#filter-status').val('');
    table.ajax.reload();
    loadSummaryBoxes();
  });

  // Delegate actions from table
  $('#bookingsTable tbody').on('click', 'button', function(e){
    const btn = this;
    const id = $(btn).data('id');
    const editurl = $(btn).data('contactediturl');
    if ($(btn).hasClass('btn-view')) {
      openViewModal(id,editurl);
      return;
    }
    // if ($(btn).hasClass('btn-go')) {

    //   const editUrl = (this.dataset.contactediturl || '').trim();
    //   const entityId = (this.dataset.contactentityid || '').trim();

    //   // If we have both an edit_url and a contact_entity_id, open that entity's page in a new tab
    //   if (editUrl && entityId) {
    //     // sanitize and build url — the server expects index.php?page=<edit_url>&id=<entity_id>
    //     const url = './?page=' + encodeURIComponent(editUrl) + '&id=' + encodeURIComponent(entityId);
    //     window.open(url, '_blank');
    //     return;
    //   }

    //   // open booking in new tab/view
    //   window.open('./?page=booking_view&id=' + encodeURIComponent(id), '_blank');
    //   return;
    // }
    // if ($(btn).hasClass('btn-edit')) {
    //   openEditModal(id);
    //   return;
    // }
  });


function loadSummaryBoxes() {
    $.getJSON('public/ajax/bookings.php?action=summary', {
        date_range: $('#filter-daterange-raw').val(),
        channel: $('#filter-channel').val(),
        contact_type: $('#filter-contact-type').val(),
        type: $('#filter-type').val(),
        assigned_to: $('#filter-assigned').val()
    }, function(res){
        if (!res.success) return;

        $('#sum-total').text(res.total);
        $('#sum-new').text(res.open);
        $('#sum-issued').text(res.closed);
        $('#sum-payment').text(res.working);
    });
}

loadSummaryBoxes();

/* ---------------- VIEW MODAL ---------------- */
function openViewModal(id,editurl="") {
    $.getJSON('public/ajax/bookings.php?action=load&id=' + id, function(res){
        if (!res.success) return alert('Load failed');
        const it = res.booking;

        $('#viewTitle').text('View Booking');

        // Build conditional blocks
        let contactBlock = "";
        // if (it.related_contact_name && it.related_contact_name !== "") {
        //     contactBlock = `
        //         <div class="col-md-4">
        //             <small class="text-muted">Contact</small>
        //             <div class="fw-bold">
        //                 <i class="fa fa-user text-info me-1"></i> ${it.related_contact_name}
        //             </div>
        //         </div>`;
        // }
        if (it.generated_pdf && it.generated_pdf !== "") {
            contactBlock = `
                <div class="col-md-4">
                    <small class="text-muted">Booking Summary</small>
                    <div class="mt-1">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary view-document"
                            data-file="uploads/bookings/${it.generated_pdf}" data-type="pdf" data-label="Booking Summary">
                            <i class="fa fa-file-pdf me-1"></i> View PDF
                        </button>
                    </div>
                </div>`;
        }

        // Related employees (array)
        let employeesBlock = "";
        if (it.related_employees && it.related_employees.length > 0) {
            let badges = it.related_employees.map(n =>
                `<span class="badge bg-secondary me-1">${n}</span>`
            ).join("");
            employeesBlock = `<hr>
                <div class="mb-3">
                    <small class="text-muted">Related Employees</small><br>
                    ${badges}
                </div>`;
        }

        // Related customer
        let customerBlock = "";
        if (it.related_customer && it.related_customer !== "") {
            customerBlock = `
                <div class="mb-3">
                    <small class="text-muted">Related Customer</small>
                    <div class="fw-bold"><i class="fa fa-building text-warning me-1"></i> ${it.related_customer}</div>
                </div><hr>`;
        }

        it.status = it.status==''?'open':it.status;
        var stclass = it.status=='open'?'primary':(it.status=='closed'?'success':'warning');

        var iattach = it.document_fileurl!=''?`<small class="text-muted">Attachment</small><br><div class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 view-document" data-label="${it.document_label ?? 'Attachment'}" data-file="${it.document_fileurl ?? ''}" data-type="${it.document_type}" style="cursor: pointer;"><i class="fa fa-eye"></i>${it.document_label ?? ' View'}</div>`:'';

        var natured = it.nature!=''?`<div class="col-md-4">
                    <small class="text-muted">Tag</small><br><div class="badge bg-info-subtle text-secondary rounded-pill px-3 py-2"><i class="fa fa-tag me-1"></i>${it.nature ?? '—'}</div></div>`:'';

        /* ---------------- Flight Details ---------------- */
        let flightBlock = "";
        if (parseInt(it.type_id) === 1 && it.origin && it.destination) {

            flightBlock = `
            <hr>
            <div class="mb-3">
                <div class="fw-semibold mb-2">
                    <i class="fa fa-plane-departure text-primary me-1"></i> Flight Details
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <small class="text-muted">Route</small>
                        <div class="fw-bold">${it.origin} → ${it.destination}</div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Trip Type</small>
                        <div class="fw-bold text-capitalize">
                            ${it.trip_type?.replace('_', ' ') ?? '—'}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Class</small>
                        <div class="fw-bold">${it.class ?? '—'}</div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Non-stop</small>
                        <div class="fw-bold">${it.non_stop == 1 ? 'Yes' : 'No'}</div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Departure</small>
                        <div>${it.departure_dated ?? '—'}</div>
                    </div>

                    ${it.return_date ? `<div class="col-md-3">
                        <small class="text-muted">Return</small>
                        <div>`+it.return_dated+`</div>
                    </div>`:``}

                    <div class="col-md-3">
                        <small class="text-muted">PNR</small>
                        <div class="fw-bold">${it.pnr ?? '—'}</div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Travellers</small>
                        <div class="fw-bold">${it.people_no ?? it.traveller_count}</div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Total Fare</small>
                        <div class="fw-bold">
                            ${it.currency ?? ''} ${it.total_amount ?? '—'}
                        </div>
                    </div>
                </div>
            </div>`;
        }

        /* ---------------- Flight Breakdown (Parsed) ---------------- */
        let flightSegmentsBlock = "";

        if (parseInt(it.type_id) === 1 && it.order_json) {

            let order;
            try {
                order = JSON.parse(it.order_json);
            } catch (e) {
                order = null;
            }

            if (order?.flightOffers?.length) {

                const segments = order.flightOffers[0]
                    .itineraries
                    .flatMap(itn => itn.segments);

                const rows = segments.map((s, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>
                            <strong>${s.carrierCode}${s.number}</strong><br>
                            <small class="text-muted">${s.aircraft?.code ?? '—'}</small>
                        </td>
                        <td>
                            ${s.departure.iataCode}<br>
                            <small class="text-muted">
                                ${new Date(s.departure.at).toLocaleString()}
                            </small>
                        </td>
                        <td>
                            ${s.arrival.iataCode}<br>
                            <small class="text-muted">
                                ${new Date(s.arrival.at).toLocaleString()}
                            </small>
                        </td>
                        <td>${s.duration?.replace('PT','').toLowerCase() ?? '—'}</td>
                    </tr>
                `).join('');

                flightSegmentsBlock = `
                <hr>
                <div class="mb-3">
                    <div class="fw-semibold mb-2">
                        <i class="fa fa-route text-primary me-1"></i>
                        Flight Segments
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Flight</th>
                                    <th>Departure</th>
                                    <th>Arrival</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                </div>`;
            }
        }

        /* ---------------- Raw Order JSON (Optional) ---------------- */
        let rawJsonBlock = "";

        if (parseInt(it.type_id) === 1 && it.order_json) {

            let pretty = JSON.stringify(JSON.parse(it.order_json), null, 2);

            rawJsonBlock = `
            <hr>
            <details>
                <summary class="fw-semibold cursor-pointer">
                    <i class="fa fa-code me-1"></i> View Raw Amadeus JSON
                </summary>
                <pre class="border rounded p-3 bg-light mt-2"
                     style="max-height:300px; overflow:auto; font-size:12px;">${pretty}</pre>
            </details>`;
        }


        /* ---------------- Tour Package Details ---------------- */
        let tourBlock = "";
        if (parseInt(it.type_id) === 2 && it.tour_name) {

            tourBlock = `
            <hr>
            <div class="mb-3">
                <div class="fw-semibold mb-2">
                    <i class="fa fa-suitcase-rolling text-success me-1"></i> Tour Package Details
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <small class="text-muted">Package</small>
                        <div class="fw-bold">${it.tour_name}</div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Duration</small>
                        <div>${it.tour_duration ?? '—'}</div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Price</small>
                        <div class="fw-bold">
                            ${it.currency ?? '£'} ${it.tour_price ?? '—'}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Travellers</small>
                        <div class="fw-bold">${it.travellers_count ?? '—'}</div>
                    </div>
                </div>
            </div>`;
        }


        /* ---------------- Continue Booking Button ---------------- */
        let continueBtnHtml = "";
        if (it.lead_type === 'Enquiry') {
            const hasFlightDetails =
                it.origin && it.destination && it.departure_date;
            if (hasFlightDetails) {
                continueBtnHtml = `
                    <div class="mt-2">
                        <a href="./?page=bookings_add&booking=${it.id}"
                           class="btn btn-sm btn-primary">
                            <i class="fa fa-arrow-right me-1"></i>
                            Continue
                        </a>
                    </div>`;
            } else {
                continueBtnHtml = `
                    <div class="mt-2 text-danger small">
                        <i class="fa fa-exclamation-circle me-1"></i>
                        Incomplete
                    </div>`;
            }
        }

        /* ---------------- Travellers ---------------- */
        let travellersBlock = "";
        if (it.travellers && it.travellers.length > 0) {

            let rows = it.travellers.map((t, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td class="fw-semibold">${t.name}</td>
                    <td>${t.dob}</td>
                    <td>${t.email}</td>
                    <td>${t.phone}</td>
                    <td>${t.passport_number}</td>
                    <td>${t.passport_nationality}</td>
                    <td>${t.passport_expiry}</td>
                </tr>
            `).join('');

            travellersBlock = `
            <div class="mb-3">
                <div class="fw-semibold mb-2 d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fa fa-users text-success me-1"></i> Travellers
                    </span>
                    <span class="badge bg-primary">${it.traveller_count}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>DOB</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Passport</th>
                                <th>Nationality</th>
                                <th>Expiry</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>`;
        }

        let html = `
        <div class="container-fluid">

            <!-- Row 1: When + Contact -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <small class="text-muted">When</small>
                    <div class="fw-bold">
                        <i class="fa fa-calendar text-primary me-1"></i>
                        ${it.dated} ${it.timed}
                    </div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Name</small>
                    <div class="fw-bold">
                        <i class="fa fa-calendar text-primary me-1"></i>
                        ${it.contact_name}
                    </div>
                </div>

                ${contactBlock}
            </div>

            <!-- Row 2: Channel, Type, Type -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <small class="text-muted">Channel</small>
                    <div class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                        <i class="fa fa-comments me-1"></i>${it.channel_name ?? '—'}
                    </div>
                    <span class="badge bg-${it.itype=='OUT'?'info':'success'}" style="padding: 0.2em 0.4em !important; margin-left: 0.3em;">${it.itype ?? 'IN'}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Type</small>
                    <div class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">
                        <i class="fa fa-tag me-1"></i>${it.contact_type_name ?? '—'}
                    </div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Type</small>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <div class="badge bg-warning-subtle text-warning rounded-pill px-3 py-2">
                          <i class="fa fa-lightbulb me-1"></i>${it.type_name ?? '—'}
                      </div>

                      ${continueBtnHtml}
                  </div>
                </div>
            </div>
            ${flightBlock}
            ${flightSegmentsBlock}
            ${travellersBlock}
            <!-- ${rawJsonBlock} --!>
            ${tourBlock}

            <hr>

            <!-- Summary -->
            <div class="mb-3">
                <small class="text-muted">Summary</small>
                <div class="border rounded p-2 bg-light">${it.subject || '—'}</div>
            </div>

            <!-- Notes -->
            <div class="mb-3">
                <small class="text-muted">Notes</small>
                <div class="border rounded p-2" style="min-height:80px;">
                    ${it.notes || '—'}
                </div>
            </div>

            <!-- Related Employees -->
            ${employeesBlock}

            <!-- Related Customer -->
            ${customerBlock}

            <!-- Contact Details -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <small class="text-muted">Phone</small>
                    <div><i class="fa fa-phone text-success me-1"></i>${it.contact_phone ?? '—'}</div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Email</small>
                    <div><i class="fa fa-envelope text-danger me-1"></i>${it.contact_email ?? '—'}</div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Owner</small>
                    <div>
                        <i class="fa fa-user-tie text-dark me-1"></i>
                        ${it.owner_name ?? '—'}
                    </div>
                </div>
            </div>

            <!-- Assigned To -->
            <div class="mb-3 row">
                <div class="col-md-4">
                <small class="text-muted">Assigned To</small><br>
                    <i class="fa fa-user-check text-primary me-1"></i>
                    ${it.assigned_name ?? '—'}
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Status</small><br>
                    <div class="badge bg-${stclass}-subtle text-${stclass} rounded-pill px-3 py-2">
                        ${it.status ?? 'open'}
                    </div>
                </div>
                <div class="col-md-4">
                    ${iattach}
                </div>
            </div>

            <!-- Follow-up -->
            <div class="row mb-3">
                <div class="col-md-4 d-none">
                    <small class="text-muted">Follow-up Date</small>
                    <div><i class="fa fa-calendar-check text-success me-1"></i>${it.follow_dated}</div>
                </div>
                <div class="col-md-4 d-none">
                    <small class="text-muted">Follow-up Time</small>
                    <div><i class="fa fa-clock text-warning me-1"></i>${it.follow_timed}</div>
                </div>
                ${natured}
            </div>

        </div>`;

        $('#viewBody').html(html);

        const viewModalEl = document.getElementById('viewModal');
        const viewModal   = new bootstrap.Modal(viewModalEl);
        viewModal.show();

    });
    $("#tab_iid").val(id);
    if(editurl!="") {
      // $("#tab_openbtn").attr("href",editurl);
      const openBtn = document.getElementById('tab_openbtn');
      openBtn.dataset.href = editurl;
    }
}

document.getElementById('tab_openbtn').addEventListener('click', function () {
  const url = this.dataset.href;
  if (url) window.open(url, '_blank');
});

  // $('#viewClose').on('click', function(){ hideModal('#viewModal'); });

  /* ---------- Edit modal ---------- */
  function openEdit(id) {
      $.getJSON('public/ajax/bookings.php?action=load&id=' + id, function(res){
          if (!res.success) return alert('Load failed');

          const it = res.booking;

          $('#edit-id').val(it.id);
          $('#edit-contact_name').val(it.contact_name);
          $('#edit-contact_phone').val(it.contact_phone);
          $('#edit-contact_email').val(it.contact_email);
          $('#edit-subject').val(it.subject);
          $('#edit-notes').val(it.notes);
          $('#edit-owner_id').val(it.owner_id);
          $('#edit-status').val(it.status);
          // toggleNatureField();
          if (it.status === 'closed') {
            $('#edit-nature').val(it.nature || '');
          }
          $('#edit-assigned_to').val(it.assigned_to);
          $('#edit-priority').val(it.priority);
          $('#edit-follow_date').val(it.follow_date);
          $('#edit-follow_time').val(it.follow_time);

          $('#editTitle').text('Edit Booking');

          // editModal.show();
      });
  }


  // $('#editCancel').on('click', function(){ hideModal('#viewModal'); });

  $('#editSave').on('click', function(){
      const payload = {
          id: $('#edit-id').val(),
          // contact_name: $('#edit-contact_name').val(),
          // contact_phone: $('#edit-contact_phone').val(),
          // contact_email: $('#edit-contact_email').val(),
          subject: $('#edit-subject').val(),
          notes: $('#edit-notes').val(),
          owner_id: $('#edit-owner_id').val(),
          status: $('#edit-status').val(),
          nature: $('#edit-nature').val(),
          assigned_to: $('#edit-assigned_to').val(),
          priority: $('#edit-priority').val(),
          follow_date: $('#edit-follow_date').val(),
          follow_time: $('#edit-follow_time').val()
      };

      $.ajax({
          url: 'public/ajax/bookings.php?action=update',
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(payload),
          success: function(res){
              if (!res.success) return alert('Save failed');

              $("#viewModal").modal('hide');
              $('#bookingsTable').DataTable().ajax.reload(null, false);
          }
      });
  });


  /* ---------- Simple modal show/hide helpers ---------- */
  function showModal(sel) {
      const node = $(sel);
      if (!node.length) return;

      // Create overlay
      const overlay = $('<div class="simple-overlay"></div>');
      overlay.css({
          position: 'fixed',
          inset: 0,
          background: 'rgba(0,0,0,0.45)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 300000
      });

      // Insert overlay *before* the modal, not wrapping it
      $('body').append(overlay);

      // Display modal on top of overlay
      node.css({
          position: 'relative',
          zIndex: 300001,
          display: 'block'
      }).appendTo('body');   // <-- modal stays in DOM always

      // Clicking backdrop closes modal
      overlay.on('click', function(e){
          if (e.target === this) hideModal(sel);
      });

      node.data('overlay', overlay);

      // Prevent background scroll
      $('body').css('overflow','hidden');
  }

  function hideModal(sel) {
      const node = $(sel);
      if (!node.length) return;

      const overlay = node.data('overlay');
      if (overlay) overlay.remove();

      node.hide()
          .css({display:'none'})
          .appendTo('body');   // ensure modal stays in document root

      $('body').css('overflow','');
  }

// })();


function esc(s){ if (s==null||s===undefined) return ""; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function scrollFollowupsBottom() {
    const list = document.getElementById("followups-list");

    // try multiple times because modal animation may shift height
    let tries = 0;

    function attemptScroll() {
        list.scrollTop = list.scrollHeight;

        tries++;
        if (tries < 10) {   // retry for 10 frames (~160ms)
            requestAnimationFrame(attemptScroll);
        }
    }

    requestAnimationFrame(attemptScroll);
}

window.openFollowups = function(bookingId) {
    document.getElementById('followup_booking_id').value = bookingId;
    document.getElementById('followup_note_text').value = '';
    const list = document.getElementById('followups-list');
    list.innerHTML = '<div class="text-center text-muted py-3">Loading...</div>';

    // show modal, then load header & list
    // const modalEl = document.getElementById('bookingFollowupModal');
    // const modal = new bootstrap.Modal(modalEl);
    // modal.show();

    // header
    fetch('public/ajax/bookings.php',{ method:'POST', body: new URLSearchParams({ action:'load', id: bookingId }) })
      .then(r=>r.json()).then(res=>{
        if(res.success && res.booking) {
          document.getElementById('followup_booking_title').textContent = res.booking.subject || '';
          document.getElementById('followup_booking_note').textContent = res.booking.notes || '';
        }
      });

    // followups list
    fetch('public/ajax/bookings.php',{ method:'POST', body: new URLSearchParams({ action:'followup_list', id: bookingId }) })
      .then(r=>r.json()).then(res=>{
        list.innerHTML = '';
        if(!res.status || !res.data || res.data.length===0) {
          list.innerHTML = '<div class="text-center text-muted py-3">No followups</div>'; return;
        }
        console.log(res.data);
        res.data.forEach(f=>{
          const user = esc(f.created_by_name || '');
          const initials = user.trim().split(/\s+/).map(w=>w.charAt(0)).join('').substring(0,2).toUpperCase();
          const div = document.createElement('div');
          div.className = 'followup-entry mb-3 d-flex align-items-start gap-2';
          div.innerHTML = `
            <div class="followup-avatar bg-primary text-white rounded-circle" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-weight:600;">
              ${initials}
            </div>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between">
                <strong class="small text-dark">${user}</strong>
                <small class="text-muted">${esc(f.created_atd)}</small>
              </div>
              <div class="note-bubble mt-1 p-2 bg-white border rounded" style="white-space:pre-wrap;">${esc(f.note_text)}</div>
            </div>
          `;
          list.appendChild(div);
        });
        scrollFollowupsBottom();
      })
      .catch(err => {
        console.error(err);
        list.innerHTML = '<div class="text-center text-danger py-3">Failed to load</div>';
      });
  };

  // add followup submit
  document.getElementById('add-followup-form').addEventListener('submit', function(e){
    e.preventDefault();
    const bookingId = document.getElementById('followup_booking_id').value;
    const empId = document.getElementById('followup_employee_id').value;
    const note = document.getElementById('followup_note_text').value.trim();
    if(!note) return;

    const fd = new FormData();
    fd.append('action','followup_add');
    fd.append('booking_id', bookingId);
    fd.append('employee_id', empId);
    fd.append('note_text', note);
    fd.append('created_by', <?= (int)$uid ?>);
    fd.append('created_by_name', <?= json_encode($uname) ?>);

    fetch('public/ajax/bookings.php',{ method:'POST', body: fd })
      .then(r=>r.json()).then(res=>{
        if(!res.status) { alert(res.msg || 'Failed to add followup'); return; }

        // append
        const list = document.getElementById('followups-list');
        if(list.innerHTML=='<div class="text-center text-muted py-3">No followups</div>') {
          list.innerHTML = '';
        }
        const user = esc(res.data.created_by_name || '');
        const initials = user.trim().split(/\s+/).map(w=>w.charAt(0)).join('').substring(0,2).toUpperCase();
        const div = document.createElement('div');
        div.className = 'followup-entry mb-3 d-flex align-items-start gap-2';
        div.innerHTML = `
          <div class="followup-avatar bg-primary text-white rounded-circle" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-weight:600;">
            ${initials}
          </div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between">
              <strong class="small text-dark">${user}</strong>
              <small class="text-muted">${esc(res.data.created_atd)}</small>
            </div>
            <div class="note-bubble mt-1 p-2 bg-white border rounded" style="white-space:pre-wrap;">${esc(res.data.note_text)}</div>
          </div>
        `;
        list.appendChild(div);
        document.getElementById('followup_note_text').value = '';
        scrollFollowupsBottom();
      }).catch(()=> alert('Network error'));
  });

    document.querySelectorAll('#bookingTabs button[data-bs-toggle="tab"]')
    .forEach(btn => {
      btn.addEventListener('shown.bs.tab', function (e) {

        const target = e.target.getAttribute('data-bs-target');
        const bookingId = document.getElementById('tab_iid').value;
        if (!bookingId) return;

        if (target === '#tabFollowup') openFollowups(bookingId);
        if (target === '#tabEdit')     openEdit(bookingId);
        if (target === '#tabDocs')     openBookingDocuments(bookingId);

      });
    });

</script>

<!-- ASSIGNED INTERACTIONS ALERT MODAL -->
<div class="modal fade" id="alertsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-bell text-warning me-2"></i>
          Newly Assigned Bookings
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-0">
        <table class="table table-sm table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Name</th>
              <th>Type</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="alertsTableBody">
            <tr>
              <td colspan="4" class="text-center text-muted py-3">
                Loading…
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          Close
        </button>
      </div>

    </div>
  </div>
</div>
<script>
const alertsModalEl = document.getElementById('alertsModal');
let alertedInteractionIds = [];
/* Load assigned alerts */
function loadAssignedAlerts() {
  $.getJSON(
    'public/ajax/bookings.php',
    { action: 'assigned_alerts' },
    function (res) {
      if (!res.success || !res.data.length) return;
      let rows = '';
      alertedInteractionIds = [];
      res.data.forEach(r => {
        alertedInteractionIds.push(r.id);
        rows += `
          <tr>
            <td>${r.dated} ${r.timed}</td>
            <td>${esc(r.contact_name)}</td>
            <td>${esc(r.type_name)}</td>
            <td>
              <span class="badge bg-${r.stclass}">
                ${r.status}
              </span>
            </td>
          </tr>`;
      });
      $('#alertsTableBody').html(rows);
      new bootstrap.Modal(alertsModalEl).show();
    }
  );
}
/* When modal is closed → mark alerts as read */
alertsModalEl.addEventListener('hidden.bs.modal', function () {
  if (!alertedInteractionIds.length) return;
  $.post(
    'public/ajax/bookings.php',
    {
      action: 'mark_alerts_read',
      ids: alertedInteractionIds
    }
  );
});
/* Auto-load on page load */
$(document).ready(function () {
  loadAssignedAlerts();
});
</script>