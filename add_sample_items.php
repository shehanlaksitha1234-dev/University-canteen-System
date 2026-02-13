<?php
/**
 * ADD TEST MENU ITEMS
 * This script adds sample menu items to test the cart system
 * Run once, then delete or comment out
 */

require './backend/config/db.php';

// Check if items already exist
$check = mysqli_query($connection, "SELECT COUNT(*) as count FROM menu_items");
$row = mysqli_fetch_assoc($check);

if ($row['count'] > 0) {
    echo "<h2>✅ Menu items already exist (" . $row['count'] . " items)</h2>";
    echo "<p><a href='frontend/menu.php'>Go to Menu</a></p>";
} else {
    echo "<h2>Adding Sample Menu Items...</h2>";
    
    // Sample menu items
    $items = [
        ['Biryani', 'Delicious Pakistani biryani with rice and meat', 250.00, 'assets/images/biryani.jpg'],
        ['Pulao', 'Traditional pulao with fragrant rice', 200.00, 'assets/images/pulao.jpg'],
        ['Chicken Karahi', 'Spicy chicken cooked in karahi', 280.00, 'assets/images/karahi.jpg'],
        ['Nihari', 'Rich and flavorful beef nihari', 280.00, 'assets/images/nihari.jpg'],
        ['Chapli Kebab', 'Spicy beef kebab with herbs', 150.00, 'assets/images/kebab.jpg'],
        ['Dal Makhni', 'Creamy lentils with butter and cream', 120.00, 'assets/images/dal.jpg'],
        ['Coke (500ml)', 'Cold refreshing beverage', 50.00, 'assets/images/coke.jpg'],
        ['Lassi', 'Traditional yogurt drink', 60.00, 'assets/images/lassi.jpg'],
        ['Ice Cream', 'Vanilla ice cream', 80.00, 'assets/images/icecream.jpg'],
    ];
    
    $added = 0;
    foreach ($items as $item) {
        $query = "INSERT INTO menu_items (name, description, price, image_path, is_available) VALUES (?, ?, ?, ?, TRUE)";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "ssds", $item[0], $item[1], $item[2], $item[3]);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p>✅ Added: " . $item[0] . " (Rs. " . $item[2] . ")</p>";
            $added++;
        } else {
            echo "<p>❌ Failed to add: " . $item[0] . "</p>";
        }
        mysqli_stmt_close($stmt);
    }
    
    echo "<h3>✅ Successfully added $added items!</h3>";
    echo "<p><a href='frontend/menu.php'>Go to Menu</a></p>";
}

mysqli_close($connection);
?>
