<?php
$db = new mysqli('localhost', 'root', '', 'gym');

// List tables
echo "Tables:\n";
$res = $db->query('SHOW TABLES');
while($row = $res->fetch_row()) echo "- " . $row[0] . PHP_EOL;

// Check if admin@gym.com exists
$res = $db->query("SELECT id FROM users WHERE email = 'admin@gym.com'");
if ($res->num_rows === 0) {
    echo "\nCreating admin account...\n";
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $db->query("INSERT INTO users (name, email, password_hash, role_name) VALUES ('Admin', 'admin@gym.com', '$pass', 'admin')");
    echo "Admin created! Email: admin@gym.com, Password: admin123\n";
} else {
    echo "\nAdmin account already exists.\n";
}
