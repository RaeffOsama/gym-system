<?php

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

if ($_SESSION['user_role'] !== 'admin') {
    sendJson(403, false, 'Forbidden: Admins only');
}

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($userId <= 0) {
    sendJson(400, false, 'user_id is required (e.g. /api/users/detail?user_id=1)');
}

$db = getDbConnection();

$stmt = $db->prepare("
    SELECT id, name, phone, address, age, gender
    FROM users
    WHERE id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$db->close();

if (!$user) {
    sendJson(404, false, 'User not found');
}

sendJson(200, true, 'User retrieved successfully', ['user' => $user]);
