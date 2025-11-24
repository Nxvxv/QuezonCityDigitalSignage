<?php
// get_announcements.php
// Returns currently active announcements as JSON from the `qcpldb` database.
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/conn.php';

$table = 'announcements';
$now = date('Y-m-d H:i:s');



$sql = "SELECT id, title, announcement AS message, video, expiry, created_at, updated_at
    FROM `{$table}`
    WHERE (expiry IS NULL OR expiry = '' OR expiry > ?)
    ORDER BY created_at DESC";

$out = ['success' => false, 'data' => [], 'error' => null];

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('s', $now);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            // escape output on consumer side if needed; return raw DB values for client formatting
            $out['data'][] = $row;
        }
        $out['success'] = true;
    } else {
        $out['error'] = $stmt->error;
    }
    $stmt->close();
} else {
    $out['error'] = $conn->error;
}

echo json_encode($out);
exit;

?>
