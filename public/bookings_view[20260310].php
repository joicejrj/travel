<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/includes/header.php';

$uid   = $_SESSION['person_id'] ?? 0;
$uname = $_SESSION['person_name'] ?? 'Agent';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo "<div class='container p-4'>Invalid booking</div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$booking_statuses = ['New','Updated','Issue request','Awaiting Payment','Issued','Expired'];
?>

<style>
.page-card{
    background:#fff;
    border-radius:8px;
    padding:18px;
    box-shadow:0 2px 6px rgba(0,0,0,.08);
}
</style>


<div class="container-fluid mt-3">

<div class="d-flex justify-content-between align-items-center mb-3" style="display: none !important;">

<h4 class="mb-0">
<i class="fa fa-ticket-alt text-primary"></i>
Booking #<?= $id ?>
</h4>

<?php if(!isset($_GET['tabview'])) { ?>
<a href="./?page=bookings_list"
class="btn btn-sm btn-outline-secondary">
<i class="fa fa-arrow-left"></i> Back
</a>
<?php } ?>

</div>


<div class="page-card">


<!-- HEADER TABS -->

<ul class="nav nav-tabs w-100" id="bookingTabs">

<input type="hidden" id="tab_iid" value="<?= $id ?>">

<li class="nav-item">
<button class="nav-link active"
id="tabViewBtn"
data-bs-toggle="tab"
data-bs-target="#tabView">
<i class="fa fa-eye me-1"></i> View
</button>
</li>

<li class="nav-item">
<button class="nav-link"
data-bs-toggle="tab"
data-bs-target="#tabFollowup">
<i class="fa fa-calendar-check me-1"></i> Follow-up
</button>
</li>

<li class="nav-item">
<button class="nav-link"
data-bs-toggle="tab"
data-bs-target="#tabEdit">
<i class="fa fa-edit me-1"></i> Edit
</button>
</li>

<li class="nav-item">
<button class="nav-link"
data-bs-toggle="tab"
data-bs-target="#tabDocs">
<i class="fa fa-paperclip me-1"></i> Documents
</button>
</li>


<li class="nav-item ms-auto">
<span
class="nav-link text-primary fw-semibold"
id="tab_openbtn"
role="button">
<i class="fa fa-external-link-alt me-1"></i>
Open
</span>
</li>

</ul>



<div class="tab-content p-3">

<!-- ================= VIEW ================= -->

<div class="tab-pane fade show active"
id="tabView">

<div id="viewBody">
Loading...
</div>

</div>


<!-- ================= FOLLOWUPS ================= -->

<div class="tab-pane fade"
id="tabFollowup">

<div class="d-flex flex-column"
style="height:60vh;">

<div>
<h6 class="fw-semibold mb-0">
<small id="followup_booking_title"></small>
</h6>

<small id="followup_booking_note"
class="text-muted"></small>
</div>

<div id="followups-list"
class="p-3 overflow-auto"
style="flex:1;border-bottom:1px solid #eee;"></div>


<div class="p-3">

<form id="add-followup-form"
class="d-flex gap-2">

<input type="hidden"
id="followup_booking_id"
name="booking_id">

<input type="hidden"
id="followup_employee_id"
name="employee_id">

<textarea
id="followup_note_text"
name="note_text"
class="form-control"
rows="2"
placeholder="Write followup..."
required></textarea>

<button class="btn btn-primary btn-sm">
Add
</button>

</form>

</div>

</div>

</div>


<!-- ================= EDIT ================= -->

<div class="tab-pane fade"
id="tabEdit">

<form id="editForm">

<input type="hidden" id="edit-id">

<div class="row g-3">

<div class="col-md-4">
<label class="form-label">Name</label>
<input type="text"
id="edit-contact_name"
class="form-control"
readonly>
</div>

<div class="col-md-4">
<label class="form-label">Phone</label>
<input type="text"
id="edit-contact_phone"
class="form-control"
readonly>
</div>

<div class="col-md-4">
<label class="form-label">Email</label>
<input type="text"
id="edit-contact_email"
class="form-control"
readonly>
</div>

<div class="col-md-12">
<label class="form-label">Summary</label>
<input type="text"
id="edit-subject"
class="form-control">
</div>

<div class="col-12">
<label class="form-label">Notes</label>
<textarea id="edit-notes"
class="form-control"
rows="3"></textarea>
</div>


<div class="col-md-4">

<label class="form-label">
Assign To
</label>

<select id="edit-assigned_to"
class="form-select">

<option value="">--</option>

<?php
$rs=$mysqli->query("SELECT id,name FROM people ORDER BY name");

