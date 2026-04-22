<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$specialistId = $_SESSION['user_id'];
$db = getDbConnection();

// Check if user is a specialist (trainer or nutritionist)
$stmtRole = $db->prepare("SELECT role_name FROM users WHERE id = ?");
$stmtRole->bind_param("i", $specialistId);
$stmtRole->execute();
$roleData = $stmtRole->get_result()->fetch_assoc();
$role = $roleData ? strtolower($roleData['role_name']) : '';
$stmtRole->close();

if ($role !== 'trainer' && $role !== 'nutritionist' && $role !== 'admin' && $role !== 'specialist') {
    sendJson(403, false, "Forbidden: Specialist access only. Your current role is: " . ($role ?: 'none'));
}

$data = [];

if ($role === 'trainer' || $role === 'specialist' || $role === 'admin') {
    // 1. Total Earnings from Sessions
    $stmt = $db->prepare("SELECT SUM(price) as earnings FROM trainer_sessions WHERE trainer_id = ? AND status = 'booked'");
    $stmt->bind_param("i", $specialistId);
    $stmt->execute();
    $earnings = $stmt->get_result()->fetch_assoc()['earnings'];
    $data['total_earnings'] = $earnings ? (float)$earnings : 0.0;
    $stmt->close();

    // 2. Client Count (Unique users they've had sessions with)
    $stmt = $db->prepare("SELECT COUNT(DISTINCT user_id) as count FROM trainer_sessions WHERE trainer_id = ? AND user_id IS NOT NULL");
    $stmt->bind_param("i", $specialistId);
    $stmt->execute();
    $data['active_clients_trainer'] = (int)$stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 3. Upcoming Sessions
    $stmt = $db->prepare("
        SELECT ts.id, ts.start_time, ts.end_time, ts.status, u.name as client_name 
        FROM trainer_sessions ts
        LEFT JOIN users u ON ts.user_id = u.id
        WHERE ts.trainer_id = ? AND ts.start_time >= NOW()
        ORDER BY ts.start_time ASC
        LIMIT 10
    ");
    $stmt->bind_param("i", $specialistId);
    $stmt->execute();
    $res = $stmt->get_result();
    $data['upcoming_sessions'] = [];
    while ($row = $res->fetch_assoc()) {
        $data['upcoming_sessions'][] = $row;
    }
    $stmt->close();

    // 4. Session Status Summary
    $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM trainer_sessions WHERE trainer_id = ? GROUP BY status");
    $stmt->bind_param("i", $specialistId);
    $stmt->execute();
    $res = $stmt->get_result();
    $data['session_stats'] = [];
    while ($row = $res->fetch_assoc()) {
        $data['session_stats'][] = $row;
    }
    $stmt->close();
}

if ($role === 'nutritionist' || $role === 'specialist' || $role === 'admin') {
    // For nutritionists, we track diet plans (they might have a different payment model or sessions, 
    // but for now we use what we have in the schema)
    
    // 1. Total Diet Plans Created
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM diet_plans WHERE nutritionist_id = ?");
    $stmt->bind_param("i", $specialistId);
    $stmt->execute();
    $data['total_plans_created'] = (int)$stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 2. Active Clients (Users with active diet plans)
    $stmt = $db->prepare("SELECT COUNT(DISTINCT user_id) as count FROM diet_plans WHERE nutritionist_id = ?");
    $stmt->bind_param("i", $specialistId);
    $stmt->execute();
    $data['active_clients_nutritionist'] = (int)$stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 3. Recent Plans Given
    $stmt = $db->prepare("
        SELECT dp.id, dp.goal, dp.description, u.name as client_name 
        FROM diet_plans dp
        JOIN users u ON dp.user_id = u.id
        WHERE dp.nutritionist_id = ?
        ORDER BY dp.id DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $specialistId);
    $stmt->execute();
    $res = $stmt->get_result();
    $data['recent_plans'] = [];
    while ($row = $res->fetch_assoc()) {
        $data['recent_plans'][] = $row;
    }
    $stmt->close();
}

$db->close();

sendJson(200, true, 'Specialist dashboard statistics retrieved successfully', $data);
