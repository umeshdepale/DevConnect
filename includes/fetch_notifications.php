<?php
session_start();
require '../includes/db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

$is_client = false;

// Check if user is a client or developer
$check_role_query = "SELECT * FROM bookings WHERE client_id = '$user_id'";
$check_role_result = $conn->query($check_role_query);

if ($check_role_result && $check_role_result->num_rows > 0) {
    $is_client = true;
}

if ($is_client) {
    // Fetch notifications for clients
    $query = "
        SELECT n.id AS notification_id, n.booking_id, n.message, n.status, n.created_at, 
               b.project_title, b.project_description, u.full_name AS developer_name 
        FROM notifications n
        JOIN bookings b ON n.booking_id = b.id
        JOIN users u ON b.developer_id = u.id
        WHERE b.client_id = '$user_id'
        ORDER BY n.created_at DESC
    ";
} else {
    // Fetch notifications for developers
    $query = "
        SELECT n.id AS notification_id, n.booking_id, n.message, n.status, n.created_at, 
               b.project_title, b.project_description, u.full_name AS client_name 
        FROM notifications n
        JOIN bookings b ON n.booking_id = b.id
        JOIN users u ON b.client_id = u.id
        WHERE n.user_id = '$user_id'
        ORDER BY n.created_at DESC
    ";
}

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'notification_id' => $row['notification_id'],
            'booking_id' => $row['booking_id'],
            'message' => $row['message'],
            'status' => $row['status'],
            'project_title' => $row['project_title'],
            'project_description' => $row['project_description'],
            'name' => $is_client ? $row['developer_name'] : $row['client_name'],
            'room_link' => "https://meet.jit.si/" . $row['booking_id'] // Add Jitsi room link
        ];
    }
    echo json_encode(['success' => true, 'notifications' => $notifications]);
} else {
    echo json_encode(['success' => true, 'notifications' => []]);
}

$conn->close();