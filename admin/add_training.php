<?php
require_once '../includes/session.php';
requireAdmin();

$message = ''; $error = '';

if ($_POST) {
    $training_name = trim($_POST['training_name']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    if (!empty($training_name) && !empty($start_date) && !empty($end_date)) {
        $host = "localhost"; $db_name = "vikundi"; $db_username = "root"; $db_password = "12345678";
        $conn = new mysqli($host, $db_username, $db_password, $db_name);
        
        if (!$conn->connect_error) {
            $training_name = $conn->real_escape_string($training_name);
            $description = $conn->real_escape_string($description);
            $start_date = $conn->real_escape_string($start_date);
            $end_date = $conn->real_escape_string($end_date);
            $created_by = $_SESSION['user_id'];
            
            $query = "INSERT INTO trainings (training_name, description, start_date, end_date, created_by) 
                      VALUES ('$training_name', '$description', '$start_date', '$end_date', $created_by)";
            
            if ($conn->query($query)) {
                $message = "Training program created successfully!";
            } else {
                $error = "Error creating training: " . $conn->error;
            }
            $conn->close();
        }
    } else {
        $error = "Please fill all required fields";
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Add Training</title>
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
.form-group input, .form-group textarea { width: 100%; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; font-size: 1rem; }
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
<a href="add_training.php" class="nav-item active">Add Training</a>
<a href="manage_trainings.php" class="nav-item">Manage Trainings</a>
<a href="manage_attendance.php" class="nav-item">Manage Attendance</a>
<a href="view_attendance.php" class="nav-item">View Attendance</a>
<a href="reports.php" class="nav-item">Generate Reports</a>
<a href="edit_account.php" class="nav-item">Edit Account</a>
</nav>
<a href="../logout.php" class="logout-btn">Logout</a>
</div>
<div class="main-content">
<div class="content-header"><h1>Add Training Program</h1></div>
<div class="form-container">
<?php if (!empty($message)): ?><div class="message success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="message error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="POST">
<div class="form-group"><label for="training_name">Training Name *</label><input type="text" id="training_name" name="training_name" required></div>
<div class="form-group"><label for="description">Description</label><textarea id="description" name="description" rows="4"></textarea></div>
<div class="form-group"><label for="start_date">Start Date *</label><input type="date" id="start_date" name="start_date" required></div>
<div class="form-group"><label for="end_date">End Date *</label><input type="date" id="end_date" name="end_date" required></div>
<button type="submit" class="btn btn-primary">Create Training</button>
</form>
</div>
</div>
</body>
</html>
