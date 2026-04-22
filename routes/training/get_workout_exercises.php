<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$planId = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;

if ($planId <= 0) {
    sendJson(400, false, 'Training Plan ID is required (e.g., /api/training/workout-exercises?plan_id=1)');
}

$db = getDbConnection();

// Verify the training plan exists (Optional but good for errors)
$checkPlan = $db->prepare("SELECT id FROM training_plans WHERE id = ?");
$checkPlan->bind_param("i", $planId);
$checkPlan->execute();
if (!$checkPlan->get_result()->fetch_assoc()) {
    $checkPlan->close();
    $db->close();
    sendJson(404, false, 'Training plan not found');
}
$checkPlan->close();

$queryWorkoutExercises = "
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

$stmt = $db->prepare($queryWorkoutExercises);
$stmt->bind_param("i", $planId);
$stmt->execute();
$result = $stmt->get_result();

$workoutExercises = [];
while ($row = $result->fetch_assoc()) {
    $workoutExercises[] = $row;
}

$stmt->close();
$db->close();

sendJson(200, true, 'Workout exercises retrieved successfully', $workoutExercises);
