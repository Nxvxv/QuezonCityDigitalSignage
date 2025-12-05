<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// Set header FIRST before any output
header('Content-Type: application/json; charset=utf-8');

// Make sure no output is sent before JSON
ob_start();

require_once '../../connection/conn.php';
require_once __DIR__ . '/util.php';

// Detect if per-admin ownership column exists
$hasCreatedBy = false;
try {
    $colChk = $conn->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'books' AND COLUMN_NAME = 'CreatedBy' LIMIT 1");
    if ($colChk && $colChk->execute()) {
        $colRes = $colChk->get_result();
        $hasCreatedBy = ($colRes && $colRes->num_rows > 0);
    }
    if ($colChk) { $colChk->close(); }
} catch (Exception $e) {
    // ignore; fallback to false
}

$branchId = isset($_SESSION['branch_id']) ? intval($_SESSION['branch_id']) : 0;
if (!$branchId) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No branch in session']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;

// Handle create book
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = validate_string($_POST['title'] ?? '', 255);
    $author = validate_string($_POST['author'] ?? '', 255);
    $category = validate_string($_POST['category'] ?? '', 255);
    $description = validate_string($_POST['description'] ?? '', 4000);
    $availability = validate_string($_POST['availability'] ?? 'Available', 50);
    $expiryDate = $_POST['expiry_date'] ?? null;
    
    if (!$title) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Title is required']);
        exit;
    }
    
    if (!$author) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Author is required']);
        exit;
    }
    
    // Convert datetime-local to datetime format
    if ($expiryDate) {
        $expiryDate = parse_datetime_local($expiryDate);
    } else {
        $expiryDate = null;
    }
    
    // Handle cover image upload
    $coverImage = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cover_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($ext, $allowed)) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Only image files allowed']);
            exit;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Image too large (max 5MB)']);
            exit;
        }
        
        $uploadDir = '../../uploads/book-covers/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $coverImage = uniqid('book_', true) . '.' . $ext;
        $targetPath = $uploadDir . $coverImage;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save cover image']);
            exit;
        }
    }
    
    if ($hasCreatedBy && isset($_SESSION['user_id'])) {
        $createdBy = intval($_SESSION['user_id']);
        $stmt = $conn->prepare("INSERT INTO books (BranchId, Title, Author, Category, Description, Availability, ExpiryDate, CoverImage, CreatedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    } else {
        $stmt = $conn->prepare("INSERT INTO books (BranchId, Title, Author, Category, Description, Availability, ExpiryDate, CoverImage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    }
    if (!$stmt) {
        error_log("Prepare error: " . $conn->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB prepare error: ' . $conn->error]);
        exit;
    }
    
    if ($hasCreatedBy && isset($_SESSION['user_id'])) {
        if (!$stmt->bind_param('isssssssi', $branchId, $title, $author, $category, $description, $availability, $expiryDate, $coverImage, $createdBy)) {
            error_log("Bind param error: " . $stmt->error);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'DB bind error: ' . $stmt->error]);
            exit;
        }
    } else if (!$stmt->bind_param('isssssss', $branchId, $title, $author, $category, $description, $availability, $expiryDate, $coverImage)) {
        error_log("Bind param error: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB bind error: ' . $stmt->error]);
        exit;
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Book added successfully']);
    } else {
        error_log("Execute error: " . $stmt->error);
        $stmt->close();
        $conn->close();
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB execute error: ' . $stmt->error]);
    }
    exit;
}

// Handle delete book
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = validate_int($_POST['id'] ?? null, 1);
    
    if (!$id) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid book ID']);
        exit;
    }
    
    // Get cover image to delete
    $stmt = $conn->prepare("SELECT CoverImage FROM books WHERE BookId = ? AND BranchId = ?");
    $stmt->bind_param('ii', $id, $branchId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row && $row['CoverImage']) {
        $filePath = '../../uploads/book-covers/' . $row['CoverImage'];
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                error_log("Failed to delete cover image: " . $filePath);
            }
        }
    }
    
    $stmt = $conn->prepare("DELETE FROM books WHERE BookId = ? AND BranchId = ?");
    $stmt->bind_param('ii', $id, $branchId);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete']);
    }
    $stmt->close();
    $conn->close();
    ob_end_clean();
    exit;
}

