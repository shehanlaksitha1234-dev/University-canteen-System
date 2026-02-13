<?php
session_start();
require './backend/config/db.php';

echo "<h1>Canteen Cart System - Diagnostic Test</h1>";

// Test 1: Database Connection
echo "<h2>Test 1: Database Connection</h2>";
if ($connection) {
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
    exit;
}

// Test 2: Check menu_items table
echo "<h2>Test 2: Menu Items Table</h2>";
$result = mysqli_query($connection, "SELECT COUNT(*) as count FROM menu_items");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p style='color: green;'>✅ Menu items table exists</p>";
    echo "<p>Total items in table: <strong>" . $row['count'] . "</strong></p>";
    
    if ($row['count'] > 0) {
        echo "<h3>Sample Menu Items:</h3>";
        $items = mysqli_query($connection, "SELECT id, name, price, image_path, is_available FROM menu_items LIMIT 5");
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Image Path</th><th>Available</th></tr>";
        while ($item = mysqli_fetch_assoc($items)) {
            echo "<tr>";
            echo "<td>" . $item['id'] . "</td>";
            echo "<td>" . $item['name'] . "</td>";
            echo "<td>Rs. " . $item['price'] . "</td>";
            echo "<td>" . $item['image_path'] . "</td>";
            echo "<td>" . ($item['is_available'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No menu items found. Please add items to the menu.</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Menu items table error: " . mysqli_error($connection) . "</p>";
}

// Test 3: Check Session
echo "<h2>Test 3: Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test 4: Test cart form submission
echo "<h2>Test 4: Cart Form Submission</h2>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<p style='color: green;'>✅ Form received POST request</p>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
} else {
    echo "<p>Make a POST request to test form submission</p>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='item_id' value='1'>";
    echo "<input type='hidden' name='quantity' value='2'>";
    echo "<button type='submit'>Test Add to Cart</button>";
    echo "</form>";
}

echo "<hr>";
echo "<a href='frontend/menu.php'>Back to Menu</a>";
?>
