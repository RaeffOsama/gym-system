<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$userId = $_SESSION['user_id'];

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['amount']) || (float)$input['amount'] <= 0) {
    sendJson(400, false, 'Invalid amount provided');
}

$amount = (float)$input['amount'];
$db = getDbConnection();

// Start transaction to update balance and log transaction
$db->begin_transaction();

try {
    // 1. Update user balance
    $stmtUpdate = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmtUpdate->bind_param("di", $amount, $userId);
    $stmtUpdate->execute();

    // 2. Record transaction
    $transactionType = "Deposit / Top-up";
    $stmtTrans = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount) VALUES (?, ?, ?)");
    $stmtTrans->bind_param("isd", $userId, $transactionType, $amount);
    $stmtTrans->execute();

    $db->commit();

    // Get new balance for response
    $stmtBalance = $db->prepare("SELECT balance FROM users WHERE id = ?");
    $stmtBalance->bind_param("i", $userId);
    $stmtBalance->execute();
    $newBalance = $stmtBalance->get_result()->fetch_assoc()['balance'];

    sendJson(200, true, 'Payment successful. Balance updated.', [
        'deposited_amount' => $amount,
        'new_balance' => (float)$newBalance
    ]);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Payment failed: ' . $e->getMessage());
} finally {
    $db->close();
}
