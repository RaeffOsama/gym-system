<?php

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

$userId = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['plan_id'])) {
    sendJson(400, false, 'Subscription Plan ID is required');
}

$planId = (int)$input['plan_id'];
$goal        = isset($input['goal'])        ? trim($input['goal'])        : '';
$description = isset($input['description']) ? trim($input['description']) : '';

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

$price    = (float)$plan['price'];
$planType = strtolower($plan['plan_type']); // 'diet', 'gym', 'both'

// 2. Check user balance
$stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userBalance = (float)$stmt->get_result()->fetch_row()[0];
$stmt->close();

if ($userBalance < $price) {
    sendJson(400, false, 'Insufficient balance. Your current balance is ' . number_format($userBalance, 2));
}

$db->begin_transaction();

try {
    // Deduct balance
    $stmtUpdate = $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $stmtUpdate->bind_param("di", $price, $userId);
    $stmtUpdate->execute();

    // Record transaction
    $transactionType = "Purchase Subscription: " . $plan['name'];
    $stmtTrans = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount) VALUES (?, ?, ?)");
    $stmtTrans->bind_param("isd", $userId, $transactionType, $price);
    $stmtTrans->execute();

    // Create user_subscriptions record
    $startDate = date('Y-m-d');
    $endDate   = date('Y-m-d', strtotime('+1 month'));
    $subStatus = 'active';
    $stmtSub = $db->prepare("INSERT INTO user_subscriptions (user_id, subscription_plan_id, purchase_date, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtSub->bind_param("iissss", $userId, $planId, $startDate, $startDate, $endDate, $subStatus);
    $stmtSub->execute();
    $userSubId = $db->insert_id;

    $dietPlanId     = null;
    $trainingPlanId = null;
    $planStatus     = 'Pending Assign';

    // Auto-create plan entries
    if ($planType === 'diet' || $planType === 'both') {
        $stmtDiet = $db->prepare("INSERT INTO diet_plans (user_id, goal, description, status) VALUES (?, ?, ?, ?)");
        $stmtDiet->bind_param("isss", $userId, $goal, $description, $planStatus);
        $stmtDiet->execute();
        $dietPlanId = $db->insert_id;
        $stmtDiet->close();
    }

    if ($planType === 'gym' || $planType === 'both') {
        $stmtTrain = $db->prepare("INSERT INTO training_plans (user_id, goal, description, status) VALUES (?, ?, ?, ?)");
        $stmtTrain->bind_param("isss", $userId, $goal, $description, $planStatus);
        $stmtTrain->execute();
        $trainingPlanId = $db->insert_id;
        $stmtTrain->close();
    }

    $db->commit();

    $responseData = [
        'subscription_id' => $userSubId,
        'new_balance'     => round($userBalance - $price, 2),
        'plan_type'       => $planType,
        'status'          => 'Pending Assign',
    ];
    if ($dietPlanId)     $responseData['diet_plan_id']     = $dietPlanId;
    if ($trainingPlanId) $responseData['training_plan_id'] = $trainingPlanId;

    sendJson(201, true, 'Subscription purchased successfully', $responseData);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Transaction failed: ' . $e->getMessage());
} finally {
    $db->close();
}
