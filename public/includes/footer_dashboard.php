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

</body>
</html>
