<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/conn.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($id <= 0) throw new Exception('Missing id');

    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $expiry = isset($_POST['expiry']) ? trim($_POST['expiry']) : null;
    $availability = isset($_POST['availability']) ? trim($_POST['availability']) : '';

    // handle optional cover upload similar to books_insert
    $coverDbValue = null;
    if (isset($_FILES['cover']) && is_uploaded_file($_FILES['cover']['tmp_name'])) {
        $uploadDir = __DIR__ . '/../assets/uploads/book_covers';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $originalName = basename($_FILES['cover']['name']);
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $targetName = $safeBase . '_' . time() . ($ext ? '.' . $ext : '');
        $targetPath = $uploadDir . '/' . $targetName;
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $targetPath)) {
            $coverDbValue = $targetName;
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['cover']['tmp_name']);
            finfo_close($finfo);
            $data = file_get_contents($_FILES['cover']['tmp_name']);
            if ($data === false) throw new Exception('Failed to read uploaded file');
            $coverDbValue = 'data:' . ($mime ?: 'application/octet-stream') . ';base64,' . base64_encode($data);
        }
    }

    // Build update SQL dynamically
    $fields = [];
    $params = [];
    $types = '';
    if ($title !== '') { $fields[] = 'title = ?'; $params[] = $title; $types .= 's'; }
    if ($author !== '') { $fields[] = 'author = ?'; $params[] = $author; $types .= 's'; }
    if ($category !== '') { $fields[] = 'category = ?'; $params[] = $category; $types .= 's'; }
    if ($description !== '') { $fields[] = 'description = ?'; $params[] = $description; $types .= 's'; }
    if ($expiry !== null) { $fields[] = 'expiry = ?'; $params[] = $expiry; $types .= 's'; }
    if ($availability !== '') { $fields[] = 'status = ?'; $params[] = $availability; $types .= 's'; }
    if ($coverDbValue !== null) { $fields[] = 'cover = ?'; $params[] = $coverDbValue; $fields[] = 'coverpic = ?'; $params[] = $coverDbValue; $types .= 'ss'; }

    if (count($fields) === 0) throw new Exception('Nothing to update');

    $sql = 'UPDATE books SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $params[] = $id; $types .= 'i';

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);

    // bind params dynamically
    $bind_names[] = $types;
    for ($i=0; $i<count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);

    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
    $stmt->close();

    echo json_encode(['success' => true, 'id' => $id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
$conn->close();
?>