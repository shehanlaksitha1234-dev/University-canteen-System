<?php
/**
 * ORDER OPERATIONS - backend/order_operations.php
 * Handles cart, order creation, order status updates
 * 
 * HOW IT WORKS:
 * 1. Student adds items to cart (session-based)
 * 2. Student proceeds to checkout
 * 3. Order is created in database
 * 4. Admin changes order status (Pending → Preparing → Completed)
 */

require 'config/db.php';

// ============================================
// SESSION-BASED CART MANAGEMENT
// ============================================
/**
 * Initialize cart in session if it doesn't exist
 * 
 * Cart structure:
 * $_SESSION['cart'] = [
 *     1 => ['quantity' => 2, 'price' => 250],  // Item ID 1, qty 2, price 250
 *     3 => ['quantity' => 1, 'price' => 150]   // Item ID 3, qty 1, price 150
 * ]
 */
function initializeCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Add item to cart
 * 
 * Parameters:
 * - $item_id: Menu item ID
 * - $quantity: How many of this item
 * - $price: Price per item
 * 
 * If item already in cart, increases quantity
 */
function addToCart($item_id, $quantity, $price) {
    initializeCart();
    
    if (isset($_SESSION['cart'][$item_id])) {
        // Item already in cart, add to quantity
        $_SESSION['cart'][$item_id]['quantity'] += $quantity;
    } else {
        // New item in cart
        $_SESSION['cart'][$item_id] = [
            'quantity' => $quantity,
            'price' => $price
        ];
    }
    
    return ['success' => true, 'message' => 'Added to cart'];
}

/**
 * Remove item from cart
 */
function removeFromCart($item_id) {
    initializeCart();
    
    if (isset($_SESSION['cart'][$item_id])) {
        unset($_SESSION['cart'][$item_id]);
        return ['success' => true, 'message' => 'Removed from cart'];
    }
    
    return ['success' => false, 'message' => 'Item not in cart'];
}

/**
 * Update item quantity in cart
 */
function updateCartQuantity($item_id, $new_quantity) {
    initializeCart();
    
    if ($new_quantity <= 0) {
        return removeFromCart($item_id);
    }
    
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id]['quantity'] = $new_quantity;
        return ['success' => true, 'message' => 'Cart updated'];
    }
    
    return ['success' => false, 'message' => 'Item not in cart'];
}

/**
 * Get cart total
 * 
 * Calculates: Sum of (quantity × price) for all items
 */
function getCartTotal() {
    initializeCart();
    
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['quantity'] * $item['price'];
    }
    
    return $total;
}

/**
 * Clear entire cart
 * 
 * Used after successful checkout
 */
function clearCart() {
    $_SESSION['cart'] = [];
    return ['success' => true, 'message' => 'Cart cleared'];
}

// ============================================
// ORDER CREATION
// ============================================
/**
 * Create order from cart in database
 * 
 * Process:
 * 1. Get total amount from cart
 * 2. Create record in 'orders' table
 * 3. For each item in cart, create record in 'order_items' table
 * 4. Create payment record
 * 5. Clear cart
 * 
 * Returns: Order ID if successful
 */
function createOrder($user_id, $connection) {
    
    initializeCart();
    
    // Check if cart is empty
    if (empty($_SESSION['cart'])) {
        return ['success' => false, 'message' => 'Cart is empty'];
    }
    
    // Calculate total
    $total_amount = getCartTotal();
    
    // Start transaction (all-or-nothing operation)
    mysqli_begin_transaction($connection);
    
    try {
        // Step 1: Create order record
        $order_query = "INSERT INTO orders (user_id, total_amount, status) 
                        VALUES (?, ?, 'Pending')";
        
        $stmt = mysqli_prepare($connection, $order_query);
        mysqli_stmt_bind_param($stmt, 'id', $user_id, $total_amount);
        mysqli_stmt_execute($stmt);
        
        $order_id = mysqli_insert_id($connection); // Get created order ID
        
        // Step 2: Add each item to order_items table
        foreach ($_SESSION['cart'] as $item_id => $item_data) {
            $item_query = "INSERT INTO order_items (order_id, menu_item_id, quantity, price) 
                           VALUES (?, ?, ?, ?)";
            
            $item_stmt = mysqli_prepare($connection, $item_query);
            mysqli_stmt_bind_param(
                $item_stmt, 
                'iiii', 
                $order_id, 
                $item_id, 
                $item_data['quantity'], 
                $item_data['price']
            );
            mysqli_stmt_execute($item_stmt);
        }
        
        // Step 3: Create payment record
        $payment_query = "INSERT INTO payments (order_id, amount, payment_method, status) 
                          VALUES (?, ?, 'Cash', 'Pending')";
        
        $payment_stmt = mysqli_prepare($connection, $payment_query);
        mysqli_stmt_bind_param($payment_stmt, 'id', $order_id, $total_amount);
        mysqli_stmt_execute($payment_stmt);
        
        // Commit transaction (save all changes)
        mysqli_commit($connection);
        
        // Step 4: Clear cart in session
        clearCart();
        
        return [
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $order_id
        ];
        
    } catch (Exception $e) {
        // Rollback if any error (undo all changes)
        mysqli_rollback($connection);
        return ['success' => false, 'message' => 'Error creating order'];
    }
}

