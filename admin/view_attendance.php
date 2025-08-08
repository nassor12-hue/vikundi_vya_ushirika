<?php
require_once '../includes/session.php';
requireAdmin();

$host = "localhost"; $db_name = "vikundi"; $db_username = "root"; $db_password = "12345678";
$attendance_records = []; $conn = new mysqli($host, $db_username, $db_password, $db_name);

if (!$conn->connect_error) {
    $query = "SELECT a.*, e.employee_id, u.full_name, e.department 
              FROM attendance a 
              JOIN employers e ON a.employer_id = e.id 
              JOIN users u ON e.user_id = u.id 
              ORDER BY a.attendance_date DESC LIMIT 50";
    $result = $conn->query($query);
    if ($result) $attendance_records = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>View Attendance</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; min-height: 100vh; }
.sidebar { width: 250px; background-color: #000000; color: white; padding: 2rem 0; position: fixed; height: 100vh; overflow-y: auto; }
.sidebar-header { padding: 0 1.5rem 2rem; border-bottom: 1px solid #333; }
.sidebar-header h2 { color: #28a745; font-size: 1.2rem; margin-bottom: 0.5rem; }
.welcome-message { font-size: 0.9rem; color: #ccc; }
.sidebar-nav { padding: 1rem 0; }
.nav-item { display: block; padding: 1rem 1.5rem; color: white; text-decoration: none; border-bottom: 1px solid #333; }
.nav-item:hover { background-color: #333; }
.nav-item.active { background-color: #28a745; }
.main-content { margin-left: 250px; flex: 1; background-color: #1e3a8a; padding: 2rem; min-height: 100vh; }
.content-header { background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; }
.content-header h1 { color: #333; font-size: 2rem; margin-bottom: 0.5rem; }
.table-container { background: white; padding: 2rem; border-radius: 10px; }
.attendance-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.attendance-table th, .attendance-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
.attendance-table th { background-color: #f8f9fa; color: #333; font-weight: 600; }
.status-present { color: #28a745; font-weight: bold; }
.status-absent { color: #dc3545; font-weight: bold; }
.status-late { color: #ffc107; font-weight: bold; }
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
<a href="view_attendance.php" class="nav-item active">View Attendance</a>
<a href="reports.php" class="nav-item">Generate Reports</a>
<a href="edit_account.php" class="nav-item">Edit Account</a>
</nav>
<a href="../logout.php" class="logout-btn">Logout</a>
</div>
<div class="main-content">
<div class="content-header"><h1>View Attendance Records</h1></div>

<div class="table-container">
<h3>Recent Attendance Records</h3>
<?php if (!empty($attendance_records)): ?>
<table class="attendance-table">
<thead><tr><th>Date</th><th>Employee</th><th>ID</th><th>Department</th><th>Status</th><th>Check In</th><th>Check Out</th><th>Notes</th></tr></thead>
<tbody>
<?php foreach ($attendance_records as $record): ?>
<tr>
<td><?php echo date('M d, Y', strtotime($record['attendance_date'])); ?></td>
<td><?php echo htmlspecialchars($record['full_name']); ?></td>
<td><?php echo htmlspecialchars($record['employee_id']); ?></td>
<td><?php echo htmlspecialchars($record['department']); ?></td>
<td class="status-<?php echo $record['status']; ?>">
<?php echo ucfirst($record['status']); ?>
<?php if ($record['status'] === 'present') echo ' ✓'; elseif ($record['status'] === 'absent') echo ' ✗'; else echo ' ⚠'; ?>
</td>
<td><?php echo $record['check_in_time'] ? date('H:i', strtotime($record['check_in_time'])) : '-'; ?></td>
<td><?php echo $record['check_out_time'] ? date('H:i', strtotime($record['check_out_time'])) : '-'; ?></td>
<td><?php echo htmlspecialchars($record['notes'] ?: '-'); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p>No attendance records found.</p>
<?php endif; ?>
</div>
</div>
</body>
</html>
