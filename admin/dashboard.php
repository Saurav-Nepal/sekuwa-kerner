<?php
/**
 * =====================================================
 * ADMIN DASHBOARD
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Main admin dashboard showing:
 * - Statistics overview
 * - Recent orders
 * - Quick actions
 */

// Include admin authentication
require_once 'admin_auth.php';

// Get admin statistics
$stats = getAdminStats($conn);

// Fetch recent orders
$recent_orders = $conn->query("SELECT o.*, u.name as customer_name 
                               FROM orders o 
                               JOIN users u ON o.user_id = u.id 
                               ORDER BY o.created_at DESC 
                               LIMIT 5");

// Fetch low stock products (featured items)
$featured_products = $conn->query("SELECT * FROM products WHERE is_featured = 1 ORDER BY name LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <!-- Admin Navigation -->
    <nav class="admin-navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">🍢 <?php echo SITE_NAME; ?> Admin</a>
            <ul class="nav-links">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="../index.php" target="_blank">View Site</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php echo displayFlashMessage(); ?>

    <!-- Dashboard Content -->
    <main class="admin-main">
        <div class="container">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
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
                    <div class="stat-icon">🍽️</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_products']; ?></h3>
                        <p>Total Products</p>
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
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_customers']; ?></h3>
                        <p>Customers</p>
                    </div>
                </div>
            </div>

            <!-- Today's Stats -->
            <div class="today-stats">
                <div class="today-stat">
                    <span class="label">Today's Orders:</span>
                    <span class="value"><?php echo $stats['today_orders']; ?></span>
                </div>
                <div class="today-stat">
                    <span class="label">Today's Revenue:</span>
                    <span class="value"><?php echo formatPrice($stats['today_revenue']); ?></span>
                </div>
                <div class="today-stat">
                    <span class="label">Pending Orders:</span>
                    <span class="value pending"><?php echo $stats['pending_orders']; ?></span>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="add_product.php" class="btn btn-primary">+ Add Product</a>
                    <a href="orders.php" class="btn btn-outline">View All Orders</a>
                    <a href="reports.php" class="btn btn-outline">View Reports</a>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Recent Orders -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Recent Orders</h2>
                        <a href="orders.php" class="view-all">View All →</a>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_orders->num_rows > 0): ?>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td><?php echo formatPrice($order['total_price']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                                <?php echo $order['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No orders yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Featured Products -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Featured Products</h2>
                        <a href="products.php" class="view-all">View All →</a>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($featured_products->num_rows > 0): ?>
                                <?php while ($product = $featured_products->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo formatPrice($product['price']); ?></td>
                                        <td>
                                            <?php if ($product['is_available']): ?>
                                                <span class="status-badge status-available">Available</span>
                                            <?php else: ?>
                                                <span class="status-badge status-unavailable">Unavailable</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No featured products</td>
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

