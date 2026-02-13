<?php
session_start();

require_once __DIR__ . '/../backend/config/db.php';

// simple admin access check
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'staff'])) {
    header('Location: login.php');
    exit;
}

// Fetch stats
$stats = mysqli_fetch_assoc(mysqli_query($connection, "SELECT
    COUNT(*) AS total_orders,
    SUM(total_amount) AS total_revenue,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_orders
    FROM orders"));

$pending = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS pending FROM orders WHERE status = 'Pending'"));

$recent_query = "SELECT o.id, u.name, o.total_amount, o.status, o.created_at
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 10";
$recent_orders = mysqli_query($connection, $recent_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Dashboard - Canteen System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Dashboard Specific Styles */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border-left: 5px solid var(--primary-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(139, 0, 0, 0.05));
            pointer-events: none;
        }
        
        .stat-title {
            color: var(--secondary-color);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .recent-orders-section {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        th {
            background-color: var(--light-gray);
            color: var(--secondary-color);
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid var(--medium-gray);
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid var(--medium-gray);
            vertical-align: middle;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending { background-color: #FFF3CD; color: #856404; }
        .status-preparing { background-color: #D1ECF1; color: #0C5460; }
        .status-completed { background-color: #D4EDDA; color: #155724; }
        .status-cancelled { background-color: #F8D7DA; color: #721C24; }
    </style>
</head>
<body>
    
    <?php include 'admin_navbar.php'; ?>

    <div class="container" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="page-title" style="text-align: left; margin-bottom: 1rem;">
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>! 👋</h2>
            <p style="font-size: 1rem; color: var(--medium-gray); margin-top: 0.5rem;">Here's what's happening in your canteen today.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Total Orders</div>
                <div class="stat-value"><?php echo $stats['total_orders'] ?? 0; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: var(--warning);">
                <div class="stat-title">Pending Orders</div>
                <div class="stat-value"><?php echo $pending['pending'] ?? 0; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: var(--success);">
                <div class="stat-title">Completed Orders</div>
                <div class="stat-value"><?php echo $stats['completed_orders'] ?? 0; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: var(--info);">
                <div class="stat-title">Total Revenue</div>
                <div class="stat-value">Rs. <?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></div>
            </div>
        </div>

        <div class="recent-orders-section">
            <div class="section-header">
                <h3>Recent Orders</h3>
                <a href="orders.php" class="btn btn-small btn-primary">View All Orders</a>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Student Name</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date & Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($recent_orders) > 0): ?>
                            <?php while ($o = mysqli_fetch_assoc($recent_orders)): ?>
                                <tr>
                                    <td><strong>#<?php echo $o['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($o['name'] ?? 'Unknown'); ?></td>
                                    <td>Rs. <?php echo number_format($o['total_amount'] ?? 0, 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($o['status'] ?? 'pending'); ?>">
                                            <?php echo htmlspecialchars($o['status'] ?? 'Pending'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, h:i A', strtotime($o['created_at'] ?? 'now')); ?></td>
                                    <td>
                                        <a href="orders.php?order_id=<?php echo $o['id']; ?>" class="btn btn-small btn-outline">Manage</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem;">No recent orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
