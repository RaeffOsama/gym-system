<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$trainerId = $_SESSION['user_id'];

// Ideally, check if the user has a 'trainer' role
$db = getDbConnection();
$stmtRole = $db->prepare("SELECT role_name FROM users WHERE id = ?");
$stmtRole->bind_param("i", $trainerId);
$stmtRole->execute();
$user = $stmtRole->get_result()->fetch_assoc();
$stmtRole->close();

if (!$user || $user['role_name'] !== 'trainer') {
    sendJson(403, false, 'Forbidden: Only trainers can create sessions');
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    sendJson(400, false, 'Invalid JSON input');
}

$startTime = isset($input['start_time']) ? trim($input['start_time']) : '';
$endTime = isset($input['end_time']) ? trim($input['end_time']) : '';
$price = isset($input['price']) ? (float)$input['price'] : 0.00;

if (empty($startTime) || empty($endTime)) {
    sendJson(400, false, 'Start time and end time are required');
}

// Check for overlaps for this trainer
$stmtCheck = $db->prepare("SELECT id FROM trainer_sessions WHERE trainer_id = ? AND status != 'cancelled' AND (start_time < ? AND end_time > ?)");
$stmtCheck->bind_param("iss", $trainerId, $endTime, $startTime);
$stmtCheck->execute();
if ($stmtCheck->get_result()->num_rows > 0) {
    $stmtCheck->close();
    sendJson(409, false, 'You already have a session scheduled during this time');
}
$stmtCheck->close();

// Insert the session
$stmt = $db->prepare("INSERT INTO trainer_sessions (trainer_id, start_time, end_time, price, status) VALUES (?, ?, ?, ?, 'available')");
$stmt->bind_param("isss", $trainerId, $startTime, $endTime, $price);

if ($stmt->execute()) {
    $sessionId = $stmt->insert_id;
    $stmt->close();
    $db->close();
    sendJson(201, true, 'Training session created successfully', ['session_id' => $sessionId]);
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to create training session. Ensure trainer_sessions table exists.');
}
