<?php
/**
 * =====================================================
 * CUSTOMER ORDER REPORT
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Shows order history and spending summary for customer
 */

// Start session
session_start();

// Include database configuration
require_once '../config/db.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to view your reports.');
    redirect('../auth/login.php');
}

// Redirect admin to admin reports
if (isAdmin()) {
    redirect('../admin/reports.php');
}

$user_id = getCurrentUserId();

// Get date range
$start_date = isset($_GET['start_date']) ? sanitize($conn, $_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitize($conn, $_GET['end_date']) : date('Y-m-d');

// Get overall statistics
$stats_stmt = $conn->prepare("SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(total_price), 0) as total_spent,
    COALESCE(AVG(total_price), 0) as avg_order,
    COUNT(CASE WHEN status = 'Delivered' THEN 1 END) as completed
    FROM orders 
    WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?");
$stats_stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Get monthly spending
$monthly_stmt = $conn->prepare("SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as orders,
    SUM(total_price) as total
    FROM orders 
    WHERE user_id = ? AND status != 'Cancelled'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12");
$monthly_stmt->bind_param("i", $user_id);
$monthly_stmt->execute();
$monthly_result = $monthly_stmt->get_result();

// Get favorite items
$favorites_stmt = $conn->prepare("SELECT 
    p.name,
    SUM(oi.quantity) as times_ordered,
    SUM(oi.quantity * oi.price) as total_spent
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.user_id = ? AND o.status != 'Cancelled'
    GROUP BY p.id
    ORDER BY times_ordered DESC
    LIMIT 5");
$favorites_stmt->bind_param("i", $user_id);
$favorites_stmt->execute();
$favorites_result = $favorites_stmt->get_result();

// Get recent orders
$orders_stmt = $conn->prepare("SELECT * FROM orders 
                               WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
                               ORDER BY created_at DESC");
$orders_stmt->bind_param("iss", $user_id, $start_date, $end_date);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();

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
    <title>Order Report - <?php echo SITE_NAME; ?></title>
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

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>Order Report</h1>
            <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Report Content -->
    <main class="report-page">
        <div class="container">
            <!-- Date Filter -->
            <div class="filters-bar">
                <form action="report.php" method="GET" class="inline-form">
                    <label>From:</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                    <label>To:</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                    <button type="submit" class="btn btn-small">Apply</button>
                    <a href="report.php" class="btn btn-outline btn-small">Reset</a>
                </form>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>Orders in Period</p>
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
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3><?php echo formatPrice($stats['avg_order']); ?></h3>
                        <p>Average Order</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['completed']; ?></h3>
                        <p>Completed Orders</p>
                    </div>
                </div>
            </div>

            <div class="reports-grid">
                <!-- Monthly Spending -->
                <div class="report-section">
                    <h2>Monthly Spending</h2>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Orders</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($monthly_result->num_rows > 0): ?>
                                <?php while ($month = $monthly_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                                        <td><?php echo $month['orders']; ?></td>
                                        <td><?php echo formatPrice($month['total']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Favorite Items -->
                <div class="report-section">
                    <h2>Your Favorite Items</h2>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Times Ordered</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($favorites_result->num_rows > 0): ?>
                                <?php while ($fav = $favorites_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fav['name']); ?></td>
                                        <td><?php echo $fav['times_ordered']; ?>x</td>
                                        <td><?php echo formatPrice($fav['total_spent']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order History -->
            <div class="report-section full-width">
                <h2>Order History (<?php echo date('M j', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?>)</h2>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders_result->num_rows > 0): ?>
                            <?php while ($order = $orders_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="order_status.php?id=<?php echo $order['id']; ?>">
                                            #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo formatPrice($order['total_price']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No orders in this period</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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

