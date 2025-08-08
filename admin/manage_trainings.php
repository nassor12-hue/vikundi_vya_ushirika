<?php
/**
 * Manage Trainings page for Vikundi vya Ushirika Attendance System
 * Admin can view all trainings, assign users, and track attendance
 */

require_once '../includes/session.php';
requireAdmin();

// Database connection
$host = "localhost";
$db_name = "vikundi";
$db_username = "root";
$db_password = "12345678";

$trainings = [];
$users = [];
$message = '';
$error = '';

// Handle assign user to training
if (isset($_POST['assign_user'])) {
    $training_id = (int)$_POST['training_id'];
    $user_id = (int)$_POST['user_id'];
    
    $conn = new mysqli($host, $db_username, $db_password, $db_name);
    
    if (!$conn->connect_error) {
        // Get employer_id from user_id
        $emp_query = "SELECT id FROM employers WHERE user_id = $user_id";
        $emp_result = $conn->query($emp_query);
        
        if ($emp_result && $emp_result->num_rows > 0) {
            $employer_id = $emp_result->fetch_assoc()['id'];
            
            // Check if already assigned
            $check_query = "SELECT id FROM training_assignments WHERE training_id = $training_id AND employer_id = $employer_id";
            $check_result = $conn->query($check_query);
            
            if ($check_result && $check_result->num_rows == 0) {
                // Assign user to training
                $assign_query = "INSERT INTO training_assignments (training_id, employer_id, status) VALUES ($training_id, $employer_id, 'assigned')";
                
                if ($conn->query($assign_query)) {
                    $message = "User assigned to training successfully!";
                } else {
                    $error = "Error assigning user: " . $conn->error;
                }
            } else {
                $error = "User is already assigned to this training.";
            }
        } else {
            $error = "User not found in employers table.";
        }
        
        $conn->close();
    }
}

// Handle mark attendance
if (isset($_POST['mark_attendance'])) {
    $assignment_id = (int)$_POST['assignment_id'];
    $attendance_status = $_POST['attendance_status'];
    
    $conn = new mysqli($host, $db_username, $db_password, $db_name);
    
    if (!$conn->connect_error) {
        // Create training_attendance table if it doesn't exist
        $create_table = "CREATE TABLE IF NOT EXISTS training_attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            assignment_id INT NOT NULL,
            attendance_date DATE NOT NULL,
            status ENUM('present', 'absent') NOT NULL,
            notes TEXT,
            marked_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (assignment_id) REFERENCES training_assignments(id) ON DELETE CASCADE,
            FOREIGN KEY (marked_by) REFERENCES users(id),
            UNIQUE KEY unique_assignment_date (assignment_id, attendance_date)
        )";
        $conn->query($create_table);
        
        // Mark attendance
        $attendance_query = "INSERT INTO training_attendance (assignment_id, attendance_date, status, marked_by) 
                            VALUES ($assignment_id, CURDATE(), '$attendance_status', {$_SESSION['user_id']})
                            ON DUPLICATE KEY UPDATE status = '$attendance_status', marked_by = {$_SESSION['user_id']}";
        
        if ($conn->query($attendance_query)) {
            // Update assignment status if present
            if ($attendance_status == 'present') {
                $update_assignment = "UPDATE training_assignments SET status = 'completed' WHERE id = $assignment_id";
                $conn->query($update_assignment);
            }
            $message = "Attendance marked successfully!";
        } else {
            $error = "Error marking attendance: " . $conn->error;
        }
        
        $conn->close();
    }
}

// Handle remove user from training
if (isset($_POST['remove_user'])) {
    $assignment_id = (int)$_POST['assignment_id'];
    
    $conn = new mysqli($host, $db_username, $db_password, $db_name);
    
    if (!$conn->connect_error) {
        // First delete all training attendance records for this assignment
        $delete_attendance = "DELETE FROM training_attendance WHERE assignment_id = $assignment_id";
        $conn->query($delete_attendance);
        
        // Then delete the assignment itself
        $delete_assignment = "DELETE FROM training_assignments WHERE id = $assignment_id";
        
        if ($conn->query($delete_assignment)) {
            if ($conn->affected_rows > 0) {
                $message = "User removed from training successfully!";
            } else {
                $error = "Assignment not found or already removed.";
            }
        } else {
            $error = "Error removing user from training: " . $conn->error;
        }
        
        $conn->close();
    }
}

// Get all trainings with assignments
$conn = new mysqli($host, $db_username, $db_password, $db_name);

