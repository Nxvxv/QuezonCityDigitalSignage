// Videos upload/list/delete via php/videos.php
// Extracted from inline script in php/dashboard.php

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const API = 'videos.php';
    const uploadBtn = document.getElementById('upload-video-btn');
    const form = document.getElementById('video-upload-form');
    const cancelBtn = document.getElementById('vid-cancel-btn');
    const videosList = document.getElementById('videos-list');

    if (uploadBtn) {
      uploadBtn.addEventListener('click', ()=>{
        form.reset(); form.classList.remove('hidden'); uploadBtn.disabled = true; document.getElementById('vid-title').focus();
      });
    }

    if (cancelBtn) {
      cancelBtn.addEventListener('click', ()=>{ form.reset(); form.classList.add('hidden'); uploadBtn.disabled = false; });
    }

    async function loadVideos(){
      const res = await fetch(API); const j = await res.json();
      videosList.innerHTML = '';
      if(!j.success || (j.data || []).length === 0){ videosList.innerHTML = '<p class="text-gray-500">No videos uploaded.</p>'; return; }
      j.data.forEach(it => {
        const div = document.createElement('div');
        div.className = 'p-4 border rounded bg-white flex items-center justify-between';
        div.innerHTML = `
          <div>
            <div class="font-semibold">${escapeHtml(it.title)}</div>
            <div class="text-sm text-gray-600">${escapeHtml(it.description)}</div>
            <div class="text-xs text-gray-400">Uploaded: ${new Date(it.created_at).toLocaleString()}</div>
          </div>
          <div class="space-x-2">
            <a href="../assets/uploads/videos/${encodeURIComponent(it.video)}" target="_blank" class="text-blue-600 hover:underline">View</a>
            <button data-id="${it.id}" class="del-video-btn bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
          </div>
        `;
        videosList.appendChild(div);
      });
      videosList.querySelectorAll('.del-video-btn').forEach(btn=>{
        btn.addEventListener('click', async ()=>{
          if(!confirm('Delete this video?')) return;
          const id = btn.getAttribute('data-id');
          const r = await fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'delete', id }) });
          const jr = await r.json(); if(jr.success) loadVideos(); else alert('Failed to delete');
        });
      });
    }

    function escapeHtml(str){ return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    if (form) {
      form.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const title = document.getElementById('vid-title').value.trim();
        const desc = document.getElementById('vid-desc').value.trim();
        const expiry = document.getElementById('vid-expiry').value;
        const fileInput = document.getElementById('vid-file');
        if(!title || !desc){ alert('Title and description required'); return; }
        if(!fileInput.files || fileInput.files.length === 0){ alert('Please choose an MP4 file'); return; }
        const file = fileInput.files[0];
        const ext = file.name.split('.').pop().toLowerCase();
        if(ext !== 'mp4'){ alert('Only MP4 files are allowed'); return; }
        if(file.size > 50 * 1024 * 1024){ alert('File exceeds 50MB limit'); return; }

        const fd = new FormData();
        fd.append('action','upload');
        fd.append('title', title);
        fd.append('description', desc);
        fd.append('expiry', expiry);
        fd.append('video', file);

        const res = await fetch(API, { method: 'POST', body: fd });
        const j = await res.json();
        if(!j.success){ alert('Upload failed: ' + (j.error || 'unknown')); return; }
        form.reset(); form.classList.add('hidden'); uploadBtn.disabled = false; loadVideos();
      });
    }

    loadVideos();
  });
})();
