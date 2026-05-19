<?php
// agent/dashboard.php — stylish dashboard with modal snooze and company fallback
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

?>

<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> -->
<!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"> -->

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script src="public/assets/js/jselect1.js?jv=<?=time()?>"></script>
<script src="public/assets/js/sweetalert.js?jv=<?=time()?>"></script>

<div class="container-fluid mt-3 mb-3">

    <!-- PAGE HEADER + FILTERS -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">All Payments</h4>

        <div class="d-flex gap-2">
            <input type="text" id="daterange" class="form-control form-control-sm" style="width:200px;" />

            <button class="btn btn-outline-primary btn-sm filter-btn active" data-type="all">All</button>
            <button class="btn btn-outline-info btn-sm filter-btn" data-type="employees">Employees</button>
            <button class="btn btn-outline-success btn-sm filter-btn" data-type="recruiters">Recruiters</button>
            <button class="btn btn-outline-warning btn-sm filter-btn" data-type="customers">Customers</button>

            <button class="btn btn-primary btn-sm" id="addPaymentBtn" style1="display: none;">
                <i class="fa fa-plus"></i> Add Payment
            </button>
        </div>
    </div>

    <!-- SUMMARY AREA -->
    <div id="payment-summary" class="px-1 pb-3"></div>

    <!-- DATATABLE -->
    <table id="paymentsTable" class="table table-striped table-bordered table-sm w-100">
        <thead>
        <tr>
            <th>ID</th>
            <th>Type</th> <!-- employee / recruiter / customer -->
            <th>Name</th>
            <th>Category</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th>Notes</th>
            <th style="width: 12%;">Action</th>
        </tr>
        </thead>
    </table>

</div>

