<?php
require_once 'db_connect.php';

// Handle logout
if (isLoggedIn()) {
    // Destroy session
    session_destroy();
    
    // Clear remember me cookie if exists
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Redirect to login page with message
    redirect('login.php', 'You have been logged out successfully.', 'success');
} else {
    // If not logged in, redirect to login page
    redirect('login.php');
}
?>
