<?php
// agent/includes/footer.php
?>
</div> <!-- /.container-fluid -->

<!-- Toast container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
  <div id="agentToast" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="agentToastBody"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<!-- Scripts -->
<!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-3gJwYp8k2v5u6s3s6b8rK3V6pK7Qv9+Y4fYh0kP4YkM=" crossorigin="anonymous"></script> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<!-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> -->
<!-- <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> -->

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>


<?php if ($is_buy_mode) { ?>
<!-- notifications start -->
<script>
(function () {

  const LS_KEY = 'notifications_viewed';

  /* ================= STORAGE ================= */
  function getViewed() {
    try {
      return JSON.parse(localStorage.getItem(LS_KEY)) || {};
    } catch {
      return {};
    }
  }

  function saveViewed(v) {
    localStorage.setItem(LS_KEY, JSON.stringify(v));
  }

  /* ================= KEY (FIXED) ================= */
  function makeKey(type, it) {
    if (type === 'reminder') {
      return `reminder|${it.id}|${it.updated_at}`;
    }
    if (type === 'requirement') {
      return `requirement|${it.id}|${it.expiry}`;
    }
    if (type === 'booking') {
      return `booking|${it.id}|${it.date}`;
    }
    if (type === 'document') {
      return `document|${it.id}|${it.expiry}`;
    }
  }

  /* ================= MARK VIEWED ================= */
  function markContainerViewed(containerId) {
    const box = document.getElementById(containerId);
    if (!box) return;

    const viewed = getViewed();
    box.querySelectorAll('.notification-item').forEach(el => {
      if (el.dataset.key) {
        viewed[el.dataset.key] = 1;
        el.classList.add('opacity1-50');
      }
    });
    saveViewed(viewed);
  }

  /* ================= UNREAD COUNTS ================= */
  function updateUnreadCountsFromAPI(apiCounts) {
    const viewed = getViewed();
    const markAllAt = viewed.__markAllAt || 0;

    function unread(type, total) {
      let explicitlyRead = 0;

      Object.keys(viewed).forEach(k => {
        if (k === '__markAllAt') return;
        if (!k.startsWith(type + '|')) return;
        explicitlyRead++;
      });

      // if mark-all was used, everything before it is read
      if (markAllAt > 0) {
        return 0;
      }

      return Math.max(total - explicitlyRead, 0);
    }

    const rem = unread('reminder', apiCounts.reminders);
    const req = unread('requirement', apiCounts.requirements);
    const boo = unread('bookings', apiCounts.bookings);
    const doc = unread('document', apiCounts.documents);

    setCount('cnt-reminders', rem);
    setCount('cnt-requirements', req);
    setCount('cnt-bookings', boo);
    setCount('cnt-documents', doc);

    const total = rem + req + doc + boo;
    const bell = document.querySelector('#notificationDropdown .badge');
    if (bell) {
      bell.textContent = total;
      bell.style.display = total ? '' : 'none';
    }
  }

  /* ================= TAB MAP ================= */
  const TAB_MAP = {
    reminders: 'list-reminders',
    requirements: 'list-requirements',
    bookings: 'list-bookings',
    documents: 'list-documents'
  };

  document.getElementById('notificationDropdown')
    ?.addEventListener('shown.bs.dropdown', () => {
      const activeTab =
        document.querySelector('[data-notif-tab].active') ||
        document.querySelector('[data-notif-tab]');

      if (!activeTab) return;

      const container = TAB_MAP[activeTab.dataset.notifTab];
      if (container) {
        markContainerViewed(container);
        updateUnreadCountsFromAPI(window.__notifCounts || {});
      }
    });

  document.querySelectorAll('[data-notif-tab]').forEach(tabBtn => {
    tabBtn.addEventListener('shown.bs.tab', () => {
      const container = TAB_MAP[tabBtn.dataset.notifTab];
      if (container) {
        markContainerViewed(container);
        updateUnreadCountsFromAPI(window.__notifCounts || {});
      }
    });
  });

  /* ================= FETCH ================= */
  const URL = 'public/ajax/notifications.php';

  function loadNotifications() {
    fetch(URL)
      .then(r => r.json())
      .then(res => {
        if (!res || !res.success) return;

        window.__notifCounts = res.counts || {};

        renderReminders(res.reminders || []);
        renderRequirements(res.requirements || []);
        renderBookingsN(res.bookings || []);
        renderDocuments(res.documents || []);

        updateUnreadCountsFromAPI(res.counts);
      });
  }

  /* ================= RENDERERS ================= */
  function renderReminders(items) {
    const box = document.getElementById('list-reminders');
    box.innerHTML = '';
    const viewed = getViewed();
    const markAllAt = viewed.__markAllAt || 0;

    if (!items.length) {
      box.innerHTML = emptyRow('No reminders');
      return;
    }

    items.forEach(it => {
      const key = makeKey('reminder', it);
      const read = viewed[key] || it.updated_at <= markAllAt;

      box.insertAdjacentHTML('beforeend', `
        <div class="list-group-item notification-item ${read ? 'opacity1-50' : ''}"
             data-key="${key}">
          <div class="fw-semibold small">${esc(it.title)}</div>
          <div class="text-muted small">${esc(it.note || '')}</div>
          <div class="text-muted small">${esc(it.due)}</div>
        </div>
      `);
    });
  }

  function renderRequirements(items) {
    const box = document.getElementById('list-requirements');
    box.innerHTML = '';
    const viewed = getViewed();

    if (!items.length) {
      box.innerHTML = emptyRow('No job expiries');
      return;
    }

    items.forEach(it => {
      const key = makeKey('requirement', it);
      const read = !!viewed[key];

      box.insertAdjacentHTML('beforeend', `
        <div class="list-group-item notification-item ${read ? 'opacity1-50' : ''}"
             data-key="${key}">
          <div class="fw-semibold small">${esc(it.title)}</div>
          <div class="text-danger small">${esc(it.expiry_text)}</div>
        </div>
      `);
    });
  }

  function renderBookingsN(items) {
    const box = document.getElementById('list-bookings');
    box.innerHTML = '';
    const viewed = getViewed();

    if (!items.length) {
      box.innerHTML = emptyRow('No recent bookings');
      return;
    }

    items.forEach(it => {
      const key = makeKey('booking', it);
      const read = !!viewed[key];

      box.insertAdjacentHTML('beforeend', `
        <div class="list-group-item notification-item ${read ? 'opacity1-50' : ''}"
             data-key="${key}">
          <div class="fw-semibold small">${esc(it.contact_name)}</div>
          <div class="text-info small">${esc(it.type)} | ${esc(it.dated)} ${esc(it.timed)}</div>
        </div>
      `);
    });
  }

  function renderDocuments(items) {
    const box = document.getElementById('list-documents');
    box.innerHTML = '';
    const viewed = getViewed();

    if (!items.length) {
      box.innerHTML = emptyRow('No document expiries');
      return;
    }

    items.forEach(it => {
      const key = makeKey('document', it);
      const read = !!viewed[key];

      box.insertAdjacentHTML('beforeend', `
        <div class="list-group-item notification-item ${read ? 'opacity1-50' : ''}"
             data-key="${key}">
          <div class="fw-semibold small">${esc(it.title)}</div>
          <div class="text-danger small">${esc(it.expiry_text)}</div>
        </div>
      `);
    });
  }

  /* ================= MARK ALL ================= */
  document.getElementById('notifMarkAll')?.addEventListener('click', () => {
    const viewed = getViewed();
    viewed.__markAllAt = Date.now();
    saveViewed(viewed);
    updateUnreadCountsFromAPI(window.__notifCounts || {});
  });

  /* ================= HELPERS ================= */
  function setCount(id, n) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = n;
    el.className = 'badge ms-1 ' + (n ? 'bg-danger' : 'bg-secondary');
  }

  function emptyRow(text) {
    return `<div class="p-3 text-center text-muted small">${text}</div>`;
  }

  function esc(str) {
    return String(str || '').replace(/[&<>"']/g, m =>
      ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])
    );
  }

  document.addEventListener('DOMContentLoaded', loadNotifications);

})();
</script>
<!-- notifications end -->
<?php } ?>


