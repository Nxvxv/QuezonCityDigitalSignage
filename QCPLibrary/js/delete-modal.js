// Reusable delete confirmation modal
(function () {
  // Create modal HTML
  const modalHTML = `
    <div id="delete-confirmation-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
      <div id="delete-modal-backdrop" class="absolute inset-0 bg-black bg-opacity-50"></div>
      <div class="relative bg-white rounded-lg shadow-xl w-11/12 md:w-1/3 p-6">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-gray-900">Confirm Delete</h3>
        </div>
        <p class="text-gray-700 mb-6">Are you sure you want to delete this? This action cannot be undone.</p>
        <div class="flex justify-end space-x-3">
          <button id="delete-cancel-btn" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 font-medium">Cancel</button>
          <button id="delete-confirm-btn" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 font-medium">Delete</button>
        </div>
      </div>
    </div>
  `;

  // Inject modal into body on DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDeleteModal);
  } else {
    initDeleteModal();
  }

  function initDeleteModal() {
    // Add modal to body if not already present
    if (!document.getElementById('delete-confirmation-modal')) {
      document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    const modal = document.getElementById('delete-confirmation-modal');
    const backdrop = document.getElementById('delete-modal-backdrop');
    const cancelBtn = document.getElementById('delete-cancel-btn');
    const confirmBtn = document.getElementById('delete-confirm-btn');

    // Close modal handlers
    cancelBtn.addEventListener('click', closeDeleteModal);
    backdrop.addEventListener('click', closeDeleteModal);

    function closeDeleteModal() {
      modal.classList.add('hidden');
    }

    // Global delete handler function
    window.showDeleteConfirmation = function (callback) {
      modal.classList.remove('hidden');
      
      // Remove old listeners and add fresh one
      confirmBtn.onclick = null;
      confirmBtn.addEventListener('click', function handleDelete() {
        modal.classList.add('hidden');
        callback();
        confirmBtn.removeEventListener('click', handleDelete);
      }, { once: true });
    };
  }
})();
