<?php
session_start();
require '../includes/db.php';

header("Content-Type: application/json");

$client_id = $_SESSION['user_id'];
$client_name = $_SESSION['full_name'];
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!$data || !isset($data['developer_id'], $data['project_title'], $data['project_description'])) {
    echo json_encode(["success" => false, "error" => "Invalid input"]);
    exit();
}

$developer_id = $data['developer_id'];
$project_title = $data['project_title'];
$project_description = $data['project_description'];

// Insert booking request
$insert_query = "
    INSERT INTO bookings (client_id, developer_id, project_title, project_description, status, created_at) 
    VALUES ('$client_id', '$developer_id', '$project_title', '$project_description', 'pending', NOW())
";

if ($conn->query($insert_query) === TRUE) {
    $booking_id = $conn->insert_id; // Get the inserted booking ID

    // Insert notification for the developer
    $notification_query = "
        INSERT INTO notifications (booking_id, user_id, message, status)
        VALUES ('$booking_id', '$developer_id', 'New booking request from $client_name', 'pending')
    ";
    $conn->query($notification_query);

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}

$conn->close();
?>
