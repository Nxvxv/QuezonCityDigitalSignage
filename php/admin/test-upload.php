<?php
// Test upload endpoint for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Test endpoint works',
    'session' => $_SESSION,
    'post' => $_POST,
    'files' => $_FILES ? array_keys($_FILES) : [],
    'branch_id' => $_SESSION['branch_id'] ?? 'NOT SET'
]);
?>
