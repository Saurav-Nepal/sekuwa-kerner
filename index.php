<?php
/**
 * HOME PAGE - Sekuwa Kerner
 * Premium Nepali Food Ordering Platform
 */

session_start();
require_once 'config/db.php';

// Fetch featured products
$featured_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   JOIN categories c ON p.category_id = c.id 
                   WHERE p.is_featured = 1 AND p.is_available = 1 
                   LIMIT 6";
$featured_result = $conn->query($featured_query);

// Fetch all categories
$categories_query = "SELECT * FROM categories";
$categories_result = $conn->query($categories_query);

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
    <title><?php echo SITE_NAME; ?> - Authentic Nepali Sekuwa</title>
    <meta name="description" content="Experience the smoky, spicy flavors of traditional Nepali grilled meat. Order authentic sekuwa online.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍢</text></svg>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">🍢 <?php echo SITE_NAME; ?></a>
            
            <form action="products.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search our menu...">
                <button type="submit">Search</button>
            </form>
            
            <ul class="nav-links">
                <li><a href="index.php" class="active">Home</a></li>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Authentic Nepali Sekuwa</h1>
                <p>Experience the smoky, spicy flavors of traditional Nepali grilled meat. Marinated with authentic Himalayan spices and grilled to perfection over charcoal.</p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="products.php" class="btn btn-primary btn-large">🔥 Order Now</a>
                    <a href="#about" class="btn btn-outline btn-large">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">Our Menu Categories</h2>
            <div class="categories-grid">
                <?php while ($category = $categories_result->fetch_assoc()): ?>
                    <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card">
                        <div class="category-icon">
                            <?php
                            $icons = [
                                'Chicken Sekuwa' => '🍗',
                                'Buff Sekuwa' => '🥩',
                                'Veg Items' => '🥗',
                                'Drinks' => '🍺'
                            ];
                            echo $icons[$category['name']] ?? '🍽️';
                            ?>
                        </div>
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-section">
        <div class="container">
            <h2 class="section-title">Featured Dishes</h2>
            <div class="products-grid">
                <?php while ($product = $featured_result->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='assets/images/default.jpg'"
                                 loading="lazy">
                            <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?>
                            </p>
                            <div class="product-footer">
                                <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-small btn-primary">View</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="text-center" style="margin-top: 2rem;">
                <a href="products.php" class="btn btn-outline btn-large">View Full Menu →</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="about-content">
                <h2>About Sekuwa Kerner</h2>
                <p>
                    Welcome to Sekuwa Kerner, your destination for authentic Nepali grilled meat delicacies. 
                    Sekuwa is a traditional Nepali dish where marinated meat is grilled over charcoal, 
                    resulting in a smoky, spicy, and incredibly flavorful experience.
                </p>
                <p>
                    Our recipes have been passed down through generations, using authentic Nepali spices 
                    and traditional cooking techniques. Every bite tells a story of the Himalayas.
                </p>
                <div class="features">
                    <div class="feature">
                        <span class="feature-icon">🔥</span>
                        <h4>Charcoal Grilled</h4>
                        <p>Authentic smoky flavor from traditional grilling</p>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">🌶️</span>
                        <h4>Traditional Spices</h4>
                        <p>Authentic Himalayan marinade blend</p>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">🚚</span>
                        <h4>Fast Delivery</h4>
                        <p>Hot and fresh to your doorstep</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>🍢 <?php echo SITE_NAME; ?></h3>
                    <p>Authentic Nepali Sekuwa delivered to your doorstep. Experience the taste of the Himalayas.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Menu</a></li>
                        <li><a href="cart/cart.php">Cart</a></li>
                        <li><a href="auth/login.php">Login</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Us</h4>
                    <p>📍 Thamel, Kathmandu, Nepal</p>
                    <p>📞 +977 9841000000</p>
                    <p>📧 info@sekuwakerner.com</p>
                </div>
                <div class="footer-section">
                    <h4>Hours</h4>
                    <p>Mon - Fri: 10AM - 10PM</p>
                    <p>Sat - Sun: 11AM - 11PM</p>
                    <p>🔥 Always fresh & hot!</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved. | Made with ❤️ in Nepal</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
