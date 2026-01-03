<?php
/**
 * =====================================================
 * PAYMENT PAGE
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Displays payment options:
 * - Cash on Delivery
 * - eSewa (mock)
 * - Khalti (mock)
 * Then proceeds to place order
 */

// Start session
session_start();

// Include database configuration
require_once '../config/db.php';

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    setFlashMessage('error', 'Your cart is empty.');
    redirect('cart.php');
}

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to checkout.');
    redirect('../auth/login.php');
}

// Check if checkout info exists
if (!isset($_SESSION['checkout'])) {
    setFlashMessage('error', 'Please complete delivery information first.');
    redirect('checkout.php');
}

$checkout = $_SESSION['checkout'];
$total = $checkout['total'];

// Handle payment selection
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = sanitize($conn, $_POST['payment_method'] ?? '');
    
    if (empty($payment_method)) {
        $error = 'Please select a payment method.';
    } elseif (!in_array($payment_method, ['Cash on Delivery', 'eSewa', 'Khalti'])) {
        $error = 'Invalid payment method.';
    } else {
        // Store payment method in session
        $_SESSION['checkout']['payment_method'] = $payment_method;
        
        // Mock payment processing for eSewa and Khalti
        if ($payment_method === 'eSewa' || $payment_method === 'Khalti') {
            // In a real application, you would redirect to the payment gateway
            // For this demo, we'll simulate a successful payment
            $_SESSION['checkout']['payment_status'] = 'Completed';
            $_SESSION['checkout']['transaction_id'] = 'TXN' . time() . rand(1000, 9999);
        } else {
            // Cash on Delivery
            $_SESSION['checkout']['payment_status'] = 'Pending';
            $_SESSION['checkout']['transaction_id'] = null;
        }
        
        // Redirect to place order
        redirect('place_order.php');
    }
}

// Get cart count for header
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - <?php echo SITE_NAME; ?></title>
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
                    <a href="cart.php" class="cart-link">
                        Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="../customer/dashboard.php">My Account</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>Payment</h1>
        </div>
    </div>

    <!-- Checkout Steps -->
    <div class="checkout-steps">
        <div class="container">
            <div class="steps">
                <div class="step completed">
                    <span class="step-number">1</span>
                    <span class="step-label">Cart</span>
                </div>
                <div class="step completed">
                    <span class="step-number">2</span>
                    <span class="step-label">Delivery</span>
                </div>
                <div class="step active">
                    <span class="step-number">3</span>
                    <span class="step-label">Payment</span>
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    <span class="step-label">Confirm</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Content -->
    <main class="payment-page">
        <div class="container">
            <!-- Display error if any -->
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="payment-layout">
                <!-- Payment Options -->
                <div class="payment-options-section">
                    <h2>Select Payment Method</h2>
                    <form action="payment.php" method="POST" class="payment-form">
                        <div class="payment-options">
                            <!-- Cash on Delivery -->
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="Cash on Delivery" required>
                                <div class="option-content">
                                    <span class="option-icon">💵</span>
                                    <div class="option-info">
                                        <h3>Cash on Delivery</h3>
                                        <p>Pay when your order arrives</p>
                                    </div>
                                </div>
                            </label>
                            
                            <!-- eSewa -->
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="eSewa">
                                <div class="option-content">
                                    <span class="option-icon">📱</span>
                                    <div class="option-info">
                                        <h3>eSewa</h3>
                                        <p>Pay using eSewa wallet (Demo)</p>
                                    </div>
                                </div>
                            </label>
                            
                            <!-- Khalti -->
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="Khalti">
                                <div class="option-content">
                                    <span class="option-icon">💜</span>
                                    <div class="option-info">
                                        <h3>Khalti</h3>
                                        <p>Pay using Khalti wallet (Demo)</p>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="payment-notice">
                            <p>⚠️ <strong>Note:</strong> eSewa and Khalti payments are mock/demo only for this college project. 
                            In a production environment, these would integrate with actual payment gateways.</p>
                        </div>

                        <div class="form-actions">
                            <a href="checkout.php" class="btn btn-outline">Back to Delivery</a>
                            <button type="submit" class="btn btn-primary btn-large">Place Order</button>
                        </div>
                    </form>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    
                    <div class="delivery-info">
                        <h4>Delivery To:</h4>
                        <p><?php echo nl2br(htmlspecialchars($checkout['address'])); ?></p>
                        <p>📞 <?php echo htmlspecialchars($checkout['phone']); ?></p>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($checkout['subtotal']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery:</span>
                            <span>
                                <?php if ($checkout['delivery_charge'] > 0): ?>
                                    <?php echo formatPrice($checkout['delivery_charge']); ?>
                                <?php else: ?>
                                    <span class="free-delivery">FREE</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="summary-row total-row">
                            <span>Total:</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                </div>
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

