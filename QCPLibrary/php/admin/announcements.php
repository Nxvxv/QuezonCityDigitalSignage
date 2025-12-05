<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json; charset=utf-8');
ob_start();

require_once '../../connection/conn.php';

$branchId = isset($_SESSION['branch_id']) ? intval($_SESSION['branch_id']) : 0;
require_once __DIR__ . '/util.php';
if (!$branchId) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No branch in session']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;

// Handle create announcement
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = validate_string($_POST['content'] ?? '', 4000);
    $expiryDate = parse_datetime_local($_POST['expiry_date'] ?? null);
    $textSize = validate_int($_POST['text_size'] ?? 16, 8, 72) ?? 16;
    
    if (!$title) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Title is required']);
        exit;
    }
    
    if (!$content) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Announcement content is required']);
        exit;
    }
    
    // Convert datetime-local to datetime format
    if ($expiryDate) {
        $expiryDate = parse_datetime_local($expiryDate);
    } else {
        $expiryDate = null;
    }
    
    $stmt = $conn->prepare("INSERT INTO announcements (BranchId, Title, Content, ExpiryDate, TextSize) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB prepare error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param('issss', $branchId, $title, $content, $expiryDate, $textSize);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Announcement created successfully']);
    } else {
        http_response_code(500);
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'DB execute error: ' . $stmt->error]);
    }
    exit;
}

// Handle delete announcement
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if (!$id) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid announcement ID']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM announcements WHERE AnnouncementId = ? AND BranchId = ?");
    if (!$stmt) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB prepare error']);
        exit;
    }
    
    $stmt->bind_param('ii', $id, $branchId);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Failed to delete']);
    }
    exit;
}

// Handle edit announcement
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $id = validate_int($_POST['id'] ?? null, 1);
    $title = validate_string($_POST['title'] ?? '', 255);
    $content = validate_string($_POST['content'] ?? '', 4000);
    $expiryDate = parse_datetime_local($_POST['expiry_date'] ?? null);
    $textSize = validate_int($_POST['text_size'] ?? 16, 8, 72) ?? 16;
    
    if (!$id) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid announcement ID']);
        exit;
    }
    
    if (!$title || !$content) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Title and content are required']);
        exit;
    }
    
    // Convert datetime-local to datetime format
    if ($expiryDate) {
        $expiryDate = parse_datetime_local($expiryDate);
    } else {
        $expiryDate = null;
    }
    
    $stmt = $conn->prepare("UPDATE announcements SET Title = ?, Content = ?, ExpiryDate = ?, TextSize = ? WHERE AnnouncementId = ? AND BranchId = ?");
    if (!$stmt) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB prepare error']);
        exit;
    }
    
    $stmt->bind_param('sssiii', $title, $content, $expiryDate, $textSize, $id, $branchId);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
    } else {
        http_response_code(500);
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Failed to update']);
    }
    exit;
}

// List announcements (default)
$stmt = $conn->prepare("SELECT AnnouncementId, Title, Content, ExpiryDate, TextSize, DatePosted FROM announcements WHERE BranchId = ? ORDER BY DatePosted DESC");
if (!$stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB prepare error']);
    exit;
}

$stmt->bind_param('i', $branchId);
$stmt->execute();
$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['AnnouncementId'],
        'title' => $row['Title'],
        'content' => $row['Content'],
        'expiry_date' => $row['ExpiryDate'],
        'text_size' => $row['TextSize'],
        'date_posted' => $row['DatePosted']
    ];
}

ob_end_clean();
echo json_encode(['success' => true, 'data' => $data]);
$stmt->close();
$conn->close();
exit;
