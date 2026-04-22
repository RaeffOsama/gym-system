<?php

require_once __DIR__ . '/../../config/database.php';

$db = getDbConnection();

// Query to get all exercises with their associated equipment name
$query = "
    SELECT 
        e.id, 
        e.name, 
        e.description, 
        e.muscle_name, 
        e.equipment_id,
        eq.name as equipment_name
    FROM exercises e
    LEFT JOIN equipment eq ON e.equipment_id = eq.id
";

$stmt = $db->prepare($query);
$stmt->execute();

$result = $stmt->get_result();

$items = [];

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

$stmt->close();
$db->close();

sendJson(200, true, 'Exercises retrieved successfully', [
    'exercises' => $items
]);
