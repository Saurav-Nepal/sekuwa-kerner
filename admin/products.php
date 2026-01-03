<?php
/**
 * =====================================================
 * ADMIN PRODUCTS MANAGEMENT
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Lists all products with:
 * - Add new product
 * - Edit product
 * - Delete product
 * - Filter by category
 */

// Include admin authentication
require_once 'admin_auth.php';

// Get filter parameters
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';

// Build query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

$params = [];
$types = "";

if ($category_filter > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

if (!empty($search)) {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%{$search}%";
    $types .= "s";
}

$sql .= " ORDER BY p.created_at DESC";

// Execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Fetch categories for filter
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <!-- Admin Navigation -->
    <nav class="admin-navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">🍢 <?php echo SITE_NAME; ?> Admin</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="products.php" class="active">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="../index.php" target="_blank">View Site</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php echo displayFlashMessage(); ?>

    <!-- Products Content -->
    <main class="admin-main">
        <div class="container">
            <div class="admin-header">
                <h1>Manage Products</h1>
                <a href="add_product.php" class="btn btn-primary">+ Add Product</a>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <form action="products.php" method="GET" class="inline-form">
                    <input type="text" name="search" placeholder="Search products..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <select name="category">
                        <option value="0">All Categories</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="btn btn-small">Filter</button>
                    <a href="products.php" class="btn btn-outline btn-small">Clear</a>
                </form>
            </div>

            <!-- Products Table -->
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Featured</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products->num_rows > 0): ?>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="product-thumb"
                                             onerror="this.src='../assets/images/default.jpg'">
                                    </td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><?php echo formatPrice($product['price']); ?></td>
                                    <td>
                                        <?php if ($product['is_featured']): ?>
                                            <span class="badge badge-yes">Yes</span>
                                        <?php else: ?>
                                            <span class="badge badge-no">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product['is_available']): ?>
                                            <span class="badge badge-yes">Yes</span>
                                        <?php else: ?>
                                            <span class="badge badge-no">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-small btn-outline">Edit</a>
                                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-small btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No products found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-info">
                <p>Total: <?php echo $products->num_rows; ?> product(s)</p>
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

