<?php
/**
 * Add User page for Vikundi vya Ushirika Attendance System
 * Admin can add new users (employers) to the system
 */

require_once '../includes/session.php';
requireAdmin();

$message = '';
$error = '';

// Handle form submission
if ($_POST) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $employee_id = trim($_POST['employee_id']);
    $department = trim($_POST['department']);
    $position = trim($_POST['position']);
    
    if (!empty($username) && !empty($email) && !empty($password) && !empty($full_name)) {
        // Database connection
        $host = "localhost";
        $db_name = "vikundi";
        $db_username = "root";
        $db_password = "12345678";
        
        $conn = new mysqli($host, $db_username, $db_password, $db_name);
        
        if (!$conn->connect_error) {
            // Escape inputs
            $username = $conn->real_escape_string($username);
            $email = $conn->real_escape_string($email);
            $password = $conn->real_escape_string($password);
            $full_name = $conn->real_escape_string($full_name);
            $employee_id = $conn->real_escape_string($employee_id);
            $department = $conn->real_escape_string($department);
            $position = $conn->real_escape_string($position);
            
            // Check if user already exists (by username or email)
            $check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
            $check_result = $conn->query($check_query);
            
            if ($check_result && $check_result->num_rows > 0) {
                $error = "User already exists! Username or email is already registered in the system.";
            } else {
                // Insert user
                $query = "INSERT INTO users (username, email, password, role, full_name) VALUES 
                          ('$username', '$email', '$password', 'employer', '$full_name')";
                
                if ($conn->query($query)) {
                    $user_id = $conn->insert_id;
                    
                    // Insert employer record
                    $emp_query = "INSERT INTO employers (user_id, employee_id, department, position, hire_date) VALUES 
                                 ($user_id, '$employee_id', '$department', '$position', CURDATE())";
                    
                    if ($conn->query($emp_query)) {
                        $message = "User added successfully!";
                    } else {
                        $error = "User created but employer record failed: " . $conn->error;
                    }
                } else {
                    $error = "Error creating user: " . $conn->error;
                }
            }
            
            $conn->close();
        } else {
            $error = "Database connection failed";
        }
    } else {
        $error = "Please fill all required fields";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Vikundi vya Ushirika</title>
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
        .form-group input, .form-group select { width: 100%; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; font-size: 1rem; }
        .form-group input:focus { outline: none; border-color: #28a745; }
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
            <a href="add_user.php" class="nav-item active">Add Users</a>
            <a href="add_training.php" class="nav-item">Add Training</a>
            <a href="manage_attendance.php" class="nav-item">Manage Attendance</a>
            <a href="view_attendance.php" class="nav-item">View Attendance</a>
            <a href="reports.php" class="nav-item">Generate Reports</a>
            <a href="edit_account.php" class="nav-item">Edit Account</a>
        </nav>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="content-header">
            <h1>Add New User</h1>
            <div class="breadcrumb">Home > Add User</div>
        </div>

        <div class="form-container">
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>

                <div class="form-group">
                    <label for="employee_id">Employee ID</label>
                    <input type="text" id="employee_id" name="employee_id">
                </div>

                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department">
                </div>

                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" id="position" name="position">
                </div>

                <button type="submit" class="btn btn-primary">Add User</button>
            </form>
        </div>
    </div>
</body>
</html>
