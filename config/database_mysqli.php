<?php
/**
 * Database connection configuration for Vikundi vya Ushirika Attendance System
 * This file handles the connection to MySQL database using MySQLi for better compatibility
 */

class Database {
    // Database configuration parameters
    private $host = "localhost";
    private $db_name = "vikundi";
    private $username = "root";
    private $password = "12345678";
    public $conn;

    /**
     * Get database connection using MySQLi
     * @return mysqli|null Returns MySQLi connection object or null on failure
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // Create MySQLi connection
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            
            // Check for connection errors
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            // Set character set to UTF-8 for proper encoding
            $this->conn->set_charset("utf8");
            
        } catch(Exception $exception) {
            // Log connection error
            echo "Connection error: " . $exception->getMessage();
            return null;
        }

        return $this->conn;
    }

    /**
     * Close database connection
     */
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
            $this->conn = null;
        }
    }

    /**
     * Escape string to prevent SQL injection
     * @param string $string The string to escape
     * @return string Escaped string
     */
    public function escapeString($string) {
        if ($this->conn) {
            return $this->conn->real_escape_string($string);
        }
        return $string;
    }

    /**
     * Execute a query and return result
     * @param string $query The SQL query to execute
     * @return mysqli_result|bool Query result or false on failure
     */
    public function query($query) {
        if ($this->conn) {
            return $this->conn->query($query);
        }
        return false;
    }

    /**
     * Get the last inserted ID
     * @return int Last inserted ID
     */
    public function getLastInsertId() {
        if ($this->conn) {
            return $this->conn->insert_id;
        }
        return 0;
    }

    /**
     * Get number of affected rows
     * @return int Number of affected rows
     */
    public function getAffectedRows() {
        if ($this->conn) {
            return $this->conn->affected_rows;
        }
        return 0;
    }
}
?>
