// Footer messages management for admin dashboard
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    // Robust API path to admin footer endpoint
    const API = '/QCPLibrary/php/admin/footer.php';
    const addBtn = document.getElementById('new-footer-msg-btn');
    const form = document.getElementById('new-footer-form');
    const cancelBtn = document.getElementById('footer-cancel');
    const listDiv = document.getElementById('footer-msgs-list');

    if (addBtn) {
      addBtn.addEventListener('click', () => {
        form.reset();
        form.classList.remove('hidden');
        addBtn.disabled = true;
        document.getElementById('footer-message').focus();
      });
    }

    if (cancelBtn) {
      cancelBtn.addEventListener('click', () => {
        form.reset();
        form.classList.add('hidden');
        addBtn.disabled = false;
      });
    }

    async function loadFooterMessages() {
      console.log('Loading footer messages from API:', API);
      try {
        const res = await fetch(API + '?t=' + Date.now(), { cache: 'no-store' });
        const j = await res.json();
        console.log('Footer response:', j);
        console.log('Number of messages returned:', j.data ? j.data.length : 0);
        
        if (!res.ok) {
          console.error('API error status:', res.status);
          listDiv.innerHTML = '<p class="text-red-500">Error: ' + (j.error || res.status) + '</p>';
          return;
        }
        
        listDiv.innerHTML = '';
        
        if (!j.success || (j.data || []).length === 0) {
          listDiv.innerHTML = '<p class="text-gray-500">No footer messages yet</p>';
          return;
        }

        j.data.forEach(msg => {
          const div = document.createElement('div');
          div.className = 'p-4 border rounded bg-white';
          
          const expiryDate = msg.expiry_date ? new Date(msg.expiry_date).toLocaleString() : 'No expiry';
          const isExpired = msg.expiry_date && new Date(msg.expiry_date) < new Date();
          const expiredBadge = isExpired ? '<span class="ml-2 text-xs bg-red-200 text-red-700 px-2 py-1 rounded">EXPIRED</span>' : '';
          
          div.innerHTML = `
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="text-sm text-gray-700">${escapeHtml(msg.content)}</div>
                <div class="text-xs text-gray-500 mt-2">
                  Speed: ${msg.scroll_speed} | 
                  Expires: ${expiryDate}${expiredBadge}
                </div>
              </div>
              <div class="space-x-2 flex ml-4">
                <button class="edit-footer-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600" data-id="${msg.id}">Edit</button>
                <button class="del-footer-btn bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600" data-id="${msg.id}">Delete</button>
              </div>
            </div>
          `;
          listDiv.appendChild(div);
        });

        // Edit button handler
        listDiv.querySelectorAll('.edit-footer-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const id = btn.getAttribute('data-id');
            console.log('Edit button clicked, ID:', id);
            
            const msg = j.data.find(m => m.id == id);
            if (!msg) {
              console.error('Message not found for ID:', id);
              alert('Error: Message not found');
              return;
            }

            console.log('Found message:', msg);

            // Populate modal fields
            const editModal = document.getElementById('footer-edit-modal');
            const editId = document.getElementById('footer-edit-id');
            const editMessage = document.getElementById('footer-edit-message');
            const editSpeed = document.getElementById('footer-edit-speed');
            const editExpiry = document.getElementById('footer-edit-expiry');

            if (!editModal || !editId || !editMessage || !editSpeed || !editExpiry) {
              console.error('Edit modal elements not found');
              alert('Error: Edit form not available');
              return;
            }

            editId.value = id;
            editMessage.value = msg.content;
            editSpeed.value = msg.scroll_speed || 2;
            
            if (msg.expiry_date) {
              const dt = new Date(msg.expiry_date);
              if (!isNaN(dt.getTime())) {
                const dateStr = dt.toISOString().slice(0, 16);
                editExpiry.value = dateStr;
              } else {
                editExpiry.value = '';
              }
            } else {
              editExpiry.value = '';
            }
            
            console.log('Opening edit modal');
            editModal.classList.remove('hidden');
            editMessage.focus();
          });
        });

        // Delete button handler
        listDiv.querySelectorAll('.del-footer-btn').forEach(btn => {
          btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const id = btn.getAttribute('data-id');
            console.log('Delete button clicked, ID:', id);
            
            // Use custom delete confirmation modal instead of browser confirm
            window.showDeleteConfirmation(async () => {
              console.log('Confirmed deletion of footer message ID:', id);
              
              // Remove from UI immediately (optimistic delete)
              const msgDiv = btn.closest('.p-4');
              if (msgDiv) {
                msgDiv.style.opacity = '0.5';
                msgDiv.style.pointerEvents = 'none';
              }
              
              try {
                const r = await fetch(API, {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: 'action=delete&id=' + encodeURIComponent(id)
                });
                
                if (!r.ok) {
                  throw new Error('Network error: ' + r.status);
                }
                
                const jr = await r.json();
                console.log('Delete response:', jr);
                
                if (jr.success) {
                  if (msgDiv) {
                    msgDiv.remove();
                  }
                  loadFooterMessages();
                  // Show success message
                  if (window.showSuccessModal) {
                    window.showSuccessModal('Deleted', 'Footer message deleted successfully!');
                  }
                } else {
                  // Restore UI state
                  if (msgDiv) {
                    msgDiv.style.opacity = '1';
                    msgDiv.style.pointerEvents = 'auto';
                  }
                  alert('Failed to delete: ' + (jr.error || 'Unknown error'));
                }
              } catch (error) {
                console.error('Delete error:', error);
                // Restore UI state
                if (msgDiv) {
                  msgDiv.style.opacity = '1';
                  msgDiv.style.pointerEvents = 'auto';
                }
                alert('Error deleting message: ' + error.message);
              }
            });
          });
        });

      } catch (err) {
        console.error('LoadFooterMessages error:', err);
        listDiv.innerHTML = '<p class="text-red-500">Error loading messages: ' + err.message + '</p>';
      }
    }

    function escapeHtml(str) {
      return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // Create form submission
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const message = document.getElementById('footer-message').value.trim();
        const scrollSpeed = document.getElementById('scroll-speed').value || 2;
        const expiryDate = document.getElementById('footer-expiry').value;

        if (!message) {
          alert('Message is required');
          return;
        }

        const fd = new FormData();
        fd.append('action', 'create');
        fd.append('content', message);
        fd.append('scroll_speed', scrollSpeed);
        fd.append('expiry_date', expiryDate);

        console.log('Creating footer message:', { message, scrollSpeed, expiryDate });

        try {
          const res = await fetch(API, { method: 'POST', body: fd });
          const text = await res.text();
          
          console.log('API response status:', res.status);
          console.log('API response text:', text);

          if (!text) {
            alert('Empty response from server');
            return;
          }

          let j;
          try {
            j = JSON.parse(text);
          } catch (parseErr) {
            alert('Server error (invalid JSON): ' + text.substring(0, 100));
            console.error('Failed to parse:', text);
            return;
          }

          if (!j.success) {
            alert('Failed to create: ' + (j.error || 'unknown error'));
            return;
          }

          form.reset();
          form.classList.add('hidden');
          addBtn.disabled = false;
          loadFooterMessages();
          window.showSuccessModal('Message Added', 'Message added successfully!', loadFooterMessages);
        } catch (err) {
          console.error('Create error:', err);
          alert('Error creating message: ' + err.message);
        }
      });
    }

    // Edit modal handlers
    const editModal = document.getElementById('footer-edit-modal');
    const editBackdrop = document.getElementById('footer-edit-backdrop');
    const editCloseBtn = document.getElementById('close-footer-edit-modal');
    const cancelEditBtn = document.getElementById('cancel-footer-edit-btn');
    const editForm = document.getElementById('footer-edit-form');

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
        const id = document.getElementById('footer-edit-id').value;
        const message = document.getElementById('footer-edit-message').value.trim();
        const scrollSpeed = document.getElementById('footer-edit-speed').value || 2;
        const expiryDate = document.getElementById('footer-edit-expiry').value;

        if (!message) {
          alert('Message is required');
          return;
        }

        const fd = new FormData();
        fd.append('action', 'edit');
        fd.append('id', id);
        fd.append('content', message);
        fd.append('scroll_speed', scrollSpeed);
        fd.append('expiry_date', expiryDate);

        try {
          const res = await fetch(API, { method: 'POST', body: fd });
          const text = await res.text();

          console.log('Edit API response:', text);

          if (!text) {
            alert('Empty response from server');
            return;
          }

          let j;
          try {
            j = JSON.parse(text);
          } catch (parseErr) {
            alert('Server error (invalid JSON)');
            return;
          }

          if (!j.success) {
            alert('Update failed: ' + (j.error || 'unknown error'));
            return;
          }

          editModal.classList.add('hidden');
          loadFooterMessages();
          window.showSuccessModal('Message Updated', 'Message updated successfully!', loadFooterMessages);
        } catch (err) {
          console.error('Edit error:', err);
          alert('Error updating message: ' + err.message);
        }
      });
    }

    loadFooterMessages();
  });
})();
