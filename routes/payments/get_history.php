<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$userId = $_SESSION['user_id'];
$db = getDbConnection();

// Fetch transactions for the user
$stmt = $db->prepare("SELECT id, transaction_type, amount, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

$stmt->close();

// Also get current balance
$stmtBalance = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmtBalance->bind_param("i", $userId);
$stmtBalance->execute();
$balance = $stmtBalance->get_result()->fetch_assoc()['balance'];
$stmtBalance->close();

$db->close();

sendJson(200, true, 'Transaction history retrieved successfully', [
    'balance' => (float)$balance,
    'transactions' => $transactions
]);
