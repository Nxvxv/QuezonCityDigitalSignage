// --------------------------------------------------
// QC Library Digital Signage - Admin Dashboard Script
// --------------------------------------------------
// Author: Quezon City Public Library — Developers' Hub
// Description: Refactored JavaScript for Admin Dashboard
// --------------------------------------------------

const tabs = ['overview', 'videos', 'announcements', 'books', 'footer'];
let editingIndex = -1;

// --------------------
// Utility Functions
// --------------------
function $(id) {
  return document.getElementById(id);
}

function showTab(tab) {
  tabs.forEach(t => {
    $(`tab-${t}-content`).classList.add('hidden');
    $(`tab-btn-${t}`).classList.remove('bg-primary', 'text-white');
    $(`tab-btn-${t}`).classList.add('hover:bg-gray-200', 'text-gray-600');
  });
  $(`tab-${tab}-content`).classList.remove('hidden');
  $(`tab-btn-${tab}`).classList.add('bg-primary', 'text-white');
  $(`tab-btn-${tab}`).classList.remove('hover:bg-gray-200', 'text-gray-600');
}

// --------------------
// Data Management
// --------------------
function getData(key) {
  return JSON.parse(localStorage.getItem(key) || '[]');
}

function saveData(key, data) {
  localStorage.setItem(key, JSON.stringify(data));
}

// --------------------
// Dashboard Counts
// --------------------
function loadCounts() {
  const videos = getData('videos');
  const announcements = getData('announcements');
  const books = getData('books');
  const footers = getData('footers');

  $('count-videos').textContent = videos.length;
  $('count-announcements').textContent = announcements.length;
  $('count-books').textContent = books.length;
  $('count-footers').textContent = footers.length;

  $('summary-videos').textContent = `${videos.length} video(s)`;
  $('summary-announcements').textContent = `${announcements.length} announcement(s)`;
  $('summary-books').textContent = `${books.length} book(s)`;
}

// --------------------
// Generic List Rendering
// --------------------
function renderList(containerId, items, renderItem) {
  const container = $(containerId);
  container.innerHTML = '';
  if (items.length === 0) {
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
  const form = e.target;
  const title = form['video-title'].value.trim();
  const description = form['video-description'].value.trim();
  let duration = parseInt(form['video-duration'].value, 10);
  const durationUnit = form['video-duration-unit'].value;
  const active = form['video-active'].checked;
  const loop = form['video-loop'].checked;
  const expiryDate = form['video-expiry'].value;
  const pin = form['video-pin'].checked;
  const fileInput = form['video-file'];
  const file = fileInput.files[0];

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
  $('upload-video-btn').classList.remove('hidden');
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
  const title = form['announcement-title'].value.trim();
  const content = form['announcement-content'].value.trim();
  let duration = parseInt(form['announcement-duration'].value, 10);
  const durationUnit = form['announcement-duration-unit'].value;
  const active = form['announcement-active'].checked;
  const expiryDate = form['announcement-expiry'].value;
  const pin = form['announcement-pin'].checked;
  const textSize = form['announcement-text-size'].value;

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
  $('new-announcement-btn').classList.remove('hidden');
  loadAnnouncements();
  loadCounts();
}

// --------------------
// Book Management
// --------------------
function createBookElement(book, index) {
  const div = document.createElement('div');
  div.className = 'p-4 border rounded mb-2 bg-gray-100 flex justify-between items-center';
  div.innerHTML = `
    <div>
      <h4 class="font-semibold">${book.title}</h4>
      <p>Author: ${book.author}</p>
      <p>Category: ${book.category}</p>
      <p>Description: ${book.description}</p>
      <p>Availability: ${book.availability}</p>
      <p>Expiry Date: ${book.expiryDate || 'None'}</p>
    </div>
    <button class="delete-book-btn bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700" data-index="${index}">Delete</button>
  `;
  return div;
}

function loadBooks() {
  const books = getData('books');
  renderList('books-list', books, createBookElement);
}

function handleBookFormSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const title = form['book-title'].value.trim();
  const author = form['book-author'].value.trim();
  const category = form['book-category'].value.trim();
  const description = form['book-description'].value.trim();
  const availability = form['book-availability'].value;
  const coverInput = form['book-cover'];
  const cover = coverInput.files[0];
  const expiryDate = form['book-expiry'].value;

  if (!title) return alert('Title is required.');
  if (!author) return alert('Author is required.');
  if (!cover) return alert('Cover image is required.');

  const books = getData('books');
  books.push({
    title,
    author,
    category,
    description,
    availability,
    cover: URL.createObjectURL(cover),
    expiryDate,
    addedAt: new Date().toISOString()
  });
  saveData('books', books);
  form.reset();
  form.classList.add('hidden');
  $('add-book-btn').classList.remove('hidden');
  loadBooks();
  loadCounts();
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

function handleFooterFormSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const message = form['footer-message'].value.trim();
  const scrollSpeed = form['scroll-speed'].value;
  const expiryDate = form['footer-expiry'].value;

  if (!message) return alert('Message is required.');

  const footers = getData('footers');

  if (editingIndex === -1) {
    footers.push({
      message,
      scrollSpeed,
      expiryDate,
      createdAt: new Date().toISOString()
    });
  } else {
    footers[editingIndex] = {
      ...footers[editingIndex],
      message,
      scrollSpeed,
      expiryDate,
      updatedAt: new Date().toISOString()
    };
    editingIndex = -1;
  }

  saveData('footers', footers);
  form.reset();
  form.classList.add('hidden');
  $('new-footer-msg-btn').classList.remove('hidden');
  loadFooters();
  loadCounts();
}

