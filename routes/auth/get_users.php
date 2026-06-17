<?php
session_start();
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

if ($_SESSION['user_role'] !== 'admin') {
    sendJson(403, false, 'Forbidden: Admins only');
}

$db = getDbConnection();

$stmt = $db->prepare("
    SELECT id, name, email, role_name, phone, age, gender, address, balance
    FROM users
    WHERE role_name = 'user'
    ORDER BY id ASC
");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

sendJson(200, true, 'Users retrieved', $users);
