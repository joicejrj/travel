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
// DOCUMENT GALLERY VIEWER - ENHANCED WITH GROUP SUPPORT
// =====================================================
let docGallery = [];
let currentIndex = 0;

let zoomLevel = 1;
let imageElement = null;

// ---------------------------------------------
// Build gallery dataset (NOW SUPPORTS GROUPS)
// ---------------------------------------------
function buildDocumentGallery(group = null) {

    docGallery = [];

    let selector = group 
        ? `.view-document-gallery[data-group="${group}"]`
        : ".view-document-gallery";

    $(selector).each(function () {
        docGallery.push({
            file: $(this).data("file"),
            type: $(this).data("type"),
            label: $(this).data("label")
        });
    });
}

// Default build (backward compatibility)
$(document).ready(function(){
    buildDocumentGallery();
});

// Rebuild after AJAX (backward compatibility)
$(document).ajaxComplete(function(){
    buildDocumentGallery();
});


// ---------------------------------------------
// Keyboard navigation (unchanged)
// ---------------------------------------------
document.addEventListener("keydown", function (e) {
    const galleryOpen = $("#documentGalleryModal").hasClass("show");
    if (!galleryOpen) return;

    if (e.key === "ArrowRight") $("#nextDocBtn").click();
    if (e.key === "ArrowLeft") $("#prevDocBtn").click();
    if (e.key === "Escape") $("#documentGalleryModal").modal("hide");
});


// ---------------------------------------------
// Open gallery viewer (NOW GROUP-AWARE)
// ---------------------------------------------
$(document).on("click", ".view-document-gallery", function () {

    const file = $(this).data("file");
    const group = $(this).data("group") || null;

    // Rebuild gallery for this group only
    buildDocumentGallery(group);

    currentIndex = docGallery.findIndex(d => d.file === file);
    openGalleryDocument(currentIndex);
});


// ---------------------------------------------
// Render selected document (unchanged)
// ---------------------------------------------
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

        html = `<img src="${doc.file}" id="galleryImage"
                     class="img-fluid rounded shadow-sm zoom-image"
                     style="max-height:82vh; object-fit:contain;">`;

        setTimeout(() => {
            imageElement = document.getElementById("galleryImage");
            $("#zoomControls").removeClass("d-none");
        }, 50);
    }

    $("#galleryViewer").html(html);
    $("#documentGalleryModal").modal("show");
}


// ---------------------------------------------
// Navigation (unchanged)
// ---------------------------------------------
$("#nextDocBtn").click(function () {
    if (!docGallery.length) return;
    currentIndex = (currentIndex + 1) % docGallery.length;
    openGalleryDocument(currentIndex);
});

$("#prevDocBtn").click(function () {
    if (!docGallery.length) return;
    currentIndex = (currentIndex - 1 + docGallery.length) % docGallery.length;
    openGalleryDocument(currentIndex);
});


// ---------------------------------------------
// Zoom controls (unchanged)
// ---------------------------------------------
$("#zoomInBtn").click(() => adjustZoom(0.1));
$("#zoomOutBtn").click(() => adjustZoom(-0.1));
$("#zoomResetBtn").click(function () {
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