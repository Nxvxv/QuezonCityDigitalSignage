<?php
// videos.php - handles listing, uploading, and deleting videos
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/conn.php';

// Ensure uploads directory exists
$uploadDir = __DIR__ . '/../assets/uploads/videos';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $rows = [];
    // Use table name tbl_videos and column 'video' to store the uploaded filename
    $sql = "SELECT id, title, description, video, expiry, created_at FROM tbl_videos ORDER BY created_at DESC";
    // create table if not exists
    $create = "CREATE TABLE IF NOT EXISTS tbl_videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        video VARCHAR(255) NOT NULL,
        expiry DATETIME DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    );";
    $conn->query($create);
    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) { $rows[] = $r; }
    }
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// handle delete via JSON POST
$input = json_decode(file_get_contents('php://input'), true);
if ($input && isset($input['action']) && $input['action'] === 'delete') {
    $id = isset($input['id']) ? intval($input['id']) : 0;
    if ($id <= 0) { echo json_encode(['success' => false, 'error' => 'Invalid id']); exit; }
    // fetch filename to delete file
    $stmt = $conn->prepare('SELECT video FROM tbl_videos WHERE id = ?');
    $stmt->bind_param('i', $id); $stmt->execute(); $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $file = __DIR__ . '/../assets/uploads/videos/' . $row['video'];
        if (file_exists($file)) @unlink($file);
    }
    $stmt->close();
    $del = $conn->prepare('DELETE FROM tbl_videos WHERE id = ?');
    $del->bind_param('i', $id); $ok = $del->execute(); $del->close();
    echo json_encode(['success' => (bool)$ok]); exit;
}

// handle upload via multipart/form-data POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $expiry = isset($_POST['expiry']) && $_POST['expiry'] !== '' ? $_POST['expiry'] : null;

    if ($title === '' || $description === '') {
        echo json_encode(['success' => false, 'error' => 'Title and description required']); exit;
    }

    if ($expiry) {
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $expiry);
        if ($dt) $expiry = $dt->format('Y-m-d H:i:s'); else $expiry = date('Y-m-d H:i:s', strtotime($expiry));
    }

    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'File upload error']); exit;
    }

    $file = $_FILES['video'];
    // Check type and extension
    $allowed = ['mp4'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) { echo json_encode(['success' => false, 'error' => 'Only MP4 allowed']); exit; }
    // size limit 50MB
    if ($file['size'] > 50 * 1024 * 1024) { echo json_encode(['success' => false, 'error' => 'File exceeds 50MB']); exit; }

    $safeName = uniqid('vid_', true) . '.' . $ext;
    $dest = $uploadDir . '/' . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) { echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']); exit; }

    $stmt = $conn->prepare('INSERT INTO tbl_videos (title, description, video, expiry) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $title, $description, $safeName, $expiry);
    $ok = $stmt->execute();
    if ($ok) echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
    else echo json_encode(['success' => false, 'error' => $conn->error]);
    $stmt->close();
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unsupported request']);

?>
