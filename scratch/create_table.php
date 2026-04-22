<?php
$c = require 'config/config.php';
$m = new mysqli($c['db_host'], $c['db_user'], $c['db_pass'], $c['db_name']);

$sql = "CREATE TABLE IF NOT EXISTS trainer_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'available',
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;";

if ($m->query($sql)) {
    echo "Table 'trainer_sessions' created successfully.\n";
} else {
    echo "Error creating table: " . $m->error . "\n";
}
