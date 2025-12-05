// --------------------------------------------------
// QC Library Digital Signage - Admin Dashboard Script
// --------------------------------------------------
// Author: Quezon City Public Library — Developers' Hub
// Description: Refactored JavaScript for Admin Dashboard
// --------------------------------------------------

const tabs = ['overview', 'videos', 'announcements', 'books', 'footer'];
let editingIndex = -1;
// hold the currently loaded books so click handlers can show details
let currentBooks = [];

// --------------------
// Utility Functions
// --------------------
function $(id) {
  return document.getElementById(id);
}

function showTab(tab) {
  tabs.forEach(t => {
    const content = document.getElementById(`tab-${t}-content`);
    const btn = document.getElementById(`tab-btn-${t}`);
    if (content) content.classList.add('hidden');
    if (btn) {
      btn.classList.remove('bg-primary', 'text-white');
      btn.classList.add('hover:bg-gray-200', 'text-gray-600');
    }
  });
  const activeContent = document.getElementById(`tab-${tab}-content`);
  const activeBtn = document.getElementById(`tab-btn-${tab}`);
  if (activeContent) activeContent.classList.remove('hidden');
  if (activeBtn) {
    activeBtn.classList.add('bg-primary', 'text-white');
    activeBtn.classList.remove('hover:bg-gray-200', 'text-gray-600');
  }
}

// --------------------
// Data Management
// --------------------
function getData(key) {
  try { return JSON.parse(localStorage.getItem(key) || '[]'); }
  catch (e) { return []; }
}

function saveData(key, data) {
  localStorage.setItem(key, JSON.stringify(data));
}

// --------------------
// Dashboard Counts
// --------------------
function loadCounts() {
  // Try to fetch counts from server endpoints. If any fetch fails, fall back to localStorage counts.
  const now = Date.now();

  const safeNumber = v => (Number.isFinite(v) ? v : 0);

  // helper: determine if an item is not expired (supports several expiry field names)
  const isNotExpired = (it) => {
    try {
      if (!it) return false;
      const exp = it.expiry || it.expiryDate || it.expire || it.expired_on || null;
      if (!exp) return true; // no expiry -> consider active
      const t = Date.parse(exp);
      if (Number.isNaN(t)) return true; // unparsable -> treat active
      return t > now;
    } catch (e) { return true; }
  };

  // boolean-like check for values coming from server/localStorage
  const isTruthyFlag = (v) => v === true || v === 1 || v === '1' || String(v).toLowerCase() === 'true' || String(v).toLowerCase() === 'yes';

  // Count logic per-type (videos, announcements, books, footers) to better reflect what is actually shown on signage:
  // - videos/announcements: must have an active flag (truthy) and not be expired
  // - footers: must have a non-empty message and not be expired
  // - books: must not be expired and should not be explicitly unavailable (best-effort based on availability/status text)

  Promise.all([
    fetch('videos.php').then(r => r.json()).catch(() => ({ success: false })),
    fetch('announcements.php').then(r => r.json()).catch(() => ({ success: false })),
    fetch('books.php').then(r => r.json()).catch(() => ({ success: false })),
    fetch('footer_messages.php').then(r => r.json()).catch(() => ({ success: false }))
  ]).then(([vRes, aRes, bRes, fRes]) => {
    let videos = [];
    let announcements = [];
    let books = [];
    let footers = [];

    if (vRes && vRes.success && Array.isArray(vRes.data)) videos = vRes.data;
    else videos = getData('videos') || [];

    if (aRes && aRes.success && Array.isArray(aRes.data)) announcements = aRes.data;
    else announcements = getData('announcements') || [];

    if (bRes && bRes.success && Array.isArray(bRes.data)) books = bRes.data;
    else books = getData('books') || [];

    if (fRes && fRes.success && Array.isArray(fRes.data)) footers = fRes.data;
    else footers = getData('footers') || [];

    const cv = document.getElementById('count-videos'); if (cv) cv.textContent = safeNumber(videos.length);
    const ca = document.getElementById('count-announcements'); if (ca) ca.textContent = safeNumber(announcements.length);
    const cb = document.getElementById('count-books'); if (cb) cb.textContent = safeNumber(books.length);
    const cf = document.getElementById('count-footers'); if (cf) cf.textContent = safeNumber(footers.length);

    const sv = document.getElementById('summary-videos'); if (sv) sv.textContent = `${safeNumber(videos.length)} video(s)`;
    const sa = document.getElementById('summary-announcements'); if (sa) sa.textContent = `${safeNumber(announcements.length)} announcement(s)`;
    const sb = document.getElementById('summary-books'); if (sb) sb.textContent = `${safeNumber(books.length)} book(s)`;

  // Active on screen: count items that actually would be shown on signage.
  const videosActive = Array.isArray(videos) ? videos.filter(v => isTruthyFlag(v.active) && isNotExpired(v)).length : 0;
  const announcementsActive = Array.isArray(announcements) ? announcements.filter(a => isTruthyFlag(a.active) && isNotExpired(a)).length : 0;
  // books: consider a book "active on screen" if it has no expiry (or not expired) and availability isn't explicitly 'UNAVAILABLE' or similar
  const booksActive = Array.isArray(books) ? books.filter(b => isNotExpired(b) && !(String(b.availability || '').toLowerCase().includes('unavail') || String(b.availability || '').toLowerCase().includes('not available'))).length : 0;
  // footers: must have a message and not be expired
  const footersActive = Array.isArray(footers) ? footers.filter(f => f && (f.message || f.msg || f.text || '').toString().trim() !== '' && isNotExpired(f)).length : 0;

  const activeCount = videosActive + announcementsActive + booksActive + footersActive;
  const ael = document.getElementById('active-on-screen'); if (ael) ael.textContent = safeNumber(activeCount);
  }).catch(err => {
    // network error -> fallback to localStorage-only counts
    console.warn('Failed to fetch counts from server, falling back to localStorage', err);
    const videos = getData('videos');
    const announcements = getData('announcements');
    const books = getData('books');
    const footers = getData('footers');

    const cv = document.getElementById('count-videos'); if (cv) cv.textContent = videos.length;
    const ca = document.getElementById('count-announcements'); if (ca) ca.textContent = announcements.length;
    const cb = document.getElementById('count-books'); if (cb) cb.textContent = books.length;
    const cf = document.getElementById('count-footers'); if (cf) cf.textContent = footers.length;

    const sv = document.getElementById('summary-videos'); if (sv) sv.textContent = `${videos.length} video(s)`;
    const sa = document.getElementById('summary-announcements'); if (sa) sa.textContent = `${announcements.length} announcement(s)`;
    const sb = document.getElementById('summary-books'); if (sb) sb.textContent = `${books.length} book(s)`;

  const videosActive = Array.isArray(videos) ? videos.filter(v => isTruthyFlag(v.active) && isNotExpired(v)).length : 0;
  const announcementsActive = Array.isArray(announcements) ? announcements.filter(a => isTruthyFlag(a.active) && isNotExpired(a)).length : 0;
  const booksActive = Array.isArray(books) ? books.filter(b => isNotExpired(b) && !(String(b.availability || '').toLowerCase().includes('unavail') || String(b.availability || '').toLowerCase().includes('not available'))).length : 0;
  const footersActive = Array.isArray(footers) ? footers.filter(f => f && (f.message || f.msg || f.text || '').toString().trim() !== '' && isNotExpired(f)).length : 0;
  const activeCount = videosActive + announcementsActive + booksActive + footersActive;
  const ael = document.getElementById('active-on-screen'); if (ael) ael.textContent = activeCount;
  });
}

