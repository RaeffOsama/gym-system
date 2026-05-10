<?php

require_once __DIR__ . '/../../config/database.php';

$db = getDbConnection();

// Auto-release equipment whose last confirmed booking has ended
$db->query("
    UPDATE equipment e
    SET e.status = 'available'
    WHERE e.status = 'unavailable'
      AND NOT EXISTS (
        SELECT 1 FROM bookings b
        WHERE b.equipment_id = e.id
          AND b.status = 'confirmed'
          AND b.end_time > NOW()
      )
");

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
