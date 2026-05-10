<?php
// Nutritionist adds meals to an existing diet plan.
// Sets plan status → 'Active' once meals are added.

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

$userId   = (int)$_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

if (!in_array($userRole, ['nutritionist', 'admin'])) {
    sendJson(403, false, 'Forbidden: Nutritionists and admins only');
}

$input = json_decode(file_get_contents('php://input'), true);

$dietPlanId = isset($input['diet_plan_id']) ? (int)$input['diet_plan_id'] : 0;
$meals      = isset($input['meals']) && is_array($input['meals']) ? $input['meals'] : [];

if ($dietPlanId <= 0 || empty($meals)) {
    sendJson(400, false, 'diet_plan_id and a non-empty meals array are required');
}

$db = getDbConnection();

// Fetch the diet plan and verify ownership for nutritionist
$stmt = $db->prepare("SELECT id, nutritionist_id FROM diet_plans WHERE id = ?");
$stmt->bind_param("i", $dietPlanId);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$plan) {
    $db->close();
    sendJson(404, false, 'Diet plan not found');
}

if ($userRole === 'nutritionist' && (int)$plan['nutritionist_id'] !== $userId) {
    $db->close();
    sendJson(403, false, 'Forbidden: This plan is not assigned to you');
}

$db->begin_transaction();

try {
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

    // Mark plan Active
    $activeStatus = 'Active';
    $stmtStatus = $db->prepare("UPDATE diet_plans SET status = ? WHERE id = ?");
    $stmtStatus->bind_param("si", $activeStatus, $dietPlanId);
    $stmtStatus->execute();
    $stmtStatus->close();

    $db->commit();
    sendJson(200, true, 'Meals added and plan is now Active', [
        'diet_plan_id' => $dietPlanId,
        'meals_added'  => count($meals),
        'status'       => 'Active',
    ]);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Failed to add meals: ' . $e->getMessage());
} finally {
    $db->close();
}
