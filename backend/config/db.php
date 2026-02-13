<?php
/**
 * DATABASE CONNECTION FILE - config/db.php
 * This file handles all connections to the MySQL database
 * 
 * HOW IT WORKS:
 * 1. Stores database credentials (host, username, password, database name)
 * 2. Creates a connection using mysqli_connect()
 * 3. If connection fails, shows an error message
 * 4. If successful, all other PHP files can use this connection
 * 
 * USAGE:
 * Include this file in any PHP file that needs database access:
 * require 'backend/config/db.php';
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================
// Only define if not already defined (prevents "already defined" warnings)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');      // Server address (localhost for XAMPP)
    define('DB_USER', 'root');           // MySQL username (default for XAMPP)
    define('DB_PASS', '');               // MySQL password (empty by default for XAMPP)
    define('DB_NAME', 'canteen_db');     // Database name we created
}

// ============================================
// CREATE DATABASE CONNECTION
// ============================================
// mysqli_connect() creates a connection to MySQL server
if (!isset($connection) || !$connection) {
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
}

// Check if connection was successful
if (!$connection) {
    // If connection fails, show error and stop execution
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Set character encoding to UTF-8 for proper Urdu/English text display
mysqli_set_charset($connection, 'utf8mb4');

/**
 * EXPLANATION FOR BEGINNERS:
 * 
 * What happens when this file is included?
 * 1. Database credentials are defined (username, password, etc.)
 * 2. mysqli_connect() tries to connect to the MySQL server
 * 3. If successful: $connection variable holds the database connection
 * 4. If failed: Shows error message and stops the script
 * 
 * How does data flow from frontend to database?
 * FRONTEND (HTML Form)
 *    ↓
 * Sends data to PHP file
 *    ↓
 * PHP file includes this db.php file
 *    ↓
 * PHP uses $connection to send queries to database
 *    ↓
 * Database (MySQL) stores the data in tables
 *    ↓
 * PHP retrieves data and sends back to Frontend
 *    ↓
 * Frontend displays the data in HTML
 */
?>
