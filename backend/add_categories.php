<?php
require 'config/db.php';

// First, add category column if it doesn't exist
$alter_query = "ALTER TABLE menu_items ADD COLUMN category VARCHAR(50) DEFAULT 'Other' AFTER image_path";
$alter_result = mysqli_query($connection, $alter_query);

if ($alter_result || strpos(mysqli_error($connection), "Duplicate column") !== false) {
    echo "✅ Category column ready<br>";
} else {
    echo "Note: " . mysqli_error($connection) . "<br>";
}

// Update items with categories
$updates = [
    ['name' => 'Masala Dosa', 'category' => 'Breakfast'],
    ['name' => 'Idli with Sambar', 'category' => 'Breakfast'],
    ['name' => 'Vegetable Fried Rice', 'category' => 'Lunch'],
    ['name' => 'Vegetable Biryani', 'category' => 'Lunch'],
    ['name' => 'Spring Roll', 'category' => 'Lunch'],
    ['name' => 'Samosa (3 pcs)', 'category' => 'Snacks'],
    ['name' => 'Black Coffee', 'category' => 'Beverages'],
    ['name' => 'Masala Tea', 'category' => 'Beverages']
];

foreach ($updates as $update) {
    $update_query = "UPDATE menu_items SET category = ? WHERE name = ?";
    $update_stmt = mysqli_prepare($connection, $update_query);
    
    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, 'ss', $update['category'], $update['name']);
        
        if (mysqli_stmt_execute($update_stmt)) {
            echo "✅ Updated: " . htmlspecialchars($update['name']) . " → " . htmlspecialchars($update['category']) . "<br>";
        } else {
            echo "❌ Failed to update " . htmlspecialchars($update['name']) . "<br>";
        }
        
        mysqli_stmt_close($update_stmt);
    }
}

echo "<p><strong>Categorization complete!</strong></p>";
echo "<a href='../frontend/menu.php'>Go to Menu Page</a>";

mysqli_close($connection);
?>
