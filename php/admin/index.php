
<?php
session_start();

// Require login: if there's no user session, redirect to login page
if (!isset($_SESSION['user_id'])) {
  // Redirect to absolute login path to avoid wrong base URL
  header('Location: /QCPLibrary/php/admin/login/login.php');
  exit;
}

// Ensure CSRF token exists for this session to allow POST-only actions from the client
if (empty($_SESSION['csrf_token'])) {
  try {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  } catch (Exception $e) {
    // fallback
    $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
  }
}
?>

<?php
// Admin Dashboard migrated from dashboard.html
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <script>
      // Set CSRF token from PHP for admin-head.js
      window.CSRF_TOKEN_PHP = <?php echo json_encode($_SESSION['csrf_token']); ?>;
      // Expose BranchId to client scripts for API calls
      window.BRANCH_ID = <?php echo json_encode(intval($_SESSION['branch_id'] ?? ($_SESSION['BranchId'] ?? ($_SESSION['admin_branch'] ?? 0)))); ?>;
    </script>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>QC Library Digital Signage - Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../css/dashboard.css" />
  <script src="/QCPLibrary/js/admin-head.js"></script>
</head>
<body class="h-full bg-gray-50 text-gray-900 font-montserrat">
  <div id="app-root" class="flex h-full">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
      <div class="p-6 flex items-center space-x-3 border-b border-gray-200">
        <div class="w-20 h-16 rounded-md overflow-hidden">
          <img src=/QCPLibrary/image/image.png alt="QC Library Logo" class="w-full h-full object-fill" />
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
        <p id="branch-info" class="mt-1 font-semibold text-gray-700">
          <?php echo htmlspecialchars($_SESSION['district'] ?? ($_SESSION['district_id'] ?? 'Unknown District')); ?> /
          <?php echo htmlspecialchars($_SESSION['branch'] ?? ($_SESSION['branch_id'] ?? 'Unknown Branch')); ?>
        </p>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-auto">
      <header class="flex justify-between items-center mb-6">
        <div>
          <h2 class="text-2xl font-bold">Dashboard</h2>
          <p class="text-gray-600" id="welcome-msg">
            Logged in as: <?php echo htmlspecialchars($_SESSION['admin_name'] ?? ($_SESSION['username'] ?? '')); ?>
          </p>
        </div>
  <a id="preview-signage" href="/QCPLibrary/php/signage.php" target="_blank" class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700 inline-block text-center">Preview Signage</a>
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
            <label for="vid-desc" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="vid-desc" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" placeholder="Video description..."></textarea>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="vid-file" class="block text-sm font-medium text-gray-700">Upload MP4 (max 50MB) <span class="text-red-500">*</span></label>
              <input id="vid-file" name="video" type="file" accept="video/mp4" required class="mt-1 block w-full" />
            </div>
          </div>
          <div class="flex space-x-2">
            <button type="submit" id="vid-upload-save" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Upload &amp; Save</button>
            <button type="button" id="vid-cancel-btn" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
          </div>
        </form>

        <!-- Videos list -->
        <div id="videos-list" class="space-y-4">
          <p class="text-gray-500">No videos yet</p>
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
            <input id="ann-title" name="title" type="text" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" placeholder="Announcement title" />
          </div>
          <div>
            <label for="ann-message" class="block text-sm font-medium text-gray-700">Announcement <span class="text-red-500">*</span></label>
            <textarea id="ann-message" name="content" rows="3" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" placeholder="Write announcement message..."></textarea>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="ann-expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
              <input id="ann-expiry" name="expiry_date" type="datetime-local" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" />
            </div>
            <div>
              <label for="ann-textsize" class="block text-sm font-medium text-gray-700">Text Size</label>
              <select id="ann-textsize" name="text_size" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2">
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

        <!-- Add Book Inline Form (hidden by default) -->
        <form id="book-form" class="hidden space-y-3 mb-6" enctype="multipart/form-data">
          <div>
            <label for="book-title" class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
            <input id="book-title" name="book-title" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" placeholder="Book title" />
          </div>
          <div>
            <label for="book-author" class="block text-sm font-medium text-gray-700">Author <span class="text-red-500">*</span></label>
            <input id="book-author" name="book-author" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" placeholder="Author name" />
          </div>
          <div>
            <label for="book-category" class="block text-sm font-medium text-gray-700">Category</label>
            <input id="book-category" name="book-category" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" placeholder="Category" />
          </div>
          <div>
            <label for="book-description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="book-description" name="book-description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" placeholder="Book description..."></textarea>
          </div>
          <div>
            <label for="book-availability" class="block text-sm font-medium text-gray-700">Availability</label>
            <select id="book-availability" name="book-availability" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2">
              <option value="Available">Available</option>
              <option value="Borrowed">Borrowed</option>
            </select>
          </div>
          <div>
            <label for="book-expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
            <input id="book-expiry" name="book-expiry" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" />
          </div>
          <div>
            <label for="book-cover" class="block text-sm font-medium text-gray-700">Cover Image (max 50MB) <span class="text-red-500">*</span></label>
            <input id="book-cover" name="book-cover" type="file" accept="image/*" class="mt-1 block w-full" />
          </div>
          <div class="flex space-x-2">
            <button type="submit" id="book-save" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Add &amp; Save</button>
            <button type="button" id="book-cancel" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
          </div>
        </form>

        <div id="books-list" class="space-y-4"></div>
      </section>

      <section id="tab-footer-content" class="tab-content hidden bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Footer</h3>
          <button id="new-footer-msg-btn" class="bg-gray-800 text-white px-3 py-2 rounded hover:bg-gray-900">New Message</button>
        </div>

        <!-- New Footer Message Inline Form (hidden by default) -->
        <form id="new-footer-form" class="hidden space-y-3 mb-6">
          <div>
            <label for="footer-message" class="block text-sm font-medium text-gray-700">Message <span class="text-red-500">*</span></label>
            <input id="footer-message" name="footer-message" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" placeholder="Footer message" />
          </div>
          <div>
            <label for="scroll-speed" class="block text-sm font-medium text-gray-700">Scroll Speed</label>
            <input id="scroll-speed" name="scroll-speed" type="number" min="1" max="20" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" placeholder="Scroll speed (1-20)" />
          </div>
          <div>
            <label for="footer-expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
            <input id="footer-expiry" name="footer-expiry" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" />
          </div>
          <div class="flex space-x-2">
            <button type="submit" id="footer-save" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Create &amp; Save</button>
            <button type="button" id="footer-cancel" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
          </div>
        </form>

        <div id="footer-msgs-list" class="space-y-4">
          <!-- Footer messages will be rendered here -->
        </div>
      </section>
    </main>
  </div>
  

  <!-- CSRF token and Tailwind config moved to admin-head.js -->
  <script src="/QCPLibrary/js/delete-modal.js"></script>
  <script src="/QCPLibrary/js/dashboard-refactored.js"></script>
  <script src="/QCPLibrary/js/announcements.js"></script>
  <script src="/QCPLibrary/js/books.js"></script>
  <script src="/QCPLibrary/js/footer.js"></script>
  <script src="/QCPLibrary/js/videos.js"></script>
  <footer class="dashboard-footer">
    &copy; Created and Owned By Quezon City Government</footer>
  
  <!-- Success Modal (reusable for all upload operations) -->
  <div id="success-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    <div class="relative bg-white rounded-lg shadow-xl w-11/12 md:w-1/3 p-8 text-center">
      <div class="mb-4">
        <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <h3 id="success-modal-title" class="text-xl font-bold text-gray-800 mb-2">Success!</h3>
      <p id="success-modal-message" class="text-gray-600 mb-6">Operation completed successfully.</p>
      <button id="success-modal-btn" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">OK</button>
    </div>
  </div>
  
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

