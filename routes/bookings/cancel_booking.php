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

$bookingId = isset($input['booking_id']) ? (int)$input['booking_id'] : 0;

if ($bookingId <= 0) {
    sendJson(400, false, 'Booking ID is required');
}

$db = getDbConnection();

// Fetch booking to verify ownership and get refund amount
$stmt = $db->prepare("
    SELECT b.*, e.booking_price, e.name AS equipment_name 
    FROM bookings b 
    JOIN equipment e ON b.equipment_id = e.id 
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    sendJson(404, false, 'Booking not found or access denied');
}

if ($booking['status'] === 'cancelled') {
    sendJson(400, false, 'Booking is already cancelled');
}

// Check if booking can be cancelled (e.g., if it hasn't started yet)
$now = date('Y-m-d H:i:s');
$isRefundable = ($booking['start_time'] > $now);

$db->begin_transaction();

try {
    // Update booking status
    $stmtUpdate = $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $stmtUpdate->bind_param("i", $bookingId);
    $stmtUpdate->execute();

    if ($isRefundable) {
        $refundAmount = (float)$booking['booking_price'];

        $stmtBalance = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmtBalance->bind_param("di", $refundAmount, $userId);
        $stmtBalance->execute();

        $transactionType = "Refund: Cancellation of " . $booking['equipment_name'];
        $stmtTrans = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount) VALUES (?, ?, ?)");
        $stmtTrans->bind_param("isd", $userId, $transactionType, $refundAmount);
        $stmtTrans->execute();
    }

    // Release equipment if no other future confirmed bookings remain
    $equipmentId = (int)$booking['equipment_id'];
    $stmtCheck = $db->prepare("SELECT COUNT(*) FROM bookings WHERE equipment_id = ? AND status = 'confirmed' AND end_time > NOW() AND id != ?");
    $stmtCheck->bind_param("ii", $equipmentId, $bookingId);
    $stmtCheck->execute();
    $remaining = $stmtCheck->get_result()->fetch_row()[0];
    if ($remaining == 0) {
        $stmtRelease = $db->prepare("UPDATE equipment SET status = 'available' WHERE id = ?");
        $stmtRelease->bind_param("i", $equipmentId);
        $stmtRelease->execute();
    }

    $db->commit();
    
    $message = $isRefundable ? 'Booking cancelled and amount refunded' : 'Booking cancelled (no refund as session already started or passed)';
    sendJson(200, true, $message);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Failed to cancel booking: ' . $e->getMessage());
} finally {
    $db->close();
}
