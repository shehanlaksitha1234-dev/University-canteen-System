<?php
require 'backend/config/db.php';

// 1. Add category column if it doesn't exist
$check_col = mysqli_query($connection, "SHOW COLUMNS FROM menu_items LIKE 'category'");
if (mysqli_num_rows($check_col) == 0) {
    $alter_query = "ALTER TABLE menu_items ADD COLUMN category ENUM('Breakfast', 'Lunch', 'Snacks', 'Beverages') DEFAULT 'Snacks' AFTER image_path";
    if (mysqli_query($connection, $alter_query)) {
        echo "✅ Added 'category' column to menu_items table.<br>";
    } else {
        echo "❌ Failed to add column: " . mysqli_error($connection) . "<br>";
    }
} else {
    echo "ℹ️ 'category' column already exists.<br>";
}

// 2. Update categories for known items
$category_map = [
    'Hot Coffee' => 'Beverages',
    'Masala Tea' => 'Beverages',
    'Veg Samosa (2pcs)' => 'Snacks',
    'Veg Roll' => 'Snacks',
    'Masala Dosa' => 'Breakfast',
    'Idli Sambar (2pcs)' => 'Breakfast',
    'Veg Fried Rice' => 'Lunch',
    'Veg Meals' => 'Lunch'
];

foreach ($category_map as $name => $cat) {
    $update = "UPDATE menu_items SET category = '$cat' WHERE name = '$name'";
    mysqli_query($connection, $update);
}

echo "✅ Updated item categories.<br>";
echo "Redirecting to menu...";
header("refresh:2;url=frontend/menu.php");
?>
