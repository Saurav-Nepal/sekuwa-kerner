<?php
/**
 * SHOPPING CART PAGE - Sekuwa Kerner
 * Premium Nepali Food Ordering Platform
 */

session_start();
require_once '../config/db.php';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_qty'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        
        if ($quantity > 0 && isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            setFlashMessage('success', '✅ Cart updated!');
        } elseif ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
            setFlashMessage('success', '🗑️ Item removed from cart.');
        }
    }
    
    if (isset($_POST['remove_item'])) {
        $product_id = intval($_POST['product_id']);
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            setFlashMessage('success', '🗑️ Item removed from cart.');
        }
    }
    
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        setFlashMessage('success', '🗑️ Cart cleared.');
    }
    
    redirect('cart.php');
}

// Calculate totals
$cart_items = [];
$subtotal = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $subtotal += $item_total;
        $cart_items[] = array_merge($item, ['total' => $item_total]);
    }
}

$delivery_charge = $subtotal >= 500 ? 0 : 50;
$total = $subtotal + $delivery_charge;

// Cart count
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
    <title>Cart - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛒</text></svg>">
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
                    <a href="cart.php" class="cart-link active">
                        🛒 Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="../admin/dashboard.php">⚡ Admin</a></li>
                    <?php else: ?>
                        <li><a href="../customer/dashboard.php">👤 Account</a></li>
                    <?php endif; ?>
                    <li><a href="../auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="../auth/login.php">Login</a></li>
                    <li><a href="../auth/register.php" class="btn btn-primary btn-small">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <?php echo displayFlashMessage(); ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>🛒 Shopping Cart</h1>
            <p><?php echo $cart_count; ?> item<?php echo $cart_count != 1 ? 's' : ''; ?> in your cart</p>
        </div>
    </div>

    <!-- Cart Content -->
    <main class="cart-page">
        <div class="container">
            <?php if (!empty($cart_items)): ?>
                <div class="cart-layout">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td class="product-cell">
                                            <img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                 onerror="this.src='../assets/images/default.jpg'"
                                                 class="cart-item-image">
                                            <span><?php echo htmlspecialchars($item['name']); ?></span>
                                        </td>
                                        <td><?php echo formatPrice($item['price']); ?></td>
                                        <td>
                                            <form action="cart.php" method="POST" class="qty-form">
                                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="10" class="qty-input">
                                                <button type="submit" name="update_qty" class="btn btn-small">Update</button>
                                            </form>
                                        </td>
                                        <td style="color: var(--color-accent); font-weight: 600;">
                                            <?php echo formatPrice($item['total']); ?>
                                        </td>
                                        <td>
                                            <form action="cart.php" method="POST">
                                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" name="remove_item" class="btn btn-danger btn-small">🗑️</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="cart-actions">
                            <a href="../products.php" class="btn btn-outline">← Continue Shopping</a>
                            <form action="cart.php" method="POST" style="display: inline;">
                                <button type="submit" name="clear_cart" class="btn btn-danger no-loading" 
                                        onclick="return confirm('Clear all items from cart?')">
                                    🗑️ Clear Cart
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <h3>📋 Order Summary</h3>
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
                        <?php if ($subtotal < 500): ?>
                            <p class="delivery-note">💡 Add Rs. <?php echo 500 - $subtotal; ?> more for free delivery!</p>
                        <?php endif; ?>
                        <div class="summary-row total-row">
                            <span>Total:</span>
                            <span style="color: var(--color-accent);"><?php echo formatPrice($total); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary btn-block btn-large" style="margin-top: 1.5rem;">
                            🔥 Proceed to Checkout
                        </a>
                        
                        <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(255,186,8,0.1); border-radius: 12px; border: 1px solid rgba(255,186,8,0.2);">
                            <p style="font-size: 0.85rem; color: var(--color-text-muted); margin: 0;">
                                🔒 Secure checkout. Your data is protected.
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem;">🛒</div>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any delicious sekuwa yet!</p>
                    <a href="../products.php" class="btn btn-primary btn-large">🔥 Browse Menu</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>🍢 <?php echo SITE_NAME; ?></h3>
                    <p>Authentic Nepali Sekuwa delivered to your doorstep.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../products.php">Menu</a></li>
                        <li><a href="cart.php">Cart</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Us</h4>
                    <p>📍 Thamel, Kathmandu, Nepal</p>
                    <p>📞 +977 9841000000</p>
                    <p>📧 info@sekuwakerner.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
