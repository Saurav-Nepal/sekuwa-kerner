<?php
/**
 * =====================================================
 * ADMIN AUTHENTICATION CHECK
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * This file is included at the top of every admin page.
 * It checks if the user is logged in and is an admin.
 * If not, redirects to login page.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once '../config/db.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to access admin area.');
    redirect('../auth/login.php');
}

// Check if user is admin
if (!isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('../index.php');
}

/**
 * Get admin statistics for dashboard
 * 
 * @param mysqli $conn Database connection
 * @return array Statistics array
 */
function getAdminStats($conn) {
    $stats = [];
    
    // Total orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $result->fetch_assoc()['count'];
    
    // Total products
    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    $stats['total_products'] = $result->fetch_assoc()['count'];
    
    // Total revenue
    $result = $conn->query("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE status != 'Cancelled'");
    $stats['total_revenue'] = $result->fetch_assoc()['total'];
    
    // Total customers
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
    $stats['total_customers'] = $result->fetch_assoc()['count'];
    
    // Pending orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'");
    $stats['pending_orders'] = $result->fetch_assoc()['count'];
    
    // Today's orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
    $stats['today_orders'] = $result->fetch_assoc()['count'];
    
    // Today's revenue
    $result = $conn->query("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'Cancelled'");
    $stats['today_revenue'] = $result->fetch_assoc()['total'];
    
    return $stats;
}
?>

