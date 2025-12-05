<?php
// Start session first (before headers)
session_start();

header('Content-Type: application/json');
// Allow CORS if needed (for local dev)
// header('Access-Control-Allow-Origin: *');

// Connect to DB
require_once '../../../connection/conn.php';
require_once __DIR__ . '/../util.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$username = validate_string($input['username'] ?? '', 255);
$password = isset($input['password']) ? $input['password'] : '';
$districtId = validate_int($input['districtId'] ?? null, 1) ?? 0;
$branchId = isset($input['branchId']) ? $input['branchId'] : '';

if (!$username || !$password || !$districtId) {
    echo json_encode(['success' => false, 'msg' => 'Missing required fields.']);
    exit;
}

// Prepare query
$sql = "SELECT LoginId, Username, PasswordHash, DistrictId, BranchId FROM logincredentials WHERE Username = ? AND DistrictId = ?";
$params = [$username, $districtId];
if ($branchId) {
    $sql .= " AND BranchId = ?";
    $params[] = $branchId;
}
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'msg' => 'Server error.']);
    exit;
}
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    // Try password_verify first (for hashed passwords)
    if (password_verify($password, $row['PasswordHash']) || $password === $row['PasswordHash']) {
        // Store user data in PHP session
        $_SESSION['user_id'] = $row['LoginId'];
        $_SESSION['username'] = $row['Username'];
        $_SESSION['district_id'] = $row['DistrictId'];
        $_SESSION['district'] = $row['DistrictId'];  // For compatibility
        $_SESSION['branch_id'] = $row['BranchId'] ?: $branchId;  // Use from DB or form
        $_SESSION['branch'] = $row['BranchId'] ?: $branchId;  // For compatibility
        $_SESSION['admin_name'] = $row['Username'];
        
        echo json_encode(['success' => true, 'msg' => 'Login successful.']);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Incorrect password.']);
    }
} else {
    echo json_encode(['success' => false, 'msg' => 'User not found.']);
}
$stmt->close();
$conn->close();
