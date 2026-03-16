<?php

/**
 * PHP Built-in Web Server Router
 * 
 * If you are using the PHP built-in web server (e.g., `php -S localhost:8000`),
 * it ignores `.htaccess` files. This file acts as a router to emulate the 
 * Apache `mod_rewrite` behavior and pass all requests to `public/index.php`.
 * 
 * Usage: php -S localhost:8000 server.php
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Serve existing files directly, if they exist in the public folder
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Otherwise, route everything through index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require_once __DIR__ . '/public/index.php';
