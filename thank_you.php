<?php
/**
 * THANK YOU PAGE - Sekuwa Kerner
 * Premium Nepali Food Ordering Platform
 */

session_start();
require_once 'config/db.php';

if (!isset($_SESSION['last_order_id'])) {
    redirect('index.php');
}

$order_id = $_SESSION['last_order_id'];

// Fetch order details
$order_stmt = $conn->prepare("SELECT o.*, u.name as customer_name, u.email 
                              FROM orders o 
                              JOIN users u ON o.user_id = u.id 
                              WHERE o.id = ?");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    redirect('index.php');
}

// Fetch order items
$items_stmt = $conn->prepare("SELECT oi.*, p.name as product_name 
                              FROM order_items oi 
                              JOIN products p ON oi.product_id = p.id 
                              WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Fetch payment info
$payment_stmt = $conn->prepare("SELECT * FROM payments WHERE order_id = ?");
$payment_stmt->bind_param("i", $order_id);
$payment_stmt->execute();
$payment = $payment_stmt->get_result()->fetch_assoc();

unset($_SESSION['last_order_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed! - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>✅</text></svg>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">🍢 <?php echo SITE_NAME; ?></a>
            
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Menu</a></li>
                <li><a href="cart/cart.php">🛒 Cart</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="customer/dashboard.php">👤 Account</a></li>
                    <li><a href="auth/logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Thank You Content -->
    <main class="thank-you-page">
        <div class="container">
            <div class="thank-you-box">
                <!-- Success Icon -->
                <div class="success-icon">✓</div>
                
                <h1>🎉 Order Confirmed!</h1>
                <p class="order-message">Thank you! Your delicious sekuwa is being prepared.</p>
                
                <!-- Order ID -->
                <div class="order-id-box">
                    <h2>Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></h2>
                    <p>📅 <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
                </div>

                <!-- Order Details -->
                <div class="order-details-section">
                    <div class="order-details-grid">
                        <!-- Items -->
                        <div class="detail-section">
                            <h3>🍽️ Order Items</h3>
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $items_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td style="color: var(--color-accent);"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2"><strong>Total</strong></td>
                                        <td><strong style="color: var(--color-accent); font-size: 1.1rem;"><?php echo formatPrice($order['total_price']); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Delivery Info -->
                        <div class="detail-section">
                            <h3>🚚 Delivery Info</h3>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                            <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                            <?php if (!empty($order['notes'])): ?>
                                <p><strong>Notes:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Payment Info -->
                        <div class="detail-section">
                            <h3>💳 Payment</h3>
                            <p><strong>Method:</strong> <?php echo htmlspecialchars($payment['payment_method']); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="status-badge status-<?php echo strtolower($payment['payment_status']); ?>">
                                    <?php echo $payment['payment_status']; ?>
                                </span>
                            </p>
                            <?php if ($payment['transaction_id']): ?>
                                <p><strong>Transaction:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Order Status -->
                        <div class="detail-section">
                            <h3>📋 Status</h3>
                            <p class="order-status">
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </p>
                            <p style="margin-top: 1rem; color: var(--color-text-muted);">Track your order in your account.</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="thank-you-actions">
                    <a href="customer/orders.php" class="btn btn-outline btn-large">📦 View Orders</a>
                    <a href="products.php" class="btn btn-primary btn-large">🔥 Order More</a>
                </div>

                <!-- What's Next -->
                <div class="whats-next">
                    <h3>⏳ What Happens Next?</h3>
                    <ul>
                        <li>📧 You'll receive an order confirmation email shortly</li>
                        <li>🍳 Our kitchen is preparing your fresh sekuwa</li>
                        <li>🚴 Your order will be delivered hot & fresh (30-45 mins)</li>
                        <li>📱 Track your order status from your account</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved. | Made with ❤️ in Nepal</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
