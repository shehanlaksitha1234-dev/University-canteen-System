<?php
session_start();
require '../backend/config/db.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: menu.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $faculty = $_POST['faculty'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate
    if (empty($name) || empty($email) || empty($faculty) || empty($password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($connection, $check_query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error = 'Email already registered';
        } else {
            // Insert user
            // Storing password as plain text to match current system configuration 
            // (Note: In a production environment, use password_hash)
            $insert_query = "INSERT INTO users (name, email, faculty, password, role) 
                           VALUES (?, ?, ?, ?, 'student')";
            
            $stmt = mysqli_prepare($connection, $insert_query);
            mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $faculty, $password);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Account created successfully!';
                // Clear form data
                $name = $email = $faculty = '';
            } else {
                $error = 'Error creating account: ' . mysqli_error($connection);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Dashboard - Canteen System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="container">
        <div class="auth-card">
            <h1 class="auth-title">Signup Dashboard</h1>
            <p class="auth-subtitle">Join our university canteen</p>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <p style="margin-top: 10px;"><a href="login.php">Go to Login →</a></p>
                </div>
            <?php else: ?>
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            placeholder="Your full name"
                            required
                            value="<?php echo htmlspecialchars($name ?? ''); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="your@university.edu"
                            required
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="faculty">Faculty/Department</label>
                        <select id="faculty" name="faculty" required>
                            <option value="">Select your faculty</option>
                            <option value="Engineering" <?php echo ($faculty ?? '') === 'Engineering' ? 'selected' : ''; ?>>Engineering</option>
                            <option value="Management Sciences" <?php echo ($faculty ?? '') === 'Management Sciences' ? 'selected' : ''; ?>>Management Sciences</option>
                            <option value="Sciences" <?php echo ($faculty ?? '') === 'Sciences' ? 'selected' : ''; ?>>Sciences</option>
                            <option value="Humanities" <?php echo ($faculty ?? '') === 'Humanities' ? 'selected' : ''; ?>>Humanities</option>
                            <option value="Social Sciences" <?php echo ($faculty ?? '') === 'Social Sciences' ? 'selected' : ''; ?>>Social Sciences</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="At least 6 characters"
                            required
                            minlength="6"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Re-enter password"
                            required
                            minlength="6"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                </form>
                
                <div class="auth-link">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>
                
                <div class="auth-link" style="margin-top: 1rem;">
                    <a href="../index.html">← Back to Home</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
