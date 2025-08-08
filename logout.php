<?php
/**
 * Logout functionality for Vikundi vya Ushirika Attendance System
 * This file handles user logout and session cleanup
 */

// Include session management functions
require_once 'includes/session.php';

// Call logout function to destroy session and redirect
logout();
?>
