<?php
session_start();
require '../backend/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$user_orders = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Canteen Management System</title>
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
                <a href="cart.php" class="btn btn-outline">🛒 Cart</a>
                <a href="../backend/logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>
    </nav>
    
    <!-- MAIN CONTENT -->
    <div class="container orders-page">
        <h2 class="page-title">My Orders</h2>
        
        <?php if (empty($user_orders)): ?>
            <div class="empty-state">
                <p class="empty-icon">📋</p>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders. Let's order some delicious food!</p>
                <a href="menu.php" class="btn btn-primary">Browse Menu</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($user_orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h3>Order #<?php echo $order['id']; ?></h3>
                                <p class="order-date">
                                    <?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                            
                            <div class="order-status-amount">
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                                <p class="order-amount">
                                    Rs. <?php echo number_format($order['total_amount'], 2); ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="order-footer">
                            <a href="order_confirmation.php?order_id=<?php echo $order['id']; ?>" 
                               class="btn btn-outline btn-small">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
