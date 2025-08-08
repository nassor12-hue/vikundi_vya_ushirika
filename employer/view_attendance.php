<?php
/**
 * View Attendance Page for Vikundi vya Ushirika Attendance System
 * This page allows employers to view their detailed attendance records
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
$attendance_records = [];
$total_records = 0;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 15;
$offset = ($current_page - 1) * $records_per_page;

// Date filter variables
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Create MySQLi connection
$conn = new mysqli($host, $db_username, $db_password, $db_name);

if (!$conn->connect_error) {
    $user_id = $conn->real_escape_string($_SESSION['user_id']);
    
    // Build WHERE clause for filters
    $where_conditions = ["e.user_id = '$user_id'"];
    
    if (!empty($start_date)) {
        $where_conditions[] = "a.attendance_date >= '" . $conn->real_escape_string($start_date) . "'";
    }
    
    if (!empty($end_date)) {
        $where_conditions[] = "a.attendance_date <= '" . $conn->real_escape_string($end_date) . "'";
    }
    
    if (!empty($status_filter)) {
        $where_conditions[] = "a.status = '" . $conn->real_escape_string($status_filter) . "'";
    }
    
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    
    // Get total count for pagination
    $count_query = "SELECT COUNT(*) as total 
                    FROM attendance a 
                    JOIN employers e ON a.employer_id = e.id 
                    $where_clause";
    
    $count_result = $conn->query($count_query);
    if ($count_result) {
        $total_records = $count_result->fetch_assoc()['total'];
    }
    
    // Get attendance records with pagination
    $query = "SELECT a.attendance_date, a.status, a.check_in_time, a.check_out_time, a.notes,
                     CASE 
                         WHEN a.check_in_time > '09:00:00' THEN 'Late'
                         ELSE 'On Time'
                     END as punctuality
              FROM attendance a 
              JOIN employers e ON a.employer_id = e.id 
              $where_clause
              ORDER BY a.attendance_date DESC 
              LIMIT $records_per_page OFFSET $offset";
    
    $result = $conn->query($query);
    if ($result) {
        $attendance_records = $result->fetch_all(MYSQLI_ASSOC);
    }
    
    $conn->close();
}

// Calculate pagination
$total_pages = ceil($total_records / $records_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance - Vikundi vya Ushirika</title>
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
            left: 2rem;
            right: 2rem;
            background-color: #e74c3c;
            color: white;
            padding: 0.75rem 1rem;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c0392b;
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
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .content-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .breadcrumb {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* Filter section */
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input, .form-group select {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .filter-btn {
            background-color: #3498db;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .filter-btn:hover {
            background-color: #2980b9;
        }

        .clear-btn {
            background-color: #95a5a6;
            margin-left: 0.5rem;
        }

        .clear-btn:hover {
            background-color: #7f8c8d;
        }

        /* Table styles */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .table-header h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .records-info {
            color: #6c757d;
            font-size: 0.9rem;
        }

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
            color: #2c3e50;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        /* Status badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-present {
            background-color: #d4edda;
            color: #28a745;
        }

        .status-absent {
            background-color: #f8d7da;
            color: #dc3545;
        }

        .status-late {
            background-color: #fff3cd;
            color: #ffc107;
        }

        .punctuality-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .punctuality-ontime {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .punctuality-late {
            background-color: #ffeaa7;
            color: #856404;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            gap: 0.5rem;
        }

        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            text-decoration: none;
            border: 1px solid #dee2e6;
            color: #495057;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background-color: #e9ecef;
        }

        .pagination .current {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
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

            .filter-form {
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
            <a href="view_attendance.php" class="nav-item active">View Attendance</a>
            <a href="view_reports.php" class="nav-item">View Reports</a>
        </nav>

        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <!-- Content header -->
        <div class="content-header">
            <h1>View Attendance Records</h1>
            <div class="breadcrumb">Home > View Attendance</div>
        </div>

        <!-- Filter section -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Status</option>
                        <option value="present" <?php echo $status_filter === 'present' ? 'selected' : ''; ?>>Present</option>
                        <option value="absent" <?php echo $status_filter === 'absent' ? 'selected' : ''; ?>>Absent</option>
                        <option value="late" <?php echo $status_filter === 'late' ? 'selected' : ''; ?>>Late</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="filter-btn">Filter</button>
                    <a href="view_attendance.php" class="filter-btn clear-btn">Clear</a>
                </div>
            </form>
        </div>

        <!-- Attendance table -->
        <div class="table-container">
            <div class="table-header">
                <h3>Attendance Records</h3>
                <div class="records-info">
                    Showing <?php echo count($attendance_records); ?> of <?php echo $total_records; ?> records
                </div>
            </div>

            <?php if (empty($attendance_records)): ?>
                <div class="empty-state">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                    <h3>No attendance records found</h3>
                    <p>Try adjusting your filter criteria or check back later.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Punctuality</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_records as $record): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($record['attendance_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($record['status']); ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $record['check_in_time'] ? date('H:i', strtotime($record['check_in_time'])) : '-'; ?></td>
                                <td><?php echo $record['check_out_time'] ? date('H:i', strtotime($record['check_out_time'])) : '-'; ?></td>
                                <td>
                                    <?php if ($record['status'] === 'present'): ?>
                                        <span class="punctuality-badge punctuality-<?php echo strtolower(str_replace(' ', '', $record['punctuality'])); ?>">
                                            <?php echo $record['punctuality']; ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($record['notes'] ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&status=<?php echo $status_filter; ?>">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $current_page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&status=<?php echo $status_filter; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&status=<?php echo $status_filter; ?>">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
