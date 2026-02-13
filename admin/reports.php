<?php
session_start();
require '../backend/config/db.php';

// Check if logged in and has permission (Admin Only)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Get total orders and revenue
$query = "SELECT COUNT(*) as total_orders, SUM(total_amount) as total_revenue FROM orders";
$result = mysqli_fetch_assoc(mysqli_query($connection, $query));
$total_orders = $result['total_orders'] ?? 0;
$total_revenue = $result['total_revenue'] ?? 0;

// Get orders by status
$query = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$statuses = mysqli_query($connection, $query);

// Orders by status
$status_query = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";

// Execute status query
$statuses = mysqli_query($connection, $status_query);

// Top selling items (overall)
$top_items_query = "SELECT m.name, SUM(oi.quantity) as total_qty, COUNT(*) as times_ordered
                   FROM order_items oi
                   JOIN menu_items m ON oi.menu_item_id = m.id
                   GROUP BY m.id
                   ORDER BY total_qty DESC
                   LIMIT 5";
$top_items = mysqli_query($connection, $top_items_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f5f5f5; }
        
        .admin-header {
            background: #8B0000;
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .admin-nav {
            background: white;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .admin-nav a {
            display: inline-block;
            margin-right: 1rem;
            padding: 0.5rem 1rem;
            background: #8B0000;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        
        .admin-nav a:hover { background: #6B0000; }
        
        .logout-btn { background: #ccc !important; color: #333 !important; float: right; }
        
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-box {
            background: white;
            padding: 1.5rem;
            border-left: 4px solid #8B0000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 4px;
        }
        
        .stat-box h3 { color: #8B0000; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .stat-box .number { font-size: 2rem; font-weight: bold; }
    </style>
</head>
<body>
    <div class="admin-header">
        <img src="../assets/images/logo.png" alt="University Logo" class="admin-logo">
        <h1>📊 Reports & Analytics</h1>
    </div>
    
    <div class="admin-nav">
        <a href="index.php">📊 Dashboard</a>
        <a href="menu_management.php">🍴 Add Menu</a>
        <a href="orders.php">📦 Orders</a>
        <a href="inventory.php">📦 Inventory</a>
        <a href="create_admin.php">➕ Create Admin</a>
        <a href="../backend/logout.php" class="logout-btn">🚪 Logout</a>
    </div>
    
    <div class="container">
        <div class="stats">
            <div class="stat-box">
                <h3>Total Orders</h3>
                <div class="number"><?php echo $total_orders; ?></div>
            </div>
            <div class="stat-box">
                <h3>Total Revenue</h3>
                <div class="number">Rs. <?php echo number_format($total_revenue, 2); ?></div>
            </div>
            <div class="stat-box">
                <h3>Avg Order Value</h3>
                <div class="number">Rs. <?php echo $total_orders > 0 ? number_format($total_revenue/$total_orders, 2) : '0.00'; ?></div>
            </div>
        </div>
        
        <h2 style="margin: 2rem 0 1rem;">Orders by Status</h2>
        <div style="background: white; padding: 1.5rem; border-radius: 4px;">
            <?php 
            $status_list = ['Pending', 'Preparing', 'Completed', 'Cancelled'];
            foreach ($status_list as $st):
                $query = "SELECT COUNT(*) as count FROM orders WHERE status = ?";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, "s", $st);
                mysqli_stmt_execute($stmt);
                $count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
            ?>
                <p style="margin: 0.5rem 0;">
                    <strong><?php echo $st; ?>:</strong> <?php echo $count; ?> orders
                </p>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
