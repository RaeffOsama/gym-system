<?php

require_once __DIR__ . '/../../config/database.php';

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';

if (empty($email) || empty($password)) {
    sendJson(400, false, 'Email and password are required');
}

$db = getDbConnection();

// Find user by email
$stmt = $db->prepare("SELECT id, name, email, role_name, password_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    // Verify password
    if (password_verify($password, $user['password_hash'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role_name'];
        $_SESSION['user_name'] = $user['name'];

        // Remove password_hash from the response representation
        unset($user['password_hash']);
        
        $stmt->close();
        $db->close();
        
        sendJson(200, true, 'Login successful', ['user' => $user]);
    }
}

$stmt->close();
$db->close();

sendJson(401, false, 'Invalid credentials');
