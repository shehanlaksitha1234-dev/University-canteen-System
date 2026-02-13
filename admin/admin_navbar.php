<?php
// Get current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <div class="container">
        <a href="index.php" class="navbar-brand">
            <img src="../assets/images/logo.png" alt="University CanTeen" class="navbar-logo">
            <h1>Admin Portal</h1>
        </a>
        <div class="navbar-menu">
            <a href="index.php" class="btn <?php echo $current_page == 'index.php' ? 'btn-primary' : 'btn-outline'; ?>">
                📊 Dashboard
            </a>
            
            <a href="orders.php" class="btn <?php echo $current_page == 'orders.php' ? 'btn-primary' : 'btn-outline'; ?>">
                📦 Orders
            </a>

            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="menu_management.php" class="btn <?php echo $current_page == 'menu_management.php' ? 'btn-primary' : 'btn-outline'; ?>">
                    🍴 Menu
                </a>
                <a href="inventory.php" class="btn <?php echo $current_page == 'inventory.php' ? 'btn-primary' : 'btn-outline'; ?>">
                    🏗️ Inventory
                </a>
                <a href="create_admin.php" class="btn <?php echo $current_page == 'create_admin.php' ? 'btn-primary' : 'btn-outline'; ?>">
                    ➕ Admins
                </a>
            <?php endif; ?>
            <a href="../backend/logout.php" class="btn btn-danger">
                🚪 Logout
            </a>
        </div>
    </div>
</nav>
