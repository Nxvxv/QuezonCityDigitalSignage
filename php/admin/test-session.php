<?php
session_start();
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'session' => $_SESSION,
    'branch_id' => $_SESSION['branch_id'] ?? 'NOT SET'
]);
