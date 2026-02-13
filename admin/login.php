<?php
session_start();

require '../backend/config/db.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff') {
        header('Location: index.php');
    } else {
        header('Location: ../frontend/menu.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and Password are required';
    } else {
        // Find admin or staff user by email
        $query = "SELECT id, name, role, password FROM users WHERE email = ? AND (role = 'admin' OR role = 'staff')";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password (plain text as per current setup, can be upgraded to password_verify later)
            if ($password === $user['password']) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $email;
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Admin account not found';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Canteen System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <img src="../assets/images/logo.png" alt="University Logo" style="width: 80px; height: 80px; object-fit: contain;">
        </div>
        <h1 class="auth-title">Admin Access</h1>
        <p class="auth-subtitle">Please sign in to continue</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="admin@university.com"
                    required
                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password"
                    required
                    style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;"
                >
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                Secure Login
            </button>
        </form>
        
        <div class="auth-link">
            <a href="../index.html">← Back to Canteen Home</a>
        </div>
    </div>
</body>
</html>