// Handle edit book
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = validate_string($_POST['title'] ?? '', 255);
    $author = validate_string($_POST['author'] ?? '', 255);
    $category = validate_string($_POST['category'] ?? '', 255);
    $description = validate_string($_POST['description'] ?? '', 4000);
    $availability = validate_string($_POST['availability'] ?? 'Available', 50);
    $expiryDate = $_POST['expiry_date'] ?? null;
    
    if (!$id || !$title || !$author) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Title and author are required']);
        exit;
    }
    
    // Get current book to check for existing cover
    $stmt = $conn->prepare("SELECT CoverImage FROM books WHERE BookId = ? AND BranchId = ?");
    $stmt->bind_param('ii', $id, $branchId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Book not found']);
        exit;
    }
    
    $coverImage = $row['CoverImage'];
    
    // Handle new cover image if provided
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cover_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($ext, $allowed)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Only image files allowed']);
            exit;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Image too large (max 5MB)']);
            exit;
        }
        
        $uploadDir = '../../uploads/book-covers/';
        $newCoverImage = uniqid('book_', true) . '.' . $ext;
        $targetPath = $uploadDir . $newCoverImage;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Delete old cover
            if ($coverImage) {
                $oldPath = $uploadDir . $coverImage;
                if (file_exists($oldPath)) {
                    if (!unlink($oldPath)) {
                        error_log("Failed to delete old cover image: " . $oldPath);
                    }
                }
            }
            $coverImage = $newCoverImage;
        }
    }
    
    // Convert datetime format
    if ($expiryDate) {
        $expiryDate = parse_datetime_local($expiryDate);
    } else {
        $expiryDate = null;
    }
    
    $stmt = $conn->prepare("UPDATE books SET Title = ?, Author = ?, Category = ?, Description = ?, Availability = ?, ExpiryDate = ?, CoverImage = ? WHERE BookId = ? AND BranchId = ?");
    if (!$stmt) {
        error_log("Prepare error: " . $conn->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB prepare error: ' . $conn->error]);
        exit;
    }
    
    if (!$stmt->bind_param('sssssssii', $title, $author, $category, $description, $availability, $expiryDate, $coverImage, $id, $branchId)) {
        error_log("Bind param error: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB bind error: ' . $stmt->error]);
        exit;
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Book updated successfully']);
    } else {
        error_log("Execute error: " . $stmt->error);
        $stmt->close();
        $conn->close();
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB execute error: ' . $stmt->error]);
    }
    exit;
}

// List books (default)
// Support per-admin visibility if 'CreatedBy' column exists and mineOnly=1 specified
$mineOnly = isset($_GET['mineOnly']) && $_GET['mineOnly'] === '1' && isset($_SESSION['user_id']);
if ($hasCreatedBy && $mineOnly) {
    $stmt = $conn->prepare("SELECT b.BookId, b.Title, b.Author, b.Category, b.Description, b.Availability, b.ExpiryDate, b.CoverImage, b.YearPublished, b.CreatedBy, lc.Username AS CreatedByName FROM books b LEFT JOIN logincredentials lc ON lc.LoginId = b.CreatedBy WHERE b.BranchId = ? AND b.CreatedBy = ? ORDER BY b.Title ASC");
} else if ($hasCreatedBy) {
    $stmt = $conn->prepare("SELECT b.BookId, b.Title, b.Author, b.Category, b.Description, b.Availability, b.ExpiryDate, b.CoverImage, b.YearPublished, b.CreatedBy, lc.Username AS CreatedByName FROM books b LEFT JOIN logincredentials lc ON lc.LoginId = b.CreatedBy WHERE b.BranchId = ? ORDER BY b.Title ASC");
} else {
    $stmt = $conn->prepare("SELECT BookId, Title, Author, Category, Description, Availability, ExpiryDate, CoverImage, YearPublished FROM books WHERE BranchId = ? ORDER BY Title ASC");
}
if (!$stmt) {
    error_log("Prepare error: " . $conn->error);
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB prepare error: ' . $conn->error]);
    exit;
}

if ($hasCreatedBy && $mineOnly) {
    $stmt->bind_param('ii', $branchId, $_SESSION['user_id']);
} else {
    $stmt->bind_param('i', $branchId);
}
if (!$stmt->execute()) {
    error_log("Execute error: " . $stmt->error);
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB execute error: ' . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $item = [
        'id' => $row['BookId'],
        'title' => $row['Title'],
        'author' => $row['Author'],
        'category' => $row['Category'],
        'description' => $row['Description'],
        'availability' => $row['Availability'],
        'expiry_date' => $row['ExpiryDate'],
        'cover_image' => $row['CoverImage'],
        'year_published' => $row['YearPublished']
    ];
    if ($hasCreatedBy) {
        $item['created_by'] = isset($row['CreatedBy']) ? $row['CreatedBy'] : null;
        $item['created_by_name'] = isset($row['CreatedByName']) ? $row['CreatedByName'] : null;
    }
    $data[] = $item;
}

$stmt->close();
$conn->close();

ob_end_clean();
echo json_encode(['success' => true, 'data' => $data]);
exit;
?>
