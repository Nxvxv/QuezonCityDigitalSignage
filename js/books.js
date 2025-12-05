// Books management for admin dashboard
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    // Use absolute path so it works from both Admin and Signage pages
    const API = '/QCPLibrary/php/admin/books.php';
    const addBtn = document.getElementById('add-book-btn');
    const form = document.getElementById('book-form');
    const cancelBtn = document.getElementById('book-cancel');
    const listDiv = document.getElementById('books-list');

    if (addBtn) {
      addBtn.addEventListener('click', () => {
        form.reset();
        form.classList.remove('hidden');
        addBtn.disabled = true;
        document.getElementById('book-title').focus();
      });
    }

    if (cancelBtn) {
      cancelBtn.addEventListener('click', () => {
        form.reset();
        form.classList.add('hidden');
        addBtn.disabled = false;
      });
    }

    // Detect if we're on signage page to hide admin controls
    const isSignagePage = window.location.pathname.includes('signage.php');
    
    // Store current flip state for signage
    let flipStates = {}; // { bookId: isFlipped }
    let flipInterval = null;
    let bookElements = [];

    async function loadBooks() {
      console.log('Loading books from API:', API);
      try {
        // On signage page: if not logged in, show message and do not fetch
        if (isSignagePage && window.IS_LOGGED_IN === false) {
          listDiv.innerHTML = '<p style="opacity:0.6">Please log in to preview your signage books.</p>';
          return;
        }
        // If logged in on signage, fetch only my books when backend supports it
        const url = isSignagePage ? (API + '?mineOnly=1') : API;
        const res = await fetch(url);
        const j = await res.json();
        console.log('Books response:', j);
        
        if (!res.ok) {
          console.error('API error status:', res.status);
          listDiv.innerHTML = '<p class="text-red-500">Error: ' + (j.error || res.status) + '</p>';
          return;
        }
        
        listDiv.innerHTML = '';
        
        if (!j.success || (j.data || []).length === 0) {
          const msg = isSignagePage ? 'No books for your account.' : 'No books yet';
          listDiv.innerHTML = '<p class="text-gray-500">' + msg + '</p>';
          return;
        }

        bookElements = [];
        
        j.data.forEach(book => {
          const div = document.createElement('div');
          
          if (isSignagePage) {
            // Signage flip card design
            div.className = 'book-flip-card';
            div.style.cssText = `
              width: 160px;
              height: 240px;
              perspective: 1000px;
              cursor: pointer;
              flex-shrink: 0;
            `;
            
            const coverImg = book.cover_image 
              ? `/QCPLibrary/uploads/book-covers/${encodeURIComponent(book.cover_image)}`
              : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="160" height="240"><rect fill="%23ddd" width="160" height="240"/><text x="80" y="120" text-anchor="middle" fill="%23999" font-size="12">No Cover</text></svg>';
            
            // Create a container for back side that will be updated with dynamic colors
            div.innerHTML = `
              <div class="flip-card-inner" style="
                position: relative;
                width: 100%;
                height: 100%;
                text-align: center;
                transition: transform 0.6s;
                transform-style: preserve-3d;
              ">
                <!-- Front side (image) -->
                <div class="flip-card-front" style="
                  position: absolute;
                  width: 100%;
                  height: 100%;
                  backface-visibility: hidden;
                  background-color: white;
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  border-radius: 8px;
                  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                  overflow: hidden;
                ">
                  <img src="${coverImg}" alt="${escapeHtml(book.title)}" 
                    class="book-cover-img"
                    data-book-id="${book.id}"
                    style="width: 100%; height: 100%; object-fit: cover;"
                    onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22160%22 height=%22240%22><rect fill=%22%23ddd%22 width=%22160%22 height=%22240%22/><text x=%2280%22 y=%22120%22 text-anchor=%22middle%22 fill=%22%23999%22>No Cover</text></svg>'" />
                </div>
                
                <!-- Back side (info) -->
                <div class="flip-card-back" style="
                  position: absolute;
                  width: 100%;
                  height: 100%;
                  backface-visibility: hidden;
                  background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
                  color: white;
                  transform: rotateY(180deg);
                  display: flex;
                  flex-direction: column;
                  align-items: center;
                  justify-content: space-between;
                  border-radius: 8px;
                  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                  padding: 15px 12px 12px 12px;
                  overflow: hidden;
                  text-align: center;
                  transition: all 0.5s ease;
                ">
                  <!-- Title section -->
                  <div class="book-title-section" style="flex-shrink: 0;">
                    <div class="book-title" style="font-weight: bold; font-size: 14px; margin-bottom: 4px; line-height: 1.25; word-break: break-word; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                      ${escapeHtml(book.title)}
                    </div>
                    <div class="book-author" style="font-size: 11px; margin-bottom: 2px; line-height: 1.2; opacity: 0.9; font-style: italic; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">
                      by ${escapeHtml(book.author)}
                    </div>
                    ${isSignagePage && (book.created_by_name || book.created_by) ? `<div style="font-size:10px; opacity:0.8;">posted by ${escapeHtml(book.created_by_name || (''+book.created_by))}</div>` : ''}
                  </div>
                  
                  <!-- Description section -->
                  <div class="book-description" style="flex: 1; display: flex; align-items: center; justify-content: center; margin: 8px 0;">
                    <div style="font-size: 11px; line-height: 1.4; max-height: 100px; overflow-y: auto; text-align: center; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); word-break: break-word;">
                      ${escapeHtml(book.description || 'No description available.')}
                    </div>
                  </div>
                  
                  <!-- Category section -->
                  <div class="book-category" style="flex-shrink: 0;">
                    <div style="font-size: 10px; line-height: 1.2; opacity: 0.8; background: rgba(0,0,0,0.2); padding: 4px 10px; border-radius: 10px; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">
                      ${escapeHtml(book.category || 'Uncategorized')}
                    </div>
                  </div>
                </div>
              </div>
            `;
            
            // Store initial flip state
            flipStates[book.id] = false;
          } else {
            // Admin layout: horizontal with edit/delete buttons
            div.className = 'p-4 border rounded bg-white';
            
            const coverImg = book.cover_image 
              ? `<img src="/QCPLibrary/uploads/book-covers/${encodeURIComponent(book.cover_image)}" alt="${escapeHtml(book.title)}" class="w-20 h-32 object-cover rounded mr-4" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22150%22><rect fill=%22%23ddd%22 width=%22100%22 height=%22150%22/><text x=%2250%22 y=%2275%22 text-anchor=%22middle%22 fill=%22%23999%22>No Image</text></svg>'" />`
              : `<div class="w-20 h-32 bg-gray-200 rounded mr-4 flex items-center justify-center text-gray-500 text-xs">No Cover</div>`;
            
            const expiryDate = book.expiry_date ? new Date(book.expiry_date).toLocaleString() : 'No expiry';
            const isExpired = book.expiry_date && new Date(book.expiry_date) < new Date();
            const expiredBadge = isExpired ? '<span class="ml-2 text-xs bg-red-200 text-red-700 px-2 py-1 rounded">EXPIRED</span>' : '';
            
            div.innerHTML = `
              <div class="flex items-start justify-between">
                <div class="flex flex-1">
                  ${coverImg}
                  <div class="flex-1">
                    <div class="font-semibold text-lg">${escapeHtml(book.title)}</div>
                    <div class="text-sm text-gray-600">by ${escapeHtml(book.author)}</div>
                    <div class="text-xs text-gray-500 mt-1">
                      Category: ${escapeHtml(book.category || 'N/A')} | 
                      Availability: <strong>${book.availability}</strong> | 
                      Expires: ${expiryDate}${expiredBadge}
                    </div>
                    <div class="text-sm text-gray-700 mt-2">${escapeHtml(book.description || '')}</div>
                  </div>
                </div>
                <div class="space-x-2 flex ml-4">
                  <button class="edit-book-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600" data-id="${book.id}">Edit</button>
                  <button class="del-book-btn bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600" data-id="${book.id}">Delete</button>
                </div>
              </div>
            `;
          }
          
          listDiv.appendChild(div);
          if (isSignagePage) {
            bookElements.push({ element: div, id: book.id });
          }
        });

        // Initialize flip animation for signage
        if (isSignagePage && bookElements.length > 0) {
          // Wait a bit for images to start loading, then analyze colors
          setTimeout(() => {
            document.querySelectorAll('.book-cover-img').forEach(img => {
              const bookId = img.getAttribute('data-book-id');
              
              const analyzeWhenReady = () => {
                analyzeImageColors(img, bookId);
              };
              
              if (img.complete && img.naturalHeight !== 0) {
                // Image already loaded
                analyzeWhenReady();
              } else {
                // Wait for image to load
                img.addEventListener('load', analyzeWhenReady, { once: true });
              }
            });
          }, 100);
          
          startFlipAnimation();
        }

        // Only add edit/delete handlers on admin pages, not signage
        if (!isSignagePage) {
          // Delete button handler (using modal)
          listDiv.querySelectorAll('.del-book-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
              const id = btn.getAttribute('data-id');
              window.showDeleteConfirmation(async () => {
                const bookDiv = btn.closest('.p-4');
                bookDiv.style.opacity = '0.5';
                
                const r = await fetch(API, {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: 'action=delete&id=' + id
                });
                const jr = await r.json();
                if (jr.success) {
                  bookDiv.remove();
                  loadBooks();
                } else {
                  bookDiv.style.opacity = '1';
                  alert('Failed to delete: ' + (jr.error || 'unknown error'));
                }
              });
            });
          });
        
          // Edit button handler
          listDiv.querySelectorAll('.edit-book-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
              const id = btn.getAttribute('data-id');
              const book = j.data.find(b => b.id == id);
              if (!book) return;

              document.getElementById('book-edit-id').value = id;
              document.getElementById('book-edit-title').value = book.title;
              document.getElementById('book-edit-author').value = book.author;
              document.getElementById('book-edit-category').value = book.category || '';
              document.getElementById('book-edit-description').value = book.description || '';
              document.getElementById('book-edit-availability').value = book.availability;
              
              if (book.expiry_date) {
                const dt = new Date(book.expiry_date);
                const dateStr = dt.toISOString().slice(0, 16);
                document.getElementById('book-edit-expiry').value = dateStr;
              } else {
                document.getElementById('book-edit-expiry').value = '';
              }
              
              if (book.cover_image) {
                document.getElementById('book-edit-current-cover').src = '../../uploads/book-covers/' + encodeURIComponent(book.cover_image);
                document.getElementById('book-edit-cover-section').classList.remove('hidden');
              } else {
                document.getElementById('book-edit-cover-section').classList.add('hidden');
              }
              
              document.getElementById('book-edit-modal').classList.remove('hidden');
            });
          });

        } // End admin-only handlers

      } catch (err) {
        console.error('LoadBooks error:', err);
        listDiv.innerHTML = '<p class="text-red-500">Error loading books: ' + err.message + '</p>';
      }
    }

    function escapeHtml(str) {
      return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // Advanced color analysis that extracts multiple dominant colors and creates sophisticated themes
    function analyzeImageColors(imgElement, bookId) {
      try {
        const performAnalysis = () => {
          try {
            const canvas = document.createElement('canvas');
            canvas.width = 150;
            canvas.height = 150;
            const ctx = canvas.getContext('2d');
            
            // Draw image on canvas with better quality
            ctx.drawImage(imgElement, 0, 0, 150, 150);
            
            const imageData = ctx.getImageData(0, 0, 150, 150);
            const data = imageData.data;
            
            // Extract dominant colors using k-means clustering approach
            const colors = extractDominantColors(data, 3);
            const primaryColor = colors[0];
            const secondaryColor = colors[1] || colors[0];
            const accentColor = colors[2] || colors[0];
            
            // Calculate brightness and determine theme
            const brightness = calculateBrightness(primaryColor);
            const isDark = brightness < 140;
            
            // Generate complementary colors
            const complementaryPrimary = generateComplementaryColor(primaryColor);
            const analogousColors = generateAnalogousColors(primaryColor);
            
            console.log(`Book ${bookId}: Primary RGB(${primaryColor.r},${primaryColor.g},${primaryColor.b}) - Brightness: ${brightness.toFixed(2)} - isDark: ${isDark}`);
            
            // Get the flip card back element
            let currentElement = imgElement;
            let flipCard = null;
            
            while (currentElement && !flipCard) {
              if (currentElement.classList && currentElement.classList.contains('book-flip-card')) {
                flipCard = currentElement;
              } else {
                currentElement = currentElement.parentElement;
              }
            }
            
            if (!flipCard) {
              console.warn('Could not find flip card for book', bookId);
              return;
            }
            
            const flipCardBack = flipCard.querySelector('.flip-card-back');
            
            if (flipCardBack) {
              // Create sophisticated background gradient
              const gradientAngle = Math.floor(Math.random() * 360);
              let backgroundGradient;
              
              if (isDark) {
                // For dark images, create a rich gradient with the extracted colors
                const darkPrimary = darkenColor(primaryColor, 0.3);
                const darkSecondary = darkenColor(secondaryColor, 0.2);
                backgroundGradient = `linear-gradient(${gradientAngle}deg, 
                  rgba(${darkPrimary.r}, ${darkPrimary.g}, ${darkPrimary.b}, 0.95) 0%, 
                  rgba(${darkSecondary.r}, ${darkSecondary.g}, ${darkSecondary.b}, 0.9) 50%,
                  rgba(${primaryColor.r}, ${primaryColor.g}, ${primaryColor.b}, 0.85) 100%)`;
              } else {
                // For light images, create a softer gradient
                const lightPrimary = lightenColor(primaryColor, 0.2);
                const lightSecondary = lightenColor(secondaryColor, 0.3);
                backgroundGradient = `linear-gradient(${gradientAngle}deg, 
                  rgba(${lightPrimary.r}, ${lightPrimary.g}, ${lightPrimary.b}, 0.9) 0%, 
                  rgba(${lightSecondary.r}, ${lightSecondary.g}, ${lightSecondary.b}, 0.8) 50%,
                  rgba(${primaryColor.r}, ${primaryColor.g}, ${primaryColor.b}, 0.75) 100%)`;
              }
              
              // Apply the gradient background
              flipCardBack.style.setProperty('background', backgroundGradient, 'important');
              
              // Add a subtle pattern overlay for texture
              const patternOverlay = `radial-gradient(circle at 20% 30%, rgba(255,255,255,0.1) 1px, transparent 2px),
                radial-gradient(circle at 70% 80%, rgba(255,255,255,0.08) 1px, transparent 2px)`;
              flipCardBack.style.setProperty('background-image', patternOverlay + ', ' + backgroundGradient, 'important');
              
              // Style different sections with appropriate colors
              styleFlipCardText(flipCardBack, {
                isDark,
                primaryColor,
                secondaryColor,
                accentColor,
                complementaryPrimary,
                analogousColors
              });
              
              console.log(`Successfully updated book ${bookId} with sophisticated theme`);
            } else {
              console.warn('Could not find flip-card-back for book', bookId);
            }
          } catch (e) {
            console.error('Error in color analysis:', e);
          }
        };
        
        if (imgElement.src && imgElement.complete && imgElement.naturalHeight > 0) {
          performAnalysis();
        } else {
          imgElement.addEventListener('load', performAnalysis, { once: true });
          if (!imgElement.src) {
            console.warn('Image element has no src for book', bookId);
          }
        }
      } catch (e) {
        console.error('Error setting up color analysis:', e);
      }
    }

    // Extract multiple dominant colors using simplified clustering
    function extractDominantColors(imageData, numColors = 3) {
      const pixels = [];
      
      // Sample every 4th pixel for performance
      for (let i = 0; i < imageData.length; i += 16) {
        const r = imageData[i];
        const g = imageData[i + 1];
        const b = imageData[i + 2];
        const a = imageData[i + 3];
        
        // Skip transparent pixels and very similar colors
        if (a > 200 && (r > 30 || g > 30 || b > 30)) {
          pixels.push({ r, g, b });
        }
      }
      
      if (pixels.length === 0) {
        return [{ r: 100, g: 100, b: 100 }];
      }
      
      // Simple k-means clustering
      const clusters = initializeClusters(pixels, numColors);
      
      for (let iter = 0; iter < 5; iter++) {
        assignPixelsToClusters(pixels, clusters);
        updateClusterCenters(clusters);
      }
      
      return clusters.map(cluster => cluster.center).filter(center => center);
    }
    
    function initializeClusters(pixels, numColors) {
      const clusters = [];
      for (let i = 0; i < numColors; i++) {
        const randomPixel = pixels[Math.floor(Math.random() * pixels.length)];
        clusters.push({
          center: { ...randomPixel },
          pixels: []
        });
      }
      return clusters;
    }
    
    function assignPixelsToClusters(pixels, clusters) {
      clusters.forEach(cluster => cluster.pixels = []);
      
      pixels.forEach(pixel => {
        let minDistance = Infinity;
        let closestCluster = clusters[0];
        
        clusters.forEach(cluster => {
          const distance = colorDistance(pixel, cluster.center);
          if (distance < minDistance) {
            minDistance = distance;
            closestCluster = cluster;
          }
        });
        
        closestCluster.pixels.push(pixel);
      });
    }
    
    function updateClusterCenters(clusters) {
      clusters.forEach(cluster => {
        if (cluster.pixels.length > 0) {
          const sum = cluster.pixels.reduce(
            (acc, pixel) => ({
              r: acc.r + pixel.r,
              g: acc.g + pixel.g,
              b: acc.b + pixel.b
            }),
            { r: 0, g: 0, b: 0 }
          );
          
          cluster.center = {
            r: Math.floor(sum.r / cluster.pixels.length),
            g: Math.floor(sum.g / cluster.pixels.length),
            b: Math.floor(sum.b / cluster.pixels.length)
          };
        }
      });
    }
    
    function colorDistance(color1, color2) {
      return Math.sqrt(
        Math.pow(color1.r - color2.r, 2) +
        Math.pow(color1.g - color2.g, 2) +
        Math.pow(color1.b - color2.b, 2)
      );
    }
    
    function calculateBrightness(color) {
      return color.r * 0.299 + color.g * 0.587 + color.b * 0.114;
    }
    
    function generateComplementaryColor(color) {
      return {
        r: 255 - color.r,
        g: 255 - color.g,
        b: 255 - color.b
      };
    }
    
    function generateAnalogousColors(color) {
      // Convert RGB to HSL, adjust hue, convert back
      const hsl = rgbToHsl(color.r, color.g, color.b);
      return [
        hslToRgb((hsl.h + 30) % 360, hsl.s, hsl.l),
        hslToRgb((hsl.h - 30 + 360) % 360, hsl.s, hsl.l)
      ];
    }
    
    function darkenColor(color, factor) {
      return {
        r: Math.max(0, Math.floor(color.r * (1 - factor))),
        g: Math.max(0, Math.floor(color.g * (1 - factor))),
        b: Math.max(0, Math.floor(color.b * (1 - factor)))
      };
    }
    
    function lightenColor(color, factor) {
      return {
        r: Math.min(255, Math.floor(color.r + (255 - color.r) * factor)),
        g: Math.min(255, Math.floor(color.g + (255 - color.g) * factor)),
        b: Math.min(255, Math.floor(color.b + (255 - color.b) * factor))
      };
    }
    
    function rgbToHsl(r, g, b) {
      r /= 255; g /= 255; b /= 255;
      const max = Math.max(r, g, b), min = Math.min(r, g, b);
      let h, s, l = (max + min) / 2;
      
      if (max === min) {
        h = s = 0;
      } else {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch (max) {
          case r: h = (g - b) / d + (g < b ? 6 : 0); break;
          case g: h = (b - r) / d + 2; break;
          case b: h = (r - g) / d + 4; break;
        }
        h /= 6;
      }
      
      return { h: h * 360, s: s * 100, l: l * 100 };
    }
    
    function hslToRgb(h, s, l) {
      h /= 360; s /= 100; l /= 100;
      const a = s * Math.min(l, 1 - l);
      const f = n => {
        const k = (n + h * 12) % 12;
        return l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
      };
      return {
        r: Math.round(f(0) * 255),
        g: Math.round(f(8) * 255),
        b: Math.round(f(4) * 255)
      };
    }
    
    // Style the flip card text with optimal contrast and visual hierarchy
    function styleFlipCardText(flipCardBack, colorTheme) {
      const { isDark, primaryColor, secondaryColor, accentColor, complementaryPrimary } = colorTheme;
      
      // Find text elements in the new structure
      const titleElement = flipCardBack.querySelector('.book-title');
      const authorElement = flipCardBack.querySelector('.book-author');
      const descriptionContainer = flipCardBack.querySelector('.book-description div');
      const categoryElement = flipCardBack.querySelector('.book-category div');
      
      // Calculate optimal text colors with high contrast
      let titleColor, authorColor, descriptionColor, categoryColor;
      let titleShadow, authorShadow, descriptionShadow, categoryShadow;
      
      if (isDark) {
        // For dark backgrounds, use light text with dark shadows
        titleColor = `rgb(255, 255, 255)`;
        authorColor = `rgba(255, 255, 255, 0.9)`;
        descriptionColor = `rgba(255, 255, 255, 0.85)`;
        categoryColor = `rgba(255, 255, 255, 0.95)`;
        
        titleShadow = '2px 2px 4px rgba(0, 0, 0, 0.7), 1px 1px 2px rgba(0, 0, 0, 0.9)';
        authorShadow = '1px 1px 3px rgba(0, 0, 0, 0.6), 1px 1px 1px rgba(0, 0, 0, 0.8)';
        descriptionShadow = '1px 1px 2px rgba(0, 0, 0, 0.7)';
        categoryShadow = '1px 1px 2px rgba(0, 0, 0, 0.6)';
      } else {
        // For light backgrounds, use dark text with light shadows
        titleColor = `rgb(0, 0, 0)`;
        authorColor = `rgba(0, 0, 0, 0.8)`;
        descriptionColor = `rgba(0, 0, 0, 0.75)`;
        categoryColor = `rgba(0, 0, 0, 0.9)`;
        
        titleShadow = '2px 2px 4px rgba(255, 255, 255, 0.8), 1px 1px 2px rgba(255, 255, 255, 1)';
        authorShadow = '1px 1px 3px rgba(255, 255, 255, 0.7), 1px 1px 1px rgba(255, 255, 255, 0.9)';
        descriptionShadow = '1px 1px 2px rgba(255, 255, 255, 0.8)';
        categoryShadow = '1px 1px 2px rgba(255, 255, 255, 0.7)';
      }
      
      // Apply styles with improved readability
      if (titleElement) {
        titleElement.style.setProperty('color', titleColor, 'important');
        titleElement.style.setProperty('text-shadow', titleShadow, 'important');
        titleElement.style.setProperty('font-weight', '700', 'important');
      }
      
      if (authorElement) {
        authorElement.style.setProperty('color', authorColor, 'important');
        authorElement.style.setProperty('text-shadow', authorShadow, 'important');
      }
      
      if (descriptionContainer) {
        descriptionContainer.style.setProperty('color', descriptionColor, 'important');
        descriptionContainer.style.setProperty('text-shadow', descriptionShadow, 'important');
      }
      
      if (categoryElement) {
        // Style category with accent color background
        const categoryBgColor = isDark ? 
          `rgba(${accentColor.r}, ${accentColor.g}, ${accentColor.b}, 0.3)` :
          `rgba(${accentColor.r}, ${accentColor.g}, ${accentColor.b}, 0.2)`;
        
        categoryElement.style.setProperty('color', categoryColor, 'important');
        categoryElement.style.setProperty('text-shadow', categoryShadow, 'important');
        categoryElement.style.setProperty('background', categoryBgColor, 'important');
        categoryElement.style.setProperty('border', `1px solid rgba(${accentColor.r}, ${accentColor.g}, ${accentColor.b}, 0.4)`, 'important');
      }
    }

    // Flip animation for signage
    function startFlipAnimation() {
      if (flipInterval) clearInterval(flipInterval);
      
      let currentIndex = 0;
      let step = 0; // 0 = show image, 1 = show description, 2 = back to image
      
      function flipNext() {
        if (bookElements.length === 0) return;
        
        // Get current book
        const currentBook = bookElements[currentIndex];
        const flipInner = currentBook.element.querySelector('.flip-card-inner');
        
        // Toggle flip state based on step
        if (step === 0) {
          // Flip to show description
          flipStates[currentBook.id] = true;
          flipInner.style.transform = 'rotateY(180deg)';
        } else if (step === 1) {
          // Flip back to show image
          flipStates[currentBook.id] = false;
          flipInner.style.transform = 'rotateY(0deg)';
          
          // Move to next book after flipping back to image
          currentIndex = (currentIndex + 1) % bookElements.length;
        }
        
        // Increment step
        step = (step + 1) % 2;
      }
      
      // Flip every 10 seconds
      flipInterval = setInterval(flipNext, 10000);
      
      // Initial flip for first book (show description)
      setTimeout(flipNext, 0);
    }

    // Create form submission
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (typeof e.stopPropagation === 'function') e.stopPropagation();
        if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
        
        const title = document.getElementById('book-title').value.trim();
        const author = document.getElementById('book-author').value.trim();
        const category = document.getElementById('book-category').value.trim();
        const description = document.getElementById('book-description').value.trim();
        const availability = document.getElementById('book-availability').value;
        const expiryDate = document.getElementById('book-expiry').value;
        const coverFile = document.getElementById('book-cover').files[0];

        if (!title || !author) {
          alert('Title and author are required');
          return;
        }

        const fd = new FormData();
        fd.append('action', 'create');
        fd.append('title', title);
        fd.append('author', author);
        fd.append('category', category);
        fd.append('description', description);
        fd.append('availability', availability);
        fd.append('expiry_date', expiryDate);
        if (coverFile) {
          fd.append('cover_image', coverFile);
        }

        console.log('Creating book:', { title, author, category, description, availability, expiryDate, hasFile: !!coverFile });

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
          loadBooks();
          window.showSuccessModal('Book Added', 'Book added successfully!', loadBooks);
        } catch (err) {
          console.error('Create error:', err);
          alert('Error creating book: ' + err.message);
        }
      });
    }

    // Edit modal handlers
    const editModal = document.getElementById('book-edit-modal');
    const editBackdrop = document.getElementById('book-edit-backdrop');
    const editCloseBtn = document.getElementById('close-book-edit-modal');
    const cancelEditBtn = document.getElementById('cancel-book-edit-btn');
    const editForm = document.getElementById('book-edit-form');

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
        const id = document.getElementById('book-edit-id').value;
        const title = document.getElementById('book-edit-title').value.trim();
        const author = document.getElementById('book-edit-author').value.trim();
        const category = document.getElementById('book-edit-category').value.trim();
        const description = document.getElementById('book-edit-description').value.trim();
        const availability = document.getElementById('book-edit-availability').value;
        const expiryDate = document.getElementById('book-edit-expiry').value;
        const coverFile = document.getElementById('book-edit-cover').files[0];

        if (!title || !author) {
          alert('Title and author are required');
          return;
        }

        const fd = new FormData();
        fd.append('action', 'edit');
        fd.append('id', id);
        fd.append('title', title);
        fd.append('author', author);
        fd.append('category', category);
        fd.append('description', description);
        fd.append('availability', availability);
        fd.append('expiry_date', expiryDate);
        if (coverFile) {
          fd.append('cover_image', coverFile);
        }

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
          loadBooks();
          window.showSuccessModal('Book Updated', 'Book updated successfully!', loadBooks);
        } catch (err) {
          console.error('Edit error:', err);
          alert('Error updating book: ' + err.message);
        }
      });
    }

    loadBooks();
  });
})();
