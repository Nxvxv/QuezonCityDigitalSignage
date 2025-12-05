<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

require_once '../../../connection/conn.php';

// Check if videos table exists and show its structure
$result = $conn->query("DESCRIBE videos");

if (!$result) {
    echo json_encode([
        'success' => false, 
        'error' => $conn->error
    ]);
    exit;
}

$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row;
}

echo json_encode([
    'success' => true,
    'table_columns' => $columns,
    'session_data' => $_SESSION
]);
