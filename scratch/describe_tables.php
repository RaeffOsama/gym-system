<?php
$c = require 'config/config.php';
$m = new mysqli($c['db_host'], $c['db_user'], $c['db_pass'], $c['db_name']);
$tables = ['bookings', 'training_plans', 'transactions', 'workout_exercises'];
foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    $res = $m->query("DESCRIBE $table");
    if ($res) {
        while($row = $res->fetch_assoc()) echo $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
    } else {
        echo "Error: " . $m->error . "\n";
    }
}
