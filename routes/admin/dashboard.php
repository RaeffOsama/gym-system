<?php

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: User not logged in');
}

$adminId = $_SESSION['user_id'];
$db = getDbConnection();

// Check if user is an admin
$stmtRole = $db->prepare("SELECT role_name FROM users WHERE id = ?");
$stmtRole->bind_param("i", $adminId);
$stmtRole->execute();
$user = $stmtRole->get_result()->fetch_assoc();
$stmtRole->close();

$role = $user ? strtolower($user['role_name']) : '';

if ($role !== 'admin' && $role !== 'manager') {
    sendJson(403, false, "Forbidden: Admin access only. Your current role is: " . ($role ?: 'none'));
}

$data = [];

// 1. Total Users Count
$res = $db->query("SELECT COUNT(*) as count FROM users");
$data['total_users'] = (int)$res->fetch_assoc()['count'];

// 2. Active Subscriptions Count
$res = $db->query("SELECT COUNT(*) as count FROM user_subscriptions WHERE status = 'active' AND end_date >= CURDATE()");
$data['active_subscriptions'] = (int)$res->fetch_assoc()['count'];

// 3. Total Revenue (Excluding Deposits)
$res = $db->query("SELECT SUM(amount) as revenue FROM transactions WHERE transaction_type NOT LIKE 'Deposit%'");
$data['total_revenue'] = (float)$res->fetch_assoc()['revenue'];

// 4. Revenue by Type
$res = $db->query("
    SELECT 
        CASE 
            WHEN transaction_type LIKE 'Purchase Subscription%' THEN 'Subscriptions'
            WHEN transaction_type LIKE 'Equipment Booking%' THEN 'Equipment'
            WHEN transaction_type LIKE 'Trainer Session%' THEN 'Specialist Sessions'
            ELSE 'Other'
        END as category,
        SUM(amount) as total
    FROM transactions 
    WHERE transaction_type NOT LIKE 'Deposit%'
    GROUP BY category
");
$data['revenue_breakdown'] = [];
while ($row = $res->fetch_assoc()) {
    $data['revenue_breakdown'][] = $row;
}

// 5. Equipment Status
$res = $db->query("SELECT status, COUNT(*) as count FROM equipment GROUP BY status");
$data['equipment_stats'] = [];
while ($row = $res->fetch_assoc()) {
    $data['equipment_stats'][] = $row;
}

// 6. Busiest Hours (Based on Equipment Bookings)
$res = $db->query("
    SELECT HOUR(start_time) as hour, COUNT(*) as booking_count 
    FROM bookings 
    GROUP BY hour 
    ORDER BY booking_count DESC 
    LIMIT 5
");
$data['busiest_hours'] = [];
while ($row = $res->fetch_assoc()) {
    $data['busiest_hours'][] = [
        'hour' => sprintf("%02d:00", $row['hour']),
        'count' => (int)$row['booking_count']
    ];
}

// 7. Recent Transactions (Last 5)
$res = $db->query("
    SELECT t.amount, t.transaction_type, t.created_at, u.name as user_name 
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.id DESC 
    LIMIT 5
");
$data['recent_activity'] = [];
while ($row = $res->fetch_assoc()) {
    $data['recent_activity'][] = $row;
}

$db->close();

sendJson(200, true, 'Admin dashboard statistics retrieved successfully', $data);
