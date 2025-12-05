// admin-head.js
// Tailwind config and CSRF token exposure for admin dashboard

// Tailwind CSS config
if (window.tailwind) {
  window.tailwind.config = {
    theme: {
      extend: {
        colors: {
          primary: "#2563eb",
          secondary: "#6b7280",
          accent: "#eab308",
          danger: "#dc2626",
          lightgray: "#f9fafb",
          darkgray: "#374151",
        },
        fontFamily: {
          montserrat: ["Montserrat", "sans-serif"],
        },
      },
    },
  };
}

// Expose CSRF token to client for POST-only actions (safe because token tied to session)
// This expects a global variable to be set by PHP inline before this script is loaded.
if (typeof window.CSRF_TOKEN_PHP !== 'undefined') {
  window.CSRF_TOKEN = window.CSRF_TOKEN_PHP;
}

// Success Modal Helper Function
window.showSuccessModal = function(title = 'Success!', message = 'Operation completed successfully.', callback = null) {
  // Ensure function runs after DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function initModal() {
      const modal = document.getElementById('success-modal');
      const titleEl = document.getElementById('success-modal-title');
      const messageEl = document.getElementById('success-modal-message');
      const btn = document.getElementById('success-modal-btn');
      
      if (!modal) {
        console.error('Success modal not found in DOM');
        return;
      }
      
      titleEl.textContent = title;
      messageEl.textContent = message;
      modal.classList.remove('hidden');
      
      // Remove previous listener
      const newBtn = btn.cloneNode(true);
      btn.parentNode.replaceChild(newBtn, btn);
      
      newBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
        if (typeof callback === 'function') {
          callback();
        }
      });
      
      document.removeEventListener('DOMContentLoaded', initModal);
    });
  } else {
    // DOM already loaded
    const modal = document.getElementById('success-modal');
    const titleEl = document.getElementById('success-modal-title');
    const messageEl = document.getElementById('success-modal-message');
    const btn = document.getElementById('success-modal-btn');
    
    if (!modal) {
      console.error('Success modal not found in DOM');
      return;
    }
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    modal.classList.remove('hidden');
    
    // Remove previous listener
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
    
    newBtn.addEventListener('click', () => {
      modal.classList.add('hidden');
      if (typeof callback === 'function') {
        callback();
      }
    });
  }
};

