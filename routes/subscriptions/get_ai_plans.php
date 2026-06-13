<?php
session_start();
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

$userId = $_SESSION['user_id'];
$role   = $_SESSION['user_role'] ?? '';

$db = getDbConnection();

if ($role === 'admin') {
    $stmt = $db->prepare("
        SELECT uap.id, uap.user_id, u.name, u.email, uap.inbody, uap.ai_plan, uap.created_at
        FROM user_ai_plans uap
        JOIN users u ON u.id = uap.user_id
        ORDER BY uap.created_at DESC
    ");
    $stmt->execute();
} else {
    $stmt = $db->prepare("
        SELECT id, user_id, inbody, ai_plan, created_at
        FROM user_ai_plans
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
}

$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

foreach ($rows as &$row) {
    $row['inbody']  = json_decode($row['inbody'],  true);
    $row['ai_plan'] = json_decode($row['ai_plan'], true);
}
unset($row);

sendJson(200, true, 'AI plans retrieved', $rows);
