<?php

function getDbConnection() {
    $configFile = __DIR__ . '/config.php';
    
    if (!file_exists($configFile)) {
        sendJson(500, false, "Configuration file missing. Please copy config.example.php to config.php and update your credentials.");
    }
    
    $config = require $configFile;
    
    // Enable error reporting for mysqli
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        $mysqli = new mysqli(
            $config['db_host'],
            $config['db_user'],
            $config['db_pass'],
            $config['db_name']
        );
        $mysqli->set_charset("utf8mb4");
        return $mysqli;
    } catch (Exception $e) {
        sendJson(500, false, "Database connection failed: " . $e->getMessage());
    }
}
