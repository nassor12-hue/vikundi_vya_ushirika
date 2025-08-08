<?php
/**
 * Manage Users page for Vikundi vya Ushirika Attendance System
 * Admin can view all users and manage their status
 */

require_once '../includes/session.php';
requireAdmin();

// Database connection
$host = "localhost";
$db_name = "vikundi";
$db_username = "root";
$db_password = "12345678";

$users = [];
$message = '';
$error = '';

// Handle delete user request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    $conn = new mysqli($host, $db_username, $db_password, $db_name);
    
    if (!$conn->connect_error) {
        // First delete from employers table
        $delete_employer = "DELETE FROM employers WHERE user_id = $user_id";
        $conn->query($delete_employer);
        
        // Then delete from attendance table
        $delete_attendance = "DELETE FROM attendance WHERE employer_id IN (SELECT id FROM employers WHERE user_id = $user_id)";
        $conn->query($delete_attendance);
        
        // Finally delete from users table
        $delete_user = "DELETE FROM users WHERE id = $user_id AND role != 'admin'";
        
        if ($conn->query($delete_user)) {
            if ($conn->affected_rows > 0) {
                $message = "User deleted successfully!";
            } else {
                $error = "User not found or cannot delete admin user.";
            }
        } else {
            $error = "Error deleting user: " . $conn->error;
        }
        
        $conn->close();
    }
}

$conn = new mysqli($host, $db_username, $db_password, $db_name);

if (!$conn->connect_error) {
    $query = "SELECT u.id, u.username, u.email, u.role, u.full_name, u.created_at,
                     e.employee_id, e.department, e.is_active
              FROM users u 
              LEFT JOIN employers e ON u.id = e.user_id 
              ORDER BY u.created_at DESC";
    
    $result = $conn->query($query);
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
    <title>Manage Users - Vikundi vya Ushirika</title>
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
        
        /* Message styles */
        .message { padding: 1rem; margin-bottom: 1rem; border-radius: 5px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .users-section { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .section-header { padding: 1.5rem; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; }
        .section-header h3 { color: #333; margin-bottom: 0.5rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background-color: #f8f9fa; font-weight: 600; color: #333; }
        tr:hover { background-color: #f8f9fa; }
        .role-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
        .role-admin { background-color: #dc3545; color: white; }
        .role-employer { background-color: #28a745; color: white; }
        
        /* Action buttons */
        .action-buttons { display: flex; gap: 0.5rem; }
        .btn { padding: 0.5rem 1rem; text-decoration: none; border-radius: 5px; font-size: 0.8rem; font-weight: 600; transition: all 0.3s ease; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn-delete:hover { background-color: #c82333; }
        .btn-edit { background-color: #007bff; color: white; }
        .btn-edit:hover { background-color: #0056b3; }
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
            <a href="manage_users.php" class="nav-item active">Manage Users</a>
            <a href="add_user.php" class="nav-item">Add Users</a>
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
            <h1>Manage Users</h1>
            <div class="breadcrumb">Home > Manage Users</div>
        </div>

        <!-- Display messages -->
        <?php if (!empty($message)): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="users-section">
            <div class="section-header">
                <h3>All System Users</h3>
                <p>Manage all users in the system. You can delete users but cannot delete admin accounts.</p>
            </div>
            
            <?php if (!empty($users)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Employee ID</th>
                            <th>Department</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo $user['employee_id'] ?: '-'; ?></td>
                                <td><?php echo $user['department'] ?: '-'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <a href="?delete=<?php echo $user['id']; ?>" 
                                               class="btn btn-delete" 
                                               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                Delete
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #6c757d; font-size: 0.8rem;">Protected</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="padding: 3rem; text-align: center; color: #6c757d;">
                    <h4>No users found</h4>
                    <p><a href="add_user.php">Add a new user</a> to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
