<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';

$uid   = $_SESSION['person_id'] ?? 0;
$uname = $_SESSION['person_name'] ?? 'Admin';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>

<div class="container py-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold">Applicants</h4>

    <div class="d-flex gap-2 flex-wrap align-items-end">
      <div>
        <label class="small text-muted">Date Range</label>
        <input type="text" id="date_range"
               class="form-control form-control-sm"
               style="width:220px;">
      </div>
      <div>
        <label class="small text-muted">Search</label>
        <input type="text" id="flt_search"
               class="form-control form-control-sm"
               placeholder="Name / Mobile / Ref"
               style="width:200px;">
      </div>
      <div>
        <label class="small text-muted">Status</label>
        <select id="flt_status" class="form-control form-control-sm" style="width:160px;">
          <option value="">All Status</option>
          <option>NEW</option>
          <option>CV_RECEIVED</option>
          <option>UNDER_REVIEW</option>
          <option>SHORTLISTED</option>
          <option>INTERVIEW</option>
          <option>SELECTED</option>
          <option>OFFERED</option>
          <option>JOINED</option>
          <option>REJECTED</option>
        </select>
      </div>
      <div>
        <label class="small text-muted">Source</label>
        <select id="flt_source" class="form-control form-control-sm" style="width:150px;">
          <option value="">All Sources</option>
          <option>WhatsApp</option>
          <option>Email</option>
          <option>Phone</option>
          <option>Walk-in</option>
          <option>Website</option>
          <option>Other</option>
        </select>
      </div>
      <div>
        <button class="btn btn-outline-secondary btn-sm" id="flt_reset">
          Reset
        </button>
      </div>
    </div>


  </div>

  <div class="card">
    <div class="card-body p-2">
      <table id="applicants-table" class="display table table-sm w-100">
        <thead>
          <tr>
            <th>Ref</th>
            <th>Name</th>
            <th>Mobile</th>
            <th>Position</th>
            <th>Location</th>
            <th>Status</th>
            <th>Source</th>
            <th>Created</th>
            <th width="160">Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

</div>

