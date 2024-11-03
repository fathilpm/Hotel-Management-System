<?php
session_start();
require_once './config/connect.php';
require_once './functions.php';

// Fetch user details if logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserDetails($_SESSION['user_id']); // Use the getUserDetails function
    // Fetch user bookings
    $bookingsStmt = $pdo->prepare("SELECT b.booking_id, b.check_in_date, b.check_out_date, b.total_amount, r.room_type 
                                    FROM Bookings b 
                                    JOIN Rooms r ON b.room_id = r.room_id 
                                    WHERE b.user_id = ?");
    $bookingsStmt->execute([$user['user_id']]);
    $bookings = $bookingsStmt->fetchAll();
}

// Handle booking cancellation
if (isset($_POST['cancel_booking'])) {
    $bookingId = $_POST['booking_id'];
    if (cancelBooking($bookingId)) {
        header("Location: index.php"); // Redirect to refresh bookings
        exit();
    }
}

// Fetch all users and their bookings if admin
if (isAdmin()) {
    $allUsersStmt = $pdo->prepare("SELECT * FROM Users");
    $allUsersStmt->execute();
    $allUsers = $allUsersStmt->fetchAll();

    // Fetch all bookings
    $allBookingsStmt = $pdo->prepare("SELECT b.booking_id, b.check_in_date, b.check_out_date, b.total_amount, r.room_type, u.name 
                                       FROM Bookings b 
                                       JOIN Rooms r ON b.room_id = r.room_id 
                                       JOIN Users u ON b.user_id = u.user_id");
    $allBookingsStmt->execute();
    $allBookings = $allBookingsStmt->fetchAll();
}

// Fetch available rooms for guests
$roomsStmt = $pdo->prepare("SELECT * FROM Rooms WHERE available = 1");
$roomsStmt->execute();
$rooms = $roomsStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="assets/js/app.js" defer></script>
    <title>Welcome to Our Hotel</title>
    <script>
        function confirmCancel() {
            return confirm("Are you sure you want to cancel this booking?");
        }
    </script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 80%;
            max-width: 1200px; /* Optional max width */
            text-align: center; /* Center text inside the container */
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Optional styling */
            background-color: #fff; /* Optional background color */
            border-radius: 8px; /* Optional rounded corners */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to SeaSide Resort</h1>
        <p>Your comfort is our priority. Book your stay with us today!</p>

        <!-- User Navigation -->
        <div>
            <?php if ($user): ?>
                <p>Hello, <?= htmlspecialchars($user['name']) ?>!</p>
                <a class="logout-button" href="logout.php">Logout</a>
            <?php else: ?>
                <p><a href="login.php">Login</a> | <a href="register.php">Register</a></p>
            <?php endif; ?>
        </div>

        <?php if (isAdmin()): ?>
            <h2>All Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allUsers as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['user_id']) ?></td>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['role']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>All Bookings</h2>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User Name</th>
                        <th>Room Type</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allBookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                            <td><?= htmlspecialchars($booking['name']) ?></td>
                            <td><?= htmlspecialchars($booking['room_type']) ?></td>
                            <td><?= htmlspecialchars($booking['check_in_date']) ?></td>
                            <td><?= htmlspecialchars($booking['check_out_date']) ?></td>
                            <td>$<?= htmlspecialchars($booking['total_amount']) ?></td>
                            <td>
                                <form action="" method="POST" style="display:inline;" onsubmit="return confirmCancel();">
                                    <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
                                    <button type="submit" name="cancel_booking">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <h2>Featured Rooms</h2>
            <table>
                <thead>
                    <tr>
                        <th>Room Type</th>
                        <th>Description</th>
                        <th style="width: 150px;">Price per Night</th> <!-- Wider column -->
                        <th>Max Occupancy</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rooms): ?>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?= htmlspecialchars($room['room_type']) ?></td>
                                <td><?= htmlspecialchars($room['description']) ?></td>
                                <td>₹<?= htmlspecialchars($room['price_per_night']) ?></td>
                                <td><?= htmlspecialchars($room['max_occupancy']) ?></td>
                                <td><a href="booking.php?room_id=<?= $room['room_id'] ?>">Book Now</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No rooms available at this time.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- User Bookings Section -->
            <?php if ($user): ?>
                <h2>Your Bookings</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Room Type</th>
                            <th>Check-in Date</th>
                            <th>Check-out Date</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings): ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                                    <td><?= htmlspecialchars($booking['room_type']) ?></td>
                                    <td><?= htmlspecialchars($booking['check_in_date']) ?></td>
                                    <td><?= htmlspecialchars($booking['check_out_date']) ?></td>
                                    <td>₹<?= htmlspecialchars($booking['total_amount']) ?></td>
                                    <td>
                                        <form action="" method="POST" style="display:inline;" onsubmit="return confirmCancel();">
                                            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
                                            <button type="submit" name="cancel_booking">Cancel</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">You have no bookings yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
