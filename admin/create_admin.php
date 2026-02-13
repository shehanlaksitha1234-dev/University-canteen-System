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
$edit_user = null;

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    // Prevent deleting yourself
    if ($delete_id != $_SESSION['user_id']) {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ User deleted successfully!";
        } else {
            $message = "❌ Error deleting user: " . mysqli_error($connection);
        }
    } else {
        $message = "❌ You cannot delete your own account!";
    }
}

// Handle Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $password = $_POST['password'] ?? '';
    $user_id = $_POST['user_id'] ?? null;
    
    if ($name && $email) {
        if ($user_id) {
            // Update Existing User
            if (!empty($password)) {
                // Update with password
                $query = "UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $role, $password, $user_id);
            } else {
                // Update without changing password
                $query = "UPDATE users SET name=?, email=?, role=? WHERE id=?";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $role, $user_id);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "✅ User account updated!";
            } else {
                $message = "❌ Error updating user: " . mysqli_error($connection);
            }
        } else {
            // Create New User
            // Check if email exists
            $check = mysqli_query($connection, "SELECT id FROM users WHERE email = '$email'");
            if (mysqli_num_rows($check) == 0) {
                $query = "INSERT INTO users (name, email, faculty, role, password, created_at) 
                          VALUES (?, ?, 'Admin', ?, ?, NOW())";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $role, $password);
                
                if (mysqli_stmt_execute($stmt)) {
                    $message = "✅ New " . ucfirst($role) . " account created!";
                } else {
                    $message = "❌ Error creating user: " . mysqli_error($connection);
                }
            } else {
                $message = "❌ Application with this email already exists!";
            }
        }
    }
}

// Check if Editing
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $res = mysqli_query($connection, "SELECT * FROM users WHERE id = $edit_id");
    $edit_user = mysqli_fetch_assoc($res);
}

// Get all admins/staff
$query = "SELECT id, name, email, role, created_at FROM users WHERE role IN ('admin', 'staff') ORDER BY created_at DESC";
$users = mysqli_query($connection, $query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    
    <?php include 'admin_navbar.php'; ?>
    
    <div class="container admin-container">
        <div class="page-title" style="text-align: left;">
            <h2>User Management</h2>
            <p style="font-size: 1rem; color: var(--medium-gray);">Create, update, and remove admin and staff accounts.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, '❌') !== false ? 'alert-danger' : 'alert-success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-section">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-color);">
                <?php echo $edit_user ? '✏️ Edit User' : '➕ Add New User'; ?>
            </h3>
            
            <form method="POST">
                <?php if ($edit_user): ?>
                    <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                <?php endif; ?>

                <div class="grid-2-col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="name">Full Name <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="name" name="name" required placeholder="John Smith" value="<?php echo $edit_user['name'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email <span style="color:var(--danger)">*</span></label>
                        <input type="email" id="email" name="email" required placeholder="john@example.com" value="<?php echo $edit_user['email'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="grid-2-col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="role">Role <span style="color:var(--danger)">*</span></label>
                        <select id="role" name="role">
                            <option value="admin" <?php echo ($edit_user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin (Full Access)</option>
                            <option value="staff" <?php echo ($edit_user['role'] ?? '') === 'staff' ? 'selected' : ''; ?>>Staff (Limited Access)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="password">Password <?php echo $edit_user ? '(Leave blank to keep current)' : '<span style="color:var(--danger)">*</span>'; ?></label>
                        <input type="password" id="password" name="password" placeholder="Enter secure password" <?php echo $edit_user ? '' : 'required'; ?>>
                    </div>
                </div>
                
                <button type="submit" name="save_user" class="btn btn-primary">
                    <?php echo $edit_user ? '💾 Update Account' : '✨ Create Account'; ?>
                </button>
                
                <?php if ($edit_user): ?>
                    <a href="create_admin.php" class="btn btn-outline">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="form-section">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-color);">Current Admin/Staff</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($users) > 0): ?>
                            <?php while ($u = mysqli_fetch_assoc($users)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <span class="status-badge" style="background-color: <?php echo $u['role'] == 'admin' ? '#E8F5E9' : '#FFF3E0'; ?>; color: <?php echo $u['role'] == 'admin' ? '#2E7D32' : '#EF6C00'; ?>">
                                            <?php echo ucfirst($u['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $u['id']; ?>" class="btn btn-small btn-primary">✏️ Edit</a>
                                        <?php if ($u['id'] != $_SESSION['user_id']): // Prevent self-delete ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-small btn-danger">🗑️</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem;">No admin users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