<!-- Applicant View Modal -->
<div class="modal fade" id="applicantViewModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header py-2">
        <h6 class="modal-title fw-bold">
          Applicant Details – <span id="av_ref" class="text-muted"></span>
        </h6>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
      <div class="modal-body p-0">

        <!-- TAB NAV -->
        <ul class="nav nav-tabs px-3 pt-2" id="applicantTabs" role="tablist">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab_overview">
              Overview
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_documents">
              Documents
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_status">
              Status
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_timeline">
              Timeline
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_reminders">
              Reminders
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_quick">
              Quick Actions
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_interviews">
              Interviews
            </button>
          </li>
        </ul>

        <!-- TAB CONTENT -->
        <div class="tab-content p-3">

          <!-- OVERVIEW -->
          <div class="tab-pane fade show active" id="tab_overview">

            <!-- SUMMARY -->
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <div class="border rounded p-3">
                  <h6 class="fw-semibold mb-2">Personal</h6>
                  <div><b>Name:</b> <span id="av_name"></span></div>
                  <div><b>Mobile:</b> <span id="av_mobile"></span></div>
                  <div><b>Email:</b> <span id="av_email"></span></div>
                  <div><b>Nationality:</b> <span id="av_nationality"></span></div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="border rounded p-3">
                  <h6 class="fw-semibold mb-2">Job</h6>
                  <div><b>Position:</b> <span id="av_position"></span></div>
                  <div><b>Experience:</b> <span id="av_exp"></span></div>
                  <div><b>Expected Salary:</b> AED <span id="av_salary"></span></div>
                  <div><b>Status:</b> <span id="av_status" class="badge bg-info"></span></div>
                </div>
              </div>
            </div>

            <!-- LOCATION -->
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <div class="border rounded p-3">
                  <h6 class="fw-semibold mb-2">Location</h6>
                  <div><b>Current:</b> <span id="av_location"></span></div>
                  <div><b>Visa:</b> <span id="av_visa"></span></div>
                  <div><b>Availability:</b> <span id="av_availability"></span></div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="border rounded p-3">
                  <h6 class="fw-semibold mb-2">Source</h6>
                  <div><b>Lead Source:</b> <span id="av_source"></span></div>
                  <div><b>Created:</b> <span id="av_created"></span></div>
                </div>
              </div>
            </div>

          </div>

          <!-- DOCUMENTS -->
          <div class="tab-pane fade" id="tab_documents">
            <div class="border rounded p-3">
              <h6 class="fw-semibold mb-2">Documents</h6>
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Type</th>
                    <th>File</th>
                    <th>Size</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody id="av_docs">
                  <tr><td colspan="4" class="text-muted">Loading…</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_status">

            <!-- STATUS UPDATE -->
            <div class="border rounded p-3 mb-3">
              <h6 class="fw-semibold mb-2">Update Status</h6>

              <div class="row g-2">
                <div class="col-md-4">
                  <label class="small">Current Status</label>
                  <input id="av_current_status" class="form-control form-control-sm" disabled>
                </div>

                <div class="col-md-4">
                  <label class="small">New Status</label>
                  <select id="av_new_status" class="form-control form-control-sm">
                    <option value="">Select</option>
                    <option>NEW</option>
                    <option>CV_RECEIVED</option>
                    <option>UNDER_REVIEW</option>
                    <option>SHORTLISTED</option>
                    <option>INTERVIEW</option>
                    <option>SELECTED</option>
                    <option>OFFERED</option>
                    <option>JOINED</option>
                    <option>REJECTED</option>
                  </select>
                </div>

                <div class="col-md-4 d-grid">
                  <label class="small">&nbsp;</label>
                  <button class="btn btn-primary btn-sm" onclick="updateApplicantStatus()">
                    Update Status
                  </button>
                </div>
              </div>

              <input id="av_status_note" class="form-control form-control-sm mt-2"
                     placeholder="Optional note">
            </div>
            <!-- HISTORY -->
            <div class="border rounded p-3">
              <h6 class="fw-semibold mb-2">Status History</h6>
              <table class="table table-sm mb-0">
                <thead>
                  <tr>
                    <th>From</th><th>To</th><th>Note</th><th>By</th><th>Date</th>
                  </tr>
                </thead>
                <tbody id="av_status_history"></tbody>
              </table>
            </div>
          </div>

          <!-- ACTIVITY TIMELINE -->
          <div class="tab-pane fade" id="tab_timeline">
            <div id="av_timeline"
                 class="p-2 mb-2"
                 style="max-height:300px; overflow-y:auto; border:1px solid #eee; border-radius:8px;">
              <div class="text-muted text-center py-3">Loading…</div>
            </div>
            <textarea id="av_new_note"
                      class="form-control form-control-sm"
                      rows="2"
                      placeholder="Add internal note..."></textarea>
            <div class="text-end mt-2">
              <button class="btn btn-primary btn-sm" onclick="addApplicantNote()">
                Add Note
              </button>
            </div>
          </div>

          <!-- REMINDERS -->
          <div class="tab-pane fade" id="tab_reminders">

            <!-- ADD REMINDER -->
            <div class="border rounded p-3 mb-3">
              <h6 class="fw-semibold mb-2">Add Reminder</h6>

              <div class="row g-2">
                <div class="col-md-4">
                  <label class="small">Date & Time</label>
                  <input type="datetime-local" id="rem_at" class="form-control form-control-sm">
                </div>

                <div class="col-md-3">
                  <label class="small">Type</label>
                  <select id="rem_type" class="form-control form-control-sm">
                    <option>General</option>
                    <option>Call</option>
                    <option>Email</option>
                    <option>Meeting</option>
                  </select>
                </div>

                <div class="col-md-5">
                  <label class="small">Note</label>
                  <input id="rem_note" class="form-control form-control-sm"
                         placeholder="Follow up / callback / send docs">
                </div>
              </div>

              <div class="text-end mt-2">
                <button class="btn btn-primary btn-sm" onclick="addReminder()">Add Reminder</button>
              </div>
            </div>

            <!-- REMINDERS LIST -->
            <div class="border rounded p-3">
              <h6 class="fw-semibold mb-2">Upcoming Reminders</h6>

              <table class="table table-sm mb-0">
                <thead>
                  <tr>
                    <th>When</th>
                    <th>Type</th>
                    <th>Note</th>
                    <th>Status</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody id="av_reminders">
                  <tr><td colspan="5" class="text-muted">Loading…</td></tr>
                </tbody>
              </table>
            </div>

          </div>
          <script>
            function loadReminders(applicantId) {
              fetch('public/ajax/applicants_reminders.php', {
                method: 'POST',
                body: new URLSearchParams({
                  action: 'list',
                  applicant_id: applicantId
                })
              })
              .then(r => r.json())
              .then(res => {
                const tbody = document.getElementById('av_reminders');
                tbody.innerHTML = '';

                if (!res.status || res.data.length === 0) {
                  tbody.innerHTML = '<tr><td colspan="5" class="text-muted">No reminders</td></tr>';
                  return;
                }

                res.data.forEach(r => {
                  const tr = document.createElement('tr');
                  tr.innerHTML = `
                    <td>${r.reminder_at}</td>
                    <td>${r.type}</td>
                    <td>${r.note}</td>
                    <td>
                      ${r.completed == 1
                        ? '<span class="badge bg-success">Done</span>'
                        : '<span class="badge bg-warning text-dark">Pending</span>'}
                    </td>
                    <td>
                      ${r.completed == 0
                        ? `<button class="btn btn-sm btn-outline-success"
                            onclick="completeReminder(${r.id})">Done</button>`
                        : ''}
                    </td>
                  `;
                  tbody.appendChild(tr);
                });
              });
            }

            function addReminder() {
              const at   = document.getElementById('rem_at').value;
              const type = document.getElementById('rem_type').value;
              const note = document.getElementById('rem_note').value.trim();

              if (!at || !note) {
                alert('Reminder date and note are required');
                return;
              }

              const fd = new FormData();
              fd.append('action','add');
              fd.append('applicant_id', window.currentApplicantId);
              fd.append('reminder_at', at);
              fd.append('type', type);
              fd.append('note', note);

              fetch('public/ajax/applicants_reminders.php', {
                method: 'POST',
                body: fd
              })
              .then(r => r.json())
              .then(res => {
                if (!res.status) {
                  alert(res.msg || 'Failed');
                  return;
                }

                document.getElementById('rem_note').value = '';
                loadReminders(window.currentApplicantId);
                loadActivityTimeline(window.currentApplicantId); // auto refresh timeline
              });
            }

            function completeReminder(id) {
              if (!confirm('Mark this reminder as completed?')) return;

              const fd = new FormData();
              fd.append('action','complete');
              fd.append('id', id);

              fetch('public/ajax/applicants_reminders.php', {
                method: 'POST',
                body: fd
              })
              .then(r => r.json())
              .then(res => {
                if (!res.status) {
                  alert('Failed');
                  return;
                }

                loadReminders(window.currentApplicantId);
                loadActivityTimeline(window.currentApplicantId);
              });
            }
          </script>

          <!-- QUICK ACTIONS -->
          <div class="tab-pane fade" id="tab_quick">

            <div class="border rounded p-3 mb-3">
              <h6 class="fw-semibold mb-1">Quick Actions</h6>
              <div class="text-muted small">
                One-click actions that update status, add timeline entries and reminders.
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2">

              <!-- CALL -->
              <button class="btn btn-outline-secondary btn-sm"
                      onclick="runQuickAction('CALL_NO_ANSWER')">No Answer</button>

              <button class="btn btn-outline-secondary btn-sm"
                      onclick="runQuickAction('CALL_ANSWERED')">Answered</button>

              <button class="btn btn-primary btn-sm"
                      onclick="runQuickAction('CALL_CALLBACK_2H')">Callback +2h</button>

              <!-- REMINDERS -->
              <button class="btn btn-primary btn-sm"
                      onclick="runQuickAction('REM_CALL_TOM_10')">Call Tomorrow 10:00</button>

              <button class="btn btn-primary btn-sm"
                      onclick="runQuickAction('REM_FOLLOWUP_2D_10')">Follow-up +2 Days</button>

              <button class="btn btn-outline-secondary btn-sm"
                      onclick="runQuickAction('REM_REQUEST_DOCS')">Request Docs</button>

              <!-- STATUS -->
              <button class="btn btn-success btn-sm"
                      onclick="runQuickAction('STATUS_SHORTLISTED')">Shortlisted</button>

              <button class="btn btn-success btn-sm"
                      onclick="runQuickAction('STATUS_OFFER_SENT')">Offer Sent</button>

              <button class="btn btn-dark btn-sm"
                      onclick="runQuickAction('STATUS_JOINED')">Joined</button>

              <button class="btn btn-danger btn-sm"
                      onclick="runQuickAction('STATUS_REJECTED')">Rejected</button>

            </div>

          </div>
          <script>
            function runQuickAction(action) {
              if (!window.currentApplicantId) return;

              const fd = new FormData();
              fd.append('action', action);
              fd.append('applicant_id', window.currentApplicantId);

              fetch('public/ajax/applicants_quick_actions.php', {
                method: 'POST',
                body: fd
              })
              .then(r => r.json())
              .then(res => {
                if (!res.status) {
                  alert(res.msg || 'Action failed');
                  return;
                }
                else {
                  alert(res.msg || 'Quick Action success');
                }

                // Refresh UI
                loadActivityTimeline(window.currentApplicantId);
                loadReminders(window.currentApplicantId);
                loadStatusHistory(window.currentApplicantId);

                // refresh table without page reset
                $('#applicants-table').DataTable().ajax.reload(null, false);
              });
            }

          </script>

          <!-- INTERVIEW -->
          <div class="tab-pane fade" id="tab_interviews">

            <!-- Schedule Interview -->
            <div id="interview_form_wrap" class="border rounded p-3 mb-3">
              <h6 class="fw-semibold mb-2">Schedule Interview</h6>

              <form id="interviewForm">
                <input type="hidden" name="applicant_id" id="iv_applicant_id">

                <div class="row g-2">
                  <div class="col-md-6">
                    <label class="small">Interview Date & Time (Dubai)</label>
                    <input type="datetime-local" name="interview_at"
                           class="form-control form-control-sm" required>
                  </div>

                  <div class="col-md-3">
                    <label class="small">Mode</label>
                    <select name="mode" class="form-control form-control-sm" required>
                      <option>Phone</option>
                      <option>WhatsApp</option>
                      <option>Video</option>
                      <option>In-person</option>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="small">Reminder</label>
                    <select name="reminder_minutes" class="form-control form-control-sm">
                      <option value="1440">24 hours before</option>
                      <option value="240">4 hours before</option>
                      <option value="120" selected>2 hours before</option>
                      <option value="60">1 hour before</option>
                      <option value="30">30 minutes before</option>
                    </select>
                  </div>
                </div>

                <div class="row g-2 mt-1">
                  <div class="col-md-6">
                    <label class="small">Location / Link</label>
                    <input name="location" class="form-control form-control-sm"
                           placeholder="Office / Google Meet / WhatsApp call">
                  </div>

                  <div class="col-md-6">
                    <label class="small">Interviewer</label>
                    <input name="interviewer" class="form-control form-control-sm">
                  </div>
                </div>

                <label class="small mt-2">Notes</label>
                <textarea name="notes" rows="2" class="form-control form-control-sm"></textarea>

                <div class="text-end mt-2">
                  <button class="btn btn-primary btn-sm">Schedule</button>
                  <button type="reset" class="btn btn-outline-secondary btn-sm">Clear</button>
                </div>
              </form>
            </div>

            <!-- Interview List -->
            <div class="border rounded p-3">
              <h6 class="fw-semibold mb-2">Scheduled Interviews</h6>
              <div id="interviews_list" class="text-muted">Loading…</div>
            </div>

          </div>
          <script>
            function loadInterviews(applicantId) {
              fetch('public/ajax/applicants_interviews.php', {
                method: 'POST',
                body: new URLSearchParams({ action:'list', applicant_id: applicantId })
              })
              .then(r => r.json())
              .then(res => {

                const box = document.getElementById('interviews_list');
                box.innerHTML = '';

                if (!res.status || res.data.length === 0) {
                  box.innerHTML = '<div class="text-muted">No interviews scheduled</div>';
                  return;
                }

                res.data.forEach(iv => {
                  const div = document.createElement('div');
                  div.className = 'border rounded p-2 mb-2';

                  div.innerHTML = `
                    <div class="d-flex justify-content-between">
                      <div>
                        <b>${iv.mode}</b> – ${iv.interview_at}
                        <div class="small text-muted">${iv.location || ''}</div>
                      </div>
                      <span class="badge bg-${iv.status_color}">${iv.status}</span>
                    </div>

                    <div class="small text-muted mt-1">
                      Interviewer: ${iv.interviewer || '-'}
                    </div>

                    <div class="mt-2 d-flex gap-2">
                      <button class="btn btn-outline-success btn-sm"
                              onclick="updateInterviewStatus(${iv.id},'COMPLETED')">
                        Completed
                      </button>
                      <button class="btn btn-outline-danger btn-sm"
                              onclick="updateInterviewStatus(${iv.id},'CANCELLED')">
                        Cancel
                      </button>
                    </div>
                  `;

                  box.appendChild(div);
                });

                // Hide form if active interview exists
                document.getElementById('interview_form_wrap')
                  .style.display = res.has_active ? 'none' : 'block';
              });
            }
            document.getElementById('interviewForm').addEventListener('submit', function(e){
              e.preventDefault();

              const fd = new FormData(this);
              fd.append('action','schedule');

              fetch('public/ajax/applicants_interviews.php', {
                method: 'POST',
                body: fd
              })
              .then(r => r.json())
              .then(res => {
                if (!res.status) {
                  alert(res.msg || 'Failed to schedule');
                  return;
                }

                this.reset();
                loadInterviews(window.currentApplicantId);
                loadActivityTimeline(window.currentApplicantId);
              });
            });
            function updateInterviewStatus(id, status) {
              fetch('public/ajax/applicants_interviews.php', {
                method:'POST',
                body: new URLSearchParams({
                  action:'status',
                  interview_id:id,
                  status:status
                })
              })
              .then(r=>r.json())
              .then(res=>{
                if(!res.status){ alert(res.msg||'Failed'); return; }

                loadInterviews(window.currentApplicantId);
                loadActivityTimeline(window.currentApplicantId);
              });
            }

          </script>





      </div>

      <div class="modal-footer py-2">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script> -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
