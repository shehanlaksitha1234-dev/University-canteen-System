<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'backend/config/db.php';

$email = 'admin@university.com';

echo "<h2>Debug Admin Login</h2>";
echo "Checking for email: <strong>$email</strong><br><br>";

// 1. Check direct query
$result = mysqli_query($connection, "SELECT * FROM users WHERE email = '$email'");
if ($result) {
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        echo "✅ User found!<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Role: " . $user['role'] . "<br>";
        
        if ($user['role'] === 'admin' || $user['role'] === 'staff') {
            echo "✅ Role is valid (admin/staff). Login SHOULD work.<br>";
        } else {
            echo "❌ Role is invalid. Expected 'admin' or 'staff', got '" . $user['role'] . "'.<br>";
        }
    } else {
        echo "❌ User NOT found in database.<br>";
    }
} else {
    echo "❌ Query failed: " . mysqli_error($connection) . "<br>";
}

echo "<br><h3>All Users in Database:</h3>";
$all = mysqli_query($connection, "SELECT id, name, email, role FROM users");
echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
while ($row = mysqli_fetch_assoc($all)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . $row['role'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