while($r=$rs->fetch_assoc()){
echo '<option value="'.$r['id'].'">'.$r['name'].'</option>';
}
?>

</select>

</div>


<div class="col-md-4">

<label class="form-label">
Status
</label>

<select id="edit-status"
class="form-select">

<?php foreach($booking_statuses as $b){ ?>

<option value="<?=$b?>">
<?=$b?>
</option>

<?php } ?>

</select>

</div>


<div class="col-md-3 pt-2">

<button
type="button"
id="editSave"
class="btn btn-primary mt-4">
Save
</button>

</div>

</div>

</form>

</div>



<!-- ================= DOCUMENTS ================= -->

<div class="tab-pane fade"
id="tabDocs">

<div class="d-flex justify-content-between mb-2">

<small class="text-muted">
Uploaded documents
</small>

<button
class="btn btn-sm btn-outline-primary"
id="btnShowAddDoc">
<i class="fa fa-plus me-1"></i>
Add Document
</button>

</div>



<div id="addDocSection"
class="border rounded p-3 mb-3 d-none bg-light">

<form id="bookingDocForm"
enctype="multipart/form-data">

<input type="hidden"
name="booking_id"
id="doc_booking_id">

<div class="row g-2">

<div class="col-md-12">

<label class="form-label small fw-semibold">
Document Label
</label>

<input type="hidden"
name="label"
id="selectedDocLabel">

<div id="docLabelGroup"
class="d-flex flex-wrap gap-2"></div>

</div>

<div class="col-md-6">

<label class="form-label small fw-semibold">
File
</label>

<input type="file"
name="file"
class="form-control form-control-sm"
required>

</div>

</div>

<div class="mt-2 text-end">

<button
type="submit"
class="btn btn-sm btn-primary">
Upload
</button>

</div>

</form>

</div>



<table class="table table-sm table-bordered">

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
<td colspan="4"
class="text-center text-muted py-3">
Loading…
</td>
</tr>

</tbody>

</table>

</div>

</div>

</div>

</div>



<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php require_once __DIR__ . '/includes/view-document-and-gallery.php'; ?>

<script>

const bookingId = <?= $id ?>;

