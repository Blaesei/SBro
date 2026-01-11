<?php
/**
 * Authentication Guard
 * Include at top of protected pages to require login
 */

session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Not logged in - redirect to landing page
    header('Location: index.php');
    exit();
}

// Store current user data for easy access
$current_user = [
    'user_id' => $_SESSION['user_id'],
    'full_name' => $_SESSION['full_name'] ?? 'User',
    'email' => $_SESSION['email'] ?? ''
];
?>