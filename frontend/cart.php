<?php
session_start();
require '../backend/config/db.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle cart actions (update quantity, remove item)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $item_id = intval($_POST['item_id'] ?? 0);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if ($action === 'remove' && isset($_SESSION['cart'][$item_id])) {
        unset($_SESSION['cart'][$item_id]);
    } elseif ($action === 'update' && isset($_SESSION['cart'][$item_id])) {
        $new_quantity = intval($_POST['quantity'] ?? 1);
        if ($new_quantity <= 0) {
            unset($_SESSION['cart'][$item_id]);
        } else {
            $_SESSION['cart'][$item_id]['quantity'] = $new_quantity;
        }
    }
    
    header('Location: cart.php');
    exit;
}

// Calculate cart totals
$cart_items = $_SESSION['cart'] ?? [];
$cart_total = 0;

foreach ($cart_items as $item_id => $item) {
    $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
    $price = isset($item['price']) ? floatval($item['price']) : 0;
    $cart_items[$item_id]['subtotal'] = $price * $quantity;
    $cart_total += $cart_items[$item_id]['subtotal'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Canteen Management System</title>
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
                <a href="../backend/logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>
    </nav>
    
    <!-- MAIN CONTENT -->
    <div class="container cart-page">
        <h2 class="page-title">Shopping Cart</h2>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p class="empty-cart-icon">🛒</p>
                <h3>Your cart is empty</h3>
                <p>No items added yet. Let's add some delicious food!</p>
                <a href="menu.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <!-- CART ITEMS TABLE -->
            <div class="cart-items-section">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item_id => $item): ?>
                            <tr class="cart-item">
                                <td class="item-name">
                                    <?php if (!empty($item['image']) && file_exists('../' . $item['image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="cart-item-image">
                                    <?php else: ?>
                                        <div class="cart-item-image placeholder">📷</div>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                </td>
                                
                                <td class="item-price">
                                    Rs. <?php echo number_format($item['price'], 2); ?>
                                </td>
                                
                                <td class="item-quantity">
                                    <form method="POST" class="quantity-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item_id); ?>">
                                        <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" 
                                               min="1" max="10" class="qty-input" onchange="this.form.submit()">
                                    </form>
                                </td>
                                
                                <td class="item-subtotal">
                                    Rs. <?php echo number_format($item['subtotal'], 2); ?>
                                </td>
                                
                                <td class="item-action">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item_id); ?>">
                                        <button type="submit" class="btn btn-danger btn-small">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- CART SUMMARY -->
            <div class="cart-summary-section">
                <div class="summary-card">
                    <h3>Order Summary</h3>
                    
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <strong>Rs. <?php echo number_format($cart_total, 2); ?></strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Delivery Fee:</span>
                        <strong>Rs. 0.00</strong>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total Amount:</span>
                        <strong>Rs. <?php echo number_format($cart_total, 2); ?></strong>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary btn-block">Proceed to Checkout</a>
                    <a href="menu.php" class="btn btn-outline btn-block">Continue Shopping</a>
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
                    <li><a href="signup.php">✍️ Sign Up</a></li>
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
