<?php
/**
 * CHECKOUT PAGE - Sekuwa Kerner
 * Premium Nepali Food Ordering Platform
 */

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    setFlashMessage('error', 'Your cart is empty.');
    redirect('cart.php');
}

if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to checkout.');
    $_SESSION['redirect_after_login'] = '../cart/checkout.php';
    redirect('../auth/login.php');
}

// Get user details
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Calculate cart totals
$cart_items = [];
$subtotal = 0;

foreach ($_SESSION['cart'] as $item) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
    $cart_items[] = array_merge($item, ['total' => $item_total]);
}

$delivery_charge = $subtotal >= 500 ? 0 : 50;
$total = $subtotal + $delivery_charge;

// Handle form submission
$errors = [];
$address = $user['phone'] ?? '';
$phone = '';
$notes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = sanitize($conn, $_POST['address'] ?? '');
    $phone = sanitize($conn, $_POST['phone'] ?? '');
    $notes = sanitize($conn, $_POST['notes'] ?? '');
    
    if (empty($address)) {
        $errors[] = 'Delivery address is required.';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = 'Please enter a valid 10-digit phone number.';
    }
    
    if (empty($errors)) {
        $_SESSION['checkout'] = [
            'address' => $address,
            'phone' => $phone,
            'notes' => $notes,
            'subtotal' => $subtotal,
            'delivery_charge' => $delivery_charge,
            'total' => $total
        ];
        
        redirect('payment.php');
    }
}

// Cart count
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
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📦</text></svg>">
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
                        🛒 Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="../customer/dashboard.php">👤 Account</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>📦 Checkout</h1>
            <p>Enter your delivery details</p>
        </div>
    </div>

    <!-- Checkout Steps -->
    <div class="checkout-steps">
        <div class="container">
            <div class="steps">
                <div class="step completed">
                    <span class="step-number">✓</span>
                    <span class="step-label">Cart</span>
                </div>
                <div class="step active">
                    <span class="step-number">2</span>
                    <span class="step-label">Delivery</span>
                </div>
                <div class="step">
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

    <!-- Checkout Content -->
    <main class="checkout-page">
        <div class="container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin-left: 1rem;">
                        <?php foreach ($errors as $error): ?>
                            <li>❌ <?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="checkout-layout">
                <!-- Delivery Form -->
                <div class="checkout-form-section">
                    <h2>🚚 Delivery Information</h2>
                    <form action="checkout.php" method="POST" class="checkout-form" id="checkoutForm">
                        <div class="form-group">
                            <label for="name">👤 Full Name</label>
                            <input type="text" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" disabled 
                                   style="opacity: 0.7;">
                            <small>Name from your account</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">📧 Email</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                                   style="opacity: 0.7;">
                            <small>Order confirmation will be sent here</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">📱 Phone Number *</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($phone ?: $user['phone']); ?>"
                                   placeholder="Enter 10-digit phone number"
                                   pattern="[0-9]{10}"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">📍 Delivery Address *</label>
                            <textarea id="address" name="address" rows="3" 
                                      placeholder="Enter your full delivery address (e.g., Street, Area, City)"
                                      required><?php echo htmlspecialchars($address); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">📝 Order Notes <span style="color: var(--color-text-muted);">(Optional)</span></label>
                            <textarea id="notes" name="notes" rows="2" 
                                      placeholder="Any special instructions? (e.g., Extra spicy, No onions)"><?php echo htmlspecialchars($notes); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <a href="cart.php" class="btn btn-outline">← Back to Cart</a>
                            <button type="submit" class="btn btn-primary btn-large">Continue to Payment →</button>
                        </div>
                    </form>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <h2>📋 Order Summary</h2>
                    <div class="summary-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="summary-item">
                                <div class="item-info">
                                    <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="item-qty">×<?php echo $item['quantity']; ?></span>
                                </div>
                                <span class="item-price" style="color: var(--color-accent);"><?php echo formatPrice($item['total']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery:</span>
                            <span>
                                <?php if ($delivery_charge > 0): ?>
                                    <?php echo formatPrice($delivery_charge); ?>
                                <?php else: ?>
                                    <span class="free-delivery">🎉 FREE</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="summary-row total-row">
                            <span>Total:</span>
                            <span style="color: var(--color-accent);"><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(255,186,8,0.1); border-radius: 12px; border: 1px solid rgba(255,186,8,0.2);">
                        <p style="font-size: 0.85rem; color: var(--color-text-muted); margin: 0;">
                            🔒 Your information is secure and encrypted
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
