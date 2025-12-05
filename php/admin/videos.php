<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);  // Don't display - just log
ini_set('log_errors', 1);

session_start();

// Set header FIRST before any output
header('Content-Type: application/json; charset=utf-8');

// Make sure no output is sent before JSON
ob_start();

require_once '../../connection/conn.php';
require_once __DIR__ . '/util.php';

// Try multiple session keys for branch/admin id
$branchId = 0;
$sessionKeys = ['branch_id', 'BranchId', 'admin_branch', 'admin_id', 'user_branch_id', 'user_id'];
foreach ($sessionKeys as $key) {
  if ($branchId === 0 && isset($_SESSION[$key]) && $_SESSION[$key] !== '') {
    $branchId = intval($_SESSION[$key]);
  }
}

// Allow explicit branchId passed from client (POST/GET) to support cases where session lacks it
if ($branchId === 0) {
    if (isset($_POST['branchId']) && $_POST['branchId'] !== '') {
        $branchId = intval($_POST['branchId']);
    } elseif (isset($_GET['branchId']) && $_GET['branchId'] !== '') {
        $branchId = intval($_GET['branchId']);
    }
}

// If still no branch, try to get from logincredentials based on username
if (!$branchId && isset($_SESSION['username'])) {
  $stmt = $conn->prepare("SELECT BranchId FROM logincredentials WHERE Username = ? LIMIT 1");
  $stmt->bind_param('s', $_SESSION['username']);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    $branchId = intval($row['BranchId']);
  }
  $stmt->close();
}

if (!$branchId) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No branch in session']);
    exit;
}

// Check if Description and created_at columns exist
$descriptionExists = false;
$createdAtExists = false;
$result = $conn->query("DESCRIBE videos");
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'Description') {
        $descriptionExists = true;
    }
    if ($row['Field'] === 'created_at') {
        $createdAtExists = true;
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;

// Handle upload
if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = validate_string($_POST['title'] ?? '', 255);
    $description = validate_string($_POST['description'] ?? '', 4000);
    $expiry = $_POST['expiry'] ?? '';
    if ($expiry === '') {
        $expiry = null;  // Convert empty string to NULL for database
    }
    
    if (!$title) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Title required']);
        exit;
    }
    
    if (!isset($_FILES['video'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No video file provided']);
        exit;
    }
    
    $file = $_FILES['video'];
    if ($file['error'] != UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'File upload error: ' . $file['error']]);
        exit;
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'mp4') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Only MP4 files allowed']);
        exit;
    }
    
    if ($file['size'] > 50 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'File too large (max 50MB)']);
        exit;
    }
    
    $targetDir = '../../uploads/videos/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $filename = uniqid('vid_', true) . '.mp4';
    $targetPath = $targetDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save video file']);
        exit;
    }
    
    // Insert into database - use different query based on whether Description column exists
    if ($descriptionExists) {
        $stmt = $conn->prepare("INSERT INTO videos (BranchId, Title, Description, VideoFile) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare error: " . $conn->error);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'DB prepare error: ' . $conn->error]);
            exit;
        }
        if (!$stmt->bind_param('isss', $branchId, $title, $description, $filename)) {
            error_log("Bind param error: " . $stmt->error);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'DB bind error: ' . $stmt->error]);
            exit;
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO videos (BranchId, Title, VideoFile) VALUES (?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare error: " . $conn->error);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'DB prepare error: ' . $conn->error]);
            exit;
        }
        if (!$stmt->bind_param('iss', $branchId, $title, $filename)) {
            error_log("Bind param error: " . $stmt->error);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'DB bind error: ' . $stmt->error]);
            exit;
        }
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Video uploaded successfully']);
    } else {
        error_log("Execute error: " . $stmt->error);
        http_response_code(500);
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'DB execute error: ' . $stmt->error]);
    }
    exit;
}

