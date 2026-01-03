<?php
/**
 * =====================================================
 * GET ORDER DETAILS (AJAX)
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * Returns HTML for order details modal
 */

// Include admin authentication
require_once 'admin_auth.php';

// Get order ID
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    echo '<p class="error">Invalid order ID</p>';
    exit;
}

// Fetch order details
$order_stmt = $conn->prepare("SELECT o.*, u.name as customer_name, u.email, u.phone as user_phone,
                              p.payment_method, p.payment_status, p.transaction_id 
                              FROM orders o 
                              JOIN users u ON o.user_id = u.id 
                              LEFT JOIN payments p ON o.id = p.order_id 
                              WHERE o.id = ?");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    echo '<p class="error">Order not found</p>';
    exit;
}

// Fetch order items
$items_stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.image 
                              FROM order_items oi 
                              JOIN products p ON oi.product_id = p.id 
                              WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result();
?>

<h2>Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></h2>

<div class="order-details-grid">
    <!-- Customer Info -->
    <div class="detail-section">
        <h3>Customer Information</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
    </div>

    <!-- Delivery Info -->
    <div class="detail-section">
        <h3>Delivery Information</h3>
        <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
        <?php if (!empty($order['notes'])): ?>
            <p><strong>Notes:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
        <?php endif; ?>
    </div>

    <!-- Payment Info -->
    <div class="detail-section">
        <h3>Payment Information</h3>
        <p><strong>Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
        <p><strong>Status:</strong> 
            <span class="status-badge status-<?php echo strtolower($order['payment_status']); ?>">
                <?php echo $order['payment_status']; ?>
            </span>
        </p>
        <?php if ($order['transaction_id']): ?>
            <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id']); ?></p>
        <?php endif; ?>
    </div>

    <!-- Order Info -->
    <div class="detail-section">
        <h3>Order Status</h3>
        <p><strong>Status:</strong> 
            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                <?php echo $order['status']; ?>
            </span>
        </p>
        <p><strong>Placed:</strong> <?php echo date('M j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
        <p><strong>Updated:</strong> <?php echo date('M j, Y \a\t g:i A', strtotime($order['updated_at'])); ?></p>
    </div>
</div>

<!-- Order Items -->
<div class="order-items-section">
    <h3>Order Items</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                             alt="" class="item-thumb" onerror="this.src='../assets/images/default.jpg'">
                        <?php echo htmlspecialchars($item['product_name']); ?>
                    </td>
                    <td><?php echo formatPrice($item['price']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong><?php echo formatPrice($order['total_price']); ?></strong></td>
            </tr>
        </tfoot>
    </table>
</div>

