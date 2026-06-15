<?php
session_start();

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

// Check if user is admin
if ($_SESSION['user_role'] !== 'admin') {
    sendJson(403, false, 'Forbidden: Admins only');
}

$db = getDbConnection();

$role = 'user';

$stmt = $db->prepare("
    SELECT 
        id,
        name,
        email,
        role_name,
        phone,
        age,
        gender,
        address,
        balance
    FROM users
    WHERE role_name = ?
    ORDER BY id ASC
");

$stmt->bind_param("s", $role);
$stmt->execute();

$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$db->close();

sendJson(200, true, 'Users retrieved successfully', $users);
?>
