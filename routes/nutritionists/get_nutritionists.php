<?php

require_once __DIR__ . '/../../config/database.php';

$db = getDbConnection();

// Fetch all nutritionists with their specialist profiles
$query = "
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.phone, 
        u.gender, 
        u.age,
        sp.experience_years, 
        sp.bio, 
        sp.achievements
    FROM users u
    LEFT JOIN specialist_profiles sp ON u.id = sp.user_id
    WHERE u.role_name = 'nutritionist'
";

$result = $db->query($query);
$nutritionists = [];

while ($row = $result->fetch_assoc()) {
    // Decode JSON fields if they exist
    if (isset($row['bio'])) {
        $row['bio'] = json_decode($row['bio'], true);
    }
    if (isset($row['achievements'])) {
        $row['achievements'] = json_decode($row['achievements'], true);
    }
    $nutritionists[] = $row;
}

$db->close();

sendJson(200, true, 'Nutritionists retrieved successfully', ['nutritionists' => $nutritionists]);
