// Simple Books Display - Direct database fetch (extracted from php/dashboard.php)

(function () {
  document.addEventListener('DOMContentLoaded', function () {
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

        if (books.length === 0) {
          booksList.innerHTML = '<p class="text-gray-500">No books found in database.</p>';
          return;
        }

        // Display each book
        books.forEach(book => {
          const card = document.createElement('div');
          card.className = 'p-4 border rounded shadow-sm bg-white mb-3';

          let coverHtml = '';
      // Accept either `cover` or `coverpic` column from DB
      const rawCover = (book.cover && String(book.cover).trim() !== '' && book.cover !== 'NULL') ? book.cover : ((book.coverpic && String(book.coverpic).trim() !== '' && book.coverpic !== 'NULL') ? book.coverpic : null);
      if (rawCover) {
        let src = rawCover;
        // If it's not a data/blob/http URL, decide whether it's a full path or just a filename.
        if (!/^data:|^blob:|^https?:\/\//i.test(String(rawCover))) {
          const s = String(rawCover);
          // If it already looks like a path (contains a slash or starts with 'assets' or '/'), use as-is.
          if (s.includes('/') || s.startsWith('assets') || s.startsWith('/')) {
            src = s;
          } else {
            src = `../assets/uploads/book_covers/${encodeURIComponent(s)}`;
          }
        }
        coverHtml = `<img src="${src}" alt="Book cover" class="w-16 h-20 object-cover rounded mr-4" onerror="this.onerror=null;this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"64\" height=\"80\" fill=\"%23ccc\"><rect width=\"64\" height=\"80\" fill=\"%23f0f0f0\"/><text x=\"32\" y=\"45\" text-anchor=\"middle\" fill=\"%23999\" font-size=\"10\">No Image</text></svg>'" />`;
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
  });
})();