// --------------------
// Generic List Rendering
// --------------------
function renderList(containerId, items, renderItem) {
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = '';
  if (!items || items.length === 0) {
    container.innerHTML = `<p class="text-gray-500">No ${containerId.replace('-list', '').replace(/-/g, ' ')} yet</p>`;
    return;
  }
  items.forEach((item, index) => {
    const element = renderItem(item, index);
    container.appendChild(element);
  });
}

// --------------------
// Video Management
// --------------------
function createVideoElement(video, index) {
  const div = document.createElement('div');
  div.className = 'p-4 border rounded mb-2 bg-gray-100 flex justify-between items-center';
  div.innerHTML = `
    <div>
      <h4 class="font-semibold">${video.title}</h4>
      <p>${video.description}</p>
      <p>Duration: ${video.duration} seconds</p>
      <p>Status: ${video.active ? 'Active' : 'Inactive'}</p>
      <p>Loop: ${video.loop ? 'Yes' : 'No'}</p>
      <p>Expiry Date: ${video.expiryDate || 'None'}</p>
      <p>Pinned: ${video.pin ? 'Yes' : 'No'}</p>
    </div>
    <button class="delete-video-btn bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700" data-index="${index}">Delete</button>
  `;
  return div;
}

function loadVideos() {
  const videos = getData('videos');
  renderList('videos-list', videos, createVideoElement);
}

function handleVideoFormSubmit(e) {
  e.preventDefault();
  // If this is the server-managed form (with #vid-title), do nothing.
  try {
    const t = e && e.target ? e.target : null;
    if (t && typeof t.querySelector === 'function' && t.querySelector('#vid-title')) {
      return; // videos.js will handle this submission
    }
  } catch (_) {}
  const form = e.target;
  const title = form['video-title'] ? form['video-title'].value.trim() : '';
  const description = form['video-description'] ? form['video-description'].value.trim() : '';
  let duration = parseInt(form['video-duration'] ? form['video-duration'].value : '0', 10);
  const durationUnit = form['video-duration-unit'] ? form['video-duration-unit'].value : 'seconds';
  const active = form['video-active'] ? form['video-active'].checked : false;
  const loop = form['video-loop'] ? form['video-loop'].checked : false;
  const expiryDate = form['video-expiry'] ? form['video-expiry'].value : '';
  const pin = form['video-pin'] ? form['video-pin'].checked : false;
  const fileInput = form['video-file'];
  const file = fileInput && fileInput.files ? fileInput.files[0] : null;

  if (!title) return alert('Title is required.');
  if (!duration || duration <= 0) return alert('Duration must be a positive number.');
  if (!file) return alert('Please select a video file.');
  if (file.type !== 'video/mp4') return alert('Please select an MP4 video file.');
  if (file.size > 50 * 1024 * 1024) return alert('File size must be 50MB or less.');

  if (durationUnit === 'minutes') duration *= 60;
  else if (durationUnit === 'hours') duration *= 3600;

  const videos = getData('videos');
  videos.push({
    title,
    description,
    duration,
    active,
    loop,
    expiryDate,
    pin,
    fileName: file.name,
    uploadedAt: new Date().toISOString()
  });
  saveData('videos', videos);
  form.reset();
  form.classList.add('hidden');
  const uploadBtn = document.getElementById('upload-video-btn'); if (uploadBtn) uploadBtn.classList.remove('hidden');
  loadVideos();
  loadCounts();
}

// --------------------
// Announcement Management
// --------------------
function createAnnouncementElement(announcement, index) {
  const div = document.createElement('div');
  div.className = 'p-4 border rounded mb-2 bg-gray-100 flex justify-between items-center';
  div.innerHTML = `
    <div>
      <h4 class="font-semibold">${announcement.title}</h4>
      <p>${announcement.content}</p>
      <p>Duration: ${announcement.duration} seconds</p>
      <p>Status: ${announcement.active ? 'Active' : 'Inactive'}</p>
      <p>Expiry Date: ${announcement.expiryDate || 'None'}</p>
      <p>Pinned: ${announcement.pin ? 'Yes' : 'No'}</p>
      <p>Text Size: ${announcement.textSize}</p>
    </div>
    <div>
      <button class="edit-announcement-btn bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 mr-2" data-index="${index}">Edit</button>
      <button class="delete-announcement-btn bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700" data-index="${index}">Delete</button>
    </div>
  `;
  return div;
}

function loadAnnouncements() {
  const announcements = getData('announcements');
  renderList('announcements-list', announcements, createAnnouncementElement);
}

function handleAnnouncementFormSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const title = form['announcement-title'] ? form['announcement-title'].value.trim() : '';
  const content = form['announcement-content'] ? form['announcement-content'].value.trim() : '';
  let duration = parseInt(form['announcement-duration'] ? form['announcement-duration'].value : '0', 10);
  const durationUnit = form['announcement-duration-unit'] ? form['announcement-duration-unit'].value : 'seconds';
  const active = form['announcement-active'] ? form['announcement-active'].checked : false;
  const expiryDate = form['announcement-expiry'] ? form['announcement-expiry'].value : '';
  const pin = form['announcement-pin'] ? form['announcement-pin'].checked : false;
  const textSize = form['announcement-text-size'] ? form['announcement-text-size'].value : 'MEDIUM';

  if (!title) return alert('Title is required.');
  if (!content) return alert('Announcement content is required.');
  if (!duration || duration <= 0) return alert('Duration must be a positive number.');

  if (durationUnit === 'minutes') duration *= 60;
  else if (durationUnit === 'hours') duration *= 3600;

  const announcements = getData('announcements');

  if (editingIndex === -1) {
    announcements.push({
      title,
      content,
      duration,
      active,
      expiryDate,
      pin,
      textSize,
      createdAt: new Date().toISOString()
    });
  } else {
    announcements[editingIndex] = {
      ...announcements[editingIndex],
      title,
      content,
      duration,
      active,
      expiryDate,
      pin,
      textSize,
      updatedAt: new Date().toISOString()
    };
    editingIndex = -1;
  }

  saveData('announcements', announcements);
  form.reset();
  form.classList.add('hidden');
  const newAnnBtn = document.getElementById('new-announcement-btn'); if (newAnnBtn) newAnnBtn.classList.remove('hidden');
  loadAnnouncements();
  loadCounts();
}

