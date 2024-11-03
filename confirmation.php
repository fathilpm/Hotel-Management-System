<?php
// public/confirmation.php
session_start();
require_once './config/connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if a recent booking exists in the session (or fetch the latest booking)
if (isset($_SESSION['recent_booking_id'])) {
    $booking_id = $_SESSION['recent_booking_id'];

    // Fetch booking details from the database
    $stmt = $pdo->prepare("
        SELECT 
            b.booking_id, b.check_in_date, b.check_out_date, b.total_amount, b.created_at,
            r.room_type, r.price_per_night
        FROM Bookings b
        JOIN Rooms r ON b.room_id = r.room_id
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        echo "Booking details not found.";
        exit();
    }
} else {
    echo "No booking details available.";
    exit();
}

// Clear recent booking ID after displaying confirmation
unset($_SESSION['recent_booking_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="assets/js/app.js" defer></script>
    <title>Booking Confirmation</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5; /* Light background color for contrast */
        }
        .confirmation-container {
            background-color: white; /* White background for the confirmation box */
            padding: 40px; /* Padding for spacing */
            border-radius: 15px; /* Curved corners */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            width: 90%;
            max-width: 500px; /* Limit the width of the confirmation box */
            text-align: center; /* Center the text inside */
        }
        table {
            width: 100%; /* Full width for the table */
            margin-top: 20px; /* Space above the table */
            border-collapse: collapse; /* Collapse borders */
        }
        th, td {
            padding: 10px; /* Padding for table cells */
            border: 1px solid #ddd; /* Light border for cells */
            text-align: left; /* Left align text */
        }
        th {
            background-color: #f2f2f2; /* Light background for table header */
        }
        a {
            text-decoration: none; /* Remove underline from links */
            color: #007BFF; /* Link color */
        }
        a:hover {
            text-decoration: underline; /* Underline on hover */
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <h1>Booking Confirmation</h1>
        <p>Thank you for your booking! Here are your booking details:</p>

        <table>
            <tr>
                <th>Booking ID:</th>
                <td><?= htmlspecialchars($booking['booking_id']) ?></td>
            </tr>
            <tr>
                <th>Room Type:</th>
                <td><?= htmlspecialchars($booking['room_type']) ?></td>
            </tr>
            <tr>
                <th>Check-in Date:</th>
                <td><?= htmlspecialchars($booking['check_in_date']) ?></td>
            </tr>
            <tr>
                <th>Check-out Date:</th>
                <td><?= htmlspecialchars($booking['check_out_date']) ?></td>
            </tr>
            <tr>
                <th>Total Amount:</th>
                <td>₹<?= htmlspecialchars($booking['total_amount']) ?></td>
            </tr>
            <tr>
                <th>Booking Date:</th>
                <td><?= htmlspecialchars($booking['created_at']) ?></td>
            </tr>
        </table>

        <p><a href="index.php">Return to Home</a></p>
    </div>
</body>
</html>
