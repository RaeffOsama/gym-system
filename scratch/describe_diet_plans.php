<?php
$c = require 'config/config.php';
$m = new mysqli($c['db_host'], $c['db_user'], $c['db_pass'], $c['db_name']);
$res = $m->query("DESCRIBE diet_plans");
while($row = $res->fetch_assoc()) echo $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
