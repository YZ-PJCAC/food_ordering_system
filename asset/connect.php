<?php
// Load environment variables
$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'cvjjintv_db_system';

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=localhost;dbname=cvjjintv_db_system;charset=utf8mb4", $username, $password);

    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: Log successful connection (remove in production)
    // error_log("Database connected successfully.");
} catch (PDOException $e) {
    // Log the error instead of displaying it
    error_log("Database connection error: " . $e->getMessage(), 3, 'errors.log');
    die("A database connection error occurred. Please try again later.");
}
?>
