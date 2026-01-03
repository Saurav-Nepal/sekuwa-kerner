<?php
/**
 * =====================================================
 * ADD NEW PRODUCT
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Form to add new product:
 * - Name, description, price
 * - Category selection
 * - Image upload
 * - Featured/Available flags
 */

// Include admin authentication
require_once 'admin_auth.php';

// Fetch categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Initialize variables
$name = '';
$description = '';
$price = '';
$category_id = '';
$is_featured = 0;
$is_available = 1;
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = sanitize($conn, $_POST['name'] ?? '');
    $description = sanitize($conn, $_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Product name is required.';
    }
    
    if ($price <= 0) {
        $errors[] = 'Price must be greater than 0.';
    }
    
    if ($category_id <= 0) {
        $errors[] = 'Please select a category.';
    }
    
    // Handle image upload
    $image_name = 'default.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $upload_path = '../assets/images/' . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = 'Failed to upload image.';
                $image_name = 'default.jpg';
            }
        } else {
            $errors[] = 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP';
        }
    }
    
    // If no errors, insert product
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products (name, price, description, category_id, image, is_featured, is_available) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsissi", $name, $price, $description, $category_id, $image_name, $is_featured, $is_available);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Product added successfully!');
            redirect('products.php');
        } else {
            $errors[] = 'Failed to add product. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - <?php echo SITE_NAME; ?></title>
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

    <!-- Add Product Content -->
    <main class="admin-main">
        <div class="container">
            <div class="admin-header">
                <h1>Add New Product</h1>
                <a href="products.php" class="btn btn-outline">← Back to Products</a>
            </div>

            <!-- Display errors if any -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Add Product Form -->
            <div class="form-container">
                <form action="add_product.php" method="POST" enctype="multipart/form-data" class="admin-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price (Rs.) *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0"
                                   value="<?php echo htmlspecialchars($price); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php 
                                $categories->data_seek(0);
                                while ($cat = $categories->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Product Image</label>
                            <input type="file" id="image" name="image" accept="image/*">
                            <small>Leave empty to use default image</small>
                        </div>
                    </div>

                    <div class="form-row checkbox-row">
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="is_featured" value="1" 
                                       <?php echo $is_featured ? 'checked' : ''; ?>>
                                Featured Product
                            </label>
                            <small>Show on homepage</small>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="is_available" value="1" 
                                       <?php echo $is_available ? 'checked' : ''; ?> checked>
                                Available
                            </label>
                            <small>Product can be ordered</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="products.php" class="btn btn-outline">Cancel</a>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
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

