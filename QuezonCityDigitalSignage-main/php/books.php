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
            // Normalize cover filename: treat empty or 'NULL' as null
            if (isset($row['cover'])) {
                $c = $row['cover'];
                if ($c === null || $c === '' || strtoupper($c) === 'NULL') {
                    $row['cover'] = null;
                } else {
                    // trim whitespace
                    $row['cover'] = trim($c);
                }
            }

            // If coverpic is a BLOB (binary), convert to a base64 data URL so the JS can use it directly
            if (isset($row['coverpic']) && $row['coverpic'] !== null && $row['coverpic'] !== '') {
                $blob = $row['coverpic'];
                // Try to detect MIME type from binary data
                $mime = 'image/jpeg';
                $imgInfo = @getimagesizefromstring($blob);
                if ($imgInfo && !empty($imgInfo['mime'])) {
                    $mime = $imgInfo['mime'];
                }
                // Convert binary to base64 data URL
                $row['coverpic'] = 'data:' . $mime . ';base64,' . base64_encode($blob);
            } else {
                $row['coverpic'] = null;
            }

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