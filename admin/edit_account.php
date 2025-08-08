<?php
require_once '../includes/session.php';
requireAdmin();

$message = ''; $error = '';

if ($_POST) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    
    if (!empty($current_password) && !empty($new_password) && $new_password === $confirm_password) {
        $host = "localhost"; $db_name = "vikundi"; $db_username = "root"; $db_password = "12345678";
        $conn = new mysqli($host, $db_username, $db_password, $db_name);
        
        if (!$conn->connect_error) {
            $user_id = $_SESSION['user_id'];
            
            // Verify current password
            $check_query = "SELECT password FROM users WHERE id = $user_id";
            $result = $conn->query($check_query);
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if ($current_password === $user['password']) {
                    // Update user information
                    $new_password = $conn->real_escape_string($new_password);
                    $email = $conn->real_escape_string($email);
                    $full_name = $conn->real_escape_string($full_name);
                    
                    $update_query = "UPDATE users SET password = '$new_password', email = '$email', full_name = '$full_name' WHERE id = $user_id";
                    
                    if ($conn->query($update_query)) {
                        $_SESSION['email'] = $email;
                        $_SESSION['full_name'] = $full_name;
                        $message = "Account updated successfully!";
                    } else {
                        $error = "Error updating account: " . $conn->error;
                    }
                } else {
                    $error = "Current password is incorrect!";
                }
            }
            $conn->close();
        }
    } else {
        $error = "Please fill all fields and ensure passwords match!";
    }
}

// Get current user info
$user_info = [];
$host = "localhost"; $db_name = "vikundi"; $db_username = "root"; $db_password = "12345678";
$conn = new mysqli($host, $db_username, $db_password, $db_name);
if (!$conn->connect_error) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT email, full_name FROM users WHERE id = $user_id";
    $result = $conn->query($query);
    if ($result) $user_info = $result->fetch_assoc();
    $conn->close();
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Edit Account</title>
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
.form-container { background: white; padding: 2rem; border-radius: 10px; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; color: #333; font-weight: 500; }
.form-group input { width: 100%; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; font-size: 1rem; }
.btn { padding: 1rem 2rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; }
.btn-primary { background-color: #28a745; color: white; }
.message { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
.message.success { background-color: #d4edda; color: #155724; }
.message.error { background-color: #f8d7da; color: #721c24; }
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
<a href="reports.php" class="nav-item">Generate Reports</a>
<a href="edit_account.php" class="nav-item active">Edit Account</a>
</nav>
<a href="../logout.php" class="logout-btn">Logout</a>
</div>
<div class="main-content">
<div class="content-header"><h1>Edit Account</h1></div>
<div class="form-container">
<?php if (!empty($message)): ?><div class="message success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="message error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="POST">
<div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>" required></div>
<div class="form-group"><label for="full_name">Full Name</label><input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_info['full_name'] ?? ''); ?>" required></div>
<div class="form-group"><label for="current_password">Current Password</label><input type="password" id="current_password" name="current_password" required></div>
<div class="form-group"><label for="new_password">New Password</label><input type="password" id="new_password" name="new_password" required></div>
<div class="form-group"><label for="confirm_password">Confirm New Password</label><input type="password" id="confirm_password" name="confirm_password" required></div>
<button type="submit" class="btn btn-primary">Update Account</button>
</form>
</div>
</div>
</body>
</html>
