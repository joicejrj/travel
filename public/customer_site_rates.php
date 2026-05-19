<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

?>
<div class="container-fluid py-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-semibold mb-0">
      <i class="fa fa-money-bill-wave text-success me-2"></i>
      Customer Site Rates
    </h4>

    <input type="text"
           id="rateSearch"
           class="form-control form-control-sm"
           placeholder="Search Customer / Site / Trade"
           style="max-width:260px;">
  </div>

  <div id="ratesMasterWrap">
    <div class="text-muted small">Loading rates...</div>
  </div>

</div>

<div class="modal fade" id="ratesEditModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title fw-semibold">
          <i class="fa fa-pen me-1"></i>
          Edit Customer Rates —
          <span id="ratesCustomerName" class="text-primary fw-bold"></span>
        </h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-0">
        

        <!-- 🔥 Reuse your existing Rates Section here -->
        <style>
          #rateTrades .trade-btn {
            border:1px solid #d1d5db;
            padding:5px 12px;
            border-radius:20px;
            font-size:0.8rem;
            cursor:pointer;
            background:#fff;
          }
          #rateTrades .trade-btn.active {
            background:#2563eb;
            color:#fff;
            border-color:#2563eb;
          }
          #rateSites .site-btn {
            border:1px solid #d1d5db;
            padding:6px 14px;
            border-radius:20px;
            font-size:0.8rem;
            cursor:pointer;
            background:#fff;
          }
          #rateSites .site-btn.active {
            background:#0d6efd;
            color:#fff;
            border-color:#0d6efd;
          }
          #addTradeBtn {
            border-style:dashed;
          }
        </style>

        <div class="card border-0 shadow-sm">

          <div class="card-body">

            <!-- SITE SELECT -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Site</label>
              <div id="rateSites" class="d-flex flex-wrap gap-2">
                <span class="text-muted small">Loading sites…</span>
              </div>
            </div>

            <!-- EDIT MODE ONLY -->
            <div id="ratesEdit">

              <!-- TRADES -->
              <div class="mb-3">
                <label class="form-label fw-semibold">Trades</label>
                <div id="rateTrades" class="d-flex flex-wrap gap-2"></div>

                <div id="addTradeBox" class="mt-2 d-none">
                  <div class="input-group input-group-sm" style="max-width:300px;">
                    <input type="text"
                           id="newTradeName"
                           class="form-control"
                           placeholder="New trade name">
                    <button class="btn btn-primary" id="saveNewTradeBtn">Add</button>
                  </div>
                </div>

                <button type="button"
                        id="addTradeBtn"
                        class="btn btn-outline-secondary btn-sm mt-2">
                  <i class="fa fa-plus me-1"></i> Add Trade
                </button>
              </div>

              <!-- RATES -->
              <div class="row g-3 mb-4">

                <!-- NORMAL RATE -->
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Normal Rate</label>
                  <input type="number"
                         step="0.01"
                         id="rateHour"
                         class="form-control form-control-sm mb-1">

                  <div class="form-check small">
                    <input class="form-check-input" type="checkbox" id="is_fixed_rate">
                    <label class="form-check-label fw-semibold ms-1" for="is_fixed_rate">
                      Fixed Rate / Month
                    </label>
                  </div>
                </div>

                <!-- OVERTIME -->
                <div class="col-md-2 d-flex align-items-end">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="allowOvertime" checked>
                    <label class="form-check-label fw-semibold ms-1" for="allowOvertime">
                      Allow Overtime
                    </label>
                  </div>
                </div>

                <!-- WORKING HOURS -->
                <div class="col-md-2 overtime-fields">
                  <label class="form-label fw-semibold">Working Hours / Day</label>
                  <input type="number"
                         step="0.01"
                         id="default_hours"
                         class="form-control form-control-sm">
                </div>

                <!-- NOT -->
                <div class="col-md-2 overtime-fields">
                  <label class="form-label fw-semibold">NOT Rate</label>
                  <input type="number"
                         step="0.01"
                         id="rateNOT"
                         class="form-control form-control-sm">
                </div>

                <!-- HOT -->
                <div class="col-md-2 overtime-fields">
                  <label class="form-label fw-semibold">HOT Rate</label>
                  <input type="number"
                         step="0.01"
                         id="rateHOT"
                         class="form-control form-control-sm">
                </div>

                <!-- PHOT -->
                <div class="col-md-2 overtime-fields">
                  <label class="form-label fw-semibold">PHOT Rate</label>
                  <input type="number"
                         step="0.01"
                         id="ratePHOT"
                         class="form-control form-control-sm">
                </div>

              </div>

              <!-- ALLOWANCES (SEPARATE CLEAN BLOCK) -->
              <div class="border rounded p-3 mb-3 bg-light">

                <div class="fw-semibold mb-2">
                  <i class="fa fa-wallet me-1 text-success"></i>
                  Allowances
                </div>

                <div class="row g-3">

                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="allowFood">
                      <label class="form-check-label fw-semibold" for="allowFood">
                        Food Allowance
                      </label>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="allowTravel">
                      <label class="form-check-label fw-semibold" for="allowTravel">
                        Travel Allowance
                      </label>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="allowAccommodation">
                      <label class="form-check-label fw-semibold" for="allowAccommodation">
                        Accommodation Allowance
                      </label>
                    </div>
                  </div>

                </div>
              </div>


              <!-- ACTIONS -->
              <div class="d-flex justify-content-end gap-2">
                <button id="saveRatesBtn" class="btn btn-success btn-sm">
                  <i class="fa fa-save me-1"></i> Apply Rates
                </button>
              </div>

            </div>


          </div>
        </div>
        <script>
          let siteRates = {};
          let allTrades = [];
          let selectedSiteId = null;

          function getCustomerId() {
            return window.CUSTOMER_ID || 0;
          }

          /* Overtime toggle */
          function toggleOvertimeFields() {
            const allowed = $('#allowOvertime').is(':checked');
            $('.overtime-fields').toggleClass('d-none', !allowed);
            if (!allowed) {
              $('#rateNOT,#rateHOT,#ratePHOT').val('');
              $('#default_hours').val(8);
            }
          }
          $(document).on('change', '#allowOvertime', toggleOvertimeFields);

          /* Add trade */
          $(document).on('click', '#addTradeBtn', () => {
            $('#addTradeBox').removeClass('d-none');
            $('#newTradeName').focus();
          });

          $(document).on('click', '#saveNewTradeBtn', () => {
            const name = $('#newTradeName').val().trim();
            if (!name) return alert('Enter trade name');

            $.post('public/ajax/customers_rates.php', {
              action: 'add_trade',
              trade_name: name
            }, res => {
              if (!res.success) return alert(res.error || 'Failed');

              allTrades.push(res.trade);
              siteRates[res.trade.id] = {};

              $('#rateTrades').append(`
                <button type="button"
                  class="trade-btn active"
                  data-id="${res.trade.id}">
                  ${res.trade.trade_name}
                </button>
              `);

              $('#newTradeName').val('');
              $('#addTradeBox').addClass('d-none');
              bindTradeClicks();
              updateRateInputs();
            });
          });

          /* Load sites */
          function loadRateSites() {
            $.post('public/ajax/customers_sites.php', {
              action: 'fetch',
              customer_id: getCustomerId()
            }, res => {
              const $wrap = $('#rateSites').empty();
              if (!res || !res.length) return;

              selectedSiteId = res[0].id;

              res.forEach((s, i) => {
                $wrap.append(`
                  <button type="button"
                    class="site-btn ${i===0?'active':''}"
                    data-id="${s.id}">
                    ${s.site_name}
                  </button>
                `);
              });

              loadRatesForSite(selectedSiteId);

              $('.site-btn').on('click', function () {
                $('.site-btn').removeClass('active');
                $(this).addClass('active');
                selectedSiteId = $(this).data('id');
                loadRatesForSite(selectedSiteId);
              });
            });
          }

          /* Load rates */
          function loadRatesForSite(siteId) {
            siteRates = {};
            $('#rateTrades').empty();
            $('#rateHour,#rateNOT,#rateHOT,#ratePHOT,#default_hours').val('');
            $('#allowOvertime').prop('checked', true);
            $('#is_fixed_rate').prop('checked', false);
            toggleOvertimeFields();

            $.post('public/ajax/customers_rates.php', {
              action: 'fetch_rates',
              customer_id: getCustomerId(),
              site_id: siteId
            }, rates => {
              siteRates = rates || {};

              $.post('public/ajax/customers_rates.php', {
                action: 'fetch_all_trades'
              }, trades => {
                allTrades = trades || [];

                allTrades.forEach(t => {
                  $('#rateTrades').append(`
                    <button type="button"
                      class="trade-btn ${siteRates[t.id]?'active':''}"
                      data-id="${t.id}">
                      ${t.trade_name}
                    </button>
                  `);
                });

                bindTradeClicks();
                updateRateInputs();
              });
            });
          }

          function bindTradeClicks() {
            $('.trade-btn').off().on('click', function () {
              $(this).toggleClass('active');
              updateRateInputs();
            });
          }

          function updateRateInputs() {
            const ids = $('.trade-btn.active').map((_,b)=>$(b).data('id')).get();
            if (!ids.length) return;

            const vals = ids.map(id=>siteRates[id]).filter(Boolean);
            const same = k => vals.length && vals.every(v=>v[k]==vals[0][k]) ? vals[0][k] : '';

            $('#rateHour').val(same('rate_per_hour'));
            $('#is_fixed_rate').prop('checked', same('is_fixed_rate')==1);
            $('#rateNOT').val(same('not_rate'));
            $('#rateHOT').val(same('hot_rate'));
            $('#ratePHOT').val(same('phot_rate'));
            $('#default_hours').val(same('default_hours'));
            $('#allowFood').prop('checked', same('food_allowance') == 1);
            $('#allowTravel').prop('checked', same('travel_allowance') == 1);
            $('#allowAccommodation').prop('checked', same('accommodation_allowance') == 1);
          }

          /* Save */
          $(document).on('click', '#saveRatesBtn', () => {
            const trades = $('.trade-btn.active').map((_,b)=>$(b).data('id')).get();
            if (!trades.length) return alert('Select trades');

            $.post('public/ajax/customers_rates.php', {
              action: 'save',
              customer_id: getCustomerId(),
              site_id: selectedSiteId,
              trades,
              rate_hour: $('#rateHour').val(),
              not_rate: $('#rateNOT').val(),
              hot_rate: $('#rateHOT').val(),
              phot_rate: $('#ratePHOT').val(),
              default_hours: $('#default_hours').val(),
              allow_overtime: $('#allowOvertime').is(':checked')?1:0,
              is_fixed_rate: $('#is_fixed_rate').is(':checked')?1:0,
              food_allowance: $('#allowFood').is(':checked') ? 1 : 0,
              travel_allowance: $('#allowTravel').is(':checked') ? 1 : 0,
              accommodation_allowance: $('#allowAccommodation').is(':checked') ? 1 : 0
            , function () {
              $('#ratesEditModal').modal('hide');
            } });
          });

          /* Init */
          function initCustomerRatesEditor() {
            siteRates = {};
            allTrades = [];
            selectedSiteId = null;
            loadRateSites();
          }
          </script>

      </div>

    </div>
  </div>
