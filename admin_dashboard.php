<?php
session_start();
require_once './config/connect.php';

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all users
$usersStmt = $pdo->prepare("SELECT * FROM Users");
$usersStmt->execute();
$users = $usersStmt->fetchAll();

// Fetch all bookings
$bookingsStmt = $pdo->prepare("SELECT b.booking_id, b.check_in_date, b.check_out_date, b.total_amount, r.room_type, u.name 
                                FROM Bookings b 
                                JOIN Rooms r ON b.room_id = r.room_id 
                                JOIN Users u ON b.user_id = u.user_id");
$bookingsStmt->execute();
$bookings = $bookingsStmt->fetchAll();

// Handle booking cancellation
if (isset($_POST['cancel_booking'])) {
    $bookingId = $_POST['booking_id'];

    // Prepare and execute the deletion
    $cancelStmt = $pdo->prepare("DELETE FROM Bookings WHERE booking_id = ?");
    if ($cancelStmt->execute([$bookingId])) {
        echo "<script>alert('Booking cancelled successfully.');</script>";
        // Refresh bookings list
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Error cancelling booking.');</script>";
    }
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];

    // Prepare and execute the deletion query
    $deleteStmt = $pdo->prepare("DELETE FROM Users WHERE user_id = ?");
    if ($deleteStmt->execute([$userId])) {
        echo "<script>alert('User deleted successfully.');</script>";
        // Refresh the users list by redirecting
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Error deleting user.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f7f7f7;
        }
        h1 {
            color: #007BFF;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        .logout-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .logout-button:hover {
            background-color: #c82333;
        }
        .cancel-button {
            color: #dc3545;
            background: none;
            border: none;
            cursor: pointer;
        }
        .cancel-button:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function confirmCancellation() {
            return confirm("Are you sure you want to cancel this booking?");
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
        
        <h2>All Users</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th> <!-- Action column for Delete -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <?php if ($user['role'] !== 'admin'): ?> <!-- Hide Delete button for admin role -->
                                <!-- Delete button with form (visible only for non-admin users) -->
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">
                                    <button type="submit" name="delete_user" class="cancel-button">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                        <td><?= htmlspecialchars($booking['name']) ?></td>
                        <td><?= htmlspecialchars($booking['room_type']) ?></td>
                        <td><?= htmlspecialchars($booking['check_in_date']) ?></td>
                        <td><?= htmlspecialchars($booking['check_out_date']) ?></td>
                        <td>₹<?= htmlspecialchars($booking['total_amount']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirmCancellation();">
                                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
                                <button type="submit" name="cancel_booking" class="cancel-button">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a class="logout-button" href="logout.php">Logout</a>
    </div>
</body>
</html>
