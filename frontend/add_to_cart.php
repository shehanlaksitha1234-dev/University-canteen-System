<?php
session_start();
require '../backend/config/db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Validate inputs
    if ($item_id > 0 && $quantity > 0) {
        // Query database for item details
        $query = "SELECT id, name, description, price, image_path FROM menu_items WHERE id = ?";
        $stmt = mysqli_prepare($connection, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $item_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $item = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            if ($item) {
                // Initialize cart if it doesn't exist
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = array();
                }
                
                // Check if item already in cart
                if (isset($_SESSION['cart'][$item_id])) {
                    // Increase quantity
                    $_SESSION['cart'][$item_id]['quantity'] += $quantity;
                } else {
                    // Add new item
                    $_SESSION['cart'][$item_id] = array(
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'price' => floatval($item['price']),
                        'image' => $item['image_path'],
                        'quantity' => $quantity
                    );
                }
                
                $_SESSION['success'] = "Item added to cart!";
            } else {
                $_SESSION['error'] = "Item not found!";
            }
        } else {
            $_SESSION['error'] = "Database error!";
        }
    } else {
        $_SESSION['error'] = "Invalid item or quantity!";
    }
}

// Redirect back to menu
header('Location: menu.php');
exit;
?>
