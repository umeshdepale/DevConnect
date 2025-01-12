<?php
session_start();
require '../includes/db.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$notification_id = $data['notification_id'];
$action = $data['action'];

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

$developer_id = $_SESSION['user_id'];

// Fetch the booking ID and client ID from the notification
$query = "
    SELECT b.id AS booking_id, b.client_id 
    FROM notifications n
    JOIN bookings b ON n.booking_id = b.id
    WHERE n.id = '$notification_id' AND n.user_id = '$developer_id'
";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $booking_id = $row['booking_id'];
    $client_id = $row['client_id'];

    if ($action === 'accept') {
        // Generate a unique room link
        $room_link = "https://meet.jit.si/devconnect_" . uniqid();

        // Update booking status to accepted and save the room link
        $update_booking_query = "
            UPDATE bookings 
            SET status = 'accepted' WHERE id = '$booking_id' AND developer_id = '$developer_id'
        ";
        $conn->query($update_booking_query);

        // Update developer notification
        $developer_message = "Booking accepted. Join the call here.";
        $update_notification_query = "
            UPDATE notifications 
            SET message = '$developer_message', status = 'read' 
            WHERE id = '$notification_id'
        ";
        $conn->query($update_notification_query);

        // Send notification to the client
        $client_message = "Your request has been accepted. Join the call here.";
        $insert_client_notification = "
            INSERT INTO notifications (user_id, booking_id, message, status, created_at) 
            VALUES ('$client_id', '$booking_id', '$client_message', 'pending', NOW())
        ";
        $conn->query($insert_client_notification);

        echo json_encode(['success' => true, 'room_link' => $room_link]);
    } elseif ($action === 'reject') {
        // Reject the booking
        $update_booking_query = "
            UPDATE bookings 
            SET status = 'rejected' 
            WHERE id = '$booking_id' AND developer_id = '$developer_id'
        ";
        $conn->query($update_booking_query);

        // Update notification
        $reject_message = "Your booking request has been rejected.";
        $update_notification_query = "
            UPDATE notifications 
            SET message = '$reject_message', status = 'read' 
            WHERE id = '$notification_id'
        ";
        $conn->query($update_notification_query);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Notification not found']);
}

$conn->close();
?>
