<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$loggedInUserId = $_SESSION['user_id'];
$loggedInUserRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

// Decide which user to update: 
// If user_id is provided AND logged in user is admin, use that. Otherwise use self.
$targetUserId = (isset($input['user_id']) && $loggedInUserRole === 'admin') ? (int)$input['user_id'] : $loggedInUserId;

$experienceYears = isset($input['experience_years']) ? (int)$input['experience_years'] : 0;
$bio = isset($input['bio']) ? $input['bio'] : null;
$achievements = isset($input['achievements']) ? $input['achievements'] : null;

$db = getDbConnection();

// Verify target user exists
$stmtUser = $db->prepare("SELECT id FROM users WHERE id = ?");
$stmtUser->bind_param("i", $targetUserId);
$stmtUser->execute();
if ($stmtUser->get_result()->num_rows === 0) {
    $stmtUser->close();
    $db->close();
    sendJson(404, false, 'Target user not found');
}
$stmtUser->close();

// Check if a profile already exists for the target user
$stmtCheck = $db->prepare("SELECT id FROM specialist_profiles WHERE user_id = ?");
$stmtCheck->bind_param("i", $targetUserId);
$stmtCheck->execute();
$exists = $stmtCheck->get_result()->num_rows > 0;
$stmtCheck->close();

// Prepare JSON for database
$bioJson = is_array($bio) ? json_encode($bio) : json_encode(['text' => $bio]);
$achievementsJson = is_array($achievements) ? json_encode($achievements) : json_encode(['items' => $achievements]);

if ($exists) {
    // Update existing profile
    $stmt = $db->prepare("UPDATE specialist_profiles SET experience_years = ?, bio = ?, achievements = ? WHERE user_id = ?");
    $stmt->bind_param("issi", $experienceYears, $bioJson, $achievementsJson, $targetUserId);
} else {
    // Create new profile
    $stmt = $db->prepare("INSERT INTO specialist_profiles (user_id, experience_years, bio, achievements) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $targetUserId, $experienceYears, $bioJson, $achievementsJson);
}

if ($stmt->execute()) {
    $stmt->close();
    $db->close();
    sendJson(200, true, 'Specialist profile updated successfully', ['user_id' => $targetUserId]);
} else {
    $stmt->close();
    $db->close();
    sendJson(500, false, 'Failed to update specialist profile');
}
