<?php
// Trainer adds workout exercises to an existing training plan.
// Sets plan status → 'Active' once exercises are added.

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

$userId   = (int)$_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

if (!in_array($userRole, ['trainer', 'admin'])) {
    sendJson(403, false, 'Forbidden: Trainers and admins only');
}

$input = json_decode(file_get_contents('php://input'), true);

$trainingPlanId = isset($input['training_plan_id']) ? (int)$input['training_plan_id'] : 0;
$exercises      = isset($input['exercises']) && is_array($input['exercises']) ? $input['exercises'] : [];

if ($trainingPlanId <= 0 || empty($exercises)) {
    sendJson(400, false, 'training_plan_id and a non-empty exercises array are required');
}

$db = getDbConnection();

// Fetch the training plan and verify ownership for trainer
$stmt = $db->prepare("SELECT id, trainer_id FROM training_plans WHERE id = ?");
$stmt->bind_param("i", $trainingPlanId);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$plan) {
    $db->close();
    sendJson(404, false, 'Training plan not found');
}

if ($userRole === 'trainer' && (int)$plan['trainer_id'] !== $userId) {
    $db->close();
    sendJson(403, false, 'Forbidden: This plan is not assigned to you');
}

$db->begin_transaction();

try {
    $stmtExercise      = $db->prepare("INSERT INTO workout_exercises (training_plan_id, exercise_id, day_number, sort_order, sets, reps, rest_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtCheckExercise = $db->prepare("SELECT id FROM exercises WHERE id = ?");

    foreach ($exercises as $item) {
        $exerciseId = (int)$item['exercise_id'];
        $dayNumber  = isset($item['day_number'])  ? (int)$item['day_number']  : 1;
        $sortOrder  = isset($item['sort_order'])  ? (int)$item['sort_order']  : 1;
        $sets       = isset($item['sets'])        ? (int)$item['sets']        : 3;
        $reps       = isset($item['reps'])        ? (int)$item['reps']        : 10;
        $restTime   = isset($item['rest_time'])   ? (int)$item['rest_time']   : 60;

        $stmtCheckExercise->bind_param("i", $exerciseId);
        $stmtCheckExercise->execute();
        if ($stmtCheckExercise->get_result()->num_rows === 0) {
            throw new Exception("Exercise ID $exerciseId not found.");
        }

        $stmtExercise->bind_param("iiiiiii", $trainingPlanId, $exerciseId, $dayNumber, $sortOrder, $sets, $reps, $restTime);
        if (!$stmtExercise->execute()) {
            throw new Exception("Failed to add exercise ID $exerciseId.");
        }
    }
    $stmtCheckExercise->close();
    $stmtExercise->close();

    // Mark plan Active
    $activeStatus = 'Active';
    $stmtStatus = $db->prepare("UPDATE training_plans SET status = ? WHERE id = ?");
    $stmtStatus->bind_param("si", $activeStatus, $trainingPlanId);
    $stmtStatus->execute();
    $stmtStatus->close();

    $db->commit();
    sendJson(200, true, 'Exercises added and plan is now Active', [
        'training_plan_id' => $trainingPlanId,
        'exercises_added'  => count($exercises),
        'status'           => 'Active',
    ]);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Failed to add exercises: ' . $e->getMessage());
} finally {
    $db->close();
}
