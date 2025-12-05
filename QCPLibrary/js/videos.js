// Videos upload/list/delete via php/videos.php
// Extracted from inline script in php/dashboard.php

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const API = '/QCPLibrary/php/admin/videos.php';  // Use absolute path
      const BRANCH_ID = (typeof window.BRANCH_ID !== 'undefined' && Number.isInteger(window.BRANCH_ID)) ? window.BRANCH_ID : 0;
    const uploadBtn = document.getElementById('upload-video-btn');
    let form = document.getElementById('video-upload-form');
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
      console.log('Loading videos from API:', API);
      try {
        const url = BRANCH_ID ? `${API}?branchId=${BRANCH_ID}` : API;
        const res = await fetch(url); 
        const j = await res.json();
        console.log('Videos response:', j);
        videosList.innerHTML = '';
        if(!j.success || (j.data || []).length === 0){ 
          videosList.innerHTML = '<p class="text-gray-500">No videos uploaded.</p>'; 
          return; 
        }
        
        // Create table
        const table = document.createElement('table');
        table.className = 'w-full border-collapse border border-gray-300 rounded overflow-hidden';
        
        // Create table header
        const thead = document.createElement('thead');
        thead.className = 'bg-gray-200';
        thead.innerHTML = `
          <tr class="border-b border-gray-300">
            <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-gray-700">Title</th>
            <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-gray-700">Video File</th>
            <th class="border border-gray-300 px-4 py-3 text-center font-semibold text-gray-700">Actions</th>
          </tr>
        `;
        table.appendChild(thead);
        
        // Create table body
        const tbody = document.createElement('tbody');
        j.data.forEach(it => {
          const tr = document.createElement('tr');
          tr.className = 'border-b border-gray-300 hover:bg-gray-50';
          tr.innerHTML = `
            <td class="border border-gray-300 px-4 py-3 text-sm font-medium text-gray-900">${escapeHtml(it.title)}</td>
            <td class="border border-gray-300 px-4 py-3 text-sm text-gray-600">${escapeHtml(it.file)}</td>
            <td class="border border-gray-300 px-4 py-3 text-center space-x-2 flex justify-center">
              <button class="edit-video-btn bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600" data-id="${it.id}" data-title="${escapeHtml(it.title)}">Edit</button>
              <button class="del-video-btn bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600" data-id="${it.id}">Delete</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
        table.appendChild(tbody);
        videosList.appendChild(table);
        
        // Edit button handler
        videosList.querySelectorAll('.edit-video-btn').forEach(btn=>{
          btn.addEventListener('click', async ()=>{
            const id = btn.getAttribute('data-id');
            const title = btn.getAttribute('data-title');
            const video = j.data.find(v => v.id == id);
            
            if (!video) return;
            
            // Populate edit modal
            document.getElementById('edit-video-id').value = id;
            document.getElementById('edit-vid-title').value = video.title;
            document.getElementById('edit-vid-desc').value = video.description || '';
            document.getElementById('edit-current-video-src').src = '../../uploads/videos/' + video.file;
            document.getElementById('edit-current-video').load();
            document.getElementById('edit-vid-file').value = ''; // Clear file input
            
            // Show edit modal
            document.getElementById('video-edit-modal').classList.remove('hidden');
          });
        });
        
        // Delete button handler
        videosList.querySelectorAll('.del-video-btn').forEach(btn=>{
          btn.addEventListener('click', async ()=>{
            const id = btn.getAttribute('data-id');
            // Show delete confirmation modal
            window.showDeleteConfirmation(async () => {
              // Remove from UI immediately (optimistic delete)
              const videoRow = btn.closest('tr');
              videoRow.style.opacity = '0.5';
              
              const r = await fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=delete&id=${id}&branchId=${BRANCH_ID}` });
              const jr = await r.json(); 
              if(jr.success) { 
                videoRow.remove();
                loadVideos(); 
              } else { 
                videoRow.style.opacity = '1';
                alert('Failed to delete: ' + (jr.error || 'unknown error'));
              }
            });
          });
        });
      } catch(err) {
        console.error('LoadVideos error:', err);
        videosList.innerHTML = '<p class="text-red-500">Error loading videos: ' + err.message + '</p>';
      }
    }

    function escapeHtml(str){ return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    if (form) {
      // Nuke any previously attached submit listeners by cloning the form
      try {
        const cloned = form.cloneNode(true);
        form.parentNode.replaceChild(cloned, form);
        form = cloned;
      } catch(_) {}

      // Also neutralize any globally exposed handler name to avoid future binds
      try { window.handleVideoFormSubmit = function(){ return false; }; } catch(_) {}

      // Best effort: remove any handler bound by dashboard-refactored.js
      try {
        if (typeof window.handleVideoFormSubmit === 'function') {
          form.removeEventListener('submit', window.handleVideoFormSubmit, false);
        }
        // Also clear inline onsubmit if any
        if (form.onsubmit) form.onsubmit = null;
      } catch (_) {}

      // Use capture so this runs before any bubbling listeners
      form.addEventListener('submit', async (e)=>{
        // Ensure only this handler runs (avoid conflicting handlers)
        e.preventDefault();
        if (typeof e.stopPropagation === 'function') e.stopPropagation();
        if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
        const title = document.getElementById('vid-title').value.trim();
        const desc = document.getElementById('vid-desc').value.trim();
        const fileInput = document.getElementById('vid-file');
        if(!title){ alert('Title required'); return; }
        if(!fileInput.files || fileInput.files.length === 0){ alert('Please choose an MP4 file'); return; }
        const file = fileInput.files[0];
        const ext = file.name.split('.').pop().toLowerCase();
        if(ext !== 'mp4'){ alert('Only MP4 files are allowed'); return; }
        if(file.size > 50 * 1024 * 1024){ alert('File exceeds 50MB limit'); return; }

        const fd = new FormData();
        fd.append('action','upload');
          if (BRANCH_ID) fd.append('branchId', BRANCH_ID);
        fd.append('title', title);
        fd.append('description', desc);
        fd.append('video', file);

        try {
          const res = await fetch(API, { method: 'POST', body: fd });
          const text = await res.text();
          console.log('Raw response:', text);
          console.log('Response status:', res.status);
          
          if (!text) {
            alert('Empty response from server');
            return;
          }
          
          let j;
          try {
            j = JSON.parse(text);
            console.log('Parsed JSON:', j);
          } catch(parseErr) {
            console.error('JSON parse error:', parseErr);
            console.error('Failed to parse:', text.substring(0, 200));
            alert('Server error (invalid JSON): ' + text.substring(0, 100));
            return;
          }
          
          if(!j.success){ 
            alert('Upload failed: ' + (j.error || j.msg || 'unknown error')); 
            return; 
          }
          
          // Show success modal with preview
          const videoFile = file;
          const videoUrl = URL.createObjectURL(videoFile);
          document.getElementById('modal-video-title').textContent = title;
          document.getElementById('modal-video-time').textContent = new Date().toLocaleString();
          document.getElementById('modal-video-src').src = videoUrl;
          const player = document.getElementById('modal-video-player');
          player.load();
          document.getElementById('video-success-modal').classList.remove('hidden');
          
          // Auto-close modal after 5 seconds
          setTimeout(() => {
            closeModal();
            window.showSuccessModal('Video Uploaded', 'Video uploaded successfully!', loadVideos);
          }, 5000);
          
          // Reset form
          form.reset(); 
          form.classList.add('hidden'); 
          uploadBtn.disabled = false; 
          
          // Reload videos list after 2 seconds
          setTimeout(loadVideos, 2000);
        } catch(err) {
          console.error('Upload error:', err);
          alert('Upload error: ' + err.message);
        }
      }, true);
    }

    loadVideos();
    
    // ====== SUCCESS MODAL HANDLERS ======
    const modal = document.getElementById('video-success-modal');
    const backdrop = document.getElementById('video-success-backdrop');
    const closeBtn = document.getElementById('close-video-success');
    const closeBtnMain = document.getElementById('close-video-success-btn');
    const uploadAnotherBtn = document.getElementById('upload-another-btn');
    
    function closeModal() {
      modal.classList.add('hidden');
    }
    
    closeBtn.addEventListener('click', closeModal);
    closeBtnMain.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
    
    uploadAnotherBtn.addEventListener('click', () => {
      closeModal();
      form.reset();
      form.classList.remove('hidden');
      uploadBtn.disabled = true;
      document.getElementById('vid-title').focus();
    });
    
    // ====== EDIT MODAL HANDLERS ======
    const editModal = document.getElementById('video-edit-modal');
    const editBackdrop = document.getElementById('video-edit-backdrop');
    const editCloseBtn = document.getElementById('close-edit-modal');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const editForm = document.getElementById('edit-video-form');
    
    function closeEditModal() {
      editModal.classList.add('hidden');
    }
    
    editCloseBtn.addEventListener('click', closeEditModal);
    cancelEditBtn.addEventListener('click', closeEditModal);
    editBackdrop.addEventListener('click', closeEditModal);
    
    // Edit form submission
    editForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const id = document.getElementById('edit-video-id').value;
      const title = document.getElementById('edit-vid-title').value.trim();
      const description = document.getElementById('edit-vid-desc').value.trim();
      const fileInput = document.getElementById('edit-vid-file');
      
      if (!title) {
        alert('Title is required');
        return;
      }
      
      // Validate file if provided
      if (fileInput.files && fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const ext = file.name.split('.').pop().toLowerCase();
        
        if (ext !== 'mp4') {
          alert('Only MP4 files are allowed');
          return;
        }
        
        if (file.size > 50 * 1024 * 1024) {
          alert('File exceeds 50MB limit');
          return;
        }
      }
      
      // Build form data
      const fd = new FormData();
      fd.append('action', 'edit');
        if (BRANCH_ID) fd.append('branchId', BRANCH_ID);
      fd.append('id', id);
      fd.append('title', title);
      fd.append('description', description);
      if (fileInput.files && fileInput.files.length > 0) {
        fd.append('video', fileInput.files[0]);
      }
      
      try {
        const res = await fetch(API, { method: 'POST', body: fd });
        const text = await res.text();
        
        if (!text) {
          alert('Empty response from server');
          return;
        }
        
        let j;
        try {
          j = JSON.parse(text);
        } catch (parseErr) {
          alert('Server error (invalid JSON)');
          console.error('Failed to parse:', text.substring(0, 200));
          return;
        }
        
        if (!j.success) {
          alert('Update failed: ' + (j.error || 'unknown error'));
          return;
        }
        
        // Success - close modal and reload videos
        closeEditModal();
        loadVideos();
        window.showSuccessModal('Video Updated', 'Video updated successfully!', loadVideos);
      } catch (err) {
        console.error('Edit error:', err);
        alert('Error updating video: ' + err.message);
      }
    });
  });
})();
