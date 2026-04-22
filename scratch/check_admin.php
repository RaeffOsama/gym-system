<?php
require 'config/database.php';
function sendJson($code, $success, $msg, $data = []) {
    echo json_encode(['success' => $success, 'message' => $msg, 'data' => $data], JSON_PRETTY_PRINT);
}
$db = getDbConnection();
$res = $db->query("SELECT id, name, email, role_name FROM users WHERE role_name = 'admin'");
print_r($res->fetch_all(MYSQLI_ASSOC));
