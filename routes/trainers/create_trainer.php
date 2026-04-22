<?php

require_once __DIR__ . '/../../config/database.php';

// Check if logged in user is an admin
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    sendJson(403, false, 'Forbidden: Only admins can create trainer accounts');
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

// User fields
$name = isset($input['name']) ? trim($input['name']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '123456'; 
$role_name = 'trainer'; // Hardcoded for this endpoint

// Profile fields
$experience_years = isset($input['experience_years']) ? (int)$input['experience_years'] : 0;
$bio = isset($input['bio']) ? $input['bio'] : null;
$achievements = isset($input['achievements']) ? $input['achievements'] : null;

if (empty($name) || empty($email)) {
    sendJson(400, false, 'Name and Email are required');
}

$db = getDbConnection();

// Check if email already exists
$stmtCheck = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmtCheck->bind_param("s", $email);
$stmtCheck->execute();
if ($stmtCheck->get_result()->num_rows > 0) {
    $stmtCheck->close();
    $db->close();
    sendJson(400, false, 'Email already registered');
}
$stmtCheck->close();

$db->begin_transaction();

try {
    // 1. Create User
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmtUser = $db->prepare("INSERT INTO users (name, email, password_hash, role_name) VALUES (?, ?, ?, ?)");
    $stmtUser->bind_param("ssss", $name, $email, $passwordHash, $role_name);
    $stmtUser->execute();
    $userId = $db->insert_id;
    $stmtUser->close();

    // 2. Create Specialist Profile
    $bioJson = is_array($bio) ? json_encode($bio) : json_encode(['text' => $bio]);
    $achievementsJson = is_array($achievements) ? json_encode($achievements) : json_encode(['items' => $achievements]);

    $stmtProfile = $db->prepare("INSERT INTO specialist_profiles (user_id, experience_years, bio, achievements) VALUES (?, ?, ?, ?)");
    $stmtProfile->bind_param("iiss", $userId, $experience_years, $bioJson, $achievementsJson);
    $stmtProfile->execute();
    $stmtProfile->close();

    $db->commit();
    sendJson(201, true, 'Trainer created successfully', ['user_id' => $userId]);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Failed to create trainer: ' . $e->getMessage());
} finally {
    $db->close();
}
