<?php
/**
 * PRODUCT DETAILS PAGE - Sekuwa Kerner
 * Premium Nepali Food Ordering Platform
 */

session_start();
require_once 'config/db.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    setFlashMessage('error', 'Invalid product.');
    redirect('products.php');
}

// Fetch product
$stmt = $conn->prepare("SELECT p.*, c.name as category_name 
                        FROM products p 
                        JOIN categories c ON p.category_id = c.id 
                        WHERE p.id = ? AND p.is_available = 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setFlashMessage('error', 'Product not found.');
    redirect('products.php');
}

$product = $result->fetch_assoc();

// Fetch related products
$related_stmt = $conn->prepare("SELECT * FROM products 
                                WHERE category_id = ? AND id != ? AND is_available = 1 
                                LIMIT 4");
$related_stmt->bind_param("ii", $product['category_id'], $product_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    if ($quantity < 1) $quantity = 1;
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity
        ];
    }
    
    setFlashMessage('success', '🔥 ' . $product['name'] . ' added to cart!');
    redirect('product_details.php?id=' . $product_id);
}

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
    <title><?php echo htmlspecialchars($product['name']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍢</text></svg>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">🍢 <?php echo SITE_NAME; ?></a>
            
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Menu</a></li>
                <li>
                    <a href="cart/cart.php" class="cart-link">
                        🛒 Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/dashboard.php">⚡ Admin</a></li>
                    <?php else: ?>
                        <li><a href="customer/dashboard.php">👤 Account</a></li>
                    <?php endif; ?>
                    <li><a href="auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="auth/login.php">Login</a></li>
                    <li><a href="auth/register.php" class="btn btn-primary btn-small">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <?php echo displayFlashMessage(); ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a> → 
            <a href="products.php">Menu</a> → 
            <a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a> → 
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>
    </div>

    <!-- Product Details -->
    <main class="product-details-page">
        <div class="container">
            <div class="product-details">
                <!-- Product Image -->
                <div class="product-image-large">
                    <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         onerror="this.src='assets/images/default.jpg'">
                </div>

                <!-- Product Info -->
                <div class="product-info-details">
                    <span class="product-category-badge"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="product-price-large"><?php echo formatPrice($product['price']); ?></p>
                    
                    <div class="product-description-full">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <!-- Add to Cart Form -->
                    <form action="product_details.php?id=<?php echo $product_id; ?>" method="POST" class="add-to-cart-form">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <div class="quantity-controls">
                                <button type="button" class="qty-btn" onclick="decrementQty()">−</button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="10">
                                <button type="button" class="qty-btn" onclick="incrementQty()">+</button>
                            </div>
                        </div>
                        
                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-large" style="width: 100%;">
                            🛒 Add to Cart
                        </button>
                    </form>

                    <!-- Quick Info -->
                    <div class="product-quick-info">
                        <p>✅ Fresh ingredients daily</p>
                        <p>✅ Prepared on order</p>
                        <p>✅ Fast delivery (30-45 mins)</p>
                        <p>✅ Authentic Nepali recipe</p>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <?php if ($related_result->num_rows > 0): ?>
                <section class="related-products">
                    <h2>You May Also Like</h2>
                    <div class="products-grid">
                        <?php while ($related = $related_result->fetch_assoc()): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="assets/images/<?php echo htmlspecialchars($related['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($related['name']); ?>"
                                         onerror="this.src='assets/images/default.jpg'"
                                         loading="lazy">
                                </div>
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($related['name']); ?></h3>
                                    <div class="product-footer">
                                        <span class="product-price"><?php echo formatPrice($related['price']); ?></span>
                                        <a href="product_details.php?id=<?php echo $related['id']; ?>" class="btn btn-small btn-primary">View</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </section>
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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Menu</a></li>
                        <li><a href="cart/cart.php">Cart</a></li>
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

    <script src="assets/js/script.js"></script>
</body>
</html>
