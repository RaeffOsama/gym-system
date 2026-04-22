<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$userId = $_SESSION['user_id'];
$db = getDbConnection();

$now = date('Y-m-d H:i:s');

// Fetch the booking that is active right now
$query = "
    SELECT 
        b.id, 
        b.equipment_id, 
        e.name AS equipment_name, 
        b.start_time, 
        b.end_time, 
        b.status 
    FROM bookings b
    JOIN equipment e ON b.equipment_id = e.id
    WHERE b.user_id = ? 
    AND b.status = 'confirmed'
    AND ? >= b.start_time 
    AND ? <= b.end_time
    LIMIT 1
";

$stmt = $db->prepare($query);
$stmt->bind_param("iss", $userId, $now, $now);
$stmt->execute();
$result = $stmt->get_result();

if ($booking = $result->fetch_assoc()) {
    $stmt->close();
    $db->close();
    sendJson(200, true, 'Active booking found', ['booking' => $booking]);
} else {
    $stmt->close();
    $db->close();
    sendJson(404, false, 'No active booking found for current time');
}
