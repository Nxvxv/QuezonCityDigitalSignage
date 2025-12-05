<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

ob_start();

require_once '../../connection/conn.php';

// Get BranchId from session - use 0 as default if not set (will match all or use specific logic)
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

// List footer messages
$stmt = $conn->prepare("SELECT FooterId, Content, ScrollSpeed, ExpiryDate FROM footer WHERE BranchId = ? ORDER BY FooterId DESC");
if (!$stmt) {
    error_log("Prepare error: " . $conn->error);
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB prepare error']);
    exit;
}

$stmt->bind_param('i', $branchId);
if (!$stmt->execute()) {
    error_log("Execute error: " . $stmt->error);
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB execute error']);
    exit;
}

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['FooterId'],
        'message' => $row['Content'],
        'msg' => $row['Content'],
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
