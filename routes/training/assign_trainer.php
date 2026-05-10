<?php
// Admin assigns a trainer to a training plan.
// Sets status → 'Planning'.

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}
if ($_SESSION['user_role'] !== 'admin') {
    sendJson(403, false, 'Forbidden: Admins only');
}

$input = json_decode(file_get_contents('php://input'), true);

$trainingPlanId = isset($input['training_plan_id']) ? (int)$input['training_plan_id'] : 0;
$trainerId      = isset($input['trainer_id'])       ? (int)$input['trainer_id']       : 0;

if ($trainingPlanId <= 0 || $trainerId <= 0) {
    sendJson(400, false, 'training_plan_id and trainer_id are required');
}

$db = getDbConnection();

// Verify training plan exists
$stmt = $db->prepare("SELECT id FROM training_plans WHERE id = ?");
$stmt->bind_param("i", $trainingPlanId);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $stmt->close(); $db->close();
    sendJson(404, false, 'Training plan not found');
}
$stmt->close();

// Verify the user is a trainer
$stmt = $db->prepare("SELECT role_name FROM users WHERE id = ?");
$stmt->bind_param("i", $trainerId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || $user['role_name'] !== 'trainer') {
    $db->close();
    sendJson(400, false, 'The specified user is not a trainer');
}

$newStatus = 'Planning';
$stmt = $db->prepare("UPDATE training_plans SET trainer_id = ?, status = ? WHERE id = ?");
$stmt->bind_param("isi", $trainerId, $newStatus, $trainingPlanId);

if ($stmt->execute()) {
    $stmt->close(); $db->close();
    sendJson(200, true, 'Trainer assigned. Plan status is now Planning.', [
        'training_plan_id' => $trainingPlanId,
        'trainer_id'       => $trainerId,
        'status'           => $newStatus,
    ]);
} else {
    $stmt->close(); $db->close();
    sendJson(500, false, 'Failed to assign trainer');
}
