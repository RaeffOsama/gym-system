<?php

require_once __DIR__ . '/../../config/database.php';

$db = getDbConnection();

// Query to get all equipment
$stmt = $db->prepare("SELECT id, name, description, booking_price, status FROM equipment");
$stmt->execute();

$result = $stmt->get_result();

$items = [];

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

$stmt->close();
$db->close();

sendJson(200, true, 'Equipment retrieved successfully', [
    'equipment' => $items
]);
