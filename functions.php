<?php
// functions.php

// Connect to the database
require_once './config/connect.php';

/**
 * Sanitize user input to prevent SQL injection and XSS
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Check if a user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to a specified URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Calculate total amount for a booking
 */
function calculateTotalAmount($roomPrice, $nights) {
    return $roomPrice * $nights;
}

/**
 * Fetch user details from the database
 */
function getUserDetails($userId) {
    global $pdo; // Access the global PDO connection
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Fetch all users from the database for admin purposes
 */
function getAllUsers() {
    global $pdo; // Access the global PDO connection
    $stmt = $pdo->prepare("SELECT * FROM Users");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Fetch available rooms from the database
 */
function getAvailableRooms($limit = 5) {
    global $pdo; // Access the global PDO connection
    $stmt = $pdo->prepare("SELECT * FROM Rooms WHERE available = 1 LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Check if a room is available for the given dates
 */
function isRoomAvailable($roomId, $checkIn, $checkOut) {
    global $pdo; // Access the global PDO connection
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM Bookings 
        WHERE room_id = ? 
        AND (
            (check_in_date < ? AND check_out_date > ?) OR
            (check_in_date <= ? AND check_out_date > ?)
        )
    ");
    $stmt->execute([$roomId, $checkOut, $checkIn, $checkIn, $checkIn]);
    return $stmt->fetchColumn() == 0; // Returns true if no bookings overlap
}

/**
 * Add a new booking to the database
 */
function addBooking($userId, $roomId, $checkIn, $checkOut, $totalAmount) {
    global $pdo; // Access the global PDO connection
    $stmt = $pdo->prepare("
        INSERT INTO Bookings (user_id, room_id, check_in_date, check_out_date, total_amount) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $roomId, $checkIn, $checkOut, $totalAmount]);
    return $pdo->lastInsertId(); // Return the last inserted booking ID
}

/**
 * Fetch booking details by booking ID
 */
function getBookingDetails($bookingId) {
    global $pdo; // Access the global PDO connection
    $stmt = $pdo->prepare("
        SELECT 
            b.booking_id, b.check_in_date, b.check_out_date, b.total_amount, b.created_at,
            r.room_type
        FROM Bookings b
        JOIN Rooms r ON b.room_id = r.room_id
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$bookingId]);
    return $stmt->fetch();
}

/**
 * Cancel a booking
 */
function cancelBooking($bookingId) {
    global $pdo; // Access the global PDO connection
    $stmt = $pdo->prepare("DELETE FROM Bookings WHERE booking_id = ?");
    return $stmt->execute([$bookingId]);
}

/**
 * Log in user and set session variables
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_name'] = $user['name']; // Assuming there's a name field
    $_SESSION['user_role'] = $user['role']; // Store the role in session
}

/**
 * Logout user and destroy session
 */
function logoutUser() {
    session_unset();
    session_destroy();
}
?>
