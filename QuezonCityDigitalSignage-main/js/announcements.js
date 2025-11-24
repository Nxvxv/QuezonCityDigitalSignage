// announcements.js
// Fetch active announcements and render them into the page.
document.addEventListener('DOMContentLoaded', function () {
  const container = document.querySelector('.announcement');
  if (!container) return;

  function renderAnnouncements(list) {
    if (!list || list.length === 0) {
      container.innerHTML = '<p style="font-weight:600;color:#2950b5;">No current announcements.</p>';
      return;
    }
    // Build a simple accessible table
    let html = '<table style="width:100%;border-collapse:collapse;">';
    // Single header label (user requested: change "Title" to "ANNOUNCEMENT" and remove the other header)
    // Make the header larger and raised: increase font-size, weight, and vertical offset
    html += '<thead><tr>' +
  '<th colspan="2" style="text-align:left;padding:2px 6px;font-size:2.5rem;font-weight:800;line-height:1;position:relative;top:-180px;color:#2950b5;letter-spacing:0.02em;">ANNOUNCEMENT</th>' +
      '</tr></thead><tbody style="position:relative;top:-160px;">';
    for (const a of list) {
      const title = escapeHtml(a.title || '');
      const msg = escapeHtml(a.message || '');
      // Render title above the announcement text in a single full-width cell
      html += '<tr>' +
        `<td colspan="2" style='padding:10px 6px;vertical-align:top;'>` +
  `<div style="font-weight:800;font-size:2rem;color:#2950b5;margin-bottom:6px;">${title}</div>` +
  `<div style="font-weight:400;color:#2950b5;">${msg}</div>` +
        `</td>` +
        '</tr>';
    }
    html += '</tbody></table>';
    container.innerHTML = html;
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  async function fetchAnnouncements() {
    try {
      const resp = await fetch('../php/get_announcements.php', { cache: 'no-store' });
      const data = await resp.json();
      if (data && data.success) {
        renderAnnouncements(data.data);
      } else {
        container.innerHTML = `<p style="color:#dc2626;font-weight:700;">Failed to load announcements${data && data.error ? ': ' + escapeHtml(data.error) : ''}</p>`;
      }
    } catch (err) {
      container.innerHTML = `<p style="color:#dc2626;font-weight:700;">Failed to load announcements: ${escapeHtml(err.message || err)}</p>`;
    }
  }

  // Initial fetch and periodic refresh every 30 seconds
  fetchAnnouncements();
  setInterval(fetchAnnouncements, 30000);
});
// Announcements feature: server-backed CRUD via php/announcements.php
// Extracted from inline script in php/dashboard.php

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const API = 'announcements.php';
    const addBtn = document.getElementById('add-announcement-btn');
    const form = document.getElementById('announcement-form');
    const annCancel = document.getElementById('ann-cancel-btn');
    const annList = document.getElementById('announcements-list');
    const saveBtn = document.getElementById('ann-save-btn');

    let editingId = null;

    async function apiFetch(payload){
      const res = await fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      return res.json();
    }

    async function loadAndRender(){
      const res = await fetch(API);
      const json = await res.json();
      if(!json.success) { annList.innerHTML = '<p class="text-red-500">Failed to load announcements.</p>'; return; }
      const items = json.data || [];
      annList.innerHTML = '';
      if(items.length === 0){ annList.innerHTML = '<p class="text-gray-500">No announcements yet.</p>'; return; }
      items.forEach(it => {
        const card = document.createElement('div');
        card.className = 'p-4 border rounded shadow-sm bg-white';
        const expiryLabel = it.expiry ? `<p class="text-xs text-gray-400 mt-2">Expires: ${new Date(it.expiry).toLocaleString()}</p>` : '';
        const videoLabel = it.video ? `<p class="mt-2"><a href="../assets/uploads/videos/${encodeURIComponent(it.video)}" target="_blank" class="text-blue-600 hover:underline">View Video</a></p>` : '';
        card.innerHTML = `
          <div class="flex justify-between items-start">
            <div>
              <h4 class="font-semibold">${escapeHtml(it.title)}</h4>
              <p class="text-sm text-gray-600 mt-1 ${it.text_size === 'LARGE' ? 'text-lg' : (it.text_size === 'SMALL' ? 'text-xs' : '')}">${escapeHtml(it.message)}</p>
              <p class="text-xs text-gray-400 mt-2">Created: ${new Date(it.created_at).toLocaleString()}</p>
              ${expiryLabel}
              ${videoLabel}
            </div>
            <div class="ml-4 flex flex-col space-y-2">
              <button data-id="${it.id}" class="edit-ann-btn bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
              <button data-id="${it.id}" class="del-ann-btn bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
            </div>
          </div>
        `;
        annList.appendChild(card);
      });

      annList.querySelectorAll('.del-ann-btn').forEach(btn => {
        btn.addEventListener('click', async ()=>{
          if(!confirm('Delete this announcement?')) return;
          const id = btn.getAttribute('data-id');
          const r = await apiFetch({ action: 'delete', id: id });
          if(r.success) loadAndRender(); else alert('Failed to delete');
        });
      });

      annList.querySelectorAll('.edit-ann-btn').forEach(btn => {
        btn.addEventListener('click', async ()=>{
          const id = btn.getAttribute('data-id');
          // populate form with announcement data
          const itemsRes = await fetch(API); const j = await itemsRes.json();
          const item = (j.data || []).find(x => String(x.id) === String(id));
          if(!item) { alert('Announcement not found'); return; }
          editingId = item.id;
          document.getElementById('ann-title').value = item.title;
          document.getElementById('ann-message').value = item.message;
          if(item.expiry) {
            // convert from server datetime to input value format
            const dt = new Date(item.expiry);
            const local = dt.toISOString().slice(0,16);
            document.getElementById('ann-expiry').value = local;
          } else document.getElementById('ann-expiry').value = '';
          document.getElementById('ann-textsize').value = item.text_size || 'MEDIUM';
          form.classList.remove('hidden'); addBtn.disabled = true; document.getElementById('ann-title').focus();
          saveBtn.textContent = 'Save Changes';
        });
      });
    }

    function escapeHtml(str){
      return String(str||'')
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;')
        .replace(/'/g,'&#039;');
    }

    addBtn.addEventListener('click', ()=>{
      editingId = null;
      form.reset();
      saveBtn.textContent = 'Create & Save';
      form.classList.remove('hidden');
      addBtn.disabled = true;
      document.getElementById('ann-title').focus();
    });

    annCancel.addEventListener('click', ()=>{
      form.reset(); form.classList.add('hidden'); addBtn.disabled = false; editingId = null; saveBtn.textContent = 'Create & Save';
    });

    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const title = document.getElementById('ann-title').value.trim();
      const message = document.getElementById('ann-message').value.trim();
      const expiry = document.getElementById('ann-expiry').value;
      const text_size = document.getElementById('ann-textsize').value;
      if(!title || !message){ alert('Title and Announcement are required'); return; }
      if(editingId){
        const r = await apiFetch({ action: 'update', id: editingId, title, message, expiry, text_size });
        if(!r.success) { alert('Failed to update'); return; }
      } else {
        const r = await apiFetch({ action: 'create', title, message, expiry, text_size });
        if(!r.success) { alert('Failed to create'); return; }
      }
      form.reset(); form.classList.add('hidden'); addBtn.disabled = false; editingId = null; saveBtn.textContent = 'Create & Save';
      loadAndRender();
    });

    // initial load
    loadAndRender();
  });
})();
