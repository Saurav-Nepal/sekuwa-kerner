<?php
/**
 * =====================================================
 * EDIT PRODUCT
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Form to edit existing product
 */

// Include admin authentication
require_once 'admin_auth.php';

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    setFlashMessage('error', 'Invalid product ID.');
    redirect('products.php');
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    setFlashMessage('error', 'Product not found.');
    redirect('products.php');
}

// Fetch categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Initialize variables with existing data
$name = $product['name'];
$description = $product['description'];
$price = $product['price'];
$category_id = $product['category_id'];
$is_featured = $product['is_featured'];
$is_available = $product['is_available'];
$current_image = $product['image'];
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
    $image_name = $current_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $upload_path = '../assets/images/' . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if not default
                if ($current_image !== 'default.jpg' && file_exists('../assets/images/' . $current_image)) {
                    unlink('../assets/images/' . $current_image);
                }
            } else {
                $errors[] = 'Failed to upload image.';
                $image_name = $current_image;
            }
        } else {
            $errors[] = 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP';
        }
    }
    
    // If no errors, update product
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, 
                                category_id = ?, image = ?, is_featured = ?, is_available = ? 
                                WHERE id = ?");
        $stmt->bind_param("sdsissii", $name, $price, $description, $category_id, $image_name, $is_featured, $is_available, $product_id);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Product updated successfully!');
            redirect('products.php');
        } else {
            $errors[] = 'Failed to update product. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - <?php echo SITE_NAME; ?></title>
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

    <!-- Edit Product Content -->
    <main class="admin-main">
        <div class="container">
            <div class="admin-header">
                <h1>Edit Product</h1>
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

            <!-- Edit Product Form -->
            <div class="form-container">
                <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST" 
                      enctype="multipart/form-data" class="admin-form">
                    
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
                            <small>Leave empty to keep current image</small>
                            <?php if ($current_image): ?>
                                <div class="current-image">
                                    <p>Current image:</p>
                                    <img src="../assets/images/<?php echo htmlspecialchars($current_image); ?>" 
                                         alt="Current" onerror="this.src='../assets/images/default.jpg'">
                                </div>
                            <?php endif; ?>
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
                                       <?php echo $is_available ? 'checked' : ''; ?>>
                                Available
                            </label>
                            <small>Product can be ordered</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="products.php" class="btn btn-outline">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Product</button>
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

