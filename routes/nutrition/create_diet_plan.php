<?php
// Admin shortcut: create a complete diet plan (with optional meals) directly for a user.

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}
if ($_SESSION['user_role'] !== 'admin') {
    sendJson(403, false, 'Forbidden: Admins only');
}

$input = json_decode(file_get_contents('php://input'), true);
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

$targetUserId    = isset($input['user_id'])       ? (int)$input['user_id']           : 0;
$nutritionistId  = isset($input['nutritionist_id']) ? (int)$input['nutritionist_id'] : null;
$goal            = isset($input['goal'])           ? trim($input['goal'])             : '';
$description     = isset($input['description'])    ? trim($input['description'])      : '';
$meals           = isset($input['meals']) && is_array($input['meals']) ? $input['meals'] : [];

if ($targetUserId <= 0 || empty($goal)) {
    sendJson(400, false, 'user_id and goal are required');
}

$db = getDbConnection();
$db->begin_transaction();

try {
    $stmtCheckUser = $db->prepare("SELECT id FROM users WHERE id = ?");
    $stmtCheckUser->bind_param("i", $targetUserId);
    $stmtCheckUser->execute();
    if ($stmtCheckUser->get_result()->num_rows === 0) {
        throw new Exception("User with ID $targetUserId not found.");
    }
    $stmtCheckUser->close();

    $planStatus = empty($meals) ? 'Pending Assign' : 'Active';
    if ($nutritionistId) $planStatus = empty($meals) ? 'Planning' : 'Active';

    $stmtPlan = $db->prepare("INSERT INTO diet_plans (user_id, nutritionist_id, goal, description, status) VALUES (?, ?, ?, ?, ?)");
    $stmtPlan->bind_param("iisss", $targetUserId, $nutritionistId, $goal, $description, $planStatus);
    if (!$stmtPlan->execute()) {
        throw new Exception("Failed to create diet plan: " . $stmtPlan->error);
    }
    $dietPlanId = $db->insert_id;
    $stmtPlan->close();

    if (!empty($meals)) {
        $stmtMeal      = $db->prepare("INSERT INTO diet_meals (diet_plan_id, meal_id, day_number) VALUES (?, ?, ?)");
        $stmtCheckMeal = $db->prepare("SELECT id FROM meals WHERE id = ?");
        foreach ($meals as $mealItem) {
            $mealId    = (int)$mealItem['meal_id'];
            $dayNumber = (int)$mealItem['day_number'];
            $stmtCheckMeal->bind_param("i", $mealId);
            $stmtCheckMeal->execute();
            if ($stmtCheckMeal->get_result()->num_rows === 0) {
                throw new Exception("Meal ID $mealId not found.");
            }
            $stmtMeal->bind_param("iii", $dietPlanId, $mealId, $dayNumber);
            if (!$stmtMeal->execute()) {
                throw new Exception("Failed to add meal ID $mealId.");
            }
        }
        $stmtCheckMeal->close();
        $stmtMeal->close();
    }

    $db->commit();
    sendJson(201, true, 'Diet plan created successfully', ['diet_plan_id' => $dietPlanId, 'status' => $planStatus]);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Failed to create diet plan: ' . $e->getMessage());
} finally {
    $db->close();
}
