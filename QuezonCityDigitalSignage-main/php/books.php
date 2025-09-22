<?php
// books.php - Simple direct database query for books
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/conn.php';

try {
    // Simple SELECT query to get all books
    $sql = "SELECT * FROM books ORDER BY title DESC";
    $result = $conn->query($sql);
    
    $books = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
    
    // Return success response with books data
    echo json_encode([
        'success' => true,
        'data' => $books,
        'count' => count($books)
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}

$conn->close();
?>