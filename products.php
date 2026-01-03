<?php
/**
 * PRODUCTS LISTING PAGE - Sekuwa Kerner
 * Premium Nepali Food Ordering Platform
 */

session_start();
require_once 'config/db.php';

// Get filter parameters
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search_query = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? sanitize($conn, $_GET['sort']) : 'name';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000;

// Build SQL query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.is_available = 1";

$params = [];
$types = "";

if ($category_filter > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

if (!empty($search_query)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%{$search_query}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$sql .= " AND p.price >= ? AND p.price <= ?";
$params[] = $min_price;
$params[] = $max_price;
$types .= "dd";

switch ($sort_by) {
    case 'price_low': $sql .= " ORDER BY p.price ASC"; break;
    case 'price_high': $sql .= " ORDER BY p.price DESC"; break;
    case 'newest': $sql .= " ORDER BY p.created_at DESC"; break;
    default: $sql .= " ORDER BY p.name ASC";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products_result = $stmt->get_result();

// Fetch categories
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

// Cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

// Current category name
$current_category_name = "All Dishes";
if ($category_filter > 0) {
    $cat_stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $cat_stmt->bind_param("i", $category_filter);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    if ($cat = $cat_result->fetch_assoc()) {
        $current_category_name = $cat['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍢</text></svg>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">🍢 <?php echo SITE_NAME; ?></a>
            
            <form action="products.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search our menu..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">Search</button>
            </form>
            
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php" class="active">Menu</a></li>
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

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><?php echo htmlspecialchars($current_category_name); ?></h1>
            <?php if (!empty($search_query)): ?>
                <p>🔍 Search results for: "<strong><?php echo htmlspecialchars($search_query); ?></strong>"</p>
            <?php else: ?>
                <p>Discover our authentic Nepali grilled delicacies</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <main class="products-page">
        <div class="container">
            <div class="products-layout">
                <!-- Filters Sidebar -->
                <aside class="filters-sidebar">
                    <form action="products.php" method="GET" class="filter-form">
                        <div class="filter-group">
                            <h3>🍽️ Categories</h3>
                            <select name="category" onchange="this.form.submit()">
                                <option value="0">All Categories</option>
                                <?php 
                                $categories_result->data_seek(0);
                                while ($category = $categories_result->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <h3>💰 Price Range</h3>
                            <div class="price-inputs">
                                <input type="number" name="min_price" placeholder="Min" 
                                       value="<?php echo $min_price > 0 ? $min_price : ''; ?>" min="0">
                                <span>to</span>
                                <input type="number" name="max_price" placeholder="Max" 
                                       value="<?php echo $max_price < 10000 ? $max_price : ''; ?>" min="0">
                            </div>
                        </div>

                        <div class="filter-group">
                            <h3>📊 Sort By</h3>
                            <select name="sort" onchange="this.form.submit()">
                                <option value="name" <?php echo $sort_by == 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                                <option value="price_low" <?php echo $sort_by == 'price_low' ? 'selected' : ''; ?>>Price: Low → High</option>
                                <option value="price_high" <?php echo $sort_by == 'price_high' ? 'selected' : ''; ?>>Price: High → Low</option>
                                <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            </select>
                        </div>

                        <?php if (!empty($search_query)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                        <a href="products.php" class="btn btn-outline btn-block" style="margin-top: 0.5rem;">Clear All</a>
                    </form>
                </aside>

                <!-- Products Grid -->
                <div class="products-content">
                    <?php if ($products_result->num_rows > 0): ?>
                        <p class="results-count">
                            🔥 <strong><?php echo $products_result->num_rows; ?></strong> delicious item<?php echo $products_result->num_rows > 1 ? 's' : ''; ?> found
                        </p>
                        <div class="products-grid">
                            <?php while ($product = $products_result->fetch_assoc()): ?>
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
                                            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-small btn-primary">Order</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-products">
                            <h3>😕 No dishes found</h3>
                            <p>Try adjusting your filters or search terms.</p>
                            <a href="products.php" class="btn btn-primary">View All Menu</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