<!-- Video Upload Success Modal with Preview -->
<div id="video-success-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <div id="video-success-backdrop" class="absolute inset-0 bg-black bg-opacity-50"></div>
  <div class="relative bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2 p-6 max-h-screen overflow-y-auto">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-bold text-green-600">✓ Video Uploaded Successfully!</h3>
      <button id="close-video-success" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
    </div>
    
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded">
      <p class="text-green-700"><strong>Title:</strong> <span id="modal-video-title"></span></p>
      <p class="text-green-700 text-sm"><strong>Uploaded:</strong> <span id="modal-video-time"></span></p>
    </div>
    
    <div class="mb-4">
      <label class="block text-sm font-semibold text-gray-700 mb-2">Video Preview:</label>
      <video id="modal-video-player" class="w-full h-auto rounded border border-gray-300" controls muted>
        <source id="modal-video-src" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    </div>
    
    <div class="flex justify-end space-x-3">
      <button id="upload-another-btn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Upload Another</button>
      <button id="close-video-success-btn" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Done</button>
    </div>
  </div>
</div>

<!-- Video Edit Modal -->
<div id="video-edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <div id="video-edit-backdrop" class="absolute inset-0 bg-black bg-opacity-50"></div>
  <div class="relative bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2 p-6 max-h-screen overflow-y-auto">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-bold">Edit Video</h3>
      <button id="close-edit-modal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
    </div>
    
    <form id="edit-video-form" class="space-y-4">
      <input type="hidden" id="edit-video-id" value="">
      
      <div>
        <label for="edit-vid-title" class="block text-sm font-medium text-gray-700">Video Title <span class="text-red-500">*</span></label>
        <input id="edit-vid-title" type="text" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" placeholder="Video title" />
      </div>
      
      <div>
        <label for="edit-vid-desc" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea id="edit-vid-desc" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" placeholder="Video description..."></textarea>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Current Video</label>
        <video id="edit-current-video" class="w-full h-auto rounded border border-gray-300" controls muted>
          <source id="edit-current-video-src" type="video/mp4">
        </video>
      </div>
      
      <div>
        <label for="edit-vid-file" class="block text-sm font-medium text-gray-700">Replace Video (MP4, max 50MB)</label>
        <input id="edit-vid-file" type="file" accept="video/mp4" class="mt-1 block w-full" />
        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current video</p>
      </div>
      
      <div class="flex justify-end space-x-3">
        <button type="button" id="cancel-edit-btn" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
        <button type="submit" id="save-edit-btn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Announcement Edit Modal -->
