<?php
session_start();
require '../backend/config/db.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get all menu items grouped by category
$query = "SELECT id, name, description, price, image_path, category FROM menu_items ORDER BY FIELD(category, 'Breakfast', 'Lunch', 'Snacks', 'Beverages'), id";
$result = mysqli_query($connection, $query);

if (!$result) {
    $menu_items = array();
} else {
    $menu_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Initialize cart counters to avoid undefined variable warnings
$cart_count = 0;
$cart_total = 0.00;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $cid => $citem) {
        $qty = isset($citem['quantity']) ? (int)$citem['quantity'] : 0;
        $price = isset($citem['price']) ? (float)$citem['price'] : 0.0;
        $cart_count += $qty;
        $cart_total += $price * $qty;
    }
}

// Set timezone to Pakistan time (UTC+5)
date_default_timezone_set('Asia/Karachi');

// Determine canteen open state (11:00 AM - 3:00 PM)
$current_time = time();
$current_hour = (int)date('H', $current_time);
$current_minute = (int)date('i', $current_time);
$current_time_total = $current_hour * 60 + $current_minute; // Convert to minutes for easy comparison

$opening_hour = 6;      // 6:00 AM
$opening_minute = 0;
$opening_time_total = $opening_hour * 60 + $opening_minute;

$closing_hour = 18;      // 6:00 PM
$closing_minute = 0;
$closing_time_total = $closing_hour * 60 + $closing_minute;

// Check if canteen is open
$canteen_open = ($current_time_total >= $opening_time_total && $current_time_total < $closing_time_total);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Canteen Management System</title>
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
                <span class="welcome-text">Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                
                <a href="cart.php" class="btn btn-outline">
                    🛒 Cart <span class="cart-badge"><?php echo $cart_count; ?></span>
                </a>
                
                <a href="orders.php" class="btn btn-outline">📋 My Orders</a>
                
                <a href="../backend/logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>
    </nav>
    
    <!-- CANTEEN STATUS BANNER -->
    <?php if (!$canteen_open): ?>
        <div class="container">
            <div class="alert alert-warning">
                <strong>⏰ Canteen is Currently CLOSED</strong>
                <p>Operating Hours: 6:00 AM - 6:00 PM</p>
                <p style="font-size: 0.9rem;">Current Time: <?php echo date('g:i A'); ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="alert alert-success">
                <strong>✅ Canteen is OPEN</strong>
                <p>Operating Hours: 6:00 AM - 6:00 PM</p>
                <p style="font-size: 0.9rem;">Current Time: <?php echo date('g:i A'); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- ERROR MESSAGE -->
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="container">
            <div class="alert alert-danger">
                ❌ <?php echo htmlspecialchars($_SESSION['error']); ?>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- SUCCESS MESSAGE -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="container">
            <div class="alert alert-success">
                ✅ <?php echo htmlspecialchars($_SESSION['success']); ?>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <!-- MAIN CONTENT -->
    <div class="container menu-page">
        <h2 class="page-title">Today's Menu</h2>
        
        <?php if (empty($menu_items)): ?>
            <div class="alert alert-info">
                No food items available at the moment. Please check back later.
            </div>
        <?php else: ?>
            <?php 
            // Group items by category
            $categories = [];
            $category_emojis = [
                'Breakfast' => '🌅',
                'Lunch' => '🍛',
                'Snacks' => '🥪',
                'Beverages' => '☕'
            ];
            
            foreach ($menu_items as $item) {
                $cat = $item['category'] ?? 'Other';
                if (!isset($categories[$cat])) {
                    $categories[$cat] = [];
                }
                $categories[$cat][] = $item;
            }
            
            // Display each category
            foreach (['Breakfast', 'Lunch', 'Snacks', 'Beverages'] as $cat):
                if (isset($categories[$cat]) && !empty($categories[$cat])):
                    $emoji = $category_emojis[$cat] ?? '🍽️';
            ?>
                
                <!-- CATEGORY SECTION -->
                <div class="category-section">
                    <h3 class="category-title"><?php echo $emoji; ?> <?php echo $cat; ?></h3>
                    
                    <div class="menu-grid">
                        <?php foreach ($categories[$cat] as $item): ?>
                            <div class="food-card">
                                <!-- FOOD IMAGE -->
                                <div class="food-image">
                                    <?php if ($item['image_path'] && file_exists('../' . $item['image_path'])): ?>
                                        <img src="../<?php echo htmlspecialchars($item['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image">📷 No Image</div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- FOOD DETAILS -->
                                <div class="food-details">
                                    <h3 class="food-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    
                                    <?php if ($item['description']): ?>
                                        <p class="food-description">
                                            <?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="food-footer">
                                        <span class="food-price">
                                            Rs. <?php echo number_format($item['price'], 2); ?>
                                        </span>
                                        
                                        <!-- ADD TO CART FORM -->
                                        <form method="POST" action="add_to_cart.php" class="add-to-cart-form">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            
                                            <div class="quantity-input">
                                                <button type="button" class="qty-btn" onclick="decreaseQty(this)">−</button>
                                                <input type="number" name="quantity" value="1" min="1" max="10" class="qty-field">
                                                <button type="button" class="qty-btn" onclick="increaseQty(this)">+</button>
                                            </div>
                                            
                                            <button 
                                                type="submit" 
                                                class="btn btn-primary add-cart-btn"
                                                <?php echo !$canteen_open ? 'disabled' : ''; ?>
                                            >
                                                🛒 Add
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
                endif;
            endforeach; 
            ?>
        <?php endif; ?>
    </div>
    
    <!-- FLOATING CART SUMMARY -->
    <?php if ($cart_count > 0): ?>
        <div class="cart-summary-floating">
            <p><?php echo $cart_count; ?> item<?php echo $cart_count !== 1 ? 's' : ''; ?> in cart - 
               Total: <strong>Rs. <?php echo number_format($cart_total, 2); ?></strong></p>
            <a href="cart.php" class="btn btn-primary">View Cart & Checkout</a>
        </div>
    <?php endif; ?>
    
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
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
