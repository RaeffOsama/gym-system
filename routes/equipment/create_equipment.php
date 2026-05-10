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
$name = isset($input['name']) ? trim($input['name']) : '';
$description = isset($input['description']) ? trim($input['description']) : '';
$booking_price = isset($input['booking_price']) ? (float)$input['booking_price'] : 0.00;
$status = isset($input['status']) ? trim($input['status']) : 'available';

// Validate required fields
if (empty($name)) {
    sendJson(400, false, 'Equipment name is required');
}

$db = getDbConnection();

// Insert the equipment
$stmt = $db->prepare("INSERT INTO equipment (name, description, booking_price, status) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssds", $name, $description, $booking_price, $status);

if ($stmt->execute()) {
    $equipmentId = $stmt->insert_id;
    $stmt->close();
    $db->close();
    
    sendJson(201, true, 'Equipment added successfully', ['equipment_id' => $equipmentId]);
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to add equipment');
}
