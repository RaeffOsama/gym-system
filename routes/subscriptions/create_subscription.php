<?php

require_once __DIR__ . '/../../config/database.php';

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendJson(400, false, 'Invalid JSON input');
}

// Extract fields
$name = isset($input['name']) ? trim($input['name']) : '';
$description = isset($input['description']) ? trim($input['description']) : '';
$plan_type = isset($input['plan_type']) ? trim($input['plan_type']) : '';
$price = isset($input['price']) ? (float)$input['price'] : 0.00;

// Validate required fields
if (empty($name) || empty($plan_type)) {
    sendJson(400, false, 'Name and plan type are required');
}

$db = getDbConnection();

// Insert the subscription plan
$stmt = $db->prepare("INSERT INTO subscription_plans (name, description, plan_type, price) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssd", $name, $description, $plan_type, $price);

if ($stmt->execute()) {
    $planId = $stmt->insert_id;
    $stmt->close();
    $db->close();
    
    sendJson(201, true, 'Subscription plan created successfully', ['plan_id' => $planId]);
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to create subscription plan');
}