// --------------------
// Book Management
// --------------------
function createBookElement(book, index) {
  // Simple card container without flip functionality
  const card = document.createElement('div');
  card.className = 'bg-white p-4 rounded shadow-sm border flex items-center gap-4';

  // Book cover image - left side
  const coverContainer = document.createElement('div');
  coverContainer.className = 'flex-shrink-0';
  
  let coverSrc = '';
  const rawCover = (book.cover && String(book.cover).trim() !== '' && book.cover !== 'NULL') ? book.cover : ((book.coverpic && String(book.coverpic).trim() !== '' && book.coverpic !== 'NULL') ? book.coverpic : null);
  
  if (rawCover) {
    if (!/^data:|^blob:|^https?:\/\//i.test(String(rawCover))) {
      const s = String(rawCover);
      if (s.includes('/') || s.startsWith('assets') || s.startsWith('/')) {
        coverSrc = s;
      } else {
        coverSrc = `../assets/uploads/book_covers/${encodeURIComponent(s)}`;
      }
    } else {
      coverSrc = rawCover;
    }
    coverContainer.innerHTML = `<img src="${coverSrc}" alt="Book cover" style="width:80px;height:100px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.15);object-fit:cover;" onerror="this.onerror=null;this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'100\' fill=\'%23ccc\'><rect width=\'80\' height=\'100\' fill=\'%23f0f0f0\'/><text x=\'40\' y=\'55\' text-anchor=\'middle\' fill=\'%23999\' font-size=\'12\'>No Cover</text></svg>'" />`;
  } else {
    coverContainer.innerHTML = `<div style="width:80px;height:100px;background:#eee;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:12px;">No Cover</div>`;
  }

  // Book information display - center
  const bookInfo = document.createElement('div');
  bookInfo.className = 'flex-1';
  
  // Title
  const title = document.createElement('div');
  title.className = 'text-lg font-bold text-gray-900 mb-1';
  title.textContent = book.title || 'No Title';
  
  // Author
  const author = document.createElement('div');
  author.className = 'text-md text-gray-700 mb-1';
  author.textContent = book.author || 'No Author';
  
  // Category
  const category = document.createElement('div');
  category.className = 'text-sm text-gray-600';
  category.textContent = book.category ? `Category: ${book.category}` : 'No Category';

  // Assemble book info
  bookInfo.appendChild(title);
  bookInfo.appendChild(author);
  bookInfo.appendChild(category);

  // Action buttons (edit/delete) - right side
  const actions = document.createElement('div');
  actions.className = 'flex flex-col gap-2 flex-shrink-0';
  const editBtn = document.createElement('button');
  editBtn.className = 'edit-book-btn bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm';
  editBtn.setAttribute('data-index', String(index));
  editBtn.textContent = 'Edit';
  const deleteBtn = document.createElement('button');
  deleteBtn.className = 'delete-book-btn bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm';
  deleteBtn.setAttribute('data-index', String(index));
  if (book.id) deleteBtn.setAttribute('data-id', String(book.id));
  deleteBtn.textContent = 'Delete';
  actions.appendChild(editBtn);
  actions.appendChild(deleteBtn);

  // Assemble card
  card.appendChild(coverContainer);
  card.appendChild(bookInfo);
  card.appendChild(actions);

  return card;
}

function loadBooks() {
  // Prefer server-side books (via books.php). If that fails, fall back to localStorage.
  fetch('books.php')
    .then(res => res.json())
    .then(json => {
      if (json && json.success && Array.isArray(json.data)) {
        // Map DB rows to frontend book shape
        const books = json.data.map(b => ({
          // Keep the server id so we can call delete on the server
          id: b.id || b.book_id || b.bookId || null,
          title: b.title || b.name || '',
          author: b.author || '',
          category: b.category || '',
          description: b.description || '',
          availability: b.status || b.availability || '',
          expiryDate: b.expiry || b.expiryDate || '',
          // Prefer cover field; could be data URI or filename
          cover: b.cover || b.coverpic || ''
        }));
  currentBooks = books;
  renderList('books-list', books, createBookElement);
        return;
      }
      // fallback to localStorage
  const books = getData('books');
  currentBooks = books;
  renderList('books-list', books, createBookElement);
    })
    .catch(err => {
      console.warn('Failed to load books from server, falling back to localStorage', err);
      const books = getData('books');
      currentBooks = books;
      renderList('books-list', books, createBookElement);
    });
}

function handleBookFormSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const title = form['book-title'] ? form['book-title'].value.trim() : '';
  const author = form['book-author'] ? form['book-author'].value.trim() : '';
  const category = form['book-category'] ? form['book-category'].value.trim() : '';
  const description = form['book-description'] ? form['book-description'].value.trim() : '';
  const availability = form['book-availability'] ? form['book-availability'].value : '';
    const coverInput = form['book-cover'];
    const cover = coverInput && coverInput.files ? coverInput.files[0] : null;
    // Cover required only when creating a new book; when editing, reusing existing cover is allowed
    if (editingIndex === -1 && !cover) return alert('Cover image is required.');
  const expiryDate = form['book-expiry'] ? form['book-expiry'].value : '';

  if (!title) return alert('Title is required.');
  if (!author) return alert('Author is required.');
  if (!cover) return alert('Cover image is required.');
  // Prepare FormData for server upload
  const fd = new FormData();
  fd.append('title', title);
  fd.append('author', author);
  fd.append('category', category);
  fd.append('description', description);
  fd.append('availability', availability);
  fd.append('expiry', expiryDate);
  fd.append('cover', cover);

  // Try server upload first
  // If editingIndex is -1 -> create (insert). Otherwise attempt update.
  if (editingIndex === -1) {
    fetch('books_insert.php', {
      method: 'POST',
      body: fd
    }).then(res => res.json())
      .then(data => {
        if (data && data.success) {
          form.reset();
          form.classList.add('hidden');
          const addBookBtn = document.getElementById('add-book-btn'); if (addBookBtn) addBookBtn.classList.remove('hidden');
          loadBooks();
          loadCounts();
        } else {
          // Fallback to localStorage
          console.warn('Server insert failed, falling back to localStorage', data && data.error);
          const books = getData('books');
          books.push({
            title,
            author,
            category,
            description,
            availability,
            cover: cover ? URL.createObjectURL(cover) : '',
            expiryDate,
            addedAt: new Date().toISOString()
          });
          saveData('books', books);
          form.reset();
          form.classList.add('hidden');
          const addBookBtn = document.getElementById('add-book-btn'); if (addBookBtn) addBookBtn.classList.remove('hidden');
          loadBooks();
          loadCounts();
        }
      })
      .catch(err => {
        console.error('Network error while uploading book:', err);
        const books = getData('books');
        books.push({
          title,
          author,
          category,
          description,
          availability,
          cover: cover ? URL.createObjectURL(cover) : '',
          expiryDate,
          addedAt: new Date().toISOString()
        });
        saveData('books', books);
        form.reset();
        form.classList.add('hidden');
        const addBookBtn = document.getElementById('add-book-btn'); if (addBookBtn) addBookBtn.classList.remove('hidden');
        loadBooks();
        loadCounts();
      });
    return;
  }

  // Update flow
  // Build FormData for update and include the server id if present
  const bookIndex = editingIndex;
  const existing = currentBooks && currentBooks[bookIndex] ? currentBooks[bookIndex] : (getData('books') || [])[bookIndex];
  const serverId = existing && existing.id ? existing.id : null;
  const updateFd = new FormData();
  updateFd.append('title', title);
  updateFd.append('author', author);
  updateFd.append('category', category);
  updateFd.append('description', description);
  updateFd.append('availability', availability);
  updateFd.append('expiry', expiryDate);
  if (cover) updateFd.append('cover', cover);
  if (serverId) updateFd.append('id', serverId);

  // Attempt server update if we have an id
  if (serverId) {
    fetch('books_update.php', { method: 'POST', body: updateFd })
      .then(r => r.json())
      .then(res => {
        if (res && res.success) {
          form.reset();
          form.classList.add('hidden');
          editingIndex = -1;
          const addBookBtn = document.getElementById('add-book-btn'); if (addBookBtn) addBookBtn.classList.remove('hidden');
          loadBooks();
          loadCounts();
        } else {
          // Server-side failed; fallback to local update
          console.warn('Server update failed, falling back to local update', res && res.error);
          const books = getData('books');
          if (books && books.length > bookIndex) {
            books[bookIndex] = {
              ...books[bookIndex],
              title,
              author,
              category,
              description,
              availability,
              expiryDate,
              // if a new cover file provided, use objectURL, else preserve existing
              cover: cover ? URL.createObjectURL(cover) : (books[bookIndex].cover || '')
            };
            saveData('books', books);
          }
          form.reset();
          form.classList.add('hidden');
          editingIndex = -1;
          loadBooks();
          loadCounts();
        }
      }).catch(err => {
        console.error('Network error updating book, falling back to local update', err);
        const books = getData('books');
        if (books && books.length > bookIndex) {
          books[bookIndex] = {
            ...books[bookIndex],
            title,
            author,
            category,
            description,
            availability,
            expiryDate,
            cover: cover ? URL.createObjectURL(cover) : (books[bookIndex].cover || '')
          };
          saveData('books', books);
        }
        form.reset();
        form.classList.add('hidden');
        editingIndex = -1;
        loadBooks();
        loadCounts();
      });
  } else {
    // No server id -> only local update possible
    const books = getData('books');
    if (books && books.length > bookIndex) {
      books[bookIndex] = {
        ...books[bookIndex],
        title,
        author,
        category,
        description,
        availability,
        expiryDate,
        cover: cover ? URL.createObjectURL(cover) : (books[bookIndex].cover || '')
      };
      saveData('books', books);
    }
    form.reset();
    form.classList.add('hidden');
    editingIndex = -1;
    const addBookBtn = document.getElementById('add-book-btn'); if (addBookBtn) addBookBtn.classList.remove('hidden');
    loadBooks();
    loadCounts();
  }
}

// --------------------
// Footer Management
// --------------------
function createFooterElement(footer, index) {
  const div = document.createElement('div');
  div.className = 'p-4 border rounded mb-2 bg-gray-100 flex justify-between items-center';
  div.innerHTML = `
    <div>
      <h4 class="font-semibold">${footer.message}</h4>
      <p>Scroll Speed: ${footer.scrollSpeed}</p>
      <p>Expiry Date: ${footer.expiryDate || 'None'}</p>
    </div>
    <div>
      <button class="edit-footer-btn bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 mr-2" data-index="${index}">Edit</button>
      <button class="delete-footer-btn bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700" data-index="${index}">Delete</button>
    </div>
  `;
  return div;
}

function loadFooters() {
  const footers = getData('footers');
  renderList('footer-msgs-list', footers, createFooterElement);
}

// Attempt to fetch footers from server; fall back to localStorage on error
function loadFootersFromServer() {
  fetch('footer_messages.php')
    .then(r => r.json())
    .then(json => {
      if (json && json.success && Array.isArray(json.data)) {
        // Normalize server rows to frontend shape and save to localStorage for offline use
        const rows = json.data.map(r => ({
          id: parseInt(r.id, 10),
          message: r.message,
          expiryDate: r.expiry || '',
          scrollSpeed: r.scroll_speed == null ? '' : String(r.scroll_speed)
        }));
        saveData('footers', rows);
        renderList('footer-msgs-list', rows, createFooterElement);
        return;
      }
      // fallback
      loadFooters();
    }).catch(err => {
      console.warn('Failed to load footers from server, falling back to localStorage', err);
      loadFooters();
    });
}

function handleFooterFormSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const message = form['footer-message'] ? form['footer-message'].value.trim() : '';
  const scrollSpeedRaw = form['scroll-speed'] ? form['scroll-speed'].value : '';
  const footerIdField = form['footer-id'] ? form['footer-id'].value : '';
  const expiryDate = form['footer-expiry'] ? form['footer-expiry'].value : '';

  if (!message) return alert('Message is required.');

  // Try server-side save first
  // normalize scroll_speed to integer or null
  let scroll_speed = null;
  if (String(scrollSpeedRaw).trim() !== '') {
    const tmp = parseInt(scrollSpeedRaw, 10);
    if (!Number.isNaN(tmp)) scroll_speed = tmp;
  }

  const payload = {
    message,
    expiry: expiryDate || '',
    scroll_speed: scroll_speed
  };
  // if editing an existing footer (we populated the form from local state), include id
  // prefer explicit footer-id hidden input (set on Edit). Fall back to local state by index.
  if (footerIdField && String(footerIdField).trim() !== '') {
    payload.id = parseInt(footerIdField, 10) || undefined;
  } else if (editingIndex !== -1) {
    const localFooters = getData('footers');
    const f = localFooters && localFooters[editingIndex] ? localFooters[editingIndex] : null;
    if (f && f.id) payload.id = f.id;
  }

  fetch('footer_messages.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  }).then(r => r.json()).then(res => {
    if (res && res.success) {
      // If server returned an id for a new row, prefer reloading from server
      loadFootersFromServer();
      // fully close the modal and reset form so only the overlay/header doesn't remain
      try {
        const footerModalEl = document.getElementById('footer-modal'); if (footerModalEl) footerModalEl.classList.add('hidden');
        document.body.classList.remove('body-blurred');
      } catch (e) {}
      form.reset();
      const hid = document.getElementById('footer-id'); if (hid) hid.value = '';
      editingIndex = -1;
      const nf = document.getElementById('new-footer-msg-btn'); if (nf) nf.classList.remove('hidden');
      loadCounts();
      return;
    }
    // Server returned failure -> fallback to localStorage
    console.warn('Server save failed for footer, falling back to localStorage', res && res.error);
    fallbackSaveLocal();
  }).catch(err => {
    console.error('Network error saving footer, falling back to localStorage', err);
    fallbackSaveLocal();
  });

  function fallbackSaveLocal() {
    const footers = getData('footers');
    if (editingIndex === -1) {
      footers.push({
        message,
        scrollSpeed: scrollSpeedRaw === '' ? '' : String(scroll_speed === null ? scrollSpeedRaw : scroll_speed),
        expiryDate,
        createdAt: new Date().toISOString()
      });
    } else {
      footers[editingIndex] = {
        ...footers[editingIndex],
        message,
        scrollSpeed: scrollSpeedRaw === '' ? '' : String(scroll_speed === null ? scrollSpeedRaw : scroll_speed),
        expiryDate,
        updatedAt: new Date().toISOString()
      };
      editingIndex = -1;
    }
    saveData('footers', footers);
    // fully close the modal (not just hide the inner form) and reset
    try {
      const footerModalEl = document.getElementById('footer-modal'); if (footerModalEl) footerModalEl.classList.add('hidden');
      document.body.classList.remove('body-blurred');
    } catch (e) {}
    form.reset();
    const hid = document.getElementById('footer-id'); if (hid) hid.value = '';
    const nf = document.getElementById('new-footer-msg-btn'); if (nf) nf.classList.remove('hidden');
    loadFooters();
    loadCounts();
  }
}

