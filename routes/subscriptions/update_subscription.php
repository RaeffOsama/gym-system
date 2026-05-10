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

// Extract fields
$id = isset($input['id']) ? (int)$input['id'] : 0;
$name = isset($input['name']) ? trim($input['name']) : '';
$description = isset($input['description']) ? trim($input['description']) : '';
$plan_type = isset($input['plan_type']) ? trim($input['plan_type']) : '';
$price = isset($input['price']) ? (float)$input['price'] : null;

if ($id <= 0) {
    sendJson(400, false, 'Valid Plan ID is required');
}

$db = getDbConnection();

// Check if the plan exists
$checkStmt = $db->prepare("SELECT id FROM subscription_plans WHERE id = ?");
$checkStmt->bind_param("i", $id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $checkStmt->close();
    $db->close();
    sendJson(404, false, 'Subscription plan not found');
}
$checkStmt->close();

// Build dynamic update query
$updateFields = [];
$types = "";
$params = [];

if (!empty($name)) {
    $updateFields[] = "name = ?";
    $types .= "s";
    $params[] = $name;
}

if (!empty($description)) {
    $updateFields[] = "description = ?";
    $types .= "s";
    $params[] = $description;
}

if (!empty($plan_type)) {
    $updateFields[] = "plan_type = ?";
    $types .= "s";
    $params[] = $plan_type;
}

if ($price !== null) {
    $updateFields[] = "price = ?";
    $types .= "d";
    $params[] = $price;
}

if (empty($updateFields)) {
    $db->close();
    sendJson(400, false, 'No fields to update');
}

$sql = "UPDATE subscription_plans SET " . implode(", ", $updateFields) . " WHERE id = ?";
$types .= "i";
$params[] = $id;

$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $stmt->close();
    $db->close();
    sendJson(200, true, 'Subscription plan updated successfully');
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to update subscription plan');
}
