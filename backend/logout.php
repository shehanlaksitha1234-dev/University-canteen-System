<?php
/**
 * LOGOUT HANDLER - backend/logout.php
 * Handles user logout
 */

require 'config/db.php';
require 'auth.php';

// Destroy session
session_destroy();

// Clear any cart data
setcookie('PHPSESSID', '', time() - 3600, '/');

// Redirect to login
header('Location: ../frontend/login.php?message=logout');
exit;
?>