// --------------------
// Event Listeners Setup
// --------------------
function setupEventListeners() {
  tabs.forEach(tab => {
    $(`tab-btn-${tab}`).addEventListener('click', () => showTab(tab));
  });

  $('upload-video-btn').addEventListener('click', () => {
    $('upload-video-form').classList.remove('hidden');
    $('upload-video-btn').classList.add('hidden');
  });

  $('cancel-upload-video').addEventListener('click', () => {
    $('upload-video-form').reset();
    $('upload-video-form').classList.add('hidden');
    $('upload-video-btn').classList.remove('hidden');
  });

  $('upload-video-form').addEventListener('submit', handleVideoFormSubmit);

  $('videos-list').addEventListener('click', e => {
    if (e.target.classList.contains('delete-video-btn')) {
      const index = parseInt(e.target.getAttribute('data-index'), 10);
      if (confirm('Are you sure you want to delete this video?')) {
        const videos = getData('videos');
        videos.splice(index, 1);
        saveData('videos', videos);
        loadVideos();
        loadCounts();
      }
    }
  });

  $('new-announcement-btn').addEventListener('click', () => {
    $('new-announcement-form').classList.remove('hidden');
    $('new-announcement-btn').classList.add('hidden');
  });

  $('cancel-new-announcement').addEventListener('click', () => {
    $('new-announcement-form').reset();
    $('new-announcement-form').classList.add('hidden');
    $('new-announcement-btn').classList.remove('hidden');
    editingIndex = -1;
  });

  $('new-announcement-form').addEventListener('submit', handleAnnouncementFormSubmit);

  $('announcements-list').addEventListener('click', e => {
    if (e.target.classList.contains('delete-announcement-btn')) {
      const index = parseInt(e.target.getAttribute('data-index'), 10);
      if (confirm('Are you sure you want to delete this announcement?')) {
        const announcements = getData('announcements');
        announcements.splice(index, 1);
        saveData('announcements', announcements);
        loadAnnouncements();
        loadCounts();
      }
    } else if (e.target.classList.contains('edit-announcement-btn')) {
      const index = parseInt(e.target.getAttribute('data-index'), 10);
      const announcements = getData('announcements');
      const announcement = announcements[index];
      $('announcement-title').value = announcement.title;
      $('announcement-content').value = announcement.content;
      $('announcement-duration').value = announcement.duration;
      $('announcement-duration-unit').value = 'seconds';
      $('announcement-active').checked = announcement.active;
      $('announcement-expiry').value = announcement.expiryDate || '';
      $('announcement-pin').checked = announcement.pin;
      $('announcement-text-size').value = announcement.textSize;
      $('new-announcement-form').classList.remove('hidden');
      $('new-announcement-btn').classList.add('hidden');
      editingIndex = index;
    }
  });

  $('add-book-btn').addEventListener('click', () => {
    $('add-book-form').classList.remove('hidden');
    $('add-book-btn').classList.add('hidden');
  });

  $('cancel-add-book').addEventListener('click', () => {
    $('add-book-form').reset();
    $('add-book-form').classList.add('hidden');
    $('add-book-btn').classList.remove('hidden');
  });

  $('add-book-form').addEventListener('submit', handleBookFormSubmit);

  $('books-list').addEventListener('click', e => {
    if (e.target.classList.contains('delete-book-btn')) {
      const index = parseInt(e.target.getAttribute('data-index'), 10);
      if (confirm('Are you sure you want to delete this book?')) {
        const books = getData('books');
        books.splice(index, 1);
        saveData('books', books);
        loadBooks();
        loadCounts();
      }
    }
  });

  $('new-footer-msg-btn').addEventListener('click', () => {
    $('new-footer-msg-form').classList.remove('hidden');
    $('new-footer-msg-btn').classList.add('hidden');
  });

  $('cancel-new-footer-msg').addEventListener('click', () => {
    $('new-footer-msg-form').reset();
    $('new-footer-msg-form').classList.add('hidden');
    $('new-footer-msg-btn').classList.remove('hidden');
  });

  $('new-footer-msg-form').addEventListener('submit', handleFooterFormSubmit);

  $('footer-msgs-list').addEventListener('click', e => {
    if (e.target.classList.contains('delete-footer-btn')) {
      const index = parseInt(e.target.getAttribute('data-index'), 10);
      if (confirm('Are you sure you want to delete this footer message?')) {
        const footers = getData('footers');
        footers.splice(index, 1);
        saveData('footers', footers);
        loadFooters();
        loadCounts();
      }
    } else if (e.target.classList.contains('edit-footer-btn')) {
      const index = parseInt(e.target.getAttribute('data-index'), 10);
      const footers = getData('footers');
      const footer = footers[index];
      $('footer-message').value = footer.message;
      $('scroll-speed').value = footer.scrollSpeed;
      $('scroll-speed-value').textContent = footer.scrollSpeed;
      $('footer-expiry').value = footer.expiryDate || '';
      $('new-footer-msg-form').classList.remove('hidden');
      $('new-footer-msg-btn').classList.add('hidden');
      editingIndex = index;
    }
  });

  // Logout functionality with confirmation popup
  $('logout-btn').addEventListener('click', () => {
    showLogoutConfirmation();
  });

  // Scroll speed input update
  $('scroll-speed').addEventListener('input', e => {
    $('scroll-speed-value').textContent = e.target.value;
  });

  // Preview Signage button event
  $('preview-signage').addEventListener('click', showSignagePreview);
}