// ============================================
// ORDER MANAGEMENT
// ============================================

/**
 * Get all orders for a specific user (student)
 */
function getUserOrders($user_id, $connection) {
    
    $query = "SELECT o.id, o.total_amount, o.status, o.created_at 
              FROM orders o
              WHERE o.user_id = ?
              ORDER BY o.created_at DESC";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $orders = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    return $orders;
}

/**
 * Get order details (all items in an order)
 */
function getOrderDetails($order_id, $connection) {
    
    $query = "SELECT oi.id, oi.menu_item_id, m.name, m.image_path, 
                     oi.quantity, oi.price, (oi.quantity * oi.price) as item_total
              FROM order_items oi
              JOIN menu_items m ON oi.menu_item_id = m.id
              WHERE oi.order_id = ?";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $items = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    return $items;
}

/**
 * Get full order information
 */
function getOrder($order_id, $connection) {
    
    $query = "SELECT o.id, o.user_id, u.name, u.email, o.total_amount, 
                     o.status, o.created_at
              FROM orders o
              JOIN users u ON o.user_id = u.id
              WHERE o.id = ?";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    mysqli_stmt_execute($stmt);
    
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

/**
 * Update order status (Admin only)
 * 
 * Allowed statuses: Pending, Preparing, Completed, Cancelled
 * 
 * Used when admin marks order as "Preparing" or "Completed"
 */
function updateOrderStatus($order_id, $new_status, $connection) {
    
    $allowed_statuses = ['Pending', 'Preparing', 'Completed', 'Cancelled'];
    
    if (!in_array($new_status, $allowed_statuses)) {
        return ['success' => false, 'message' => 'Invalid status'];
    }
    
    $query = "UPDATE orders SET status = ? WHERE id = ?";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'si', $new_status, $order_id);
    
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'message' => 'Order status updated'];
    } else {
        return ['success' => false, 'message' => 'Error updating status'];
    }
}

/**
 * Get all orders (for admin dashboard)
 */
function getAllOrders($connection) {
    
    $query = "SELECT o.id, u.name, u.email, o.total_amount, o.status, o.created_at
              FROM orders o
              JOIN users u ON o.user_id = u.id
              ORDER BY o.created_at DESC";
    
    $result = mysqli_query($connection, $query);
    $orders = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    return $orders;
}

/**
 * EXPLANATION FOR BEGINNERS:
 * 
 * CART FLOW:
 * 1. User selects item and clicks "Add to Cart"
 * 2. JavaScript calls addToCart() via AJAX
 * 3. Cart stored in $_SESSION (server-side, secure)
 * 4. User can view/edit cart before checkout
 * 5. User clicks "Place Order"
 * 6. createOrder() saves cart to database permanently
 * 7. Cart is cleared from session
 * 
 * DATA FLOW:
 * Browser (JavaScript)
 *    ↓ Sends: {item_id: 1, quantity: 2}
 * PHP (addToCart)
 *    ↓ Stores in: $_SESSION['cart'][1] = {quantity: 2}
 * Session Data (Server Memory)
 *    ↓ User clicks Place Order
 * Database (createOrder)
 *    ↓ Saves to: orders and order_items tables
 * MySQL Tables
 *    ↓ Admin views
 * Admin Dashboard
 *    ↓ Changes status
 * Back to Database
 *    ↓ Student sees update
 * Student's Order Status Page
 */
?>
