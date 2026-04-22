<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

// Optional: check for nutritionist or admin role
// ...

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

$name = isset($input['name']) ? trim($input['name']) : '';
$preparation_steps = isset($input['preparation_steps']) ? trim($input['preparation_steps']) : '';
$calories = isset($input['calories']) ? (int)$input['calories'] : 0;
$serving_size = isset($input['serving_size']) ? (int)$input['serving_size'] : 0;
$meal_type = isset($input['meal_type']) ? trim($input['meal_type']) : '';

if (empty($name)) {
    sendJson(400, false, 'Meal name is required');
}

$db = getDbConnection();

$stmt = $db->prepare("INSERT INTO meals (name, preparation_steps, calories, serving_size, meal_type) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssiis", $name, $preparation_steps, $calories, $serving_size, $meal_type);

if ($stmt->execute()) {
    $mealId = $stmt->insert_id;
    $stmt->close();
    $db->close();
    sendJson(201, true, 'Meal added successfully', ['meal_id' => $mealId]);
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to add meal');
}