/* ---------------- VIEW MODAL ---------------- */
function openViewModal(id,editurl="") {
    $.getJSON('public/ajax/bookings.php?action=load&id=' + id, function(res){
        if (!res.success) return alert('Load failed');
        const it = res.booking;

        $('#viewTitle').text('View Booking');

        // Build conditional blocks
        let contactBlock = `<div class="col-md-4">`;
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
            contactBlock += `
                <small class="text-muted d-block mb-1">Booking Summary</small>

                <div class="d-flex align-items-center gap-2 mt-1">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary view-document"
                        data-file="uploads/bookings/${it.generated_pdf}"
                        data-type="pdf"
                        data-label="Booking Summary">
                        <i class="fa fa-file-pdf me-1"></i> View PDF
                    </button>

                    <button
                        type="button"
                        class="btn btn-success btn-sm"
                        id="sendWhatsappBtn"
                        data-contact-id="${it.contact_entity_id}"
                        data-url="/uploads/bookings/${it.generated_pdf}"
                        data-booking-id="${it.id}"
                        data-travel-date="${it.travel_dated ?? ''}"
                        data-amount="${it.currency_symbol ?? ''} ${it.final_amount ?? ''}">
                        <i class="fab fa-whatsapp"></i> Send
                    </button>

                    <span id="waLoader" style="display:none;">
                        <i class="fa fa-spinner fa-spin text-success"></i>
                    </span>
                </div>`;
        }

        contactBlock += `</div>`;

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

        if (parseInt(it.type_id) === 2 && it.tour_package) {

            const comps = it.tour_package.components || [];

            const rows = comps.length
                ? comps.map((c, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${c.type}</td>
                        <td>${c.name}</td>
                        <td class="text-center">${c.qty}</td>
                        <td class="text-end">£ ${Number(c.sell).toFixed(2)}</td>
                    </tr>
                `).join('')
                : `<tr><td colspan="5" class="text-center text-muted">No components</td></tr>`;


            var wordpress_url = it.wordpress_url!=''?`<a href="${it.wordpress_url}" class="btn btn-sm btn-outline-success ms-2 py-0 px-1" target="_blank"><i class="fa fa-link"></i> Package URL</a>`:``;

            tourBlock = `
            <hr>
            <div class="mb-3">

                <div class="fw-semibold mb-2">
                    <i class="fa fa-suitcase-rolling text-success me-1"></i>
                    Tour Package Details ${wordpress_url}
                </div>

                <!-- Summary -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <small class="text-muted">Package</small>
                        <div class="fw-bold">${it.tour_package.name}</div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Duration</small>
                        <div>${it.tour_package.duration}</div>
                    </div>

                    <div class="col-md-2">
                        <small class="text-muted">Base Cost</small>
                        <div class="fw-bold">£ ${Number(it.tour_package.base_cost).toFixed(2)}</div>
                    </div>

                    <div class="col-md-1">
                        <small class="text-muted">Charges</small>
                        <div class="fw-bold">£ ${Number(it.tour_package.sell_price-it.tour_package.base_cost).toFixed(2)}</div>
                    </div>

                    <div class="col-md-1">
                        <small class="text-muted">Discount</small>
                        <div class="fw-bold text-success">
                            £ ${Number(it.discount_amount).toFixed(2)}
                        </div>
                    </div>

                    <div class="col-md-2">
                        <small class="text-muted">Final Price</small>
                        <div class="fw-bold text-success">
                            £ ${Number((it.final_amount>0?it.final_amount:it.tour_package.sell_price)).toFixed(2)}
                        </div>
                    </div>
                </div>

                <!-- Components -->
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Component</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows}
                        </tbody>
                    </table>
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
                <div class="col-md-4 d-none">
                    <small class="text-muted">Type</small>
                    <div class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">
                        <i class="fa fa-tag me-1"></i>${it.contact_type_name ?? '—'}
                    </div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Travel Date</small>
                    <div class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">
                        <i class="fa fa-calendar me-1"></i>${it.travel_dated ?? '—'}
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
            <div class="row mb-3">
              <div class="col-md-6">
                <small class="text-muted">Summary</small>
                <div class="border rounded p-2 bg-light">${it.subject || '—'}</div>
              </div>
              <div class="col-md-6">
                <small class="text-muted">Travel Date</small>
                <div class="fw-bold">${it.travel_dated}</div>
              </div>
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

        // const viewModalEl = document.getElementById('viewModal');
        // const viewModal   = new bootstrap.Modal(viewModalEl);
        // viewModal.show();

        editurl = './?page=customers_view&id='+it.contact_entity_id;
        if(it.contact_entity_id!="") {
          // $("#tab_openbtn").attr("href",editurl);
          const openBtn = document.getElementById('tab_openbtn');
          openBtn.dataset.href = editurl;
        }

    });
    $("#tab_iid").val(id);
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

              // $("#viewModal").modal('hide');
              // $('#bookingsTable').DataTable().ajax.reload(null, false);
              openViewModal(bookingId);

              const viewTabBtn = document.getElementById('tabViewBtn');
              bootstrap.Tab.getOrCreateInstance(viewTabBtn).show();
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
<script>
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

<script>

/* load booking view */

$(document).ready(function(){

openViewModal(bookingId);

});


/* TAB LOADERS */

document.querySelectorAll(
'#bookingTabs button[data-bs-toggle="tab"]'
).forEach(btn => {

btn.addEventListener(
'shown.bs.tab',
function(e){

const target =
e.target.getAttribute(
'data-bs-target'
);

if(target==='#tabFollowup')
openFollowups(bookingId);

if(target==='#tabEdit')
openEdit(bookingId);

if(target==='#tabDocs')
openBookingDocuments(bookingId);

});

});


/* OPEN BUTTON */

document.getElementById(
'tab_openbtn'
).addEventListener(
'click',
function(){

const url =
this.dataset.href;

if(url)
window.open(url,'_blank');

});

</script>

<script>
$(document).on('click', '#sendWhatsappBtn', function () {

  const btn    = $(this);
  const loader = $('#waLoader');

  const payload = {
    contact_id:  btn.data('contact-id'),
    url:  btn.data('url'),
    booking_id:  btn.data('booking-id'),
    travel_date: btn.data('travel-date'),
    amount:      btn.data('amount')
  };

  btn.prop('disabled', true);
  loader.show();

  $.ajax({
    url: 'public/ajax/bookings_send_whatsapp.php',
    method: 'POST',
    data: payload,
    dataType: 'json',
    success: function (res) {

      btn.prop('disabled', false);
      loader.hide();

      if (res.success) {
        salert('Sent','WhatsApp message sent successfully','success');
      } else {
        salert('Failed','Failed to send WhatsApp message','error');
      }
    },
    error: function () {
      btn.prop('disabled', false);
      loader.hide();
      alert('WhatsApp request failed');
    }
  });
});
</script>