<?php

require_once __DIR__ . '/../../config/database.php';

// Read raw input
$rawInput = file_get_contents('php://input');

if (empty($rawInput)) {
    sendJson(400, false, 'No input data provided. Please send a JSON body.');
}

// Decode JSON input
$input = json_decode($rawInput, true);

if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

// Extract fields
$name = isset($input['name']) ? trim($input['name']) : '';
$description = isset($input['description']) ? trim($input['description']) : '';
$muscle_name = isset($input['muscle_name']) ? trim($input['muscle_name']) : '';
$equipment_id = isset($input['equipment_id']) ? (int)$input['equipment_id'] : null;

// Validate required fields
if (empty($name)) {
    sendJson(400, false, 'Exercise name is required');
}

$db = getDbConnection();

// If equipment_id is provided, check if it exists
if ($equipment_id !== null) {
    $stmtCheck = $db->prepare("SELECT id FROM equipment WHERE id = ?");
    $stmtCheck->bind_param("i", $equipment_id);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows === 0) {
        $stmtCheck->close();
        $db->close();
        sendJson(404, false, 'Equipment not found');
    }
    $stmtCheck->close();
}

// Insert the exercise
$stmt = $db->prepare("INSERT INTO exercises (name, description, muscle_name, equipment_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $name, $description, $muscle_name, $equipment_id);

if ($stmt->execute()) {
    $exerciseId = $stmt->insert_id;
    $stmt->close();
    $db->close();
    
    sendJson(201, true, 'Exercise added successfully', ['exercise_id' => $exerciseId]);
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to add exercise');
}
