<?php
// Admin assigns a nutritionist to a diet plan.
// Sets status → 'Planning'.

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}
if ($_SESSION['user_role'] !== 'admin') {
    sendJson(403, false, 'Forbidden: Admins only');
}

$input = json_decode(file_get_contents('php://input'), true);

$dietPlanId      = isset($input['diet_plan_id'])    ? (int)$input['diet_plan_id']    : 0;
$nutritionistId  = isset($input['nutritionist_id']) ? (int)$input['nutritionist_id'] : 0;

if ($dietPlanId <= 0 || $nutritionistId <= 0) {
    sendJson(400, false, 'diet_plan_id and nutritionist_id are required');
}

$db = getDbConnection();

// Verify diet plan exists
$stmt = $db->prepare("SELECT id FROM diet_plans WHERE id = ?");
$stmt->bind_param("i", $dietPlanId);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $stmt->close(); $db->close();
    sendJson(404, false, 'Diet plan not found');
}
$stmt->close();

// Verify the user is a nutritionist
$stmt = $db->prepare("SELECT role_name FROM users WHERE id = ?");
$stmt->bind_param("i", $nutritionistId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || $user['role_name'] !== 'nutritionist') {
    $db->close();
    sendJson(400, false, 'The specified user is not a nutritionist');
}

$newStatus = 'Planning';
$stmt = $db->prepare("UPDATE diet_plans SET nutritionist_id = ?, status = ? WHERE id = ?");
$stmt->bind_param("isi", $nutritionistId, $newStatus, $dietPlanId);

if ($stmt->execute()) {
    $stmt->close(); $db->close();
    sendJson(200, true, 'Nutritionist assigned. Plan status is now Planning.', [
        'diet_plan_id'     => $dietPlanId,
        'nutritionist_id'  => $nutritionistId,
        'status'           => $newStatus,
    ]);
} else {
    $stmt->close(); $db->close();
    sendJson(500, false, 'Failed to assign nutritionist');
}
