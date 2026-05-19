<?php
// templates.php (UI + JS) — updated
// require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

require_once __DIR__ . '/includes/header.php';
?>
<div class="container1">
  <div class="d-flex align-items-center mb-3">
    <h1 class="h4 me-3">Document Templates Manager</h1>
    <div class="ms-auto">
      <button class="btn btn-primary btn-sm" id="btnNewTemplate"><i class="fa fa-plus me-1"></i> New Template</button>
      <button class="btn btn-outline-secondary btn-sm" id="btnRefresh"><i class="fa fa-sync"></i> Refresh</button>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card p-3">
        <h5>Templates</h5>
        <div id="templatesList" class="mt-2"></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card p-3">
        <h5>Details & Versions</h5>
        <div id="templateDetails" class="mt-2">
          <p class="small-muted">Select a template to view versions and actions.</p>
        </div>
      </div>

      <div class="card p-3 mt-3">
        <h5>Created Documents</h5>
        <div id="documentsList" class="mt-2"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modals -->
<div class="modal fade" id="modalTemplate" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="formTemplate">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTemplateTitle">New Template</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="template_id">
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <div class="btn-group w-100" id="templateCategoryGroup">
                  <button type="button" class="btn btn-outline-primary active" data-value="employee">Employee</button>
                  <button type="button" class="btn btn-outline-primary" data-value="customer">Customer</button>
                  <input type="hidden" name="category" id="template_category" value="employee">
                  <button type="button" class="btn btn-outline-primary" data-value="other">Other</button>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Template name</label>
              <input class="form-control" name="subtype" id="template_subtype" placeholder="e.g. Appointment letter" required>
              <!-- <div class="small text-muted mt-1">Slug will be auto-generated from this name.</div> -->
            </div>

            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" id="template_description" rows="3"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" id="saveTemplateBtn">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalVersion" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <form id="formVersion">
        <div class="modal-header">
          <h5 class="modal-title" id="modalVersionTitle">New Version</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="version_id" name="id">
          <input type="hidden" id="version_template_id" name="template_id">
          <div class="mb-2 row g-2">
            <div class="col-md-3">
              <label class="form-label">Version label</label>
              <input class="form-control" name="version" id="version_label" placeholder="v1.0" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Notes</label>
              <input class="form-control" name="notes" id="version_notes" placeholder="Short notes">
            </div>
            <div class="col-md-6 d-flex align-items-end justify-content-end">
              <button type="button" class="btn btn-outline-secondary me-2" id="btnPreviewContent"><i class="fa fa-eye"></i> Preview</button>
              <button type="button" class="btn btn-secondary" id="btnCopyActive">Create from active</button>
            </div>
            <div class="col-md-6">
              <input type="checkbox" class="form-check1" name="show_header" id="show_header">
              <label for="show_header">Show Header and Footer in Document</label>
            </div>
          </div>

          <label class="form-label">Content (HTML allowed). Use placeholders like <code>{{name}}</code>, <code>{{date}}</code></label>
          <textarea id="version_content" name="content" rows="12" class="form-control mono"></textarea>

          <div class="mt-2">
            <small class="text-muted">Detected placeholders in the content will be offered when creating documents.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary">Save Version</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalPreview" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="previewContent" class="content-preview"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalCreateDoc" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="formCreateDoc">
        <div class="modal-header">
          <h5 class="modal-title">Create Document</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="create_version_id" name="version_id">

          <div class="mb-2">
            <label class="form-label">Entity Type</label>
            <div class="btn-group w-100" id="entityTypeGroup">
                <input type="hidden" name="entity_type" id="entity_type_value" value="employee">
                <button type="button" class="btn btn-outline-dark active" id="entityEmpbtn" data-value="employee">Employee</button>
                <button type="button" class="btn btn-outline-dark" data-value="customer">Customer</button>
                <button type="button" class="btn btn-outline-dark" data-value="recruiter">Recruiter</button>
                <button type="button" class="btn btn-outline-dark" data-value="other">Other</button>
            </div>
          </div>

          <div id="entitySelectorBlock">

            <div class="mb-2">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" id="entity_search" placeholder="Type to search...">
            </div>
            <div id="entityList" class="d-flex flex-wrap gap-2 mt-2"></div>

          </div>

          <!-- Hidden actual identifier -->
          <input type="hidden" name="entity_identifier" id="entity_identifier_input">

          <div class="row g-2">
            <div class="col-md-8">
              <label class="form-label">Title</label>
              <input class="form-control" name="title" id="doc_title" placeholder="Document title">
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <div class="small text-muted">Fields below are placeholders extracted from the template version.</div>
            </div>
          </div>

          <div class="mt-3" id="placeholdersFields">
            <!-- dynamic placeholder fields will be injected here -->
          </div>

        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalPdfPreview" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered" style1="max-width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dlabel">PDF Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="pdfFrame" style="width:100%;height:75vh;border:0;"></iframe>
      </div>
    </div>
  </div>
