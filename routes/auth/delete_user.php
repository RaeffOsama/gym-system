<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

// Strictly admin only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    sendJson(403, false, 'Forbidden: Only admins can delete users');
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

$targetUserId = isset($input['user_id']) ? (int)$input['user_id'] : 0;

if ($targetUserId <= 0) {
    sendJson(400, false, 'Valid User ID is required');
}

// Prevent admin from deleting themselves (optional safety)
if ($targetUserId === $_SESSION['user_id']) {
    sendJson(400, false, 'You cannot delete your own account while logged in');
}

$db = getDbConnection();

$stmt = $db->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $targetUserId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $db->close();
        sendJson(200, true, 'User deleted successfully');
    } else {
        $stmt->close();
        $db->close();
        sendJson(404, false, 'User not found');
    }
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to delete user');
}
