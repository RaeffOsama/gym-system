<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$loggedInUserId = $_SESSION['user_id'];
$loggedInUserRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

// Decide target user (Admin can update anyone, regular user can only update self)
$targetUserId = (isset($input['user_id']) && $loggedInUserRole === 'admin') ? (int)$input['user_id'] : $loggedInUserId;

$name = isset($input['name']) ? trim($input['name']) : '';
$address = isset($input['address']) ? trim($input['address']) : '';
$phone = isset($input['phone']) ? trim($input['phone']) : '';
$age = isset($input['age']) ? (int)$input['age'] : null;
$gender = isset($input['gender']) ? trim($input['gender']) : '';
$role_name = isset($input['role_name']) ? trim($input['role_name']) : '';

$db = getDbConnection();

// Build dynamic update query
$updateFields = [];
$types = "";
$params = [];

if (!empty($name)) {
    $updateFields[] = "name = ?";
    $types .= "s";
    $params[] = $name;
}
if (!empty($address)) {
    $updateFields[] = "address = ?";
    $types .= "s";
    $params[] = $address;
}
if (!empty($phone)) {
    $updateFields[] = "phone = ?";
    $types .= "s";
    $params[] = $phone;
}
if ($age !== null) {
    $updateFields[] = "age = ?";
    $types .= "i";
    $params[] = $age;
}
if (!empty($gender)) {
    $updateFields[] = "gender = ?";
    $types .= "s";
    $params[] = $gender;
}
// Only admin can change roles
if (!empty($role_name) && $loggedInUserRole === 'admin') {
    $updateFields[] = "role_name = ?";
    $types .= "s";
    $params[] = $role_name;
}

if (empty($updateFields)) {
    $db->close();
    sendJson(400, false, 'No fields provided for update');
}

$sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
$types .= "i";
$params[] = $targetUserId;

$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $stmt->close();
    $db->close();
    sendJson(200, true, 'User profile updated successfully', ['user_id' => $targetUserId]);
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to update user profile');
}
