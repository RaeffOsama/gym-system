<?php

require_once __DIR__ . '/../../config/database.php';

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendJson(400, false, 'Invalid JSON input');
}

// Extract fields
$name = isset($input['name']) ? trim($input['name']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$address = isset($input['address']) ? trim($input['address']) : '';
$age = isset($input['age']) ? (int)$input['age'] : null;
$gender = isset($input['gender']) ? trim($input['gender']) : '';
$password = isset($input['password']) ? $input['password'] : '';
$role_name = isset($input['role_name']) ? trim($input['role_name']) : (isset($input['role']) ? trim($input['role']) : 'user');
$phone = isset($input['phone']) ? trim($input['phone']) : '';
$balance = 0.00;

// Validate required fields
if (empty($name) || empty($email) || empty($password)) {
    sendJson(400, false, 'Name, email, and password are required');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJson(400, false, 'Invalid email format');
}

$db = getDbConnection();

// Check if email already exists
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $db->close();
    sendJson(400, false, 'Email already registered');
}
$stmt->close();

// Hash the password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Insert the user
$stmt = $db->prepare("INSERT INTO users (name, email, address, age, gender, password_hash, role_name, phone, balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssissssd", $name, $email, $address, $age, $gender, $passwordHash, $role_name, $phone, $balance);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;
    $stmt->close();
    $db->close();
    
    sendJson(201, true, 'User registered successfully', ['user_id' => $userId]);
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to register user');
}
