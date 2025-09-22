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
  <link rel="stylesheet" href="dashboard.css" />
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
  <div class="flex h-full">
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
          <!-- ...existing code for overview cards... -->
        </div>
        <div class="bg-gray-50 p-4 rounded shadow">
          <p class="text-gray-900">You are managing <span id="summary-videos">0 video(s)</span>, <span id="summary-announcements">0 announcement(s)</span>, and <span id="summary-books">0 book(s)</span> for this branch.</p>
          <p class="text-gray-900">Click on a category above to manage its content.</p>
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

  <form id="book-form" class="hidden space-y-3 mb-6" enctype="multipart/form-data">
          <div>
            <label class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
            <input id="book-title" type="text" class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2" required />
          </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Author <span class="text-red-500">*</span></label>
              <input id="book-author" type="text" class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2" required />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select id="book-category" class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2"></select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Availability</label>
                <select id="book-availability" class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2"></select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
                <input id="book-expiry" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2" />
              </div>
            </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Cover Image</label>
                <input id="book-cover" name="cover" type="file" accept="image/*" class="mt-1 block w-full" />
              </div>
            <div class="flex space-x-2">
              <button id="book-save" class="bg-yellow-600 text-white px-4 py-2 rounded">Add &amp; Save</button>
              <button id="book-cancel" type="button" class="bg-gray-200 px-4 py-2 rounded">Cancel</button>
            </div>
          </form>

        <div id="books-list" class="space-y-4"></div>
      </section>

      <section id="tab-footer-content" class="tab-content hidden bg-white p-6 rounded shadow">
        <!-- ...existing code for footer tab... -->
      </section>
    </main>
  </div>

  <script src="dashboard-refactored.js"></script>
  <script>
    // Announcements feature: server-backed CRUD via php/announcements.php
    (function(){
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
    })();
  </script>
  <script>
    // Videos upload/list/delete via php/videos.php
    (function(){
      const API = 'videos.php';
      const uploadBtn = document.getElementById('upload-video-btn');
      const form = document.getElementById('video-upload-form');
      const cancelBtn = document.getElementById('vid-cancel-btn');
      const videosList = document.getElementById('videos-list');

      uploadBtn.addEventListener('click', ()=>{
        form.reset(); form.classList.remove('hidden'); uploadBtn.disabled = true; document.getElementById('vid-title').focus();
      });

      cancelBtn.addEventListener('click', ()=>{ form.reset(); form.classList.add('hidden'); uploadBtn.disabled = false; });

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

      loadVideos();
    })();
  </script>
  <script>
    // Simple Books Display - Direct database fetch
    (function(){
      const booksList = document.getElementById('books-list');

      async function loadBooks(){
        try {
          console.log('Fetching books from database...');
          const res = await fetch('books.php');
          const json = await res.json();
          
          console.log('Response:', json);
          
          booksList.innerHTML = '';
          
          if (!json.success) {
            booksList.innerHTML = `<p class="text-red-500">Error: ${json.error}</p>`;
            return;
          }
          
          const books = json.data || [];
          console.log('Books found:', books.length);
          
          // Show count
          if (books.length === 0) {
            booksList.innerHTML = '<p class="text-gray-500">No books found in database.</p>';
            return;
          }
          
          // Display count header
          const countDiv = document.createElement('div');
          booksList.appendChild(countDiv);
          
          // Display each book
          books.forEach(book => {
            const card = document.createElement('div');
            card.className = 'p-4 border rounded shadow-sm bg-white mb-3';
            
            let coverHtml = '';
            if (book.cover && book.cover !== 'NULL' && book.cover.trim() !== '') {
              coverHtml = `<img src="../assets/uploads/book_covers/${encodeURIComponent(book.cover)}" alt="Book cover" class="w-16 h-20 object-cover rounded mr-4" onerror="this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"64\" height=\"80\" fill=\"%23ccc\"><rect width=\"64\" height=\"80\" fill=\"%23f0f0f0\"/><text x=\"32\" y=\"45\" text-anchor=\"middle\" fill=\"%23999\" font-size=\"10\">No Image</text></svg>'" />`;
            } else {
              coverHtml = `<div class="w-16 h-20 bg-gray-200 flex items-center justify-center rounded mr-4 text-gray-400 text-xs">No Cover</div>`;
            }
            
            const expiryHtml = book.expiry ? 
              `<p class="text-xs text-gray-400">Expires: ${new Date(book.expiry).toLocaleString()}</p>` : '';
            
            card.innerHTML = `
              <div class="flex justify-between items-start">
                <div class="flex items-start">
                  ${coverHtml}
                  <div>
                    <h4 class="font-semibold">${escapeHtml(book.title)}</h4>
                    <p class="text-sm text-gray-600 mt-1">by ${escapeHtml(book.author)}</p>
                    ${book.category ? `<p class="text-xs text-gray-500 mt-1">Category: ${escapeHtml(book.category)}</p>` : ''}
                    ${book.status ? `<p class="text-xs text-gray-500">Status: ${escapeHtml(book.status)}</p>` : ''}
                    ${book.description ? `<p class="text-xs text-gray-600 mt-1">${escapeHtml(book.description)}</p>` : ''}
                    <p class="text-xs text-gray-400 mt-2">ID: ${book.id}</p>
                    ${book.created_at ? `<p class="text-xs text-gray-400">Added: ${new Date(book.created_at).toLocaleString()}</p>` : ''}
                    ${expiryHtml}
                  </div>
                </div>
              </div>
            `;
            
            booksList.appendChild(card);
          });
          
        } catch (error) {
          console.error('Error loading books:', error);
          booksList.innerHTML = `<p class="text-red-500">Network Error: ${error.message}</p>`;
        }
      }

      function escapeHtml(str) {
        return String(str || '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      // Load books immediately
      loadBooks();
    })();
  </script>
  <footer class="dashboard-footer">
    &copy; Created and Owned By Quezon City Government</footer>
</body>
</html>
