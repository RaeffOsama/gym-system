<?php
$db = new mysqli('localhost', 'root', '', 'gym');
$res = $db->query('SELECT name, email, role_name FROM users');
while($row = $res->fetch_assoc()) {
    echo $row['name'] . ' | ' . $row['email'] . ' | ' . $row['role_name'] . PHP_EOL;
}
