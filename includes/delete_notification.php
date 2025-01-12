<?php
session_start();
require '../includes/db.php'; // Include your database connection

header("Content-Type: application/json");

// Decode the JSON request from the frontend
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['notification_id']) || empty($data['notification_id'])) {
    echo json_encode(['success' => false, 'error' => 'Notification ID is required.']);
    exit();
}

$notification_id = $data['notification_id'];

// Debugging: Log the notification ID
error_log("Deleting Notification ID: $notification_id");

// Prepare the query to delete the notification
$query = "DELETE FROM notifications WHERE booking_id  = '$notification_id'";

if ($conn->query($query) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Notification deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Notification not found or already deleted.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
