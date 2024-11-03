<?php
session_start();
require_once './config/connect.php';
require_once 'functions.php'; // Include functions.php for isRoomAvailable function

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);
$roomId = $data['room_id'];
$checkIn = $data['check_in'];
$checkOut = $data['check_out'];

// Check room availability
$available = isRoomAvailable($pdo, $roomId, $checkIn, $checkOut);

// Return availability status as JSON
header('Content-Type: application/json');
echo json_encode(['available' => $available]);
