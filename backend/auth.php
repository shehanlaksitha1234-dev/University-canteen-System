<?php
/**
 * AUTHENTICATION FILE - backend/auth.php
 * Handles user registration, login, logout, and session management
 * 
 * SECURITY FEATURES:
 * - password_hash() encrypts passwords (can't be reversed)
 * - Sessions track logged-in users
 * - Password verification with password_verify()
 */

session_start(); // Start session to track logged-in users

// Check if session already exists
if (isset($_SESSION['user_id'])) {
    // User is already logged in
    $logged_in = true;
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $user_role = $_SESSION['user_role'];
}

// ============================================
// USER REGISTRATION
// ============================================
/**
 * This function registers a new user
 * 
 * Parameters:
 * - $name: Student's full name
 * - $email: Student's email address
 * - $faculty: Faculty/Department name
 * - $password: Plain text password (will be hashed)
 * - $connection: Database connection object
 * 
 * Returns:
 * - array with 'success' (true/false) and 'message' (explanation)
 */
function registerUser($name, $email, $faculty, $password, $connection) {
    
    // Validate input
    if (empty($name) || empty($email) || empty($password)) {
        return [
            'success' => false,
            'message' => 'All fields are required'
        ];
    }
    
    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($connection, $check_email);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Database error: ' . mysqli_error($connection)
        ];
    }
    
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        return [
            'success' => false,
            'message' => 'Email already registered'
        ];
    }
    
    // Hash the password (one-way encryption)
    // password_hash() creates a secure hash that can't be reversed
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user into database
    $insert_query = "INSERT INTO users (name, email, faculty, password, role) 
                     VALUES (?, ?, ?, ?, 'student')";
    
    $stmt = mysqli_prepare($connection, $insert_query);
    mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $faculty, $hashed_password);
    
    if (mysqli_stmt_execute($stmt)) {
        return [
            'success' => true,
            'message' => 'Registration successful! Please log in.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Error during registration: ' . mysqli_error($connection)
        ];
    }
}

// ============================================
// USER LOGIN
// ============================================
/**
 * This function logs in a user
 * 
 * Parameters:
 * - $email: User's email address
 * - $password: Plain text password (to verify against hash)
 * - $connection: Database connection object
 * 
 * Returns:
 * - array with 'success' (true/false) and 'message'
 * 
 * HOW IT WORKS:
 * 1. Find user by email
 * 2. Compare provided password with stored hash using password_verify()
 * 3. If match: create session and allow login
 * 4. If not match: deny access
 */
function loginUser($email, $password, $connection) {
    
    if (empty($email) || empty($password)) {
        return [
            'success' => false,
            'message' => 'Email and password required'
        ];
    }
    
    // Find user by email
    $query = "SELECT id, name, password, role FROM users WHERE email = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        return [
            'success' => false,
            'message' => 'Email not found'
        ];
    }
    
    // Get user data
    $user = mysqli_fetch_assoc($result);
    
    // Verify password: password_verify() checks if plain password matches hash
    if (password_verify($password, $user['password'])) {
        
        // Password is correct! Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $email;
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'role' => $user['role']
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Incorrect password'
        ];
    }
}

// ============================================
// USER LOGOUT
// ============================================
/**
 * This function logs out a user by destroying the session
 * 
 * HOW IT WORKS:
 * 1. session_destroy() removes all session data
 * 2. User is no longer logged in
 */
function logoutUser() {
    session_destroy(); // Destroy all session variables
    return [
        'success' => true,
        'message' => 'Logged out successfully'
    ];
}

// ============================================
// CHECK IF USER IS LOGGED IN
// ============================================
/**
 * This function checks if a user is currently logged in
 * 
 * Usage in other pages:
 * if (!isLoggedIn()) {
 *     header('Location: frontend/login.php'); // Redirect to login
 * }
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ============================================
// EXPLANATION FOR BEGINNERS
// ============================================
/**
 * PASSWORD SECURITY:
 * 
 * INCORRECT WAY (Not secure):
 * $password = "12345"; // Stored directly - if hacked, passwords are revealed
 * 
 * CORRECT WAY (Secure with hashing):
 * $password = "12345";
 * $hashed = password_hash($password, PASSWORD_DEFAULT);
 * // $hashed = "$2y$10$abc123xyz..." (different every time, can't reverse)
 * // If database is hacked, only hashes are visible, not actual passwords
 * 
 * VERIFICATION:
 * When user logs in with password "12345":
 * password_verify("12345", $stored_hash) → TRUE
 * password_verify("54321", $stored_hash) → FALSE
 * 
 * SESSIONS:
 * After login, $_SESSION stores user info temporarily
 * Session data is stored on server, not sent to client
 * Browser gets a session ID cookie to identify the user
 * When user logs out, session is destroyed
 */
?>
