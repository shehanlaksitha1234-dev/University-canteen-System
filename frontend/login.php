<?php
session_start();

require '../backend/config/db.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: menu.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $error = 'Email is required';
    } else {
        // Find user by email
        $query = "SELECT id, name, role FROM users WHERE email = ?";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            if ($user['role'] === 'admin' || $user['role'] === 'staff') {
                // Admins and Staff must login via the admin portal with password
                header('Location: ../admin/login.php');
                exit;
            }

            // Students login (no password required)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $email;
            
            header('Location: menu.php');
            exit;
        } else {
            $error = 'Email not found. Please sign up first.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial;
            background: linear-gradient(135deg, #8B0000 0%, #6B0000 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        
        h1 {
            color: #8B0000;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        input:focus {
            outline: none;
            border-color: #8B0000;
            box-shadow: 0 0 0 2px rgba(139, 0, 0, 0.1);
        }
        
        .btn {
            width: 100%;
            background: #8B0000;
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #6B0000;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .link a {
            color: #8B0000;
            text-decoration: none;
            font-weight: 600;
        }
        
        .link a:hover {
            text-decoration: underline;
        }
        .login-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="../assets/images/logo.png" alt="University Logo" class="login-logo">
        <h1>👨‍🎓 Student Login</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="your@university.com"
                    required
                >
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="link">
            Don't have account? <a href="signup.php">Sign Up</a>
        </div>

        <div class="link" style="margin-top:0.5rem;">
            <a href="../index.html">&larr; Back to Home</a>
        </div>
    </div>
</body>
</html>
