<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$userId = $_SESSION['user_id'];
$db = getDbConnection();

// 1. Fetch the latest training plan for the user
$queryPlan = "
    SELECT 
        tp.id, 
        tp.trainer_id, 
        u.name AS trainer_name, 
        tp.goal, 
        tp.description 
    FROM training_plans tp
    JOIN users u ON tp.trainer_id = u.id
    WHERE tp.user_id = ?
    ORDER BY tp.id DESC
    LIMIT 1
";

$stmt = $db->prepare($queryPlan);
$stmt->bind_param("i", $userId);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$plan) {
    $db->close();
    sendJson(404, false, 'No training plan found for this user');
}

// 2. Fetch workout exercises for this plan
$queryExercises = "
    SELECT 
        we.id AS workout_exercise_id,
        we.day_number, 
        we.sort_order,
        we.sets,
        we.reps,
        we.rest_time,
        e.id AS exercise_id,
        e.name AS exercise_name, 
        e.description AS exercise_description, 
        e.muscle_name, 
        eq.name AS equipment_name
    FROM workout_exercises we
    JOIN exercises e ON we.exercise_id = e.id
    LEFT JOIN equipment eq ON e.equipment_id = eq.id
    WHERE we.training_plan_id = ?
    ORDER BY we.day_number ASC, we.sort_order ASC
";

$stmtEx = $db->prepare($queryExercises);
$stmtEx->bind_param("i", $plan['id']);
$stmtEx->execute();
$exResult = $stmtEx->get_result();

$exercises = [];
while ($row = $exResult->fetch_assoc()) {
    $exercises[] = $row;
}

$stmtEx->close();
$db->close();

sendJson(200, true, 'Training plan and exercises retrieved successfully', [
    'plan' => $plan,
    'exercises' => $exercises
]);