<!-- 🟦 DOCUMENT VIEWER MODAL (REQUIRED) -->
<div class="modal fade" id="documentModal" tabindex="-1">
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


            <div class="modal fade" id="viewPaymentModal" tabindex="-1">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                  <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>

                  <div class="modal-body" id="viewPaymentBody">
                    Loading...
                  </div>

                  <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        Close
                    </button>
                  </div>

                </div>
              </div>
            </div>
            <!-- payment Modal -->
              <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header py-2">
                      <h6 class="modal-title fw-semibold" id="paymentModalLabel">
                        <i class="fa fa-file-payment me-2 text-primary"></i>
                        <span id="paymentModalTitle">Add Payment</span>
                      </h6>
                      <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
                    </div>

                    <form id="paymentForm" enctype="multipart/form-data">
                      <input type="hidden" name="id" id="payment_id">

                      <div class="modal-body py-2 px-3">

                        <!-- USER TYPE + USER -->
                        <div class="row mb-3">

                          <!-- USER TYPE -->
                          <div class="col-md-5 addonly">
                            <label class="form-label fw-semibold small mb-1">User Type</label>
                            <select id="payment_user_type" name="payment_user_type" class="form-control form-control-xs rounded-pill jselect" data-class="outline-primary btn-xs" required>
                              <option value="customer">Customer</option>
                              <option value="recruiter">Recruiter</option>
                              <option value="employee">Employee</option>
                            </select>
                          </div>

                            <!-- USER SEARCH -->
                            <div class="mt-2 addonly">
                              <input type="text"
                                     id="payment_user_search"
                                     class="form-control form-control-xs rounded-pill"
                                     placeholder="Search user..."
                                     autocomplete="off">
                            </div>

                          <!-- USER SELECT -->
                          <div class="col-md-7 addonly">
                            <label class="form-label fw-semibold small mb-1">Select User</label>
                            <select id="payment_user" name="payment_user"
                                    class="form-control form-control-xs rounded-pill jselect"
                                    data-class="outline-success btn-xs" required>
                              <!-- <option value="">Select User</option> -->
                            </select>
                          </div>

                        </div>


                        <!-- Date + Type -->
                        <div class="mb-3 row">
                          <div class="col-md-4">
                            <label for="payment_date" class="form-label fw-semibold small mb-1">Date</label>
                            <select name="payment_date" id="payment_date"
                                    class="form-control form-control-xs rounded-pill jselect"
                                    data-class="outline-primary btn-xs" data-type="date" required>
                              <option value="<?=date("Y-m-d",strtotime($date))?>">Today</option>
                              <option value="<?=date("Y-m-d",strtotime("+1 day",strtotime($date)))?>">Tomorrow</option>
                              <option value="Other" data-other="true">Other</option>
                            </select>
                          </div>

                          <div class="col-md-4">
                            <label for="payment_amount" class="form-label fw-semibold small mb-1">Amount</label>
                            <input type="number" name="payment_amount" id="payment_amount"
                                   step="0.01" class="form-control form-control-xs rounded-pill"
                                   value="0" placeholder="Payment Amount..">
                          </div>

                          <div class="col-md-4">
                            <label for="payment_type" class="form-label fw-semibold small mb-1 d-flex align-items-center">
                              Type
                              <i class="fa fa-info-circle ms-1 text-primary"
                                 data-bs-toggle="tooltip"
                                 style="cursor:pointer;"
                                 title="Income = Money RECEIVED.&#10;Expense = Money SPENT."></i>
                            </label>
                            <select name="payment_type" id="payment_type"
                                    class="form-control form-control-xs rounded-pill jselect"
                                    data-class="outline-primary btn-xs" required>
                              <option value="Expense">Expense</option>
                              <option value="Income">Income</option>
                            </select>
                          </div>

                          <div class="col-md-4" style="display:none;">
                            <label for="reclaim_by" class="form-label fw-semibold small mb-1">Reclaim By</label>
                            <select name="reclaim_by" id="reclaim_by"
                                    class="form-control form-control-xs rounded-pill jselect"
                                    data-class="outline-primary btn-xs">
                              <option value="Company">Company</option>
                              <option value="Employee">Employee</option>
                            </select>
                          </div>
                        </div>

                        <!-- ◼️ CATEGORY (UNCHANGED – AS YOU REQUESTED) -->
                        <div class="mb-3">
                          <label for="payment_category" class="form-label fw-semibold small mb-1">
                            Category
                            <button type="button" class="btn btn-outline-success rounded-pill btn-xs"
                                    style="padding:0em 1em;"
                                    id="addCategoryBtn">
                              <i class="fa fa-plus"></i> New
                            </button>
                          </label>

                          <select name="payment_category" id="payment_category"
                                  class="form-control form-control-xs jselect"
                                  data-class="outline-success btn-xs">

                            <?php
                              $getpcats = $db->get('payment_categories',array('#all'=>1,'type'=>'Income'));
                              foreach ($getpcats->data as $pk => $pcat) {
                            ?>
                                <option value="<?=$pcat->category?>"><?=$pcat->category?></option>
                            <?php } ?>

                          </select>
                        </div>

                        <!-- AMOUNT FIRST -->
                        <div class="row">
                          <!-- PAYMENT STATUS -->
                          <div class="col-md-4">
                            <label class="form-label fw-semibold small">Payment Status</label>
                            <select name="payment_status" id="payment_status"
                                    class="form-control form-control-xs jselect"
                                    data-class="outline-primary btn-xs" required>
                              <option value="Unpaid">Unpaid</option>
                              <option value="Paid">Paid</option>
                              <option value="Partial Paid">Partial Paid</option>
                            </select>
                          </div>

                          <div class="col-md-4" id="payment_partiald" style="display:none;">
                            <label for="payment_partial" class="form-label fw-semibold small mb-1">Partial Amount</label>
                            <input type="number" name="payment_partial" id="payment_partial"
                                   step="0.01" class="form-control form-control-xs rounded-pill"
                                   value="0" placeholder="Partial Amount..">
                          </div>
                            
                            <!-- PAYMENT METHOD -->
                            <div class="col-md-8" id="payment_payment_methodd">
                              <label for="payment_payment_method" class="form-label fw-semibold small mb-1">Payment Method</label>
                              <select name="payment_payment_method" id="payment_payment_method"
                                      class="form-control form-control-xs jselect"
                                      data-class="outline-success btn-xs">
                                <option value="Cash">Cash</option>
                                <option value="Card">Card</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Included in Salary">Included in Salary</option>
                              </select>
                            </div>

                        </div>

                        <!-- CARD LAST 4 -->
                        <div class="col-md-4 d-none" id="card_last4_field">
                          <label class="form-label fw-semibold small mb-1">Last 4 digits of card</label>
                          <input type="text" maxlength="4"
                                 class="form-control form-control-xs rounded-pill"
                                 name="card_last4" id="card_last4">
                        </div>

                        <!-- CHEQUE FIELDS -->
                        <div class="row">
                          <div class="col-md-6 d-none" id="bank_fields">
                            <label class="form-label fw-semibold small mb-1">Bank Name</label>
                            <!-- <input type="text" class="form-control form-control-xs rounded-pill" name="cheque_bank" id="cheque_bank"> -->
                            <select class="form-control form-control-xs rounded-pill jselect" 
                                    name="cheque_bank" id="cheque_bank" data-class="outline-primary btn-xs">
                            </select>
                          </div>

                          <div class="col-md-6 d-none" id="cheque_fields">
                            <label class="form-label fw-semibold small mb-1">Cheque Number</label>
                            <input type="text" class="form-control form-control-xs rounded-pill"
                                   name="cheque_issuer" id="cheque_issuer">
                          </div>
                        </div>

                        <!-- REIMBURSABLE -->
                        <div class="row d-none" id="reimbursable_section">
                          <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">Reimbursable?</label>
                            <select name="reimbursable" id="reimbursable" data-class="outline-success btn-xs"
                                    class="form-control form-control-xs rounded-pill jselect">
                              <option value="No">No</option>
                              <option value="Yes">Yes</option>
                            </select>
                          </div>

                          <div class="col-md-4 d-none" id="reimbursement_amount_field">
                            <label class="form-label fw-semibold small mb-1">Reimbursement Amount</label>
                            <input type="number" step="0.01"
                                   class="form-control form-control-xs rounded-pill"
                                   name="reimbursement_amount" id="reimbursement_amount">
                          </div>
                        </div>

                        <!-- DOCUMENT UPLOAD -->
                        <div class="mb-3">
                          <label class="form-label fw-semibold small mb-1">Upload Document(s) (Image / PDF)</label>
                          <input type="file" name="document[]" id="documentp"
                                 accept=".pdf,image/*"
                                 class="form-control form-control-sm rounded-pill" multiple>

                          <div id="documentPreviewp" class="mt-2 d-flex flex-wrap gap-2"></div>
                          <div id="existingDocuments" class="mt-2"></div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-2">
                          <label for="notesp" class="form-label fw-semibold small mb-1">Notes</label>
                          <textarea name="notes" id="notesp"
                                    class="form-control form-control-xs rounded-3"
                                    rows="2"></textarea>
                        </div>

                      </div>

                      <div class="modal-footer py-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                          <i class="fa fa-save me-1"></i> Save payment
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- 🟢 ADD CATEGORY MODAL -->
              <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                  <div class="modal-content rounded-3 shadow">
                    <div class="modal-header py-2">
                      <h6 class="modal-title" id="addCategoryLabel">
                        <i class="fa fa-plus-circle me-1"></i> Add New Category
                      </h6>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form id="addCategoryForm" class="p-3">
                      <div class="mb-2">
                        <label for="new_category_name" class="form-label small mb-1">Category Name</label>
                        <input type="text" class="form-control form-control-sm" id="new_category_name" name="category" placeholder="Enter category name" required>
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
                window.loadPaymentCategories = function(type, selectedCategory = null) {
                    let actionFile = "";

                    // employee → employee categories
                    if ($("#payment_user_type").val() === "employee") {
                        actionFile = "public/ajax/employees_payment_categories.php";
                    }
                    // customer / recruiter → invoice categories
                    else {
                        actionFile = "public/ajax/invoices_categories.php";
                    }

                    const $select = $("#payment_category");
                    $select.html('<option value="">Loading...</option>').prop("disabled", true);

                    $.post(actionFile, { action: "fetch", type: type }, function(res) {

                        $select.empty();

                        if (res.success && res.data.length) {
                            res.data.forEach(cat => {
                                $select.append(
                                    `<option value="${cat.category}">${cat.category}</option>`
                                );
                            });
                            if (selectedCategory) {
                                $select.val(selectedCategory);
                            }
                        } else {
                            $select.append('<option value="">No categories found</option>');
                        }

                        $("#payment_category").attr("name", "payment_category");
                        refreshJSelect("payment_category");
                        $select.prop("disabled", false);

                        $("#payment_category").trigger("change");

                    }, "json");
                };


                  $(function () {
                    const addCatModal = new bootstrap.Modal('#addCategoryModal');

                    // 🟢 Show add-category modal
                    $("#addCategoryBtn").on("click", function () {
                      $("#new_category_name").val('');
                      addCatModal.show();
                      setTimeout(() => $("#new_category_name").trigger("focus"), 300);
                    });

                    // 🟢 Submit new category
                    $("#addCategoryForm").on("submit", function (e) {
                      e.preventDefault();

                      const category = $("#new_category_name").val().trim();
                      let itype = $("#payment_type").val()?.trim() || 'Expense';

                      if (!category) {
                        alert("Please enter a category name.");
                        return;
                      }

                      $.ajax({
                        url: "public/ajax/invoices_categories.php",
                        type: "POST",
                        dataType: "json",
                        data: {
                          action: "add",   // 🟢 tells PHP switch which action to run
                          category: category,
                          type: itype
                        },
                        beforeSend: function () {
                          $("#addCategoryForm button[type='submit']")
                            .prop("disabled", true)
                            .html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                        },
                        success: function (res) {
                          $("#addCategoryForm button[type='submit']")
                            .prop("disabled", false)
                            .html('<i class="fa fa-save me-1"></i> Save');

                          if (res.success) {
                            addCatModal.hide();

                              // ✅ Add to dropdown dynamically
                              var $select = $("#payment_category");
                              if ($select.find(`option[value="${category}"]`).length === 0) {
                                $select.append(`<option value="${category}">${category}</option>`);
                                const newOpt = $select.find(`option[value="${category}"]`)[0];
                                newOpt.dataset.class = "outline-success"; // optional
                                $select[0].addNewOptionButton(newOpt);
                              }
                              $select.val(category).trigger("change");
                              $("input[type='hidden'][name='payment_category']").val(category);

                              generateAutoNotes();

                            // Optional alert/toast (your custom function)
                            salert("Category Added", `"${category}" was added successfully.`, "success");
                          } else {
                            salert("Failed", res.error || "Failed to add category.", "error");
                          }
                        },
                        error: function () {
                          $("#addCategoryForm button[type='submit']")
                            .prop("disabled", false)
                            .html('<i class="fa fa-save me-1"></i> Save');
                          alert("Error while adding category.");
                        }
                      });
                    });

                    // 🟢 When payment type changes, reload category options dynamically
                    $("#payment_type").on("change", function (event, selectedCategory = null) {

                      // SHOW/HIDE UNPAID BASED ON RULES
                      if (
                          $("#payment_user_type").val() === "employee" &&
                          $("#payment_type").val() === "Expense" &&
                          $("#payment_id").val().trim() === ""       // only new payments
                      ) {
                          hideUnpaidInJselect(); // Employee + Expense → hide
                          $("#payment_status").val("Paid");
                          refreshJSelect("payment_status");
                      }
                      else {
                          showUnpaidInJselect(); // All other cases → show Unpaid
                      }


                      // If new + employee + expense → hide unpaid
                      // if ($("#payment_id").val().trim() === "" &&
                      //     $("#payment_user_type").val() === "employee" &&
                      //     $("#payment_type").val() === "Expense") {

                      //     $("#payment_status option[value='Unpaid']").hide();
                      //     // optional:
                      //     // $("#payment_status option[value='Partial Paid']").hide();

                      //     // Force Paid to stay selected
                      //     $("#payment_status").attr("name", "payment_status");
                      //     $("#payment_status").val("Paid");
                      //     refreshJSelect("payment_status");
                      // }


                        const type = $("#payment_type").val();
                        window.loadPaymentCategories(type, selectedCategory);
                    });

                    function loadUsers(type, keyword = "") {
                        let $sel = $("#payment_user");
                        // Destroy jSelect before updating
                        if ($sel.data("jselect")) {
                            $sel.data("jselect").destroy();
                        }
                        $sel.html('<option value="">Loading...</option>');
                        $.post("public/ajax/get_users.php", 
                            { type: type, keyword: keyword },
                            function (res) {
                            $sel.empty();
                            if (res.success && res.data.length) {
                                res.data.forEach(u => {
                                    $sel.append(`<option value="${u.id}">${u.name}</option>`);
                                });
                            } else {
                                $sel.append('<option value="">No users found</option>');
                            }
                            $sel.attr("name", "payment_user");
                            refreshJSelect("payment_user");
                        }, "json");
                    }


                    function hideUnpaidInJselect() {
                        // Hide the actual <option>
                        $("#payment_status option[value='Unpaid']").hide();


                        const $status = $("#payment_status");
                        $status.empty(); // clear all
                        $status.append(`<option value="Paid">Paid</option>`);
                        $status.append(`<option value="Partial Paid">Partial Paid</option>`);
                   

                        // Refresh jSelect → regenerates buttons
                        $("#payment_status").attr("name", "payment_status");
                        refreshJSelect("payment_status");

                        // Hide corresponding jSelect button
                        // const wrap = $("#payment_status").closest(".jselect-wrapper");
                        // wrap.find(".jselect-btn[data-value='Unpaid']").hide();
                    }
                    function showUnpaidInJselect() {
                        $("#payment_status option[value='Unpaid']").show();
                        
                        const $status = $("#payment_status");
                        $status.empty(); // clear all
                        
                        $status.append(`<option value="Unpaid">Unpaid</option>`);
                        $status.append(`<option value="Paid">Paid</option>`);
                        $status.append(`<option value="Partial Paid">Partial Paid</option>`);
                        
                        $("#payment_status").attr("name", "payment_status");
                        refreshJSelect("payment_status");

                        // const wrap = $("#payment_status").closest(".jselect-wrapper");
                        // wrap.find(".jselect-btn[data-value='Unpaid']").show();
                    };

                    // ➤ Load users based on type
                    $("#payment_user_type").on("change", function () {

                      // ▶ AUTO SET FOR EMPLOYEE (ADD PAYMENT ONLY)
                      if (
                          $("#payment_user_type").val() === "employee" &&
                          $("#payment_type").val() === "Expense" &&
                          $("#payment_id").val().trim() === ""       // new payment only
                      ) {
                          hideUnpaidInJselect();
                          $("#payment_status").val("Paid");
                          $("#payment_status").attr("name", "payment_status");
                          refreshJSelect("payment_status");
                      }
                      else {
                          showUnpaidInJselect();
                      }

                        let type = $(this).val();
                        const placeholders = {
                            employee: "Search employee...",
                            recruiter: "Search recruiter...",
                            customer: "Search customer..."
                        };
                        $("#payment_user_search").attr("placeholder", placeholders[type]);
                        loadUsers(type, "");  // load initial list

                        // Load categories based on new user type + current payment type
                        const ptype = $("#payment_type").val();
                        window.loadPaymentCategories(ptype);                        

                        // let $userSelect = $("#payment_user");
                        // // 1️⃣ Destroy jSelect before modifying options
                        // if ($userSelect.data("jselect")) {
                        //     $userSelect.data("jselect").destroy();
                        // }
                        // // 2️⃣ Clear dropdown and show loading...
                        // $userSelect.html('<option value="">Loading...</option>');
                        // $.post("public/ajax/get_users.php", { type: type }, function (res) {
                        //     $userSelect.empty(); // clear old
                        //     if (res.success && res.data.length) {
                        //         res.data.forEach(u => {
                        //             $userSelect.append(`<option value="${u.id}">${u.name}</option>`);
                        //         });
                        //     } else {
                        //         $userSelect.append('<option value="">No users found</option>');
                        //     }
                        //     // 3️⃣ Restore the select name
                        //     $userSelect.attr("name", "payment_user");
                        //     // 4️⃣ Rebuild jSelect correctly
                        //     refreshJSelect("payment_user");
                        // }, "json")
                        // .fail(function () {
                        //     alert("Error loading users.");
                        // });
                    });

                    let userSearchTimer = null;
                    $("#payment_user_search").on("keyup", function () {
                        clearTimeout(userSearchTimer);
                        const keyword = $(this).val().trim();
                        const type = $("#payment_user_type").val();
                        userSearchTimer = setTimeout(() => {
                            loadUsers(type, keyword);
                        }, 300);
                    });

                    // Auto-notes generator for NEW payments only
                    window.generateAutoNotes = function () {
                      // function generateAutoNotes() {
                        const isNew = ($("#payment_id").val().trim() === ""); // new payment only
                        if (!isNew) return; // prevent overwriting when editing
                        const type       = $("#payment_type").val();
                        const amount     = parseFloat($("#payment_amount").val() || 0);
                        const category   = $("#payment_category").val();
                        const method     = $("#payment_payment_method").val();
                        const status     = $("#payment_status").val();
                        if (!amount || !category) return;
                        let text = "";
                        // Income → Received
                        const meth = method!=''&&method!=null?' using '+method:'';
                        if (type === "Income") {
                            if (status === "Paid")
                                text = `Received AED ${amount} for ${category}`+meth+`.`;
                            else if (status === "Partial Paid")
                                text = `Partially received <?=$currency_symbol?>${amount} for ${category}`+meth+`.`;
                            else
                                text = `Pending receipt of <?=$currency_symbol?>${amount} for ${category}.`;
                        }
                        // Expense → Paid
                        if (type === "Expense") {
                            if (status === "Paid")
                                text = `Paid AED ${amount} for ${category}`+meth+`.`;
                            else if (status === "Partial Paid")
                                text = `Partially paid <?=$currency_symbol?>${amount} for ${category}`+meth+`.`;
                            else
                                text = `Pending payment of <?=$currency_symbol?>${amount} for ${category}.`;
                        }
                        $("#notesp").val(text);
                    }
                    // Trigger auto generation when any of these change:
                    $("#payment_type, #payment_amount, #payment_category, #payment_payment_method, #payment_status")
                    .on("change keyup", generateAutoNotes);

                    $("#payment_category").on("change", function () {
                        // Only for employees
                        if ($("#payment_user_type").val() !== "employee") {
                            $("#reimbursable_section").addClass("d-none");
                            return;
                        }
                        const cat = $(this).val();
                        if (!cat) return;

                        $.post("public/ajax/employees_payment_categories.php",
                            { action: "check_reimbursable", category: cat },
                            function(res) {
                                if (res.success && res.reimbursable == 1) {
                                    $("#reimbursable_section").removeClass("d-none");
                                    $("#reimbursable").val("Yes");
                                } else {
                                    $("#reimbursable_section").addClass("d-none");
                                    $("#reimbursable").val("No");
                                }
                                $("#reimbursable").attr("name", "reimbursable").trigger("change");
                                refreshJSelect("reimbursable");
                            },
                        "json");
                    });



                    $("#payment_user_type").trigger("change");


                    // function checkReimbursable() {
                    //   const type = $("#payment_type").val();
                    //   const status = $("#payment_status").val();
                    //   const amount = $("#payment_amount").val();

                    //   if (type === "Expense" && (status === "Paid" || status === "Partial Paid")) {
                    //     $("#reimbursable_section").removeClass("d-none");
                    //   } else {
                    //     $("#reimbursable_section").addClass("d-none");
                    //     $("#reimbursement_amount_field").addClass("d-none");
                    //   }

                    //   if(status === "Paid" || status === "Partial Paid") {
                    //     $("#payment_payment_methodd").removeClass("d-none");
                    //   }
                    //   else {
                    //     $("#payment_payment_methodd").addClass("d-none");
                    //     // $("#card_last4_field").addClass("d-none");
                    //   }

                    // }
                    // $("#payment_type, #payment_status").on("change", checkReimbursable);
                    $("#payment_type, #payment_status").on("change", function () {
                        // Only for employees
                        if ($("#payment_user_type").val() !== "employee") {
                            $("#reimbursable_section").addClass("d-none");
                            return;
                        }
                        const cat = $("#payment_category").val();
                        if (!cat) return;

                        $.post("public/ajax/employees_payment_categories.php",
                            { action: "check_reimbursable", category: cat },
                            function(res) {
                                if (res.success && res.reimbursable == 1) {
                                    $("#reimbursable_section").removeClass("d-none");
                                    $("#reimbursable").val("Yes");
                                } else {
                                    $("#reimbursable_section").addClass("d-none");
                                    $("#reimbursable").val("No");
                                }
                                $("#reimbursable").attr("name", "reimbursable").trigger("change");
                                refreshJSelect("reimbursable");
                            },
                        "json");
                    });

                    $("#reimbursable").on("change", function() {
                      if ($(this).val() === "Yes") {
                        $("#reimbursement_amount_field").removeClass("d-none");
                        $("#reimbursement_amount").val($("#payment_amount").val());
                      } else {
                        $("#reimbursement_amount_field").addClass("d-none");
                      }
                    });

                    $("#payment_amount").on("keyup change", function() {
                      if ($("#reimbursement_amount_field").is(":visible")) {
                        $("#reimbursement_amount").val($(this).val());
                      }
                    });

                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function(el){ return new bootstrap.Tooltip(el); });


                    // store existing documents when editing (array of {name, url, type})
                    var existingDocs = [];

                    // helper: render preview for selected FileList
                    function renderSelectedPreviews(files) {
                      const $preview = $("#documentPreviewp").empty();
                      if (!files || files.length === 0) return;

                      Array.from(files).forEach((file, index) => {
                        const fileName = file.name;
                        const fileType = file.type || '';
                        const $item = $('<div class="border rounded p-2 bg-white small text-truncate" style="width:160px;"></div>');

                        let fileHTML = '';
                        if (fileType.startsWith('image/')) {
                          const reader = new FileReader();
                          reader.onload = function(e) {
                            $item.html(`
                              <img src="${e.target.result}" style="max-width:120px; max-height:90px; display:block; margin-bottom:6px; object-fit:contain;">
                              <input type="text" name="document_name[]" class="form-control form-control-sm mt-1" placeholder="Document Name" data-index="${index}">
                              <div class="small text-muted text-truncate">${fileName}</div>
                            `);
                          };
                          reader.readAsDataURL(file);
                        } else {
                          fileHTML = `
                            <div class="d-flex align-items-center gap-2" style="height:90px; justify-content:center; flex-direction:column;">
                              <i class="fa fa-file-pdf fa-2x text-secondary"></i>
                              <div class="text-truncate">${fileName}</div>
                            </div>
                            <input type="text" name="document_name[]" class="form-control form-control-sm mt-1" placeholder="Document Name" data-index="${index}">
                          `;
                          $item.html(fileHTML);
                        }

                        $preview.append($item);
                      });
                    }

                    // helper: render existing docs (when editing). existingDocs should be array of {name,url,type,id(optional)}
                    function renderExistingDocs() {
                        const $box = $("#existingDocuments").empty();
                        if (!existingDocs || !existingDocs.length) return;
                        existingDocs.forEach(doc => {
                          // doc.url expected to be full path to file, doc.name is filename, doc.type optional (image/pdf)
                          const $el = $(`
                            <div class="d-flex align-items-center gap-2 bg-white border rounded p-2 mb-1" style="max-width:100%;">
                              <div class="me-2" style="width:56px; flex:0 0 56px;">
                                ${ (doc.type && doc.type.startsWith('image')) ? `<img src="${doc.url}" style="max-width:56px; max-height:40px; object-fit:contain;">`
                                   : `<i class="fa fa-file fa-2x text-muted"></i>` }
                              </div>
                              <div class="flex-grow-1 small text-truncate">${doc.name || doc.file}</div>
                              <div class="btn-group btn-group-sm ms-2">
                                <button data-file="${doc.url}" data-type="${ (doc.type && doc.type.startsWith('image'))?'image':'pdf'}" data-label="Payment" class="btn btn-light border view-document"><i class="fa fa-eye"></i></button>
                                <button type="button" class="btn btn-light border text-danger remove-existing-doc" data-name="${doc.name}"><i class="fa fa-trash"></i></button>
                              </div>
                            </div>
                          `);
                          $box.append($el);
                        });

                        $(".remove-existing-doc").on("click", function () {
                          const fileToRemove = $(this).data("name"); // should be actual file name
                          existingDocs = existingDocs.filter(d => d.file !== fileToRemove && d.name !== fileToRemove);
                          renderExistingDocs();
                        });

                        // $('.view-document').on('click',viewDoc);
                        $(document).on("click", ".view-document", window.viewDoc);
                    }

                    // when selecting new files, show previews
                    $("#documentp").on("change", function() {
                        const files = this.files;
                        renderSelectedPreviews(files);
                    });

                    $(".remove-existing-doc").on("click", function () {
                      const fileToRemove = $(this).data("name"); // should be actual file name
                      existingDocs = existingDocs.filter(d => d.file !== fileToRemove && d.name !== fileToRemove);
                      renderExistingDocs();
                    });

                    $("#payment_payment_method").on("change", function() {
                      const v = $(this).val();
                      $("#card_last4_field").addClass("d-none");
                      $("#cheque_fields").addClass("d-none");
                      $("#bank_fields").addClass("d-none");
                      if (v === "Card") {
                        $("#card_last4_field").removeClass("d-none");
                      }
                      if (v === "Cheque") {
                        $("#cheque_fields").removeClass("d-none");
                        $("#bank_fields").removeClass("d-none");
                      }
                      if (v === "Bank Transfer") {
                        $("#bank_fields").removeClass("d-none");
                      }
                    });

                    /* ---------------------------
                       EDIT PAYMENT HANDLER
                    ---------------------------- */
                    $(document).on("click", ".editPayment", function () {
                        let id  = $(this).data("id");
                        let src = $(this).data("src"); // employee / recruiter / customer

                        $.post("public/ajax/payments_actions.php", { action: "get", id: id, user_type: src }, function (res) {
                            if (!res.success) { alert("Payment not found"); return; }

                            openEditPaymentModal(res.data,src);  // you already have this function
                        }, "json");
                    });

                    function openEditPaymentModal(row,utype) {
                        $("#paymentForm")[0].reset();
                        $("#paymentModalTitle").text("Edit Payment");
                        $("#documentPreviewp").empty();
                        $("#existingDocuments").empty();

                        $("#payment_id").val(row.pid);
                        // $("#payment_user_type").val(utype).trigger("change");
                        $("#payment_user_type").val(utype);
                        $("#payment_user").val(row.user_id);
                        $("#payment_user").removeAttr("required");


                        $("#payment_date").val(row.date).trigger("date");
                        const today    = "<?=date('Y-m-d',strtotime($date))?>";
                        const tomorrow = "<?=date('Y-m-d',strtotime('+1 day',strtotime($date)))?>";
                        const saved    = row.date;
                        // jSelect wrapper + custom input
                        const wrapper      = $("#payment_date").closest(".jselect-wrapper");
                        const customInput  = wrapper.find(".jselect-custom");
                        // RESET UI
                        customInput.addClass("d-none").val("");
                        wrapper.find(".jselect-btn").removeClass("active");
                        // CASE: Today
                        if (saved === today) {
                            $("#payment_date").val(today);
                            $("#payment_date").attr("name", "payment_date").trigger("change");
                            refreshJSelect("payment_date");
                        }
                        // CASE: Tomorrow
                        else if (saved === tomorrow) {
                            $("#payment_date").val(tomorrow);
                            $("#payment_date").attr("name", "payment_date").trigger("change");
                            refreshJSelect("payment_date");
                        }
                        // CASE: OTHER DATE (most important)
                        else {
                            // 1️⃣ Set hidden real date into select
                            $("#payment_date").val(saved).trigger("change");
                            // 2️⃣ Activate OTHER option
                            $("#payment_date option[data-other='true']").prop("selected", true);
                            $("#payment_date").attr("name", "payment_date");
                            refreshJSelect("payment_date");
                            // 3️⃣ After refresh, jSelect re-created, so re-grab elements
                            const newWrapper     = $("#payment_date").closest(".jselect-wrapper");
                            const newCustomInput = newWrapper.find(".jselect-custom");
                            // 4️⃣ Show displayed date input & fill value
                            newCustomInput.removeClass("d-none").val(saved);
                            // $("#payment_date").val(saved);
                        }


                        // 🔥 MUST DO THIS FIRST
                        $("#payment_user_type").val(utype);
                        $("#payment_user_type").attr("name", "payment_user_type");
                        refreshJSelect("payment_user_type");

                        // 🔥 Set payment type next (because categories depend on this)
                        $("#payment_type").val(row.type);
                        $("#payment_type").attr("name", "payment_type");
                        refreshJSelect("payment_type");

                        // 🔥 Load correct categories file (employee OR invoice)
                        window.loadPaymentCategories(row.type, row.category);

                        // 🔥 If employee → check reimbursable
                        if (utype === "employee") {
                            $.post("public/ajax/employees_payment_categories.php",
                                { action: "check_reimbursable", category: row.category },
                                function(res) {
                                    if (res.success && res.reimbursable == 1) {
                                        $("#reimbursable_section").removeClass("d-none");
                                    } else {
                                        $("#reimbursable_section").addClass("d-none");
                                        $("#reimbursable").val("No");
                                    }
                                    $("#reimbursable").attr("name", "reimbursable");
                                    refreshJSelect("reimbursable");
                                },
                            "json");
                        }

                        $("#payment_amount").val(row.amount);
                        $("#payment_category").val(row.category).trigger("change");
                        // $("#payment_type").val(row.type).trigger("change");
                        $("#payment_type").val(row.type).trigger("change", row.category);
                        
                        $("#payment_status").val(row.status).trigger("change");
                        $("#payment_status").attr("name", "payment_status").trigger("change");
                        refreshJSelect("payment_status");
                        
                        $("#payment_partial").val(row.invoice_partial);
                        $("#payment_payment_method").val(row.invoice_payment_method).trigger("change");
                        $("#reclaim_by").val(row.reclaim_by);
                        $("#card_last4").val(row.card_last4);
                        $("#cheque_bank").val(row.cheque_bank);
                        $("#cheque_issuer").val(row.cheque_issuer);

                        $("#reimbursable").val(row.reimbursable).trigger("change");
                        $("#reimbursement_amount").val(row.reimbursement_amount);
                        $("#notesp").val(row.notes);

                        existingDocs = row.documents || [];
                        renderExistingDocs(existingDocs);

                        $(".addonly").hide();

                        // load correct user type + user ID
                        // $("#payment_user_type").val(utype).trigger("change");
                        
                        $("#payment_user").val(row.user_id);

                        // setTimeout(() => {
                        //     $("#payment_user").val(row.user_id);
                        //     $("#payment_user").attr("name", "payment_user");
                        //     refreshJSelect("payment_user");
                        // }, 400);

                        new bootstrap.Modal($("#paymentModal")).show();
                    }

                    /* ---------------------------
                       ADD PAYMENT BUTTON
                    ---------------------------- */
                    $("#addPaymentBtn").on("click", function () {
                        // open your existing modal
                        $("#paymentModal").modal("show");
                        $(".addonly").show();

                        $("#paymentModalTitle").text("Add Payment");

                        $("#paymentForm")[0].reset();

                        
                        $("#payment_status").val('Unpaid').trigger("change");
                        $("#payment_status").attr("name", "payment_status");
                        refreshJSelect("payment_status");

                        $("#payment_id").val('');
                        existingDocs = [];
                        $("#documentPreviewp").empty();
                        $("#existingDocuments").empty();
                        $("#payment_date").val('<?=date("Y-m-d",strtotime($date))?>').trigger('change');

                        window.loadPaymentCategories($("#payment_type").val());

                    });

                    $("#paymentForm").on("submit", function (e) {
                        e.preventDefault();

                        let formData = new FormData(this);

                        formData.append("action", "save");
                        formData.append("user_type", $("#payment_user_type").val());
                        formData.append("user_id", $("#payment_user").val());

                        // 🟢 HANDLE "OTHER" DATE OPTION
                        let paymentDate = $("#payment_date").val();
                        if (paymentDate === "Other") {
                            const wrapper     = $("#payment_date").closest(".jselect-wrapper");
                            const customInput = wrapper.find(".jselect-custom");
                            let otherDate = customInput.val().trim();
                            if (otherDate === "") {
                                alert("Please select a date.");
                                return;
                            }
                            // Replace the date value in formData
                            formData.append("payment_date", otherDate);
                        } else {
                            // Use the selected date
                            formData.append("payment_date", paymentDate);
                        }

                        // Add document label names
                        let names = [];
                        $('input[name="document_name[]"]').each(function () {
                            names.push($(this).val().trim());
                        });
                        formData.append("document_names", JSON.stringify(names));

                        // Existing docs (for edit)
                        formData.append("existing_documents", JSON.stringify(existingDocs));

                        $.ajax({
                            url: "public/ajax/payments_actions.php",              // your PHP handler URL
                            type: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: "json",
                            success: function (res) {
                                if (res.success) {
                                    bootstrap.Modal.getInstance($("#paymentModal")[0]).hide();
                                    table.ajax.reload();
                                } else {
                                    alert(res.error || "Failed to save payment");
                                }
                            },
                            error: function () {
                                alert("Error saving payment");
                            }
                        });
                    });

                    $(document).on("click", ".deletePayment", function () {
                        let id  = $(this).data("id");
                        let src = $(this).data("src");

                        if (!confirm("Are you sure you want to delete this payment?")) {
                            return;
                        }

                        $.post("public/ajax/payments_actions.php", 
                            { action: "delete", id: id, user_type: src }, 
                            function (res) {
                                if (res.success) {
                                    table.ajax.reload();
                                } else {
                                    alert(res.error || "Failed to delete payment.");
                                }
                            }, 
                        "json");
                    });


                    loadBankNames();

                  });

                    function loadBankNames() {
                        $.post("public/ajax/bank_accounts.php", { action: "list" }, function (res) {
                            if (!res.success) return;

                            const $bank = $("#cheque_bank");
                            $bank.empty();

                            res.data.forEach(b => {
                                $bank.append(`<option value="${b.bank_name}">${b.bank_name}</option>`);
                            });

                            $("#cheque_bank").attr("name", "cheque_bank");
                            refreshJSelect("cheque_bank");
                        }, "json");
                    }
                  </script>


