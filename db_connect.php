<?php
// db_connect.php - Improved with error handling and configuration
$host = 'localhost';
$dbname = 'adoptly_db';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Optional: Set a global function to handle database errors
    function db_error($e) {
        // For production, log errors instead of displaying them
        // error_log($e->getMessage());
        return "A database error occurred. Please try again later.";
    }
    
} catch (PDOException $e) {
    // For development purposes, show detailed error
    // For production, use a generic message
    die("Database connection failed: " . $e->getMessage());
}
?>