<script>
(function () {
  // Simple toast helper: showToast('Message', 3000)
  function showToast(msg, timeout) {
    var toastEl = document.getElementById('agentToast');
    var body = document.getElementById('agentToastBody');
    body.textContent = msg || '';
    var toast = new bootstrap.Toast(toastEl, { delay: timeout || 3000 });
    toast.show();
  }
  window.showToast = showToast;

  // Logout confirmation (any logout link with data-confirm)
  document.addEventListener('click', function(e){
    var t = e.target.closest && e.target.closest('[data-confirm]');
    if (!t) return;
    var msg = t.getAttribute('data-confirm') || 'Are you sure?';
    if (!confirm(msg)) {
      e.preventDefault();
      return false;
    }
  }, true);

  // Optional: attach global AJAX error handler for fetch calls used in the portal
  window.agentFetch = function(url, opts){
    opts = opts || {};
    opts.headers = opts.headers || {};
    // If you want to add any auth headers or token, do it here
    return fetch(url, opts).then(function(resp){
      if (!resp.ok) {
        // try to parse json error
        return resp.json().then(function(j){
          throw j;
        }).catch(function(){
          throw { ok:false, error: 'Network error: ' + resp.statusText };
        });
      }
      return resp.json().catch(function(){ return resp.text(); });
    });
  };

  // Enable tooltips globally
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(function (tooltipTriggerEl) {
    new bootstrap.Tooltip(tooltipTriggerEl);
  });
})();
</script>


<!-- MEETING ALERT MODAL -->
<div class="modal fade" id="meetingAlertsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-calendar text-primary me-2"></i>
          Today's & Upcoming Meetings
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-0">
        <table class="table table-sm table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Time</th>
              <th>Name</th>
              <th>Timezone</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="meetingAlertsTableBody">
            <tr>
              <td colspan="5" class="text-center text-muted py-3">
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
  const meetingModalEl = document.getElementById('meetingAlertsModal');
  let meetingAlertIds = [];

  /* Load meeting alerts */
  function loadMeetingAlerts() {
    $.getJSON(
      'public/ajax/bookings.php',
      { action: 'meeting_alerts' },
      function (res) {

        if (!res.success || !res.data.length) return;

        let rows = '';
        meetingAlertIds = [];

        res.data.forEach(r => {
          meetingAlertIds.push(r.id);

          rows += `
            <tr>
              <td>${r.date}</td>
              <td>${r.time}</td>
              <td>${esc(r.name)}</td>
              <td>${r.tz}</td>
              <td>
                <span class="badge bg-info">
                  ${r.status}
                </span>
              </td>
            </tr>`;
        });

        $('#meetingAlertsTableBody').html(rows);

        new bootstrap.Modal(meetingModalEl).show();
      }
    );
  }

  /* When modal closes → mark as read */
  meetingModalEl.addEventListener('hidden.bs.modal', function () {

    if (!meetingAlertIds.length) return;

    $.post(
      'public/ajax/bookings.php',
      {
        action: 'mark_meeting_alert_read',
        ids: meetingAlertIds
      }
    );
  });

  /* Auto-load on page load */
  $(document).ready(function () {
    loadMeetingAlerts();
  });
</script>

</body>
</html>
