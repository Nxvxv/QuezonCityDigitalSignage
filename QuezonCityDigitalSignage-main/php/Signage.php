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
  <link rel="stylesheet" href="../css/style.css" />
  <!-- Ticker styles moved to ../css/style.css -->
  </head>
  <body>
    <header>
      <div class="header-logo">
        <img src="../assets/header.png" alt="Logo" class="header-img" />
      </div>
      <div class="date-time">
        <p class="time" id="time">00:00 PM</p>
        <p class="date" id="date">SEPTEMBER 08, 2025</p>
      </div>
    </header>
    <main>
      <div class="video-preview">
      <?php
      require_once '../connection/conn.php';
      $now = date('Y-m-d H:i:s');
      $query = "SELECT video FROM tbl_videos WHERE expiry > '$now' ORDER BY created_at DESC LIMIT 1";
      $result = mysqli_query($conn, $query);
      if ($row = mysqli_fetch_assoc($result)) {
        $videoFile = $row['video'];
        $src = '../assets/uploads/videos/' . htmlspecialchars($videoFile);
  // Attempt autoplay with sound. Note: many browsers block autoplay with sound — a fallback will try muted play and then unmute when possible.
  echo '<video id="signageVideo" autoplay loop playsinline preload="auto">';
  echo '<source src="' . $src . '" type="video/mp4">';
  echo 'Your browser does not support the video tag.';
  echo '</video>';
  // Inline script: set a sensible volume and try to play. If autoplay with sound is blocked, retry muted then attempt to unmute.
  ?>
  <script src="../js/signage-autoplay.js"></script>
  <?php
      } else {
        echo '<p>No featured video available.</p>';
      }
      ?>
      </div>
      <div class="announcement">
        <!-- Announcements will be loaded via JavaScript from ../php/get_announcements.php -->
        <p style="opacity:0.6">Loading announcements…</p>
      </div>
    </main>
  <div class="book-row" style="margin-bottom:10px; height:330px;">
      <!-- Books will be loaded from the database via JS -->
      <!-- Increase columns and tighten gap so more books fit on wide screens -->
  <!-- Converted from CSS grid to flex row to remove large empty column space -->
    <div id="books-list" class="flex flex-row flex-wrap gap-8" style="width:100%;">
        <p style="opacity:0.6">Loading books…</p>
      </div>
    </div>
  <footer style="padding:0; margin:0; height:96px; margin-top:16px; background:#dc2626; color:#fff;">
      <?php
      // Load footer message(s) from DB. Table assumed name: `footer` with columns (id, message, expiry, scroll_speed)
      require_once '../connection/conn.php';
      $now = date('Y-m-d H:i:s');
      // Select non-expired rows ordered newest first. Adjust table name if different.
      $fquery = "SELECT message, scroll_speed FROM footer WHERE expiry > '$now' ORDER BY id DESC";
      $fres = mysqli_query($conn, $fquery);
      if ($fres && mysqli_num_rows($fres) > 0) {
        // Use the first active footer row. If you want to combine multiple rows, we can concatenate them.
        $row = mysqli_fetch_assoc($fres);
        $message = $row['message'];
        // Sanitize values for output
        $message_html = htmlspecialchars($message);
        $scroll_speed = is_numeric($row['scroll_speed']) ? (float)$row['scroll_speed'] : 50;
        // Interpret scroll_speed: higher number -> faster. We'll convert to duration seconds inversely.
        // Choose a mapping: duration (seconds) = max(5, 200 / scroll_speed)
        $duration = max(5, 200 / max(1, $scroll_speed));
  // Output ticker with CSS variable for duration and increased font size
  echo '<div class="ticker" style="padding:0; margin:0;"><span class="ticker-text" style="--ticker-duration: ' . $duration . 's; font-size:2.4rem; margin-top:16px; display:inline-block; color:#fff;">' . $message_html . '</span></div>';
      } else {
  echo '<div class="ticker" style="padding:0; margin:0;"><span class="ticker-text" style="--ticker-duration: 20s; font-size:2.4rem; margin-top:16px; display:inline-block; color:#fff;">No footer message configured.</span></div>';
      }
      ?>
    </footer>
    <script src="../js/clock.js"></script>
    <script src="../js/signage-video.js"></script>
    <script src="../js/announcements.js"></script>
    <script src="../js/books.js"></script>
  </body>
</html>
