<?php
/**
 * =====================================================
 * CUSTOMER ORDERS LIST
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Shows all orders for the logged-in customer
 */

// Start session
session_start();

// Include database configuration
require_once '../config/db.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to view your orders.');
    redirect('../auth/login.php');
}

// Redirect admin to admin dashboard
if (isAdmin()) {
    redirect('../admin/orders.php');
}

$user_id = getCurrentUserId();

// Get filter
$status_filter = isset($_GET['status']) ? sanitize($conn, $_GET['status']) : '';

// Build query
$sql = "SELECT o.*, p.payment_method, p.payment_status 
        FROM orders o 
        LEFT JOIN payments p ON o.id = p.order_id 
        WHERE o.user_id = ?";
$params = [$user_id];
$types = "i";

if (!empty($status_filter)) {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();

// Get cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

$statuses = ['Pending', 'Cooking', 'Out for Delivery', 'Delivered', 'Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - <?php echo SITE_NAME; ?></title>
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
            <h1>My Orders</h1>
            <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Orders Content -->
    <main class="orders-page">
        <div class="container">
            <!-- Filter -->
            <div class="filters-bar">
                <form action="orders.php" method="GET" class="inline-form">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All Orders</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status; ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                                <?php echo $status; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <!-- Orders List -->
            <?php if ($orders->num_rows > 0): ?>
                <div class="orders-list">
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <?php
                        // Get order items
                        $items_stmt = $conn->prepare("SELECT oi.*, p.name as product_name 
                                                      FROM order_items oi 
                                                      JOIN products p ON oi.product_id = p.id 
                                                      WHERE oi.order_id = ?");
                        $items_stmt->bind_param("i", $order['id']);
                        $items_stmt->execute();
                        $items = $items_stmt->get_result();
                        ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-id">
                                    <h3>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                    <span class="order-date"><?php echo date('M j, Y \a\t g:i A', strtotime($order['created_at'])); ?></span>
                                </div>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </div>
                            
                            <div class="order-items">
                                <?php 
                                $item_names = [];
                                while ($item = $items->fetch_assoc()) {
                                    $item_names[] = $item['product_name'] . ' x' . $item['quantity'];
                                }
                                echo implode(', ', $item_names);
                                ?>
                            </div>
                            
                            <div class="order-footer">
                                <div class="order-total">
                                    <span class="label">Total:</span>
                                    <span class="value"><?php echo formatPrice($order['total_price']); ?></span>
                                </div>
                                <div class="order-payment">
                                    <span class="label">Payment:</span>
                                    <span class="value"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                                </div>
                                <a href="order_status.php?id=<?php echo $order['id']; ?>" class="btn btn-small">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h2>No orders found</h2>
                    <?php if ($status_filter): ?>
                        <p>No orders with status "<?php echo htmlspecialchars($status_filter); ?>"</p>
                        <a href="orders.php" class="btn btn-outline">View All Orders</a>
                    <?php else: ?>
                        <p>You haven't placed any orders yet.</p>
                        <a href="../products.php" class="btn btn-primary">Browse Menu</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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

