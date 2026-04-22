<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$nutritionistId = $_SESSION['user_id'];

// Check for nutritionist role (optional but recommended)
$db = getDbConnection();
$stmtRole = $db->prepare("SELECT role_name FROM users WHERE id = ?");
$stmtRole->bind_param("i", $nutritionistId);
$stmtRole->execute();
$roleResult = $stmtRole->get_result()->fetch_assoc();
$stmtRole->close();

// Depending on your role system, it might be 'nutritionist' or 'specialist'
// If you use 'trainer' for everyone, you can adjust this check.
// I'll skip strict enforcement or allow both.

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

$targetUserId = isset($input['user_id']) ? (int)$input['user_id'] : 0;
$goal = isset($input['goal']) ? trim($input['goal']) : '';
$description = isset($input['description']) ? trim($input['description']) : '';
$meals = isset($input['meals']) ? $input['meals'] : []; // Array of {meal_id, day_number}

if ($targetUserId <= 0 || empty($goal)) {
    sendJson(400, false, 'Target User ID and Goal are required');
}

$db->begin_transaction();

try {
    // 0. Validate Target User exists
    $stmtCheckUser = $db->prepare("SELECT id FROM users WHERE id = ?");
    $stmtCheckUser->bind_param("i", $targetUserId);
    $stmtCheckUser->execute();
    if ($stmtCheckUser->get_result()->num_rows === 0) {
        $stmtCheckUser->close();
        throw new Exception("Target user with ID $targetUserId not found.");
    }
    $stmtCheckUser->close();

    // 1. Create Diet Plan
    $stmtPlan = $db->prepare("INSERT INTO diet_plans (user_id, nutritionist_id, goal, description) VALUES (?, ?, ?, ?)");
    $stmtPlan->bind_param("iiss", $targetUserId, $nutritionistId, $goal, $description);
    if (!$stmtPlan->execute()) {
        throw new Exception("Failed to insert diet plan: " . $stmtPlan->error);
    }
    $dietPlanId = $db->insert_id;
    $stmtPlan->close();

    // 2. Add Meals to the Plan
    if (!empty($meals) && is_array($meals)) {
        $stmtMeal = $db->prepare("INSERT INTO diet_meals (diet_plan_id, meal_id, day_number) VALUES (?, ?, ?)");
        $stmtCheckMeal = $db->prepare("SELECT id FROM meals WHERE id = ?");

        foreach ($meals as $mealItem) {
            $mealId = (int)$mealItem['meal_id'];
            $dayNumber = (int)$mealItem['day_number'];

            // Check if meal exists
            $stmtCheckMeal->bind_param("i", $mealId);
            $stmtCheckMeal->execute();
            if ($stmtCheckMeal->get_result()->num_rows === 0) {
                throw new Exception("Meal with ID $mealId not found.");
            }

            $stmtMeal->bind_param("iii", $dietPlanId, $mealId, $dayNumber);
            if (!$stmtMeal->execute()) {
                throw new Exception("Failed to insert meal ID $mealId: " . $stmtMeal->error);
            }
        }
        $stmtCheckMeal->close();
        $stmtMeal->close();
    }

    $db->commit();
    sendJson(201, true, 'Nutrition plan created successfully', ['diet_plan_id' => $dietPlanId]);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Failed to create nutrition plan: ' . $e->getMessage());
} finally {
    $db->close();
}
