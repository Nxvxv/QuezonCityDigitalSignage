<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/conn.php';

// Allowed enums
$allowedCategories = [
    'Adventure','Anthropology','Art & Architecture','Autobiography','Biography','Business & Economics','Classic Literature','Cooking/Food','Dystopian','Fantasy','Graphic Novels & Comics','Horror','History',''
];
$allowedAvailability = ['Available', 'Borrowed'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Basic fields
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $expiry = isset($_POST['expiry']) ? trim($_POST['expiry']) : null;
    $availability = isset($_POST['availability']) ? trim($_POST['availability']) : '';

    if ($title === '' || $author === '') {
        throw new Exception('Title and author are required');
    }

    // Validate enums
    if (!in_array($category, $allowedCategories, true)) {
        $category = '';
    }
    if (!in_array($availability, $allowedAvailability, true)) {
        $availability = 'Available';
    }

    // Handle file upload: prefer saving file to disk and storing filename in DB.
    // If saving to disk fails, fall back to storing as data URI.
    $coverDbValue = null;
    if (isset($_FILES['cover']) && is_uploaded_file($_FILES['cover']['tmp_name'])) {
        $uploadDir = __DIR__ . '/../assets/uploads/book_covers';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $originalName = basename($_FILES['cover']['name']);
        // sanitize filename (simple)
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $targetName = $safeBase . '_' . time() . ($ext ? '.' . $ext : '');
        $targetPath = $uploadDir . '/' . $targetName;
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $targetPath)) {
            // store relative path or filename depending on preference; use filename here
            $coverDbValue = $targetName;
        } else {
            // fallback: store as data URI
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['cover']['tmp_name']);
            finfo_close($finfo);
            $data = file_get_contents($_FILES['cover']['tmp_name']);
            if ($data === false) throw new Exception('Failed to read uploaded file');
            $coverDbValue = 'data:' . ($mime ?: 'application/octet-stream') . ';base64,' . base64_encode($data);
        }
    }

    // Insert into DB - map to actual columns in your books table
    // Columns observed in your DB: title, author, cover, category, coverpic, description, expiry, status
    $stmt = $conn->prepare("INSERT INTO books (title, author, cover, category, coverpic, description, expiry, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    // Store the computed cover value (either filename or data URI) for both cover and coverpic
    $stmt->bind_param('ssssssss', $title, $author, $coverDbValue, $category, $coverDbValue, $description, $expiry, $availability);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $insertedId = $stmt->insert_id;
    $stmt->close();

    // Return the stored cover value (may be null or a data URI)
    echo json_encode(['success' => true, 'id' => $insertedId, 'cover' => $coverDbValue]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