if (!$conn->connect_error) {
    // Get trainings
    $training_query = "SELECT t.*, u.full_name as created_by_name,
                       (SELECT COUNT(*) FROM training_assignments ta WHERE ta.training_id = t.id) as total_assigned,
                       (SELECT COUNT(*) FROM training_assignments ta WHERE ta.training_id = t.id AND ta.status = 'completed') as completed_count
                       FROM trainings t 
                       LEFT JOIN users u ON t.created_by = u.id 
                       ORDER BY t.created_at DESC";
    
    $result = $conn->query($training_query);
    if ($result) {
        $trainings = $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get all employer users for assignment
    $user_query = "SELECT u.id, u.full_name, u.username, e.employee_id 
                   FROM users u 
                   JOIN employers e ON u.id = e.user_id 
                   WHERE u.role = 'employer' AND e.is_active = 1
                   ORDER BY u.full_name";
    
    $result = $conn->query($user_query);
    if ($result) {
        $users = $result->fetch_all(MYSQLI_ASSOC);
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trainings - Vikundi vya Ushirika</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; min-height: 100vh; }
        
        /* Sidebar styles */
        .sidebar { width: 250px; background-color: #000000; color: white; padding: 2rem 0; position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-header { padding: 0 1.5rem 2rem; border-bottom: 1px solid #333; }
        .sidebar-header h2 { color: #28a745; font-size: 1.2rem; margin-bottom: 0.5rem; }
        .welcome-message { font-size: 0.9rem; color: #ccc; }
        .sidebar-nav { padding: 1rem 0; }
        .nav-item { display: block; padding: 1rem 1.5rem; color: white; text-decoration: none; transition: background-color 0.3s ease; border-bottom: 1px solid #333; }
        .nav-item:hover { background-color: #333; }
        .nav-item.active { background-color: #28a745; }
        .logout-btn { position: absolute; bottom: 2rem; left: 1.5rem; right: 1.5rem; background-color: #dc3545; color: white; padding: 0.75rem 1rem; text-decoration: none; border-radius: 5px; text-align: center; transition: background-color 0.3s ease; }
        .logout-btn:hover { background-color: #c82333; }
        
        /* Main content */
        .main-content { margin-left: 250px; flex: 1; background-color: #1e3a8a; padding: 2rem; min-height: 100vh; }
        .content-header { background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .content-header h1 { color: #333; font-size: 2rem; margin-bottom: 0.5rem; }
        .breadcrumb { color: #666; font-size: 0.9rem; }
        
        /* Message styles */
        .message { padding: 1rem; margin-bottom: 1rem; border-radius: 5px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* Training cards */
        .training-grid { display: grid; gap: 2rem; }
        .training-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .training-header { padding: 1.5rem; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; }
        .training-title { color: #333; font-size: 1.3rem; margin-bottom: 0.5rem; }
        .training-meta { color: #6c757d; font-size: 0.9rem; }
        .training-content { padding: 1.5rem; }
        .training-description { color: #666; margin-bottom: 1rem; }
        .training-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-item { text-align: center; padding: 1rem; background-color: #f8f9fa; border-radius: 8px; }
        .stat-number { font-size: 1.5rem; font-weight: bold; color: #28a745; }
        .stat-label { font-size: 0.8rem; color: #666; margin-top: 0.25rem; }
        
        /* Assigned users section */
        .assigned-users { margin-top: 1.5rem; }
        .assigned-users h4 { color: #333; margin-bottom: 1rem; }
        .user-list { display: grid; gap: 0.5rem; }
        .user-item { display: flex; justify-content: between; align-items: center; padding: 0.75rem; background-color: #f8f9fa; border-radius: 5px; }
        .user-info { flex: 1; }
        .user-name { font-weight: 600; color: #333; }
        .user-details { font-size: 0.8rem; color: #666; }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .status-assigned { background-color: #fff3cd; color: #856404; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        
        /* Forms */
        .form-section { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .form-grid { display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: end; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 0.5rem; font-weight: 600; color: #333; }
        .form-group select, .form-group input { padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 5px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; transition: all 0.3s ease; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-success:hover { background-color: #1e7e34; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-danger:hover { background-color: #c82333; }
        .btn-warning { background-color: #ffc107; color: #212529; }
        .btn-warning:hover { background-color: #e0a800; }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.8rem; }
        
        /* Attendance buttons */
        .attendance-buttons { display: flex; gap: 0.5rem; }
        
        /* Empty state */
        .empty-state { text-align: center; padding: 3rem; color: #6c757d; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .training-stats { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
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
            <a href="manage_trainings.php" class="nav-item active">Manage Trainings</a>
            <a href="manage_attendance.php" class="nav-item">Manage Attendance</a>
            <a href="view_attendance.php" class="nav-item">View Attendance</a>
            <a href="reports.php" class="nav-item">Generate Reports</a>
            <a href="edit_account.php" class="nav-item">Edit Account</a>
        </nav>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <div class="content-header">
            <h1>Manage Training Programs</h1>
            <div class="breadcrumb">Home > Manage Trainings</div>
        </div>

        <!-- Messages -->
        <?php if (!empty($message)): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Assign User Form -->
        <div class="form-section">
            <h3 style="color: #333; margin-bottom: 1rem;">Assign User to Training</h3>
            <form method="POST" class="form-grid">
                <div class="form-group">
                    <label for="training_id">Select Training</label>
                    <select name="training_id" id="training_id" required>
                        <option value="">Choose a training...</option>
                        <?php foreach ($trainings as $training): ?>
                            <option value="<?php echo $training['id']; ?>">
                                <?php echo htmlspecialchars($training['training_name']); ?> 
                                (<?php echo date('M d, Y', strtotime($training['start_date'])); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="user_id">Select User</label>
                    <select name="user_id" id="user_id" required>
                        <option value="">Choose a user...</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['full_name']); ?> 
                                (<?php echo htmlspecialchars($user['employee_id']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" name="assign_user" class="btn btn-primary">Assign User</button>
            </form>
        </div>

        <!-- Training Programs -->
        <div class="training-grid">
            <?php if (empty($trainings)): ?>
                <div class="empty-state">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
                    <h3>No training programs found</h3>
                    <p><a href="add_training.php">Create a new training program</a> to get started.</p>
                </div>
            <?php else: ?>
                <?php foreach ($trainings as $training): ?>
                    <div class="training-card">
                        <div class="training-header">
                            <div class="training-title"><?php echo htmlspecialchars($training['training_name']); ?></div>
                            <div class="training-meta">
                                Created by <?php echo htmlspecialchars($training['created_by_name']); ?> • 
                                <?php echo date('M d, Y', strtotime($training['start_date'])); ?> - 
                                <?php echo date('M d, Y', strtotime($training['end_date'])); ?>
                            </div>
                        </div>
                        
                        <div class="training-content">
                            <?php if ($training['description']): ?>
                                <div class="training-description">
                                    <?php echo htmlspecialchars($training['description']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="training-stats">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo $training['total_assigned']; ?></div>
                                    <div class="stat-label">Assigned</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo $training['completed_count']; ?></div>
                                    <div class="stat-label">Completed</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">
                                        <?php 
                                        $completion_rate = $training['total_assigned'] > 0 ? 
                                            round(($training['completed_count'] / $training['total_assigned']) * 100) : 0;
                                        echo $completion_rate;
                                        ?>%
                                    </div>
                                    <div class="stat-label">Completion</div>
                                </div>
                            </div>
                            
                            <!-- Assigned Users -->
                            <?php
                            // Get assigned users for this training
                            $conn = new mysqli($host, $db_username, $db_password, $db_name);
                            $assigned_users = [];
                            
                            if (!$conn->connect_error) {
                                $assigned_query = "SELECT ta.id as assignment_id, ta.status, u.full_name, u.username, e.employee_id,
                                                  (SELECT COUNT(*) FROM training_attendance tat WHERE tat.assignment_id = ta.id AND tat.status = 'present') as attended_days
                                                  FROM training_assignments ta
                                                  JOIN employers e ON ta.employer_id = e.id
                                                  JOIN users u ON e.user_id = u.id
                                                  WHERE ta.training_id = {$training['id']}
                                                  ORDER BY u.full_name";
                                
                                $result = $conn->query($assigned_query);
                                if ($result) {
                                    $assigned_users = $result->fetch_all(MYSQLI_ASSOC);
                                }
                                $conn->close();
                            }
                            ?>
                            
                            <?php if (!empty($assigned_users)): ?>
                                <div class="assigned-users">
                                    <h4>Assigned Users (<?php echo count($assigned_users); ?>)</h4>
                                    <div class="user-list">
                                        <?php foreach ($assigned_users as $assigned_user): ?>
                                            <div class="user-item">
                                                <div class="user-info">
                                                    <div class="user-name"><?php echo htmlspecialchars($assigned_user['full_name']); ?></div>
                                                    <div class="user-details">
                                                        ID: <?php echo htmlspecialchars($assigned_user['employee_id']); ?> • 
                                                        Attended: <?php echo $assigned_user['attended_days']; ?> days
                                                    </div>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 1rem;">
                                                    <span class="status-badge status-<?php echo $assigned_user['status']; ?>">
                                                        <?php echo ucfirst($assigned_user['status']); ?>
                                                    </span>
                                                    <div class="attendance-buttons">
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="assignment_id" value="<?php echo $assigned_user['assignment_id']; ?>">
                                                            <input type="hidden" name="attendance_status" value="present">
                                                            <button type="submit" name="mark_attendance" class="btn btn-success btn-sm">Present</button>
                                                        </form>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="assignment_id" value="<?php echo $assigned_user['assignment_id']; ?>">
                                                            <input type="hidden" name="attendance_status" value="absent">
                                                            <button type="submit" name="mark_attendance" class="btn btn-danger btn-sm">Absent</button>
                                                        </form>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="assignment_id" value="<?php echo $assigned_user['assignment_id']; ?>">
                                                            <button type="submit" name="remove_user" class="btn btn-warning btn-sm" 
                                                                    onclick="return confirm('Are you sure you want to remove this user from the training? All attendance records will be deleted.')">
                                                                Remove
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="text-align: center; padding: 1rem; color: #6c757d; background-color: #f8f9fa; border-radius: 5px;">
                                    No users assigned to this training yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
