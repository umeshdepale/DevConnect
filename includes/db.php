<?php
$host = 'localhost';    // Host name
$user = 'root';         // Database username
$password = '';         // Database password
$dbname = 'cam'; // Database name

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>