<?php
require_once '../includes/session.php';
requireAdmin();

$message = ''; $report_data = [];

if ($_POST && isset($_POST['generate_report'])) {
    $report_type = $_POST['report_type'];
    $date_from = $_POST['date_from'];
    $date_to = $_POST['date_to'];
    
    $host = "localhost"; $db_name = "vikundi"; $db_username = "root"; $db_password = "12345678";
    $conn = new mysqli($host, $db_username, $db_password, $db_name);
    
    if (!$conn->connect_error) {
        if ($report_type === 'attendance') {
            $query = "SELECT a.attendance_date, u.full_name, e.employee_id, e.department, a.status, a.check_in_time, a.check_out_time
                      FROM attendance a 
                      JOIN employers e ON a.employer_id = e.id 
                      JOIN users u ON e.user_id = u.id 
                      WHERE a.attendance_date BETWEEN '$date_from' AND '$date_to'
                      ORDER BY a.attendance_date DESC, u.full_name";
        } else {
            $query = "SELECT u.full_name, e.employee_id, e.department, 
                             COUNT(*) as total_days,
                             SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                             SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days
                      FROM attendance a 
                      JOIN employers e ON a.employer_id = e.id 
                      JOIN users u ON e.user_id = u.id 
                      WHERE a.attendance_date BETWEEN '$date_from' AND '$date_to'
                      GROUP BY e.id, u.full_name, e.employee_id, e.department
                      ORDER BY u.full_name";
        }
        
        $result = $conn->query($query);
        if ($result) {
            $report_data = $result->fetch_all(MYSQLI_ASSOC);
            $message = "Report generated successfully!";
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Reports</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; min-height: 100vh; }
.sidebar { width: 250px; background-color: #000000; color: white; padding: 2rem 0; position: fixed; height: 100vh; }
.sidebar-header { padding: 0 1.5rem 2rem; border-bottom: 1px solid #333; }
.sidebar-header h2 { color: #28a745; font-size: 1.2rem; margin-bottom: 0.5rem; }
.sidebar-nav { padding: 1rem 0; }
.nav-item { display: block; padding: 1rem 1.5rem; color: white; text-decoration: none; border-bottom: 1px solid #333; }
.nav-item:hover { background-color: #333; }
.nav-item.active { background-color: #28a745; }
.main-content { margin-left: 250px; flex: 1; background-color: #1e3a8a; padding: 2rem; min-height: 100vh; }
.content-header { background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; }
.content-header h1 { color: #333; font-size: 2rem; margin-bottom: 0.5rem; }
.form-container { background: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; color: #333; font-weight: 500; }
.form-group input, .form-group select { width: 100%; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; font-size: 1rem; }
.btn { padding: 1rem 2rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; }
.btn-primary { background-color: #28a745; color: white; }
.report-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.report-table th, .report-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
.report-table th { background-color: #f8f9fa; color: #333; font-weight: 600; }
.message { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; background-color: #d4edda; color: #155724; }
.logout-btn { position: absolute; bottom: 2rem; left: 1.5rem; right: 1.5rem; padding: 1rem; background-color: #dc3545; color: white; text-decoration: none; text-align: center; border-radius: 8px; }
</style>
</head>
<body>
<div class="sidebar">
<div class="sidebar-header"><h2>Admin Panel</h2><div class="welcome-message">Welcome, <?php echo htmlspecialchars(getUserWelcomeName()); ?>!</div></div>
<nav class="sidebar-nav">
<a href="dashboard.php" class="nav-item">Dashboard</a>
<a href="manage_users.php" class="nav-item">Manage Users</a>
<a href="add_user.php" class="nav-item">Add Users</a>
<a href="add_training.php" class="nav-item">Add Training</a>
<a href="manage_attendance.php" class="nav-item">Manage Attendance</a>
<a href="view_attendance.php" class="nav-item">View Attendance</a>
<a href="reports.php" class="nav-item active">Generate Reports</a>
<a href="edit_account.php" class="nav-item">Edit Account</a>
</nav>
<a href="../logout.php" class="logout-btn">Logout</a>
</div>
<div class="main-content">
<div class="content-header"><h1>Generate Reports</h1></div>
<div class="form-container">
<h3>Report Generator</h3>
<form method="POST">
<div class="form-group"><label for="report_type">Report Type</label>
<select id="report_type" name="report_type" required>
<option value="">Select Report Type...</option>
<option value="attendance">Detailed Attendance Report</option>
<option value="summary">Attendance Summary Report</option>
</select>
</div>
<div class="form-group"><label for="date_from">From Date</label><input type="date" id="date_from" name="date_from" required></div>
<div class="form-group"><label for="date_to">To Date</label><input type="date" id="date_to" name="date_to" required></div>
<button type="submit" name="generate_report" class="btn btn-primary">Generate Report</button>
</form>
</div>
<?php if (!empty($message)): ?><div class="form-container"><div class="message"><?php echo $message; ?></div>
<?php if (!empty($report_data)): ?>
<table class="report-table">
<thead><tr>
<?php if ($_POST['report_type'] === 'attendance'): ?>
<th>Date</th><th>Employee</th><th>ID</th><th>Department</th><th>Status</th><th>Check In</th><th>Check Out</th>
<?php else: ?>
<th>Employee</th><th>ID</th><th>Department</th><th>Total Days</th><th>Present</th><th>Absent</th>
<?php endif; ?>
</tr></thead>
<tbody>
<?php foreach ($report_data as $row): ?>
<tr>
<?php if ($_POST['report_type'] === 'attendance'): ?>
<td><?php echo date('M d, Y', strtotime($row['attendance_date'])); ?></td>
<td><?php echo htmlspecialchars($row['full_name']); ?></td>
<td><?php echo htmlspecialchars($row['employee_id']); ?></td>
<td><?php echo htmlspecialchars($row['department']); ?></td>
<td><?php echo ucfirst($row['status']); ?></td>
<td><?php echo $row['check_in_time'] ?: '-'; ?></td>
<td><?php echo $row['check_out_time'] ?: '-'; ?></td>
<?php else: ?>
<td><?php echo htmlspecialchars($row['full_name']); ?></td>
<td><?php echo htmlspecialchars($row['employee_id']); ?></td>
<td><?php echo htmlspecialchars($row['department']); ?></td>
<td><?php echo $row['total_days']; ?></td>
<td><?php echo $row['present_days']; ?></td>
<td><?php echo $row['absent_days']; ?></td>
<?php endif; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div><?php endif; ?>
</div>
</body>
</html>
