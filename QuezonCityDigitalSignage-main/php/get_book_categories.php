<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/conn.php';

$fallback = ['Adventure','Anthropology','Art & Architecture','Autobiography','Biography','Business & Economics','Classic Literature','Cooking/Food','Dystopian','Fantasy','Graphic Novels & Comics','Horror','History',''];

try {
    // Attempt to read column type from information_schema to extract ENUM values
    $sql = "SELECT column_type FROM information_schema.columns WHERE table_schema = ? AND table_name = 'books' AND column_name = 'category' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $db = $conn->real_escape_string($conn->query("SELECT DATABASE() as db")->fetch_assoc()['db'] ?? 'qcpldb');
    if ($stmt) {
        $stmt->bind_param('s', $db);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $colType = $row['column_type'];
            // column_type example: enum('Adventure','Anthropology',...)
            if (strpos($colType, "enum(") === 0) {
                $vals = str_getcsv(trim(substr($colType, 5, -1)), ',', "'");
                echo json_encode(['success' => true, 'data' => $vals]);
                $stmt->close();
                $conn->close();
                exit;
            }
        }
        $stmt->close();
    }
    // Fallback
    echo json_encode(['success' => true, 'data' => $fallback]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'data' => $fallback]);
}

$conn->close();
?>
