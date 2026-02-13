<?php
session_start();
require '../backend/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$order_placed = false;
$order_id = null;
$cart_total = 0;
$error = '';

// Calculate cart total
foreach ($_SESSION['cart'] as $item_id => $item_data) {
    $cart_total += ($item_data['price'] * $item_data['quantity']);
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Cash';
    
    // Create order in database
    $query = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'Pending')";
    $stmt = mysqli_prepare($connection, $query);
    
    if ($stmt) {
        $user_id = $_SESSION['user_id'];
        mysqli_stmt_bind_param($stmt, "id", $user_id, $cart_total);
        
        if (mysqli_stmt_execute($stmt)) {
            $order_id = mysqli_insert_id($connection);
            
            // Add order items
            $insert_item_query = "INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)";
            $item_stmt = mysqli_prepare($connection, $insert_item_query);
            
            if ($item_stmt) {
                // Prepare statement for updating inventory
                $update_inv = "UPDATE inventory SET quantity_available = quantity_available - ?, quantity_used = quantity_used + ? WHERE menu_item_id = ?";
                $inv_stmt = mysqli_prepare($connection, $update_inv);

                foreach ($_SESSION['cart'] as $item_id => $item_data) {
                    $quantity = $item_data['quantity'];
                    $price = $item_data['price'];
                    
                    // Add to order items
                    mysqli_stmt_bind_param($item_stmt, "iidi", $order_id, $item_id, $quantity, $price);
                    mysqli_stmt_execute($item_stmt);

                    // Update inventory
                    if ($inv_stmt) {
                        mysqli_stmt_bind_param($inv_stmt, "iii", $quantity, $quantity, $item_id);
                        mysqli_stmt_execute($inv_stmt);
                    }
                }
                mysqli_stmt_close($item_stmt);
                if ($inv_stmt) mysqli_stmt_close($inv_stmt);
            }
            
            // Clear cart session
            unset($_SESSION['cart']);
            
            $order_placed = true;
        } else {
            $error = "Failed to place order. Please try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "Database error. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Canteen Management System</title>
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
                <a href="menu.php" class="btn btn-outline">📅 Today's Menu</a>
                <a href="cart.php" class="btn btn-outline">🛒 Cart</a>
                <a href="orders.php" class="btn btn-outline">📋 My Orders</a>
                <a href="../backend/logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>
    </nav>
    
    <!-- MAIN CONTENT -->
    <div class="container checkout-page">
        
        <?php if ($order_placed): ?>
            <!-- ORDER CONFIRMATION MESSAGE -->
            <div class="success-container">
                <div class="success-card">
                    <div class="success-icon">✅</div>
                    <h2>Order Placed Successfully!</h2>
                    <p class="order-id">Order ID: <strong>#<?php echo $order_id; ?></strong></p>
                    <p class="order-message">Your order has been submitted to the canteen staff.</p>
                    
                    <div class="order-details-mini">
                        <p><strong>Status:</strong> <span class="status-badge status-pending">Pending</span></p>
                        <p><strong>Total Amount:</strong> Rs. <?php echo number_format($cart_total, 2); ?></p>
                        <p><strong>Payment Method:</strong> Cash</p>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="order_confirmation.php?order_id=<?php echo $order_id; ?>" 
                           class="btn btn-primary">View Order Details</a>
                        <a href="menu.php" class="btn btn-outline">Back to Menu</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- CHECKOUT FORM -->
            <h2 class="page-title">Review Your Order</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="checkout-grid">
                <!-- ORDER ITEMS -->
                <div class="checkout-items">
                    <h3>Order Items</h3>
                    
                    <div class="order-items-list">
                        <?php 
                        foreach ($_SESSION['cart'] as $item_id => $item_data) {
                            $item_subtotal = $item_data['price'] * $item_data['quantity'];
                        ?>
                            <div class="order-item">
                                <div class="item-details">
                                    <p class="item-name"><?php echo htmlspecialchars($item_data['name']); ?></p>
                                    <p class="item-qty" style="color: #666; font-size: 0.9rem;">
                                        Rs. <?php echo number_format($item_data['price'], 2); ?> x <?php echo $item_data['quantity']; ?>
                                    </p>
                                </div>
                                <p class="item-price">
                                    Rs. <?php echo number_format($item_subtotal, 2); ?>
                                </p>
                            </div>
                        <?php 
                        }
                        ?>
                    </div>
                </div>
                
                <!-- PAYMENT SUMMARY -->
                <div class="checkout-summary">
                    <h3>Payment Summary</h3>
                    
                    <div class="summary-card">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <strong>Rs. <?php echo number_format($cart_total, 2); ?></strong>
                        </div>
                        
                        <div class="summary-row">
                            <span>Tax (0%):</span>
                            <strong>Rs. 0.00</strong>
                        </div>
                        
                        <div class="summary-row">
                            <span>Delivery:</span>
                            <strong>Rs. 0.00</strong>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total:</span>
                            <strong>Rs. <?php echo number_format($cart_total, 2); ?></strong>
                        </div>
                        
                        <form method="POST" class="checkout-form">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select name="payment_method" required>
                                    <option value="Cash">Cash (Pay at Counter)</option>
                                    <option value="Card">Card</option>
                                    <option value="Online">Online Payment</option>
                                </select>
                            </div>
                            
                            <div class="form-group checkbox">
                                <input type="checkbox" id="terms" required>
                                <label for="terms">I agree to the terms and conditions</label>
                            </div>
                            
                            <button type="submit" name="place_order" class="btn btn-primary btn-block">
                                Place Order - Rs. <?php echo number_format($cart_total, 2); ?>
                            </button>
                            
                            <a href="cart.php" class="btn btn-outline btn-block">Edit Cart</a>
                        </form>
                    </div>
                </div>
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
                    <li>Open: 8:00 AM - 6:00 PM</li>
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
