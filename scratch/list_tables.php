<?php
$c = require 'config/config.php';
$m = new mysqli($c['db_host'], $c['db_user'], $c['db_pass'], $c['db_name']);
$res = $m->query('SHOW TABLES');
while($row = $res->fetch_row()) echo $row[0] . PHP_EOL;