(function(){

  const start = moment().subtract(29,'days');
  const end   = moment();

  function cb(s,e){
    $('#date_range').val(s.format('DD-MM-YYYY') + ' - ' + e.format('DD-MM-YYYY'));
  }

  $('#date_range').daterangepicker({
    startDate: start,
    endDate: end,
    locale: { format: 'DD-MM-YYYY' },
    ranges:{
      'Today':[moment(),moment()],
      'Last 7 Days':[moment().subtract(6,'days'),moment()],
      'Last 30 Days':[moment().subtract(29,'days'),moment()]
    }
  }, cb);

  cb(start,end);

  const table = $('#applicants-table').DataTable({
    processing:true,
    serverSide:true,
    searching: false,
    ajax:{
      url:'public/ajax/applicants_list.php',
      type:'POST',
      data:function(d){
        d.date_range = $('#date_range').val();
        d.search_text = $('#flt_search').val();
        d.status = $('#flt_status').val();
        d.source = $('#flt_source').val();
      }
    },
    columns:[
      {data:'ref_no'},
      {data:'full_name'},
      {data:'mobile'},
      {data:'position'},
      {data:'location'},
      {data:'status'},
      {data:'source'},
      {data:'created_at'},
      {data:'actions', orderable:false, searchable:false}
    ],
    order:[[7,'desc']],
    pageLength:10
  });

  $('#flt_search').on('keyup', function(){
    table.ajax.reload();
  });

  $('#flt_status, #flt_source').on('change', function(){
    table.ajax.reload();
  });

  $('#flt_reset').on('click', function(){
    $('#flt_search').val('');
    $('#flt_status').val('');
    $('#flt_source').val('');
    cb(start, end); // reset date range
    table.ajax.reload();
  });


  $('#date_range').on('apply.daterangepicker', ()=> table.ajax.reload());

})();
</script>
<script>
function loadStatusHistory(applicantId) {
  fetch('public/ajax/applicants_history.php', {
    method: 'POST',
    body: new URLSearchParams({ applicant_id: applicantId })
  })
  .then(r => r.json())
  .then(res => {
    const tbody = document.getElementById('av_status_history');
    tbody.innerHTML = '';

    if (!res.status || res.data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-muted">No history</td></tr>';
      return;
    }

    res.data.forEach(h => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${h.old_status || '-'}</td>
        <td><span class="badge bg-info">${h.new_status}</span></td>
        <td>${h.note || '-'}</td>
        <td>${h.changed_by || '-'}</td>
        <td>${h.created_at}</td>
      `;
      tbody.appendChild(tr);
    });
  });
}
</script>
<script>
function updateApplicantStatus() {

  const newStatus = document.getElementById('av_new_status').value;
  if (!newStatus) {
    alert('Please select a new status');
    return;
  }

  const note = document.getElementById('av_status_note').value;

  const fd = new FormData();
  fd.append('applicant_id', window.currentApplicantId);
  fd.append('new_status', newStatus);
  fd.append('note', note);

  fetch('public/ajax/applicants_status.php', {
    method: 'POST',
    body: fd
  })
  .then(r => r.json())
  .then(res => {
    if (!res.status) {
      alert(res.msg || 'Failed to update');
      return;
    }

    // update UI
    document.getElementById('av_current_status').value = newStatus;
    document.getElementById('av_status').textContent = newStatus;

    // reload history
    loadStatusHistory(window.currentApplicantId);

    // refresh table
    $('#applicants-table').DataTable().ajax.reload(null, false);
  });
}
</script>
<script>
function viewApplicant(id,istimeline=false) {

  fetch('public/ajax/applicants_get.php', {
    method: 'POST',
    body: new URLSearchParams({ id })
  })
  .then(r => r.json())
  .then(res => {
    if (!res.status) {
      alert(res.msg || 'Failed to load applicant');
      return;
    }

    const a = res.data;

    document.getElementById('av_ref').textContent = a.ref_no || '';
    document.getElementById('av_name').textContent = a.full_name;
    document.getElementById('av_mobile').textContent = a.mobile;
    document.getElementById('av_email').textContent = a.email || '-';
    document.getElementById('av_nationality').textContent = a.nationality;

    document.getElementById('av_position').textContent = a.position;
    document.getElementById('av_exp').textContent = a.years_experience ?? '-';
    document.getElementById('av_salary').textContent = a.expected_salary_aed ?? '-';
    document.getElementById('av_status').textContent = a.status;

    document.getElementById('av_current_status').value = a.status;
    document.getElementById('av_new_status').value = '';
    document.getElementById('av_status_note').value = '';

    window.currentApplicantId = a.id;

    // load status history
    loadStatusHistory(a.id);

    // load timeline
    loadActivityTimeline(a.id);

    loadReminders(a.id);

    loadInterviews(a.id);
    document.getElementById('iv_applicant_id').value = a.id;

    document.getElementById('av_location').textContent = a.current_location + ' - ' + a.city;
    document.getElementById('av_visa').textContent = a.visa_status || '-';
    document.getElementById('av_availability').textContent = a.availability;

    document.getElementById('av_source').textContent = a.lead_source;
    document.getElementById('av_created').textContent = a.created_at;

    // Documents
    const tbody = document.getElementById('av_docs');
    tbody.innerHTML = '';

    if (!a.documents || a.documents.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" class="text-muted">No documents</td></tr>';
    } else {
      a.documents.forEach(d => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${d.doc_type}</td>
          <td>${d.original_filename}</td>
          <td>${d.size_kb} KB</td>
          <td>
            <a class="btn btn-sm btn-outline-primary"
               href="${d.file_path}" target="_blank">View</a>
          </td>`;
        tbody.appendChild(tr);
      });
    }

    // Force Overview tab active
    const modalEl = document.getElementById('applicantViewModal');

    if(istimeline==false) {
      modalEl.addEventListener('shown.bs.modal', function () {
        const overviewTab = modalEl.querySelector('[data-bs-target="#tab_overview"]');
        if (overviewTab) overviewTab.click();
      }, { once: true });
    }


    const modal = new bootstrap.Modal(modalEl);
    modal.show();
    // new bootstrap.Modal(document.getElementById('applicantViewModal')).show();
  });
}
</script>
<script>
function loadActivityTimeline(applicantId) {

  fetch('public/ajax/applicants_activity.php', {
    method: 'POST',
    body: new URLSearchParams({ applicant_id: applicantId })
  })
  .then(r => r.json())
  .then(res => {

    const box = document.getElementById('av_timeline');
    box.innerHTML = '';

    if (!res.status || res.data.length === 0) {
      box.innerHTML = '<div class="text-muted text-center py-3">No activity yet</div>';
      return;
    }

    res.data.forEach(a => {
      const div = document.createElement('div');
      div.className = 'd-flex gap-2 mb-3';

      div.innerHTML = `
        <div class="rounded-circle text-white d-flex align-items-center justify-content-center"
             style="width:32px;height:32px;background:${a.color}">
          ${a.icon}
        </div>
        <div class="flex-grow-1">
          <div class="small fw-semibold">${a.title}</div>
          ${a.details ? `<div class="small text-muted">${a.details}</div>` : ``}
          <div class="small text-muted">
            ${a.created_by || 'System'} • ${a.created_at}
          </div>
        </div>
      `;

      box.appendChild(div);
    });

    // scroll bottom
    box.scrollTop = box.scrollHeight;
  });
}
</script>
<script>
function openTimeline(applicantId) {
  viewApplicant(applicantId,true);

  setTimeout(() => {
    const tab = document.querySelector('[data-bs-target="#tab_timeline"]');
    if (tab) tab.click();
  }, 500);
}
</script>
<script>
function addApplicantNote() {

  const note = document.getElementById('av_new_note').value.trim();
  if (!note) return;

  const fd = new FormData();
  fd.append('applicant_id', window.currentApplicantId);
  fd.append('note', note);

  fetch('public/ajax/applicants_activity_add.php', {
    method: 'POST',
    body: fd
  })
  .then(r => r.json())
  .then(res => {
    if (!res.status) {
      alert(res.msg || 'Failed to add note');
      return;
    }

    document.getElementById('av_new_note').value = '';
    loadActivityTimeline(window.currentApplicantId);
  });
}
</script>