// --------------------
// Event Listeners Setup
// --------------------
function setupEventListeners() {
  tabs.forEach(tab => {
    const btn = document.getElementById(`tab-btn-${tab}`);
    if (btn) btn.addEventListener('click', () => showTab(tab));
  });

  // Videos tab: Upload Video button and form toggle logic
  // Videos tab: Upload Video button and form toggle logic
  const uploadBtn = document.getElementById('upload-video-btn');
  const videoForm = document.getElementById('video-upload-form');
  const videoCancel = document.getElementById('vid-cancel-btn');
  if (uploadBtn) uploadBtn.addEventListener('click', () => {
    if (videoForm) videoForm.classList.remove('hidden');
    uploadBtn.classList.add('hidden');
  });
  if (videoCancel) videoCancel.addEventListener('click', () => {
    if (videoForm) { videoForm.reset(); videoForm.classList.add('hidden'); }
    if (uploadBtn) uploadBtn.classList.remove('hidden');
  });
  // If the page uses the server-backed video form (handled by videos.js),
  // it will have inputs like #vid-title. In that case, skip attaching the
  // localStorage handler here to avoid duplicate validation like
  // "Title is required." coming from mismatched field names.
  const usesServerVideoForm = !!document.getElementById('vid-title');
  if (videoForm && !usesServerVideoForm) videoForm.addEventListener('submit', handleVideoFormSubmit);

  const videosList = document.getElementById('videos-list');
  if (videosList) videosList.addEventListener('click', e => {
    const target = e.target;
    if (target.classList.contains('delete-video-btn')) {
      const index = parseInt(target.getAttribute('data-index'), 10);
      if (confirm('Are you sure you want to delete this video?')) {
        const videos = getData('videos');
        videos.splice(index, 1);
        saveData('videos', videos);
        loadVideos();
        loadCounts();
      }
    }
  });

  // Announcements tab: Add Announcement button and form toggle logic
  const addAnnBtn = document.getElementById('add-announcement-btn');
  const annForm = document.getElementById('announcement-form');
  const annCancel = document.getElementById('ann-cancel-btn');
  if (addAnnBtn) addAnnBtn.addEventListener('click', () => {
    if (annForm) annForm.classList.remove('hidden');
    addAnnBtn.classList.add('hidden');
  });
  if (annCancel) annCancel.addEventListener('click', () => {
    if (annForm) { annForm.reset(); annForm.classList.add('hidden'); }
    if (addAnnBtn) addAnnBtn.classList.remove('hidden');
    editingIndex = -1;
  });
  // If the page uses the server-backed announcement form (handled by announcements.js),
  // it will have inputs like #ann-title. In that case, skip attaching the
  // localStorage handler here to avoid duplicate validation (e.g., "Title is required.").
  const usesServerAnnouncementForm = !!document.getElementById('ann-title');
  if (annForm && !usesServerAnnouncementForm) annForm.addEventListener('submit', handleAnnouncementFormSubmit);

  const annList = document.getElementById('announcements-list');
  if (annList) annList.addEventListener('click', e => {
    const t = e.target;
    if (t.classList.contains('delete-announcement-btn')) {
      const index = parseInt(t.getAttribute('data-index'), 10);
      if (confirm('Are you sure you want to delete this announcement?')) {
        const announcements = getData('announcements');
        announcements.splice(index, 1);
        saveData('announcements', announcements);
        loadAnnouncements();
        loadCounts();
      }
    } else if (t.classList.contains('edit-announcement-btn')) {
      const index = parseInt(t.getAttribute('data-index'), 10);
      const announcements = getData('announcements');
      const announcement = announcements[index];
      const at = document.getElementById('announcement-title'); if (at) at.value = announcement.title;
      const ac = document.getElementById('announcement-content'); if (ac) ac.value = announcement.content;
      const ad = document.getElementById('announcement-duration'); if (ad) ad.value = announcement.duration;
      const au = document.getElementById('announcement-duration-unit'); if (au) au.value = 'seconds';
      const aa = document.getElementById('announcement-active'); if (aa) aa.checked = announcement.active;
      const aexp = document.getElementById('announcement-expiry'); if (aexp) aexp.value = announcement.expiryDate || '';
      const ap = document.getElementById('announcement-pin'); if (ap) ap.checked = announcement.pin;
      const atx = document.getElementById('announcement-text-size'); if (atx) atx.value = announcement.textSize || 'MEDIUM';
      editingIndex = index;
      const form = document.getElementById('new-announcement-form'); if (form) form.classList.remove('hidden');
      if (newAnnBtn) newAnnBtn.classList.add('hidden');
    }
  });

  // Book modal handling (uses #book-modal and #book-form)
  // --- Add Book Inline Form ---
  const addBookBtn = document.getElementById('add-book-btn');
  const bookForm = document.getElementById('book-form');
  const bookCancel = document.getElementById('book-cancel');
  if (addBookBtn) addBookBtn.addEventListener('click', () => {
    if (bookForm) bookForm.classList.remove('hidden');
    addBookBtn.classList.add('hidden');
  });
  if (bookCancel) bookCancel.addEventListener('click', () => {
    if (bookForm) { bookForm.reset(); bookForm.classList.add('hidden'); }
    if (addBookBtn) addBookBtn.classList.remove('hidden');
    editingIndex = -1;
  });
  if (bookForm) bookForm.addEventListener('submit', handleBookFormSubmit);

  // --- Book Details Modal (for viewing info when clicking the thumbnail) ---
  // Create modal DOM once and append to body
  function ensureBookDetailsModal() {
    if (document.getElementById('book-details-modal')) return;
    const modal = document.createElement('div');
    modal.id = 'book-details-modal';
    // use flex so the inner panel centers properly
    modal.className = 'fixed inset-0 z-50 hidden flex items-center justify-center';
    modal.innerHTML = `
      <div id="book-details-backdrop" class="absolute inset-0 bg-black bg-opacity-60"></div>
  <!-- rounded-3xl + overflow-hidden so child image corners are clipped and not pointy -->
  <div class="relative bg-white rounded-3xl shadow-2xl w-11/12 md:w-11/12 lg:max-w-6xl p-10 max-h-[92vh] overflow-hidden my-6">
        <button id="book-details-close" class="absolute top-3 right-4 text-gray-600 hover:text-gray-900">&times;</button>
        <div id="book-details-content"></div>
      </div>
    `;
    document.body.appendChild(modal);

    // close handlers
    modal.addEventListener('click', (e) => {
      if (e.target === modal || e.target.id === 'book-details-backdrop') closeBookDetailsModal();
    });
    const closeBtn = modal.querySelector('#book-details-close');
    if (closeBtn) closeBtn.addEventListener('click', closeBookDetailsModal);
    // prevent clicks inside panel from closing
    const panel = modal.querySelector('div.relative');
    if (panel) panel.addEventListener('click', e => e.stopPropagation());
    // ESC key closes
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        const m = document.getElementById('book-details-modal');
        if (m && !m.classList.contains('hidden')) closeBookDetailsModal();
      }
    });
  }

  function openBookDetailsModal(index) {
    ensureBookDetailsModal();
    const modal = document.getElementById('book-details-modal');
    const content = document.getElementById('book-details-content');
    if (!modal || !content) return;
    const book = currentBooks && currentBooks[index] ? currentBooks[index] : null;
    if (!book) return;
    // build content: left column = details, right column = large image (responsive)
    const coverSrc = (function () {
      if (!book.cover) return null;
      let s = book.cover;
      if (!/^data:|^blob:|^https?:\/\//i.test(String(s))) {
        const t = String(s);
        if (t.includes('/') || t.startsWith('assets') || t.startsWith('/')) return t;
        return `../assets/uploads/book_covers/${encodeURIComponent(t)}`;
      }
      return s;
    })();

    // New layout: image as large background on right, text overlaid on a translucent panel to the left
    content.innerHTML = `
      <div class="relative flex flex-col md:flex-row md:items-stretch">
        <div class="md:w-1/2 flex items-center">
          <div class="overlay-text-panel p-6 md:p-10 rounded-l-3xl md:rounded-l-3xl bg-white/70 backdrop-blur-sm max-h-[80vh] overflow-auto">
            <h3 id="bd-title" class="text-4xl md:text-5xl font-extrabold mb-4">${escapeHtml(book.title || '')}</h3>
              <p class="text-lg md:text-xl text-gray-800 mb-3"><strong>Author:</strong> ${escapeHtml(book.author || '')}</p>
              <p class="text-lg md:text-xl text-gray-800 mb-3"><strong>Category:</strong> ${escapeHtml(book.category || '')}</p>
              <div class="mt-4 text-gray-800"><strong class="text-lg">Description:</strong>
                <p class="mt-2 text-base md:text-lg leading-relaxed">${escapeHtml(book.description || '')}</p>
            </div>
            <div class="mt-4 text-gray-800">
              <p class="text-sm"><strong>Availability:</strong> ${escapeHtml(book.availability || '')}</p>
              <p class="text-sm"><strong>Expiry Date:</strong> ${escapeHtml(book.expiryDate || 'None')}</p>
            </div>
          </div>
        </div>
        <div class="md:w-1/2 relative flex items-stretch justify-center">
          ${coverSrc ? `<div class="book-details-image-wrap relative w-full h-full flex items-center justify-center"><img src="${coverSrc}" alt="cover" class="book-details-cover-img w-full h-full object-cover shadow-2xl" onerror="this.onerror=null;this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'720\' height=\'960\' fill=\'%23ccc\'><rect width=\'720\' height=\'960\' fill=\'%23f0f0f0\'/><text x=\'360\' y=\'480\' text-anchor=\'middle\' fill=\'%23999\' font-size=\'24\'>No Cover</text></svg>'}"/></div>` : `<div class="book-details-image-wrap relative w-full h-96 bg-gray-100 flex items-center justify-center text-gray-400">No Cover</div>`}
        </div>
      </div>`;

    // After inserting content, compute a dominant color from the image and add a gradient overlay
    try {
  const imgWrap = content.querySelector('.book-details-image-wrap');
  const detailImg = content.querySelector('.book-details-cover-img');
      function computeDominantColor(img, quality = 8) {
        try {
          const canvas = document.createElement('canvas');
          const ctx = canvas.getContext('2d');
          const w = 40, h = 40;
          canvas.width = w; canvas.height = h;
          ctx.drawImage(img, 0, 0, w, h);
          const data = ctx.getImageData(0, 0, w, h).data;
          let r = 0, g = 0, b = 0, count = 0;
          for (let i = 0; i < data.length; i += 4 * Math.max(1, quality)) {
            r += data[i]; g += data[i+1]; b += data[i+2]; count++;
          }
          if (count === 0) return [220,220,220];
          return [Math.round(r / count), Math.round(g / count), Math.round(b / count)];
        } catch (e) {
          return [220,220,220];
        }
      }

      const applyOverlay = () => {
        if (!imgWrap || !detailImg) return;
        // remove existing overlay if any
        const existing = imgWrap.querySelector('.book-cover-gradient-overlay');
        if (existing) existing.remove();
        const col = computeDominantColor(detailImg, 6);
        const ov = document.createElement('div');
        ov.className = 'book-cover-gradient-overlay';
        ov.style.position = 'absolute';
        ov.style.inset = '0';
        ov.style.pointerEvents = 'none';
        ov.style.borderRadius = window.getComputedStyle(detailImg).borderRadius || '8px';
        ov.style.background = `linear-gradient(to left, rgba(${col[0]},${col[1]},${col[2]},0.95) 0%, rgba(${col[0]},${col[1]},${col[2]},0.6) 35%, rgba(0,0,0,0) 100%)`;
        imgWrap.appendChild(ov);
        // apply color to title with simple contrast check
        try {
          const titleEl = content.querySelector('#bd-title');
          if (titleEl) {
            const rgb = col;
            // compute relative luminance
            const lum = (0.2126 * (rgb[0]/255) + 0.7152 * (rgb[1]/255) + 0.0722 * (rgb[2]/255));
            // choose white or dark text depending on luminance for contrast
            const textColor = lum < 0.5 ? 'white' : `rgb(${rgb[0]}, ${rgb[1]}, ${rgb[2]})`;
            titleEl.style.color = textColor;
            // if white, add text shadow for readability
            if (textColor === 'white') titleEl.style.textShadow = '0 2px 6px rgba(0,0,0,0.6)';
          }
        } catch (e) {}
      };

      if (detailImg) {
        if (detailImg.complete) applyOverlay();
        else detailImg.addEventListener('load', applyOverlay);
      }
  } catch (e) { /* ignore overlay errors */ }
    modal.classList.remove('hidden');
    document.body.classList.add('body-blurred');
  }

  function closeBookDetailsModal() {
    const modal = document.getElementById('book-details-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.classList.remove('body-blurred');
  }

  // small helper to prevent XSS in inserted HTML
  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // (thumbnail clicks handled together with delete button below on #books-list)

  const booksList = document.getElementById('books-list');
  if (booksList) booksList.addEventListener('click', e => {
    const t = e.target;
    // if user clicked a book thumbnail (or an element inside it), open details
    if (t.classList.contains('book-cover-thumb') || (t.closest && t.closest('.book-cover-thumb'))) {
      const img = t.classList.contains('book-cover-thumb') ? t : t.closest('.book-cover-thumb');
      const idx = parseInt(img.getAttribute('data-book-index'), 10);
      if (!Number.isNaN(idx)) return openBookDetailsModal(idx);
    }
    // Edit book - open the add/edit modal prefilled with book data
    if (t.classList.contains('edit-book-btn')) {
      const index = parseInt(t.getAttribute('data-index'), 10);
      if (Number.isNaN(index)) return;
      const book = currentBooks && currentBooks[index] ? currentBooks[index] : (getData('books') || [])[index];
      if (!book) return alert('Book not found for editing.');
      // populate form fields
      const titleEl = document.getElementById('book-title'); if (titleEl) titleEl.value = book.title || '';
      const authorEl = document.getElementById('book-author'); if (authorEl) authorEl.value = book.author || '';
      const categoryEl = document.getElementById('book-category'); if (categoryEl) categoryEl.value = book.category || '';
      const descEl = document.getElementById('book-description'); if (descEl) descEl.value = book.description || '';
      const availEl = document.getElementById('book-availability'); if (availEl) availEl.value = book.availability || '';
      const expiryEl = document.getElementById('book-expiry'); if (expiryEl) expiryEl.value = book.expiryDate || '';
      // show a cover preview inside modal (create if needed)
      const bookModalEl = document.getElementById('book-modal');
      if (bookModalEl) {
        // change modal title
        const hdr = bookModalEl.querySelector('h4'); if (hdr) hdr.textContent = 'Edit Book';
        // ensure preview spot
        let preview = document.getElementById('book-cover-preview');
        const coverInput = document.getElementById('book-cover');
        if (!preview && coverInput) {
          preview = document.createElement('img');
          preview.id = 'book-cover-preview';
          preview.className = 'mt-2 mb-2 max-h-48 rounded shadow';
          coverInput.parentNode.insertBefore(preview, coverInput);
        }
        if (preview) {
          // compute cover src similar to the details modal
          let coverSrc = book.cover || '';
          if (coverSrc && !/^data:|^blob:|^https?:\/\//i.test(String(coverSrc))) {
            const tstr = String(coverSrc);
            if (!(tstr.includes('/') || tstr.startsWith('assets') || tstr.startsWith('/'))) {
              coverSrc = `../assets/uploads/book_covers/${encodeURIComponent(tstr)}`;
            }
          }
          preview.src = coverSrc || '';
          preview.onerror = function() { this.onerror = null; this.style.display = 'none'; };
          preview.style.display = coverSrc ? '' : 'none';
        }
        }
        // indicate edit mode on the save button
        const saveBtn = document.getElementById('book-save'); if (saveBtn) saveBtn.textContent = 'Update & Save';
        editingIndex = index;
      // open modal
      openBookModal();
      return;
    }
    if (t.classList.contains('delete-book-btn')) {
      const index = parseInt(t.getAttribute('data-index'), 10);
      const serverId = t.getAttribute('data-id');
      if (!confirm('Are you sure you want to delete this book?')) return;

      // If the book has a server id, attempt server-side deletion first
      if (serverId) {
        fetch('books_delete.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ id: serverId })
        }).then(r => r.json()).then(res => {
          if (res && res.success) {
            // reload list from server
            loadBooks();
            loadCounts();
          } else {
            // server returned failure - show message and fallback to localStorage removal if present
            console.warn('Server failed to delete book:', res && res.error);
            const books = getData('books');
            if (books && books.length > index) {
              books.splice(index, 1);
              saveData('books', books);
            }
            loadBooks();
            loadCounts();
          }
        }).catch(err => {
          console.error('Network error deleting book:', err);
          // fallback to localStorage
          const books = getData('books');
          if (books && books.length > index) {
            books.splice(index, 1);
            saveData('books', books);
          }
          loadBooks();
          loadCounts();
        });
      } else {
        // No server id -> operate on localStorage
        const books = getData('books');
        if (books && books.length > index) {
          books.splice(index, 1);
          saveData('books', books);
        }
        loadBooks();
        loadCounts();
      }
    }
  });

  // --- New Footer Message Inline Form ---
  const newFooterBtn = document.getElementById('new-footer-msg-btn');
  const newFooterForm = document.getElementById('new-footer-form');
  const footerCancel = document.getElementById('footer-cancel');
  if (newFooterBtn) newFooterBtn.addEventListener('click', () => {
    if (newFooterForm) newFooterForm.classList.remove('hidden');
    newFooterBtn.classList.add('hidden');
  });
  if (footerCancel) footerCancel.addEventListener('click', () => {
    if (newFooterForm) { newFooterForm.reset(); newFooterForm.classList.add('hidden'); }
    if (newFooterBtn) newFooterBtn.classList.remove('hidden');
    editingIndex = -1;
  });
  if (newFooterForm) newFooterForm.addEventListener('submit', handleFooterFormSubmit);

  const footerList = document.getElementById('footer-msgs-list');
  if (footerList) footerList.addEventListener('click', e => {
    const t = e.target;
    if (t.classList.contains('delete-footer-btn')) {
      const index = parseInt(t.getAttribute('data-index'), 10);
      // Use proper delete confirmation modal instead of browser confirm
      window.showDeleteConfirmation(() => {
        const footers = getData('footers');
        footers.splice(index, 1);
        saveData('footers', footers);
        loadFooters();
        loadCounts();
      });
    } else if (t.classList.contains('edit-footer-btn')) {
      const index = parseInt(t.getAttribute('data-index'), 10);
      const footers = getData('footers');
      const footer = footers[index];
      const fm = document.getElementById('footer-message'); if (fm) fm.value = footer.message;
      const fs = document.getElementById('scroll-speed'); if (fs) fs.value = footer.scrollSpeed || '';
      const fid = document.getElementById('footer-id'); if (fid) fid.value = footer.id ? String(footer.id) : '';
      const fe = document.getElementById('footer-expiry'); if (fe) fe.value = footer.expiryDate || '';
      editingIndex = index;
      // Open the inline form instead of modal for consistency
      if (newFooterForm) newFooterForm.classList.remove('hidden');
      if (newFooterBtn) newFooterBtn.classList.add('hidden');
      // Focus the message input
      if (fm) fm.focus();
    }
  });

  // Support both old button id `preview-signage-btn` and new anchor/button id `preview-signage`.
  const previewBtn = document.getElementById('preview-signage-btn') || document.getElementById('preview-signage');
  if (previewBtn) {
    // If it's an anchor (<a>), ensure it opens the Signage page in a new tab for preview convenience.
    if (previewBtn.tagName && previewBtn.tagName.toLowerCase() === 'a') {
      // set target if not already set
      if (!previewBtn.getAttribute('target')) previewBtn.setAttribute('target', '_blank');
      // make sure href points to Signage.php (relative to php folder)
      const href = previewBtn.getAttribute('href');
      if (!href || !/Signage\.php$/i.test(href)) previewBtn.setAttribute('href', 'Signage.php');
    } else {
      // fallback: if it's a button, use the existing modal toggle behavior
      previewBtn.addEventListener('click', showSignagePreview);
    }
  }

  const logoutBtn = document.getElementById('logout-btn');
  if (logoutBtn) logoutBtn.addEventListener('click', showLogoutConfirmation);
}

