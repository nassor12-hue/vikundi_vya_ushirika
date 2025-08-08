<?php
/**
 * Manage Attendance page for Vikundi vya Ushirika Attendance System
 * Admin can mark attendance for employees
 */

require_once '../includes/session.php';
requireAdmin();

$message = '';
$error = '';
$employees = [];

// Database connection
$host = "localhost";
$db_name = "vikundi";
$db_username = "root";
$db_password = "12345678";

$conn = new mysqli($host, $db_username, $db_password, $db_name);

// Handle attendance marking
if ($_POST && isset($_POST['mark_attendance'])) {
    $employer_id = $_POST['employer_id'];
    $attendance_date = $_POST['attendance_date'];
    $status = $_POST['status'];
    $check_in_time = $_POST['check_in_time'];
    $check_out_time = $_POST['check_out_time'];
    $notes = $_POST['notes'];
    
    if (!$conn->connect_error) {
        $employer_id = $conn->real_escape_string($employer_id);
        $attendance_date = $conn->real_escape_string($attendance_date);
        $status = $conn->real_escape_string($status);
        $check_in_time = $conn->real_escape_string($check_in_time);
        $check_out_time = $conn->real_escape_string($check_out_time);
        $notes = $conn->real_escape_string($notes);
        $marked_by = $_SESSION['user_id'];
        
        // Check if attendance already exists for this date
        $check_query = "SELECT id FROM attendance WHERE employer_id = '$employer_id' AND attendance_date = '$attendance_date'";
        $check_result = $conn->query($check_query);
        
        if ($check_result && $check_result->num_rows > 0) {
            // Update existing attendance
            $update_query = "UPDATE attendance SET 
                            status = '$status',
                            check_in_time = '$check_in_time',
                            check_out_time = '$check_out_time',
                            notes = '$notes',
                            marked_by = $marked_by
                            WHERE employer_id = '$employer_id' AND attendance_date = '$attendance_date'";
            
            if ($conn->query($update_query)) {
                $message = "Attendance updated successfully!";
            } else {
                $error = "Error updating attendance: " . $conn->error;
            }
        } else {
            // Insert new attendance
            $insert_query = "INSERT INTO attendance (employer_id, attendance_date, status, check_in_time, check_out_time, notes, marked_by) 
                            VALUES ('$employer_id', '$attendance_date', '$status', '$check_in_time', '$check_out_time', '$notes', $marked_by)";
            
            if ($conn->query($insert_query)) {
                $message = "Attendance marked successfully!";
            } else {
                $error = "Error marking attendance: " . $conn->error;
            }
        }
    }
}

// Get all employees
if (!$conn->connect_error) {
    $query = "SELECT e.id, e.employee_id, u.full_name, e.department, e.position 
              FROM employers e 
              JOIN users u ON e.user_id = u.id 
              WHERE e.is_active = 1 
              ORDER BY u.full_name";
    
    $result = $conn->query($query);
    if ($result) {
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance - Vikundi vya Ushirika</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: #000000; color: white; padding: 2rem 0; position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-header { padding: 0 1.5rem 2rem; border-bottom: 1px solid #333; }
        .sidebar-header h2 { color: #28a745; font-size: 1.2rem; margin-bottom: 0.5rem; }
        .welcome-message { font-size: 0.9rem; color: #ccc; }
        .sidebar-nav { padding: 1rem 0; }
        .nav-item { display: block; padding: 1rem 1.5rem; color: white; text-decoration: none; transition: background-color 0.3s ease; border-bottom: 1px solid #333; }
        .nav-item:hover { background-color: #333; }
        .nav-item.active { background-color: #28a745; }
        .main-content { margin-left: 250px; flex: 1; background-color: #1e3a8a; padding: 2rem; min-height: 100vh; }
        .content-header { background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .content-header h1 { color: #333; font-size: 2rem; margin-bottom: 0.5rem; }
        .breadcrumb { color: #666; font-size: 0.9rem; }
        .form-container { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #333; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; font-size: 1rem; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #28a745; }
        .btn { padding: 1rem 2rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background-color 0.3s ease; }
        .btn-primary { background-color: #28a745; color: white; }
        .btn-primary:hover { background-color: #218838; }
        .message { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .logout-btn { position: absolute; bottom: 2rem; left: 1.5rem; right: 1.5rem; padding: 1rem; background-color: #dc3545; color: white; text-decoration: none; text-align: center; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
            <div class="welcome-message">Welcome, <?php echo htmlspecialchars(getUserWelcomeName()); ?>!</div>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">Dashboard</a>
            <a href="manage_users.php" class="nav-item">Manage Users</a>
            <a href="add_user.php" class="nav-item">Add Users</a>
            <a href="add_training.php" class="nav-item">Add Training</a>
            <a href="manage_attendance.php" class="nav-item active">Manage Attendance</a>
            <a href="view_attendance.php" class="nav-item">View Attendance</a>
            <a href="reports.php" class="nav-item">Generate Reports</a>
            <a href="edit_account.php" class="nav-item">Edit Account</a>
        </nav>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="content-header">
            <h1>Manage Attendance</h1>
            <div class="breadcrumb">Home > Manage Attendance</div>
        </div>

        <div class="form-container">
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <h3>Mark Employee Attendance</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="employer_id">Select Employee *</label>
                    <select id="employer_id" name="employer_id" required>
                        <option value="">Choose Employee...</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?php echo $employee['id']; ?>">
                                <?php echo htmlspecialchars($employee['full_name']) . ' (' . $employee['employee_id'] . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="attendance_date">Date *</label>
                    <input type="date" id="attendance_date" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="">Choose Status...</option>
                        <option value="present">Present ✓</option>
                        <option value="absent">Absent ✗</option>
                        <option value="late">Late ⚠</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="check_in_time">Check In Time</label>
                    <input type="time" id="check_in_time" name="check_in_time">
                </div>

                <div class="form-group">
                    <label for="check_out_time">Check Out Time</label>
                    <input type="time" id="check_out_time" name="check_out_time">
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Optional notes about attendance..."></textarea>
                </div>

                <button type="submit" name="mark_attendance" class="btn btn-primary">Mark Attendance</button>
            </form>
        </div>
    </div>
</body>
</html>
