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
$booking_price = isset($input['booking_price']) ? (float)$input['booking_price'] : null;
$status = isset($input['status']) ? trim($input['status']) : '';

if ($id <= 0) {
    sendJson(400, false, 'Valid Equipment ID is required');
}

$db = getDbConnection();

// Check if the equipment exists
$checkStmt = $db->prepare("SELECT id FROM equipment WHERE id = ?");
$checkStmt->bind_param("i", $id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $checkStmt->close();
    $db->close();
    sendJson(404, false, 'Equipment not found');
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

if ($booking_price !== null) {
    $updateFields[] = "booking_price = ?";
    $types .= "d";
    $params[] = $booking_price;
}

if (!empty($status)) {
    $updateFields[] = "status = ?";
    $types .= "s";
    $params[] = $status;
}

if (empty($updateFields)) {
    $db->close();
    sendJson(400, false, 'No fields to update');
}

$sql = "UPDATE equipment SET " . implode(", ", $updateFields) . " WHERE id = ?";
$types .= "i";
$params[] = $id;

$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $stmt->close();
    $db->close();
    sendJson(200, true, 'Equipment updated successfully');
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to update equipment');
}
