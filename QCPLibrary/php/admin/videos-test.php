<?php
// Simple video upload test
session_start();

$debug = [];
$debug['step1'] = 'Script started';
$debug['step2'] = 'Session: ' . json_encode($_SESSION);

$branchId = $_SESSION['branch_id'] ?? null;
$debug['step3'] = 'BranchId: ' . ($branchId ? $branchId : 'NULL');

if (!$branchId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'No branch ID', 'debug' => $debug]);
    exit;
}

$action = $_POST['action'] ?? null;
$debug['step4'] = 'Action: ' . ($action ? $action : 'NULL');

if ($action === 'upload') {
    $debug['step5'] = 'Upload action triggered';
    
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $debug['step6'] = 'Title: ' . $title . ', Desc: ' . $description;
    
    if (!$title || !$description) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Missing title or description', 'debug' => $debug]);
        exit;
    }
    
    if (!isset($_FILES['video'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No video file', 'debug' => $debug]);
        exit;
    }
    
    $file = $_FILES['video'];
    $debug['step7'] = 'File: ' . $file['name'] . ', Size: ' . $file['size'] . ', Error: ' . $file['error'];
    
    // Try to move file
    $targetDir = '../../uploads/videos/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $filename = uniqid('vid_', true) . '.mp4';
    $targetPath = $targetDir . $filename;
    $debug['step8'] = 'Target path: ' . $targetPath;
    
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Failed to move file', 'debug' => $debug]);
        exit;
    }
    
    $debug['step9'] = 'File moved successfully';
    
    // Connect to DB
    require_once '../../connection/conn.php';
    $debug['step10'] = 'DB connection established';
    
    $branchId = intval($branchId);
    $expiry = $_POST['expiry'] ?? null;
    if ($expiry === '') $expiry = null;
    
    $stmt = $conn->prepare("INSERT INTO videos (BranchId, Title, VideoFile) VALUES (?, ?, ?)");
    $debug['step11'] = 'Statement prepared';
    
    $stmt->bind_param('iss', $branchId, $title, $filename);
    $debug['step12'] = 'Parameters bound';
    
    if ($stmt->execute()) {
        $debug['step13'] = 'Insert successful';
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Video uploaded', 'debug' => $debug]);
    } else {
        $debug['step13'] = 'Insert failed: ' . $stmt->error;
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $stmt->error, 'debug' => $debug]);
    }
    $stmt->close();
    exit;
}

// List videos
require_once '../../connection/conn.php';
$branchId = intval($branchId);
$stmt = $conn->prepare("SELECT VideoId, Title, Description, VideoFile, ExpiryDate, created_at FROM videos WHERE BranchId = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $branchId);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $data, 'debug' => $debug]);
$stmt->close();
$conn->close();
