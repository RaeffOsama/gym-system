<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$userId = $_SESSION['user_id'];
$db = getDbConnection();

// Prepare statement to fetch user data
$stmt = $db->prepare("SELECT id, name, email, address, age, gender, role_name, phone, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    $stmt->close();
    $db->close();
    
    sendJson(200, true, 'User profile retrieved successfully', ['user' => $user]);
} else {
    $stmt->close();
    $db->close();
    sendJson(404, false, 'User profile not found');
}