</div>

<div class="mt-3"></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- scripts -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
<script>
(function(){
  const templatesList = document.getElementById('templatesList');
  const templateDetails = document.getElementById('templateDetails');
  const documentsList = document.getElementById('documentsList');

  const modalTemplate = new bootstrap.Modal(document.getElementById('modalTemplate'));
  const modalVersion = new bootstrap.Modal(document.getElementById('modalVersion'));
  const modalPreview = new bootstrap.Modal(document.getElementById('modalPreview'));
  const modalCreateDoc = new bootstrap.Modal(document.getElementById('modalCreateDoc'));

  // category buttons
  document.querySelectorAll("#templateCategoryGroup button").forEach(btn => {
      btn.onclick = function () {
          document.getElementById("template_category").value = this.dataset.value;
          document.querySelectorAll("#templateCategoryGroup button").forEach(b => b.classList.remove("active"));
          this.classList.add("active");
      };
  });

  // LIVE SEARCH (auto load on typing)
  document.getElementById("entity_search").addEventListener("keyup", async (e) => {
      const keyword = e.target.value.trim();
      const type = document.getElementById("entity_type_value").value;

      loadEntityList(type, keyword); // auto refresh list
  });


  async function loadEntityData(identifier) {
      const entity_type = document.getElementById("entity_type_value").value;

      if (!identifier) return;

      const url = `../public/ajax/document_templates_admin.php?action=load_entity
          &entity_type=${encodeURIComponent(entity_type)}
          &identifier=${encodeURIComponent(identifier)}`.replace(/\s+/g, "");

      const res = await fetch(url).then(r => r.json());
      if (!res.ok) return alert(res.error || "Failed to load entity");

      const data = res.data;

      // Fill placeholders automatically
      Object.keys(data).forEach(k => {
          const el = document.getElementById("ph_" + k);
          if (el) el.value = data[k];
      });

      // Set title if empty
      // if (data.name && !document.getElementById("doc_title").value) {
          document.getElementById("doc_title").value = data.name + " - Document";
      // }
  }

  async function loadEntityList(type, keyword = "") {
      const url = `../public/ajax/document_templates_admin.php?action=search_entities
                   &type=${encodeURIComponent(type)}
                   &keyword=${encodeURIComponent(keyword)}
                   &limit=10`.replace(/\s+/g, "");

      const res = await fetch(url).then(r => r.json());

      if (!res.ok) {
          document.getElementById("entityList").innerHTML =
              '<div class="small text-danger">Failed loading data</div>';
          return;
      }

      renderEntityList(res.data, type);
  }

  function renderEntityList(list, type) {
      const box = document.getElementById("entityList");
      box.innerHTML = "";

      if (!list.length) {
          box.innerHTML = '<div class="small text-muted">No results</div>';
          return;
      }

      list.forEach(item => {
          let label =
              type === "employee" ? `${item.name}` :
              type === "customer" ? `${item.name}` :
              `${item.name}`;

          const btn = document.createElement("button");
          btn.type = "button";
          btn.className = "btn btn-outline-primary";
          // btn.style.minWidth = "180px";
          btn.innerHTML = label;

          btn.onclick = () => {
              document.getElementById("entity_identifier_input").value = item.id;
              loadEntityData(item.id);

              document.querySelectorAll("#entityList button")
                  .forEach(b => b.classList.remove("active"));
              btn.classList.add("active");
          };

          box.appendChild(btn);
      });
  }

  // Toggle entity selector when entity type changes
  document.querySelectorAll("#entityTypeGroup button").forEach(btn => {
      btn.onclick = function () {
          const type = this.dataset.value;
          document.getElementById("entity_type_value").value = type;

          document.querySelectorAll("#entityTypeGroup button")
              .forEach(b => b.classList.remove("active"));
          this.classList.add("active");

          const block = document.getElementById("entitySelectorBlock");

          // reset fields
          document.getElementById("entityList").innerHTML = "";
          document.getElementById("entity_search").value = "";
          document.getElementById("entity_identifier_input").value = "";

          if (type === "other") {
              block.style.display = "none";
              return;
          }

          block.style.display = "block";

          // auto-load default 10 results
          loadEntityList(type, "");
      };
  });


  document.getElementById("entity_search").addEventListener("keyup", e => {
      if (e.key === "Enter") document.getElementById("btnSearchEntity").click();
  });



  // fetch and render templates
  async function loadTemplates(){
    const res = await fetch('../public/ajax/document_templates_admin.php?action=list_templates').then(r=>r.json());
    if(!res.ok) return alert(res.error || 'Failed to load');
    const rows = res.data;
    templatesList.innerHTML = '';

    // 🔥 If EMPTY → Show message
    if (!rows.length) {
        templatesList.innerHTML = `
            <div class="text-muted small p-2">
                No templates found. Click <strong>"New Template"</strong> to create one.
            </div>`;
        return;
    }
    
    rows.forEach(r=>{
      const card = document.createElement('div');
      card.className = 'template-card card p-2 mb-2';
      card.innerHTML = `
        <div class="d-flex align-items-start">
          <div class="flex-grow-1">
            <div class="fw-bold">${escapeHtml(r.subtype)}</div>
            <div class="small-muted">${escapeHtml(r.category)} • <code>${escapeHtml(r.slug)}</code></div>
            <div class="mt-2 small">${escapeHtml(r.description || '')}</div>
          </div>
          <div class="ms-3 text-end">
            <button class="btn btn-sm btn-outline-primary me-1 btn-view" data-id="${r.id}" title="Open"><i class="fa fa-folder-open"></i></button>
            <button class="btn btn-sm btn-outline-secondary me-1 btn-edit" data-id="${r.id}" title="Edit"><i class="fa fa-pen"></i></button>
            <button class="btn btn-sm btn-danger btn-delete" data-id="${r.id}" title="Delete"><i class="fa fa-trash"></i></button>
          </div>
        </div>
      `;
      templatesList.appendChild(card);
    });

    // attach events
    templatesList.querySelectorAll('.btn-view').forEach(btn=>{
      btn.onclick = ()=> openTemplateDetails(btn.dataset.id);
    });
    templatesList.querySelectorAll('.btn-edit').forEach(btn=>{
      btn.onclick = ()=> editTemplate(btn.dataset.id);
    });
    templatesList.querySelectorAll('.btn-delete').forEach(btn=>{
      btn.onclick = async ()=> {
        if(!confirm('Delete template and all versions?')) return;
        const res = await fetch('../public/ajax/document_templates_admin.php?action=delete_template',{method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'id='+encodeURIComponent(btn.dataset.id)}).then(r=>r.json());
        if(res.ok) loadTemplates();
        else alert(res.error || 'Error');
      };
    });
  }

  // open details: show versions and actions
  async function openTemplateDetails(id){
    const res = await fetch('../public/ajax/document_templates_admin.php?action=list_versions&template_id='+encodeURIComponent(id)).then(r=>r.json());
    if(!res.ok) return alert(res.error || 'Failed to load versions');
    const versions = res.data;
    templateDetails.innerHTML = `
      <div class="d-flex align-items-center mb-2">
        <h6 class="mb-0">Versions (${versions.length})</h6>
        <div class="ms-auto">
          <button class="btn btn-sm btn-primary me-1" id="addVersionBtn"><i class="fa fa-plus"></i> New Version</button>
          <button class="btn btn-sm btn-outline-secondary" id="refreshVersionsBtn"><i class="fa fa-sync"></i></button>
        </div>
      </div>
      <div id="versionsList"></div>
    `;
    const versionsList = document.getElementById('versionsList');
    versionsList.innerHTML = '';
    versions.forEach(v=>{
      const activeBadge = v.is_active == 1 ? '<span class="badge bg-success ms-2">Active</span>' : '';
      const box = document.createElement('div');
      box.className = 'd-flex align-items-start p-2 border rounded mb-2';
      box.innerHTML = `
        <div>
          <div class="fw-semibold">${escapeHtml(v.version)} ${activeBadge}</div>
          <div class="small-muted">${escapeHtml(v.notes || '')} • ${escapeHtml(v.created_at)}</div>
        </div>
        <div class="ms-auto text-end">
          <button class="btn btn-sm btn-outline-primary btn-preview me-1" data-id="${v.id}" title="View"><i class="fa fa-eye"></i></button>
          <button class="btn btn-sm btn-outline-secondary btn-edit-version me-1" data-id="${v.id}" data-template="${v.template_id}" title="Edit"><i class="fa fa-pen"></i></button>
          <button class="btn btn-sm btn-outline-success btn-set-active me-1" data-id="${v.id}" data-template="${v.template_id}" title="Set Active"><i class="fa fa-check"></i></button>
          <button class="btn btn-sm btn-danger btn-delete-version" data-id="${v.id}" title="Delete"><i class="fa fa-trash"></i></button>
          <!-- <button class="btn btn-sm btn-success btn-create-doc ms-1" data-id="${v.id}" title="Create Document"><i class="fa fa-file-circle-plus"></i></button> --!>
        </div>
      `;
      versionsList.appendChild(box);
    });

    document.getElementById('addVersionBtn').onclick = ()=> {
      document.getElementById('formVersion').reset();
      document.getElementById('version_content').value = '';
      document.getElementById('version_template_id').value = id;
      document.getElementById('version_id').value = '';
      document.getElementById('modalVersionTitle').textContent = 'New Version';
      modalVersion.show();
    };

    document.getElementById('refreshVersionsBtn').onclick = ()=> openTemplateDetails(id);

    // preview
    versionsList.querySelectorAll('.btn-preview').forEach(b=>{
      b.onclick = async ()=>{
        const res = await fetch('../public/ajax/document_templates_admin.php?action=get_version&id='+encodeURIComponent(b.dataset.id)).then(r=>r.json());
        if(res.ok) {
          document.getElementById('previewContent').innerHTML = (res.data.content || "").replace(/\n/g, "<br>");
          modalPreview.show();
        } else alert(res.error || 'Failed');
      };
    });

    // edit version
    versionsList.querySelectorAll('.btn-edit-version').forEach(b=>{
      b.onclick = async ()=>{
        const res = await fetch('../public/ajax/document_templates_admin.php?action=get_version&id='+encodeURIComponent(b.dataset.id)).then(r=>r.json());
        if(res.ok) {
          document.getElementById('version_id').value = res.data.id;
          document.getElementById('version_label').value = res.data.version;
          document.getElementById('version_notes').value = res.data.notes;
          document.getElementById('show_header').checked = (res.data.show_header==1?'checked':'');
          document.getElementById('version_content').value = res.data.content;
          document.getElementById('version_template_id').value = res.data.template_id;
          document.getElementById('modalVersionTitle').textContent = 'Edit Version';
          modalVersion.show();
        } else alert(res.error || 'Not found');
      };
    });

    // set active
    versionsList.querySelectorAll('.btn-set-active').forEach(b=>{
      b.onclick = async ()=>{
        if(!confirm('Set this version as active?')) return;
        const body = new URLSearchParams();
        body.append('template_id', b.dataset.template);
        body.append('version_id', b.dataset.id);
        const res = await fetch('../public/ajax/document_templates_admin.php?action=set_active_version', {method:'POST', body}).then(r=>r.json());
        if(res.ok) openTemplateDetails(b.dataset.template);
        else alert(res.error || 'Error');
      };
    });

    // delete version
    versionsList.querySelectorAll('.btn-delete-version').forEach(b=>{
      b.onclick = async ()=>{
        if(!confirm('Delete this version? This cannot be undone')) return;
        const res = await fetch('../public/ajax/document_templates_admin.php?action=delete_version', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'id='+encodeURIComponent(b.dataset.id)}).then(r=>r.json());
        if(res.ok) openTemplateDetails(b.dataset.template);
        else alert(res.error || 'Error deleting');
      };
    });

    // create document from version -> open modal and load placeholders
    versionsList.querySelectorAll('.btn-create-doc').forEach(b=>{
      b.onclick = async ()=>{
        document.getElementById('formCreateDoc').reset();
        document.getElementById('create_version_id').value = b.dataset.id;

        document.getElementById('entityEmpbtn').click();

        // fetch placeholders for this version
        const res = await fetch('../public/ajax/document_templates_admin.php?action=get_placeholders&version_id=' + encodeURIComponent(b.dataset.id)).then(r=>r.json());
        if(!res.ok) return alert(res.error || 'Failed to get placeholders');
        buildPlaceholderFields(res.data); // data = array of placeholder keys
        modalCreateDoc.show();
      };
    });
  }

  function buildPlaceholderFields(placeholders){
    // placeholders: array of keys e.g. ['name','date','joining_date']
    const container = document.getElementById('placeholdersFields');
    container.innerHTML = '';
    placeholders.forEach(key=>{
      const id = 'ph_' + key;
      const wrapper = document.createElement('div');
      wrapper.className = 'mb-2';
      wrapper.innerHTML = `
        <label class="form-label">${escapeHtml(key)}</label>
        <input class="form-control" name="${id}" id="${id}" placeholder="${escapeHtml(key)}">
      `;
      container.appendChild(wrapper);
    });
    // set sensible defaults for name and date if present
    if(document.getElementById('ph_name')) document.getElementById('ph_name').placeholder = 'Full name';
    if(document.getElementById('ph_date')) document.getElementById('ph_date').value = new Date().toISOString().slice(0,10);
  }

  async function editTemplate(id) {
    const res = await fetch('../public/ajax/document_templates_admin.php?action=list_templates').then(r=>r.json());
    const row = res.data.find(r=>r.id==id);
    if(!row) return alert('Not found');
    document.getElementById('template_id').value = row.id;
    document.getElementById('template_category').value = row.category;
    // set category button state:
    document.querySelectorAll('#templateCategoryGroup button').forEach(b=> b.classList.remove('active'));
    const catBtn = document.querySelector('#templateCategoryGroup button[data-value="'+row.category+'"]');
    if(catBtn) catBtn.classList.add('active');

    document.getElementById('template_subtype').value = row.subtype;
    document.getElementById('template_description').value = row.description;
    document.getElementById('modalTemplateTitle').textContent = 'Edit Template';
    modalTemplate.show();
  }

  // save template form
  document.getElementById('formTemplate').addEventListener('submit', async function(e){
    e.preventDefault();
    const fd = new FormData(this);
    // slug is generated server-side; do not send slug
    const id = fd.get('id');
    const action = id ? 'edit_template' : 'create_template';
    const res = await fetch('../public/ajax/document_templates_admin.php?action='+action, {method:'POST', body: fd}).then(r=>r.json());
    if(res.ok) {
      modalTemplate.hide();
      loadTemplates();
    } else alert(res.error || 'Error');
  });

  // version form save
  document.getElementById('formVersion').addEventListener('submit', async function(e){
    e.preventDefault();
    const fd = new FormData(this);
    const id = fd.get('id');
    const action = id ? 'edit_version' : 'create_version';
    const res = await fetch('../public/ajax/document_templates_admin.php?action='+action, {method:'POST', body: fd}).then(r=>r.json());
    if(res.ok) {
      modalVersion.hide();
      const tid = document.getElementById('version_template_id').value;
      if(tid) openTemplateDetails(tid);
      loadTemplates();
    } else alert(res.error || 'Error saving version');
  });

  // preview button in version modal
  document.getElementById('btnPreviewContent').onclick = ()=>{
    const content = document.getElementById('version_content').value || '';
    document.getElementById('previewContent').innerHTML = (content || "").replace(/\n/g, "<br>");
    modalPreview.show();
  };

  // copy active button
  document.getElementById('btnCopyActive').onclick = async ()=>{
    const tid = document.getElementById('version_template_id').value;
    if(!tid) return alert('Template id missing');
    const res = await fetch('../public/ajax/document_templates_admin.php?action=list_versions&template_id='+encodeURIComponent(tid)).then(r=>r.json());
    if(!res.ok) return alert('Failed');
    const active = res.data.find(v=>v.is_active==1);
    if(!active) return alert('No active version found');
    document.getElementById('version_content').value = active.content;
    document.getElementById('version_label').value = (active.version || '') + '-copy';
  };

  // create doc form submit — collect placeholder fields automatically
  document.getElementById('formCreateDoc').addEventListener('submit', async function(e){
    e.preventDefault();
    const fd = new FormData(this);
    const version_id = fd.get('version_id') || document.getElementById('create_version_id').value;
    if(!version_id) return alert('Version missing');
    const entity_type = fd.get('entity_type') || document.getElementById('entity_type_value').value;
    const entity_identifier = document.getElementById('entity_identifier_input').value || '';
    const title = document.getElementById('doc_title').value || 'Document';

    // collect all placeholder inputs starting with ph_
    const placeholderInputs = document.querySelectorAll('[id^="ph_"]');
    const placeholders = {};
    placeholderInputs.forEach(inp=>{
      const name = inp.id.replace(/^ph_/, '');
      placeholders[name] = inp.value;
    });

    // build POST body
    const body = new URLSearchParams();
    body.append('version_id', version_id);
    body.append('entity_type', entity_type);
    body.append('entity_identifier', entity_identifier);
    body.append('title', title);
    body.append('placeholders', JSON.stringify(placeholders)); // server will decode

    const res = await fetch('../public/ajax/document_templates_admin.php?action=create_document', {method:'POST', body}).then(r=>r.json());
    if(res.ok) {
      modalCreateDoc.hide();
      loadDocuments();
      alert('Document created (id: '+res.id+')');
    } else alert(res.error || 'Error');
  });

  // load documents
  async function loadDocuments(){
    const res = await fetch('../public/ajax/document_templates_admin.php?action=list_documents').then(r=>r.json());
    if(!res.ok) return documentsList.innerHTML = '<div class="small text-danger">Failed to load documents</div>';
    const rows = res.data;
    if(!rows.length) { documentsList.innerHTML = '<div class="small-muted">No documents yet</div>'; return; }
    let html = '<div class="list-group">';
    rows.forEach(r=>{
      html += `<div class="list-group-item d-flex align-items-start">
        <div>
          <div class="fw-semibold">${escapeHtml(r.title)}</div>
          <div class="small-muted">${escapeHtml(r.subtype)} • ${escapeHtml(r.entity_type)} • <!-- ${escapeHtml(r.entity_identifier || '')} --!> ${escapeHtml(r.created_at)}</div>
        </div>
        <div class="ms-auto">

          <button class="btn btn-sm btn-outline-primary me-1 btn-view-doc" title="View" data-id="${r.id}">
          <i class="fa fa-eye"></i></button>

          <button class="btn btn-sm btn-outline-secondary me-1 btn-download-doc" title="Print" data-id="${r.id}" data-label="${r.title}">
          <i class="fa fa-print"></i></button>

          <!--`+(r.entity_type!='other'?`<button class="btn btn-sm me-1 btn-add-profile`+(r.add_to_profile=='1'?'1 btn-success':' btn-outline-success')+`" title="`+(r.add_to_profile=='1'?'Already Added to Profile':'Add to Profile')+`"
              data-id="${r.id}"
              data-entity="${r.entity_type}"
              data-identifier="${r.entity_identifier}"
              data-title="${escapeHtml(r.title)}">
          <i class="fa fa-`+(r.add_to_profile=='1'?'check-circle':'user-plus')+`"></i></button>`:``)+`

          <button class="btn btn-sm btn-danger btn-delete-doc" title="Delete document" data-id="${r.id}">
          <i class="fa fa-trash"></i></button> --!>

        </div>
      </div>`;
    });
    html += '</div>';
    documentsList.innerHTML = html;

    documentsList.querySelectorAll('.btn-view-doc').forEach(b=>{
      b.onclick = async ()=>{
        const res = await fetch('../public/ajax/document_templates_admin.php?action=get_document&id='+encodeURIComponent(b.dataset.id)).then(r=>r.json());
        if(res.ok) {
          document.getElementById('previewContent').innerHTML = (res.data.content || "").replace(/\n/g, "<br>");
          modalPreview.show();
        } else alert('Error');
      };
    });

    // documentsList.querySelectorAll('.btn-download-doc').forEach(b=>{
    //   b.onclick = async ()=>{
    //     // open pdf in new tab using download_pdf endpoint
    //     const id = b.dataset.id;
    //     window.open('../public/ajax/document_templates_admin.php?action=download_pdf&id=' + encodeURIComponent(id), '_blank');
    //   };
    // });
    documentsList.querySelectorAll('.btn-download-doc').forEach(b=>{
      b.onclick = async ()=>{
        const did = b.dataset.id;
        const dlabel = b.dataset.label;

        const pdfUrl = '../public/ajax/document_templates_admin.php?action=download_pdf&id=' + encodeURIComponent(did);

        // const response = await fetch(
        //   '../public/ajax/document_templates_admin.php?action=pdf&id=' + id,
        //   { method: "GET" }
        // );

        // if (!response.ok) {
        //   alert("Failed to generate PDF");
        //   return;
        // }

        // const blob = await response.blob();
        // const pdfUrl = URL.createObjectURL(blob);

        // load to iframe
        document.getElementById('dlabel').innerHTML = dlabel;
        document.getElementById('pdfFrame').src = pdfUrl;

        // show modal
        const modalPdf = new bootstrap.Modal(document.getElementById('modalPdfPreview'));
        modalPdf.show();
      };
    });


    documentsList.querySelectorAll('.btn-delete-doc').forEach(b=>{
      b.onclick = async ()=>{
        if(!confirm('Delete created document?')) return;
        const res = await fetch('../public/ajax/document_templates_admin.php?action=delete_document', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'id='+encodeURIComponent(b.dataset.id)}).then(r=>r.json());
        if(res.ok) loadDocuments();
        else alert(res.error || 'Error');
      };
    });

    documentsList.querySelectorAll('.btn-add-profile').forEach(b => {
        b.onclick = async () => {
            const id = b.dataset.id;
            const entity = b.dataset.entity;
            const identifier = b.dataset.identifier;
            const title = b.dataset.title;

            if (!identifier) return alert("Missing entity ID");

            if (!confirm("Add this document to the profile?")) return;

            const body = new URLSearchParams();
            body.append("doc_id", id);
            body.append("entity_type", entity);
            body.append("entity_identifier", identifier);
            body.append("title", title);

            const res = await fetch(
                "../public/ajax/document_templates_admin.php?action=add_to_profile",
                { method: "POST", body }
            ).then(r => r.json());

            if (res.ok) {
                loadDocuments();
                alert("Added to profile successfully.");
            } else {
                alert(res.error || "Failed adding to profile.");
            }
        };
    });


  }

  // utility: escape html
  function escapeHtml(s){ if(s==null) return ''; return String(s).replace(/[&<>"']/g, function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];}); }

  // small initialization
  document.getElementById('btnNewTemplate').onclick = ()=> {
    document.getElementById('formTemplate').reset();
    document.getElementById('template_id').value = '';
    document.querySelectorAll('#templateCategoryGroup button').forEach(b=> b.classList.remove('active'));
    document.querySelector('#templateCategoryGroup button[data-value="employee"]').classList.add('active');
    document.getElementById('template_category').value = 'employee';
    document.getElementById('modalTemplateTitle').textContent = 'New Template';
    modalTemplate.show();
  };
  document.getElementById('btnRefresh').onclick = ()=> {
    loadTemplates();
    loadDocuments();
  };

  // initial load
  loadTemplates();
  loadDocuments();

})();
</script>