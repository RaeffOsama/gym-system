<?php

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}
if ($_SESSION['user_role'] !== 'admin') {
    sendJson(403, false, 'Forbidden: Admins only');
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendJson(400, false, 'Invalid JSON input');
}

// Extract field
$id = isset($input['id']) ? (int)$input['id'] : 0;

if ($id <= 0) {
    sendJson(400, false, 'Valid Equipment ID is required');
}

$db = getDbConnection();

// Delete the equipment
$stmt = $db->prepare("DELETE FROM equipment WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $db->close();
        sendJson(200, true, 'Equipment deleted successfully');
    } else {
        $stmt->close();
        $db->close();
        sendJson(404, false, 'Equipment not found');
    }
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to delete equipment');
}
