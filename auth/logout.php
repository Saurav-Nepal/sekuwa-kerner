<?php
/**
 * =====================================================
 * USER LOGOUT
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Destroys the user session and redirects to home page.
 */

// Start session
session_start();

// Include database configuration for helper functions
require_once '../config/db.php';

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Start a new session for flash message
session_start();
setFlashMessage('success', 'You have been logged out successfully.');

// Redirect to home page
redirect('../index.php');
?>

