<?php

require_once __DIR__ . '/../../config/database.php';

// Strictly admin only
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    sendJson(403, false, 'Forbidden: Only admins can delete nutritionist accounts');
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

$targetUserId = isset($input['user_id']) ? (int)$input['user_id'] : 0;

if ($targetUserId <= 0) {
    sendJson(400, false, 'Valid Nutritionist User ID is required');
}

$db = getDbConnection();

// Verify user is a nutritionist before deleting
$stmtVerify = $db->prepare("SELECT role_name FROM users WHERE id = ?");
$stmtVerify->bind_param("i", $targetUserId);
$stmtVerify->execute();
$userData = $stmtVerify->get_result()->fetch_assoc();
$stmtVerify->close();

if (!$userData || $userData['role_name'] !== 'nutritionist') {
    $db->close();
    sendJson(403, false, 'Forbidden: Targeted user is not a nutritionist');
}

// Proceed with deletion
$stmt = $db->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $targetUserId);

if ($stmt->execute()) {
    $stmt->close();
    $db->close();
    sendJson(200, true, 'Nutritionist deleted successfully');
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to delete nutritionist');
}
