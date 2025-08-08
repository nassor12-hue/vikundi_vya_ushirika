<?php
/**
 * Admin Dashboard for Vikundi vya Ushirika Attendance System
 * This page provides admin interface for managing users, attendance, and reports
 */

// Include required files for session management
require_once '../includes/session.php';

// Ensure only admin users can access this page
requireAdmin();

// Database connection parameters
$host = "localhost";
$db_name = "vikundi";
$db_username = "root";
$db_password = "12345678";

// Get dashboard statistics
$stats = ['total_employers' => 0, 'today_present' => 0, 'active_trainings' => 0];

// Create MySQLi connection
$conn = new mysqli($host, $db_username, $db_password, $db_name);

// Check connection and get statistics
if (!$conn->connect_error) {
    // Count total employers
    $query = "SELECT COUNT(*) as total_employers FROM employers WHERE is_active = 1";
    $result = $conn->query($query);
    if ($result) {
        $stats['total_employers'] = $result->fetch_assoc()['total_employers'];
    }
    
    // Count today's attendance
    $query = "SELECT COUNT(*) as today_present FROM attendance WHERE attendance_date = CURDATE() AND status = 'present'";
    $result = $conn->query($query);
    if ($result) {
        $stats['today_present'] = $result->fetch_assoc()['today_present'];
    }
    
    // Count active trainings
    $query = "SELECT COUNT(*) as active_trainings FROM trainings WHERE end_date >= CURDATE()";
    $result = $conn->query($query);
    if ($result) {
        $stats['active_trainings'] = $result->fetch_assoc()['active_trainings'];
    }
    
    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Vikundi vya Ushirika</title>
    <style>
        /* Reset default margins and padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            min-height: 100vh;
        }

        /* Left sidebar with black background as requested */
        .sidebar {
            width: 250px;
            background-color: #000000; /* Black background */
            color: white;
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        /* Sidebar header with welcome message */
        .sidebar-header {
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid #333;
        }

        .sidebar-header h2 {
            color: #28a745;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .welcome-message {
            font-size: 0.9rem;
            color: #ccc;
        }

        /* Sidebar navigation menu */
        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            display: block;
            padding: 1rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s ease;
            border-bottom: 1px solid #333;
        }

        .nav-item:hover {
            background-color: #333;
        }

        .nav-item.active {
            background-color: #28a745;
        }

        /* Main content area with dark blue background as requested */
        .main-content {
            margin-left: 250px;
            flex: 1;
            background-color: #1e3a8a; /* Dark blue background */
            padding: 2rem;
            min-height: 100vh;
        }

        /* Content header */
        .content-header {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .content-header h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .breadcrumb {
            color: #666;
            font-size: 0.9rem;
        }

        /* Dashboard statistics cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 1rem;
        }

        /* Quick actions section */
        .quick-actions {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .quick-actions h3 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            display: block;
            padding: 1rem;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .action-btn:hover {
            background-color: #218838;
        }

        .action-btn.secondary {
            background-color: #6c757d;
        }

        .action-btn.secondary:hover {
            background-color: #545b62;
        }

        /* Recent activity section */
        .recent-activity {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .recent-activity h3 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
            color: #666;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        /* Logout button */
        .logout-btn {
            position: absolute;
            bottom: 2rem;
            left: 1.5rem;
            right: 1.5rem;
            padding: 1rem;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Left sidebar with black background and navigation menu -->
    <div class="sidebar">
        <!-- Sidebar header with welcome message -->
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
            <div class="welcome-message">
                Welcome, <?php echo htmlspecialchars(getUserWelcomeName()); ?>!
            </div>
        </div>

        <!-- Sidebar navigation menu with all admin functionalities -->
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item active">Dashboard</a>
            <a href="manage_users.php" class="nav-item">Manage Users</a>
            <a href="add_user.php" class="nav-item">Add Users</a>
            <a href="add_training.php" class="nav-item">Add Training</a>
            <a href="manage_trainings.php" class="nav-item">Manage Trainings</a>
            <a href="manage_attendance.php" class="nav-item">Manage Attendance</a>
            <a href="view_attendance.php" class="nav-item">View Attendance</a>
            <a href="reports.php" class="nav-item">Generate Reports</a>
            <a href="edit_account.php" class="nav-item">Edit Account</a>
        </nav>

        <!-- Logout button at bottom of sidebar -->
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Main content area with dark blue background -->
    <div class="main-content">
        <!-- Content header -->
        <div class="content-header">
            <h1>Admin Dashboard</h1>
            <div class="breadcrumb">Home > Dashboard</div>
        </div>

        <!-- Dashboard statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_employers']; ?></div>
                <div class="stat-label">Total Employers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['today_present']; ?></div>
                <div class="stat-label">Present Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['active_trainings']; ?></div>
                <div class="stat-label">Active Trainings</div>
            </div>
        </div>

        <!-- Quick actions section -->
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="actions-grid">
                <a href="add_user.php" class="action-btn">Add New User</a>
                <a href="manage_attendance.php" class="action-btn">Mark Attendance</a>
                <a href="add_training.php" class="action-btn">Create Training</a>
                <a href="reports.php" class="action-btn secondary">Generate Report</a>
            </div>
        </div>

        <!-- Recent activity section -->
        <div class="recent-activity">
            <h3>Recent Activity</h3>
            <ul class="activity-list">
                <li class="activity-item">System initialized successfully</li>
                <li class="activity-item">Admin dashboard loaded</li>
                <li class="activity-item">Ready to manage attendance and users</li>
            </ul>
        </div>
    </div>
</body>
</html>
