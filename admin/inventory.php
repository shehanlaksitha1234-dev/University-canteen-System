<?php
session_start();
require '../backend/config/db.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check role permissions (Only Admin can access)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$message = '';

// Handle inventory update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity'] ?? 0);
    
    // First check if inventory record exists
    $check_query = "SELECT id FROM inventory WHERE menu_item_id = ?";
    $check_stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $item_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Record exists, UPDATE it
        $query = "UPDATE inventory SET quantity_available = ? WHERE menu_item_id = ?";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "ii", $quantity, $item_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ Stock updated successfully!";
        } else {
            $message = "❌ Error updating stock!";
        }
        mysqli_stmt_close($stmt);
    } else {
        // Record doesn't exist, INSERT it
        $query = "INSERT INTO inventory (menu_item_id, quantity_available) VALUES (?, ?)";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "ii", $item_id, $quantity);
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ Stock created and updated successfully!";
        } else {
            $message = "❌ Error creating stock record!";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_stmt_close($check_stmt);
}

// Get all menu items with inventory
$query = "SELECT m.id, m.name, i.quantity_available 
          FROM menu_items m
          LEFT JOIN inventory i ON m.id = i.menu_item_id
          ORDER BY m.name";
$items = mysqli_query($connection, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        input[type="number"] {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 120px;
            text-align: center;
        }
    </style>
</head>
<body>
    
    <?php include 'admin_navbar.php'; ?>
    
    <div class="container admin-container">
        <div class="page-title" style="text-align: left;">
            <h2>Inventory Management</h2>
            <p style="font-size: 1rem; color: var(--medium-gray);">Track and update item stock levels.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, '❌') !== false ? 'alert-danger' : 'alert-success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Available Stock</th>
                        <th>Update Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($items) > 0): ?>
                        <?php while ($item = mysqli_fetch_assoc($items)): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                <td>
                                    <span style="font-weight: bold; font-size: 1.1rem; color: var(--secondary-color);">
                                        <?php echo $item['quantity_available'] ?? 0; ?>
                                    </span> units
                                </td>
                                <td>
                                    <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity_available'] ?? 0; ?>" min="0" required>
                                        <button type="submit" class="btn btn-small btn-primary">
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 2rem;">No inventory items found. Add items to the menu first.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
