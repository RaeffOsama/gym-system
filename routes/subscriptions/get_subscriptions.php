<?php

require_once __DIR__ . '/../../config/database.php';

$db = getDbConnection();

// Query to get all subscription plans
$stmt = $db->prepare("SELECT id, name, description, plan_type, price FROM subscription_plans");
$stmt->execute();

$result = $stmt->get_result();

$plans = [];

while ($row = $result->fetch_assoc()) {
    $plans[] = $row;
}

$stmt->close();
$db->close();

sendJson(200, true, 'Subscription plans retrieved successfully', [
    'subscriptions' => $plans
]);