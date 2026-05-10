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

if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

// Extract fields
$id = isset($input['id']) ? (int)$input['id'] : 0;
$name = isset($input['name']) ? trim($input['name']) : '';
$description = isset($input['description']) ? trim($input['description']) : '';
$muscle_name = isset($input['muscle_name']) ? trim($input['muscle_name']) : '';
$equipment_id = isset($input['equipment_id']) ? (int)$input['equipment_id'] : null;

if ($id <= 0) {
    sendJson(400, false, 'Valid Exercise ID is required');
}

$db = getDbConnection();

// Check if the exercise exists
$checkStmt = $db->prepare("SELECT id FROM exercises WHERE id = ?");
$checkStmt->bind_param("i", $id);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows === 0) {
    $checkStmt->close();
    $db->close();
    sendJson(404, false, 'Exercise not found');
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

if (!empty($muscle_name)) {
    $updateFields[] = "muscle_name = ?";
    $types .= "s";
    $params[] = $muscle_name;
}

if (isset($input['equipment_id'])) {
    // If equipment_id is provided, check if it exists (allows setting to null if needed, but table says INT)
    // Actually, checking existence if not null
    if ($equipment_id !== null) {
        $stmtEq = $db->prepare("SELECT id FROM equipment WHERE id = ?");
        $stmtEq->bind_param("i", $equipment_id);
        $stmtEq->execute();
        if ($stmtEq->get_result()->num_rows === 0) {
            $stmtEq->close();
            $db->close();
            sendJson(404, false, 'Equipment not found');
        }
        $stmtEq->close();
    }
    
    $updateFields[] = "equipment_id = ?";
    $types .= "i";
    $params[] = $equipment_id;
}

if (empty($updateFields)) {
    $db->close();
    sendJson(400, false, 'No fields to update');
}

$sql = "UPDATE exercises SET " . implode(", ", $updateFields) . " WHERE id = ?";
$types .= "i";
$params[] = $id;

$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $stmt->close();
    $db->close();
    sendJson(200, true, 'Exercise updated successfully');
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to update exercise');
}
