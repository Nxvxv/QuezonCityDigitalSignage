<?php
// Require login to access signage preview
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
if (!isset($_SESSION['user_id'])) {
  header('Location: /QCPLibrary/php/admin/login/login.php');
  exit;
}
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
  <body style="min-height:100vh;display:flex;flex-direction:column;">
    <script>
      // Expose login state to JS for conditional data loading (books/videos)
      window.IS_LOGGED_IN = <?php
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        echo isset($_SESSION['user_id']) ? 'true' : 'false';
      ?>;
    </script>
    <header style="height: 110px; min-height: 90px; display: flex; align-items: center;">
      <div class="header-logo">
        <img src="../assets/header.png" alt="Logo" class="header-img" />
      </div>
      <div class="date-time">
        <p class="time" id="time"></p>
        <p class="date" id="date"></p>
      </div>
    </header>
    <main style="flex:1 0 auto; display: grid; grid-template-columns: 2fr 1.5fr; grid-template-rows: 1fr; gap: 24px; align-items: start; padding: 16px 16px 0 16px; min-height: 330px;">
      <div class="video-preview" style="height: 60vh; max-height: 520px; min-height: 240px; background: #0d1326; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
        <?php
        // Limit featured video visibility to the uploader's branch/admin only
        if (session_status() !== PHP_SESSION_ACTIVE) {
          session_start();
        }
        require_once __DIR__ . '/../connection/conn.php';
        $now = date('Y-m-d H:i:s');
        // Determine current user's branch/admin id from session or query string
        $currentBranchId = null;
        // Allow explicit preview via URL: signage.php?branchId=1
        if (isset($_GET['branchId']) && $_GET['branchId'] !== '') {
          $currentBranchId = (int)$_GET['branchId'];
        }
        // Common session key variants used across the app
        $sessionKeys = [
          'BranchId', 'branch_id', 'admin_branch', 'admin_id', 'user_branch_id', 'user_id'
        ];
        foreach ($sessionKeys as $key) {
          if ($currentBranchId === null && isset($_SESSION[$key]) && $_SESSION[$key] !== '') {
            $currentBranchId = (int)$_SESSION[$key];
          }
        }

        // If we have a branch/admin id, show only their latest video
        if ($currentBranchId !== null) {
          $stmt = $conn->prepare('SELECT VideoFile FROM videos WHERE BranchId = ? ORDER BY VideoId DESC LIMIT 1');
          $stmt->bind_param('i', $currentBranchId);
        } else {
          // Not logged in or no branch/admin context: do not expose other users' videos
          $stmt = null;
        }

        if ($stmt) {
          $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
          $videoFile = $row['VideoFile'];
          // Correct path to uploads/videos directory
          $src = '../uploads/videos/' . htmlspecialchars($videoFile);
          echo '<video id="signageVideo" autoplay loop playsinline preload="auto">';
          echo '<source src="' . $src . '" type="video/mp4">';
          echo 'Your browser does not support the video tag.';
          echo '</video>';
        ?>
        <script src="../js/signage-autoplay.js"></script>
        <?php
        } else {
          echo '<p>No featured video available for your account.</p>';
        }
        if (isset($stmt)) { $stmt->close(); }
        } else {
          echo '<p>Please log in to preview your signage video.</p>';
        }
        ?>
      </div>
      <div class="announcement" style="height: 60vh; max-height: 520px; min-height: 240px; background: #e8edff; border-radius: 12px; display: flex; align-items: flex-start; justify-content: center; padding: 16px;">
        <?php
          // Render announcements for the same Branch/Admin as video
          // Uses $currentBranchId determined above
          if (!isset($conn)) {
            require_once __DIR__ . '/../connection/conn.php';
          }
          // Prepare announcements only if we have a branch/admin id
          if (isset($currentBranchId) && $currentBranchId !== null) {
            $astmt = $conn->prepare('SELECT Title, Content, ExpiryDate FROM announcements WHERE BranchId = ? AND (ExpiryDate IS NULL OR ExpiryDate >= ?) ORDER BY AnnouncementId DESC LIMIT 5');
            $astmt->bind_param('is', $currentBranchId, $now);
            $astmt->execute();
            $ares = $astmt->get_result();
            if ($ares && $ares->num_rows > 0) {
              echo '<div style="width: 100%;">';
              while ($arow = $ares->fetch_assoc()) {
                $title = htmlspecialchars($arow['Title'] ?? '');
                $content = htmlspecialchars($arow['Content'] ?? '');
                // Plain text without inner box
                if ($title !== '') {
                  echo '<div class="announcement-title" style="margin-bottom:6px;">' . $title . '</div>';
                }
                echo '<div class="announcement-content" style="color:#111827; line-height:1.6; margin-bottom:14px;">' . $content . '</div>';
              }
              echo '</div>';
            } else {
              echo '<p style="opacity:0.6; text-align:center;">No announcements for your account.</p>';
            }
            if (isset($astmt)) { $astmt->close(); }
          } else {
            echo '<p style="opacity:0.6; text-align:center;">Please log in to view your announcements.</p>';
          }
        ?>
      </div>
    </main>
  <div class="book-row" style="margin-bottom:10px; height:380px; margin-top: -20px;">
      <!-- Books will be loaded from the database via JS -->
      <!-- Increase columns and tighten gap so more books fit on wide screens -->
  <!-- Converted from CSS grid to flex row to remove large empty column space -->
    <div id="books-list" class="flex flex-row flex-wrap gap-16" style="width:100%; padding-left: 24px;">
        <p style="opacity:0.6">Loading books…</p>
      </div>
    </div>
  <footer style="padding:0; margin:0; height:72px; background:#dc2626; color:#fff; flex-shrink:0; width:100vw; display:flex; align-items:center; justify-content:center;">
      <?php
      // Load footer message(s) from DB. Table assumed name: `footer` with columns (id, message, expiry, scroll_speed)
      require_once __DIR__ . '/../connection/conn.php';
      $now = date('Y-m-d H:i:s');
      // Determine current user's branch/admin id (already computed above as $currentBranchId)
      if (!isset($currentBranchId)) {
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        $currentBranchId = null;
        $sessionKeys = ['BranchId','branch_id','admin_branch','admin_id','user_branch_id','user_id'];
        foreach ($sessionKeys as $key) {
          if ($currentBranchId === null && isset($_SESSION[$key]) && $_SESSION[$key] !== '') {
            $currentBranchId = (int)$_SESSION[$key];
          }
        }
      }

      // Parameterized select for active footer message for the user's branch with expiry respected
      if ($currentBranchId !== null) {
        $fstmt = $conn->prepare('SELECT Content, ScrollSpeed FROM footer WHERE BranchId = ? AND (ExpiryDate IS NULL OR ExpiryDate >= ?) ORDER BY FooterId DESC LIMIT 1');
        $fstmt->bind_param('is', $currentBranchId, $now);
        $fstmt->execute();
        $fres = $fstmt->get_result();
        if ($fres && $fres->num_rows > 0) {
          $row = $fres->fetch_assoc();
          $message = $row['Content'] ?? '';
          $speed = isset($row['ScrollSpeed']) ? (int)$row['ScrollSpeed'] : 20;
          if ($speed <= 0) { $speed = 20; }
          $message_html = htmlspecialchars($message);
          echo '<div class="ticker" style="padding:0; margin:0;"><span class="ticker-text" style="--ticker-duration: ' . $speed . 's; font-size:2.4rem; display:inline-block; color:#fff;">' . $message_html . '</span></div>';
        } else {
          echo '<div class="ticker" style="padding:0; margin:0;"><span class="ticker-text" style="--ticker-duration: 20s; font-size:2.4rem; display:inline-block; color:#fff;">No footer message configured for your account.</span></div>';
        }
        if (isset($fstmt)) { $fstmt->close(); }
      } else {
        echo '<div class="ticker" style="padding:0; margin:0;"><span class="ticker-text" style="--ticker-duration: 20s; font-size:2.4rem; display:inline-block; color:#fff;">Please log in to view your footer message.</span></div>';
      }
      ?>
    </footer>
    <script src="QCPLibrary/js/clock.js"></script>
    <script>
      // Live clock and date updater
      function updateClockAndDate() {
        const now = new Date();
        // Format time as HH:MM AM/PM
        let hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 should be 12
        const timeString = `${hours.toString().padStart(2, '0')}:${minutes} ${ampm}`;
        document.getElementById('time').textContent = timeString;
        // Format date as MONTH DD, YYYY
        const monthNames = [
          'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
          'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'
        ];
        const dateString = `${monthNames[now.getMonth()]} ${now.getDate().toString().padStart(2, '0')}, ${now.getFullYear()}`;
        document.getElementById('date').textContent = dateString;
      }
      setInterval(updateClockAndDate, 1000);
      updateClockAndDate();
    </script>
    <script src="../js/signage-video.js"></script>
    <script src="../js/announcements.js"></script>
    <script src="../js/books.js"></script>
  </body>
</html>
