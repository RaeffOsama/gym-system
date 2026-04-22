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

$sessionId = isset($input['session_id']) ? (int)$input['session_id'] : 0;

if ($sessionId <= 0) {
    sendJson(400, false, 'Session ID is required');
}

$db = getDbConnection();

// 1. Fetch session details and trainer name
$query = "
    SELECT ts.*, u.name as trainer_name 
    FROM trainer_sessions ts 
    JOIN users u ON ts.trainer_id = u.id 
    WHERE ts.id = ?
";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $sessionId);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$session) {
    sendJson(404, false, 'Session not found');
}

if ($session['status'] !== 'available' || $session['user_id'] !== null) {
    sendJson(400, false, 'Session is already booked or not available');
}

// 2. Check user balance
$stmtUser = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$userProfile = $stmtUser->get_result()->fetch_assoc();
$stmtUser->close();

$price = (float)$session['price'];
if ($userProfile['balance'] < $price) {
    sendJson(400, false, 'Insufficient balance to book this session');
}

// 3. Process Booking (Transaction)
$db->begin_transaction();

try {
    // Deduct balance
    $newBalance = $userProfile['balance'] - $price;
    $stmtUpdateUser = $db->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmtUpdateUser->bind_param("di", $newBalance, $userId);
    $stmtUpdateUser->execute();

    // Create transaction record
    $transactionType = "Trainer Session: " . $session['trainer_name'];
    $stmtTrans = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount) VALUES (?, ?, ?)");
    $stmtTrans->bind_param("isd", $userId, $transactionType, $price);
    $stmtTrans->execute();

    // Update session status
    $stmtUpdateSession = $db->prepare("UPDATE trainer_sessions SET user_id = ?, status = 'booked' WHERE id = ?");
    $stmtUpdateSession->bind_param("ii", $userId, $sessionId);
    $stmtUpdateSession->execute();

    // Optionally create a training_plan entry if that's the system's way of tracking long-term relationships
    // $stmtPlan = $db->prepare("INSERT INTO training_plans (user_id, trainer_id, goal, description) VALUES (?, ?, ?, ?)");
    // ...

    $db->commit();
    sendJson(200, true, 'Session booked successfully', [
        'session_id' => $sessionId,
        'new_balance' => $newBalance
    ]);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Failed to book session: ' . $e->getMessage());
} finally {
    $db->close();
}
