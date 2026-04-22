<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$planId = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;

if ($planId <= 0) {
    sendJson(400, false, 'Plan ID is required (e.g., /api/nutrition/diet-meals?plan_id=1)');
}

$db = getDbConnection();

// Verify the diet plan exists (Optional but good for errors)
$checkPlan = $db->prepare("SELECT id FROM diet_plans WHERE id = ?");
$checkPlan->bind_param("i", $planId);
$checkPlan->execute();
if (!$checkPlan->get_result()->fetch_assoc()) {
    $checkPlan->close();
    $db->close();
    sendJson(404, false, 'Diet plan not found');
}
$checkPlan->close();

$queryMeals = "
    SELECT 
        dm.id AS diet_meal_id,
        dm.day_number, 
        m.id AS meal_id,
        m.name AS meal_name, 
        m.calories, 
        m.serving_size, 
        m.meal_type, 
        m.preparation_steps
    FROM diet_meals dm
    JOIN meals m ON dm.meal_id = m.id
    WHERE dm.diet_plan_id = ?
    ORDER BY dm.day_number ASC
";

$stmt = $db->prepare($queryMeals);
$stmt->bind_param("i", $planId);
$stmt->execute();
$result = $stmt->get_result();

$dietMeals = [];
while ($row = $result->fetch_assoc()) {
    $dietMeals[] = $row;
}

$stmt->close();
$db->close();

sendJson(200, true, 'Diet meals retrieved successfully', $dietMeals);
