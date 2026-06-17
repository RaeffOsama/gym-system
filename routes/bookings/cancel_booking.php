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

// Check if booking can be cancelled (e.g., if it hasn't started yet)
$now = date('Y-m-d H:i:s');
$isRefundable = ($booking['start_time'] > $now);

$db->begin_transaction();

try {
    if ($isRefundable) {
        $refundAmount = (float)$booking['booking_price'];

        $stmtBalance = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmtBalance->bind_param("di", $refundAmount, $userId);
        $stmtBalance->execute();
        $stmtBalance->close();

        $transactionType = "Refund: Cancellation of " . $booking['equipment_name'];
        $stmtTrans = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount) VALUES (?, ?, ?)");
        $stmtTrans->bind_param("isd", $userId, $transactionType, $refundAmount);
        $stmtTrans->execute();
        $stmtTrans->close();
    }

    $stmtDelete = $db->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
    $stmtDelete->bind_param("ii", $bookingId, $userId);
    $stmtDelete->execute();
    if ($stmtDelete->affected_rows === 0) {
        throw new Exception('Booking could not be deleted');
    }
    $stmtDelete->close();

    // Release equipment when no other active confirmed bookings remain (same rule as GET /api/equipment)
    $equipmentId = (int)$booking['equipment_id'];
    $stmtRelease = $db->prepare("
        UPDATE equipment e
        SET e.status = 'available'
        WHERE e.id = ?
          AND NOT EXISTS (
            SELECT 1 FROM bookings b
            WHERE b.equipment_id = e.id
              AND b.status = 'confirmed'
              AND b.end_time > NOW()
          )
    ");
    $stmtRelease->bind_param("i", $equipmentId);
    $stmtRelease->execute();
    $equipmentReleased = $stmtRelease->affected_rows > 0;
    $stmtRelease->close();

    $db->commit();

    $message = $isRefundable ? 'Booking cancelled and amount refunded' : 'Booking cancelled (no refund as session already started or passed)';
    sendJson(200, true, $message, [
        'equipment_id' => $equipmentId,
        'equipment_status' => $equipmentReleased ? 'available' : 'unavailable',
    ]);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Failed to cancel booking: ' . $e->getMessage());
} finally {
    $db->close();
}
