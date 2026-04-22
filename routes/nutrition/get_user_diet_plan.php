<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$userId = $_SESSION['user_id'];
$db = getDbConnection();

// 1. Fetch the diet plan
$queryPlan = "
    SELECT 
        dp.id, 
        dp.nutritionist_id, 
        u.name AS nutritionist_name, 
        dp.goal, 
        dp.description 
    FROM diet_plans dp
    JOIN users u ON dp.nutritionist_id = u.id
    WHERE dp.user_id = ?
    ORDER BY dp.id DESC
    LIMIT 1
";

$stmt = $db->prepare($queryPlan);
$stmt->bind_param("i", $userId);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$plan) {
    $db->close();
    sendJson(404, false, 'No nutrition plan found for this user');
}

// 2. Fetch meals for this plan
$queryMeals = "
    SELECT 
        dm.day_number, 
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

$stmtMeals = $db->prepare($queryMeals);
$stmtMeals->bind_param("i", $plan['id']);
$stmtMeals->execute();
$mealsResult = $stmtMeals->get_result();

$meals = [];
while ($row = $mealsResult->fetch_assoc()) {
    $meals[] = $row;
}

$stmtMeals->close();
$db->close();

sendJson(200, true, 'Nutrition plan retrieved successfully', [
    'plan' => $plan,
    'meals' => $meals
]);
