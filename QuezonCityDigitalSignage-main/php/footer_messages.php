<?php
header('Content-Type: application/json; charset=utf-8');
// Simple REST-style endpoint for footer messages
// Supported actions:
// GET -> returns all footer rows as JSON { success: true, data: [...] }
// POST -> create new or update existing (if id provided). Expects: message, expiry, scroll_speed (optional), id (optional)
// DELETE -> delete by id (expects id in POST body or query string)

require_once __DIR__ . '/../connection/conn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT id, message, expiry, scroll_speed FROM footer ORDER BY id DESC";
    $res = $conn->query($sql);
    $rows = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
    }
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// For POST and DELETE we'll read form-encoded or JSON bodies
$input = $_POST;
// Support JSON body
if (empty($input)) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) $input = $json;
}

// Delete handler (accepts _method=DELETE or HTTP DELETE)
if ($method === 'DELETE' || (isset($input['_method']) && strtoupper($input['_method']) === 'DELETE')) {
    $id = isset($input['id']) ? intval($input['id']) : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Missing id']); exit;
    }
    $stmt = $conn->prepare('DELETE FROM footer WHERE id = ?');
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok]);
    exit;
}

// POST -> create or update
if ($method === 'POST') {
    $message = isset($input['message']) ? trim($input['message']) : '';
    $expiry = isset($input['expiry']) ? trim($input['expiry']) : '';
    $scroll_speed = isset($input['scroll_speed']) && $input['scroll_speed'] !== '' ? intval($input['scroll_speed']) : null;
    $id = isset($input['id']) ? intval($input['id']) : 0;

    if ($message === '') {
        echo json_encode(['success' => false, 'error' => 'Message is required']); exit;
    }

    // Basic expiry normalization: if empty, set to NULL
    if ($expiry === '') $expiry = null;
    if ($id > 0) {
        // UPDATE - handle combinations of NULL expiry and NULL scroll_speed explicitly
        if ($expiry === null && $scroll_speed === null) {
            $stmt = $conn->prepare('UPDATE footer SET message = ?, expiry = NULL, scroll_speed = NULL WHERE id = ?');
            $stmt->bind_param('si', $message, $id);
        } elseif ($expiry === null && $scroll_speed !== null) {
            $stmt = $conn->prepare('UPDATE footer SET message = ?, expiry = NULL, scroll_speed = ? WHERE id = ?');
            $stmt->bind_param('sii', $message, $scroll_speed, $id);
        } elseif ($expiry !== null && $scroll_speed === null) {
            $stmt = $conn->prepare('UPDATE footer SET message = ?, expiry = ?, scroll_speed = NULL WHERE id = ?');
            $stmt->bind_param('ssi', $message, $expiry, $id);
        } else {
            $stmt = $conn->prepare('UPDATE footer SET message = ?, expiry = ?, scroll_speed = ? WHERE id = ?');
            $stmt->bind_param('ssii', $message, $expiry, $scroll_speed, $id);
        }
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok]);
        exit;
    }

    // INSERT - handle NULL scroll_speed/expiry combinations explicitly so bind_param matches placeholders
    if ($expiry === null && $scroll_speed === null) {
        $stmt = $conn->prepare('INSERT INTO footer (message, expiry, scroll_speed) VALUES (?, NULL, NULL)');
        $stmt->bind_param('s', $message);
    } elseif ($expiry === null && $scroll_speed !== null) {
        $stmt = $conn->prepare('INSERT INTO footer (message, expiry, scroll_speed) VALUES (?, NULL, ?)');
        $stmt->bind_param('si', $message, $scroll_speed);
    } elseif ($expiry !== null && $scroll_speed === null) {
        $stmt = $conn->prepare('INSERT INTO footer (message, expiry, scroll_speed) VALUES (?, ?, NULL)');
        $stmt->bind_param('ss', $message, $expiry);
    } else {
        $stmt = $conn->prepare('INSERT INTO footer (message, expiry, scroll_speed) VALUES (?, ?, ?)');
        $stmt->bind_param('ssi', $message, $expiry, $scroll_speed);
    }

    $ok = $stmt->execute();
    if ($ok) {
        $newId = $stmt->insert_id;
        echo json_encode(['success' => true, 'id' => $newId]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    exit;
}

// Fallback
echo json_encode(['success' => false, 'error' => 'Unsupported method']);
exit;

?>