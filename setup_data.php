<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'backend/config/db.php';

// 1. Create/Reset Admin User
// Using simple password 'admin123' as requested
$admin_email = 'admin@university.com';
$admin_name = 'System Admin';
$admin_password = 'admin123';
$role = 'admin';

// Check if admin exists
$query = "SELECT id FROM users WHERE email = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 's', $admin_email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    // Update existing
    $query = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $admin_password, $admin_email);
    mysqli_stmt_execute($stmt);
    echo "✅ Admin password updated to '$admin_password' for $admin_email<br>";
} else {
    // Create new
    $query = "INSERT INTO users (name, email, role, faculty, password, created_at) VALUES (?, ?, ?, 'Admin', ?, NOW())";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'ssss', $admin_name, $admin_email, $role, $admin_password);
    mysqli_stmt_execute($stmt);
    echo "✅ Created new admin: $admin_email / $admin_password<br>";
}

// 2. Populate Menu Based on Assets
// Map of filename keywords to menu item details
// Clear existing menu to ensure clean state
mysqli_query($connection, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($connection, "TRUNCATE TABLE menu_items");
mysqli_query($connection, "SET FOREIGN_KEY_CHECKS = 1");
echo "🗑️ Cleared existing menu items.<br>";

// Define categories for items
$menu_items = [
    'coffee' => ['name' => 'Hot Coffee', 'price' => 20.00, 'desc' => 'Freshly brewed hot coffee to kickstart your day.', 'cat' => 'Beverages'],
    'tea' => ['name' => 'Masala Tea', 'price' => 15.00, 'desc' => 'Traditional masala chai with spices.', 'cat' => 'Beverages'],
    'samosa' => ['name' => 'Veg Samosa (2pcs)', 'price' => 30.00, 'desc' => 'Crispy fried pastry with spiced potato filling.', 'cat' => 'Snacks'],
    'dosa' => ['name' => 'Masala Dosa', 'price' => 60.00, 'desc' => 'Crispy rice crepe filled with spiced potatoes, served with chutney.', 'cat' => 'Breakfast'],
    'idli' => ['name' => 'Idli Sambar (2pcs)', 'price' => 40.00, 'desc' => 'Steamed rice cakes served with lentil soup and chutney.', 'cat' => 'Breakfast'],
    'friedrice' => ['name' => 'Veg Fried Rice', 'price' => 80.00, 'desc' => 'Wok-tossed rice with fresh vegetables and chinese sauces.', 'cat' => 'Lunch'],
    'rice' => ['name' => 'Veg Meals', 'price' => 90.00, 'desc' => 'Full meal with rice, sambar, rasam, curries and curd.', 'cat' => 'Lunch'],
    'roll' => ['name' => 'Veg Roll', 'price' => 50.00, 'desc' => 'Spiced vegetables wrapped in a soft roti.', 'cat' => 'Snacks'],
];

foreach ($menu_items as $key => $details) {
    $query = "INSERT INTO menu_items (name, description, price, image_path, category, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($connection, $query);
    $image_path = "assets/images/" . $key . ".jpeg"; 
    mysqli_stmt_bind_param($stmt, 'ssdss', $details['name'], $details['desc'], $details['price'], $image_path, $details['cat']);
    
    if (mysqli_stmt_execute($stmt)) {
            echo "✅ Added: " . $details['name'] . " (" . $details['cat'] . ")<br>";
    } else {
            echo "❌ Failed to add: " . $details['name'] . " - " . mysqli_error($connection) . "<br>";
    }
}

echo "<br><strong>Setup Complete!</strong> You can now login at <a href='admin/login.php'>admin/login.php</a>";
?>
