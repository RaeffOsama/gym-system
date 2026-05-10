<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$userId = $_SESSION['user_id'];

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendJson(400, false, 'Invalid JSON input');
}

// Extract and sanitize fields
$equipmentId = isset($input['equipment_id']) ? (int)$input['equipment_id'] : 0;
$startTime = isset($input['start_time']) ? trim($input['start_time']) : '';
$endTime = isset($input['end_time']) ? trim($input['end_time']) : '';

// Validate required fields
if ($equipmentId <= 0 || empty($startTime) || empty($endTime)) {
    sendJson(400, false, 'Equipment ID, start time, and end time are required');
}

$db = getDbConnection();

// 1. Check if equipment exists and get its price
$stmt = $db->prepare("SELECT name, booking_price, status FROM equipment WHERE id = ?");
$stmt->bind_param("i", $equipmentId);
$stmt->execute();
$result = $stmt->get_result();
$equipment = $result->fetch_assoc();

if (!$equipment) {
    $stmt->close();
    sendJson(404, false, 'Equipment not found');
}

if ($equipment['status'] !== 'available') {
    $stmt->close();
    sendJson(409, false, 'Equipment is currently unavailable for booking');
}

$price = (float)$equipment['booking_price'];

// 2. Check for overlapping bookings
$stmt = $db->prepare("SELECT id FROM bookings WHERE equipment_id = ? AND status != 'cancelled' AND (start_time < ? AND end_time > ?)");
$stmt->bind_param("iss", $equipmentId, $endTime, $startTime);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    sendJson(409, false, 'Equipment is already booked for the selected time range');
}
$stmt->close();

// 3. Check user balance
$stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userProfile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($userProfile['balance'] < $price) {
    sendJson(400, false, 'Insufficient balance. Please top up your account.');
}

// 4. Start transaction
$db->begin_transaction();

try {
    // Deduct balance
    $newBalance = $userProfile['balance'] - $price;
    $stmtUpdate = $db->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmtUpdate->bind_param("di", $newBalance, $userId);
    $stmtUpdate->execute();

    // Create transaction record
    $transactionType = "Equipment Booking: " . $equipment['name'];
    $stmtTrans = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount) VALUES (?, ?, ?)");
    $stmtTrans->bind_param("isd", $userId, $transactionType, $price);
    $stmtTrans->execute();

    // Create booking
    $purchaseDate = date('Y-m-d');
    $bookingStatus = 'confirmed';
    $stmtBooking = $db->prepare("INSERT INTO bookings (equipment_id, user_id, purchase_date, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtBooking->bind_param("iissss", $equipmentId, $userId, $purchaseDate, $startTime, $endTime, $bookingStatus);
    $stmtBooking->execute();
    $bookingId = $db->insert_id;

    // Mark equipment unavailable
    $stmtEquip = $db->prepare("UPDATE equipment SET status = 'unavailable' WHERE id = ?");
    $stmtEquip->bind_param("i", $equipmentId);
    $stmtEquip->execute();

    $db->commit();
    
    sendJson(201, true, 'Booking created successfully', [
        'booking_id' => $bookingId,
        'new_balance' => $newBalance
    ]);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Failed to create booking: ' . $e->getMessage());
} finally {
    $db->close();
}
