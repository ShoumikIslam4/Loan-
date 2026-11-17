<?php
// Start session for admin authentication
session_start();
require_once 'config/database.php';

// Check if admin is logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    try {
        // Log logout action
        $log_stmt = $pdo->prepare("
            INSERT INTO admin_actions_log 
            (admin_username, action, ip_address, user_agent) 
            VALUES (?, 'logout', ?, ?)
        ");
        $log_stmt->execute([
            $_SESSION['admin_username'] ?? 'unknown',
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        // Continue with logout even if logging fails
    }
}

// Destroy all session data
session_unset();
session_destroy();

// Start a new session for the logout message
session_start();
$_SESSION['logout_message'] = 'You have been successfully logged out.';

// Redirect to login page
header('Location: admin_login.php');
exit();
?>