<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$loggedInUserId = $_SESSION['user_id'];
$loggedInUserRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

// Only admins can update ANY trainer. Trainers can only update THEMSELVES.
// However, the user wants CRUD for trainers/nutritionists separately.

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(400, false, 'Invalid JSON input: ' . json_last_error_msg());
}

$targetUserId = (isset($input['user_id']) && $loggedInUserRole === 'admin') ? (int)$input['user_id'] : $loggedInUserId;

$db = getDbConnection();

// Verify target user is a trainer
$stmtVerify = $db->prepare("SELECT role_name FROM users WHERE id = ?");
$stmtVerify->bind_param("i", $targetUserId);
$stmtVerify->execute();
$userData = $stmtVerify->get_result()->fetch_assoc();
$stmtVerify->close();

if (!$userData || $userData['role_name'] !== 'trainer') {
    $db->close();
    sendJson(403, false, 'Forbidden: Targeted user is not a trainer');
}

// Support updating user Info and Profile info
$name = isset($input['name']) ? trim($input['name']) : null;
$phone = isset($input['phone']) ? trim($input['phone']) : null;
$experienceYears = isset($input['experience_years']) ? (int)$input['experience_years'] : null;
$bio = isset($input['bio']) ? $input['bio'] : null;
$achievements = isset($input['achievements']) ? $input['achievements'] : null;

$db->begin_transaction();

try {
    // 1. Update Core User Info if provided
    if ($name || $phone) {
        $updateUserParts = [];
        $params = [];
        $types = "";
        
        if ($name) { $updateUserParts[] = "name = ?"; $params[] = $name; $types .= "s"; }
        if ($phone) { $updateUserParts[] = "phone = ?"; $params[] = $phone; $types .= "s"; }
        
        $params[] = $targetUserId;
        $types .= "i";
        
        $stmtUser = $db->prepare("UPDATE users SET " . implode(", ", $updateUserParts) . " WHERE id = ?");
        $stmtUser->bind_param($types, ...$params);
        $stmtUser->execute();
        $stmtUser->close();
    }

    // 2. Update Specialist Profile Info if provided
    if ($experienceYears !== null || $bio !== null || $achievements !== null) {
        // Find existing profile
        $stmtCheck = $db->prepare("SELECT id FROM specialist_profiles WHERE user_id = ?");
        $stmtCheck->bind_param("i", $targetUserId);
        $stmtCheck->execute();
        $profileExists = $stmtCheck->get_result()->num_rows > 0;
        $stmtCheck->close();

        $bioJson = is_array($bio) ? json_encode($bio) : json_encode(['text' => $bio]);
        $achievementsJson = is_array($achievements) ? json_encode($achievements) : json_encode(['items' => $achievements]);

        if ($profileExists) {
            $updateProfileParts = [];
            $pParams = [];
            $pTypes = "";
            
            if ($experienceYears !== null) { $updateProfileParts[] = "experience_years = ?"; $pParams[] = $experienceYears; $pTypes .= "i"; }
            if ($bio !== null) { $updateProfileParts[] = "bio = ?"; $pParams[] = $bioJson; $pTypes .= "s"; }
            if ($achievements !== null) { $updateProfileParts[] = "achievements = ?"; $pParams[] = $achievementsJson; $pTypes .= "s"; }
            
            $pParams[] = $targetUserId;
            $pTypes .= "i";
            
            $stmtEx = $db->prepare("UPDATE specialist_profiles SET " . implode(", ", $updateProfileParts) . " WHERE user_id = ?");
            $stmtEx->bind_param($pTypes, ...$pParams);
            $stmtEx->execute();
            $stmtEx->close();
        } else {
            // Should not really happen if created correctly, but for safety:
            $stmtEx = $db->prepare("INSERT INTO specialist_profiles (user_id, experience_years, bio, achievements) VALUES (?, ?, ?, ?)");
            $stmtEx->bind_param("iiss", $targetUserId, $experienceYears, $bioJson, $achievementsJson);
            $stmtEx->execute();
            $stmtEx->close();
        }
    }

    $db->commit();
    sendJson(200, true, 'Trainer updated successfully', ['user_id' => $targetUserId]);

} catch (Exception $e) {
    $db->rollback();
    sendJson(500, false, 'Failed to update trainer: ' . $e->getMessage());
} finally {
    $db->close();
}
