<?php
require_once __DIR__ . '/_auth.php'; // adjust if your auth path differs
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php'; // optional

require_once __DIR__ . '/includes/header.php'; // include your page header (nav, styles)
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

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
<div class="container-fluid">

    <!-- PAGE TITLE -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="mb-0 fw-semibold">
            <i class="fa fa-credit-card me-2 text-primary"></i>
            Bookings Payments
        </h4>
    </div>


    <!-- FILTERS -->
    <div class="card shadow-sm mb-2">
        <div class="card-body">

            <div class="row g-3">

                <div class="col-lg-3">
                    <label class="form-label small text-muted">
                        Date Range
                    </label>
                    <input type="text"
                           class="form-control"
                           id="filterDate">
                    <input type="hidden" id="filter-daterange-raw">
                </div>

                <div class="col-lg-2">
                    <label class="form-label small text-muted">
                        Status
                    </label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All</option>
                        <option value="captured">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="declined">Declined</option>
                    </select>
                </div>

                <div class="col-lg-3">
                    <label class="form-label small text-muted">
                        Search
                    </label>
                    <input type="text"
                           class="form-control"
                           id="filterSearch"
                           placeholder="Booking ID / Reference">
                </div>

                <div class="col-lg-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" id="btnFilter">
                        <i class="fa fa-search me-1"></i>
                        Apply
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="row g-3 mb-3">

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Total Payments</div>
                    <div class="fs-4 fw-bold" id="stat_total">0</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Successful</div>
                    <div class="fs-4 fw-bold text-success" id="stat_success">0</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Pending</div>
                    <div class="fs-4 fw-bold text-warning" id="stat_pending">0</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Failed</div>
                    <div class="fs-4 fw-bold text-danger" id="stat_failed">0</div>
                </div>
            </div>
        </div>

    </div>

    <!-- PAYMENTS TABLE -->
    <div class="card shadow-sm">

        <div class="card-body">

            <table id="paymentsTable"
                   class="table table-hover align-middle w-100">

                <thead class="table-light">
                <tr>
                    <th>Booking</th>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th width="80"></th>
                </tr>
                </thead>

            </table>

        </div>

    </div>

</div>



<!-- VIEW PAYMENT MODAL -->
<div class="modal fade"
     id="paymentModal"
     tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title">
                    Payment Details
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div id="paymentsBody"></div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script>

let paymentsTable;

let bookingId = 0;

$(function(){

    function getLabel(start, end){

        const today = moment().startOf('day');
        const yesterday = moment().subtract(1,'day').startOf('day');

        if(start.isSame(today,'day') && end.isSame(today,'day')) return 'Today';
        if(start.isSame(yesterday,'day') && end.isSame(yesterday,'day')) return 'Yesterday';

        return start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD');
    }

    function updateRaw(start,end){

        $('#filter-daterange-raw').val(
            start.format('YYYY-MM-DD') +
            ' - ' +
            end.format('YYYY-MM-DD')
        );

    }

    let start = moment().subtract(29,'days');
    let end   = moment();


    $('#filterDate').daterangepicker({

        startDate:start,
        endDate:end,

        autoUpdateInput:false,

        locale:{
            cancelLabel:'Clear',
            format:'YYYY-MM-DD'
        },

        ranges:{
            'Today':[moment(),moment()],
            'Yesterday':[moment().subtract(1,'days'),moment().subtract(1,'days')],
            'Last 7 Days':[moment().subtract(6,'days'),moment()],
            'Last 30 Days':[moment().subtract(29,'days'),moment()],
            'This Month':[moment().startOf('month'),moment().endOf('month')],
            'Last Month':[
                moment().subtract(1,'month').startOf('month'),
                moment().subtract(1,'month').endOf('month')
            ]
        }

    }, function(start,end){

        $('#filterDate').val(getLabel(start,end));
        updateRaw(start,end);

    });


    /* DEFAULT VALUES */

    $('#filterDate').val(getLabel(start,end));
    updateRaw(start,end);


    /* CLEAR */

    $('#filterDate').on('cancel.daterangepicker',function(){

        $(this).val('');
        $('#filter-daterange-raw').val('');

        paymentsTable.ajax.reload();
        loadPaymentStats();

    });

    $('#filterDate').on('apply.daterangepicker',function(){

        paymentsTable.ajax.reload();
        loadPaymentStats();

    });


    /* DATATABLE */

    paymentsTable = $('#paymentsTable').DataTable({

        processing:true,
        serverSide:true,
        ordering:true,

        ajax:{
            url:'public/ajax/bookings_payments.php',
            type:'GET',

            data:function(d){

                d.action   = 'list';
                d.status   = $('#filterStatus').val();
                d.currency = $('#filterCurrency').val();
                d.search   = $('#filterSearch').val();
                d.daterange = $('#filter-daterange-raw').val();

            }
        },

        order:[[5,'desc']],

        columns:[

            {
                data:'booking_id',
                render:function(data){
                    return '<span class="fw-semibold">#'+data+'</span>';
                }
            },

            {data:'reference'},

            {
                data:'amount',
                render:function(data,type,row){

                    let amount = (row.charged_amount/100).toFixed(2);

                    return row.currency+' '+amount;

                }
            },

            {
                data:'status',
                render:function(data){

                    let badge='secondary';

                    if(data=='captured'||data=='success') badge='success';
                    if(data=='pending') badge='warning';
                    if(data=='failed'||data=='declined') badge='danger';

                    return '<span class="badge bg-'+badge+'">'+data+'</span>';

                }
            },

            {data:'payment_method'},

            {
                data:'created_at',
                render:function(data){

                    return moment(data).format('DD MMM YYYY HH:mm');

                }
            },

            {
                data:null,
                orderable:false,
                searchable:false,
                render:function(data,type,row){

                    return `
                    <button class="btn btn-sm btn-outline-primary"
                            onclick="viewPayment(${row.booking_id})">
                            View
                    </button>`;

                }
            }

        ]

    });


    $('#btnFilter').click(function(){
        paymentsTable.ajax.reload();
        loadPaymentStats();
    });


    loadPaymentStats();

});


function viewPayment(id){

    bookingId = id;

    $('#paymentModal').modal('show');

    loadBookingPayments(id);

}


function loadPaymentStats(){

    $.getJSON(
        'public/ajax/bookings_payments.php',
        {
            action:'stats',
            daterange: $('#filter-daterange-raw').val()
        },
        function(res){

            if(!res.success) return;

            $('#stat_total').text(res.data.total);
            $('#stat_success').text(res.data.success);
            $('#stat_pending').text(res.data.pending);
            $('#stat_failed').text(res.data.failed);

        }
    );

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

</script>