<?php
// User selects a trainer directly: charges fixed fee, creates subscription + training plan (status Planning).

require_once __DIR__ . '/../../config/database.php';

$bookingPrice = 10.00;

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

$userId = (int)$_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

$trainerId   = isset($input['trainer_id']) ? (int)$input['trainer_id'] : 0;
$goal        = isset($input['goal']) ? trim($input['goal']) : '';
$description = isset($input['description']) ? trim($input['description']) : '';

if ($trainerId <= 0) {
    sendJson(400, false, 'trainer_id is required');
}

if ($trainerId === $userId) {
    sendJson(400, false, 'You cannot book yourself as a trainer');
}

$db = getDbConnection();

$stmt = $db->prepare("SELECT id, name, role_name FROM users WHERE id = ?");
$stmt->bind_param("i", $trainerId);
$stmt->execute();
$trainer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$trainer || $trainer['role_name'] !== 'trainer') {
    $db->close();
    sendJson(404, false, 'Trainer not found');
}

// Block only if gym plan is already being built or active (diet plan is separate)
$stmt = $db->prepare("
    SELECT id, status, trainer_id
    FROM training_plans
    WHERE user_id = ? AND status IN ('Planning', 'Active')
    ORDER BY id DESC
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$activePlan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($activePlan) {
    $db->close();
    sendJson(409, false, 'You already have an active or in-progress training plan', [
        'existing_training_plan_id' => (int)$activePlan['id'],
        'status'                    => $activePlan['status'],
        'trainer_id'                => $activePlan['trainer_id'] ? (int)$activePlan['trainer_id'] : null,
    ]);
}

$stmt = $db->prepare("
    SELECT id, trainer_id
    FROM training_plans
    WHERE user_id = ? AND status = 'Pending Assign'
    ORDER BY id DESC
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$pendingPlan = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $db->prepare("SELECT id FROM subscription_plans WHERE plan_type = 'gym' ORDER BY id ASC LIMIT 1");
$stmt->execute();
$subPlan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$subPlan) {
    $db->close();
    sendJson(500, false, 'Gym subscription plan is not configured');
}

$planId = (int)$subPlan['id'];
$price  = $bookingPrice;

$stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userBalance = (float)$stmt->get_result()->fetch_row()[0];
$stmt->close();

if ($userBalance < $price) {
    $db->close();
    sendJson(400, false, 'Insufficient balance. Required: ' . number_format($price, 2) . ', current: ' . number_format($userBalance, 2));
}

$db->begin_transaction();

try {
    $stmtUpdate = $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $stmtUpdate->bind_param("di", $price, $userId);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    $transactionType = 'Trainer Plan Booking: ' . $trainer['name'];
    $stmtTrans = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount) VALUES (?, ?, ?)");
    $stmtTrans->bind_param("isd", $userId, $transactionType, $price);
    $stmtTrans->execute();
    $stmtTrans->close();

    $userSubId = null;
    $stmtSubCheck = $db->prepare("
        SELECT us.id FROM user_subscriptions us
        JOIN subscription_plans sp ON sp.id = us.subscription_plan_id
        WHERE us.user_id = ? AND sp.plan_type = 'gym' AND us.status = 'active' AND us.end_date >= CURDATE()
        LIMIT 1
    ");
    $stmtSubCheck->bind_param("i", $userId);
    $stmtSubCheck->execute();
    $existingSub = $stmtSubCheck->get_result()->fetch_assoc();
    $stmtSubCheck->close();

    if (!$existingSub) {
        $startDate = date('Y-m-d');
        $endDate   = date('Y-m-d', strtotime('+1 month'));
        $subStatus = 'active';
        $stmtSub = $db->prepare("INSERT INTO user_subscriptions (user_id, subscription_plan_id, purchase_date, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtSub->bind_param("iissss", $userId, $planId, $startDate, $startDate, $endDate, $subStatus);
        $stmtSub->execute();
        $userSubId = $db->insert_id;
        $stmtSub->close();
    } else {
        $userSubId = (int)$existingSub['id'];
    }

    $planStatus = 'Planning';

    if ($pendingPlan) {
        $trainingPlanId = (int)$pendingPlan['id'];
        $stmtTrain = $db->prepare("
            UPDATE training_plans
            SET trainer_id = ?, goal = ?, description = ?, status = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmtTrain->bind_param("isssii", $trainerId, $goal, $description, $planStatus, $trainingPlanId, $userId);
        $stmtTrain->execute();
        $stmtTrain->close();
    } else {
        $stmtTrain = $db->prepare("INSERT INTO training_plans (user_id, trainer_id, goal, description, status) VALUES (?, ?, ?, ?, ?)");
        $stmtTrain->bind_param("iisss", $userId, $trainerId, $goal, $description, $planStatus);
        $stmtTrain->execute();
        $trainingPlanId = $db->insert_id;
        $stmtTrain->close();
    }

    $db->commit();

    sendJson(201, true, 'Trainer booked successfully', [
        'subscription_id'  => $userSubId,
        'training_plan_id' => $trainingPlanId,
        'trainer_id'       => $trainerId,
        'trainer_name'     => $trainer['name'],
        'plan_type'        => 'gym',
        'status'           => $planStatus,
        'amount_charged'   => $price,
        'new_balance'      => round($userBalance - $price, 2),
    ]);
} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Booking failed: ' . $e->getMessage());
} finally {
    $db->close();
}
