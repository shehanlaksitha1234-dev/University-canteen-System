<?php
require 'backend/config/db.php';

$name = 'System Admin';
$email = 'admin@university.com';
$password = 'admin123'; // stored but not used for login logic currently
$role = 'admin';

// Check if exists
$check = mysqli_query($connection, "SELECT id FROM users WHERE email = '$email'");
if (mysqli_num_rows($check) == 0) {
    $query = "INSERT INTO users (name, email, password, role, faculty) VALUES (?, ?, ?, ?, 'Admin')";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $password, $role);
    if (mysqli_stmt_execute($stmt)) {
        echo "✅ Admin user created successfully.<br>";
        echo "Email: $email<br>";
        echo "Role: $role<br>";
    } else {
        echo "❌ Failed to create admin: " . mysqli_error($connection);
    }
} else {
    echo "ℹ️ Admin user already exists. Updating role to be sure...<br>";
    mysqli_query($connection, "UPDATE users SET role='admin' WHERE email='$email'");
    echo "✅ Role updated to Admin.";
}
?>
