<?php
require_once './config/connect.php'; // Adjust the path if necessary

// Prepare the admin user details
$adminEmail = "admin@gmail.com";
$adminPassword = password_hash("admin123", PASSWORD_DEFAULT); // Hash the password
$adminName = "Admin";
$adminRole = "admin";
$adminid = 1;

// SQL query to insert the admin user
$sql = "INSERT INTO users (user_id, name, email, password, role) VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);

// Execute the statement
try {
    $stmt->execute([$adminid, $adminName, $adminEmail, $adminPassword, $adminRole]);
    echo "Admin user created successfully.";
} catch (Exception $e) {
    echo "Error creating admin user: " . $e->getMessage();
}
?>
