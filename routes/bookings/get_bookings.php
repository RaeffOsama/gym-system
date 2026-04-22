<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$userId = $_SESSION['user_id'];
$db = getDbConnection();

// Fetch bookings with equipment details
$query = "
    SELECT 
        b.id, 
        b.equipment_id, 
        e.name AS equipment_name, 
        b.purchase_date, 
        b.start_time, 
        b.end_time, 
        b.status 
    FROM bookings b
    JOIN equipment e ON b.equipment_id = e.id
    WHERE b.user_id = ?
    ORDER BY b.start_time DESC
";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

$stmt->close();
$db->close();

sendJson(200, true, 'Bookings retrieved successfully', ['bookings' => $bookings]);
