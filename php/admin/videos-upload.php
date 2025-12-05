<?php
// Handle video upload and save to DB
error_reporting(E_ALL);
ini_set('display_errors', 0);  // Don't display errors in output
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request.']);
    exit;
}

require_once '../../../connection/conn.php';

// Get branch id from session (or POST if needed)
$branchId = $_SESSION['branch_id'] ?? null;
if (!$branchId) {
    echo json_encode(['success' => false, 'msg' => 'No branch selected.']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$expiry = $_POST['expiry'] ?? null;

if (!$title || !$description || !isset($_FILES['video'])) {
    echo json_encode(['success' => false, 'msg' => 'Missing required fields.']);
    exit;
}

// Handle file upload
$targetDir = '../../../uploads/videos/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}
$videoFile = $_FILES['video'];
$ext = strtolower(pathinfo($videoFile['name'], PATHINFO_EXTENSION));
if ($ext !== 'mp4') {
    echo json_encode(['success' => false, 'msg' => 'Only MP4 files allowed.']);
    exit;
}
if ($videoFile['size'] > 50 * 1024 * 1024) {
    echo json_encode(['success' => false, 'msg' => 'File too large (max 50MB).']);
    exit;
}
$filename = uniqid('vid_', true) . '.mp4';
$targetPath = $targetDir . $filename;
if (!move_uploaded_file($videoFile['tmp_name'], $targetPath)) {
    echo json_encode(['success' => false, 'msg' => 'Failed to upload file.']);
    exit;
}

// Save to DB (table: videos)
$stmt = $conn->prepare("INSERT INTO videos (BranchId, Title, VideoFile) VALUES (?, ?, ?)");
$stmt->bind_param('iss', $branchId, $title, $filename);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'msg' => 'Video uploaded successfully.']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Database error.']);
}
$stmt->close();
$conn->close();
