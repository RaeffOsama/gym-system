<?php
// User cancels their training plan: deletes plan (cascades exercises), cancels gym subscription, refunds if still Planning/Pending Assign.

require_once __DIR__ . '/../../config/database.php';

$bookingPrice = 10.00;

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

$userId   = (int)$_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? '';

$input = json_decode(file_get_contents('php://input'), true);
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

$trainingPlanId = isset($input['training_plan_id']) ? (int)$input['training_plan_id'] : 0;

if ($trainingPlanId <= 0) {
    sendJson(400, false, 'training_plan_id is required');
}

$db = getDbConnection();

$stmt = $db->prepare("
    SELECT tp.id, tp.user_id, tp.trainer_id, tp.status, t.name AS trainer_name
    FROM training_plans tp
    LEFT JOIN users t ON t.id = tp.trainer_id
    WHERE tp.id = ?
");
$stmt->bind_param("i", $trainingPlanId);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$plan) {
    $db->close();
    sendJson(404, false, 'Training plan not found');
}

if ($userRole !== 'admin' && (int)$plan['user_id'] !== $userId) {
    $db->close();
    sendJson(403, false, 'Forbidden: This plan does not belong to you');
}

$planOwnerId  = (int)$plan['user_id'];
$isRefundable = in_array($plan['status'], ['Pending Assign', 'Planning'], true);

$db->begin_transaction();

try {
    if ($isRefundable) {
        $stmtBalance = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmtBalance->bind_param("di", $bookingPrice, $planOwnerId);
        $stmtBalance->execute();
        $stmtBalance->close();

        $trainerLabel = $plan['trainer_name'] ?: 'trainer';
        $transactionType = 'Refund: Cancellation of training plan with ' . $trainerLabel;
        $stmtTrans = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount) VALUES (?, ?, ?)");
        $stmtTrans->bind_param("isd", $planOwnerId, $transactionType, $bookingPrice);
        $stmtTrans->execute();
        $stmtTrans->close();
    }

    $stmtSub = $db->prepare("
        SELECT us.id FROM user_subscriptions us
        JOIN subscription_plans sp ON sp.id = us.subscription_plan_id
        WHERE us.user_id = ? AND sp.plan_type = 'gym' AND us.status = 'active'
        ORDER BY us.id DESC
        LIMIT 1
    ");
    $stmtSub->bind_param("i", $planOwnerId);
    $stmtSub->execute();
    $sub = $stmtSub->get_result()->fetch_assoc();
    $stmtSub->close();

    if ($sub) {
        $cancelledStatus = 'cancelled';
        $stmtSubUpdate = $db->prepare("UPDATE user_subscriptions SET status = ? WHERE id = ?");
        $stmtSubUpdate->bind_param("si", $cancelledStatus, $sub['id']);
        $stmtSubUpdate->execute();
        $stmtSubUpdate->close();
    }

    $stmtDelete = $db->prepare("DELETE FROM training_plans WHERE id = ?");
    $stmtDelete->bind_param("i", $trainingPlanId);
    $stmtDelete->execute();
    if ($stmtDelete->affected_rows === 0) {
        throw new Exception('Training plan could not be deleted');
    }
    $stmtDelete->close();

    $db->commit();

    $message = $isRefundable
        ? 'Training plan cancelled, specialist unassigned, and booking fee refunded'
        : 'Training plan cancelled and specialist unassigned (no refund — plan was already Active)';

    sendJson(200, true, $message, [
        'training_plan_id' => $trainingPlanId,
        'refunded'         => $isRefundable,
        'refund_amount'    => $isRefundable ? $bookingPrice : 0,
    ]);
} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Failed to cancel training plan: ' . $e->getMessage());
} finally {
    $db->close();
}
