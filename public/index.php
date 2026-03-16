<?php
// Define simple REST API Entry point

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
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Get the base path dynamically to avoid hardcoding subfolder paths
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Normalize the URI (make sure it starts with / and removes trailing slashes)
$requestUri = rtrim($requestUri, '/');
if (empty($requestUri)) {
    $requestUri = '/';
}

// Route Definitions
if ($requestUri === '/api/auth/register' && $method === 'POST') {
    require __DIR__ . '/../routes/auth/register.php';
} elseif ($requestUri === '/api/auth/login' && $method === 'POST') {
    require __DIR__ . '/../routes/auth/login.php';
} elseif ($requestUri === '/api/subscriptions' && $method === 'GET') {
    require __DIR__ . '/../routes/subscriptions/get_subscriptions.php';
} elseif ($requestUri === '/api/subscriptions/create' && $method === 'POST') {
    require __DIR__ . '/../routes/subscriptions/create_subscription.php';
} elseif ($requestUri === '/api/subscriptions/update' && $method === 'POST') {
    require __DIR__ . '/../routes/subscriptions/update_subscription.php';
} elseif ($requestUri === '/api/subscriptions/delete' && $method === 'POST') {
    require __DIR__ . '/../routes/subscriptions/delete_subscription.php';
} elseif ($requestUri === '/api/equipment' && $method === 'GET') {
    require __DIR__ . '/../routes/equipment/get_equipment.php';
} elseif ($requestUri === '/api/equipment/create' && $method === 'POST') {
    require __DIR__ . '/../routes/equipment/create_equipment.php';
} elseif ($requestUri === '/api/equipment/update' && $method === 'POST') {
    require __DIR__ . '/../routes/equipment/update_equipment.php';
} elseif ($requestUri === '/api/equipment/delete' && $method === 'POST') {
    require __DIR__ . '/../routes/equipment/delete_equipment.php';
} else {
    sendJson(404, false, 'Route not found');
}
