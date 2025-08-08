-- Database schema for Vikundi vya Ushirika Attendance System
-- This file contains all the table structures needed for the system

-- Create database
CREATE DATABASE IF NOT EXISTS vikundi;
USE vikundi;

-- Users table - stores both Admin and Employer users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employer') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Employers table - additional information for employers
CREATE TABLE employers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    department VARCHAR(100),
    position VARCHAR(100),
    hire_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Trainings table - stores training programs
CREATE TABLE trainings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    training_name VARCHAR(200) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    max_participants INT DEFAULT 2,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Training assignments table - tracks which employers are assigned to trainings
CREATE TABLE training_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    training_id INT NOT NULL,
    employer_id INT NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('assigned', 'completed', 'cancelled') DEFAULT 'assigned',
    FOREIGN KEY (training_id) REFERENCES trainings(id) ON DELETE CASCADE,
    FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_training_employer (training_id, employer_id)
);

-- Attendance table - tracks daily attendance
CREATE TABLE attendance (
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
);

-- Reports table - stores generated reports
CREATE TABLE reports (
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
);

-- Insert default admin user
INSERT INTO users (username, email, password, role, full_name) VALUES 
('admin', 'admin@gmail.com', 'vikundi', 'admin', 'System Administrator');

-- Sample data for testing
INSERT INTO users (username, email, password, role, full_name) VALUES 
('john_doe', 'john@vikundi.com', 'password123', 'employer', 'John Doe'),
('jane_smith', 'jane@vikundi.com', 'password123', 'employer', 'Jane Smith');

INSERT INTO employers (user_id, employee_id, department, position, hire_date) VALUES 
(2, 'EMP001', 'Operations', 'Field Officer', '2024-01-15'),
(3, 'EMP002', 'Finance', 'Accountant', '2024-02-01');
