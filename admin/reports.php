<?php
/**
 * =====================================================
 * ADMIN SALES REPORTS
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Display sales reports:
 * - Daily sales summary
 * - Popular products
 * - Revenue statistics
 */

// Include admin authentication
require_once 'admin_auth.php';

// Get date range filter
$start_date = isset($_GET['start_date']) ? sanitize($conn, $_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitize($conn, $_GET['end_date']) : date('Y-m-d');

// Get overall statistics
$stats_query = $conn->prepare("SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(total_price), 0) as total_revenue,
    COALESCE(AVG(total_price), 0) as avg_order_value,
    COUNT(CASE WHEN status = 'Delivered' THEN 1 END) as completed_orders,
    COUNT(CASE WHEN status = 'Cancelled' THEN 1 END) as cancelled_orders
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?");
$stats_query->bind_param("ss", $start_date, $end_date);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();

// Get daily sales
$daily_sales = $conn->prepare("SELECT 
    DATE(created_at) as date,
    COUNT(*) as orders,
    SUM(total_price) as revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'Cancelled'
    GROUP BY DATE(created_at)
    ORDER BY date DESC");
$daily_sales->bind_param("ss", $start_date, $end_date);
$daily_sales->execute();
$daily_result = $daily_sales->get_result();

// Get popular products
$popular_products = $conn->query("SELECT 
    p.name,
    SUM(oi.quantity) as total_sold,
    SUM(oi.quantity * oi.price) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'Cancelled'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10");

// Get orders by status
$status_breakdown = $conn->query("SELECT 
    status,
    COUNT(*) as count
    FROM orders
    GROUP BY status
    ORDER BY count DESC");

// Get payment method breakdown
$payment_breakdown = $conn->query("SELECT 
    p.payment_method,
    COUNT(*) as count,
    SUM(o.total_price) as total
    FROM payments p
    JOIN orders o ON p.order_id = o.id
    WHERE o.status != 'Cancelled'
    GROUP BY p.payment_method");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <!-- Admin Navigation -->
    <nav class="admin-navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">🍢 <?php echo SITE_NAME; ?> Admin</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="reports.php" class="active">Reports</a></li>
                <li><a href="../index.php" target="_blank">View Site</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Reports Content -->
    <main class="admin-main">
        <div class="container">
            <div class="admin-header">
                <h1>Sales Reports</h1>
            </div>

            <!-- Date Range Filter -->
            <div class="filters-bar">
                <form action="reports.php" method="GET" class="inline-form">
                    <label>From:</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                    <label>To:</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                    <button type="submit" class="btn btn-small">Apply</button>
                    <a href="reports.php" class="btn btn-outline btn-small">Reset</a>
                </form>
            </div>

            <!-- Statistics Cards -->
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
                        <h3><?php echo formatPrice($stats['total_revenue']); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3><?php echo formatPrice($stats['avg_order_value']); ?></h3>
                        <p>Avg Order Value</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['completed_orders']; ?></h3>
                        <p>Completed Orders</p>
                    </div>
                </div>
            </div>

            <div class="reports-grid">
                <!-- Daily Sales Report -->
                <div class="report-section">
                    <h2>Daily Sales</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($daily_result->num_rows > 0): ?>
                                <?php while ($day = $daily_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($day['date'])); ?></td>
                                        <td><?php echo $day['orders']; ?></td>
                                        <td><?php echo formatPrice($day['revenue']); ?></td>
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

                <!-- Popular Products -->
                <div class="report-section">
                    <h2>Top Selling Products</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($popular_products->num_rows > 0): ?>
                                <?php while ($product = $popular_products->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo $product['total_sold']; ?></td>
                                        <td><?php echo formatPrice($product['total_revenue']); ?></td>
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

                <!-- Order Status Breakdown -->
                <div class="report-section">
                    <h2>Orders by Status</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($status = $status_breakdown->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $status['status'])); ?>">
                                            <?php echo $status['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $status['count']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Payment Method Breakdown -->
                <div class="report-section">
                    <h2>Payment Methods</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th>Orders</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($payment_breakdown->num_rows > 0): ?>
                                <?php while ($payment = $payment_breakdown->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                        <td><?php echo $payment['count']; ?></td>
                                        <td><?php echo formatPrice($payment['total']); ?></td>
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
        </div>
    </main>

    <!-- Footer -->
    <footer class="admin-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> Admin Panel | College Project</p>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>

