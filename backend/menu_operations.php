<?php
/**
 * MENU ITEMS OPERATIONS - backend/menu_operations.php
 * Handles CRUD operations (Create, Read, Update, Delete) for menu items
 * Also manages inventory
 * 
 * CRUD = Create (add), Read (view), Update (edit), Delete (remove)
 */

require 'config/db.php';

// ============================================
// READ ALL MENU ITEMS (Display on menu page)
// ============================================
/**
 * Gets all available menu items from database
 * 
 * Returns: Array of menu items with id, name, description, price, image_path
 * 
 * HOW IT'S USED:
 * - Menu page uses this to display all food items
 * - Shows item name, price, image
 */
function getAllMenuItems($connection) {
    $query = "SELECT id, name, description, price, image_path, is_available 
              FROM menu_items 
              WHERE is_available = TRUE";
    
    $result = mysqli_query($connection, $query);
    $menu_items = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $menu_items[] = $row;
    }
    
    return $menu_items;
}

// ============================================
// READ SINGLE MENU ITEM
// ============================================
function getMenuItemById($item_id, $connection) {
    $query = "SELECT * FROM menu_items WHERE id = ?";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $item_id);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ============================================
// CREATE NEW MENU ITEM (Admin only)
// ============================================
/**
 * Adds a new food item to the menu
 * 
 * Parameters:
 * - $name: Item name (Biryani, Pizza, etc.)
 * - $description: Item details
 * - $price: Cost in Rs.
 * - $image_path: Location of image file
 * - $created_by: Admin user ID who created it
 * - $connection: Database connection
 * 
 * Returns: Success/failure message
 */
function addMenuItem($name, $description, $price, $image_path, $created_by, $connection) {
    
    if (empty($name) || empty($price)) {
        return ['success' => false, 'message' => 'Name and price required'];
    }
    
    $query = "INSERT INTO menu_items (name, description, price, image_path, created_by, is_available) 
              VALUES (?, ?, ?, ?, ?, TRUE)";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'ssdsi', $name, $description, $price, $image_path, $created_by);
    
    if (mysqli_stmt_execute($stmt)) {
        $item_id = mysqli_insert_id($connection); // Get the ID of new item
        
        // Also create inventory record for this item
        $inv_query = "INSERT INTO inventory (menu_item_id, quantity_available) VALUES (?, 0)";
        $inv_stmt = mysqli_prepare($connection, $inv_query);
        mysqli_stmt_bind_param($inv_stmt, 'i', $item_id);
        mysqli_stmt_execute($inv_stmt);
        
        return ['success' => true, 'message' => 'Menu item added'];
    } else {
        return ['success' => false, 'message' => 'Error adding item'];
    }
}

// ============================================
// UPDATE MENU ITEM (Admin only)
// ============================================
/**
 * Edits an existing menu item
 * 
 * Parameters: Similar to addMenuItem
 * 
 * Returns: Success/failure message
 */
function updateMenuItem($item_id, $name, $description, $price, $image_path, $is_available, $connection) {
    
    $query = "UPDATE menu_items 
              SET name = ?, description = ?, price = ?, image_path = ?, is_available = ?
              WHERE id = ?";
    
    $stmt = mysqli_prepare($connection, $query);
    
    // Convert is_available to 1 or 0 for database
    $available_int = $is_available ? 1 : 0;
    mysqli_stmt_bind_param($stmt, 'ssdsii', $name, $description, $price, $image_path, $available_int, $item_id);
    
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'message' => 'Menu item updated'];
    } else {
        return ['success' => false, 'message' => 'Error updating item'];
    }
}

// ============================================
// DELETE MENU ITEM (Admin only)
// ============================================
/**
 * Removes a menu item from the system
 * 
 * Note: This will cascade delete related orders, order items, and inventory
 * (due to ON DELETE CASCADE in database)
 */
function deleteMenuItem($item_id, $connection) {
    
    $query = "DELETE FROM menu_items WHERE id = ?";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $item_id);
    
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'message' => 'Menu item deleted'];
    } else {
        return ['success' => false, 'message' => 'Error deleting item'];
    }
}

// ============================================
// INVENTORY OPERATIONS
// ============================================

/**
 * Update inventory stock quantity
 * 
 * Used when admin updates stock levels
 */
function updateInventory($menu_item_id, $quantity_available, $connection) {
    
    $query = "UPDATE inventory 
              SET quantity_available = ?
              WHERE menu_item_id = ?";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $quantity_available, $menu_item_id);
    
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'message' => 'Inventory updated'];
    } else {
        return ['success' => false, 'message' => 'Error updating inventory'];
    }
}

/**
 * Get inventory status for a specific item
 */
function getInventoryStatus($menu_item_id, $connection) {
    
    $query = "SELECT * FROM inventory WHERE menu_item_id = ?";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $menu_item_id);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ============================================
// CHECK IF ITEM IS AVAILABLE
// ============================================
/**
 * Checks if an item is both in stock and marked as available
 * 
 * Used before adding to cart
 */
function isItemAvailable($menu_item_id, $connection) {
    
    $query = "SELECT m.is_available, i.quantity_available 
              FROM menu_items m
              LEFT JOIN inventory i ON m.id = i.menu_item_id
              WHERE m.id = ?";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $menu_item_id);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $item = mysqli_fetch_assoc($result);
    
    if ($item && $item['is_available'] && $item['quantity_available'] > 0) {
        return true;
    }
    return false;
}

?>
