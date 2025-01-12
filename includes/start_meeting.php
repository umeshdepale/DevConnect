<?php
session_start();
require '../includes/db.php';

header("Content-Type: application/json");

// Get booking_id and client_id
$booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : null;
$client_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$booking_id) {
    echo json_encode(['success' => false, 'error' => 'Booking ID is required.']);
    exit();
}

if (!$client_id) {
    echo json_encode(['success' => false, 'error' => 'Client ID is required.']);
    exit();
}

// Fetch the developer_id from the bookings table
$booking_query = "SELECT developer_id FROM bookings WHERE id = '$booking_id' AND client_id = '$client_id'";
$result = $conn->query($booking_query);

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid booking or client mismatch.']);
    exit();
}

$row = $result->fetch_assoc();
$developer_id = $row['developer_id'];

if (!$developer_id) {
    echo json_encode(['success' => false, 'error' => 'Developer ID not found for this booking.']);
    exit();
}

// Log the meeting start time if not already logged
$insert_query = "
    INSERT INTO meeting_logs (booking_id, client_id, developer_id, start_time, status) 
    VALUES ('$booking_id', '$client_id', '$developer_id', NOW(), 'ongoing') 
    ON DUPLICATE KEY UPDATE start_time = NOW(), status = 'ongoing'
";

if ($conn->query($insert_query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
