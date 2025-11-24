<?php
// get_book_cover.php?id=123
require_once __DIR__ . '/../connection/conn.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="80"><rect width="64" height="80" fill="#f0f0f0"/><text x="32" y="45" text-anchor="middle" fill="#999" font-size="10">No Image</text></svg>';
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare('SELECT cover, coverpic FROM books WHERE id = ? LIMIT 1');
if (!$stmt) {
    http_response_code(500);
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="80"><rect width="64" height="80" fill="#f0f0f0"/><text x="32" y="45" text-anchor="middle" fill="#999" font-size="10">Error</text></svg>';
    exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

// Check for file-based cover
if ($row) {
    $cover = isset($row['cover']) ? trim($row['cover']) : null;
    $coverpic = isset($row['coverpic']) ? $row['coverpic'] : null;

    $uploadsDir = __DIR__ . '/../assets/uploads/book_covers/';

    if ($cover && $cover !== '' && strtoupper($cover) !== 'NULL') {
        // sanitize filename
        $file = basename($cover);
        $path = $uploadsDir . $file;
        if (file_exists($path) && is_readable($path)) {
            $mime = mime_content_type($path) ?: 'image/jpeg';
            header('Content-Type: ' . $mime);
            header('Cache-Control: public, max-age=86400');
            readfile($path);
            exit;
        }
    }

    // If file not present, try coverpic blob
    if (!empty($coverpic)) {
        $blob = $coverpic;
        $imgInfo = @getimagesizefromstring($blob);
        $mime = ($imgInfo && !empty($imgInfo['mime'])) ? $imgInfo['mime'] : 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=86400');
        echo $blob;
        exit;
    }
}

// Fallback: SVG placeholder
header('Content-Type: image/svg+xml');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="80"><rect width="64" height="80" fill="#f0f0f0"/><text x="32" y="45" text-anchor="middle" fill="#999" font-size="10">No Image</text></svg>';
exit;

?>
