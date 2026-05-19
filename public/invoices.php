<?php
// public/invoices.php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
?>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script src="public/assets/js/jselect1.js?jv=<?=time()?>"></script>
<script src="public/assets/js/sweetalert.js?jv=<?=time()?>"></script>

<div class="container-fluid mt-3 mb-3">

    <!-- PAGE HEADER + FILTERS -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">All Invoices</h4>

        <div class="d-flex gap-2">
            <input type="text" id="daterange" class="form-control form-control-sm" style="width:200px;" />

            <button class="btn btn-outline-primary btn-sm filter-btn active" data-type="all">All</button>
            <!-- <button class="btn btn-outline-info btn-sm filter-btn" data-type="employees">Employees</button> -->
            <button class="btn btn-outline-success btn-sm filter-btn" data-type="recruiters">Recruiters</button>
            <button class="btn btn-outline-warning btn-sm filter-btn" data-type="customers">Customers</button>

            <button class="btn btn-primary btn-sm" id="addInvoiceBtn">
                <i class="fa fa-plus"></i> Add Invoice
            </button>
        </div>
    </div>

    <!-- SUMMARY AREA -->
    <div id="invoice-summary" class="px-1 pb-3"></div>

    <!-- DATATABLE -->
    <table id="invoicesTable" class="table table-striped table-bordered table-sm w-100">
        <thead>
        <tr>
            <th>ID</th>
            <th>User Type</th>
            <th>Name</th>
            <th>Category</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Invoice Date</th>
            <th>Due Date</th>
            <th>Notes</th>
            <th style="width:12%;">Action</th>
        </tr>
        </thead>
    </table>

</div>


<!-- DOCUMENT VIEWER -->
<div class="modal fade" id="documentModal" tabindex="-1" style="z-index: 1056 !important;">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="documentModalLabel">Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="documentViewer"
           style="min-height: 75vh; display:flex; justify-content:center; align-items:center;">
        <div class="text-muted small">Loading...</div>
      </div>

    </div>
  </div>
</div>


