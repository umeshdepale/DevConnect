<?php
session_start();
require '../includes/db.php'; // Include database connection

header("Content-Type: application/json"); // Ensure JSON output

$client_id = $_SESSION['user_id'];

// Decode JSON input and validate
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['developer_id'])) {
    echo json_encode(["error" => "Invalid input"]);
    exit();
}

$developer_id = $data['developer_id'];

// Get developer's charges per minute
$developer_query = "SELECT charges_per_min FROM profiles WHERE user_id = '$developer_id'";
$developer_result = $conn->query($developer_query);

if (!$developer_result || $developer_result->num_rows === 0) {
    echo json_encode(["error" => "Developer not found"]);
    exit();
}

$developer = $developer_result->fetch_assoc();
$charges_per_min = $developer['charges_per_min'];

// Get client's balance
$client_query = "SELECT balance FROM users WHERE id = '$client_id'";
$client_result = $conn->query($client_query);

if (!$client_result || $client_result->num_rows === 0) {
    echo json_encode(["error" => "Client not found"]);
    exit();
}

$client = $client_result->fetch_assoc();
$balance = $client['balance'];

// Check if client has enough balance
if ($balance >= $charges_per_min) {
    echo json_encode(["enough_balance" => true]);
} else {
    echo json_encode(["enough_balance" => false]);
}

$conn->close();
?>
