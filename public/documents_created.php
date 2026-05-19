<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/includes/header.php';
?>

<!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"> -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<div class="container1 mt-3">

  <!-- <div class="d-flex align-items-center mb-3">
      <h1 class="h4 me-3">Created Documents</h1>
  </div> -->

  <!-- FILTERS ROW -->
  <div class="card p-3 mb-3">
      <div class="row g-2">

          <!-- Entity Type -->
          <div class="col-md-8">
                <h1 class="h4 me-3">Created Documents</h1>
              <label class="form-label d-none">Entity Type</label>
              <div class="btn-group d-none" id="filter_entity_type">
                  <button class="btn btn-outline-primary active" data-value="">All</button>
                  <button class="btn btn-outline-primary" data-value="employee">Employee</button>
                  <button class="btn btn-outline-primary" data-value="customer">Customer</button>
                  <button class="btn btn-outline-primary" data-value="recruiter">Recruiter</button>
                  <button class="btn btn-outline-primary" data-value="other">Other</button>
              </div>
          </div>
          
          <!-- Date Range -->
          <div class="col-md-4">
              <!-- <label class="form-label">Date Range</label> -->
              <input type="text" id="filter_daterange" class="form-control" autocomplete="off">
          </div>

      </div>
  </div>

  <!-- DATATABLE -->
  <div class="card p-3">
      <table id="documentsTable" class="table table-striped table-hover w-100">
          <thead>
              <tr>
                  <!-- <th>ID</th> -->
                  <th>Title</th>
                  <th>Type</th>
                  <th>User</th>
                  <th>Created At</th>
                  <th>Actions</th>
              </tr>
          </thead>
      </table>
  </div>

</div>

<!-- Preview Modal -->
<div class="modal fade" id="modalPreview" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="previewContent"></div>
      </div>
    </div>
  </div>
</div>

<!-- PDF Preview -->
<div class="modal fade" id="modalPdfPreview" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pdfTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="pdfFrame" style="width:100%;height:80vh;border:0;"></iframe>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> -->
<!-- <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
$(document).ready(function() {

    /* -------------------------------
       DATE RANGE PICKER (FIXED)
    --------------------------------*/
    let startDate = moment().subtract(29, 'days');
    let endDate   = moment();

    function updateDateRangeDisplay(start, end) {
        $('#filter_daterange').val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
    }

    $('#filter_daterange').daterangepicker({
            startDate: startDate,
            endDate: endDate,
            opens: 'right',
            autoUpdateInput: true,

            locale: {
                format: 'DD-MM-YYYY',
                applyLabel: "Apply",
                cancelLabel: "Clear"
            },

            ranges: {
                'Today': [moment(), moment()],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [
                    moment().subtract(1, 'month').startOf('month'),
                    moment().subtract(1, 'month').endOf('month')
                ]
            }
        },

        function(start, end) {
            startDate = start;
            endDate   = end;

            updateDateRangeDisplay(start, end);

            // reload table
            table.ajax.reload();
        }
    );

    // Set initial display on load
    updateDateRangeDisplay(startDate, endDate);



    /* -------------------------------
       ENTITY TYPE FILTER
    --------------------------------*/
    let filterEntityType = "";

    $("#filter_entity_type button").click(function() {
        $("#filter_entity_type button").removeClass("active");
        $(this).addClass("active");
        filterEntityType = $(this).data("value");
        table.ajax.reload();
    });


    /* -------------------------------
       DATATABLE INITIALIZATION
    --------------------------------*/
    const table = $("#documentsTable").DataTable({
        processing: true,
        serverSide: true,
        order: [[3,'desc']],
        ajax: {
            url: "public/ajax/document_templates.php?action=datatable_documents",
            type: "POST",
            data: function(d) {
                d.start_date = startDate.format("YYYY-MM-DD");
                d.end_date   = endDate.format("YYYY-MM-DD");
                d.entity_type = filterEntityType;
            }
        },
        columns: [
            // { data: "id" },
            { data: "title" },
            { data: "subtype" },
            {
                data: null,
                render: function(row) {
                    return (row.entity_name ?" <strong>"+row.entity_name+ "</strong><br>":'')+row.entity_type;
                }
            },

            { data: "created_at" },
            {
                data: null,
                orderable: false,
                render: function(row) {
                    return `
                        <button class="btn btn-sm btn-outline-primary btn-view" data-id="${row.id}">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary btn-print" data-id="${row.id}" data-title="${row.title}">
                            <i class="fa fa-print"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success btn-add" 
                            data-id="${row.id}" 
                            data-entity="${row.entity_type}"
                            data-identifier="${row.entity_identifier}"
                            data-title="${row.title}">
                            <i class="fa fa-user-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}">
                            <i class="fa fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });


    /* -------------------------------
       ACTION BUTTON EVENTS
    --------------------------------*/

    // PREVIEW
    $("#documentsTable").on("click", ".btn-view", async function() {
        const id = $(this).data("id");
        const res = await fetch("public/ajax/document_templates.php?action=get_document&id=" + id).then(r => r.json());
        if (res.ok) {
            $("#previewContent").html(res.data.content.replace(/\n/g, "<br>"));
            new bootstrap.Modal("#modalPreview").show();
        }
    });

    // PRINT (PDF modal)
    $("#documentsTable").on("click", ".btn-print", function() {
        const id = $(this).data("id");
        const title = $(this).data("title");
        $("#pdfTitle").text(title);
        $("#pdfFrame").attr("src", "public/ajax/document_templates.php?action=download_pdf&id=" + id);
        new bootstrap.Modal("#modalPdfPreview").show();
    });

    // ADD TO PROFILE
    $("#documentsTable").on("click", ".btn-add", async function() {
        const id = $(this).data("id");
        const entity = $(this).data("entity");
        const identifier = $(this).data("identifier");
        const title = $(this).data("title");

        if (!confirm("Add this document to profile?")) return;

        const body = new URLSearchParams();
        body.append("doc_id", id);
        body.append("entity_type", entity);
        body.append("entity_identifier", identifier);
        body.append("title", title);

        const res = await fetch("public/ajax/document_templates.php?action=add_to_profile", {
            method: "POST",
            body
        }).then(r => r.json());

        if (res.ok) alert("Added to profile.");
        else alert(res.error);
    });

    // DELETE
    $("#documentsTable").on("click", ".btn-delete", async function() {
        if (!confirm("Delete this document?")) return;

        const id = $(this).data("id");

        const res = await fetch("public/ajax/document_templates.php?action=delete_document", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + encodeURIComponent(id)
        }).then(r => r.json());

        if (res.ok) table.ajax.reload();
        else alert(res.error);
    });

});
</script>