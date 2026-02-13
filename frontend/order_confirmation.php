<?php
session_start();
require '../backend/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['order_id'] ?? 0;
if (!$order_id) {
    header('Location: orders.php');
    exit;
}

// Get order details
$query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'ii', $order_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get user details
$user_query = "SELECT name, email FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($connection, $user_query);
mysqli_stmt_bind_param($user_stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($user_stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($user_stmt));
mysqli_stmt_close($user_stmt);

// Get order items with menu item details
$items_query = "SELECT oi.*, mi.name, mi.image_path FROM order_items oi 
                LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id 
                WHERE oi.order_id = ?";
$items_stmt = mysqli_prepare($connection, $items_query);
mysqli_stmt_bind_param($items_stmt, 'i', $order_id);
mysqli_stmt_execute($items_stmt);
$order_items_result = mysqli_stmt_get_result($items_stmt);
$order_items = mysqli_fetch_all($order_items_result, MYSQLI_ASSOC);
mysqli_stmt_close($items_stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Canteen Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
</head>
<body>
    <!-- NAVIGATION BAR -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <img src="../assets/images/logo.png" alt="University Logo" class="navbar-logo">
                <h1>🍽️ University Canteen</h1>
            </div>
            
            <div class="navbar-menu">
                <a href="menu.php" class="btn btn-primary">📅 Today's Menu</a>
                <a href="orders.php" class="btn btn-outline">📋 My Orders</a>
                <a href="cart.php" class="btn btn-outline">🛒 Cart</a>
                <a href="../backend/logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>
    </nav>
    
    <!-- MAIN CONTENT -->
    <div class="container confirmation-page">
        <h2 class="page-title">Order Details</h2>
        
        <!-- ORDER STATUS CARD -->
        <div class="order-status-card">
            <div class="status-header">
                <h3>Order #<?php echo $order['id']; ?></h3>
                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                    <?php echo ucfirst($order['status']); ?>
                </span>
            </div>
            
            <div class="status-timeline">
                <div class="timeline-item <?php echo in_array($order['status'], ['Preparing', 'Completed']) ? 'active' : ''; ?>">
                    <div class="timeline-dot">📋</div>
                    <p>Order Received</p>
                </div>
                
                <div class="timeline-item <?php echo $order['status'] === 'Preparing' || $order['status'] === 'Completed' ? 'active' : ''; ?>">
                    <div class="timeline-dot">👨‍🍳</div>
                    <p>Preparing</p>
                </div>
                
                <div class="timeline-item <?php echo $order['status'] === 'Completed' ? 'active' : ''; ?>">
                    <div class="timeline-dot">✅</div>
                    <p>Completed</p>
                </div>
            </div>
            
            <div class="order-info">
                <p><strong>Order Date:</strong> <?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?></p>
                <p><strong>Student Name:</strong> <?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
            </div>
        </div>
        
        <!-- ORDER ITEMS -->
        <div class="order-details-card">
            <h3>Items Ordered</h3>
            
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price x Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($order_items)):
                        foreach ($order_items as $item): 
                            $item_subtotal = $item['price'] * $item['quantity'];
                    ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['image_path']) && file_exists('../' . $item['image_path'])): ?>
                                    <img src="../<?php echo htmlspecialchars($item['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="order-item-image">
                                <?php endif; ?>
                                <?php echo htmlspecialchars($item['name'] ?? 'Item'); ?>
                            </td>
                            <td>Rs. <?php echo number_format($item['price'], 2); ?> * <?php echo $item['quantity']; ?></td>
                            <td>Rs. <?php echo number_format($item_subtotal, 2); ?></td>
                        </tr>
                    <?php 
                        endforeach;
                    else:
                    ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: #999;">No items in this order</td>
                        </tr>
                    <?php 
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- PAYMENT DETAILS -->
        <div class="payment-card">
            <h3>Payment Details</h3>
            
            <div class="payment-info">
                <div class="payment-row">
                    <span>Subtotal:</span>
                    <strong>Rs. <?php echo number_format($order['total_amount'], 2); ?></strong>
                </div>
                
                <div class="payment-row">
                    <span>Tax:</span>
                    <strong>Rs. 0.00</strong>
                </div>
                
                <div class="payment-row total">
                    <span>Total Amount:</span>
                    <strong>Rs. <?php echo number_format($order['total_amount'], 2); ?></strong>
                </div>
                
                <div class="payment-method">
                    <p><strong>Payment Method:</strong> Cash (Pay at Counter)</p>
                    <p><strong>Status:</strong> <span class="status-badge status-pending">Pending</span></p>
                </div>
            </div>
        </div>
        
        <!-- ACTION BUTTONS -->
        <div class="action-section">
            <a href="orders.php" class="btn btn-outline">← Back to Orders</a>
            <a href="menu.php" class="btn btn-primary">Order Again</a>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <!-- About Section -->
            <div class="footer-section">
                <h3>🍽️ About Canteen</h3>
                <ul>
                    <li>University of Vavuniya Canteen</li>
                    <li>Serving quality meals since 2024</li>
                    <li>Open: 11:00 AM - 3:00 PM</li>
                    <li>Monday to Friday</li>
                    <li>Fresh & Healthy Food Options</li>
                </ul>
            </div>
            
            <!-- Contact Section -->
            <div class="footer-section">
                <h3>📞 Contact Us</h3>
                <div class="footer-contact-info">
                    <strong>Phone:</strong>
                    <span>+94 76 XXX XXXX</span>
                </div>
                <div class="footer-contact-info">
                    <strong>Email:</strong>
                    <span>canteen@univavuniya.edu.lk</span>
                </div>
                <div class="footer-contact-info">
                    <strong>Location:</strong>
                    <span>University of Vavuniya Campus</span>
                </div>
                <div class="footer-contact-info">
                    <strong>Address:</strong>
                    <span>Vavuniya, Sri Lanka</span>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-section">
                <h3>🔗 Quick Links</h3>
                <ul>
                    <li><a href="menu.php">📅 Today's Menu</a></li>
                    <li><a href="cart.php">🛒 My Cart</a></li>
                    <li><a href="orders.php">📋 My Orders</a></li>
                    <li><a href="login.php">🔐 Login</a></li>
                    <li><a href="signup.php">✏️ Sign Up</a></li>
                </ul>
            </div>
            
            <!-- Important Info -->
            <div class="footer-section">
                <h3>ℹ️ Information</h3>
                <ul>
                    <li>✅ Safe & Hygienic Food</li>
                    <li>✅ Student-Friendly Prices</li>
                    <li>✅ Easy Online Ordering</li>
                    <li>✅ Quick Pickup Service</li>
                    <li>✅ Cash Payment Available</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-divider"></div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 University of Vavuniya Canteen. All rights reserved.</p>
            <p>Managed by Canteen Management System</p>
            <div class="footer-social">
                <a href="#" title="Facebook">f</a>
                <a href="#" title="WhatsApp">W</a>
                <a href="#" title="Email">✉</a>
            </div>
        </div>
    </footer>
</body>
</html>
