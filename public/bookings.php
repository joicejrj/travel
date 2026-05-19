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
.dt-actions button { margin-right:6px; padding:6px 8px; border-radius:6px; border:1px solid #e5e7eb; background:#fff; cursor:pointer; }
#bookingsTable { width:100% !important; }

.page-filters {
  display: flex;
  align-items: flex-end;
  gap: 12px;
  flex-wrap: nowrap;   /* keep everything in one line */
  margin: 14px 0;
}

.filter-field {
  display: flex;
  flex-direction: column;
  min-width: 150px;
}

.filter-field label {
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 6px;
  font-weight: 600;
}

.filter-actions {
  display: flex;
  gap: 8px;
  align-items: flex-end;
}

.page-filters .spacer {
  flex: 1;  /* pushes New button to right */
}
</style>

<div class="container" style="margin-top: -25px; padding:18px;">


  <div class="card mb-0" id="tabCard" style="display: none; border-bottom-left-radius: 0px; border-bottom-right-radius: 0px;">
    <div class="card-body py-2">
      <div class="col">
        <!-- Tabs Header -->
        <ul class="nav nav-tabs flex-nowrap overflow-auto" id="customerTabs" role="tablist" style1="max-width: 65em;">
          <li class="nav-item" role="presentation">
            <button class="nav-link active"
                    id="tab-all"
                    data-bs-toggle="tab"
                    data-bs-target="#tabContent-all"
                    type="button">
              All Bookings
            </button>
          </li>
        </ul>
      </div>
    </div>
  </div>

<style>
  #customerTabs {
    white-space: nowrap;
  }
  .nav-tabs .nav-link {
    position: relative;
    padding-right: 10px;
  }
  .nav-tabs .nav-link.active {
    background-color: #fff;
    border-bottom: 2px solid #0d6efd;
  }
  #customerTabs {
    display: none;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    white-space: nowrap;
    scroll-behavior: smooth;
    /* Firefox */
    scrollbar-width: thin;
    scrollbar-color: #c5cbd3 transparent;
  }
  /* Chrome / Edge / Safari */
  #customerTabs::-webkit-scrollbar {
    height: 6px; /* thin horizontal scrollbar */
  }
  #customerTabs::-webkit-scrollbar-track {
    background: transparent;
  }
  #customerTabs::-webkit-scrollbar-thumb {
    background-color: #c5cbd3;
    border-radius: 20px;
    transition: background-color 0.2s ease;
  }
  #customerTabs::-webkit-scrollbar-thumb:hover {
    background-color: #9aa4af;
  }
</style>
<!-- Chrome Style Tabs -->
<div class="card shadow-sm mb-2" id="tabsContainer" style="margin-top: -0.6em;">
  <div class="card-body p-0">


    <!-- Tabs Content -->
    <div class="tab-content border-top" id="customerTabContent">

      <!-- Default Tab -->
      <div class="tab-pane fade show active p-3" id="tabContent-all" role="tabpanel">



        <div class="page-filters">

          <div class="filter-field">
            <label for="filter-daterange">Date range</label>
            <input type="hidden" id="filter-daterange-raw">
            <input type="text" id="filter-daterange" placeholder="Pick date range" readonly style="padding:8px;border-radius:6px;border:1px solid #d1d5db;width:200px">
          </div>

          <div class="filter-field" style="min-width: 120px;">
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

          <div class="filter-field" style="min-width: 120px;">
            <label for="filter-status">Status</label>
            <?php
              $booking_statuses = array('New','Updated','Issue request','Awaiting Payment','Payment Success','Payment Failed','Issued','Expired');
            ?>
            <select id="filter-status" style="padding:8px;border-radius:6px;border:1px solid #d1d5db;">
              <option value="">All Status</option>
              <?php foreach ($booking_statuses as $bstat) { ?>
                <option value="<?=$bstat?>"><?=$bstat?></option>
              <?php } ?>
            </select>
          </div>

          <div class="filter-actions">
            <button id="applyFilters" class="btn-primary" style="padding:8px 12px;border-radius:6px;border:0;background:#2563eb;color:#fff;">Apply</button>
            <button id="clearFilters" class="btn-secondary" style="padding:8px 12px;border-radius:6px;border:1px solid #d1d5db;background:#fff;">Clear</button>
          </div>

          <!-- Spacer -->
          <div class="spacer"></div>

          <!-- New Button -->
          <a href="./?page=bookings_add" class="btn btn-sm btn-primary">
            <i class="fa fa-plus"></i> New
          </a>

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
    </div>
  </div>
