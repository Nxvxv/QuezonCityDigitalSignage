<?php
// Admin Dashboard migrated from dashboard.html
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>QC Library Digital Signage - Live Display</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <header>
      <div class="header-logo">
        <img src="assets/header.png" alt="Logo" class="header-img" />
      </div>
      <div class="date-time">
        <p class="time" id="time">00:00 PM</p>
        <p class="date" id="date">SEPTEMBER 08, 2025</p>
      </div>
    </header>
    <main>
      <div class="video-preview">
        FEATURED VIDEO PREVIEW
      </div>
      <div class="announcement">
        ANNOUNCEMENT PREVIEW<br />
        <span class="announcement-span"></span>
      </div>
    </main>
    <div class="book-row">
      <!-- Book Boxes -->
      <div class="book-box book-hover">
        <div class="book-overlay">
          <h3>Book Title 1</h3>
          <p>Author: Author 1</p>
          <p>Description of book 1.</p>
        </div>
      </div>
      <div class="book-box book-hover">
        <div class="book-overlay">
          <h3>Book Title 2</h3>
          <p>Author: Author 2</p>
          <p>Description of book 2.</p>
        </div>
      </div>
      <div class="book-box book-hover">
        <div class="book-overlay">
          <h3>Book Title 3</h3>
          <p>Author: Author 3</p>
          <p>Description of book 3.</p>
        </div>
      </div>
      <div class="book-box book-hover">
        <div class="book-overlay">
          <h3>Book Title 4</h3>
          <p>Author: Author 4</p>
          <p>Description of book 4.</p>
        </div>
      </div>
      <div class="book-box book-hover">
        <div class="book-overlay">
          <h3>Book Title 5</h3>
          <p>Author: Author 5</p>
          <p>Description of book 5.</p>
        </div>
      </div>
      <div class="book-box book-hover">
        <div class="book-overlay">
          <h3>Book Title 6</h3>
          <p>Author: Author 6</p>
          <p>Description of book 6.</p>
        </div>
      </div>
      <div class="book-box book-hover">
        <div class="book-overlay">
          <h3>Book Title 7</h3>
          <p>Author: Author 7</p>
          <p>Description of book 7.</p>
        </div>
      </div>
      <div class="book-box book-hover">
        <div class="book-overlay">
          <h3>Book Title 8</h3>
          <p>Author: Author 8</p>
          <p>Description of book 8.</p>
        </div>
      </div>
    </div>
    <footer>
      <span class="ticker-text">
        PREVIEW TEXT HERE PREVIEW TEXT HERE PREVIEW TEXT HERE PREVIEW TEXT HERE PREVIEW TEXT HERE PREVIEW TEXT HERE PREVIEW TEXT HERE PREVIEW TEXT HERE
      </span>
    </footer>
    <script>
      // Get the current date formatted as uppercase string
      function getCurrentDate() {
        const now = new Date();
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return now.toLocaleDateString('en-US', options).toUpperCase();
      }

      // Get the current time formatted as uppercase string
      function getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('en-US', { hour12: true }).toUpperCase();
      }

      // Update the display with current date and time
      function updateDisplay() {
        const dateElement = document.getElementById('date');
        const timeElement = document.getElementById('time');
        dateElement.textContent = getCurrentDate();
        timeElement.textContent = getCurrentTime();
      }

      // Initialize and start the clock update
      function startClock() {
        updateDisplay();
        setInterval(updateDisplay, 1000);
      }

      startClock();
    </script>
  </body>
</html>
