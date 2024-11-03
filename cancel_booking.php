<?php
// public/cancel_booking.php
require_once './config/connect.php';
require_once './functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'];
    if (cancelBooking($bookingId)) {
        redirect('index.php'); // Redirect back to index after cancellation
    } else {
        echo "Failed to cancel the booking.";
    }
}
?>
