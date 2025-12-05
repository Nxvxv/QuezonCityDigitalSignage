// Announcements management for admin dashboard
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const API = 'announcements.php';
    const addBtn = document.getElementById('add-announcement-btn');
    const form = document.getElementById('announcement-form');
    const cancelBtn = document.getElementById('ann-cancel-btn');
    const listDiv = document.getElementById('announcements-list');

    if (addBtn) {
      addBtn.addEventListener('click', () => {
        form.reset();
        form.classList.remove('hidden');
        addBtn.classList.add('hidden');
        document.getElementById('ann-title').focus();
      });
    }

    if (cancelBtn) {
      cancelBtn.addEventListener('click', () => {
        form.reset();
        form.classList.add('hidden');
        addBtn.classList.remove('hidden');
      });
    }

    async function loadAnnouncements() {
      console.log('Loading announcements from API:', API);
      // Ensure button is visible and form is hidden
      if (addBtn) addBtn.classList.remove('hidden');
      form.classList.add('hidden');
      
      try {
        const res = await fetch(API);
        const j = await res.json();
        console.log('Announcements response:', j);
        listDiv.innerHTML = '';
        
        if (!j.success || (j.data || []).length === 0) {
          listDiv.innerHTML = '<p class="text-gray-500">No announcements yet</p>';
          return;
        }

        j.data.forEach(ann => {
          const div = document.createElement('div');
          div.className = 'p-4 border rounded bg-white';
          
          const expiryDate = ann.expiry_date ? new Date(ann.expiry_date).toLocaleString() : 'No expiry';
          const isExpired = ann.expiry_date && new Date(ann.expiry_date) < new Date();
          const expiredBadge = isExpired ? '<span class="ml-2 text-xs bg-red-200 text-red-700 px-2 py-1 rounded">EXPIRED</span>' : '';
          
          div.innerHTML = `
            <div class="flex items-start justify-between mb-2">
              <div class="flex-1">
                <div class="font-semibold text-lg">${escapeHtml(ann.title)}</div>
                <div class="text-sm text-gray-500">Text Size: <strong>${ann.text_size}</strong> | Expires: ${expiryDate}${expiredBadge}</div>
              </div>
              <div class="space-x-2 flex">
                <button class="edit-ann-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600" data-id="${ann.id}">Edit</button>
                <button class="del-ann-btn bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600" data-id="${ann.id}">Delete</button>
              </div>
            </div>
            <div class="text-gray-700 bg-gray-50 p-3 rounded mt-2">${escapeHtml(ann.content)}</div>
            <div class="text-xs text-gray-400 mt-2">Posted: ${new Date(ann.date_posted).toLocaleString()}</div>
          `;
          listDiv.appendChild(div);
        });

        // Edit button handler
        listDiv.querySelectorAll('.edit-ann-btn').forEach(btn => {
          btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-id');
            const ann = j.data.find(a => a.id == id);
            if (!ann) return;

            document.getElementById('ann-edit-id').value = id;
            document.getElementById('ann-edit-title').value = ann.title;
            document.getElementById('ann-edit-content').value = ann.content;
            document.getElementById('ann-edit-textsize').value = ann.text_size;
            
            // Convert datetime format for input
            if (ann.expiry_date) {
              const dt = new Date(ann.expiry_date);
              const dateStr = dt.toISOString().slice(0, 16);
              document.getElementById('ann-edit-expiry').value = dateStr;
            } else {
              document.getElementById('ann-edit-expiry').value = '';
            }
            
            document.getElementById('announcement-edit-modal').classList.remove('hidden');
          });
        });

        // Delete button handler
        listDiv.querySelectorAll('.del-ann-btn').forEach(btn => {
          btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-id');
            window.showDeleteConfirmation(async () => {
            
            // Remove from UI immediately (optimistic delete)
            const annDiv = btn.closest('.p-4');
            annDiv.style.opacity = '0.5';
            
            const r = await fetch(API, {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'action=delete&id=' + id
            });
            const jr = await r.json();
            if (jr.success) {
              annDiv.remove();
              loadAnnouncements();
            } else {
              annDiv.style.opacity = '1';
              alert('Failed to delete: ' + (jr.error || 'unknown error'));
            }
            });
          });
        });

      } catch (err) {
        console.error('LoadAnnouncements error:', err);
        listDiv.innerHTML = '<p class="text-red-500">Error loading announcements: ' + err.message + '</p>';
      }
    }

    function escapeHtml(str) {
      return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // Create form submission
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const title = document.getElementById('ann-title').value.trim();
        const content = document.getElementById('ann-message').value.trim();
        const expiryDate = document.getElementById('ann-expiry').value;
        const textSize = document.getElementById('ann-textsize').value;

        console.log('Form submit - title:', title, 'content:', content);

        if (!title || !content) {
          window.showSuccessModal('Error', 'Title and announcement are required');
          return;
        }

        const fd = new FormData();
        fd.append('action', 'create');
        fd.append('title', title);
        fd.append('content', content);
        fd.append('expiry_date', expiryDate);
        fd.append('text_size', textSize);

        try {
          const res = await fetch(API, { method: 'POST', body: fd });
          const text = await res.text();
          console.log('Response text:', text);
          
          if (!text) {
            window.showSuccessModal('Error', 'Empty response from server');
            return;
          }

          let j;
          try {
            j = JSON.parse(text);
          } catch (parseErr) {
            window.showSuccessModal('Error', 'Server error (invalid JSON): ' + text.substring(0, 100));
            console.error('Failed to parse:', text.substring(0, 200));
            return;
          }

          if (!j.success) {
            window.showSuccessModal('Error', j.error || 'Failed to create announcement');
            return;
          }

          form.reset();
          form.classList.add('hidden');
          addBtn.classList.remove('hidden');
          window.showSuccessModal('Announcement Created', 'Announcement created successfully!', loadAnnouncements);
        } catch (err) {
          console.error('Create error:', err);
          window.showSuccessModal('Error', 'Error creating announcement: ' + err.message);
        }
      });
    }

    // Edit modal handlers
    const editModal = document.getElementById('announcement-edit-modal');
    const editBackdrop = document.getElementById('announcement-edit-backdrop');
    const editCloseBtn = document.getElementById('close-ann-edit-modal');
    const cancelEditBtn = document.getElementById('cancel-ann-edit-btn');
    const editForm = document.getElementById('announcement-edit-form');

    if (editCloseBtn) {
      editCloseBtn.addEventListener('click', () => {
        editModal.classList.add('hidden');
      });
    }

    if (cancelEditBtn) {
      cancelEditBtn.addEventListener('click', () => {
        editModal.classList.add('hidden');
      });
    }

    if (editBackdrop) {
      editBackdrop.addEventListener('click', () => {
        editModal.classList.add('hidden');
      });
    }

    if (editForm) {
      editForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('ann-edit-id').value;
        const title = document.getElementById('ann-edit-title').value.trim();
        const content = document.getElementById('ann-edit-content').value.trim();
        const expiryDate = document.getElementById('ann-edit-expiry').value;
        const textSize = document.getElementById('ann-edit-textsize').value;

        if (!title || !content) {
          alert('Title and content are required');
          return;
        }

        const fd = new FormData();
        fd.append('action', 'edit');
        fd.append('id', id);
        fd.append('title', title);
        fd.append('content', content);
        fd.append('expiry_date', expiryDate);
        fd.append('text_size', textSize);

        try {
          const res = await fetch(API, { method: 'POST', body: fd });
          const text = await res.text();

          if (!text) {
            window.showSuccessModal('Error', 'Empty response from server');
            return;
          }

          let j;
          try {
            j = JSON.parse(text);
          } catch (parseErr) {
            window.showSuccessModal('Error', 'Server error (invalid JSON)');
            return;
          }

          if (!j.success) {
            window.showSuccessModal('Error', j.error || 'Failed to update announcement');
            return;
          }

          editModal.classList.add('hidden');
          window.showSuccessModal('Announcement Updated', 'Announcement updated successfully!', loadAnnouncements);
        } catch (err) {
          console.error('Edit error:', err);
          window.showSuccessModal('Error', 'Error updating announcement: ' + err.message);
        }
      });
    }

    loadAnnouncements();
  });
})();
