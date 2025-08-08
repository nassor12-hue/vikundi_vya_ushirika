<?php
/**
 * View Reports Page for Vikundi vya Ushirika Attendance System
 * This page allows employers to view their attendance reports
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

// Initialize variables
$monthly_stats = [];
$yearly_stats = [];
$current_month = date('Y-m');
$current_year = date('Y');

// Create MySQLi connection
$conn = new mysqli($host, $db_username, $db_password, $db_name);

if (!$conn->connect_error) {
    $user_id = $conn->real_escape_string($_SESSION['user_id']);
    
    // Get monthly statistics for current year
    $query = "SELECT 
                DATE_FORMAT(a.attendance_date, '%Y-%m') as month,
                COUNT(*) as total_days,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days
              FROM attendance a 
              JOIN employers e ON a.employer_id = e.id 
              WHERE e.user_id = '$user_id' 
              AND YEAR(a.attendance_date) = '$current_year'
              GROUP BY DATE_FORMAT(a.attendance_date, '%Y-%m')
              ORDER BY month DESC";
    
    $result = $conn->query($query);
    if ($result) {
        $monthly_stats = $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get yearly statistics
    $query = "SELECT 
                YEAR(a.attendance_date) as year,
                COUNT(*) as total_days,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days
              FROM attendance a 
              JOIN employers e ON a.employer_id = e.id 
              WHERE e.user_id = '$user_id'
              GROUP BY YEAR(a.attendance_date)
              ORDER BY year DESC";
    
    $result = $conn->query($query);
    if ($result) {
        $yearly_stats = $result->fetch_all(MYSQLI_ASSOC);
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports - Vikundi vya Ushirika</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background-color: #000000;
            color: white;
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

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

        .logout-btn {
            position: absolute;
            bottom: 2rem;
            left: 1.5rem;
            right: 1.5rem;
            background-color: #dc3545;
            color: white;
            padding: 0.75rem 1rem;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        /* Main content styles */
        .main-content {
            margin-left: 250px;
            flex: 1;
            background-color: #1e3a8a;
            padding: 2rem;
            min-height: 100vh;
        }

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

        /* Report sections */
        .report-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .section-header {
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .section-header h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .section-description {
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Stats grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #28a745;
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

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        /* Progress bars */
        .progress-bar {
            background-color: #e9ecef;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .progress-present {
            background-color: #28a745;
        }

        .progress-absent {
            background-color: #dc3545;
        }

        .progress-late {
            background-color: #ffc107;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
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

            table {
                font-size: 0.9rem;
            }

            th, td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Left sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Employee Panel</h2>
            <div class="welcome-message">
                Welcome, <?php echo htmlspecialchars(getUserWelcomeName()); ?>!
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">Dashboard</a>
            <a href="view_attendance.php" class="nav-item">View Attendance</a>
            <a href="view_reports.php" class="nav-item active">View Reports</a>
        </nav>

        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <!-- Content header -->
        <div class="content-header">
            <h1>Attendance Reports</h1>
            <div class="breadcrumb">Home > View Reports</div>
        </div>

        <!-- Monthly Report Section -->
        <div class="report-section">
            <div class="section-header">
                <h3>Monthly Attendance Report - <?php echo date('Y'); ?></h3>
                <div class="section-description">Your attendance breakdown by month for the current year</div>
            </div>

            <?php if (empty($monthly_stats)): ?>
                <div class="empty-state">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                    <h3>No monthly data available</h3>
                    <p>Start recording your attendance to see monthly reports.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total Days</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>Attendance Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monthly_stats as $stat): ?>
                            <?php 
                                $attendance_rate = $stat['total_days'] > 0 ? 
                                    round(($stat['present_days'] / $stat['total_days']) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td><?php echo date('F Y', strtotime($stat['month'] . '-01')); ?></td>
                                <td><?php echo $stat['total_days']; ?></td>
                                <td>
                                    <span class="stat-number present" style="font-size: 1rem;"><?php echo $stat['present_days']; ?></span>
                                    <div class="progress-bar">
                                        <div class="progress-fill progress-present" 
                                             style="width: <?php echo $attendance_rate; ?>%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="stat-number absent" style="font-size: 1rem;"><?php echo $stat['absent_days']; ?></span>
                                </td>
                                <td>
                                    <span class="stat-number late" style="font-size: 1rem;"><?php echo $stat['late_days']; ?></span>
                                </td>
                                <td><strong><?php echo $attendance_rate; ?>%</strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Yearly Report Section -->
        <div class="report-section">
            <div class="section-header">
                <h3>Yearly Attendance Summary</h3>
                <div class="section-description">Your attendance performance across different years</div>
            </div>

            <?php if (empty($yearly_stats)): ?>
                <div class="empty-state">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📈</div>
                    <h3>No yearly data available</h3>
                    <p>Continue recording attendance to build your yearly summary.</p>
                </div>
            <?php else: ?>
                <?php foreach ($yearly_stats as $stat): ?>
                    <?php 
                        $attendance_rate = $stat['total_days'] > 0 ? 
                            round(($stat['present_days'] / $stat['total_days']) * 100, 1) : 0;
                    ?>
                    <div style="padding: 1.5rem; border-bottom: 1px solid #dee2e6;">
                        <h4 style="color: #333; margin-bottom: 1rem;">Year <?php echo $stat['year']; ?></h4>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-number total"><?php echo $stat['total_days']; ?></div>
                                <div class="stat-label">Total Days</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number present"><?php echo $stat['present_days']; ?></div>
                                <div class="stat-label">Days Present</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number absent"><?php echo $stat['absent_days']; ?></div>
                                <div class="stat-label">Days Absent</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number late"><?php echo $stat['late_days']; ?></div>
                                <div class="stat-label">Days Late</div>
                            </div>
                        </div>
                        <div style="margin-top: 1rem; text-align: center;">
                            <strong style="font-size: 1.2rem; color: #28a745;">
                                Overall Attendance Rate: <?php echo $attendance_rate; ?>%
                            </strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
