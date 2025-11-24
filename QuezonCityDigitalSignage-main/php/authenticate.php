<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../connection/conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'msg' => 'Method not allowed']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$adminName = trim($input['adminName'] ?? '');
$district = $input['district'] ?? '';
$branch = $input['branch'] ?? '';

if ($adminName === '' || $district === '') {
  http_response_code(400);
  echo json_encode(['success' => false, 'msg' => 'Missing fields']);
  exit;
}

// Prepare statement to avoid SQL injection
$stmt = $conn->prepare("SELECT ID, Admin_name, District, Branch FROM login_tbl WHERE LOWER(Admin_name)=LOWER(?) AND District=? AND Branch=? LIMIT 1");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['success' => false, 'msg' => 'Server error']);
  exit;
}
$stmt->bind_param('sss', $adminName, $district, $branch);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
  $row = $res->fetch_assoc();
  // Successful authentication - create server session
  session_regenerate_id(true);
  $_SESSION['user_id'] = $row['ID'];
  $_SESSION['admin_name'] = $row['Admin_name'];
  $_SESSION['district'] = $row['District'];
  $_SESSION['branch'] = $row['Branch'];
  // CSRF token for future POST actions
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  echo json_encode(['success' => true]);
  exit;
} else {
  echo json_encode(['success' => false, 'msg' => 'Verification failed']);
  exit;
}

?>
