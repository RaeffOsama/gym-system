<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$userId = $_SESSION['user_id'];

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['plan_id'])) {
    sendJson(400, false, 'Subscription Plan ID is required');
}

$planId = (int)$input['plan_id'];
$db = getDbConnection();

// 1. Get plan details
$stmt = $db->prepare("SELECT name, price, plan_type FROM subscription_plans WHERE id = ?");
$stmt->bind_param("i", $planId);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$plan) {
    sendJson(404, false, 'Subscription plan not found');
}

$price = (float)$plan['price'];

// 2. Check user balance
$stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userBalance = $stmt->get_result()->fetch_row()[0];
$stmt->close();

if ($userBalance < $price) {
    sendJson(400, false, 'Insufficient balance. Your current balance is ' . number_format($userBalance, 2));
}

// 3. Process Payment and Subscription
$db->begin_transaction();

try {
    // Deduct balance
    $stmtUpdate = $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $stmtUpdate->bind_param("di", $price, $userId);
    $stmtUpdate->execute();

    // Record Transaction
    $transactionType = "Purchase Subscription: " . $plan['name'];
    $stmtTrans = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount) VALUES (?, ?, ?)");
    $stmtTrans->bind_param("isd", $userId, $transactionType, $price);
    $stmtTrans->execute();

    // Create User Subscription
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime('+1 month')); // Default to 1 month, could be smarter based on plan_type
    $status = 'active';
    
    $stmtSub = $db->prepare("INSERT INTO user_subscriptions (user_id, subscription_plan_id, purchase_date, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtSub->bind_param("iissss", $userId, $planId, $startDate, $startDate, $endDate, $status);
    $stmtSub->execute();
    
    $userSubId = $db->insert_id;

    $db->commit();

    sendJson(201, true, 'Subscription purchased successfully!', [
        'subscription_id' => $userSubId,
        'new_balance' => (float)($userBalance - $price)
    ]);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Transaction failed: ' . $e->getMessage());
} finally {
    $db->close();
}
