<?php
require 'config/db.php';

// Food items to add back
$food_items = [
    [
        'name' => 'Samosa (3 pcs)',
        'description' => 'Golden fried pastry pockets filled with spiced potato and peas',
        'price' => 80.00,
        'image_path' => 'assets/images/samosa.jpeg'
    ],
    [
        'name' => 'Black Coffee',
        'description' => 'Hot freshly brewed coffee - strong and aromatic',
        'price' => 60.00,
        'image_path' => 'assets/images/coffee.jpeg'
    ],
    [
        'name' => 'Masala Tea',
        'description' => 'Hot milk tea infused with cardamom, ginger, and spices',
        'price' => 40.00,
        'image_path' => 'assets/images/tea.jpeg'
    ]
];

// Insert food items
foreach ($food_items as $item) {
    // Check if item already exists
    $check_query = "SELECT id FROM menu_items WHERE name = ?";
    $check_stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($check_stmt, 's', $item['name']);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        // Insert new item
        $insert_query = "INSERT INTO menu_items (name, description, price, image_path) VALUES (?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($connection, $insert_query);
        
        if (!$insert_stmt) {
            echo "Error preparing statement: " . mysqli_error($connection) . "<br>";
            continue;
        }
        
        mysqli_stmt_bind_param($insert_stmt, 'ssds', $item['name'], $item['description'], $item['price'], $item['image_path']);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            echo "✅ Added: " . htmlspecialchars($item['name']) . "<br>";
        } else {
            echo "❌ Failed to add " . htmlspecialchars($item['name']) . ": " . mysqli_error($connection) . "<br>";
        }
        
        mysqli_stmt_close($insert_stmt);
    } else {
        echo "ℹ️ Already exists: " . htmlspecialchars($item['name']) . "<br>";
    }
    
    mysqli_stmt_close($check_stmt);
}

echo "<p><strong>All menu items added successfully!</strong></p>";
echo "<a href='../frontend/menu.php'>Go to Menu Page</a>";

mysqli_close($connection);
?>
