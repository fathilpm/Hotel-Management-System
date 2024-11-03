<?php
// public/booking.php
session_start();
require_once './config/connect.php';
require_once 'functions.php'; // Include functions file for reusability

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if a room ID is provided in the URL
if (isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];

    // Fetch room details from the database
    $stmt = $pdo->prepare("SELECT * FROM Rooms WHERE room_id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) {
        echo "Room not found.";
        exit();
    }
} else {
    echo "Room not specified.";
    exit();
}

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $user_id = $_SESSION['user_id'];

    // Get current date
    $current_date = date('Y-m-d');

    // Validate check-in and check-out dates
    if ($check_in < $current_date) {
        echo "Booking cannot be done. Check-in date cannot be earlier than today's date.";
        exit();
    }

    if ($check_out <= $check_in) {
        echo "Invalid booking dates. Check-out date must be later than check-in date.";
        exit();
    }

    // Check if room is available
    if (!isRoomAvailable($room_id, $check_in, $check_out)) {
        echo "Sorry, this room is already booked for the selected dates.";
    } else {
        // Calculate the number of nights
        $check_in_date = strtotime($check_in);
        $check_out_date = strtotime($check_out);
        $nights = ($check_out_date - $check_in_date) / (60 * 60 * 24);

        // Calculate the total amount
        $total_amount = $nights * $room['price_per_night'];

        // Save booking to the database
        $stmt = $pdo->prepare("
            INSERT INTO Bookings (user_id, room_id, check_in_date, check_out_date, total_amount) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $room_id, $check_in, $check_out, $total_amount]);

        // Set session variable for the recent booking ID
        $_SESSION['recent_booking_id'] = $pdo->lastInsertId();

        // Redirect to confirmation page
        header('Location: confirmation.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="assets/js/app.js" defer></script>
    <title>Book Room - <?= htmlspecialchars($room['room_type']) ?></title>
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
        .booking-container {
            background-color: white; /* White background for the booking box */
            padding: 40px; /* Increased padding for more space */
            border-radius: 15px; /* Curved corners */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            width: 90%;
            max-width: 500px; /* Limit the width of the booking box */
            min-height: 400px; /* Set a minimum height for the container */
            text-align: center; /* Center the text inside */
        }
        h1 {
            margin-bottom: 10px; /* Space below heading */
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px; /* Space between form fields */
        }
        button {
            background-color: #007BFF; /* Button color */
            color: white; /* Button text color */
            border: none; /* No border */
            padding: 10px; /* Padding for the button */
            border-radius: 5px; /* Rounded corners for button */
            cursor: pointer; /* Pointer cursor on hover */
        }
        button:hover {
            background-color: #0056b3; /* Darker shade on hover */
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <h1>Book Room - <?= htmlspecialchars($room['room_type']) ?></h1>
        <p><?= htmlspecialchars($room['description']) ?></p>
        <p>Price per night: ₹<?= htmlspecialchars($room['price_per_night']) ?></p>
        <p>Max Occupancy: <?= htmlspecialchars($room['max_occupancy']) ?> people</p>

        <form method="POST">
            <label for="check_in">Check-in Date:</label>
            <input type="date" id="check_in" name="check_in" min="<?= date('Y-m-d') ?>" required>

            <label for="check_out">Check-out Date:</label>
            <input type="date" id="check_out" name="check_out" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>

            <button type="submit">Confirm Booking</button>
        </form>

        <p><a href="index.php">Back to Rooms</a></p>
    </div>
</body>
</html>