// Show the signage preview (index.php) in a new tab
function showSignagePreview() {
  window.open('../index.php', '_blank');
}

// --------------------
// Logout Confirmation
// --------------------
function showLogoutConfirmation() {
  const overlay = document.createElement('div');
  overlay.id = 'logout-overlay';
  Object.assign(overlay.style, {
    position: 'fixed',
    top: '0',
    left: '0',
    width: '100vw',
    height: '100vh',
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: '1000'
  });

  const popup = document.createElement('div');
  Object.assign(popup.style, {
    backgroundColor: 'white',
    padding: '30px',
    borderRadius: '12px',
    boxShadow: '0 2px 15px rgba(0,0,0,0.4)',
    textAlign: 'center',
    width: '350px'
  });

  const message = document.createElement('p');
  message.textContent = 'Are you sure you want to log out?';
  Object.assign(message.style, {
    marginBottom: '25px',
    fontSize: '18px',
    fontWeight: '600'
  });

  const btnContainer = document.createElement('div');
  Object.assign(btnContainer.style, {
    display: 'flex',
    justifyContent: 'space-around'
  });

  const yesBtn = document.createElement('button');
  yesBtn.textContent = 'Yes';
  Object.assign(yesBtn.style, {
    backgroundColor: '#dc2626',
    color: 'white',
    border: 'none',
    padding: '14px 28px',
    borderRadius: '6px',
    cursor: 'pointer',
    fontSize: '16px',
    fontWeight: '600'
  });

  const noBtn = document.createElement('button');
  noBtn.textContent = 'No';
  Object.assign(noBtn.style, {
    backgroundColor: '#6b7280',
    color: 'white',
    border: 'none',
    padding: '14px 28px',
    borderRadius: '6px',
    cursor: 'pointer',
    fontSize: '16px',
    fontWeight: '600'
  });

  btnContainer.appendChild(yesBtn);
  btnContainer.appendChild(noBtn);
  popup.appendChild(message);
  popup.appendChild(btnContainer);
  overlay.appendChild(popup);
  document.body.appendChild(overlay);

  yesBtn.addEventListener('click', () => {
    document.body.removeChild(overlay);
    const loader = document.createElement('div');
    loader.className = 'loader';
    document.body.innerHTML = '';
    Object.assign(document.body.style, {
      display: 'flex',
      justifyContent: 'center',
      alignItems: 'center',
      height: '100vh'
    });
    document.body.appendChild(loader);
    setTimeout(() => {
      localStorage.clear();
      window.location.href = 'login.php';
    }, 5000);
  });

  noBtn.addEventListener('click', () => {
    document.body.removeChild(overlay);
  });
}

// --------------------
// Initialization
// --------------------
function init() {
  tabs.forEach(tab => {
    $(`tab-btn-${tab}`).addEventListener('click', () => showTab(tab));
  });
  showTab('overview');

  const branch = localStorage.getItem('branch') || 'Main Branch';
  const adminName = localStorage.getItem('adminName') || 'Administrator';
  $('branch-info').textContent = branch;
  $('welcome-msg').textContent = `Administrator ${adminName} for ${branch}`;

  loadCounts();
  loadVideos();
  loadAnnouncements();
  loadBooks();
  loadFooters();

  setupEventListeners();
}

document.addEventListener('DOMContentLoaded', init);
