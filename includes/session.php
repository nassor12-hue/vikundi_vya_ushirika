<?php
/**
 * Session management for Vikundi vya Ushirika Attendance System
 * This file handles user sessions and authentication checks
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool Returns true if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role']);
}

/**
 * Check if user has admin role
 * @return bool Returns true if user is admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

/**
 * Check if user has employer role
 * @return bool Returns true if user is employer, false otherwise
 */
function isEmployer() {
    return isLoggedIn() && $_SESSION['role'] === 'employer';
}

/**
 * Redirect to login page if user is not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Redirect to login page if user is not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Get current user's full name for welcome message
 * @return string Returns user's full name or username
 */
function getUserWelcomeName() {
    if (isLoggedIn()) {
        return isset($_SESSION['full_name']) ? $_SESSION['full_name'] : $_SESSION['username'];
    }
    return '';
}

/**
 * Logout user by destroying session
 */
function logout() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to home page
    header("Location: index.php");
    exit();
}
?>
