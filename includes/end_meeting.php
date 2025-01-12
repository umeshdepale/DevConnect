<?php
session_start();
require '../includes/db.php';

header("Content-Type: application/json");

// Validate the request
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['booking_id']) || empty($data['booking_id'])) {
    echo json_encode(['success' => false, 'error' => 'Booking ID is required.']);
    exit();
}

$booking_id = $data['booking_id'];

// Fetch meeting details and balances
$meeting_query = "
    SELECT 
        b.client_id, 
        b.developer_id, 
        ml.start_time, 
        p.charges_per_min, 
        u_client.balance AS client_balance, 
        u_dev.balance AS developer_balance
    FROM bookings b
    JOIN profiles p ON b.developer_id = p.user_id
    JOIN meeting_logs ml ON b.id = ml.booking_id
    JOIN users u_client ON b.client_id = u_client.id
    JOIN users u_dev ON b.developer_id = u_dev.id
    WHERE b.id = '$booking_id'
";
$meeting_result = $conn->query($meeting_query);

if ($meeting_result && $meeting_result->num_rows > 0) {
    $meeting = $meeting_result->fetch_assoc();
    $client_id = $meeting['client_id'];
    $developer_id = $meeting['developer_id'];
    $charges_per_min = $meeting['charges_per_min'];
    $client_balance = $meeting['client_balance'];
    $developer_balance = $meeting['developer_balance'];
    $start_time = $meeting['start_time'];

    // Calculate duration and charges
    $end_time = date("Y-m-d H:i:s");
    $duration_seconds = strtotime($end_time) - strtotime($start_time);
    $duration_minutes = max(ceil($duration_seconds / 60), 1); // Minimum 1 minute
    $total_charges = $duration_minutes * $charges_per_min;
    

    // Check if the client has sufficient balance
    if ($client_balance < $total_charges) {
        echo json_encode(['success' => false, 'error' => 'Insufficient balance in client account.']);
        exit();
    }

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Deduct from client and add to developer
        $new_client_balance = $client_balance - $total_charges;
        $new_developer_balance = $developer_balance + $total_charges;

        $update_client_balance = "UPDATE users SET balance = $new_client_balance WHERE id = '$client_id'";
        $update_developer_balance = "UPDATE users SET balance = $new_developer_balance WHERE id = '$developer_id'";

        $conn->query($update_client_balance);
        $conn->query($update_developer_balance);

        // Update meeting logs with end time, duration, charges, and status
        $log_query = "
            UPDATE meeting_logs
            SET end_time = '$end_time', duration = $duration_minutes, charges = $total_charges, status = 'completed'
            WHERE booking_id = '$booking_id'
        ";
        $conn->query($log_query);

        // Mark the booking as completed in the `bookings` table
        $update_booking_status = "UPDATE bookings SET status = 'completed' WHERE id = '$booking_id'";
        $conn->query($update_booking_status);

        // Remove related notifications for both client and developer
        $delete_notifications_query = "DELETE FROM notifications WHERE booking_id = '$booking_id'";
        $conn->query($delete_notifications_query);

        // Optionally, clear the booking record from the `bookings` table
        $delete_booking_query = "DELETE FROM bookings WHERE id = '$booking_id'";
        $conn->query($delete_booking_query);

        // Commit the transaction
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Meeting ended, balances updated, logs saved, and related data cleared.']);
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Failed to update balances: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Booking not found.']);
}

$conn->close();
?>
