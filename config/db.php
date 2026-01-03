<?php
/**
 * =====================================================
 * DATABASE CONFIGURATION
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * This file handles the MySQL database connection.
 * Update the credentials below to match your XAMPP setup.
 */

// Database credentials - Update these for your environment
define('DB_HOST', 'localhost');      // Database host (usually localhost for XAMPP)
define('DB_USER', 'root');           // Database username (default: root for XAMPP)
define('DB_PASS', '');               // Database password (default: empty for XAMPP)
define('DB_NAME', 'sekuwa_kerner');  // Database name

// Site configuration
define('SITE_NAME', 'Sekuwa Kerner');
define('SITE_URL', 'http://localhost/sekuwa-kerner');

/**
 * Create database connection using MySQLi
 * We use mysqli for prepared statements to prevent SQL injection
 */
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check if connection was successful
if ($conn->connect_error) {
    // Log error and display user-friendly message
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please check if MySQL is running in XAMPP and the database exists.");
}

// Set charset to UTF-8 for proper character handling (Nepali text support)
$conn->set_charset("utf8mb4");

/**
 * Helper function to sanitize user input
 * 
 * @param mysqli $conn Database connection
 * @param string $data User input to sanitize
 * @return string Sanitized input
 */
function sanitize($conn, $data) {
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}

/**
 * Helper function to redirect to a URL
 * 
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Helper function to display flash messages
 * Sets a session message that can be displayed once
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message The message to display
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display and clear flash message
 * 
 * @return string HTML for the flash message
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        unset($_SESSION['flash']);
        return "<div class='alert alert-{$type}'>{$message}</div>";
    }
    return '';
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if logged in user is admin
 * 
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Format price in Nepali Rupees
 * 
 * @param float $price The price to format
 * @return string Formatted price
 */
function formatPrice($price) {
    return 'Rs. ' . number_format($price, 2);
}
?>

