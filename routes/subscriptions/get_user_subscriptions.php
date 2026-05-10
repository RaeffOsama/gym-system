<?php
// Returns the authenticated user's subscription history with linked plan IDs.

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

$userId = $_SESSION['user_id'];
$db = getDbConnection();

$stmt = $db->prepare("
    SELECT
        us.id,
        us.status,
        us.purchase_date,
        us.start_date,
        us.end_date,
        sp.id   AS plan_id,
        sp.name AS plan_name,
        sp.description AS plan_description,
        sp.plan_type,
        sp.price,
        dp.id   AS diet_plan_id,
        dp.status AS diet_plan_status,
        tp.id   AS training_plan_id,
        tp.status AS training_plan_status
    FROM user_subscriptions us
    JOIN subscription_plans sp ON us.subscription_plan_id = sp.id
    LEFT JOIN diet_plans dp     ON dp.user_id = us.user_id AND (sp.plan_type = 'diet' OR sp.plan_type = 'both')
    LEFT JOIN training_plans tp ON tp.user_id = us.user_id AND (sp.plan_type = 'gym'  OR sp.plan_type = 'both')
    WHERE us.user_id = ?
    ORDER BY us.purchase_date DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$subscriptions = [];
while ($row = $result->fetch_assoc()) {
    $subscriptions[] = $row;
}
$stmt->close();
$db->close();

sendJson(200, true, 'Subscriptions retrieved successfully', ['subscriptions' => $subscriptions]);