</div>

<script>
let CURRENT_CUSTOMER_ID = null;

function openRatesModal(customerId, customerName) {
  if (!customerId) {
    alert('Invalid customer');
    return;
  }

  window.CUSTOMER_ID = customerId;

  // set customer name in modal
  $('#ratesCustomerName').text(customerName || '');

  $('#ratesEditModal').modal('show');

  $('#ratesEditModal').one('shown.bs.modal', function () {
    initCustomerRatesEditor();
  });
}

$('#ratesEditModal').on('hidden.bs.modal', function(){
  loadRatesMaster(); // refresh master after edit
  $('#ratesCustomerName').text('');
});
</script>
<script>
$('#rateSearch').on('keyup', function(){
  const q = $(this).val().toLowerCase();
  $('.rate-card').each(function(){
    $(this).toggle($(this).text().toLowerCase().includes(q));
  });
});
</script>
<script>
const RATE_API = 'public/ajax/customers_rates.php';
const SITE_API = 'public/ajax/customers_sites.php';

function loadRatesMaster() {

  $('#ratesMasterWrap').html('<div class="text-muted small">Loading...</div>');

  $.post(RATE_API, { action:'fetch_all_rates' }, function(res){

    if (!res || !res.length) {
      $('#ratesMasterWrap').html('<div class="text-muted">No data</div>');
      return;
    }

    let html = '';

    res.forEach(cust => {

      html += `
        <div class="card shadow-sm border-0 mb-3 rate-card">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div class="fw-semibold">
              <i class="fa fa-building me-1 text-primary"></i>
              ${cust.customer_name}
            </div>
            <button class="btn btn-outline-primary btn-sm"
                    onclick="openRatesModal(${cust.customer_id}, '${cust.customer_name.replace(/'/g, "\\'")}')">
              <i class="fa fa-pen me-1"></i> Edit Rates
            </button>
          </div>
          <div class="card-body p-0">
      `;

      cust.sites.forEach(site => {

        html += `
          <div class="p-3 border-top">
            <div class="site-header d-flex justify-content-between align-items-center mb-2"
                 style="cursor:pointer;">
              <div class="fw-semibold">
                <i class="fa fa-location-dot me-1 text-muted"></i>
                ${site.site_name}
              </div>
              <div class="text-muted1 toggle-text"><i class="fa fa-circle-chevron-down"></i></div>
            </div>
            <div class="table-responsive site-rates d-none">
              <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Trade</th>
                    <th>Rate</th>
                    <th>Type</th>
                    <th>OT</th>
                    <th>Hours</th>
                    <th>NOT</th>
                    <th>HOT</th>
                    <th>PHOT</th>
                  </tr>
                </thead>
                <tbody>
        `;

        site.trades.forEach(r => {
          html += `
            <tr>
              <td>
                <div class="fw-semibold">${r.trade_name}</div>

                <div class="mt-1 d-flex flex-wrap gap-1 small">
                  ${r.food_allowance == 1 
                    ? '<span class="badge bg-success-subtle text-success border">Food</span>' 
                    : ''}

                  ${r.travel_allowance == 1 
                    ? '<span class="badge bg-primary-subtle text-primary border">Travel</span>' 
                    : ''}

                  ${r.accommodation_allowance == 1 
                    ? '<span class="badge bg-warning-subtle text-warning border">Accommodation</span>' 
                    : ''}
                </div>
              </td>
              <td>${r.rate_per_hour}</td>
              <td>${r.is_fixed_rate==1 ? 'Monthly' : 'Hourly'}</td>
              <td>${r.allow_overtime==1 ? 'Yes' : 'No'}</td>
              <td>${r.default_hours ?? '-'}</td>
              <td>${r.not_rate ?? '-'}</td>
              <td>${r.hot_rate ?? '-'}</td>
              <td>${r.phot_rate ?? '-'}</td>
            </tr>
          `;
        });

        html += `
                </tbody>
              </table>
            </div>
          </div>
        `;
      });

      html += `</div></div>`;
    });

    $('#ratesMasterWrap').html(html);
  });
}

loadRatesMaster();
</script>
<script>
$(document).on('click', '.site-header', function () {

  const $header = $(this);
  const $site   = $header.closest('.p-3');   // site container
  const $rates  = $site.find('.site-rates');
  const $label  = $header.find('.toggle-text');

  const isHidden = $rates.hasClass('d-none');

  if (isHidden) {
    $rates.removeClass('d-none');
    $label.html('<i class="fa fa-circle-chevron-up"></i>');
  } else {
    $rates.addClass('d-none');
    $label.html('<i class="fa fa-circle-chevron-down"></i>');
  }
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>