<?php

// Check if a session exists
if (isset($_SESSION['user_id'])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    sendJson(200, true, 'Logout successful');
} else {
    sendJson(400, false, 'No active session found');
}
