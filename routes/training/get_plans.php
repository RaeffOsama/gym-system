<?php
// Training plans:
//   admin   → all plans
//   trainer → plans assigned to them
//   user    → their own plans

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

$userId   = (int)$_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

$db = getDbConnection();

$baseQuery = "
    SELECT
        tp.id,
        tp.goal,
        tp.description,
        tp.status,
        tp.user_id,
        u.name  AS user_name,
        tp.trainer_id,
        t.name  AS trainer_name,
        (SELECT COUNT(*) FROM workout_exercises we WHERE we.training_plan_id = tp.id) AS exercise_count
    FROM training_plans tp
    JOIN users u ON u.id = tp.user_id
    LEFT JOIN users t ON t.id = tp.trainer_id
";

if ($userRole === 'admin') {
    $stmt = $db->prepare($baseQuery . " ORDER BY tp.id DESC");
    $stmt->execute();
} elseif ($userRole === 'trainer') {
    $stmt = $db->prepare($baseQuery . " WHERE tp.trainer_id = ? ORDER BY tp.id DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
} else {
    $stmt = $db->prepare($baseQuery . " WHERE tp.user_id = ? ORDER BY tp.id DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
}

$plans = [];
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $plans[] = $row;
}
$stmt->close();
$db->close();

sendJson(200, true, 'Training plans retrieved successfully', ['plans' => $plans]);
