<?php
require_once __DIR__ . '/../config/database.php';
$db = getDbConnection();
$res = $db->query("SELECT id, name, email, role_name FROM users");
$users = [];
while ($row = $res->fetch_assoc()) {
    $users[] = $row;
}
header('Content-Type: application/json');
echo json_encode($users, JSON_PRETTY_PRINT);
