<?php
/**
 * Simple Login page for Vikundi vya Ushirika Attendance System
 * Uses basic MySQLi connection without PDO dependency
 */

// Start session
session_start();

// Redirect if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: employer/dashboard.php");
    }
    exit();
}

$error_message = '';

// Handle login form submission
if ($_POST) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (!empty($username) && !empty($password)) {
        // Database connection parameters
        $host = "localhost";
        $db_name = "vikundi";
        $db_username = "root";
        $db_password = "12345678";
        
        // Create MySQLi connection
        $conn = new mysqli($host, $db_username, $db_password, $db_name);
        
        // Check connection
        if ($conn->connect_error) {
            $error_message = "Database connection failed: " . $conn->connect_error;
        } else {
            // Escape input to prevent SQL injection
            $username = $conn->real_escape_string($username);
            $password = $conn->real_escape_string($password);
            
            // Query to find user by username
            $query = "SELECT u.id, u.username, u.email, u.password, u.role, u.full_name, 
                             e.id as employer_id, e.employee_id 
                      FROM users u 
                      LEFT JOIN employers e ON u.id = e.user_id 
                      WHERE u.username = '$username'";
            
            $result = $conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verify password (in production, use password_hash/password_verify)
                if ($password === $user['password']) {
                    // Set session variables for successful login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['full_name'];
                    
                    // Set employer-specific session data if applicable
                    if ($user['role'] === 'employer' && $user['employer_id']) {
                        $_SESSION['employer_id'] = $user['employer_id'];
                        $_SESSION['employee_id'] = $user['employee_id'];
                    }
                    
                    // Redirect based on user role
                    if ($user['role'] === 'admin') {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: employer/dashboard.php");
                    }
                    exit();
                } else {
                    $error_message = "Invalid username or password!";
                }
            } else {
                $error_message = "Invalid username or password!";
            }
            
            // Close connection
            $conn->close();
        }
    } else {
        $error_message = "Please enter both username and password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vikundi vya Ushirika</title>
    <style>
        /* Reset default margins and padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styling with brown background as requested */
        body {
            font-family: Arial, sans-serif;
            background-color: #8B4513; /* Brown background */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        /* Login container with center alignment and border radius as requested */
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 15px; /* Border radius as requested */
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        /* Logo and title styling */
        .login-header {
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: #8B4513;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #666;
            font-size: 1rem;
        }

        /* Form styling */
        .login-form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #8B4513;
        }

        /* Submit button styling */
        .login-btn {
            width: 100%;
            padding: 1rem;
            background-color: #8B4513;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-btn:hover {
            background-color: #654321;
        }

        /* Error message styling */
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        }

        /* Success message styling */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
        }

        /* Navigation links */
        .nav-links {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .nav-links a {
            color: #8B4513;
            text-decoration: none;
            margin: 0 1rem;
            font-weight: 500;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }

        /* Default credentials info */
        .credentials-info {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #0066cc;
        }

        /* Database setup info */
        .setup-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #856404;
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                padding: 2rem 1.5rem;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Login container centered with border radius as requested -->
    <div class="login-container">
        <!-- Login header -->
        <div class="login-header">
            <h1>Vikundi vya Ushirika</h1>
            <p>Attendance Management System</p>
        </div>

        <!-- Display error message if login fails -->
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Login form with username and password fields as requested -->
        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <!-- Database setup information -->
        <div class="setup-info">
            <strong>Database Setup Required:</strong><br>
            1. Create MySQL database named "vikundi"<br>
            2. Import the database.sql file<br>
            3. Update password in this file if needed
        </div>

        <!-- Default credentials information for testing -->
        <div class="credentials-info">
            <strong>Default Admin Credentials:</strong><br>
            Username: admin<br>
            Password: vikundi<br>
            Email: admin@gmail.com
        </div>

        <!-- Navigation links -->
        <div class="nav-links">
            <a href="index.php">← Back to Home</a>
            <a href="about.php">About Us</a>
        </div>
    </div>
</body>
</html>
