<?php
/**
 * =====================================================
 * ADMIN ORDERS MANAGEMENT
 * Sekuwa Kerner - Nepali Food Ordering Platform
 * =====================================================
 * 
 * View and manage all customer orders:
 * - View order details
 * - Update order status
 * - Filter by status
 */

// Include admin authentication
require_once 'admin_auth.php';

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitize($conn, $_GET['status']) : '';
$date_filter = isset($_GET['date']) ? sanitize($conn, $_GET['date']) : '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = sanitize($conn, $_POST['status']);
    
    $valid_statuses = ['Pending', 'Cooking', 'Out for Delivery', 'Delivered', 'Cancelled'];
    
    if (in_array($new_status, $valid_statuses)) {
        $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $order_id);
        
        if ($update_stmt->execute()) {
            // If delivered and cash on delivery, update payment status
            if ($new_status === 'Delivered') {
                $conn->query("UPDATE payments SET payment_status = 'Completed' 
                             WHERE order_id = {$order_id} AND payment_method = 'Cash on Delivery'");
            }
            
            setFlashMessage('success', 'Order status updated successfully!');
        } else {
            setFlashMessage('error', 'Failed to update order status.');
        }
    }
    
    redirect('orders.php' . ($status_filter ? '?status=' . $status_filter : ''));
}

// Build query
$sql = "SELECT o.*, u.name as customer_name, u.email, p.payment_method, p.payment_status 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        LEFT JOIN payments p ON o.id = p.order_id 
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($status_filter)) {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($date_filter)) {
    $sql .= " AND DATE(o.created_at) = ?";
    $params[] = $date_filter;
    $types .= "s";
}

$sql .= " ORDER BY o.created_at DESC";

// Execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result();

// Get all possible statuses for filter
$statuses = ['Pending', 'Cooking', 'Out for Delivery', 'Delivered', 'Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <!-- Admin Navigation -->
    <nav class="admin-navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">🍢 <?php echo SITE_NAME; ?> Admin</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="../index.php" target="_blank">View Site</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php echo displayFlashMessage(); ?>

    <!-- Orders Content -->
    <main class="admin-main">
        <div class="container">
            <div class="admin-header">
                <h1>Manage Orders</h1>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <form action="orders.php" method="GET" class="inline-form">
                    <select name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status; ?>" 
                                    <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                                <?php echo $status; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                    <button type="submit" class="btn btn-small">Filter</button>
                    <a href="orders.php" class="btn btn-outline btn-small">Clear</a>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders->num_rows > 0): ?>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <?php
                                // Get order items count
                                $items_count = $conn->query("SELECT SUM(quantity) as count FROM order_items WHERE order_id = {$order['id']}")->fetch_assoc()['count'];
                                ?>
                                <tr>
                                    <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($order['phone']); ?></small>
                                    </td>
                                    <td><?php echo $items_count; ?> item(s)</td>
                                    <td><?php echo formatPrice($order['total_price']); ?></td>
                                    <td>
                                        <span class="badge"><?php echo htmlspecialchars($order['payment_method']); ?></span><br>
                                        <small class="status-<?php echo strtolower($order['payment_status']); ?>">
                                            <?php echo $order['payment_status']; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <form action="orders.php<?php echo $status_filter ? '?status=' . $status_filter : ''; ?>" 
                                              method="POST" class="status-form">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="status-select status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                                <?php foreach ($statuses as $status): ?>
                                                    <option value="<?php echo $status; ?>" 
                                                            <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                                        <?php echo $status; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-small">Update</button>
                                        </form>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($order['created_at'])); ?><br>
                                        <small><?php echo date('g:i A', strtotime($order['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <button class="btn btn-small btn-outline" 
                                                onclick="showOrderDetails(<?php echo $order['id']; ?>)">
                                            View
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-info">
                <p>Total: <?php echo $orders->num_rows; ?> order(s)</p>
            </div>
        </div>
    </main>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="orderDetails">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="admin-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> Admin Panel | College Project</p>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
    <script>
        // Show order details in modal
        function showOrderDetails(orderId) {
            // Fetch order details via AJAX
            fetch('get_order_details.php?id=' + orderId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('orderDetails').innerHTML = html;
                    document.getElementById('orderModal').style.display = 'block';
                })
                .catch(error => {
                    alert('Error loading order details');
                });
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

