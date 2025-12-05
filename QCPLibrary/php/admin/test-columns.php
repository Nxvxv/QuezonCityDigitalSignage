<?php
session_start();
header('Content-Type: application/json');

require_once '../../connection/conn.php';

// Get table structure
$result = $conn->query("DESCRIBE videos");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

echo json_encode([
    'success' => true,
    'columns' => $columns,
    'has_description' => in_array('Description', $columns)
]);
