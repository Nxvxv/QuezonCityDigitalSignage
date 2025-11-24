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

          // Helper to sanitize filenames / strings
          function sanitizeFileName(s) {
            if (!s) return null;
            let t = String(s).trim();
            // strip surrounding quotes if present
            if ((t.startsWith('"') && t.endsWith('"')) || (t.startsWith("'") && t.endsWith("'"))) {
              t = t.slice(1, -1).trim();
            }
            // Remove accidental HTML fragments
            t = t.replace(/<[^>]*>/g, '').trim();
            return t || null;
          }

          // Prefer server endpoint by id to stream file or BLOB; fallback to coverpic data URL or filename
          const coverpic = sanitizeFileName(book.coverpic);
          const cover = sanitizeFileName(book.cover);
          let src = null;
          if (book.id) {
            src = `../php/get_book_cover.php?id=${encodeURIComponent(book.id)}`;
          } else if (coverpic && /^data:/i.test(coverpic)) {
            src = coverpic;
          } else if (cover) {
            if (/^data:|^blob:|^https?:\/\//i.test(cover) || cover.includes('/') || cover.startsWith('assets') || cover.startsWith('/')) {
              src = cover;
            } else {
              src = `../assets/uploads/book_covers/${encodeURIComponent(cover)}`;
            }
          }

          // New layout: flip card structure
          card.className = 'flex flex-col items-center p-2 bg-white rounded shadow-sm';
          // Add semantic class so CSS can target and force smaller sizes
          card.classList.add('book-card');

          // Create flip card inner container
          const flipCardInner = document.createElement('div');
          flipCardInner.className = 'flip-card-inner';

          // Create front of card (book cover)
          const flipCardFront = document.createElement('div');
          flipCardFront.className = 'flip-card-front';

          // Create img element for the cover
          const img = document.createElement('img');
          img.alt = book.title ? String(book.title) : 'Book cover';
          img.dataset.author = book.author ? String(book.author) : '';
          img.dataset.title = book.title ? String(book.title) : '';
          
          // Set src if available, otherwise set placeholder SVG via data URL
          if (src) {
            img.src = src;
            // Add crossOrigin attribute to enable color extraction
            img.crossOrigin = 'anonymous';
          } else {
            img.src = 'data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="160" height="224"><rect width="160" height="224" fill="#f0f0f0"/><text x="80" y="112" text-anchor="middle" fill="#999" font-size="14">No Image</text></svg>');
          }

          // on error fallback: try coverpic data URL, else placeholder
          img.addEventListener('error', function () {
            if (coverpic && coverpic !== '' && /^data:/i.test(coverpic) && this.src !== coverpic) {
              this.src = coverpic;
              return;
            }
            this.onerror = null;
            this.src = 'data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="160" height="224"><rect width="160" height="224" fill="#f0f0f0"/><text x="80" y="112" text-anchor="middle" fill="#999" font-size="14">No Image</text></svg>');
          });

          // Create back of card (title and author)
          const flipCardBack = document.createElement('div');
          flipCardBack.className = 'flip-card-back';

          const title = book.title ? String(book.title) : 'Untitled';
          const author = book.author ? String(book.author) : 'Unknown Author';

          const titleElement = document.createElement('div');
          titleElement.className = 'book-title';
          titleElement.textContent = title;

          const authorElement = document.createElement('div');
          authorElement.className = 'book-author';
          authorElement.textContent = `by ${author}`;

          // Assemble the flip card structure
          flipCardFront.appendChild(img);
          flipCardBack.appendChild(titleElement);
          flipCardBack.appendChild(authorElement);
          flipCardInner.appendChild(flipCardFront);
          flipCardInner.appendChild(flipCardBack);
          card.appendChild(flipCardInner);
          booksList.appendChild(card);

          // Apply color theming after image loads
          if (img.complete) {
            applyColorTheming(img, flipCardBack);
          } else {
            img.onload = () => applyColorTheming(img, flipCardBack);
          }
        });

        // After rendering all books, set up cycling flip effect
        try {
          const cards = Array.from(document.querySelectorAll('#books-list .book-card'));
          if (cards.length > 0) {
            // Cycle through cards left-to-right every 10s. Active duration: 2.5s
            let activeIndex = 0;
            const activeDuration = 2500;
            const cycleInterval = 10000;
            // helper to activate index
            const activate = (i) => {
              cards.forEach((cc, j) => cc.classList.toggle('active', j === i));
            };

            // initial activation
            activate(activeIndex);
            // schedule deactivation after activeDuration
            let timeoutId = setTimeout(() => { cards[activeIndex].classList.remove('active'); }, activeDuration);

            const intervalId = setInterval(() => {
              // clear any pending timeout
              if (timeoutId) clearTimeout(timeoutId);
              // move to next
              activeIndex = (activeIndex + 1) % cards.length;
              activate(activeIndex);
              // remove active after activeDuration
              timeoutId = setTimeout(() => { cards[activeIndex].classList.remove('active'); }, activeDuration);
            }, cycleInterval);
            // store interval on window for potential cleanup
            window.__booksCycleInterval = intervalId;
          }
        } catch (e) { console.warn('Error setting up book cycle:', e); }

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

    // Load Color Thief library dynamically if not present
    if (typeof ColorThief === 'undefined') {
      const script = document.createElement('script');
      script.src = 'https://cdnjs.cloudflare.com/ajax/libs/color-thief/2.3.2/color-thief.umd.js';
      document.head.appendChild(script);
    }
  
    function getContrastColor(rgb) {
      // Calculate luminance using the relative luminance formula
      const [r, g, b] = rgb;
      const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
      
      // Return high contrast color based on luminance
      if (luminance > 0.6) {
        return '#1a202c'; // Dark color for light backgrounds
      } else if (luminance > 0.3) {
        return '#2d3748'; // Medium dark color for medium backgrounds
      } else {
        return '#ffffff'; // White for dark backgrounds
      }
    }

    function createGradientFromColor(rgb) {
      const [r, g, b] = rgb;
      
      // Create a subtle gradient using the dominant color
      const lighterColor = `rgb(${Math.min(255, r + 30)}, ${Math.min(255, g + 30)}, ${Math.min(255, b + 30)})`;
      const darkerColor = `rgb(${Math.max(0, r - 30)}, ${Math.max(0, g - 30)}, ${Math.max(0, b - 30)})`;
      
      return `linear-gradient(135deg, ${lighterColor} 0%, rgb(${r}, ${g}, ${b}) 50%, ${darkerColor} 100%)`;
    }

    function applyColorTheming(img, flipCardBack) {
      try {
        if (!window.ColorThief || !img.complete) {
          return;
        }

        const colorThief = new window.ColorThief();
        let dominantColor;
        
        try {
          dominantColor = colorThief.getColor(img);
        } catch (crossOriginError) {
          console.warn('CrossOrigin issue, using fallback color extraction:', crossOriginError);
          // Fallback: create a canvas and extract color manually
          const canvas = document.createElement('canvas');
          const ctx = canvas.getContext('2d');
          canvas.width = img.width || 160;
          canvas.height = img.height || 224;
          
          try {
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            dominantColor = extractDominantColorFromImageData(imageData);
          } catch (canvasError) {
            console.warn('Canvas extraction failed, using default colors:', canvasError);
            // Ultimate fallback
            flipCardBack.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            flipCardBack.style.color = 'white';
            return;
          }
        }

        const textColor = getContrastColor(dominantColor);
        const backgroundGradient = createGradientFromColor(dominantColor);

        // Apply theming
        flipCardBack.classList.add('themed');
        flipCardBack.style.setProperty('--theme-bg-color', backgroundGradient);
        flipCardBack.style.setProperty('--theme-text-color', textColor);
        
        // Apply directly as well for immediate effect
        flipCardBack.style.background = backgroundGradient;
        flipCardBack.style.color = textColor;

      } catch (error) {
        console.warn('Error applying color theming:', error);
        // Fallback to default gradient
        flipCardBack.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        flipCardBack.style.color = 'white';
      }
    }

    function extractDominantColorFromImageData(imageData) {
      const data = imageData.data;
      const colorCounts = {};
      
      // Sample every 10th pixel for performance
      for (let i = 0; i < data.length; i += 40) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];
        const alpha = data[i + 3];
        
        // Skip transparent pixels
        if (alpha < 128) continue;
        
        // Round colors to reduce variations
        const roundedR = Math.round(r / 10) * 10;
        const roundedG = Math.round(g / 10) * 10;
        const roundedB = Math.round(b / 10) * 10;
        
        const colorKey = `${roundedR},${roundedG},${roundedB}`;
        colorCounts[colorKey] = (colorCounts[colorKey] || 0) + 1;
      }
      
      // Find most frequent color
      let maxCount = 0;
      let dominantColor = [102, 126, 234]; // fallback blue
      
      for (const [colorKey, count] of Object.entries(colorCounts)) {
        if (count > maxCount) {
          maxCount = count;
          dominantColor = colorKey.split(',').map(Number);
        }
      }
      
      return dominantColor;
    }
  
    // After books are rendered, apply color logic to any missed cards
    function applyFlipColorsToAllBooks() {
      document.querySelectorAll('.book-card .flip-card-back').forEach(flipCardBack => {
        if (!flipCardBack.classList.contains('themed')) {
          const img = flipCardBack.parentElement.querySelector('.flip-card-front img');
          if (img && img.complete) {
            applyColorTheming(img, flipCardBack);
          }
        }
      });
    }
  
    // Load books immediately
    loadBooks();

    // Apply color theming after a delay to ensure ColorThief is loaded and images are ready
    setTimeout(() => {
      applyFlipColorsToAllBooks();
    }, 1500);
  });
})();
