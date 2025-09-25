<?php
// Database configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'bookdress_db'
];

// Function to get database connection
function getDbConnection() {
    global $db_config;
    
    try {
        $mysqli = new mysqli(
            $db_config['host'],
            $db_config['username'],
            $db_config['password'],
            $db_config['database']
        );

        // Check connection
        if ($mysqli->connect_error) {
            throw new Exception("Database connection failed: " . $mysqli->connect_error);
        }

        // Set character set
        $mysqli->set_charset('utf8mb4');
        
        return $mysqli;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}
?> 