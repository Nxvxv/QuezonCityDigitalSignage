<?php
session_start();

// Only accept POST requests for logout to prevent accidental GET logout
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'msg' => 'Method not allowed']);
  exit;
}

// Expect JSON body with csrf_token
$data = json_decode(file_get_contents('php://input'), true);
$token = $data['csrf_token'] ?? '';

if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
  http_response_code(403);
  echo json_encode(['success' => false, 'msg' => 'Invalid CSRF token']);
  exit;
}

// Destroy session safely
$_SESSION = [];
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}
session_destroy();

// Return success JSON
echo json_encode(['success' => true]);
exit;
?>
