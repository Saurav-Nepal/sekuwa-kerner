<?php
/**
 * =====================================================
 * DELETE PRODUCT
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Handles product deletion
 */

// Include admin authentication
require_once 'admin_auth.php';

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    setFlashMessage('error', 'Invalid product ID.');
    redirect('products.php');
}

// Fetch product to get image name
$stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    setFlashMessage('error', 'Product not found.');
    redirect('products.php');
}

// Delete product
$delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$delete_stmt->bind_param("i", $product_id);

if ($delete_stmt->execute()) {
    // Delete product image if not default
    if ($product['image'] !== 'default.jpg' && file_exists('../assets/images/' . $product['image'])) {
        unlink('../assets/images/' . $product['image']);
    }
    
    setFlashMessage('success', 'Product deleted successfully!');
} else {
    setFlashMessage('error', 'Failed to delete product. It may be referenced in orders.');
}

redirect('products.php');
?>

