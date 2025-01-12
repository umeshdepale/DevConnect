<?php
session_start(); 


if (isset($_SESSION['user_id'])) {
    header("Location: dashboard"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an account</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex flex-col items-center">
<?php include("includes/header.php"); ?>
    <!-- Login Form -->
    <div id="responseMessage" class="w-96 mt-4"></div>
    <div class="bg-white p-8 rounded-lg shadow-md w-96 mt-12">
        <h2 class="text-2xl font-bold text-gray-700 text-center">Create an account</h2>
        <form id="signupForm">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-600">Name</label>
                <input type="name" name="name" id="name" placeholder="Enter your name" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-600">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-600">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>
            <input type="hidden" name="signup" value="1">
            <button type="submit"class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg focus:ring-2 focus:ring-blue-400">
                Sign Up
            </button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">
             
            <a href="login.php" class="text-blue-500 hover:underline">Login Here</a>.
        </p>
    </div>

    <script>
  $(document).ready(function () {
    $('#signupForm').on('submit', function (e) {
      e.preventDefault();

      $.ajax({
        url: 'includes/auth.php', 
        type: 'POST',
        data: $(this).serialize(),
        success: function (response) {
          if (response.trim() === "success") {
            // Redirect to the dashboard
            window.location.href = "dashboard";
          } else {
            // Show error message
            $('#responseMessage').html('<div class="bg-red-100 text-red-700 p-4 rounded-md">' + response + '</div>');
          }
        },
        error: function () {
          // Show error message
          $('#responseMessage').html('<div class="bg-red-100 text-red-700 p-4 rounded-md">An error occurred. Please try again.</div>');
        }
      });
    });
  });
</script>
</body>
</html>
