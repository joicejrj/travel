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

$booking_statuses = ['New','Updated','Issue request','Awaiting Payment','Payment Success','Payment Failed','Issued','Expired'];
?>

<style>
.page-card{
    background:#fff;
    border-radius:8px;
    padding:18px;
    box-shadow:0 2px 6px rgba(0,0,0,.08);
}
</style>
<style>
.payment-receipt{
border-radius:10px;
}

.payment-label{
display:block;
font-size:11px;
color:#6c757d;
line-height:1.2;
}

.payment-value{
font-weight:600;
font-size:14px;
line-height:1.2;
}

.payment-grid .col-md-4{
padding-top:4px;
padding-bottom:4px;
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

<li class="nav-item">
<button class="nav-link"
data-bs-toggle="tab"
data-bs-target="#tabPayments">
<i class="fa fa-credit-card me-1"></i> Payments
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

<!-- Payments tab -->
<div class="tab-pane fade" id="tabPayments">
    <div id="paymentsBody" class="p-2">

    <div class="text-center text-muted py-4">
    Loading payment details...
    </div>

    </div>
</div>


</div>

</div>

</div>



<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php require_once __DIR__ . '/includes/view-document-and-gallery.php'; ?>

<script src="public/assets/js/sweetalert.js?jv=<?=time()?>"></script>

<script>

const bookingId = <?= $id ?>;

const AIRLINES = {
    "AA": "American Airlines",
    "AC": "Air Canada",
    "AF": "Air France",
    "AI": "Air India",
    "AS": "Alaska Airlines",
    "AT": "Royal Air Maroc",
    "AV": "Avianca",
    "AY": "Finnair",
    "AZ": "ITA Airways",
    "BA": "British Airways",
    "BD": "BMI Regional",
    "BE": "Flybe",
    "BG": "Biman Bangladesh Airlines",
    "BI": "Royal Brunei Airlines",
    "BR": "EVA Air",
    "BT": "Air Baltic",
    "B6": "JetBlue Airways",
    "CA": "Air China",
    "CI": "China Airlines",
    "CM": "Copa Airlines",
    "CX": "Cathay Pacific",
    "CZ": "China Southern Airlines",
    "DL": "Delta Air Lines",
    "EK": "Emirates",
    "ET": "Ethiopian Airlines",
    "EY": "Etihad Airways",
    "FI": "Icelandair",
    "FJ": "Fiji Airways",
    "FM": "Shanghai Airlines",
    "GA": "Garuda Indonesia",
    "GF": "Gulf Air",
    "G3": "Gol Linhas Aéreas",
    "G8": "Go First",
    "HA": "Hawaiian Airlines",
    "HU": "Hainan Airlines",
    "HY": "Uzbekistan Airways",
    "IB": "Iberia",
    "IR": "Iran Air",
    "IT": "Tigerair Taiwan",
    "JL": "Japan Airlines",
    "JQ": "Jetstar Airways",
    "JU": "Air Serbia",
    "KE": "Korean Air",
    "KL": "KLM Royal Dutch Airlines",
    "KM": "Air Malta",
    "KQ": "Kenya Airways",
    "KU": "Kuwait Airways",
    "LA": "LATAM Airlines",
    "LH": "Lufthansa",
    "LO": "LOT Polish Airlines",
    "LX": "Swiss International Air Lines",
    "LY": "El Al Israel Airlines",
    "ME": "Middle East Airlines",
    "MH": "Malaysia Airlines",
    "MK": "Air Mauritius",
    "MS": "EgyptAir",
    "MU": "China Eastern Airlines",
    "NH": "All Nippon Airways",
    "NZ": "Air New Zealand",
    "OS": "Austrian Airlines",
    "OZ": "Asiana Airlines",
    "PG": "Bangkok Airways",
    "PK": "Pakistan International Airlines",
    "PR": "Philippine Airlines",
    "QF": "Qantas",
    "QR": "Qatar Airways",
    "RJ": "Royal Jordanian",
    "SA": "South African Airways",
    "SK": "Scandinavian Airlines",
    "SN": "Brussels Airlines",
    "SQ": "Singapore Airlines",
    "SV": "Saudia",
    "TG": "Thai Airways",
    "TK": "Turkish Airlines",
    "TP": "TAP Air Portugal",
    "UA": "United Airlines",
    "UL": "SriLankan Airlines",
    "UX": "Air Europa",
    "VN": "Vietnam Airlines",
    "VS": "Virgin Atlantic",
    "WY": "Oman Air",
    "WS": "WestJet",
    "WN": "Southwest Airlines",

    // India
    "6E": "IndiGo",
    "UK": "Vistara",
    "SG": "SpiceJet",
    "IX": "Air India Express",
    "AK": "AirAsia",
    "I5": "AirAsia India",

    // Middle East
    "FZ": "FlyDubai",
    "XY": "Flynas",
    "J9": "Jazeera Airways",
    "G9": "Air Arabia",

    // Europe Low-cost
    "FR": "Ryanair",
    "U2": "easyJet",
    "W6": "Wizz Air",
    "VY": "Vueling",
    "HV": "Transavia",

    // Asia Low-cost
    "TR": "Scoot",
    "D7": "AirAsia X",
    "FD": "Thai AirAsia",
    "Z2": "Philippines AirAsia",

    // US Low-cost
    "NK": "Spirit Airlines",
    "F9": "Frontier Airlines",

    // Australia
    "VA": "Virgin Australia",

    // Africa
    "KP": "ASKY Airlines",
    "DT": "TAAG Angola Airlines",

    // South America
    "AD": "Azul Brazilian Airlines",
    "JJ": "LATAM Brasil",
    "4M": "LATAM Argentina"
  };

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

        let tickets = [];
        if (it.tickets) {
            // case 1: already array
            if (Array.isArray(it.tickets)) {
                tickets = it.tickets;
            }
            // case 2: JSON string
            else if (typeof it.tickets === "string") {
                try {
                    const parsed = JSON.parse(it.tickets);
                    if (Array.isArray(parsed)) {
                        tickets = parsed;
                    } else if (parsed) {
                        tickets = [parsed];
                    }
                } catch(e) {
                    // case 3: comma separated string
                    tickets = it.tickets.split(',').map(t => t.trim()).filter(Boolean);
                }
            }
        }
        let ticketBlock = "";
        if (tickets.length > 0) {
            const badges = tickets.map(t =>
                `<span class="badge text-bg-success me-1 mb-1 cursor-pointer"
                    style="cursor:pointer"
                    onclick="viewTicket('${t}', ${it.id})"
                    title="Click to view ticket">
                    <i class="fa fa-ticket-alt me-1"></i>${t}
                </span>`
            ).join('');
            ticketBlock = `
            <div class="col-md-12">
                <small class="text-muted">Tickets</small>
                <div>${badges}</div>
            </div>`;
        }

        let farerules = "";
        let farebreaks = "";
        if(it.provider=='TRAVELPORT') {
            farerules = `<button class="btn btn-outline-primary btn-sm mt-2 d-flex align-items-center justify-content-center gap-2 fw-semibold" onclick="loadResFareRules('${it.pnr}')"><i class="fa fa-file-alt"></i><span>Fare Rules</span></button>`;
            farebreaks = `<button class="btn btn-sm btn-outline-primary" onclick='showFareBreakup(${JSON.stringify(it.order_json || null)})'><i class="fa fa-receipt"></i></button>`;
        }



        let fetchTicketBtn = '';
        if ((!tickets || tickets.length === 0) && it.provider === 'TRAVELPORT') {
            fetchTicketBtn = `
            <div class="col-md-12 mt-2">
                <button class="btn btn-outline-success btn-sm"
                    onclick="fetchTickets('${it.pnr}', ${it.id})">
                    <i class="fa fa-download"></i> Fetch Tickets
                </button>
            </div>`;
        }

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
                        <div class="fw-bold d-flex align-items-center gap-2">${it.pnr ?? '—'}
                        ${farerules}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Travellers</small>
                        <div class="fw-bold">${it.people_no ?? it.traveller_count}</div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Total Fare</small>
                        <div class="fw-bold d-flex align-items-center gap-2">
                            ${it.currency ?? ''} ${it.total_amount ?? '—'}
                            ${farebreaks}
                        </div>
                    </div>

                    ${ticketBlock}
                    ${fetchTicketBtn}

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

            let segments = [];

            /* =========================
               ✈️ AMADEUS FORMAT
            ========================= */
            if (order?.flightOffers?.length) {

                segments = order.flightOffers[0]
                    .itineraries
                    .flatMap(itn => itn.segments)
                    .map(s => ({
                        carrier: s.carrierCode,
                        number: s.number,
                        aircraft: s.aircraft?.code,
                        dep: s.departure.iataCode,
                        depTime: s.departure.at,
                        arr: s.arrival.iataCode,
                        arrTime: s.arrival.at,
                        duration: s.duration
                    }));
            }

            /* =========================
               ✈️ TRAVELPORT FORMAT (MULTICITY)
            ========================= */
            else if (order?.ReservationResponse?.Reservation?.Offer) {

                const offers = order.ReservationResponse.Reservation.Offer;

                segments = offers.flatMap(ofr =>
                    (ofr.Product || []).flatMap(prod =>
                        (prod.FlightSegment || []).map(fs => {
                            const f = fs.Flight || {};
                            return {
                                carrier: f.carrier,
                                number: f.number,
                                aircraft: f.equipment,
                                dep: f.Departure?.location,
                                depTime: f.Departure?.date + "T" + f.Departure?.time,
                                arr: f.Arrival?.location,
                                arrTime: f.Arrival?.date + "T" + f.Arrival?.time,
                                duration: f.duration
                            };
                        })
                    )
                );
            }

            /* =========================
               RENDER TABLE
            ========================= */
            if (segments.length) {

                const rows = segments.map((s, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img class="flight-logo"
                                     src="https://images.kiwi.com/airlines/64/${s.carrier}.png"
                                     alt="${s.carrier}"
                                     style="width:28px;height:28px;object-fit:contain;"
                                     onerror="this.style.display='none'">
                                <div>
                                    <div class="fw-semibold text-dark">
                                        ${AIRLINES[s.carrier] || s.carrier || ''}
                                        <span class="text-muted ms-1">
                                            ${s.carrier || ''}${s.number || ''}
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        ${s.aircraft ? `Aircraft: ${s.aircraft}` : '—'}
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            ${s.dep || '—'}<br>
                            <small class="text-muted">
                                ${s.depTime ? new Date(s.depTime).toLocaleString() : '—'}
                            </small>
                        </td>
                        <td>
                            ${s.arr || '—'}<br>
                            <small class="text-muted">
                                ${s.arrTime ? new Date(s.arrTime).toLocaleString() : '—'}
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
                        <div class="fw-bold">£ ${Number(it.original_amount).toFixed(2)}</div>
                    </div>

                    <div class="col-md-1">
                        <small class="text-muted">Charges</small>
                        <div class="fw-bold">£ ${Number(it.final_amount-Number(it.original_amount)+Number(it.discount_amount)).toFixed(2)}</div>
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
                        <a href="./?page=bookings_add&booking=${it.id}" target="_blank"
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
                      <div class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                          <i class="fa fa-route me-1"></i>${it.type_name ?? '—'} ${parseInt(it.type_id)===1 ? '| ':''}${it.provider ?? ''}
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

function loadBookingPayments(id){

    $('#paymentsBody').html(
        '<div class="text-center text-muted py-4">Loading...</div>'
    );

    $.getJSON(
        'public/ajax/bookings.php',
        {action:'payments_load', id:id},
        function(res){

            if(!res.success){
                $('#paymentsBody').html('Failed to load payments');
                return;
            }

            let html = '';

            /* ===============================
               PAYMENT LINK CARD
            =============================== */

            html += `
            <div class="card mb-3 shadow-sm ${res.payment&&(res.payment.status=='captured'&&res.payment.bstatus!='Awaiting Payment')?'d-none':''}">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold">
                            <i class="fa fa-link me-1 text-primary"></i>
                            Payment Link
                        </div>
                    </div>

                    <div class="input-group">

                        <input type="text"
                               class="form-control"
                               id="paymentLinkInput"
                               value="${res.payment_link}"
                               readonly>

                        <button class="btn btn-outline-secondary"
                                onclick="copyPaymentLink()">
                                Copy
                        </button>

                        <button class="btn btn-outline-primary"
                                onclick="openPaymentLink()">
                                Open
                        </button>

                        <button class="btn btn-success"
                                onclick="sendPaymentSMS()">
                                SMS
                        </button>

                    </div>

                </div>
            </div>
            `;

            /* ===============================
               PAYMENT DETAILS CARD
            =============================== */

            if(!res.payment){

                html += `
                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fa fa-credit-card fa-2x mb-2"></i>
                        <div>No payment received yet</div>
                    </div>
                </div>
                `;

            }else{

                const p = res.payment;

                let statusColor = "secondary";
                let statusIcon = "bi-clock";

                if (p.status === "captured" || p.status === "success") {
                    statusColor = "success";
                    statusIcon = "bi-check-circle";
                }
                if (p.status === "pending") {
                    statusColor = "warning";
                    statusIcon = "bi-hourglass";
                }
                if (p.status === "declined" || p.status === "failed") {
                    statusColor = "danger";
                    statusIcon = "bi-x-circle";
                }

                const amountMinor = Number(p.amount ?? 0);
                const chargedMinor = Number(p.charged_amount ?? 0);

                const amount = (amountMinor / 100).toFixed(2);
                const charged = (chargedMinor / 100).toFixed(2);
                const fee = ((chargedMinor - amountMinor) / 100).toFixed(2);

                const paymentDate = p.created_at
                ? new Date(p.created_at.replace(' ', 'T')).toLocaleString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                })
                : "-";

                html += `
                <div class="card shadow-sm border-0 payment-receipt">

                <div class="card-body p-3">

                    <!-- HEADER -->
                    <div class="d-flex justify-content-between align-items-center mb-2">

                        <div>
                            <div class="fw-semibold small text-muted">
                                Payment Status
                            </div>
                            <div class="text-${statusColor} fw-semibold">
                                <i class="bi ${statusIcon} me-1"></i>
                                ${p.status.toUpperCase()}
                            </div>
                        </div>

                        <div class="text-end">
                            <div class="text-muted small">Total Charged</div>
                            <div class="fw-bold fs-5">
                                ${p.currency} ${charged}
                            </div>
                        </div>

                    </div>


                    <!-- DETAILS GRID -->
                    <div class="row g-2 payment-grid">

                        <div class="col-md-4" ${chargedMinor > amountMinor}>
                            <span class="payment-label">Booking Amount</span>
                            <div class="payment-value">${p.currency} ${amount}</div>
                        </div>

                        ${
                            chargedMinor > amountMinor
                            ? `
                        <div class="col-md-4">
                            <span class="payment-label">Corporate Fee</span>
                            <div class="payment-value text-warning">
                                ${p.currency} ${fee}
                            </div>
                        </div>
                        `
                        : ''
                        }

                        <div class="col-md-4">
                            <span class="payment-label">Payment Method</span>
                            <div class="payment-value">
                                ${p.payment_method || "Card"}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <span class="payment-label">Card</span>
                            <div class="payment-value">${p.card || "-"}</div>
                        </div>

                        <div class="col-md-4">
                            <span class="payment-label">Card Type</span>
                            <div class="payment-value">${p.card_type || "-"}</div>
                        </div>

                        <div class="col-md-4">
                            <span class="payment-label">Category</span>
                            <div class="payment-value">${p.card_category || "-"}</div>
                        </div>

                        <div class="col-md-4">
                            <span class="payment-label">Issuer</span>
                            <div class="payment-value">${p.card_issuer || "-"}</div>
                        </div>

                        <div class="col-md-4">
                            <span class="payment-label">Country</span>
                            <div class="payment-value">${p.card_country || "-"}</div>
                        </div>

                        <div class="col-md-4">
                            <span class="payment-label">Expiry</span>
                            <div class="payment-value">${p.card_expiry || "-"}</div>
                        </div>

                        <div class="col-md-4">
                            <span class="payment-label">Reference</span>
                            <div class="payment-value">${p.reference || "-"}</div>
                        </div>

                        <div class="col-md-4">
                            <span class="payment-label">Transaction ID</span>
                            <div class="payment-value text-break">
                                ${p.transaction_id || "-"}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <span class="payment-label">Payment Date</span>
                            <div class="payment-value">${paymentDate}</div>
                        </div>

                    </div>

                </div>
                </div>
                `;
            }

            $('#paymentsBody').html(html);

        }
    );
}
function copyPaymentLink(){

    const link = $('#paymentLinkInput').val();

    navigator.clipboard.writeText(link);

    salert('Copied','Copied Payment URL to clipboard','success');
}

function openPaymentLink(){

    const link = $('#paymentLinkInput').val();
    
    markPaymentRequested();

    window.open(link,'_blank');

}

function sendPaymentSMS(){

    const link = $('#paymentLinkInput').val();

    $.post(
        'public/ajax/bookings.php',
        {
            action:'send_payment_sms',
            booking_id: bookingId,
            link: link
        },
        function(res){

            if(res.success){
                salert('Sent','Payment SMS sent','success');
            }else{
                salert('Error',res.msg || 'SMS failed','error');
            }

        },
        'json'
    );
}

function markPaymentRequested(){

    $.post(
        'public/ajax/bookings.php',
        {
            action:'mark_awaiting_payment',
            id: bookingId
        }
    );

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

if(target==='#tabView')
openViewModal(bookingId);

if(target==='#tabFollowup')
openFollowups(bookingId);

if(target==='#tabEdit')
openEdit(bookingId);

if(target==='#tabDocs')
openBookingDocuments(bookingId);

if (target === '#tabPayments')
loadBookingPayments(bookingId);

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

<!-- Ticket Display Modal -->
<div class="modal fade" id="ticketDisplayModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-ticket-detailed me-1"></i> E-Ticket Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="ticketDisplayBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Loading ticket details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" onclick="printTicket()"><i class="bi bi-printer"></i> Print Ticket</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function escHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
function viewTicket(ticketNumber, bookingId) {
    const body = document.getElementById('ticketDisplayBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Fetching ticket ' + ticketNumber + '...</p></div>';
    const modal = new bootstrap.Modal(document.getElementById('ticketDisplayModal'));
    modal.show();

    fetch('public/ajax/ticket_display.php?ticket_number=' + encodeURIComponent(ticketNumber) + '&booking_id=' + bookingId)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                body.innerHTML = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> ' + escHtml(data.error) + '</div>';
                return;
            }
            if (!data.tickets || data.tickets.length === 0) {
                body.innerHTML = '<div class="alert alert-info">No ticket details available.</div>';
                return;
            }
            body.innerHTML = renderTicketDisplay(data.tickets[0]);
        })
        .catch(err => { body.innerHTML = '<div class="alert alert-danger">' + escHtml(err.message) + '</div>'; });
}

function renderTicketDisplay(t) {
    let h = '<div id="ticketPrintArea">';

    // Header
    h += '<div style="text-align:center;padding:16px 0;border-bottom:3px solid var(--jrj-primary)">';
    h += '<div style="font-size:1.4rem;font-weight:800;color:var(--jrj-primary);letter-spacing:2px">ELECTRONIC TICKET</div>';
    h += '<div style="font-size:1.8rem;font-weight:900;letter-spacing:4px;color:#1a1a2e;margin:6px 0">' + escHtml(t.ticketNumber) + '</div>';
    if (t.pnr) h += '<div class="text-muted">PNR: <strong>' + escHtml(t.pnr) + '</strong> (' + escHtml(t.pnrSource) + ')</div>';
    h += '</div>';

    // Passenger
    h += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:16px 0;border-bottom:1px solid #e5e7eb">';
    h += '<div><div class="text-muted small">PASSENGER</div><div style="font-size:1.1rem;font-weight:700">' + escHtml(t.passengerName) + '</div><span class="badge bg-light text-dark">' + escHtml(t.passengerType) + '</span></div>';
    h += '<div style="text-align:right"><div class="text-muted small">VALIDATING CARRIER</div><div style="font-size:1.1rem;font-weight:700">' + escHtml(t.validatingCarrier) + '</div>';
    if (t.pricingType) h += '<div class="small text-muted">' + escHtml(t.pricingType) + '</div>';
    h += '</div></div>';

    // FOP
    if (t.formOfPayment && t.formOfPayment.length > 0) {
        h += '<div style="padding:10px 0;border-bottom:1px solid #e5e7eb"><div class="text-muted small">FORM OF PAYMENT</div>';
        t.formOfPayment.forEach(fop => {
            if (fop.type === 'Card') {
                h += '<span class="fw-semibold">' + escHtml(fop.cardCode) + ' ' + escHtml(fop.cardNumber) + '</span>';
                if (fop.approvalCode) h += ' <span class="text-muted small">Approval: ' + escHtml(fop.approvalCode) + '</span>';
            } else {
                h += '<span class="fw-semibold">' + escHtml(fop.type) + '</span>';
            }
        });
        h += '</div>';
    }

    // Segments
    h += '<div style="padding:12px 0"><div class="text-muted small fw-bold mb-2" style="letter-spacing:1px">FLIGHT ITINERARY</div>';
    h += '<table style="width:100%;border-collapse:collapse;font-size:.85rem">';
    h += '<thead><tr style="background:var(--jrj-primary);color:#fff"><th style="padding:8px">Seg</th><th style="padding:8px">Flight</th><th style="padding:8px">From</th><th style="padding:8px">To</th><th style="padding:8px">Date</th><th style="padding:8px">Class</th><th style="padding:8px">Fare Basis</th><th style="padding:8px">Status</th><th style="padding:8px">Baggage</th></tr></thead><tbody>';
    (t.segments || []).forEach(seg => {
        const statusColor = seg.status === 'OpenForUse' ? '#059669' : (seg.status === 'Voided' || seg.status === 'Refunded' ? '#DC2626' : '#6B7280');
        const statusLabel = (seg.status || '').replace(/([A-Z])/g, ' $1').trim();
        h += '<tr style="border-bottom:1px solid #e5e7eb">';
        h += '<td style="padding:8px;text-align:center">' + seg.sequence + (seg.connection ? ' <span title="Connection" style="color:#F59E0B">⟶</span>' : '') + '</td>';
        h += '<td style="padding:8px;font-weight:700">' + escHtml(seg.carrier + ' ' + seg.flightNumber) + '</td>';
        h += '<td style="padding:8px"><strong>' + escHtml(seg.from) + '</strong>';
        if (seg.fromTime) h += '<br><span class="small text-muted">' + escHtml(seg.fromTime.substring(0,5)) + '</span>';
        h += '</td>';
        h += '<td style="padding:8px"><strong>' + escHtml(seg.to) + '</strong>';
        if (seg.toTime) h += '<br><span class="small text-muted">' + escHtml(seg.toTime.substring(0,5)) + '</span>';
        h += '</td>';
        h += '<td style="padding:8px">' + escHtml(seg.fromDate) + '</td>';
        h += '<td style="padding:8px;text-align:center;font-weight:600">' + escHtml(seg.classOfService) + '</td>';
        h += '<td style="padding:8px;font-family:monospace;font-size:.8rem">' + escHtml(seg.fareBasisCode) + '</td>';
        h += '<td style="padding:8px"><span style="color:' + statusColor + ';font-weight:600">' + escHtml(statusLabel) + '</span></td>';
        h += '<td style="padding:8px">' + escHtml(seg.baggage) + '</td>';
        h += '</tr>';
    });
    h += '</tbody></table></div>';

    // Price
    if (t.price) {
        h += '<div style="padding:12px 0;border-top:2px solid var(--jrj-primary)">';
        h += '<div class="text-muted small fw-bold mb-2" style="letter-spacing:1px">FARE DETAILS</div>';
        h += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">';
        h += '<div>';
        h += '<table style="width:100%;font-size:.85rem">';
        h += '<tr><td class="text-muted">Base Fare</td><td style="text-align:right;font-weight:600">' + t.price.currency + ' ' + Number(t.price.base).toFixed(2) + '</td></tr>';
        (t.price.taxes || []).forEach(tax => {
            h += '<tr><td class="text-muted">Tax ' + escHtml(tax.code) + '</td><td style="text-align:right">' + (tax.currency || t.price.currency) + ' ' + Number(tax.amount).toFixed(2) + '</td></tr>';
        });
        h += '<tr style="border-top:2px solid var(--jrj-primary)"><td style="font-weight:800;font-size:1rem">TOTAL</td><td style="text-align:right;font-weight:800;font-size:1.1rem;color:var(--jrj-primary)">' + t.price.currency + ' ' + Number(t.price.total).toFixed(2) + '</td></tr>';
        h += '</table>';
        if (t.price.filedAmount) {
            h += '<div class="small text-muted mt-1">Filed: ' + t.price.filedCurrency + ' ' + t.price.filedAmount + '</div>';
        }
        h += '</div>';
        h += '<div>';
        if (t.price.fareBreakdown) h += '<div class="small"><strong>Fare Breakdown:</strong><br><code style="font-size:.75rem;word-break:break-all">' + escHtml(t.price.fareBreakdown) + '</code></div>';
        h += '</div></div></div>';
    }

    // Restrictions
    if (t.restrictions && t.restrictions.length > 0) {
        h += '<div style="padding:10px 0;border-top:1px solid #e5e7eb"><div class="text-muted small fw-bold">RESTRICTIONS</div>';
        t.restrictions.forEach(r => { h += '<div class="small" style="color:#B45309">' + escHtml(r) + '</div>'; });
        h += '</div>';
    }

    // Agency
    if (t.agency && t.agency.name) {
        h += '<div style="padding:10px 0;border-top:1px solid #e5e7eb;font-size:.8rem;color:#6B7280">';
        h += '<strong>Issued by:</strong> ' + escHtml(t.agency.name) + ' (' + escHtml(t.agency.code) + ')';
        h += ' | PCC: ' + escHtml(t.agency.pcc) + ' | ' + escHtml(t.agency.city) + ', ' + escHtml(t.agency.country);
        if (t.agency.ticketDate) h += ' | Date: ' + escHtml(t.agency.ticketDate);
        h += '</div>';
    }

    // Previous Issue
    if (t.previousIssue) {
        h += '<div style="padding:8px 0;border-top:1px solid #e5e7eb;font-size:.8rem">';
        h += '<div class="text-muted small fw-bold">PREVIOUS ISSUE (EXCHANGE)</div>';
        t.previousIssue.forEach(pi => {
            h += '<div>Ticket: <code>' + escHtml(pi.value || '') + '</code> issued ' + escHtml(pi.issueDate || '') + ' in ' + escHtml(pi.issuingCity || '') + '</div>';
        });
        h += '</div>';
    }

    h += '</div>'; // end ticketPrintArea
    return h;
}

function printTicket() {
    const content = document.getElementById('ticketPrintArea');
    if (!content) return;
    const win = window.open('', '_blank', 'width=900,height=700');
    win.document.write('<!DOCTYPE html><html><head><title>E-Ticket</title>');
    win.document.write('<style>');
    win.document.write('* { margin:0; padding:0; box-sizing:border-box; }');
    win.document.write('body { font-family: "Segoe UI", Tahoma, sans-serif; font-size:13px; color:#1a1a2e; padding:20px; }');
    win.document.write('table { border-collapse:collapse; width:100%; }');
    win.document.write('th, td { padding:6px 8px; text-align:left; }');
    win.document.write('code { font-family:monospace; }');
    win.document.write('.text-muted { color:#6B7280; } .small { font-size:.85em; }');
    win.document.write('.badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:.75rem; }');
    win.document.write('.bg-light { background:#f3f4f6; } .text-dark { color:#1f2937; }');
    win.document.write(':root { --jrj-primary: #0F52BA; --jrj-primary-light: #EEF3FB; }');
    win.document.write('</style></head><body>');
    win.document.write(content.innerHTML);
    win.document.write('<div style="text-align:center;margin-top:20px;font-size:11px;color:#9CA3AF">Generated by <?= APP_NAME??'TRAVELPORT' ?> on ' + new Date().toLocaleString() + '</div>');
    win.document.write('</body></html>');
    win.document.close();
    setTimeout(() => { win.print(); }, 500);
}
</script>

<div class="modal fade" id="fareModal" tabindex="-1">
  <div class="modal-dialog modal-md modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-receipt me-1"></i> Fare Breakdown
        </h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="fareBody"></div>

      <div class="modal-footer">
        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>
<script>
function showFareBreakup(orderJson){

    const body = $('#fareBody');
    body.html('<div class="text-center py-3">Loading...</div>');

    let data;
    try{
        data = typeof orderJson === 'string'
            ? JSON.parse(orderJson)
            : orderJson;
    }catch(e){
        body.html('<div class="text-danger">Invalid fare data</div>');
        return;
    }

    let html = '';

    const price = data?.ReservationResponse?.Reservation?.Offer?.[0]?.Price;
    const breakdowns = price?.PriceBreakdown || [];

    if(price && breakdowns.length){

        breakdowns.forEach(b => {

            const amt = b.Amount;
            const currency = amt?.CurrencyCode?.value || 'GBP';
            const qty = b.quantity || 1;
            const type = b.requestedPassengerType;

            const label =
                type === 'ADT' ? 'Adult' :
                type === 'CNN' ? 'Child' :
                'Infant';

            html += `
            <div class="card mb-3 shadow-sm">

                <div class="card-header bg-light fw-semibold d-flex justify-content-between">
                    <span><i class="fa fa-user me-1"></i> ${label} (${type})</span>
                    <span class="badge bg-primary">× ${qty}</span>
                </div>

                <div class="card-body p-2">

                    <table class="table table-sm mb-2">

                        <tr>
                            <td>Base Fare</td>
                            <td class="text-end fw-semibold">
                                ${currency} ${Number(amt.Base).toFixed(2)}
                            </td>
                        </tr>
            `;

            // TAXES
            if(amt?.Taxes?.Tax?.length){
                html += `<tr><td colspan="2" class="text-muted small">Taxes</td></tr>`;
                amt.Taxes.Tax.forEach(t => {
                    html += `
                    <tr class="small">
                        <td class="ps-3">• ${t.taxCode}</td>
                        <td class="text-end">
                            ${t.currencyCode} ${Number(t.value).toFixed(2)}
                        </td>
                    </tr>`;
                });
            }

            html += `
                <tr class="border-top fw-bold">
                    <td>Total Taxes</td>
                    <td class="text-end">
                        ${currency} ${Number(amt.Taxes?.TotalTaxes || 0).toFixed(2)}
                    </td>
                </tr>

                <tr class="fw-bold text-primary">
                    <td>Total Fare</td>
                    <td class="text-end">
                        ${currency} ${Number(amt.Total).toFixed(2)}
                    </td>
                </tr>

                </table>
            `;

            // if(b?.FiledAmount){
            //     html += `
            //     <div class="text-end small text-muted">
            //         Filed: ${b.FiledAmount.currencyCode} ${b.FiledAmount.value}
            //     </div>`;
            // }

            html += `
                </div>
            </div>
            `;
        });

        // GRAND TOTAL
        // html += `
        // <div class="alert alert-primary text-end fw-bold">
        //     Grand Total: ${price.CurrencyCode?.value}
        //     ${Number(price.TotalPrice).toFixed(2)}
        // </div>
        // `;
        html += `
        <div class="card border-primary">
            <div class="card-body text-end fw-bold fs-5 text-primary">
                Grand Total: ${price.CurrencyCode?.value}
                ${Number(price.TotalPrice).toFixed(2)}
            </div>
        </div>
        `;

    } else {
        html = `<div class="text-muted text-center py-3">
                    No fare details available
                </div>`;
    }

    body.html(html);

    new bootstrap.Modal(
        document.getElementById('fareModal')
    ).show();
}
</script>

<div class="modal fade" id="resFareRulesModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-text"></i>
                    Fare Rules — <span id="resFR_PNR"></span>
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="resFareRulesBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Loading fare rules...</p>
                </div>
            </div>

            <div class="modal-footer">
                <div class="d-flex gap-2 w-100">
                    <button class="btn btn-sm btn-outline-primary" onclick="loadResFareRulesType('ShortText')">Short</button>
                    <button class="btn btn-sm btn-outline-primary" onclick="loadResFareRulesType('LongText')">Long</button>
                    <button class="btn btn-sm btn-outline-primary" onclick="loadResFareRulesType('Structured')">Structured</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="loadResFareRulesType('Structured&categories=Penalties')">Penalties</button>

                    <button class="btn btn-secondary btn-sm ms-auto" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
let currentResPNR = '';

function loadResFareRules(pnr){

    if(!pnr){
        alert('PNR not available');
        return;
    }

    currentResPNR = pnr;

    document.getElementById('resFR_PNR').textContent = pnr;

    const modal = new bootstrap.Modal(
        document.getElementById('resFareRulesModal')
    );

    modal.show();

    loadResFareRulesType('ShortText');
}

function loadResFareRulesType(ruleType){

    const body = document.getElementById('resFareRulesBody');

    body.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2 text-muted">Loading fare rules...</p>
        </div>
    `;

    let url =
        'public/ajax/fare_rules.php?source=reservation'
        + '&pnr=' + encodeURIComponent(currentResPNR)
        + '&rule_type=' + encodeURIComponent(ruleType);

    fetch(url)
    .then(r => r.json())
    .then(data => {

        if(data.error){
            body.innerHTML = `
                <div class="alert alert-danger">
                    ${escHtml1(data.error)}
                </div>`;
            return;
        }

        let html = '';

        /* ---------- WARNINGS ---------- */
        if(data.warnings?.length){
            html += `
            <div class="alert alert-warning small">`;
            data.warnings.forEach(w=>{
                html += escHtml1(w.Message) + '<br>';
            });
            html += `</div>`;
        }

        /* ---------- RULES ---------- */
        if(!data.fareRules || !data.fareRules.length){

            html += `
            <div class="text-center text-muted py-3">
                No fare rules found
            </div>`;

        }else{

            data.fareRules.forEach(fr=>{

                /* TEXT RULES */
                if(fr.type === 'text'){

                    html += `
                    <div class="mb-3">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:200px">Category</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    fr.rules.forEach(r=>{
                        html += `
                        <tr>
                            <td class="fw-semibold small">${escHtml1(r.name)}</td>
                            <td class="small" style="white-space:pre-wrap;">
                                ${escHtml1(r.value)}
                            </td>
                        </tr>`;
                    });

                    html += `</tbody></table></div>`;
                }

                /* STRUCTURED RULES */
                if(fr.type === 'structured'){

                    html += `
                    <div class="mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="bi bi-shield-check"></i> Structured Rules
                        </h6>

                        <table class="table table-sm table-bordered">
                            <tbody>`;

                    fr.penalties.forEach(p=>{

                        if(p.change){
                            html += `<tr>
                                <td>Change</td>
                                <td>${escHtml1(p.change)}</td>
                            </tr>`;
                        }

                        if(p.cancel){
                            html += `<tr>
                                <td>Cancel</td>
                                <td class="text-danger">${escHtml1(p.cancel)}</td>
                            </tr>`;
                        }

                        if(p.minimumStay){
                            html += `<tr>
                                <td>Min Stay</td>
                                <td>${escHtml1(p.minimumStay)}</td>
                            </tr>`;
                        }

                        if(p.maximumStay){
                            html += `<tr>
                                <td>Max Stay</td>
                                <td>${escHtml1(p.maximumStay)}</td>
                            </tr>`;
                        }

                    });

                    html += `</tbody></table></div>`;
                }

            });
        }

        body.innerHTML = html;

    })
    .catch(err=>{
        body.innerHTML = `
            <div class="alert alert-danger">
                ${escHtml1(err.message)}
            </div>`;
    });
}

/* helper */
function escHtml1(str){
    if(!str) return '';
    return String(str)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;');
}
</script>

<script>
function fetchTickets(pnr, bookingId){

    if(!pnr){
        alert('PNR missing');
        return;
    }

    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Fetching...';

    fetch('public/ajax/fetch_travelport_tickets.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
            pnr: pnr,
            booking_id: bookingId
        })
    })
    .then(r => r.json())
    .then(res => {

        if(!res.success){
            alert(res.error || 'Failed');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-download"></i> Fetch Tickets';
            return;
        }

        // reload view
        openViewModal(bookingId);

    })
    .catch(err=>{
        alert(err.message);
        btn.disabled = false;
    });
}
</script>