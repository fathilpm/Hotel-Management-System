<?php
// public/rooms.php
require_once './config/connect.php';

$stmt = $pdo->query("SELECT * FROM Rooms WHERE availability = TRUE");
$rooms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head><title>Available Rooms</title></head>
<body>
<h1>Available Rooms</h1>
<?php foreach ($rooms as $room): ?>
    <div>
        <h2><?= htmlspecialchars($room['room_type']) ?></h2>
        <p><?= htmlspecialchars($room['description']) ?></p>
        <p>Price per night: ₹<?= htmlspecialchars($room['price_per_night']) ?></p>
        <a href="booking.php?room_id=<?= $room['room_id'] ?>">Book Now</a>
    </div>
<?php endforeach; ?>
</body>
</html>
