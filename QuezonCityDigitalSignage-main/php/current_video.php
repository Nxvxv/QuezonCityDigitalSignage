<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/conn.php';

$now = date('Y-m-d H:i:s');
$query = "SELECT id, title, description, video, expiry, created_at FROM tbl_videos WHERE expiry > ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $now);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    // return the filename (client will build the URL relative to the Signage page)
    echo json_encode(['success' => true, 'video' => $row['video'], 'expiry' => $row['expiry'], 'title' => $row['title']]);
} else {
    echo json_encode(['success' => false, 'error' => 'No active video']);
}
$stmt->close();
exit;

?>
