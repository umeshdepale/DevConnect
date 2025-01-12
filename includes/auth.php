<?php
session_start();
require 'db.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['login'])) {
        // Handle login
        $email = $conn->real_escape_string($_POST['email']);
        $password = $conn->real_escape_string($_POST['password']);

        // Check if the email exists
        $check_email_query = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($check_email_query);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];

                // Return success response
                echo "success";
            } else {
                echo "Incorrect password. Please try again.";
            }
        } else {
            echo "Email not found. Please sign up.";
        }
    } elseif (isset($_POST['signup'])) {
        // Handle signup
        $full_name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $conn->real_escape_string($_POST['password']);
        
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if the email is already registered
        $check_email_query = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($check_email_query);

        if ($result->num_rows > 0) {
            // Email already registered
            echo "Email is already registered.";
        } else {
            // Insert user data into the database
            $insert_query = "INSERT INTO users (full_name, email, password) VALUES ('$full_name', '$email', '$hashed_password')";
            
            if ($conn->query($insert_query)) {
                // Set session variables
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;

                // Return success response
                echo "success";
            } else {
                echo "Error: " . $conn->error;
            }
        }
    } else {
        echo "Invalid request.";
    }
} else {
    echo "Invalid request method.";
}

$conn->close();
?>