</div>

<script>
function openCustomerTab(id, title, url) {

    const tabId = "empTab-" + id;
    const tabContentId = "tabContent-" + tabId;

    // If already exists → just switch
    if (document.getElementById(tabId)) {
        new bootstrap.Tab(document.getElementById(tabId)).show();
        return;
    }

    // Create Tab Header
    const tabHeader = `
      <li class="nav-item" role="presentation">
        <button class="nav-link"
                id="${tabId}"
                data-bs-toggle="tab"
                data-bs-target="#${tabContentId}"
                type="button">

          ${title}

          <span class="ms-2 text-danger"
                style="cursor:pointer;"
                onclick="event.stopPropagation(); closeCustomerTab('${tabId}','${tabContentId}')">
            &times;
          </span>

        </button>
      </li>
    `;

    document.getElementById("customerTabs").style.display = "flex";
    document.getElementById("customerTabs")
            .insertAdjacentHTML("beforeend", tabHeader);
    document.getElementById("tabCard").style.display = "block";

    // Create Tab Content with iframe
    const tabContent = `
      <div class="tab-pane fade"
           id="${tabContentId}"
           role="tabpanel">

        <iframe src="${url}"
                style="width:100%; height:80vh; border:none;">
        </iframe>

      </div>
    `;

    document.getElementById("customerTabContent")
            .insertAdjacentHTML("beforeend", tabContent);

    // Activate new tab
    new bootstrap.Tab(document.getElementById(tabId)).show();
}

function closeCustomerTab(tabId, contentId) {

    const tab = document.getElementById(tabId);
    const content = document.getElementById(contentId);

    if (!tab || !content) return;

    // If active → switch to All tab
    if (tab.classList.contains("active")) {
        new bootstrap.Tab(document.getElementById("tab-all")).show();
    }

    tab.parentElement.remove();
    content.remove();

    // Hide container if only All tab left
    const tabs = document.querySelectorAll('#customerTabs .nav-item');
    if (tabs.length <= 1) {
        document.getElementById("customerTabs").style.display = "none";
        document.getElementById("tabCard").style.display = "none";
    }
}
// Refresh datatable when "All Bookings" tab becomes active
document.getElementById('tab-all').addEventListener('shown.bs.tab', function () {
    if ($.fn.DataTable.isDataTable('#bookingsTable')) {
        $('#bookingsTable').DataTable().ajax.reload(null, false);
    }
});
</script>



</div>


<script>

// const viewModalEl = document.getElementById('viewModal');

// viewModalEl.addEventListener('shown.bs.modal', function () {

  // Activate View tab button
//   const viewTabBtn = document.getElementById('tabViewBtn');
//   if (!viewTabBtn) return;

//   const tab = bootstrap.Tab.getOrCreateInstance(viewTabBtn);
//   tab.show();

// });

// const statusSelect   = document.getElementById('edit-status');
// const natureWrapper  = document.getElementById('natureWrapper');
// const natureInput    = document.getElementById('edit-nature');
// function toggleNatureField() {
//   if (statusSelect.value === 'closed') {
//     natureWrapper.classList.remove('d-none');
//     natureInput.required = true;
//   } else {
//     natureWrapper.classList.add('d-none');
//     natureInput.required = false;
//     natureInput.value = '';
//   }
// }
// Run on change
// statusSelect.addEventListener('change', toggleNatureField);

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

<script src="public/assets/js/sweetalert.js?jv=<?=time()?>"></script>

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
    const title = $(btn).data('name');
    // if ($(btn).hasClass('btn-view')) {
    //   openViewModal(id,editurl);
    //   return;
    // }
    if ($(btn).hasClass('btn-view')) {
      var url = './?page=bookings_view&id='+id+'&tabview';
      openCustomerTab(id, title, url)
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

// })();

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
function esc(s){ if (s==null||s===undefined) return ""; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
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