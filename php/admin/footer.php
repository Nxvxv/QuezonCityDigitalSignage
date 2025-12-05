<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/util.php';
session_start();

// Set header FIRST before any output
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Make sure no output is sent before JSON
ob_start();

require_once '../../connection/conn.php';

$branchId = isset($_SESSION['branch_id']) ? intval($_SESSION['branch_id']) : 0;

// If no branchId in session, try to get from logincredentials based on username
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

// Default to 1 if still no branchId
if (!$branchId) {
    $branchId = 1;
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;

error_log("Footer action: " . ($action ?? 'list') . " | BranchId: " . $branchId);

// Handle create footer message
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $content = validate_string($_POST['content'] ?? '', 1000);
        $scrollSpeed = validate_int($_POST['scroll_speed'] ?? 2, 1, 50) ?? 2;
        $expiryDate = parse_datetime_local($_POST['expiry_date'] ?? null);
    
    if (!$content) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Message is required']);
        exit;
    }
    
    // Convert datetime-local to datetime format
    if ($expiryDate) {
            $expiryDate = parse_datetime_local($expiryDate);
    } else {
        $expiryDate = null;
    }
    
    $stmt = $conn->prepare("INSERT INTO footer (BranchId, Content, ScrollSpeed, ExpiryDate) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare error: " . $conn->error);
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB prepare error: ' . $conn->error]);
        exit;
    }
    
    if (!$stmt->bind_param('isis', $branchId, $content, $scrollSpeed, $expiryDate)) {
        error_log("Bind param error: " . $stmt->error);
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB bind error: ' . $stmt->error]);
        exit;
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Message added successfully']);
    } else {
        error_log("Execute error: " . $stmt->error);
        $stmt->close();
        $conn->close();
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB execute error: ' . $stmt->error]);
    }
    exit;
}

// Handle delete footer message
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if (!$id) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM footer WHERE FooterId = ? AND BranchId = ?");
    $stmt->bind_param('ii', $id, $branchId);
    if ($stmt->execute()) {
        error_log("Deleted footer message ID: $id for BranchId: $branchId");
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => true]);
    } else {
        error_log("Delete failed: " . $stmt->error);
        $stmt->close();
        $conn->close();
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete']);
    }
    exit;
}

// Handle edit footer message
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $content = trim($_POST['content'] ?? '');
    $scrollSpeed = intval($_POST['scroll_speed'] ?? 2);
    $expiryDate = $_POST['expiry_date'] ?? null;
    
    if (!$id || !$content) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Message is required']);
        exit;
    }
    
    // Convert datetime format
    if ($expiryDate) {
            $expiryDate = parse_datetime_local($expiryDate);
    } else {
        $expiryDate = null;
    }
    
    $stmt = $conn->prepare("UPDATE footer SET Content = ?, ScrollSpeed = ?, ExpiryDate = ? WHERE FooterId = ? AND BranchId = ?");
    if (!$stmt) {
        error_log("Prepare error: " . $conn->error);
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB prepare error: ' . $conn->error]);
        exit;
    }
    
    if (!$stmt->bind_param('sisii', $content, $scrollSpeed, $expiryDate, $id, $branchId)) {
        error_log("Bind param error: " . $stmt->error);
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB bind error: ' . $stmt->error]);
        exit;
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Message updated successfully']);
    } else {
        error_log("Execute error: " . $stmt->error);
        $stmt->close();
        $conn->close();
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB execute error: ' . $stmt->error]);
    }
    exit;
}

// List footer messages (default)
$stmt = $conn->prepare("SELECT FooterId, Content, ScrollSpeed, ExpiryDate FROM footer WHERE BranchId = ? ORDER BY FooterId DESC");
if (!$stmt) {
    error_log("Prepare error: " . $conn->error);
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB prepare error: ' . $conn->error]);
    exit;
}

$stmt->bind_param('i', $branchId);
if (!$stmt->execute()) {
    error_log("Execute error: " . $stmt->error);
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB execute error: ' . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['FooterId'],
        'content' => $row['Content'],
        'scroll_speed' => $row['ScrollSpeed'],
        'expiry_date' => $row['ExpiryDate']
    ];
}

$stmt->close();
$conn->close();

ob_end_clean();
echo json_encode(['success' => true, 'data' => $data]);
exit;
?>
