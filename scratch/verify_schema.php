<?php
$db = new mysqli('localhost', 'root', '', 'gym');
$res = $db->query("DESCRIBE users");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . PHP_EOL;
}
echo "\nTransactions table:\n";
$res = $db->query("DESCRIBE transactions");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . PHP_EOL;
}
