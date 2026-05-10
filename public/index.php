<?php
// Define simple REST API Entry point

// 0. Start session for authentication
session_start();

// 1. CORS Headers
require_once __DIR__ . '/../helpers/cors.php';

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Response Helpers
require_once __DIR__ . '/../helpers/response.php';

// 3. Simple Router
$requestUri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$method = strtoupper($_SERVER['REQUEST_METHOD']);
$requestUri = strtolower($requestUri);

// Get the base path dynamically to avoid hardcoding subfolder paths
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = strtolower($basePath);

if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Normalize the URI (ensure it starts with / and has no trailing slash, whitespace, or newlines)
$requestUri = trim($requestUri);
$requestUri = rtrim($requestUri, '/');
if (empty($requestUri) || $requestUri === '') {
    $requestUri = '/';
}
if ($requestUri !== '/' && strpos($requestUri, '/') !== 0) {
    $requestUri = '/' . $requestUri;
}

// Route Definitions
if ($requestUri === '/api/auth/register' && $method === 'POST') {
    require __DIR__ . '/../routes/auth/register.php';
} elseif ($requestUri === '/api/auth/login' && $method === 'POST') {
    require __DIR__ . '/../routes/auth/login.php';
} elseif ($requestUri === '/api/auth/logout' && $method === 'POST') {
    require __DIR__ . '/../routes/auth/logout.php';
} elseif ($requestUri === '/api/auth/profile' && $method === 'GET') {
    require __DIR__ . '/../routes/auth/profile.php';
} elseif ($requestUri === '/api/auth/specialist-profile' && $method === 'POST') {
    require __DIR__ . '/../routes/auth/update_specialist_profile.php';
} elseif ($requestUri === '/api/auth/update' && $method === 'POST') {
    require __DIR__ . '/../routes/auth/update_profile.php';
} elseif ($requestUri === '/api/auth/delete-user' && $method === 'POST') {
    require __DIR__ . '/../routes/auth/delete_user.php';
} elseif ($requestUri === '/api/subscriptions' && $method === 'GET') {
    require __DIR__ . '/../routes/subscriptions/get_subscriptions.php';
} elseif ($requestUri === '/api/subscriptions/user' && $method === 'GET') {
    require __DIR__ . '/../routes/subscriptions/get_user_subscriptions.php';
} elseif ($requestUri === '/api/subscriptions/purchase' && $method === 'POST') {
    require __DIR__ . '/../routes/subscriptions/purchase_subscription.php';
} elseif ($requestUri === '/api/subscriptions/create' && $method === 'POST') {
    require __DIR__ . '/../routes/subscriptions/create_subscription.php';
} elseif ($requestUri === '/api/subscriptions/update' && $method === 'POST') {
    require __DIR__ . '/../routes/subscriptions/update_subscription.php';
} elseif ($requestUri === '/api/subscriptions/delete' && $method === 'POST') {
    require __DIR__ . '/../routes/subscriptions/delete_subscription.php';
} elseif (($requestUri === '/api/equipment' || $requestUri === '/api/equipments') && $method === 'GET') {
    require __DIR__ . '/../routes/equipment/get_equipment.php';
} elseif ($requestUri === '/api/equipment/create' && $method === 'POST') {
    require __DIR__ . '/../routes/equipment/create_equipment.php';
} elseif ($requestUri === '/api/equipment/update' && $method === 'POST') {
    require __DIR__ . '/../routes/equipment/update_equipment.php';
} elseif ($requestUri === '/api/equipment/delete' && $method === 'POST') {
    require __DIR__ . '/../routes/equipment/delete_equipment.php';
} elseif ($requestUri === '/api/bookings' && $method === 'GET') {
    require __DIR__ . '/../routes/bookings/get_bookings.php';
} elseif ($requestUri === '/api/bookings/create' && $method === 'POST') {
    require __DIR__ . '/../routes/bookings/create_booking.php';
} elseif ($requestUri === '/api/bookings/cancel' && $method === 'POST') {
    require __DIR__ . '/../routes/bookings/cancel_booking.php';
} elseif ($requestUri === '/api/bookings/use' && $method === 'GET') {
    require __DIR__ . '/../routes/bookings/use_booking.php';
} elseif ($requestUri === '/api/specialists' && $method === 'GET') {
    require __DIR__ . '/../routes/specialists/get_all_specialists.php';
} elseif ($requestUri === '/api/specialists/create' && $method === 'POST') {
    require __DIR__ . '/../routes/specialists/create_specialist.php';
} elseif (($requestUri === '/api/trainers' || $requestUri === '/api/coaches') && $method === 'GET') {
    require __DIR__ . '/../routes/trainers/get_trainers.php';
} elseif ($requestUri === '/api/sessions/create' && $method === 'POST') {
    require __DIR__ . '/../routes/sessions/create_session.php';
} elseif ($requestUri === '/api/sessions/book' && $method === 'POST') {
    require __DIR__ . '/../routes/sessions/book_session.php';
} elseif ($requestUri === '/api/trainers/create' && $method === 'POST') {
    require __DIR__ . '/../routes/trainers/create_trainer.php';
} elseif ($requestUri === '/api/trainers/update' && $method === 'POST') {
    require __DIR__ . '/../routes/trainers/update_trainer.php';
} elseif ($requestUri === '/api/trainers/delete' && $method === 'POST') {
    require __DIR__ . '/../routes/trainers/delete_trainer.php';
} elseif ($requestUri === '/api/nutrition/create' && $method === 'POST') {
    require __DIR__ . '/../routes/nutrition/create_diet_plan.php';
} elseif ($requestUri === '/api/nutrition/user' && $method === 'GET') {
    require __DIR__ . '/../routes/nutrition/get_user_diet_plan.php';
} elseif ($requestUri === '/api/nutrition/diet-meals' && $method === 'GET') {
    require __DIR__ . '/../routes/nutrition/get_diet_meals.php';
} elseif ($requestUri === '/api/nutrition/plans' && $method === 'GET') {
    require __DIR__ . '/../routes/nutrition/get_plans.php';
} elseif ($requestUri === '/api/nutrition/plans/assign' && $method === 'POST') {
    require __DIR__ . '/../routes/nutrition/assign_nutritionist.php';
} elseif ($requestUri === '/api/nutrition/plans/add-meals' && $method === 'POST') {
    require __DIR__ . '/../routes/nutrition/add_meals_to_plan.php';
} elseif ($requestUri === '/api/nutritionists' && $method === 'GET') {
    require __DIR__ . '/../routes/nutritionists/get_nutritionists.php';
} elseif ($requestUri === '/api/nutritionists/create' && $method === 'POST') {
    require __DIR__ . '/../routes/nutritionists/create_nutritionist.php';
} elseif ($requestUri === '/api/nutritionists/update' && $method === 'POST') {
    require __DIR__ . '/../routes/nutritionists/update_nutritionist.php';
} elseif ($requestUri === '/api/nutritionists/delete' && $method === 'POST') {
    require __DIR__ . '/../routes/nutritionists/delete_nutritionist.php';
} elseif ($requestUri === '/api/meals' && $method === 'GET') {
    require __DIR__ . '/../routes/nutrition/get_meals.php';
} elseif ($requestUri === '/api/meals/create' && $method === 'POST') {
    require __DIR__ . '/../routes/nutrition/create_meal.php';
} elseif ($requestUri === '/api/exercises' && $method === 'GET') {
    require __DIR__ . '/../routes/exercises/get_exercises.php';
} elseif ($requestUri === '/api/exercises/create' && $method === 'POST') {
    require __DIR__ . '/../routes/exercises/create_exercise.php';
} elseif ($requestUri === '/api/exercises/update' && $method === 'POST') {
    require __DIR__ . '/../routes/exercises/update_exercise.php';
} elseif ($requestUri === '/api/exercises/delete' && $method === 'POST') {
    require __DIR__ . '/../routes/exercises/delete_exercise.php';
} elseif ($requestUri === '/api/payments/deposit' && $method === 'POST') {
    require __DIR__ . '/../routes/payments/deposit.php';
} elseif ($requestUri === '/api/payments/history' && $method === 'GET') {
    require __DIR__ . '/../routes/payments/get_history.php';
} elseif ($requestUri === '/api/training/user' && $method === 'GET') {
    require __DIR__ . '/../routes/training/get_user_training_plan.php';
} elseif ($requestUri === '/api/training/workout-exercises' && $method === 'GET') {
    require __DIR__ . '/../routes/training/get_workout_exercises.php';
} elseif ($requestUri === '/api/training/plans' && $method === 'GET') {
    require __DIR__ . '/../routes/training/get_plans.php';
} elseif ($requestUri === '/api/training/plans/assign' && $method === 'POST') {
    require __DIR__ . '/../routes/training/assign_trainer.php';
} elseif ($requestUri === '/api/training/plans/add-exercises' && $method === 'POST') {
    require __DIR__ . '/../routes/training/add_exercises_to_plan.php';
} elseif ($requestUri === '/api/admin/dashboard' && $method === 'GET') {
    require __DIR__ . '/../routes/admin/dashboard.php';
} elseif ($requestUri === '/api/specialists/dashboard' && $method === 'GET') {
    require __DIR__ . '/../routes/specialists/dashboard.php';
} else {
    sendJson(404, false, 'Route not found: ' . $method . ' ' . $requestUri);
}
