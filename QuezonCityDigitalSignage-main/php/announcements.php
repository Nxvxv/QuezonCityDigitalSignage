<?php
// announcements.php
// Simple CRUD endpoint for announcements.
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/conn.php';

// Use the table name provided by the user: annoucements
$table = 'annoucements';

// Create table if not exists with the fields the user specified: title, announcement, expiry, video
$createSql = "CREATE TABLE IF NOT EXISTS `{$table}` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    announcement TEXT NOT NULL,
    video VARCHAR(255) DEFAULT NULL,
    expiry DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);";
$conn->query($createSql);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Return list of announcements from user table and map DB column names to the shape expected by the client
    $rows = [];
    $sql = "SELECT id, title, announcement AS message, video, expiry, created_at, updated_at FROM `{$table}` ORDER BY created_at DESC";
    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            // keep compatibility with client that expects 'message'
            $rows[] = $r;
        }
    }
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// For POST requests, accept JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$action = isset($input['action']) ? $input['action'] : '';

if ($action === 'create') {
    $title = isset($input['title']) ? trim($input['title']) : '';
    // client uses 'message' key; map it to DB 'announcement'
    $message = isset($input['message']) ? trim($input['message']) : (isset($input['announcement']) ? trim($input['announcement']) : '');
    $video = isset($input['video']) ? trim($input['video']) : null;
    $expiry = isset($input['expiry']) && $input['expiry'] !== '' ? $input['expiry'] : null;

    if ($title === '' || $message === '') {
        echo json_encode(['success' => false, 'error' => 'Title and announcement are required']);
        exit;
    }

    // Convert expiry from datetime-local (Y-m-d\TH:i) if needed
    if ($expiry) {
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $expiry);
        if ($dt) { $expiry = $dt->format('Y-m-d H:i:s'); }
        else { $expiry = date('Y-m-d H:i:s', strtotime($expiry)); }
    }

    $stmt = $conn->prepare("INSERT INTO `{$table}` (title, announcement, video, expiry) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $title, $message, $video, $expiry);
    $ok = $stmt->execute();
    if ($ok) {
        echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
    exit;
}

if ($action === 'update') {
    $id = isset($input['id']) ? intval($input['id']) : 0;
    $title = isset($input['title']) ? trim($input['title']) : '';
    $message = isset($input['message']) ? trim($input['message']) : (isset($input['announcement']) ? trim($input['announcement']) : '');
    $video = isset($input['video']) ? trim($input['video']) : null;
    $expiry = isset($input['expiry']) && $input['expiry'] !== '' ? $input['expiry'] : null;

    if ($id <= 0 || $title === '' || $message === '') {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }
    if ($expiry) {
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $expiry);
        if ($dt) { $expiry = $dt->format('Y-m-d H:i:s'); }
        else { $expiry = date('Y-m-d H:i:s', strtotime($expiry)); }
    }

    $stmt = $conn->prepare("UPDATE `{$table}` SET title=?, announcement=?, video=?, expiry=? WHERE id=?");
    $stmt->bind_param('ssssi', $title, $message, $video, $expiry, $id);
    $ok = $stmt->execute();
    if ($ok) echo json_encode(['success' => true]);
    else echo json_encode(['success' => false, 'error' => $conn->error]);
    $stmt->close();
    exit;
}

if ($action === 'delete') {
    $id = isset($input['id']) ? intval($input['id']) : 0;
    if ($id <= 0) { echo json_encode(['success' => false, 'error' => 'Invalid id']); exit; }
    $stmt = $conn->prepare("DELETE FROM `{$table}` WHERE id=?");
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    if ($ok) echo json_encode(['success' => true]);
    else echo json_encode(['success' => false, 'error' => $conn->error]);
    $stmt->close();
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);

?>
