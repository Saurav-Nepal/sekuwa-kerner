<?php
/**
 * =====================================================
 * PLACE ORDER PROCESSING
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * This script processes the order:
 * - Creates order record
 * - Saves order items
 * - Records payment
 * - Clears cart
 * - Redirects to thank you page
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
    setFlashMessage('error', 'Please login to place order.');
    redirect('../auth/login.php');
}

// Check if checkout info exists
if (!isset($_SESSION['checkout']) || !isset($_SESSION['checkout']['payment_method'])) {
    setFlashMessage('error', 'Please complete checkout process.');
    redirect('checkout.php');
}

$checkout = $_SESSION['checkout'];
$user_id = getCurrentUserId();

// Start database transaction
$conn->begin_transaction();

try {
    // 1. Create order record
    $order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, address, phone, status, notes) 
                                  VALUES (?, ?, ?, ?, 'Pending', ?)");
    $order_stmt->bind_param("idsss", 
        $user_id,
        $checkout['total'],
        $checkout['address'],
        $checkout['phone'],
        $checkout['notes']
    );
    $order_stmt->execute();
    $order_id = $conn->insert_id;
    
    // 2. Insert order items
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                 VALUES (?, ?, ?, ?)");
    
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $item_stmt->bind_param("iiid", 
            $order_id,
            $product_id,
            $item['quantity'],
            $item['price']
        );
        $item_stmt->execute();
    }
    
    // 3. Record payment
    $payment_stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, payment_status, transaction_id) 
                                    VALUES (?, ?, ?, ?)");
    $payment_stmt->bind_param("isss",
        $order_id,
        $checkout['payment_method'],
        $checkout['payment_status'],
        $checkout['transaction_id']
    );
    $payment_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // 4. Clear cart and checkout session
    unset($_SESSION['cart']);
    unset($_SESSION['checkout']);
    
    // 5. Store order ID for thank you page
    $_SESSION['last_order_id'] = $order_id;
    
    // Redirect to thank you page
    redirect('../thank_you.php');
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    // Log error
    error_log("Order placement failed: " . $e->getMessage());
    
    setFlashMessage('error', 'Failed to place order. Please try again.');
    redirect('payment.php');
}
?>

