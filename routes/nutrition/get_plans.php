<?php
// Diet plans:
//   admin       → all plans
//   nutritionist → plans assigned to them
//   user        → their own plans

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

$userId   = (int)$_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

$db = getDbConnection();

$baseQuery = "
    SELECT
        dp.id,
        dp.goal,
        dp.description,
        dp.status,
        dp.user_id,
        u.name  AS user_name,
        dp.nutritionist_id,
        n.name  AS nutritionist_name,
        (SELECT COUNT(*) FROM diet_meals dm WHERE dm.diet_plan_id = dp.id) AS meal_count
    FROM diet_plans dp
    JOIN users u ON u.id = dp.user_id
    LEFT JOIN users n ON n.id = dp.nutritionist_id
";

if ($userRole === 'admin') {
    $stmt = $db->prepare($baseQuery . " ORDER BY dp.id DESC");
    $stmt->execute();
} elseif ($userRole === 'nutritionist') {
    $stmt = $db->prepare($baseQuery . " WHERE dp.nutritionist_id = ? ORDER BY dp.id DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
} else {
    $stmt = $db->prepare($baseQuery . " WHERE dp.user_id = ? ORDER BY dp.id DESC");
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

sendJson(200, true, 'Diet plans retrieved successfully', ['plans' => $plans]);
