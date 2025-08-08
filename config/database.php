<?php
/**
 * Database connection configuration for Vikundi vya Ushirika Attendance System
 * This file handles the connection to MySQL database
 */

class Database {
    // Database configuration parameters
    private $host = "localhost";
    private $db_name = "vikundi";
    private $username = "root";
    private $password = "12345678";
    public $conn;

    /**
     * Get database connection
     * @return PDO|null Returns PDO connection object or null on failure
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // Create PDO connection with MySQL database
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            
            // Set PDO error mode to exception for better error handling
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set character set to UTF-8 for proper encoding
            $this->conn->exec("set names utf8");
            
        } catch(PDOException $exception) {
            // Log connection error
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>