// --------------------
// Small UI helpers
// --------------------
function showSignagePreview() {
  const modal = document.getElementById('signage-preview');
  if (modal) modal.classList.toggle('hidden');
}

function showLogoutConfirmation() {
  const modal = document.getElementById('logout-confirmation');
  if (modal) {
    modal.classList.remove('hidden');
    document.body.classList.add('body-blurred');
  }
}

// --------------------
// Initialization
// --------------------
function init() {
  showTab('overview');
  loadCounts();
  loadVideos();
  loadAnnouncements();
  loadBooks();
  // prefer server-side footers when available
  loadFootersFromServer();
  
  // Add logout button event listener directly here to ensure it works
  const logoutBtn = document.getElementById('logout-btn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      showLogoutConfirmation();
    });
  }
  
  // Setup logout modal buttons
  const cancelBtn = document.getElementById('logout-cancel');
  const confirmBtn = document.getElementById('logout-confirm');
  const backdrop = document.getElementById('logout-confirmation-backdrop');
  const modal = document.getElementById('logout-confirmation');
  const loader = document.getElementById('logout-loader');

  function hideModal() {
    if (modal) modal.classList.add('hidden');
    document.body.classList.remove('body-blurred');
  }

  if (cancelBtn) cancelBtn.addEventListener('click', hideModal);
  if (backdrop) backdrop.addEventListener('click', hideModal);

  if (confirmBtn) confirmBtn.addEventListener('click', async function(e){
    e.preventDefault();
    // show loader overlay
    if (loader) loader.classList.remove('hidden');
    hideModal();

    // Clear login data from localStorage
    localStorage.removeItem('loggedIn');
    localStorage.removeItem('adminName');
    localStorage.removeItem('district');
    localStorage.removeItem('branch');

    try {
      const resp = await fetch('logout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ csrf_token: window.CSRF_TOKEN || '' })
      });
      const data = await resp.json();
      if (data && data.success) {
        // redirect immediately when server confirms logout
        window.location.href = 'login/login.php';
        return;
      } else {
        console.warn('Logout failed on server', data);
      }
    } catch (err) {
      console.error('Logout request failed', err);
    }

    // fallback: after 3s redirect to login page even if server didn't respond
    setTimeout(() => { window.location.href = 'login/login.php'; }, 3000);
  });
  
  // Populate category and availability selects - try server first, fallback to static
  const catSelect = document.getElementById('book-category');
  const availSelect = document.getElementById('book-availability');

  const populateSelect = (selectEl, options, emptyLabel) => {
    if (!selectEl) return;
    selectEl.innerHTML = '';
    options.forEach(optVal => {
      const opt = document.createElement('option'); opt.value = optVal; opt.textContent = optVal || (emptyLabel || 'Uncategorized'); selectEl.appendChild(opt);
    });
  };

  // Attempt to fetch categories and statuses from server
  Promise.all([
    fetch('get_book_categories.php').then(r => r.json()).catch(() => ({ success: false })),
    fetch('get_book_statuses.php').then(r => r.json()).catch(() => ({ success: false }))
  ]).then(([catRes, statRes]) => {
    if (catRes && catRes.success && Array.isArray(catRes.data) && catRes.data.length > 0) {
      populateSelect(catSelect, catRes.data, 'Uncategorized');
    } else {
      // fallback
      populateSelect(catSelect, ['Adventure','Anthropology','Art & Architecture','Autobiography','Biography','Business & Economics','Classic Literature','Cooking/Food','Dystopian','Fantasy','Graphic Novels & Comics','Horror','History',''], 'Uncategorized');
    }

    if (statRes && statRes.success && Array.isArray(statRes.data) && statRes.data.length > 0) {
      populateSelect(availSelect, statRes.data, 'Unknown');
    } else {
      populateSelect(availSelect, ['Available', 'Borrowed', ''], 'Unknown');
    }
  }).catch(() => {
    // global fallback
    populateSelect(catSelect, ['Adventure','Anthropology','Art & Architecture','Autobiography','Biography','Business & Economics','Classic Literature','Cooking/Food','Dystopian','Fantasy','Graphic Novels & Comics','Horror','History',''], 'Uncategorized');
    populateSelect(availSelect, ['Available', 'Borrowed', ''], 'Unknown');
  });
  setupEventListeners();
}

document.addEventListener('DOMContentLoaded', init);
