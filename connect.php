<?php
// connect.php
$host = 'localhost';
$db = 'hotel_booking'; // Name of the database you created
$user = 'root';        // Default XAMPP MySQL username
$pass = '';            // Default XAMPP MySQL password is empty

try {
    // Create a new PDO instance for database connection
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // Set error mode to exception for error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Database connected successfully!";
} catch (PDOException $e) {
    // Display error message if connection fails
    die("Could not connect to the database $db: " . $e->getMessage());
}
?>
