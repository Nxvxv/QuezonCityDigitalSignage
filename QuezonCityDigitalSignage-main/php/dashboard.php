
<?php
session_start();

// Kung naka-login na, i-redirect pabalik sa dashboard (o homepage)
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<?php
// Admin Dashboard migrated from dashboard.html
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>QC Library Digital Signage - Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../css/dashboard.css" />
  <style>
    /* blur only the app content when modal open */
    body.body-blurred #app-root {
      filter: blur(6px) brightness(0.8);
      pointer-events: none;
      user-select: none;
    }
  </style>
  <script>
    tailwind.config = {
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
  </script>
</head>
<body class="h-full bg-gray-50 text-gray-900 font-montserrat">
  <div id="app-root" class="flex h-full">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
      <div class="p-6 flex items-center space-x-3 border-b border-gray-200">
        <div class="w-20 h-12 rounded-md overflow-hidden">
          <img src="../assets/logoo.png" alt="QC Library Logo" class="w-full h-full object-fill" />
        </div>
        <div>
          <h1 class="text-lg font-semibold text-gray-900">QC Library</h1>
          <p class="text-sm text-gray-500">Digital Signage</p>
        </div>
      </div>
      <nav class="flex-1 px-4 py-6 space-y-2">
        <a href="#" id="tab-overview" class="flex items-center px-3 py-2 rounded-md text-primary bg-primary/20 hover:bg-primary/30">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18" /></svg>
          Dashboard
        </a>
        <a href="#" id="logout-btn" class="flex items-center px-3 py-2 rounded-md text-secondary hover:bg-gray-100">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
          Log Out
        </a>
      </nav>
      <div class="p-6 border-t border-gray-200 text-sm text-gray-500">
        <p>Branch Info</p>
        <p id="branch-info" class="mt-1 font-semibold text-gray-700">Main Branch</p>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-auto">
      <header class="flex justify-between items-center mb-6">
        <div>
          <h2 class="text-2xl font-bold">Dashboard</h2>
          <p class="text-gray-600" id="welcome-msg"></p>
        </div>
        <button id="preview-signage" class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700">Preview Signage</button>
      </header>

      <!-- Tabs -->
      <nav class="mb-6 border border-gray-300 rounded bg-white flex overflow-hidden">
        <button id="tab-btn-overview" class="flex-1 py-2 px-4 bg-primary text-white flex items-center justify-center space-x-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18" /></svg>
          <span>Overview</span>
        </button>
        <button id="tab-btn-videos" class="flex-1 py-2 px-4 flex items-center justify-center space-x-2 hover:bg-red-100 text-red-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14v-4zM4 6h10a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z" /></svg>
          <span>Videos</span>
        </button>
        <button id="tab-btn-announcements" class="flex-1 py-2 px-4 flex items-center justify-center space-x-2 hover:bg-blue-100 text-blue-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14v-4zM4 6h10a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z" /></svg>
          <span>Announcements</span>
        </button>
        <button id="tab-btn-books" class="flex-1 py-2 px-4 flex items-center justify-center space-x-2 hover:bg-yellow-100 text-yellow-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20l9-5-9-5-9 5 9 5z" /></svg>
          <span>Featured Books</span>
        </button>
        <button id="tab-btn-footer" class="flex-1 py-2 px-4 flex items-center justify-center space-x-2 hover:bg-gray-200 text-gray-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
          <span>Footer</span>
        </button>
      </nav>

      <!-- Tab Contents -->
      <section id="tab-overview-content" class="tab-content bg-white p-6 rounded shadow">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <!-- Overview cards -->
          <div class="p-4 bg-white border rounded shadow flex items-center justify-between">
            <div>
              <h5 class="text-sm text-gray-500">Videos</h5>
              <div class="text-2xl font-bold text-gray-900" id="count-videos">0</div>
            </div>
            <div class="text-gray-400">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14v-4zM4 6h10a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z" /></svg>
            </div>
          </div>

          <div class="p-4 bg-white border rounded shadow flex items-center justify-between">
            <div>
              <h5 class="text-sm text-gray-500">Announcements</h5>
              <div class="text-2xl font-bold text-gray-900" id="count-announcements">0</div>
            </div>
            <div class="text-gray-400">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M5 6h14M5 18h14" /></svg>
            </div>
          </div>

          <div class="p-4 bg-white border rounded shadow flex items-center justify-between">
            <div>
              <h5 class="text-sm text-gray-500">Books</h5>
              <div class="text-2xl font-bold text-gray-900" id="count-books">0</div>
            </div>
            <div class="text-gray-400">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20l9-5-9-5-9 5 9 5z" /></svg>
            </div>
          </div>

          <div class="p-4 bg-white border rounded shadow flex items-center justify-between">
            <div>
              <h5 class="text-sm text-gray-500">Footer Messages</h5>
              <div class="text-2xl font-bold text-gray-900" id="count-footers">0</div>
            </div>
            <div class="text-gray-400">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="md:col-span-2 bg-gray-50 p-4 rounded shadow">
            <p class="text-gray-900">You are managing <span id="summary-videos">0 video(s)</span>, <span id="summary-announcements">0 announcement(s)</span>, and <span id="summary-books">0 book(s)</span> for this branch.</p>
            <p class="text-gray-900">Click on a category above to manage its content.</p>
          </div>

          <div class="bg-blue-600 text-white p-6 rounded shadow flex items-center justify-between">
            <div>
              <h5 class="text-sm opacity-90">Active on Screen</h5>
              <div class="text-3xl font-extrabold" id="active-on-screen">0</div>
              <p class="text-sm opacity-80">items are live on the signage.</p>
            </div>
            <div class="opacity-60">
              <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18" /></svg>
            </div>
          </div>
        </div>
      </section>

      <section id="tab-videos-content" class="tab-content hidden bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Videos</h3>
          <button id="upload-video-btn" class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700">Upload Video</button>
        </div>

        <!-- Video upload form -->
        <form id="video-upload-form" class="hidden space-y-3 mb-6" enctype="multipart/form-data">
          <div>
            <label for="vid-title" class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
            <input id="vid-title" name="title" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" placeholder="Video title" />
          </div>
          <div>
            <label for="vid-desc" class="block text-sm font-medium text-gray-700">Description <span class="text-red-500">*</span></label>
            <textarea id="vid-desc" name="description" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" placeholder="Video description..."></textarea>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="vid-expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
              <input id="vid-expiry" name="expiry" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" />
            </div>
            <div>
              <label for="vid-file" class="block text-sm font-medium text-gray-700">Upload MP4 (max 50MB)</label>
              <input id="vid-file" name="video" type="file" accept="video/mp4" class="mt-1 block w-full" />
            </div>
          </div>
          <div class="flex space-x-2">
            <button type="submit" id="vid-upload-save" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Upload &amp; Save</button>
            <button type="button" id="vid-cancel-btn" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
          </div>
        </form>

        <!-- Videos list -->
        <div id="videos-list" class="space-y-4">
          <!-- video items will render here -->
        </div>
      </section>

      <section id="tab-announcements-content" class="tab-content hidden bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Announcements</h3>
          <button id="add-announcement-btn" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">Add Announcement</button>
        </div>

        <!-- Announcement form (hidden by default) -->
        <form id="announcement-form" class="hidden space-y-3 mb-6">
          <div>
            <label for="ann-title" class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
            <input id="ann-title" name="title" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" placeholder="Announcement title" />
          </div>
          <div>
            <label for="ann-message" class="block text-sm font-medium text-gray-700">Announcement <span class="text-red-500">*</span></label>
            <textarea id="ann-message" name="message" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" placeholder="Write announcement message..."></textarea>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="ann-expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
              <input id="ann-expiry" name="expiry" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" />
            </div>
            <div>
              <label for="ann-textsize" class="block text-sm font-medium text-gray-700">Text Size</label>
              <select id="ann-textsize" name="text_size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2">
                <option value="SMALL">SMALL</option>
                <option value="MEDIUM" selected>MEDIUM</option>
                <option value="LARGE">LARGE</option>
              </select>
            </div>
          </div>
          <div class="flex space-x-2">
            <button type="submit" id="ann-save-btn" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Create &amp; Save</button>
            <button type="button" id="ann-cancel-btn" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
          </div>
        </form>

        <!-- Announcements list -->
        <div id="announcements-list" class="space-y-4">
          <!-- Announcement cards will be rendered here -->
        </div>
      </section>

      <section id="tab-books-content" class="tab-content hidden bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Featured Books</h3>
          <button id="add-book-btn" class="bg-yellow-500 text-white px-3 py-2 rounded hover:bg-yellow-600">Add Book</button>
        </div>

        <div id="books-list" class="space-y-4"></div>
      </section>

      <section id="tab-footer-content" class="tab-content hidden bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Footer</h3>
          <button id="new-footer-msg-btn" class="bg-gray-800 text-white px-3 py-2 rounded hover:bg-gray-900">New Message</button>
        </div>

        <!-- Footer messages list -->
        

        <!-- Footer messages list -->
        <div id="footer-msgs-list" class="space-y-4">
          <!-- Footer messages will be rendered here -->
        </div>
      </section>
    </main>
  </div>
  
  <!-- Footer modal moved outside #app-root so it remains unblurred -->
  <div id="footer-modal" class="hidden fixed inset-0 z-40 flex items-center justify-center">
    <div id="footer-modal-backdrop" class="absolute inset-0 bg-black bg-opacity-40"></div>
    <div class="relative bg-white rounded shadow-lg w-11/12 md:w-2/3 lg:w-1/2 p-6 max-h-[80vh] overflow-auto">
      <div class="flex justify-between items-center mb-4">
        <h4 class="text-lg font-semibold">New Footer Message</h4>
        <button id="footer-modal-close" class="text-gray-500 hover:text-gray-700">&times;</button>
      </div>

      <form id="new-footer-form" class="space-y-3 mb-6">
        <input type="hidden" id="footer-id" name="footer-id" value="" />
        <div>
          <label for="footer-message" class="block text-sm font-medium text-gray-700">Message <span class="text-red-500">*</span></label>
          <input id="footer-message" name="footer-message" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" placeholder="Footer message" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label for="scroll-speed" class="block text-sm font-medium text-gray-700">Scroll Speed</label>
            <input id="scroll-speed" name="scroll-speed" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2" placeholder="e.g. 50" />
          </div>
          <div>
            <label for="footer-expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
            <input id="footer-expiry" name="footer-expiry" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2" />
          </div>
        </div>
        <div class="flex space-x-2">
          <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Create &amp; Save</button>
          <button id="cancel-new-footer" type="button" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../js/dashboard-refactored.js"></script>
  <script src="../js/announcements.js"></script>
  <script src="../js/videos.js"></script>
  <!-- books listing handled by dashboard-refactored.js; removed duplicate books.js include -->
  <!-- Book modal (cleaned up) -->
  <div id="book-modal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded shadow-lg w-11/12 md:w-3/4 lg:w-1/2 p-6 max-h-[90vh] overflow-auto">
      <div class="flex justify-between items-center mb-4">
        <h4 class="text-lg font-semibold">Add New Book</h4>
        <button id="book-modal-close" class="text-gray-500 hover:text-gray-700">&times;</button>
      </div>

      <form id="book-form" class="space-y-3 mb-6" enctype="multipart/form-data">
        <div>
          <label for="book-title" class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
          <input id="book-title" name="book-title" type="text" class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2" required />
        </div>
        <div>
          <label for="book-author" class="block text-sm font-medium text-gray-700">Author <span class="text-red-500">*</span></label>
          <input id="book-author" name="book-author" type="text" class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2" required />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label for="book-category" class="block text-sm font-medium text-gray-700">Category</label>
            <select id="book-category" name="book-category" class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2"></select>
          </div>
          <div>
            <label for="book-availability" class="block text-sm font-medium text-gray-700">Availability</label>
            <select id="book-availability" name="book-availability" class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2"></select>
          </div>
          <div>
            <label for="book-expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
            <input id="book-expiry" name="book-expiry" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2" />
          </div>
        </div>
        <div>
          <label for="book-cover" class="block text-sm font-medium text-gray-700">Cover Image</label>
          <input id="book-cover" name="book-cover" type="file" accept="image/*" class="mt-1 block w-full" />
        </div>
        <div class="flex space-x-2">
          <button id="book-save" type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded">Add &amp; Save</button>
          <button id="book-cancel" type="button" class="bg-gray-200 px-4 py-2 rounded">Cancel</button>
        </div>
      </form>
    </div>
  </div>
  <footer class="dashboard-footer">
    &copy; Created and Owned By Quezon City Government</footer>
  
  <!-- Logout confirmation modal (outside #app-root so blur doesn't hide it) -->
  <div id="logout-confirmation" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div id="logout-confirmation-backdrop" class="absolute inset-0 bg-black bg-opacity-40"></div>
    <div class="relative bg-white rounded shadow-lg w-11/12 md:w-1/3 p-6">
      <div class="mb-4">
        <h4 class="text-lg font-semibold">Confirm Logout</h4>
      </div>
      <p class="text-gray-700 mb-6">Are you sure you want to log out?</p>
      <div class="flex justify-end space-x-2">
        <button id="logout-cancel" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Cancel</button>
        <button id="logout-confirm" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Log Out</button>
      </div>
    </div>
  </div>

  <!-- Loader overlay (will show for 3s on logout) -->
  <!-- Loader element -->
<div id="logout-loader" class="hidden fixed inset-0 z-60 flex items-center justify-center bg-white">
  <div class="loader"></div>
</div>

<!-- Loader styles -->
<style>
  .loader {
    width: 45px;
    height: 40px;
    background: linear-gradient(#fff calc(1*100%/6), #ce0b22 0 calc(3*100%/6), #0000 0),
                linear-gradient(#fff calc(2*100%/6), #0139a5 0 calc(4*100%/6), #0000 0),
                linear-gradient(#fff calc(3*100%/6), #facf00 0 calc(5*100%/6), #0000 0);
    background-size: 10px 400%;
    background-repeat: no-repeat;
    animation: matrix 1s infinite linear;
  }

  @keyframes matrix {
    0%   { background-position: 0% 100%, 50% 100%, 100% 100%; }
    100% { background-position: 0% 0%,   50% 0%,   100% 0%; }
  }

</body>
</html>
