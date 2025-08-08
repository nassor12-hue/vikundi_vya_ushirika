<?php
/**
 * Employer Dashboard for Vikundi vya Ushirika Attendance System
 * This page provides employer interface for viewing attendance and reports
 */

// Include required files for session management
require_once '../includes/session.php';

// Ensure only employer users can access this page
requireLogin();
if (!isEmployer()) {
    header("Location: ../login.php");
    exit();
}

// Database connection parameters
$host = "localhost";
$db_name = "vikundi";
$db_username = "root";
$db_password = "12345678";

// Get employer's attendance data
$attendance_data = [];
$employer_stats = ['total_days' => 0, 'present_days' => 0, 'absent_days' => 0, 'late_days' => 0];

// Create MySQLi connection
$conn = new mysqli($host, $db_username, $db_password, $db_name);

// Check connection and get data
if (!$conn->connect_error) {
    // Get employer's recent attendance records
    $user_id = $conn->real_escape_string($_SESSION['user_id']);
    $query = "SELECT a.attendance_date, a.status, a.check_in_time, a.check_out_time, a.notes
              FROM attendance a 
              JOIN employers e ON a.employer_id = e.id 
              WHERE e.user_id = '$user_id' 
              ORDER BY a.attendance_date DESC 
              LIMIT 10";
    
    $result = $conn->query($query);
    if ($result) {
        $attendance_data = $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get attendance statistics for current month
    $query = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
              FROM attendance a 
              JOIN employers e ON a.employer_id = e.id 
              WHERE e.user_id = '$user_id' 
              AND MONTH(a.attendance_date) = MONTH(CURDATE()) 
              AND YEAR(a.attendance_date) = YEAR(CURDATE())";
    
    $result = $conn->query($query);
    if ($result) {
        $employer_stats = $result->fetch_assoc();
        if (!$employer_stats) {
            $employer_stats = ['total_days' => 0, 'present_days' => 0, 'absent_days' => 0, 'late_days' => 0];
        }
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
    <title>Employer Dashboard - Vikundi vya Ushirika</title>
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-number.present {
            color: #28a745;
        }

        .stat-number.absent {
            color: #dc3545;
        }

        .stat-number.late {
            color: #ffc107;
        }

        .stat-number.total {
            color: #6c757d;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        /* Attendance table */
        .attendance-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .attendance-section h3 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .attendance-table th,
        .attendance-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .attendance-table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
        }

        /* Status indicators */
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-present {
            background-color: #d4edda;
            color: #155724;
        }

        .status-absent {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-late {
            background-color: #fff3cd;
            color: #856404;
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
                grid-template-columns: repeat(2, 1fr);
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .attendance-table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <!-- Left sidebar with black background and navigation menu -->
    <div class="sidebar">
        <!-- Sidebar header with welcome message -->
        <div class="sidebar-header">
            <h2>Employee Panel</h2>
            <div class="welcome-message">
                Welcome, <?php echo htmlspecialchars(getUserWelcomeName()); ?>!
            </div>
        </div>

        <!-- Sidebar navigation menu with employer functionalities -->
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item active">Dashboard</a>
            <a href="view_attendance.php" class="nav-item">View Attendance</a>
            <a href="view_reports.php" class="nav-item">View Reports</a>
        </nav>

        <!-- Logout button at bottom of sidebar -->
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Main content area with dark blue background -->
    <div class="main-content">
        <!-- Content header -->
        <div class="content-header">
            <h1>Employee Dashboard</h1>
            <div class="breadcrumb">Home > Dashboard</div>
        </div>

        <!-- Monthly attendance statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number present"><?php echo $employer_stats['present_days']; ?></div>
                <div class="stat-label">Days Present</div>
            </div>
            <div class="stat-card">
                <div class="stat-number absent"><?php echo $employer_stats['absent_days']; ?></div>
                <div class="stat-label">Days Absent</div>
            </div>
            <div class="stat-card">
                <div class="stat-number late"><?php echo $employer_stats['late_days']; ?></div>
                <div class="stat-label">Days Late</div>
            </div>
            <div class="stat-card">
                <div class="stat-number total"><?php echo $employer_stats['total_days']; ?></div>
                <div class="stat-label">Total Days</div>
            </div>
        </div>

        <!-- Quick actions section -->
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="actions-grid">
                <a href="view_attendance.php" class="action-btn">View Full Attendance</a>
                <a href="view_reports.php" class="action-btn secondary">View Reports</a>
            </div>
        </div>

        <!-- Recent attendance records -->
        <div class="attendance-section">
            <h3>Recent Attendance Records</h3>
            <?php if (!empty($attendance_data)): ?>
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_data as $record): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($record['attendance_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $record['status']; ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                        <?php if ($record['status'] === 'present'): ?>
                                            ✓
                                        <?php elseif ($record['status'] === 'absent'): ?>
                                            ✗
                                        <?php else: ?>
                                            ⚠
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?php echo $record['check_in_time'] ? date('H:i', strtotime($record['check_in_time'])) : '-'; ?></td>
                                <td><?php echo $record['check_out_time'] ? date('H:i', strtotime($record['check_out_time'])) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($record['notes'] ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #666; text-align: center; padding: 2rem;">
                    No attendance records found. Contact your administrator if you believe this is an error.
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