<!-- INVOICE MODAL -->
<div class="modal fade" id="invoiceModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header py-2">
        <h6 class="modal-title fw-semibold">
          <i class="fa fa-file-invoice me-2 text-primary"></i>
          <span id="invoiceModalTitle">Add Invoice</span>
        </h6>
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
      </div>

      <form id="invoiceForm" enctype="multipart/form-data">
        <input type="hidden" name="id" id="invoice_id">

        <div class="modal-body py-2 px-3">

          <!-- Add Mode Only -->
          <div class="row mb-3 addonly">

            <!-- USER TYPE -->
            <div class="col-md-4">
              <label class="form-label fw-semibold small mb-1">User Type</label>
              <select id="invoice_user_type" name="invoice_user_type"
                      class="form-control form-control-xs rounded-pill jselect"
                      data-class="outline-primary btn-xs" required>
                <option value="customer">Customer</option>
                <option value="recruiter">Recruiter</option>
              </select>
                <!-- <option value="employee">Employee</option> -->
            </div>

            <!-- USER SEARCH -->
            <div class="col-md-4 mt-2 addonly">
              <input type="text"
                     id="invoice_user_search"
                     class="form-control form-control-xs rounded-pill"
                     placeholder="Search user..."
                     autocomplete="off">
            </div>

            <!-- USER SELECT -->
            <div class="col-md-4">
              <label class="form-label fw-semibold small mb-1">Select User</label>
              <select id="invoice_user" name="invoice_user"
                      class="form-control form-control-xs rounded-pill jselect"
                      data-class="outline-success btn-xs" required></select>
            </div>

          </div>

          <!-- Date + Amount + Type -->
          <div class="row mb-3">

            <div class="col-md-4">
              <label class="form-label fw-semibold small mb-1">Invoice Date</label>
              <input type="date" class="form-control form-control-xs rounded-pill"
                     name="invoice_date" id="invoice_date" required>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold small mb-1">Due Date</label>
              <input type="date" class="form-control form-control-xs rounded-pill"
                     name="due_date" id="due_date">
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold small mb-1">Amount</label>
              <input type="number" step="0.01"
                     class="form-control form-control-xs rounded-pill"
                     name="invoice_amount" id="invoice_amount" required>
            </div>

          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold small mb-1">Type</label>
              <select id="invoice_type" name="invoice_type"
                      class="form-control form-control-xs rounded-pill jselect"
                      data-class="outline-primary btn-xs">
                <option value="Received">Received</option>
                <option value="Sent">Sent</option>
              </select>
            </div>

            <div class="col-md-8">
              <label class="form-label fw-semibold small mb-1">
                Invoice Category
                <button type="button" class="btn btn-outline-success rounded-pill btn-xs"
                          style="padding:0em 1em;"
                          id="addCategoryBtni">
                    <i class="fa fa-plus"></i> New
                  </button>
              </label>
              <select name="invoice_category" id="invoice_category" class="form-control form-control-xs rounded-pill jselect" data-class="outline-success btn-xs">
                  <!-- <option value="">Loading...</option> -->
                </select>
              <!-- <input type="text" class="form-control form-control-xs rounded-pill" name="invoice_category" id="invoice_category"> -->
            </div>
          </div>

          <!-- DOCUMENT -->
          <div class="mb-3">
            <label class="form-label fw-semibold small mb-1">Upload Document (single file)</label>
            <input type="file" accept=".pdf,image/*"
                   class="form-control form-control-sm rounded-pill"
                   name="document" id="document">
            <div id="existingDocument" class="mt-2"></div>
          </div>

          <!-- NOTES -->
          <div class="mb-2">
            <label class="form-label fw-semibold small mb-1">Notes</label>
            <textarea name="notes" id="notesi"
                      class="form-control form-control-xs rounded-3"
                      rows="2"></textarea>
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

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
$(function () {

    /* ---------------------------
       DOCUMENT VIEW HANDLER
    ---------------------------- */
    window.viewDoc = function(e) {
        e.preventDefault();
        const btn = $(this);
        const file = btn.data("file");
        const type = btn.data("type") || "";

        $("#documentViewer").html("Loading...");
        $("#documentModal").modal("show");

        if (!file) {
            $("#documentViewer").html('<div class="text-center text-muted small py-5">File not found.</div>');
            return;
        }

        if (type === "pdf") {
            $("#documentViewer").html(
                `<iframe src="${file}" style="width:100%; height:80vh; border:0;"></iframe>`
            );
        } else {
            $("#documentViewer").html(
                `<img src="${file}" class="img-fluid rounded shadow" style="max-height:80vh;">`
            );
        }
    };

    /* ---------------------------
       INIT DATE RANGE PICKER
    ---------------------------- */
    $('#daterange').daterangepicker({
        locale: { format: 'DD-MM-YYYY' },
        startDate: "<?=date('d-m-Y',strtotime($date.' - 6 days'))?>",
        endDate: "<?=date('d-m-Y',strtotime($date))?>",
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    /* ---------------------------
       Load users helper (Option A — uses public/ajax/get_users.php)
    ---------------------------- */
    function loadUsersInvoices(type, keyword = "") {
        const $sel = $("#invoice_user");
        // destroy jselect if present
        if ($sel.data("jselect")) $sel.data("jselect").destroy();
        $sel.html('<option value="">Loading...</option>');

        $.post("public/ajax/get_users.php", { type: type, keyword: keyword }, function (res) {
            $sel.empty();
            if (res.success && Array.isArray(res.data) && res.data.length) {
                res.data.forEach(u => {
                    $sel.append(`<option value="${u.id}">${u.name}</option>`);
                });
            } else {
                $sel.append('<option value="">No users found</option>');
            }
            $sel.attr("name", "invoice_user");
            refreshJSelect("invoice_user");
        }, "json").fail(function () {
            $sel.empty().append('<option value="">Error loading users</option>');
            refreshJSelect("invoice_user");
        });
    }

    $("#invoice_user_type").on("change", function () {
        const type = $(this).val();
        const placeholders = {
            employee: "Search employee...",
            recruiter: "Search recruiter...",
            customer: "Search customer..."
        };
        $("#invoice_user_search").attr("placeholder", placeholders[type] || "Search user...");
        loadUsersInvoices(type, "");
    });

    let invUserTimer = null;
    $("#invoice_user_search").on("keyup", function () {
        clearTimeout(invUserTimer);
        const keyword = $(this).val().trim();
        const type = $("#invoice_user_type").val();
        invUserTimer = setTimeout(() => loadUsersInvoices(type, keyword), 300);
    });

    // trigger initial load
    $("#invoice_user_type").trigger("change");

    /* ---------------------------
       INIT DATATABLE
    ---------------------------- */
    window.table = $("#invoicesTable").DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        order: [[6, "desc"]], // invoice date column index
        ajax: {
            url: "public/ajax/invoices.php",
            type: "POST",
            data: function (d) {
                d.daterange = $("#daterange").val();
                d.filterType = window.currentType ?? "all";
            },
            dataSrc: function (json) {
                if (json.summary) {
                    let r = parseFloat(json.summary.received || 0);
                    let s = parseFloat(json.summary.sent || 0);
                    let c = parseInt(json.summary.total || 0);

                    $("#invoice-summary").html(`
                      <div class="row g-2">
                        <div class="col-4">
                          <div class="rounded bg-success-subtle border border-success text-center p-2">
                            <div class="fw-bold small text-success">Total Received</div>
                            <div class="fw-bold text-success fs-6">AED ${r.toFixed(2)}</div>
                          </div>
                        </div>
                        <div class="col-4">
                          <div class="rounded bg-warning-subtle border border-warning text-center p-2">
                            <div class="fw-bold small text-warning">Total Sent</div>
                            <div class="fw-bold text-warning fs-6">AED ${s.toFixed(2)}</div>
                          </div>
                        </div>
                        <div class="col-4">
                          <div class="rounded bg-primary-subtle border border-primary text-center p-2">
                            <div class="fw-bold small text-primary">Total Invoices</div>
                            <div class="fw-bold text-primary fs-6">${c}</div>
                          </div>
                        </div>
                      </div>
                    `);
                }
                return json.data || [];
            }
        },
        columns: [
            { data: "id" },
            { data: "source_typed" },
            { data: "name" },
            { data: "category" },
            { data: "type" },
            { data: "amount" },
            { data: "date" },
            { data: "due_date" },
            { data: "notes" },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (row) {
                    return `
                        <button class="btn btn-sm btn-outline-primary viewInvoice" data-id="${row.pid}" data-src="${row.source_type}">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success editInvoice" data-id="${row.pid}" data-src="${row.source_type}">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger deleteInvoice" data-id="${row.pid}" data-src="${row.source_type}">
                            <i class="fa fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    /* ---------------------------
       FILTER HANDLERS
    ---------------------------- */
    $('#daterange').on("apply.daterangepicker", () => table.ajax.reload());
    $(".filter-btn").on("click", function () {
        $(".filter-btn").removeClass("active");
        $(this).addClass("active");

        window.currentType = $(this).data("type");
        table.ajax.reload();
    });

    /* ---------------------------
       ADD INVOICE
    ---------------------------- */
    $("#addInvoiceBtn").click(function () {
        $("#invoiceForm")[0].reset();
        $("#invoice_id").val("");
        $("#existingDocument").html("");
        $(".addonly").show();
        $("#invoiceModalTitle").text("Add Invoice");
        // set invoice date to today
        $("#invoice_date").val("<?=date('Y-m-d', strtotime($date))?>");
        $("#due_date").val("<?=date('Y-m-d', strtotime("+7 days",strtotime($date)))?>");
        $("#invoiceModal").modal("show");
        // ensure user select has data
        const type = $("#invoice_user_type").val();
        loadUsersInvoices(type, "");
    });

    /* ---------------------------
       EDIT INVOICE
    ---------------------------- */
    $(document).on("click", ".editInvoice", function () {
        let id = $(this).data("id");
        let src = $(this).data("src");

        $.post("public/ajax/invoices_actions.php", { action: "get", id: id, user_type: src }, function (res) {
            if (!res.success) return alert("Failed to load invoice.");

            const d = res.data;

            $("#invoiceForm")[0].reset();
            $("#invoice_id").val(d.pid);
            $("#invoice_date").val(d.invoice_date);
            $("#due_date").val(d.due_date || "");
            $("#invoice_amount").val(d.amount);
            $("#invoice_type").val(d.type).trigger("change");
            $("#invoice_user_type").val(src);
            $("input[type='hidden'][name='invoice_user_type']").val(src);
            $("#invoice_category").val(d.category);

            if(d.category!='') {
              var $select = $("#invoice_category");
              $select.html(''); // prevent loading other category
              if ($select.find(`option[value="${d.category}"]`).length === 0) {
                $select.append(`<option value="${d.category}">${d.category}</option>`);
                const newOpt = $select.find(`option[value="${d.category}"]`)[0];
                newOpt.dataset.class = "outline-success"; // optional
                $select[0].addNewOptionButton(newOpt);
              }
              $("#invoice_category").attr("name", "invoice_category");
              refreshJSelect("invoice_category");
              $("#invoice_category").val(d.category).trigger("change");
              $("input[type='hidden'][name='invoice_category']").val(d.category);
            }

            $("#notesi").val(d.notes || "");

            // Hide add-only fields (user type/select) when editing
            $(".addonly").hide();

            // Show existing document if present (single file stored as filename)
            if (d.document) {
                const fileUrl = d.document_fullpath || ("uploads/" + (d.source_type+"s" || "employees") + "/invoices/" + d.document);
                $("#existingDocument").html(`
                    <div class="d-flex align-items-center gap-2 bg-white border rounded p-2">
                        <div><i class="fa fa-file fa-2x text-muted"></i></div>
                        <div class="flex-grow-1 small text-truncate">${d.document}</div>
                        <div>
                          <button type="button" class="btn btn-sm btn-outline-primary viewInvoiceDoc" data-file="${fileUrl}" data-type="${d.document_type || 'pdf'}">View</button>
                          <button type="button" class="btn btn-sm btn-danger removeExistingDoc">Remove</button>
                        </div>
                    </div>
                `);
            } else {
                $("#existingDocument").html("");
            }

            $("#invoiceModalTitle").text("Edit Invoice");
            $("#invoiceModal").modal("show");

            // clicking view button should open document viewer
            $(document).on("click", ".viewInvoiceDoc", window.viewDoc);
            $(document).on("click", ".removeExistingDoc", function () {
                // mark for deletion by setting hidden input or clearing existingDocument html
                $("#existingDocument").html("");
                // you may also set a hidden flag if invoices_actions.php expects one:
                if (!$("#remove_existing_doc").length) {
                    $("<input>").attr({
                        type: "hidden",
                        id: "remove_existing_doc",
                        name: "remove_existing_doc",
                        value: "1"
                    }).appendTo("#invoiceForm");
                } else {
                    $("#remove_existing_doc").val("1");
                }
            });
        }, "json");
    });

    /* ---------------------------
       VIEW INVOICE (row)
    ---------------------------- */
    $(document).on("click", ".viewInvoice", function () {
        let id = $(this).data("id");
        let src = $(this).data("src");

        $.post("public/ajax/invoices_actions.php", { action: "get", id: id, user_type: src }, function (res) {
            if (!res.success) return alert("Failed to load invoice.");

            const d = res.data;

            // Build document viewer buttons (single file)
            let docsHTML = "<span class='text-muted'>No document</span>";
            if (d.document) {
                const fileUrl = d.document_fullpath || ("uploads/" + (d.source_type+"s" || "employees") + "/invoices/" + d.document);
                docsHTML = `<button class="btn btn-sm btn-outline-primary view-document" data-file="${fileUrl}" data-type="${d.document_type || 'pdf'}">${d.document}</button>`;
            }

            let html = `
                <table class="table table-bordered">
                    <tr><th>ID</th><td>${d.id}</td></tr>
                    <tr><th>User Type</th><td>${d.source_type}</td></tr>
                    <tr><th>User</th><td>${d.name}</td></tr>
                    <tr><th>Category</th><td>${d.category}</td></tr>
                    <tr><th>Type</th><td>${d.type}</td></tr>
                    <tr><th>Amount</th><td>${d.amount}</td></tr>
                    <tr><th>Invoice Date</th><td>${d.invoice_date}</td></tr>
                    <tr><th>Due Date</th><td>${d.due_date || '-'}</td></tr>
                    <tr><th>Notes</th><td>${d.notes || ''}</td></tr>
                    <tr><th>Document</th><td>${docsHTML}</td></tr>
                </table>
            `;

            $("#documentViewer").html(html);
            $("#documentModalLabel").text("Invoice Details");
            $("#documentModal").modal("show");
            $(document).on("click", ".view-document", window.viewDoc);
        }, "json");
    });

    /* ---------------------------
       SAVE FORM (ADD / EDIT)
    ---------------------------- */
    $("#invoiceForm").on("submit", function (e) {
        e.preventDefault();

        const fd = new FormData(this);
        fd.append("action", "save");

        // Determine whether add mode (invoice_id empty) -> require user type/user
        if (!$("#invoice_id").val()) {
            fd.append("user_type", $("#invoice_user_type").val());
            fd.append("user_id", $("#invoice_user").val());
        } else {
            fd.append("user_type", $("#invoice_user_type").val());
        }

        $.ajax({
            url: "public/ajax/invoices_actions.php",
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    $("#invoiceModal").modal("hide");
                    table.ajax.reload();
                } else {
                    alert(res.error || "Failed to save invoice");
                }
            },
            error: function () {
                alert("Error saving invoice");
            }
        });
    });

    /* ---------------------------
       DELETE INVOICE
    ---------------------------- */
    $(document).on("click", ".deleteInvoice", function () {
        if (!confirm("Delete invoice?")) return;
        const id = $(this).data("id");
        const src = $(this).data("src");

        $.post("public/ajax/invoices_actions.php", { action: "delete", id: id, user_type: src }, function (res) {
            if (res.success) table.ajax.reload();
            else alert(res.error || "Failed to delete invoice");
        }, "json");
    });

    /* ---------------------------
       Cleanup events when modals hidden (prevent duplicate handlers)
    ---------------------------- */
    $("#invoiceModal").on("hidden.bs.modal", function () {
        // remove dynamic listeners
        $(document).off("click", ".viewInvoiceDoc");
        $(document).off("click", ".removeExistingDoc");
        $("#remove_existing_doc").remove();
    });

    // Trigger auto generation

    // Auto-notes generator for NEW invoices only
    window.generateAutoNotesinvoice = function () {

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

    $("#invoice_type, #invoice_amount, #invoice_category")
      .on("change keyup", generateAutoNotesinvoice);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>