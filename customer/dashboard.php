<?php
/**
 * =====================================================
 * CUSTOMER DASHBOARD
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Customer account page showing:
 * - Profile information
 * - Recent orders
 * - Quick stats
 */

// Start session
session_start();

// Include database configuration
require_once '../config/db.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to access your account.');
    redirect('../auth/login.php');
}

// Redirect admin to admin dashboard
if (isAdmin()) {
    redirect('../admin/dashboard.php');
}

$user_id = getCurrentUserId();

// Get user details
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Get order statistics
$stats_query = $conn->prepare("SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(CASE WHEN status != 'Cancelled' THEN total_price ELSE 0 END), 0) as total_spent,
    COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending_orders,
    COUNT(CASE WHEN status = 'Delivered' THEN 1 END) as completed_orders
    FROM orders WHERE user_id = ?");
$stats_query->bind_param("i", $user_id);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();

// Get recent orders
$recent_orders = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recent_orders->bind_param("i", $user_id);
$recent_orders->execute();
$orders_result = $recent_orders->get_result();

// Get cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="../index.php" class="logo">🍢 <?php echo SITE_NAME; ?></a>
            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
                <li><a href="../products.php">Menu</a></li>
                <li>
                    <a href="../cart/cart.php" class="cart-link">
                        Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="dashboard.php" class="active">My Account</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php echo displayFlashMessage(); ?>

    <!-- Dashboard Content -->
    <main class="customer-dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p>Manage your orders and account settings</p>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3><?php echo formatPrice($stats['total_spent']); ?></h3>
                        <p>Total Spent</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['pending_orders']; ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['completed_orders']; ?></h3>
                        <p>Completed</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Profile Section -->
                <div class="dashboard-section">
                    <h2>Profile Information</h2>
                    <div class="profile-info">
                        <div class="info-row">
                            <span class="label">Name:</span>
                            <span class="value"><?php echo htmlspecialchars($user['name']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Email:</span>
                            <span class="value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Phone:</span>
                            <span class="value"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Member Since:</span>
                            <span class="value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="dashboard-section">
                    <h2>Quick Actions</h2>
                    <div class="quick-actions-grid">
                        <a href="../products.php" class="action-card">
                            <span class="action-icon">🍽️</span>
                            <span>Browse Menu</span>
                        </a>
                        <a href="orders.php" class="action-card">
                            <span class="action-icon">📋</span>
                            <span>My Orders</span>
                        </a>
                        <a href="order_status.php" class="action-card">
                            <span class="action-icon">🔍</span>
                            <span>Track Order</span>
                        </a>
                        <a href="report.php" class="action-card">
                            <span class="action-icon">📊</span>
                            <span>Order History</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="dashboard-section full-width">
                <div class="section-header">
                    <h2>Recent Orders</h2>
                    <a href="orders.php" class="view-all">View All →</a>
                </div>
                
                <?php if ($orders_result->num_rows > 0): ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders_result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo formatPrice($order['total_price']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order_status.php?id=<?php echo $order['id']; ?>" class="btn btn-small">
                                            Track
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <p>You haven't placed any orders yet.</p>
                        <a href="../products.php" class="btn btn-primary">Order Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved. | College Project</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>

