<?php
/**
 * Database Configuration File
 * SEHS4517 Web Application Development and Management
 * Configuration for MySQL database connection
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cinema_reservation');

/**
 * Function to establish database connection
 * @return mysqli|null Returns mysqli connection object or null on failure
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set charset to utf8 for proper character handling
    $conn->set_charset("utf8");

    return $conn;
}
?>