<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

<!-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> -->
<!-- <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> -->

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
window.viewDoc = function(e) {
    e.preventDefault();
    // The clicked button
    const $btn   = $(this);
    const file  = $btn.data("file")  || "";
    const type  = $btn.data("type")  || "";
    const label = $btn.data("label") || "Document";

  $('#documentModalLabel').text(label);
  const viewer = $('#documentViewer');

  if (!file) {
    viewer.html('<div class="text-center text-muted small py-5">File not found.</div>');
    $('#documentModal').modal('show');
    return;
  }

  // Choose rendering mode
  let html = '';
  if (type === 'pdf') {
    html = `
      <iframe src="${file}" frameborder="0" width="100%" height="100%" 
              style="border:none; background:#fff; height: 70vh;"></iframe>`;
    // html = `
    //     <iframe src="https://docs.google.com/gview?url=${file}&embedded=true" 
    //       frameborder="0" width="100%" height="100%" 
    //       style="border:none; background:#fff;"></iframe>`;

  } else {
    html = `
      <img src="${file}" alt="Document Preview" class="img-fluid rounded shadow-sm"
           style="max-height: 80vh; object-fit: contain;">`;
  }

  //hide other modals
  $('#viewPaymentModal').modal('hide');
  $('#paymentModal').modal('hide');

  viewer.html(html);
  $('#documentModal').modal('show');
}
$(function () {

    /* ---------------------------
       INIT DATE RANGE PICKER
    ---------------------------- */
    $('#daterange').daterangepicker({
        locale: {
            format: 'DD-MM-YYYY',
            separator: ' - ' // define the separator you prefer
        },
        startDate: "<?=date("d-m-Y",strtotime($date." - 6 days"))?>",
        endDate: "<?=date("d-m-Y",strtotime($date))?>",
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
       INIT DATATABLE
    ---------------------------- */
    window.table = $("#paymentsTable").DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ordering: true,
        order: [[6, "desc"]],
        ajax: {
            url: "public/ajax/payments.php",
            type: "POST",
            data: function (d) {
                d.daterange = $("#daterange").val();
                d.filterType = window.currentType ?? "all"; // employees / recruiters / customers
            },
            dataSrc: function (json) {
              // ⬅️ json.summary MUST COME FROM SERVER
              if (json.summary) {
                  let totalIncome   = parseFloat(json.summary.totalIncome   || 0);
                  let totalExpense  = parseFloat(json.summary.totalExpense  || 0);
                  let paidAmount    = parseFloat(json.summary.paidAmount    || 0);
                  let unpaidAmount  = parseFloat(json.summary.unpaidAmount  || 0);
                  let partialAmount = parseFloat(json.summary.partialAmount || 0);

                  // ================================
                  // 🔥 UPDATE PAYMENT SUMMARY BOX
                  // ================================
                  $("#payment-summary").html(`
                      <div class="row g-2 mt-2">
                          <div class="col-6 col-md-3">
                              <div class="rounded bg-primary-subtle border border-primary text-center p-2">
                                  <div class="fw-bold small text-primary">Total Income</div>
                                  <div class="fw-bold text-primary fs-6">
                                      <?=$currency_symbol?>${totalIncome.toFixed(2)}
                                  </div>
                              </div>
                          </div>
                          <div class="col-6 col-md-3">
                              <div class="rounded bg-warning-subtle border border-warning text-center p-2">
                                  <div class="fw-bold small text-warning">Total Expense</div>
                                  <div class="fw-bold text-warning fs-6">
                                      <?=$currency_symbol?>${totalExpense.toFixed(2)}
                                  </div>
                              </div>
                          </div>
                          <div class="col-4 col-md-2">
                              <div class="rounded bg-success-subtle border border-success text-center p-2">
                                  <div class="fw-bold small text-success">Paid</div>
                                  <div class="fw-bold text-success fs-6">
                                      <?=$currency_symbol?>${paidAmount.toFixed(2)}
                                  </div>
                              </div>
                          </div>
                          <div class="col-4 col-md-2">
                              <div class="rounded bg-secondary-subtle border border-secondary text-center p-2">
                                  <div class="fw-bold small text-secondary">Unpaid</div>
                                  <div class="fw-bold text-secondary fs-6">
                                      <?=$currency_symbol?>${unpaidAmount.toFixed(2)}
                                  </div>
                              </div>
                          </div>
                          <div class="col-4 col-md-2">
                              <div class="rounded bg-info-subtle border border-info text-center p-2">
                                  <div class="fw-bold small text-info">Partial</div>
                                  <div class="fw-bold text-info fs-6">
                                      <?=$currency_symbol?>${partialAmount.toFixed(2)}
                                  </div>
                              </div>
                          </div>
                      </div>
                  `);
              }
              return json.data; // Return table rows
            }

        },
        columns: [
            { data: "id" },
            { data: "source_typed" },  // employees / recruiter / customer
            { data: "name" },         // employee name / recruiter / customer
            { data: "category" },
            { data: "amount" },
            { data: "status" },
            { data: "date" },
            { data: "notes" },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (row) {
                    return `
                        <button class="btn btn-sm btn-outline-primary viewPayment"
                                data-id="${row.pid}" data-src="${row.source_type}">
                            <i class="fa fa-eye"></i>
                        </button>

                        <button class="btn btn-sm btn-outline-success editPayment"
                                data-id="${row.pid}" data-src="${row.source_type}">
                            <i class="fa fa-edit"></i>
                        </button>

                        <button class="btn btn-sm btn-outline-danger deletePayment"
                                data-id="${row.pid}" data-src="${row.source_type}">
                            <i class="fa fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    /* ---------------------------
       FILTER BY DATE
    ---------------------------- */
    $('#daterange').on("apply.daterangepicker", function () {
        table.ajax.reload();
    });

    /* ---------------------------
       FILTER BY TYPE BUTTONS
    ---------------------------- */
    $(".filter-btn").on("click", function () {
        $(".filter-btn").removeClass("active");
        $(this).addClass("active");

        window.currentType = $(this).data("type"); // employees / recruiters / customers
        table.ajax.reload();
    });


    $(document).on("click", ".viewPayment", function () {
        let id  = $(this).data("id");
        let src = $(this).data("src");

        $("#viewPaymentBody").html("Loading...");
        let modal = new bootstrap.Modal("#viewPaymentModal");
        modal.show();

        $.post("public/ajax/payments_actions.php", { action: "get", id: id, user_type: src }, function (res) {
            if (!res.success) {
                $("#viewPaymentBody").html("<div class='text-danger'>Error loading payment.</div>");
                return;
            }

            let p = res.data;

            let docsHTML = "";

            if (p.documents && p.documents.length) {
                p.documents.forEach(doc => {
                    docsHTML += `
                        <button type="button" data-file="${doc.url}" data-type="${ (doc.type && doc.type.startsWith('image'))?'image':'pdf'}" data-label="${doc.name || doc.file}" class="btn btn-sm btn-outline-primary mb-1 me-2 view-document">
                            ${doc.name || doc.file}
                        </button>`;
                });
            } else {
                docsHTML = "<span class='text-muted'>No documents</span>";
            }

            let methodRow = "";
            let partialRow = "";
            let reimbursableRow = "";
            let cardRow = "";
            let chequeRows = "";

            // Payment Method
            if (p.invoice_payment_method) {
                methodRow = `<tr><th>Payment Method</th><td>${p.invoice_payment_method}</td></tr>`;
            }

            // Partial Paid
            if (p.status === "Partial Paid" && p.invoice_partial > 0) {
                partialRow = `
                    <tr>
                        <th>Partial Amount</th>
                        <td>${p.invoice_partial}</td>
                    </tr>
                `;
            }

            // Card Payment
            if (p.invoice_payment_method === "Card" && p.card_last4) {
                cardRow = `
                    <tr>
                        <th>Card Last 4 Digits</th>
                        <td>${p.card_last4}</td>
                    </tr>
                `;
            }

            // Cheque Payment
            if (p.invoice_payment_method === "Cheque") {
                chequeRows = `
                    <tr>
                        <th>Cheque Bank</th>
                        <td>${p.cheque_bank || '-'}</td>
                    </tr>
                    <tr>
                        <th>Cheque Issuer</th>
                        <td>${p.cheque_issuer || '-'}</td>
                    </tr>
                `;
            }

            // Reimbursable
            if (p.reimbursable === "Yes") {
                reimbursableRow = `
                    <tr>
                        <th>Reimbursable</th>
                        <td>Yes (Amount: ${p.reimbursement_amount})</td>
                    </tr>
                `;
            } else if (p.reimbursable === "No") {
                reimbursableRow = `
                    <tr>
                        <th>Reimbursable</th>
                        <td>No</td>
                    </tr>
                `;
            }

            // Build Final HTML
            $("#viewPaymentBody").html(`
                <table class="table table-bordered">
                    <tr><th>ID</th><td>${p.id}</td></tr>
                    <tr><th>Source</th><td>${p.source_type}</td></tr>
                    <tr><th>User</th><td>${p.name}</td></tr>

                    <tr><th>Category</th><td>${p.category}</td></tr>
                    <tr><th>Amount</th><td>${p.amount}</td></tr>
                    <tr><th>Status</th><td>${p.status}</td></tr>
                    <tr><th>Date</th><td>${p.date}</td></tr>

                    ${partialRow}
                    ${methodRow}
                    ${cardRow}
                    ${chequeRows}
                    ${reimbursableRow}

                    <tr><th>Notes</th><td>${p.notes ?? ''}</td></tr>

                    <tr><th>Documents</th><td>${docsHTML}</td></tr>
                </table>
            `);

            // $('.view-document').on('click',viewDoc());
            $(document).on("click", ".view-document", window.viewDoc);

        }, "json");
    });
    
    // $('.view-document').on('click',viewDoc());
    $(document).on("click", ".view-document", window.viewDoc);


});
</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>