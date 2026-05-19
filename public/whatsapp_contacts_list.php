<?php
// whatsapp_contacts_list.php
// Single-file page: left list of contacts (assigned = 'no'), right chat pane.
// NOTE: adapt include paths to match your project (use same includes as employees_view.php).

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';


// -- fetch contacts from DB where assigned = 'no' (case-insensitive) --
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->prepare("SELECT id, contact_name, phone, company, assigned FROM whatsapp_contacts WHERE LOWER(COALESCE(assigned,'') ) = 'no' ORDER BY contact_name ASC");
        $stmt->execute();
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif (isset($db) && is_object($db)) {
        $res = $db->query("SELECT id, contact_name, phone, company, assigned FROM whatsapp_contacts WHERE LOWER(COALESCE(assigned,'')) = 'no' ORDER BY contact_name ASC");
        $contacts = $res->fetch_all(MYSQLI_ASSOC);
    } else {
        $contacts = [];
    }
} catch (Exception $ex) {
    error_log("Failed to fetch contacts: " . $ex->getMessage());
    $contacts = [];
}

// pick first contact id for initial open (if any)
$firstContact = count($contacts) ? $contacts[0] : null;
?>
<style>
/* page specific styles - small and modern */
.whats-left {
    width: 340px;
    min-width: 240px;
    border-right: 1px solid #e9ecef;
    height: calc(100vh - 120px);
    overflow:auto;
}
.whats-right {
    flex:1;
    height: calc(100vh - 120px);
    display:flex;
    flex-direction:column;
    gap:8px;
}
.contact-item { cursor:pointer; padding:12px 14px; border-bottom:1px solid #f1f3f5; display:flex; align-items:center; gap:12px; }
.contact-item:hover { background:#f8f9fa; }
.contact-item.active { background:#e9f7ef; border-left:4px solid #1eb980; }
.contact-avatar { width:44px; height:44px; border-radius:50%; background:#dee2e6; display:flex; align-items:center; justify-content:center; font-weight:700; color:#495057; }
.contact-meta { flex:1; min-width:0; }
.contact-name { font-weight:600; font-size:0.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.contact-phone { font-size:0.85rem; color:#6c757d; }
.contact-company { font-size:0.8rem; color:#6c757d; }
.search-box { padding:10px; border-bottom:1px solid #f1f3f5; }
@media (max-width: 700px) {
    .whats-left { width:100%; height:auto; order:2; }
    .whats-right { order:1; height:auto; }
}
</style>

<div class="container-fluid py-3">
  <div class="card shadow-sm">
    <div class="card-body p-0" style="display:flex; gap:0;">
      <!-- LEFT: contacts list -->
      <div class="whats-left bg-white">
        <div class="search-box">
          <div class="d-flex gap-2 align-items-center">
            <input id="whats-search" class="form-control form-control-sm" placeholder="Search contacts (name / phone)..." />
            <button id="whats-refresh-list" class="btn btn-sm btn-outline-secondary" title="Refresh list"><i class="fa fa-sync"></i></button>
          </div>
        </div>

        <div id="contactsList">
          <?php if (!$contacts): ?>
            <div class="text-center small text-muted p-3">No contacts found.</div>
          <?php else: foreach ($contacts as $c):
                $displayName = htmlspecialchars($c['contact_name'] ?: $c['phone'] ?: 'Unknown', ENT_QUOTES);
                $phone = htmlspecialchars($c['phone'] ?? '', ENT_QUOTES);
                $company = htmlspecialchars($c['company'] ?? '', ENT_QUOTES);
          ?>
            <div class="contact-item" data-id="<?= (int)$c['id'] ?>" data-name="<?= $displayName ?>" data-phone="<?= $phone ?>">
              <div class="contact-avatar"><?= strtoupper(substr($displayName,0,1)) ?></div>
              <div class="contact-meta">
                <div class="contact-name"><?= $displayName ?></div>
                <div class="d-flex gap-2">
                  <div class="contact-phone"><?= $phone ?></div>
                  <?php if ($company): ?><div class="contact-company">· <?= $company ?></div><?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- RIGHT: chat pane -->
      <div class="whats-right p-3">
      
        <div id="whatsappCardWrapper" class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <div><i class="fa fa-whatsapp me-2 text-success"></i> <strong id="chatHeaderName">No contact</strong></div>
            <div class="d-flex align-items-center gap-2">
              <button id="openInNewWindow" class="btn btn-outline-secondary btn-sm" title="Open chat in new window"><i class="fa fa-external-link-alt"></i></button>
              <button id="whatsappRefreshBtn" class="btn btn-sm btn-outline-secondary" title="Refresh messages">
                <i class="fa fa-sync" aria-hidden="true"></i>
                <span class="ms-1">Refresh</span>
              </button>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="whatsappAutoRefreshToggle" title="Auto refresh" />
                <label class="form-check-label small text-muted" for="whatsappAutoRefreshToggle">Auto</label>
              </div>
            </div>
          </div>

          <div class="card-body d-flex flex-column" style="max-height:720px;">
            <div id="whatsappChatContainer" class="section-scroll p-2" style="flex:1; max-height:60vh; overflow:auto; display:flex; flex-direction:column;">
              <div id="whats-load-older-container" style="width:100%; text-align:center; margin-bottom:10px;">
                <button id="whats-load-older" class="btn btn-sm btn-outline-secondary">Load older</button>
              </div>
              <div id="whatsappMessages" style="display:flex; flex-direction:column; gap:10px;"></div>
            </div>

            <!-- Composer area -->
            <div id="whatsappComposer" class="mt-2 p-2 border-top bg-white">
              <form id="whatsapp-send-form" class="d-flex flex-column gap-2" enctype="multipart/form-data">
                <div class="d-flex gap-2 align-items-start">
                  <textarea id="whatsappMessageInput" name="message" class="form-control form-control-sm" placeholder="Write a message..." rows="4" required></textarea>

                  <div class="d-flex flex-column gap-2" style="min-width:160px;">
                    <label class="btn btn-outline-secondary btn-sm mb-0" style="cursor:pointer;">
                      <i class="fa fa-paperclip"></i> Attach
                      <input type="file" id="whatsappFiles" name="media[]" multiple style="display:none" accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip,text/plain">
                    </label>

                    <button type="button" id="whatsappTemplateBtn" class="btn btn-outline-secondary btn-sm mb-0" title="Use template">
                      <i class="fa fa-file-alt"></i> Template
                    </button>

                    <button type="submit" id="whatsappSendBtn" class="btn btn-primary btn-sm"><i class="fa fa-paper-plane me-1"></i> Send</button>
                  </div>
                </div>

                <div id="whatsappAttachPreview" class="d-flex flex-wrap gap-2 small"></div>
              </form>
            </div>

          </div>
        </div>

        <!-- Template modal -->
        <div class="modal fade" id="whatsappTemplateModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Select WhatsApp Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div id="templateLoading" class="small text-muted">Loading templates…</div>
                <div id="templateList" class="my-2 d-flex flex-wrap gap-1" style="max-height:220px; overflow:auto;"></div>
                <div id="templatePreviewWrap" style="display:none;">
                  <h6 class="mt-3">Preview</h6>
                  <div id="templatePreview" class="p-2 border rounded small" style="background:#f8f9fa; min-height:80px; white-space:pre-wrap;"></div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" id="templateCancelBtn" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="templateSendBtn" class="btn btn-primary btn-sm">Send Template</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Attachment modal -->
        <div class="modal fade" id="whatsappAttachModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-paperclip"></i> Add caption & send</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div id="attachList" class="d-flex flex-column gap-2"></div>
                <div class="small text-muted mt-2">You can send images or documents. Add caption for each file before sending.</div>
              </div>
              <div class="modal-footer">
                <button id="attachModalCancel" type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="attachModalSend" type="button" class="btn btn-primary">Send</button>
              </div>
            </div>
          </div>
        </div>

      </div> <!-- end right -->
    </div>
  </div>
</div>

<!-- small session banner used by the session-check script -->
<div id="sessionBanner" style="display:none"></div>

<!-- CHAT STYLES (from your original CSS) -->
<style>
.btn-outline-secondary.btn-primary { color: #fff !important; background: #6c757d !important; }
.form-check-input[type=radio] { border-color: #000; }
#whatsappMessages { width:100%; display:flex; flex-direction:column; gap:10px; }
.msg-row { display:flex; width:100%; }
.msg-row.incoming { justify-content:flex-start; }
.msg-row.outgoing { justify-content:flex-end; }
.bubble-wrap { display:block; width:50%; box-sizing:border-box; }
.chat-bubble { padding: 10px 12px; border-radius: 12px; font-size:.95rem; line-height:1.25; box-shadow: 0 1px 2px rgba(0,0,0,0.04); word-break: break-word; }
.chat-bubble.incoming { background: #f1f0f0; color: #111; border-top-left-radius: 6px; border-top-right-radius: 12px; border-bottom-right-radius: 12px; border-bottom-left-radius: 12px; margin-right: 10px; }
.chat-bubble.outgoing { background: #dcf8c6; color: #111; border-top-right-radius: 6px; border-top-left-radius: 12px; border-bottom-right-radius: 12px; border-bottom-left-radius: 12px; margin-left: 10px; }
.chat-meta { font-size:.75rem; color:#666; margin-top:6px; }
.template-btn-pill { border-radius: 20px !important; padding: 2px 10px !important; font-size: 0.78rem !important; }
#sessionBanner { display:none; margin-bottom: 8px; padding: 8px 12px; border-radius: 6px; background: #fff3cd; color: #856404; border: 1px solid #ffeeba; font-size: 0.92rem; }
#whatsappMessageInput[disabled] { background:#f8f9fa; opacity:0.9; }
.chat-media img { box-shadow: 0 1px 3px rgba(0,0,0,0.12); }
.chat-media img { max-width: 240px; max-height: 320px; border-radius: 8px; display:block; }
.attach-item { display:flex; gap:10px; align-items:flex-start; padding:8px; border-radius:6px; background:#f8f9fa; }
.attach-thumb { width:80px; height:80px; object-fit:cover; border-radius:6px; background:#eee; flex:0 0 80px; }
.attach-meta { flex:1; display:flex; flex-direction:column; gap:6px; }
.attach-filename { font-weight:600; font-size:0.95rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.attach-actions { display:flex; gap:6px; align-items:center; }
.attach-caption { width:100%; min-height:44px; }
@media (max-width: 520px) { .bubble-wrap { width:100%; } }
</style>

<!-- BEGIN: message fetch + render (uses window.supplierId) -->
<script>
window.supplierId = <?= $firstContact ? (int)$firstContact['id'] : 0 ?>;
(function(){
  const LIMIT = 20;
  let offset = 0;
  let hasMore = true;
  let loading = false;
  const loadedIds = new Set();

  const messagesContainer = document.getElementById('whatsappMessages');
  const scrollWrap = document.getElementById('whatsappChatContainer');
  const loadOlderBtn = document.getElementById('whats-load-older');

  function fmtDateTime(dtStr){
    if(!dtStr) return '';
    const d = new Date(dtStr.replace(' ', 'T'));
    if (isNaN(d)) return dtStr;
    const pad = n => n < 10 ? '0'+n : n;
    return `${pad(d.getDate())}-${pad(d.getMonth()+1)}-${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }

  function createMessageNode(msg){
    const dirRaw = (msg.direction || '').toString().toLowerCase();
    const dir = (dirRaw.indexOf('out') !== -1 || dirRaw === 'outgoing' || dirRaw === 'sent') ? 'outgoing' : 'incoming';
    const row = document.createElement('div');
    row.className = 'msg-row ' + dir;
    row.dataset.id = msg.id;

    const wrap = document.createElement('div');
    wrap.className = 'bubble-wrap';

    const bubble = document.createElement('div');
    bubble.className = 'chat-bubble ' + dir;

    const type = (msg.msg_type || msg.type || '').toString().toLowerCase();
    const makeTextDiv = (txt) => { const d = document.createElement('div'); d.textContent = txt; return d; };

    if (type === 'image' && (msg.media_fileUrl || msg.document_fileUrl)) {
      const src = msg.media_fileUrl || msg.document_fileUrl;
      const imgWrap = document.createElement('div');
      imgWrap.className = 'chat-media chat-media-image';
      const img = document.createElement('img');
      img.src = src;
      img.alt = msg.document_caption || msg.message_body || 'image';
      img.loading = 'lazy';
      img.style.maxWidth = '240px';
      img.style.maxHeight = '320px';
      img.style.display = 'block';
      img.style.borderRadius = '8px';
      imgWrap.appendChild(img);
      const cap = (msg.document_caption || msg.message_body || '').toString().trim();
      if (cap) { const c = document.createElement('div'); c.className = 'chat-media-caption'; c.textContent = cap; imgWrap.appendChild(c); }
      bubble.appendChild(imgWrap);
    } else if (type === 'document' && (msg.document_fileUrl || msg.media_fileUrl)) {
      const docUrl = msg.document_fileUrl || msg.media_fileUrl;
      const docWrap = document.createElement('div');
      docWrap.className = 'chat-media chat-media-doc';
      const a = document.createElement('a');
      a.href = docUrl; a.target = '_blank'; a.rel = 'noopener noreferrer';
      a.textContent = (msg.document_caption || docUrl.split('/').pop() || 'Download document');
      a.title = 'Open document in new tab'; a.className = 'chat-doc-link';
      docWrap.appendChild(a);
      const extraCap = (msg.message_body || '').toString().trim();
      if (extraCap) { const e = document.createElement('div'); e.className = 'chat-media-caption'; e.textContent = extraCap; docWrap.appendChild(e); }
      bubble.appendChild(docWrap);
    } else {
      const body = document.createElement('div');
      body.innerHTML = (msg.message_body || '').replace(/\n/g, '<br>');
      bubble.appendChild(body);
    }

    const title = (msg.interactive_reply_title || msg.interactiveTitle || '').toString().trim();
    const desc = (msg.interactive_reply_description || msg.interactiveDesc || '').toString().trim();
    if (title) { const t = document.createElement('div'); t.className = 'interactive-title'; t.textContent = title; bubble.appendChild(t); }
    if (desc) { const d = document.createElement('div'); d.className = 'interactive-desc'; d.innerHTML = desc.replace(/\n/g, '<br>'); bubble.appendChild(d); }

    const meta = document.createElement('div'); meta.className = 'chat-meta';
    meta.textContent = fmtDateTime(msg.date_added || msg.date || msg.created_at || '');
    bubble.appendChild(meta);

    wrap.appendChild(bubble);
    row.appendChild(wrap);
    return row;
  }

  function renderMessages(messages, prependOlder=false){
    if (!Array.isArray(messages) || messages.length === 0) return;
    messages.sort(function(a,b){
      const ta = new Date(a.date_added || a.date || a.created_at || 0).getTime() || 0;
      const tb = new Date(b.date_added || b.date || b.created_at || 0).getTime() || 0;
      return ta - tb;
    });

    if (prependOlder) {
      const prevScrollHeight = scrollWrap.scrollHeight;
      messages.forEach(m => {
        if (loadedIds.has(String(m.id))) return;
        const node = createMessageNode(m);
        messagesContainer.insertBefore(node, messagesContainer.firstChild);
        loadedIds.add(String(m.id));
      });
      requestAnimationFrame(() => {
        const newScrollHeight = scrollWrap.scrollHeight;
        scrollWrap.scrollTop = newScrollHeight - prevScrollHeight;
      });
    } else {
      messages.forEach(m => {
        if (loadedIds.has(String(m.id))) return;
        const node = createMessageNode(m);
        messagesContainer.appendChild(node);
        loadedIds.add(String(m.id));
      });
      requestAnimationFrame(()=> { scrollWrap.scrollTop = scrollWrap.scrollHeight; });
    }
  }

  function fetchWhatsApp(prependOlder=false){
    if (loading) return;
    loading = true;
    loadOlderBtn.disabled = true;
    loadOlderBtn.innerText = 'Loading...';

    const cid = (typeof window.supplierId !== 'undefined') ? window.supplierId : 0;
    if (!cid) {
      loadOlderBtn.innerText = 'No messages';
      loadOlderBtn.disabled = true;
      loading = false;
      return;
    }

    fetch('public/ajax/contacts_get_whatsapp.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `contact_id=${encodeURIComponent(cid)}&offset=${encodeURIComponent(offset)}&limit=${encodeURIComponent(LIMIT)}&contact_type=${encodeURIComponent('Contact')}`
    }).then(r => r.json()).then(res => {
      const msgs = res && (res.messages || res.results || []) ? (res.messages || res.results || []) : [];
      if (msgs.length) {
        renderMessages(msgs, prependOlder);
        offset += msgs.length;
      }
      hasMore = (typeof res.hasMore !== 'undefined') ? !!res.hasMore : (msgs.length === LIMIT);
      if (hasMore) {
        loadOlderBtn.style.display = 'inline-block';
        loadOlderBtn.disabled = false;
        loadOlderBtn.innerText = 'Load older';
      } else {
        loadOlderBtn.style.display = 'inline-block';
        loadOlderBtn.disabled = true;
        loadOlderBtn.innerText = (offset > 0 ? 'No older messages' : 'No messages');
      }
    }).catch(err => {
      console.error('Failed to fetch whatsapp messages', err);
      loadOlderBtn.innerText = 'Error, retry';
      loadOlderBtn.disabled = false;
    }).finally(()=> { loading = false; });
  }

  loadOlderBtn.addEventListener('click', function(){
    if (!hasMore || loading) return;
    fetchWhatsApp(true);
  });

  window.refreshWhatsAppChat = function(){
    offset = 0;
    loadedIds.clear();
    messagesContainer.innerHTML = '';
    fetchWhatsApp(false);
  };

  document.addEventListener('DOMContentLoaded', function(){
    if (typeof window.supplierId !== 'undefined' && window.supplierId) {
      window.refreshWhatsAppChat();
    }
  });

})();
</script>
<!-- END message fetch + render -->

<!-- BEGIN: send logic -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const sendForm = document.getElementById('whatsapp-send-form');
  const msgInp = document.getElementById('whatsappMessageInput');
  const filesInput = document.getElementById('whatsappFiles');
  const previewWrap = document.getElementById('whatsappAttachPreview');
  const sendBtn = document.getElementById('whatsappSendBtn');
  const messagesContainer = document.getElementById('whatsappMessages');
  const chatWrap = document.getElementById('whatsappChatContainer');

  if (!sendForm || !msgInp || !sendBtn || !messagesContainer || !chatWrap) {
    console.error('One or more required elements not found, aborting send script.');
    return;
  }

  const WHATSAPP_SEND_ENDPOINT = '../Whatsapp/sendAgentMessageAPI.php';

  function setChatBubbleContent(node, text, isoDate) {
    const bubble = node.querySelector('.chat-bubble') || node;
    const bodyDiv = bubble.querySelector(':scope > div:not(.chat-meta)') || bubble.querySelector('div');
    if (bodyDiv) bodyDiv.textContent = text;
    const meta = bubble.querySelector('.chat-meta');
    if (meta) {
      const dt = new Date(isoDate || Date.now());
      meta.textContent = dt.toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
  }

  function buildOutgoingNode(msg, { temp=false } = {}) {
    let sample = messagesContainer.querySelector('.msg-row.outgoing');
    if (!sample) {
      const bubbleSample = messagesContainer.querySelector('.chat-bubble.outgoing');
      if (bubbleSample) sample = bubbleSample.closest('.msg-row') || bubbleSample;
    }
    if (sample) {
      const node = sample.cloneNode(true);
      node.removeAttribute('id');
      if (temp) node.dataset.tempId = msg.id; else node.dataset.id = msg.id;
      node.classList.add('outgoing');
      const bubble = node.querySelector('.chat-bubble');
      if (bubble) bubble.classList.add('outgoing');
      setChatBubbleContent(node, msg.message_body || msg.message || '', msg.date_added);
      node.classList.remove('failed', 'pending');
      return node;
    }

    const node = document.createElement('div');
    node.className = 'msg-row outgoing';
    if (temp) node.dataset.tempId = msg.id; else node.dataset.id = msg.id;
    const wrap = document.createElement('div'); wrap.className = 'bubble-wrap';
    const bubble = document.createElement('div'); bubble.className = 'chat-bubble outgoing';
    const body = document.createElement('div'); body.textContent = msg.message_body || msg.message || '';
    const meta = document.createElement('div'); meta.className = 'chat-meta';
    const dt = new Date(msg.date_added || Date.now());
    meta.textContent = dt.toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    bubble.appendChild(body); bubble.appendChild(meta);
    wrap.appendChild(bubble); node.appendChild(wrap);
    return node;
  }

  function appendOutgoingMessage(msg, opts = { temp: false }) {
    if (typeof createMessageNode === 'function') {
      try {
        const node = createMessageNode({
          id: msg.id || ('tmp-' + Date.now()),
          message_body: msg.message_body || msg.message || '',
          direction: 'outgoing',
          date_added: msg.date_added || (new Date()).toISOString(),
          meta: msg.meta || {}
        });
        if (opts.temp) { node.classList.add('temp','pending'); node.dataset.tempId = msg.id; } else node.dataset.id = msg.id;
        messagesContainer.appendChild(node);
        requestAnimationFrame(()=> { chatWrap.scrollTop = chatWrap.scrollHeight; });
        return node;
      } catch (err) {
        console.warn('createMessageNode threw — falling back to clone', err);
      }
    }

    const node = buildOutgoingNode(msg, opts);
    if (opts.temp) { node.classList.add('temp','pending'); node.dataset.tempId = msg.id; } else node.dataset.id = msg.id;
    messagesContainer.appendChild(node);
    requestAnimationFrame(()=> { chatWrap.scrollTop = chatWrap.scrollHeight; });
    return node;
  }

  function updateTempNodeWithServerResponse(tempId, serverId, serverMeta) {
    const sel = `[data-temp-id="${CSS.escape(tempId)}"]`;
    const tempNode = messagesContainer.querySelector(sel);
    if (!tempNode) return false;
    tempNode.dataset.id = serverId;
    tempNode.removeAttribute('data-temp-id');
    tempNode.classList.remove('temp','pending');
    if (serverMeta && serverMeta.status) tempNode.dataset.serverStatus = serverMeta.status;
    return true;
  }

  function doSend(message) {
    const tmpId = 'tmp-' + Date.now();
    const tempMsg = { id: tmpId, message_body: message, date_added: (new Date()).toISOString() };
    appendOutgoingMessage(tempMsg, { temp: true });

    sendBtn.disabled = true;
    const origBtnHTML = sendBtn.innerHTML;
    sendBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';

    const fd = new FormData();
    const cid = (typeof window.supplierId !== 'undefined') ? window.supplierId : 0;
    fd.append('contact_id', cid);
    fd.append('message', message);
    fd.append('contact_type', 'Contact');
    if (filesInput && filesInput.files) { Array.from(filesInput.files).forEach((f, i) => fd.append('media[]', f, f.name)); }

    fetch(WHATSAPP_SEND_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(async r => { const text = await r.text(); try { return JSON.parse(text); } catch (err) { return { error: 'Invalid JSON', raw: text }; } })
      .then(res => {
        const statusVal = res && (res.status ?? res.code ?? res.statusCode);
        const isSuccess = (() => {
          if (statusVal === undefined || statusVal === null) return !!res.success;
          const s = String(statusVal);
          return /^2\d\d$/.test(s);
        })();

        if (isSuccess) {
          const serverId = res.messageId || res.id || ('id-' + Date.now());
          const ok = updateTempNodeWithServerResponse(tmpId, serverId, res);
          if (!ok) {
            appendOutgoingMessage({ id: serverId, message_body: message, date_added: res.date_added || new Date().toISOString() });
          }
          msgInp.value = '';
          if (filesInput) filesInput.value = '';
          if (previewWrap) previewWrap.innerHTML = '';
        } else {
          const sel = `[data-temp-id="${CSS.escape(tmpId)}"]`;
          const tempNode = messagesContainer.querySelector(sel);
          if (tempNode) tempNode.classList.remove('pending'), tempNode.classList.add('failed');
          const errMsg = res && (res.error || res.message || res.raw) || 'Failed to send message';
          alert(errMsg);
          console.error('Send error response:', res);
        }
      }).catch(err => {
        console.error('Send failed', err);
        const sel = `[data-temp-id="${CSS.escape(tmpId)}"]`;
        const tempNode = messagesContainer.querySelector(sel);
        if (tempNode) tempNode.classList.remove('pending'), tempNode.classList.add('failed');
        alert('Error sending message. See console.');
      }).finally(() => {
        sendBtn.disabled = false;
        sendBtn.innerHTML = origBtnHTML;
      });
  }

  sendForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const message = msgInp.value.trim();
    if (!message && (!filesInput || !filesInput.files || filesInput.files.length === 0)) {
      alert('Please enter a message or attach file(s).');
      return;
    }
    const cid = (typeof window.supplierId !== 'undefined') ? window.supplierId : 0;
    if (cid === 0) {
      doSend(message); return;
    }

    fetch(
        'public/ajax/check_session.php?contact_id=' 
        + encodeURIComponent(cid)
        + '&contact_type=' 
        + encodeURIComponent('Customer'),
        { credentials: 'same-origin' }
      ).then(r => r.json())
      .then(data => {
        if (data && data.allow_normal) doSend(message);
        else alert("Normal messages not allowed because customer session expired.\nPlease use a WhatsApp template.");
      }).catch(err => {
        console.warn('Session check failed — allowing send by default', err);
        doSend(message);
      });
  });

  window.appendOutgoingMessage = appendOutgoingMessage;
  window.updateTempNodeWithServerResponse = updateTempNodeWithServerResponse;

});
</script>
<!-- END: send logic -->

<!-- BEGIN: Template modal script (copied from your code exactly) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const templateBtn = document.getElementById('whatsappTemplateBtn');
  const templateModalEl = document.getElementById('whatsappTemplateModal');
  const templateList = document.getElementById('templateList');
  const templateLoading = document.getElementById('templateLoading');
  const templatePreviewWrap = document.getElementById('templatePreviewWrap');
  const templatePreview = document.getElementById('templatePreview');
  const templateSendBtn = document.getElementById('templateSendBtn');
  const templateCancelBtn = document.getElementById('templateCancelBtn');

  if (!templateBtn) { console.warn('Template button not found'); return; }
  if (!templateModalEl) { console.warn('Template modal not found'); return; }
  if (!templateList || !templateLoading || !templatePreviewWrap || !templatePreview || !templateSendBtn) {
    console.warn('Template modal required elements missing'); return;
  }

  let bsModal = null;
  if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
    bsModal = new bootstrap.Modal(templateModalEl, { keyboard: true, focus: true });
  }

  const TEMPLATES_ENDPOINT = 'public/ajax/get_whatsapp_templates.php';
  const SEND_ENDPOINT = typeof WHATSAPP_SEND_ENDPOINT !== 'undefined' ? WHATSAPP_SEND_ENDPOINT : '../Whatsapp/sendAgentTemplateAPI.php';
  let currentTemplate = null;

  function openModal() {
    templateList.innerHTML = '';
    templatePreviewWrap.style.display = 'none';
    templateLoading.style.display = 'block';
    templateList.style.display = 'none';
    currentTemplate = null;
    if (bsModal) bsModal.show();
    else { templateModalEl.classList.add('show'); templateModalEl.style.display = 'block'; templateModalEl.removeAttribute('aria-hidden'); templateModalEl.setAttribute('tabindex','-1'); templateModalEl.focus(); }
    loadTemplates();
  }

  function closeModal() {
    if (bsModal) bsModal.hide();
    else { templateModalEl.classList.remove('show'); templateModalEl.style.display = 'none'; templateModalEl.setAttribute('aria-hidden','true'); }
  }

  async function loadTemplates() {
    try {
      templateLoading.textContent = 'Loading templates…';
      templateLoading.style.display = 'block';
      templateList.style.display = 'none';

      const res = await fetch(TEMPLATES_ENDPOINT, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('Network response not ok: ' + res.status);
      const data = await res.json();
      const templates = data && (data.templates || []);
      templateList.innerHTML = '';

      if (!templates.length) {
        templateList.innerHTML = '<div class="small text-muted p-2">No templates found.</div>';
      } else {
        templates.forEach(t => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn btn-outline-primary btn-sm template-btn-pill me-1 mb-1';
          btn.textContent = t.name || ('Template #' + (t.id || ''));
          btn.dataset.templateId = t.id || '';
          btn.dataset.templateContent = t.content || '';
          btn.addEventListener('click', function () {
            selectTemplate(t);
          });
          templateList.appendChild(btn);
        });
      }
    } catch (err) {
      console.error('Failed to load templates', err);
      templateList.innerHTML = '<div class="small text-danger p-2">Failed to load templates. See console for details.</div>';
    } finally {
      templateLoading.style.display = 'none';
      templateList.style.display = 'block';
    }
  }

  function selectTemplate(tpl) {
    currentTemplate = tpl;
    templatePreview.textContent = tpl.content || '';
    templatePreviewWrap.style.display = 'block';
    templateSendBtn.focus();
  }

  templateBtn.addEventListener('click', function (e) {
    e.preventDefault();
    openModal();
  });

  templateSendBtn.addEventListener('click', async function () {
  if (!currentTemplate) { alert('Please select a template first.'); return; }
  const finalText = (currentTemplate.content || '').trim();
  if (!finalText) { alert('Template content is empty.'); return; }

  const tmpId = 'tmp-' + Date.now();

  const messagesContainerEl = document.getElementById('whatsappMessages');
  const chatWrapEl = document.getElementById('whatsappChatContainer');

  (function optimisticAppend() {
    try {
      if (typeof appendOutgoingMessage === 'function') {
        appendOutgoingMessage({ id: tmpId, message_body: finalText, date_added: (new Date()).toISOString() }, { temp: true });
        return;
      }
      if (typeof createMessageNode === 'function') {
        const node = createMessageNode({
          id: tmpId,
          message_body: finalText,
          direction: 'outgoing',
          date_added: (new Date()).toISOString()
        });
        node.classList.add('temp', 'pending');
        node.dataset.tempId = tmpId;
        if (messagesContainerEl) {
          messagesContainerEl.appendChild(node);
          requestAnimationFrame(()=> { if (chatWrapEl) chatWrapEl.scrollTop = chatWrapEl.scrollHeight; });
        }
        return;
      }

      function createFallbackOutgoingNode(msg, { temp=false } = {}) {
        const node = document.createElement('div');
        node.className = 'msg-row outgoing';
        if (temp) node.dataset.tempId = msg.id; else node.dataset.id = msg.id;
        const wrap = document.createElement('div');
        wrap.className = 'bubble-wrap';
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble outgoing';
        const body = document.createElement('div');
        body.textContent = msg.message_body || '';
        const meta = document.createElement('div');
        meta.className = 'chat-meta';
        try {
          meta.textContent = new Date(msg.date_added || Date.now()).toLocaleString('en-GB', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
        } catch (e) {
          meta.textContent = msg.date_added || '';
        }
        bubble.appendChild(body);
        bubble.appendChild(meta);
        wrap.appendChild(bubble);
        node.appendChild(wrap);
        node.classList.add('pending');
        return node;
      }

      const fallbackNode = createFallbackOutgoingNode({ id: tmpId, message_body: finalText, date_added: (new Date()).toISOString() }, { temp: true });
      if (messagesContainerEl) {
        messagesContainerEl.appendChild(fallbackNode);
        requestAnimationFrame(()=> { if (chatWrapEl) chatWrapEl.scrollTop = chatWrapEl.scrollHeight; });
      }

    } catch (err) {
      console.warn('Optimistic append failed', err);
    }
  })();

  const fd = new FormData();
  if (typeof supplierId !== 'undefined') fd.append('contact_id', supplierId);
  fd.append('message', finalText);
  fd.append('template_id', currentTemplate.tmp_id || '');
  fd.append('template_name', currentTemplate.name || '');
  fd.append('is_template', '1');
  fd.append('contact_type', 'Contact');

  templateSendBtn.disabled = true;
  const oldHTML = templateSendBtn.innerHTML;
  templateSendBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';

  try {
    const r = await fetch(SEND_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' });
    const text = await r.text();
    let res;
    try { res = JSON.parse(text); } catch (e) { res = { error: 'Invalid JSON', raw: text }; }

    const statusVal = res && (res.status ?? res.code ?? res.statusCode);
    const isSuccess = (statusVal === undefined || statusVal === null) ? !!res.success : /^2\d\d$/.test(String(statusVal));

    if (isSuccess) {
      const serverId = res.messageId || res.id || ('id-' + Date.now());
      let updated = false;
      try {
        if (typeof updateTempNodeWithServerResponse === 'function') {
          updated = updateTempNodeWithServerResponse(tmpId, serverId, res) || false;
        }
      } catch (e) { console.warn('updateTempNodeWithServerResponse threw', e); }

      if (!updated) {
        const sel = `[data-temp-id="${CSS.escape(tmpId)}"]`;
        const tempNode = document.getElementById('whatsappMessages')?.querySelector(sel);
        if (tempNode) {
            tempNode.classList.remove('pending');
            tempNode.dataset.id = serverId;
            tempNode.removeAttribute('data-temp-id');
        }
      }

      try {
        const toastEl = document.getElementById('globalToast');
        if (toastEl && window.bootstrap && typeof bootstrap.Toast === 'function') {
          const body = toastEl.querySelector('.toast-body');
          if (body) body.textContent = res.message || 'Message sent successfully';
          toastEl.style.display = '';
          const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
          toast.show();
        } else if (toastEl) {
          toastEl.style.display = '';
          const body = toastEl.querySelector('.toast-body');
          if (body) body.textContent = res.message || 'Message sent successfully';
          setTimeout(()=> { toastEl.style.display = 'none'; }, 3000);
        } else {
          alert(res.message || 'Message sent successfully');
        }
      } catch (e) {
        console.warn('Toast display failed', e);
      }

      if (typeof closeModal === 'function') closeModal();
    } else {
      try {
        const sel = `[data-temp-id="${CSS.escape(tmpId)}"]`;
        const tempNode = document.getElementById('whatsappMessages')?.querySelector(sel);
        if (tempNode) tempNode.classList.remove('pending'), tempNode.classList.add('failed');
      } catch (e) { }
      const errMsg = res && (res.error || res.message || res.raw) || 'Failed to send template';
      alert(errMsg);
    }
  } catch (err) {
    console.error('Template send failed', err);
    try {
      const sel = `[data-temp-id="${CSS.escape(tmpId)}"]`;
      const tempNode = document.getElementById('whatsappMessages')?.querySelector(sel);
      if (tempNode) tempNode.classList.remove('pending'), tempNode.classList.add('failed');
    } catch (e){}
    alert('Error sending template. See console.');
  } finally {
    templateSendBtn.disabled = false;
    templateSendBtn.innerHTML = oldHTML;
  }
});

  if (templateCancelBtn) {
    templateCancelBtn.addEventListener('click', function () {
      try { templateBtn.focus(); } catch (e) {}
    });
  }

  if (templateModalEl && bsModal) {
    templateModalEl.addEventListener('hidden.bs.modal', function () {
      templatePreviewWrap.style.display = 'none';
      currentTemplate = null;
      try { templateBtn.focus(); } catch (e) {}
    });
  }
});
</script>
<!-- END: Template modal script -->

<!-- BEGIN: session-check block (copied from your code) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const contactId = (typeof window.supplierId !== 'undefined') ? Number(window.supplierId) : 0;

  const input = document.getElementById('whatsappMessageInput');
  const sendBtn = document.getElementById('whatsappSendBtn');
  const sendFormEl = document.getElementById('whatsapp-send-form');
  const filesInputEl = document.getElementById('whatsappFiles');

  let banner = document.getElementById('sessionBanner');
  if (!banner) {
    banner = document.createElement('div');
    banner.id = 'sessionBanner';
    const composer = document.getElementById('whatsappComposer') || document.getElementById('whatsapp-send-form')?.parentNode;
    if (composer && composer.parentNode) composer.parentNode.insertBefore(banner, composer);
    else document.body.insertBefore(banner, document.body.firstChild);
  }

  let normalAllowed = true;
  function setNormalAllowed(allow, reason) {
    normalAllowed = !!allow;
    if (!allow) {
      if (input) input.setAttribute('disabled','disabled');
      if (sendBtn) sendBtn.setAttribute('disabled','disabled');
      banner.textContent = 'Session expired — only template messages are allowed.';
      banner.style.display = 'block';
      banner.setAttribute('role','status');
    } else {
      if (input) input.removeAttribute('disabled');
      if (sendBtn) sendBtn.removeAttribute('disabled');
      banner.style.display = 'none';
    }
  }

  async function checkSessionAndToggle() {
    const cid = (typeof window.supplierId !== 'undefined') ? Number(window.supplierId) : 0;
    if (!cid) {
      setNormalAllowed(true);
      return;
    }
    try {
      const res = await fetch(
    'public/ajax/check_session.php?contact_id=' 
    + encodeURIComponent(cid)
    + '&contact_type=' 
    + encodeURIComponent('Customer'),
    { credentials: 'same-origin' }
);
      if (!res.ok) throw new Error('Network');
      const data = await res.json();
      setNormalAllowed(!!data.allow_normal, data.reason || '');
    } catch (err) {
      console.error('Session check failed', err);
      setNormalAllowed(true);
    }
  }

  if (sendFormEl) {
    sendFormEl.addEventListener('submit', function(ev) {
      const message = input ? input.value.trim() : '';
      const hasFiles = filesInputEl && filesInputEl.files && filesInputEl.files.length > 0;

      if (message && !hasFiles && !normalAllowed) {
        ev.preventDefault();
        alert('Normal messages are blocked because the session has expired. Please use a WhatsApp template.');
        return false;
      }
    });
  }

  checkSessionAndToggle();
  window.checkWhatsAppSession = checkSessionAndToggle;
});
</script>
<!-- END: session-check block -->

<!-- BEGIN: attach modal + attach preview script (copied from your code) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const filesInput = document.getElementById('whatsappFiles');
  const attachPreviewWrap = document.getElementById('whatsappAttachPreview');
  const sendEndpoint = typeof WHATSAPP_SEND_ENDPOINT !== 'undefined' ? WHATSAPP_SEND_ENDPOINT : '../Whatsapp/sendAgentAttachAPI.php';
  const contactId = (typeof supplierId !== 'undefined') ? supplierId : 0;

  const attachModalEl = document.getElementById('whatsappAttachModal');
  if (!attachModalEl) return;
  let attachModal = null;
  if (window.bootstrap && typeof bootstrap.Modal === 'function') {
    attachModal = new bootstrap.Modal(attachModalEl, { keyboard: true, backdrop: 'static' });
  }

  const attachList = document.getElementById('attachList');
  const attachSendBtn = document.getElementById('attachModalSend');
  const attachCancelBtn = document.getElementById('attachModalCancel');

  let selectedFiles = [];

  function createAttachRow(item) {
    const row = document.createElement('div');
    row.className = 'attach-item';
    row.dataset.tmpId = item.id;

    const thumb = document.createElement('img');
    thumb.className = 'attach-thumb';
    if (item.file.type && item.file.type.indexOf('image/') === 0) {
      const url = URL.createObjectURL(item.file);
      thumb.src = url;
      thumb.alt = item.file.name;
      thumb.onload = () => URL.revokeObjectURL(url);
    } else {
      thumb.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(
        `<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24"><rect width="100%" height="100%" fill="#f0f0f0"/><text x="50%" y="50%" font-size="10" text-anchor="middle" fill="#999" dy=".35em">DOC</text></svg>`
      );
      thumb.alt = 'doc';
    }
    row.appendChild(thumb);

    const meta = document.createElement('div');
    meta.className = 'attach-meta';

    const name = document.createElement('div');
    name.className = 'attach-filename';
    name.textContent = item.file.name;
    meta.appendChild(name);

    const caption = document.createElement('textarea');
    caption.className = 'form-control form-control-sm attach-caption';
    caption.placeholder = 'Add a caption (optional)';
    caption.value = item.caption || '';
    caption.addEventListener('input', function () {
      item.caption = this.value;
    });
    meta.appendChild(caption);

    row.appendChild(meta);

    const actions = document.createElement('div');
    actions.className = 'attach-actions';
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-danger btn-sm';
    btn.title = 'Remove';
    btn.innerHTML = '<i class="fa fa-trash"></i>';
    btn.addEventListener('click', function () {
      selectedFiles = selectedFiles.filter(f => f.id !== item.id);
      row.remove();
      syncPreviewArea();
    });
    actions.appendChild(btn);
    row.appendChild(actions);

    return row;
  }

  function syncPreviewArea() {
    if (!attachPreviewWrap) return;
    attachPreviewWrap.innerHTML = '';
    if (!selectedFiles.length) return;
    selectedFiles.forEach(it => {
      const pill = document.createElement('div');
      pill.className = 'badge bg-light text-dark border';
      pill.style.marginRight = '6px';
      pill.style.display = 'inline-flex';
      pill.style.alignItems = 'center';
      pill.style.gap = '6px';
      pill.innerHTML = `<span style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;">${it.file.name}</span>`;
      const rm = document.createElement('button');
      rm.type = 'button';
      rm.className = 'btn btn-sm btn-link text-danger p-0';
      rm.style.marginLeft = '6px';
      rm.innerHTML = '<i class="fa fa-times"></i>';
      rm.addEventListener('click', function () {
        selectedFiles = selectedFiles.filter(f => f.id !== it.id);
        const node = attachList.querySelector(`[data-tmp-id="${CSS.escape(it.id)}"]`);
        if (node) node.remove();
        syncPreviewArea();
      });
      pill.appendChild(rm);
      attachPreviewWrap.appendChild(pill);
    });
  }

  if (filesInput) {
    filesInput.addEventListener('change', function (e) {
      const files = Array.from(filesInput.files || []);
      if (!files.length) return;
      files.forEach(f => {
        const id = 'f' + Date.now().toString(36) + Math.floor(Math.random()*1000).toString(36);
        selectedFiles.push({ file: f, id: id, caption: '' });
      });
      attachList.innerHTML = '';
      selectedFiles.forEach(it => attachList.appendChild(createAttachRow(it)));
      syncPreviewArea();
      if (attachModal) attachModal.show();
      else attachModalEl.style.display = 'block';
      filesInput.value = '';
    });
  }

  attachSendBtn.addEventListener('click', async function () {
    if (!selectedFiles.length) return;
    attachSendBtn.disabled = true;
    attachSendBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';

    const fd = new FormData();
    if (contactId) fd.append('contact_id', contactId);
    fd.append('contact_type', 'Contact');
    fd.append('is_media', '1');

    selectedFiles.forEach((it, idx) => {
      fd.append('media[]', it.file, it.file.name);
      fd.append('caption[]', it.caption || '');
    });

    const tmpIds = selectedFiles.map(it => 'tmp-' + it.id);
    selectedFiles.forEach((it, idx) => {
      const tmpMsg = {
        id: tmpIds[idx],
        message_body: it.caption || ('[' + it.file.name + ']'),
        date_added: (new Date()).toISOString()
      };
      if (typeof appendOutgoingMessage === 'function') appendOutgoingMessage(tmpMsg, { temp: true });
    });

    try {
      const resRaw = await fetch(sendEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' });
      const text = await resRaw.text();
      let res;
      try { res = JSON.parse(text); } catch (e) { res = { error: 'Invalid JSON', raw: text }; }

      let messageIds = [];
      if (Array.isArray(res.messageIds)) messageIds = res.messageIds;
      else if (Array.isArray(res.ids)) messageIds = res.ids;
      else if (res.messageId) messageIds = [res.messageId];
      else if (res.id) messageIds = [res.id];

      if (messageIds.length >= tmpIds.length) {
        for (let i=0;i<tmpIds.length;i++) {
          try {
            updateTempNodeWithServerResponse(tmpIds[i], messageIds[i], res) || null;
          } catch (e) { console.warn('updateTempNodeWithServerResponse failed', e); }
        }
      } else {
        if (messageIds.length === 1) {
          try { updateTempNodeWithServerResponse(tmpIds[0], messageIds[0], res); } catch(e){}
          for (let j=1;j<tmpIds.length;j++) {
            appendOutgoingMessage({
              id: 'id-' + Date.now() + '-' + j,
              message_body: selectedFiles[j].caption || ('[' + selectedFiles[j].file.name + ']'),
              date_added: (new Date()).toISOString()
            }, { temp: false });
          }
        } else {
          tmpIds.forEach(tid => {
            const sel = `[data-temp-id="${CSS.escape(tid)}"]`;
            const el = document.querySelector(sel);
            if (el) el.classList.remove('pending'), el.classList.add('failed');
          });
          alert(res.error || res.message || 'Send reported error. See console.');
          console.error('Send response:', res);
        }
      }

      selectedFiles = [];
      attachList.innerHTML = '';
      if (attachPreviewWrap) attachPreviewWrap.innerHTML = '';
      if (attachModal) attachModal.hide();
      else attachModalEl.style.display = 'none';
    } catch (err) {
      console.error('Attachment send failed', err);
      tmpIds.forEach(tid => {
        const sel = `[data-temp-id="${CSS.escape(tid)}"]`;
        const el = document.querySelector(sel);
        if (el) el.classList.remove('pending'), el.classList.add('failed');
      });
      alert('Failed to send attachments. See console for details.');
    } finally {
      attachSendBtn.disabled = false;
      attachSendBtn.innerHTML = 'Send';
      syncPreviewArea();
    }
  });

  attachCancelBtn.addEventListener('click', function () {
    if (attachModal) attachModal.hide();
  });

});
</script>
<!-- END: attach modal script -->

<!-- Contacts list interactions -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const contactsList = document.getElementById('contactsList');
  const searchInput = document.getElementById('whats-search');
  const refreshListBtn = document.getElementById('whats-refresh-list');
  const chatHeaderName = document.getElementById('chatHeaderName');
  const activeSub = document.getElementById('activeContactSub');
  const globalRefresh = document.getElementById('globalRefresh');
  const openNew = document.getElementById('openInNewWindow');

  function setActiveContact(el) {
    if (!el) return;
    contactsList.querySelectorAll('.contact-item').forEach(ci => ci.classList.remove('active'));
    el.classList.add('active');

    const id = el.dataset.id || 0;
    const name = el.dataset.name || el.dataset.phone || 'No name';
    const phone = el.dataset.phone || '';
    window.supplierId = Number(id);
    chatHeaderName.textContent = name;
    activeSub.textContent = phone ? phone : '';

    if (typeof window.refreshWhatsAppChat === 'function') window.refreshWhatsAppChat();
    if (typeof window.checkWhatsAppSession === 'function') window.checkWhatsAppSession();
  }

  contactsList.addEventListener('click', function (ev) {
    const item = ev.target.closest('.contact-item');
    if (!item) return;
    setActiveContact(item);
  });

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const q = (this.value || '').toLowerCase().trim();
      contactsList.querySelectorAll('.contact-item').forEach(ci => {
        const name = (ci.dataset.name || '').toLowerCase();
        const phone = (ci.dataset.phone || '').toLowerCase();
        if (!q || name.indexOf(q) !== -1 || phone.indexOf(q) !== -1) ci.style.display = '';
        else ci.style.display = 'none';
      });
    });
  }

  if (refreshListBtn) refreshListBtn.addEventListener('click', function () {
    location.reload();
  });

  if (globalRefresh) globalRefresh.addEventListener('click', function () {
    if (typeof window.refreshWhatsAppChat === 'function') window.refreshWhatsAppChat();
  });

  if (openNew) openNew.addEventListener('click', function () {
    const id = window.supplierId || 0;
    if (!id) { alert('No contact selected'); return; }
    window.open('contacts_view.php?id=' + encodeURIComponent(id), '_blank');
  });

  const first = contactsList.querySelector('.contact-item');
  if (first) setActiveContact(first);
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
