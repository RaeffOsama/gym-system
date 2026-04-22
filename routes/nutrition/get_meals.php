<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$db = getDbConnection();

$query = "SELECT * FROM meals";
$result = $db->query($query);

$meals = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $meals[] = $row;
    }
}

$db->close();

sendJson(200, true, 'Meals retrieved successfully', $meals);