// Handle delete
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = validate_int($_POST['id'] ?? null, 1);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid video ID']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT VideoFile FROM videos WHERE VideoId = ? AND BranchId = ?");
    $stmt->bind_param('ii', $id, $branchId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row || !$row['VideoFile']) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Video not found']);
        exit;
    }
    
    $targetDir = '../../uploads/videos/';
    @unlink($targetDir . $row['VideoFile']);
    
    $del = $conn->prepare("DELETE FROM videos WHERE VideoId = ? AND BranchId = ?");
    $del->bind_param('ii', $id, $branchId);
    if ($del->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete']);
    }
    $del->close();
    $conn->close();
    exit;
}

// Handle edit
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = validate_string($_POST['title'] ?? '', 255);
    $description = validate_string($_POST['description'] ?? '', 4000);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid video ID']);
        exit;
    }
    
    if (!$title) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Title is required']);
        exit;
    }
    
    // Check if video exists and belongs to this branch
    $check = $conn->prepare("SELECT VideoFile FROM videos WHERE VideoId = ? AND BranchId = ?");
    $check->bind_param('ii', $id, $branchId);
    $check->execute();
    $checkResult = $check->get_result();
    $videoRow = $checkResult->fetch_assoc();
    
    if (!$videoRow) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Video not found']);
        exit;
    }
    
    $currentVideoFile = $videoRow['VideoFile'];
    $newVideoFile = $currentVideoFile; // Keep current unless a new one is uploaded
    
    // Handle new video file if provided
    if (isset($_FILES['video']) && $_FILES['video']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['video'];
        
        if ($file['error'] != UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File upload error: ' . $file['error']]);
            exit;
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'mp4') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Only MP4 files allowed']);
            exit;
        }
        
        if ($file['size'] > 50 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File too large (max 50MB)']);
            exit;
        }
        
        $targetDir = '../../uploads/videos/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $newVideoFile = uniqid('vid_', true) . '.mp4';
        $targetPath = $targetDir . $newVideoFile;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save video file']);
            exit;
        }
        
        // Delete old video file
        @unlink($targetDir . $currentVideoFile);
    }
    
    // Update database
    if ($descriptionExists) {
        $stmt = $conn->prepare("UPDATE videos SET Title = ?, Description = ?, VideoFile = ? WHERE VideoId = ? AND BranchId = ?");
        $stmt->bind_param('sssii', $title, $description, $newVideoFile, $id, $branchId);
    } else {
        $stmt = $conn->prepare("UPDATE videos SET Title = ?, VideoFile = ? WHERE VideoId = ? AND BranchId = ?");
        $stmt->bind_param('ssii', $title, $newVideoFile, $id, $branchId);
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Video updated successfully']);
    } else {
        http_response_code(500);
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Failed to update video']);
    }
    exit;
}

// List videos (default)
if ($descriptionExists && $createdAtExists) {
    $stmt = $conn->prepare("SELECT VideoId, Title, Description, VideoFile, created_at FROM videos WHERE BranchId = ? ORDER BY created_at DESC");
} elseif ($descriptionExists) {
    $stmt = $conn->prepare("SELECT VideoId, Title, Description, VideoFile FROM videos WHERE BranchId = ? ORDER BY VideoId DESC");
} elseif ($createdAtExists) {
    $stmt = $conn->prepare("SELECT VideoId, Title, VideoFile, created_at FROM videos WHERE BranchId = ? ORDER BY created_at DESC");
} else {
    $stmt = $conn->prepare("SELECT VideoId, Title, VideoFile FROM videos WHERE BranchId = ? ORDER BY VideoId DESC");
}
$stmt->bind_param('i', $branchId);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['VideoId'],
        'title' => $row['Title'],
        'description' => $row['Description'] ?? '',
        'file' => $row['VideoFile'],
        'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s')
    ];
}

// Make sure output buffer is clean
ob_end_clean();

// Output JSON
echo json_encode(['success' => true, 'data' => $data]);
$stmt->close();
$conn->close();
exit;
