<?php
/**
 * =====================================================
 * ORDER STATUS TRACKING
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Shows detailed order status and tracking information
 */

// Start session
session_start();

// Include database configuration
require_once '../config/db.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to track your order.');
    redirect('../auth/login.php');
}

$user_id = getCurrentUserId();

// Get order ID
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    setFlashMessage('error', 'Invalid order ID.');
    redirect('orders.php');
}

// Fetch order (ensure it belongs to current user)
$order_stmt = $conn->prepare("SELECT o.*, p.payment_method, p.payment_status, p.transaction_id 
                              FROM orders o 
                              LEFT JOIN payments p ON o.id = p.order_id 
                              WHERE o.id = ? AND o.user_id = ?");
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    setFlashMessage('error', 'Order not found.');
    redirect('orders.php');
}

// Fetch order items
$items_stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.image 
                              FROM order_items oi 
                              JOIN products p ON oi.product_id = p.id 
                              WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result();

// Define status steps
$status_steps = ['Pending', 'Cooking', 'Out for Delivery', 'Delivered'];
$current_step = array_search($order['status'], $status_steps);
if ($current_step === false) {
    $current_step = -1; // Cancelled
}

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
    <title>Order Status - <?php echo SITE_NAME; ?></title>
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
            <h1>Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></h1>
            <a href="orders.php" class="back-link">← Back to Orders</a>
        </div>
    </div>

    <!-- Order Status Content -->
    <main class="order-status-page">
        <div class="container">
            <!-- Status Tracker -->
            <?php if ($order['status'] !== 'Cancelled'): ?>
                <div class="status-tracker">
                    <h2>Order Status</h2>
                    <div class="tracker-steps">
                        <?php foreach ($status_steps as $index => $step): ?>
                            <div class="tracker-step <?php echo $index <= $current_step ? 'completed' : ''; ?> <?php echo $index === $current_step ? 'current' : ''; ?>">
                                <div class="step-icon">
                                    <?php
                                    $icons = ['📋', '🍳', '🚴', '✅'];
                                    echo $icons[$index];
                                    ?>
                                </div>
                                <div class="step-label"><?php echo $step; ?></div>
                            </div>
                            <?php if ($index < count($status_steps) - 1): ?>
                                <div class="step-connector <?php echo $index < $current_step ? 'completed' : ''; ?>"></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="status-cancelled">
                    <h2>❌ Order Cancelled</h2>
                    <p>This order has been cancelled.</p>
                </div>
            <?php endif; ?>

            <div class="order-details-grid">
                <!-- Order Items -->
                <div class="detail-section">
                    <h3>Order Items</h3>
                    <div class="order-items-list">
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <div class="order-item">
                                <img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                     onerror="this.src='../assets/images/default.jpg'"
                                     class="item-image">
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <p><?php echo formatPrice($item['price']); ?> x <?php echo $item['quantity']; ?></p>
                                </div>
                                <div class="item-total">
                                    <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="order-total-section">
                        <div class="total-row">
                            <span>Total:</span>
                            <span class="total-value"><?php echo formatPrice($order['total_price']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Delivery Details -->
                <div class="detail-section">
                    <h3>Delivery Information</h3>
                    <div class="info-box">
                        <p><strong>Address:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        <?php if (!empty($order['notes'])): ?>
                            <p><strong>Notes:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="detail-section">
                    <h3>Payment Information</h3>
                    <div class="info-box">
                        <p><strong>Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status-badge status-<?php echo strtolower($order['payment_status']); ?>">
                                <?php echo $order['payment_status']; ?>
                            </span>
                        </p>
                        <?php if ($order['transaction_id']): ?>
                            <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Timeline -->
                <div class="detail-section">
                    <h3>Order Timeline</h3>
                    <div class="timeline">
                        <div class="timeline-item">
                            <span class="time"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></span>
                            <span class="event">Order Placed</span>
                        </div>
                        <?php if ($order['status'] !== 'Pending' && $order['status'] !== 'Cancelled'): ?>
                            <div class="timeline-item">
                                <span class="time"><?php echo date('M j, Y g:i A', strtotime($order['updated_at'])); ?></span>
                                <span class="event">Status Updated: <?php echo $order['status']; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="order-actions">
                <a href="orders.php" class="btn btn-outline">← Back to Orders</a>
                <a href="../products.php" class="btn btn-primary">Order Again</a>
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

