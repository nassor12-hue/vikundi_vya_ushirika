<?php
/**
 * Database Setup Script for Vikundi vya Ushirika Attendance System
 * This script creates the database and tables if they don't exist
 */

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "12345678"; // Update this to match your MySQL password
$db_name = "vikundi";

echo "<h2>Vikundi vya Ushirika - Database Setup</h2>";

// First, connect without specifying database to create it
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>");
}

echo "<p style='color: green;'>✓ Connected to MySQL server successfully!</p>";

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>✓ Database '$db_name' created successfully or already exists!</p>";
} else {
    echo "<p style='color: red;'>Error creating database: " . $conn->error . "</p>";
}

// Close connection and reconnect to the specific database
$conn->close();

// Connect to the specific database
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("<p style='color: red;'>Connection to database failed: " . $conn->connect_error . "</p>");
}

echo "<p style='color: green;'>✓ Connected to database '$db_name' successfully!</p>";

// Set charset
$conn->set_charset("utf8");

// Create tables
$tables = [
    // Users table
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'employer') NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    // Employers table
    "CREATE TABLE IF NOT EXISTS employers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        employee_id VARCHAR(20) UNIQUE NOT NULL,
        department VARCHAR(100),
        position VARCHAR(100),
        hire_date DATE,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    // Trainings table
    "CREATE TABLE IF NOT EXISTS trainings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        training_name VARCHAR(200) NOT NULL,
        description TEXT,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        max_participants INT DEFAULT 2,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )",
    
    // Training assignments table
    "CREATE TABLE IF NOT EXISTS training_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        training_id INT NOT NULL,
        employer_id INT NOT NULL,
        assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('assigned', 'completed', 'cancelled') DEFAULT 'assigned',
        FOREIGN KEY (training_id) REFERENCES trainings(id) ON DELETE CASCADE,
        FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
        UNIQUE KEY unique_training_employer (training_id, employer_id)
    )",
    
    // Attendance table
    "CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employer_id INT NOT NULL,
        attendance_date DATE NOT NULL,
        status ENUM('present', 'absent', 'late') NOT NULL,
        check_in_time TIME,
        check_out_time TIME,
        notes TEXT,
        marked_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
        FOREIGN KEY (marked_by) REFERENCES users(id),
        UNIQUE KEY unique_employer_date (employer_id, attendance_date)
    )",
    
    // Reports table
    "CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        report_type ENUM('attendance', 'training', 'monthly', 'yearly') NOT NULL,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        file_path VARCHAR(500),
        generated_by INT NOT NULL,
        date_from DATE,
        date_to DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (generated_by) REFERENCES users(id)
    )"
];

// Execute table creation queries
foreach ($tables as $index => $sql) {
    if ($conn->query($sql) === TRUE) {
        $table_names = ['users', 'employers', 'trainings', 'training_assignments', 'attendance', 'reports'];
        echo "<p style='color: green;'>✓ Table '{$table_names[$index]}' created successfully or already exists!</p>";
    } else {
        echo "<p style='color: red;'>Error creating table: " . $conn->error . "</p>";
    }
}

// Check if admin user exists, if not create it
$check_admin = "SELECT id FROM users WHERE username = 'admin'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    // Insert default admin user
    $insert_admin = "INSERT INTO users (username, email, password, role, full_name) VALUES 
                     ('admin', 'admin@gmail.com', 'vikundi', 'admin', 'System Administrator')";
    
    if ($conn->query($insert_admin) === TRUE) {
        echo "<p style='color: green;'>✓ Default admin user created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error creating admin user: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ Admin user already exists!</p>";
}

// Check if sample employers exist, if not create them
$check_employers = "SELECT id FROM users WHERE role = 'employer'";
$result = $conn->query($check_employers);

if ($result->num_rows == 0) {
    // Insert sample employer users
    $sample_users = [
        "INSERT INTO users (username, email, password, role, full_name) VALUES 
         ('john_doe', 'john@vikundi.com', 'password123', 'employer', 'John Doe')",
        "INSERT INTO users (username, email, password, role, full_name) VALUES 
         ('jane_smith', 'jane@vikundi.com', 'password123', 'employer', 'Jane Smith')"
    ];
    
    foreach ($sample_users as $sql) {
        if ($conn->query($sql) === TRUE) {
            $user_id = $conn->insert_id;
            echo "<p style='color: green;'>✓ Sample employer user created (ID: $user_id)!</p>";
            
            // Create corresponding employer record
            if ($user_id == 2) {
                $emp_sql = "INSERT INTO employers (user_id, employee_id, department, position, hire_date) VALUES 
                           ($user_id, 'EMP001', 'Operations', 'Field Officer', '2024-01-15')";
            } else {
                $emp_sql = "INSERT INTO employers (user_id, employee_id, department, position, hire_date) VALUES 
                           ($user_id, 'EMP002', 'Finance', 'Accountant', '2024-02-01')";
            }
            
            if ($conn->query($emp_sql) === TRUE) {
                echo "<p style='color: green;'>✓ Employer record created!</p>";
            }
        }
    }
} else {
    echo "<p style='color: blue;'>ℹ Sample employer users already exist!</p>";
}

// Close connection
$conn->close();

echo "<h3 style='color: green;'>Database setup completed successfully!</h3>";
echo "<p><strong>You can now use the following credentials to login:</strong></p>";
echo "<ul>";
echo "<li><strong>Admin:</strong> Username: admin, Password: vikundi</li>";
echo "<li><strong>Employer 1:</strong> Username: john_doe, Password: password123</li>";
echo "<li><strong>Employer 2:</strong> Username: jane_smith, Password: password123</li>";
echo "</ul>";
echo "<p><a href='login_simple.php'>Go to Login Page</a> | <a href='index.php'>Go to Home Page</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: #f5f5f5;
}

h2 {
    color: #333;
    border-bottom: 2px solid #28a745;
    padding-bottom: 0.5rem;
}

p {
    margin: 0.5rem 0;
    padding: 0.5rem;
    border-radius: 4px;
}

ul {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

a {
    color: #28a745;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}
</style>
