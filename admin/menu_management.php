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
$edit_item = null;

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    mysqli_query($connection, "DELETE FROM menu_items WHERE id = $id");
    $message = "Item deleted successfully!";
}

// Handle Add/Update Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_item'])) {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? 'Snacks';
    $item_id = $_POST['item_id'] ?? null;
    $image_path = null;

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../assets/images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $unique_name = uniqid() . '.' . $file_ext;
        $target_file = $upload_dir . $unique_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = 'assets/images/' . $unique_name;
        }
    }

    if ($item_id) {
        // Update existing item
        if ($image_path) {
            $query = "UPDATE menu_items SET name=?, description=?, price=?, category=?, image_path=? WHERE id=?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, "ssdssi", $name, $description, $price, $category, $image_path, $item_id);
        } else {
            $query = "UPDATE menu_items SET name=?, description=?, price=?, category=? WHERE id=?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, "ssdsi", $name, $description, $price, $category, $item_id);
        }
        mysqli_stmt_execute($stmt);
        $message = "Item updated successfully!";
    } else {
        // Create new item
        $query = "INSERT INTO menu_items (name, description, price, category, image_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "ssdss", $name, $description, $price, $category, $image_path);
        mysqli_stmt_execute($stmt);
        $message = "Item added successfully!";
    }
}

// Fetch item for editing if requested
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $res = mysqli_query($connection, "SELECT * FROM menu_items WHERE id = $edit_id");
    $edit_item = mysqli_fetch_assoc($res);
}

// Get all menu items
$query = "SELECT * FROM menu_items ORDER BY id DESC";
$items = mysqli_query($connection, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .item-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .file-upload {
            padding: 10px;
            border: 1px dashed #ccc;
            background: #f9f9f9;
            text-align: center;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    
    <?php include 'admin_navbar.php'; ?>
    
    <div class="container admin-container">
        <div class="page-title">
            <h2>Menu Management</h2>
            <p style="color: var(--medium-gray);">Add, edit, or remove items from the canteen menu.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="form-section">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-color);">
                <?php echo $edit_item ? '✏️ Edit Item' : '➕ Add New Menu Item'; ?>
            </h3>
            
            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_item): ?>
                    <input type="hidden" name="item_id" value="<?php echo $edit_item['id']; ?>">
                <?php endif; ?>

                <div class="grid-2-col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Item Name <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="name" required placeholder="e.g., Chicken Biryani" value="<?php echo $edit_item['name'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Category <span style="color:var(--danger)">*</span></label>
                        <select name="category" required>
                            <option value="Breakfast" <?php echo ($edit_item['category'] ?? '') == 'Breakfast' ? 'selected' : ''; ?>>Breakfast</option>
                            <option value="Lunch" <?php echo ($edit_item['category'] ?? '') == 'Lunch' ? 'selected' : ''; ?>>Lunch</option>
                            <option value="Snacks" <?php echo ($edit_item['category'] ?? '') == 'Snacks' ? 'selected' : ''; ?>>Snacks</option>
                            <option value="Beverages" <?php echo ($edit_item['category'] ?? '') == 'Beverages' ? 'selected' : ''; ?>>Beverages</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="2" placeholder="Brief details..."><?php echo $edit_item['description'] ?? ''; ?></textarea>
                </div>
                
                <div class="grid-2-col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Price (Rs.) <span style="color:var(--danger)">*</span></label>
                        <input type="number" name="price" step="0.01" required placeholder="0.00" value="<?php echo $edit_item['price'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Item Image</label>
                        <input type="file" name="image" accept="image/*" class="file-upload">
                        <?php if (!empty($edit_item['image_path'])): ?>
                            <small>Current: <a href="../<?php echo $edit_item['image_path']; ?>" target="_blank">View Image</a></small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" name="save_item" class="btn btn-primary">
                    <?php echo $edit_item ? '💾 Update Item' : '➕ Add Item'; ?>
                </button>
                
                <?php if ($edit_item): ?>
                    <a href="menu_management.php" class="btn btn-outline">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="form-section">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-color);">Current Menu Items</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($items)): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image_path'] && file_exists("../" . $item['image_path'])): ?>
                                        <img src="../<?php echo $item['image_path']; ?>" class="item-thumb">
                                    <?php else: ?>
                                        <div class="item-thumb" style="display:flex;align-items:center;justify-content:center;background:#eee;">📷</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                    <small style="color:#666;"><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</small>
                                </td>
                                <td><span class="status-badge" style="background:#f0f0f0;color:#333;"><?php echo $item['category']; ?></span></td>
                                <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <a href="?edit=<?php echo $item['id']; ?>" class="btn btn-small btn-primary">✏️ Edit</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this item?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
