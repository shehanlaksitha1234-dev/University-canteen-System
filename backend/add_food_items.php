<?php
require 'config/db.php';

// Food items with images to add to today's menu
$food_items = [
    [
        'name' => 'Masala Dosa',
        'description' => 'Crispy rice and lentil crepe filled with spiced potato, onion and special masala sauce',
        'price' => 150.00,
        'image_path' => 'assets/images/dosa.jpeg'
    ],
    [
        'name' => 'Vegetable Fried Rice',
        'description' => 'Fragrant jasmine rice stir-fried with mixed vegetables, soy sauce and spices',
        'price' => 200.00,
        'image_path' => 'assets/images/friedrice.jpeg'
    ],
    [
        'name' => 'Idli with Sambar',
        'description' => 'Steamed soft rice cakes served with aromatic vegetable curry and coconut chutney',
        'price' => 120.00,
        'image_path' => 'assets/images/idli.jpeg'
    ],
    [
        'name' => 'Vegetable Biryani',
        'description' => 'Fragrant basmati rice layered with mixed vegetables and aromatic spices',
        'price' => 220.00,
        'image_path' => 'assets/images/rice.jpeg'
    ],
    [
        'name' => 'Spring Roll',
        'description' => 'Crispy pastry rolls filled with vegetables, served with sweet chili sauce',
        'price' => 100.00,
        'image_path' => 'assets/images/roll.jpeg'
    ],
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

// Insert or update food items
foreach ($food_items as $item) {
    // Check if item already exists
    $check_query = "SELECT id FROM menu_items WHERE name = ? AND image_path = ?";
    $check_stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'ss', $item['name'], $item['image_path']);
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

echo "<p><strong>Food items update complete!</strong></p>";
echo "<a href='../frontend/menu.php'>Go to Menu Page</a>";

mysqli_close($connection);
?>