<div id="announcement-edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <div id="announcement-edit-backdrop" class="absolute inset-0 bg-black bg-opacity-50"></div>
  <div class="relative bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2 p-6 max-h-screen overflow-y-auto">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-bold">Edit Announcement</h3>
      <button id="close-ann-edit-modal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
    </div>
    
    <form id="announcement-edit-form" class="space-y-4">
      <input type="hidden" id="ann-edit-id" value="">
      
      <div>
        <label for="ann-edit-title" class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
        <input id="ann-edit-title" type="text" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" placeholder="Announcement title" />
      </div>
      
      <div>
        <label for="ann-edit-content" class="block text-sm font-medium text-gray-700">Content <span class="text-red-500">*</span></label>
        <textarea id="ann-edit-content" rows="4" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" placeholder="Announcement content..."></textarea>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="ann-edit-expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
          <input id="ann-edit-expiry" type="datetime-local" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" />
        </div>
        <div>
          <label for="ann-edit-textsize" class="block text-sm font-medium text-gray-700">Text Size</label>
          <select id="ann-edit-textsize" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2">
            <option value="SMALL">SMALL</option>
            <option value="MEDIUM" selected>MEDIUM</option>
            <option value="LARGE">LARGE</option>
          </select>
        </div>
      </div>
      
      <div class="flex justify-end space-x-3">
        <button type="button" id="cancel-ann-edit-btn" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Book Edit Modal -->
<div id="book-edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <div id="book-edit-backdrop" class="absolute inset-0 bg-black bg-opacity-50"></div>
  <div class="relative bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2 p-6 max-h-screen overflow-y-auto">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-bold">Edit Book</h3>
      <button id="close-book-edit-modal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
    </div>
    
    <form id="book-edit-form" class="space-y-4">
      <input type="hidden" id="book-edit-id" value="">
      
      <div>
        <label for="book-edit-title" class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
        <input id="book-edit-title" type="text" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" placeholder="Book title" />
      </div>
      
      <div>
        <label for="book-edit-author" class="block text-sm font-medium text-gray-700">Author <span class="text-red-500">*</span></label>
        <input id="book-edit-author" type="text" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" placeholder="Author name" />
      </div>
      
      <div>
        <label for="book-edit-category" class="block text-sm font-medium text-gray-700">Category</label>
        <input id="book-edit-category" type="text" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" placeholder="Category" />
      </div>
      
      <div>
        <label for="book-edit-description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea id="book-edit-description" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" placeholder="Book description..."></textarea>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="book-edit-availability" class="block text-sm font-medium text-gray-700">Availability</label>
          <select id="book-edit-availability" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2">
            <option value="Available">Available</option>
            <option value="Unavailable">Unavailable</option>
          </select>
        </div>
        <div>
          <label for="book-edit-expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
          <input id="book-edit-expiry" type="datetime-local" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" />
        </div>
      </div>
      
      <div id="book-edit-cover-section">
        <label class="block text-sm font-medium text-gray-700 mb-2">Current Cover</label>
        <img id="book-edit-current-cover" class="w-24 h-36 object-cover rounded mb-3" alt="Current cover" />
      </div>
      
      <div>
        <label for="book-edit-cover" class="block text-sm font-medium text-gray-700">Replace Cover Image (max 50MB)</label>
        <input id="book-edit-cover" type="file" accept="image/*" class="mt-1 block w-full" />
        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current cover</p>
      </div>
      
      <div class="flex justify-end space-x-3">
        <button type="button" id="cancel-book-edit-btn" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Footer Edit Modal -->
<div id="footer-edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <div id="footer-edit-backdrop" class="absolute inset-0 bg-black bg-opacity-50"></div>
  <div class="relative bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2 p-6 max-h-screen overflow-y-auto">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-bold">Edit Footer Message</h3>
      <button id="close-footer-edit-modal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
    </div>
    
    <form id="footer-edit-form" class="space-y-4">
      <input type="hidden" id="footer-edit-id" value="">
      
      <div>
        <label for="footer-edit-message" class="block text-sm font-medium text-gray-700">Message <span class="text-red-500">*</span></label>
        <textarea id="footer-edit-message" rows="2" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" placeholder="Footer message..."></textarea>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="footer-edit-speed" class="block text-sm font-medium text-gray-700">Scroll Speed</label>
          <input id="footer-edit-speed" type="number" min="1" max="20" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" placeholder="Scroll speed (1-20)" />
        </div>
        <div>
          <label for="footer-edit-expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
          <input id="footer-edit-expiry" type="datetime-local" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm px-3 py-2" />
        </div>
      </div>
      
      <div class="flex justify-end space-x-3">
        <button type="button" id="cancel-footer-edit-btn" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
      </div>
    </form>
  </div>
</div>



</body>
</html>
