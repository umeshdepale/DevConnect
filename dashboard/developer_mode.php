<?php
session_start(); 
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the users table
$query = "SELECT * FROM `users` WHERE `id` = '$user_id'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $p = $result->fetch_assoc();
    $name = $p['full_name'];

    // Check if the user_id exists in the profiles table
    $query_profile = "SELECT * FROM `profiles` WHERE `user_id` = '$user_id'";
    $result_profile = $conn->query($query_profile);

    if ($result_profile->num_rows > 0) {
        // Profile exists, fetch data
        $profile = $result_profile->fetch_assoc();
        $skills = $profile['skills'];
        $github_link = $profile['github_link'];
        $charges_per_min = $profile['charges_per_min'];
        $status = $profile['status'];
    } else {
        // Profile does not exist, insert default profile data
        $skills = "No skills added";
        $github_link = "https://github.com/default";
        $charges_per_min = 0.00;
        $status = "unavailable";

        $insert_query = "INSERT INTO `profiles`(`user_id`, `charges_per_min`, `skills`, `github_link`, `status`)
                         VALUES ('$user_id', '$charges_per_min', '$skills', '$github_link', '$status')";

        if ($conn->query($insert_query) === TRUE) {
         //   echo "Default profile created for user_id: $user_id.<br>";
        } else {
          //  echo "Error inserting default profile: " . $conn->error;
        }
    }
} else {
    echo "User not found.";
}

if (isset($_POST['update'])) {
    // Sanitize and validate input data
    $skills = $conn->real_escape_string($_POST['skills']);
    $github_link = $conn->real_escape_string($_POST['github']);
    $charges_per_min = floatval($_POST['charges']); // Ensure numeric value

    // Check the status toggle
    $status = isset($_POST['status']) && $_POST['status'] === 'on' ? 'available' : 'unavailable';

    // Update the profiles table
    $update_query = "UPDATE `profiles` 
                     SET `skills` = '$skills', 
                         `github_link` = '$github_link', 
                         `charges_per_min` = '$charges_per_min', 
                         `status` = '$status' 
                     WHERE `user_id` = '$user_id'";

    if ($conn->query($update_query) === TRUE) {
        // Redirect back to the profile page with a success message
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: developer_mode.php");
        exit();
    } else {
        echo "Error updating profile: " . $conn->error;
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">    
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body class="bg-gray-100">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo Section -->
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-indigo-600">DevConnect</h1>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex space-x-4">
                    <a href="topup.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Top
                        Up</a>
                    <a href="index.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Switch
                        to Client Mode</a>
                </div>

                <!-- Mobile Hamburger Menu -->
                <div class="lg:hidden">
                    <button id="navToggle"
                        class="text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div id="mobileMenu" class="hidden lg:hidden bg-white shadow-md">
            <a href="topup.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Top Up</a>
            <a href="index.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Switch to Client Mode</a>
        </div>
        <script>
        // Toggle menu visibility for mobile
        document.getElementById("navToggle").addEventListener("click", function() {
            const mobileMenu = document.getElementById("mobileMenu");
            mobileMenu.classList.toggle("hidden");
        });
        </script>
    </nav>

    <!--- Navbar End -->

    <?php if(isset($_SESSION['success_message'])){ ?>
   <center> <div id="responseMessage" class="w-96 mt-4"><div class="bg-red-100 text-red-700 p-4 rounded-md"><?php echo $_SESSION['success_message']; ?> </div></div></center><?php } ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    <div id="developerNotificationsList" class="space-y-4">
    <!-- Notifications will be dynamically loaded here -->
</div>
        <h2 class="text-2xl font-bold mb-4">Manage Your Profile</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"><?php echo $name; ?></h3>
                    <?php if($status == 'available'){ ?>
                    <span class="px-2 py-1 text-sm bg-green-100 text-green-800 rounded">Availabe</span>
                    <?php } ?>
                    <?php if($status == 'unavailable'){ ?>
                    <span class="px-2 py-1 text-sm bg-red-100 text-red-800 rounded">Unavailable</span>
                    <?php } ?>
                </div>
                <p class="text-gray-600 mb-2">Charges: $<?php echo $charges_per_min ?></p>
                <p class="text-sm text-gray-500 mb-4">Skills: <?php echo $skills ?></p>
                <a href="<?php echo $github_link ?>" target="" class="text-indigo-600 hover:text-indigo-800">
                <i class="bi bi-github"></i> GitHub</a><br><br>
                <div class="flex space-x-4 ">
                
                    <button onclick="openEditModal()"
                        class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 ">
                        Edit
                    </button>
                </div>
            </div>



        </div>

        

    </div>

    <!-- Modals -->
    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white w-96 p-6 rounded-lg shadow">
        <h3 class="text-xl font-bold mb-4">Edit Profile</h3>
        <!-- Add method and action to form -->
        <form id="editForm" method="POST" action="">
            <div class="mb-4">
                <label for="editName" class="block text-sm font-medium text-gray-600">Name</label>
                <input type="text" id="editName" name="name" value="<?php echo htmlspecialchars($name); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    disabled>
            </div>
            <div class="mb-4">
                <label for="editSkills" class="block text-sm font-medium text-gray-600">Skills</label>
                <input type="text" id="editSkills" name="skills" value="<?php echo htmlspecialchars($skills); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>
            <div class="mb-4">
                <label for="editGithub" class="block text-sm font-medium text-gray-600">Github</label>
                <input type="text" id="editGithub" name="github" value="<?php echo htmlspecialchars($github_link); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>
            <div class="mb-4">
                <label for="editCharges" class="block text-sm font-medium text-gray-600">Charge Per Minute ($)</label>
                <input type="text" id="editCharges" name="charges" value="<?php echo htmlspecialchars($charges_per_min); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>
            <div class="mb-4">
                <label class="inline-flex items-center cursor-pointer">
                    <!-- Add an onchange handler to dynamically update the displayed status -->
                    <input type="checkbox" id="toggleSwitch" name="status" class="sr-only peer"
                        <?php echo $status === 'available' ? 'checked' : ''; ?> value="on"
                        onchange="updateStatusText(this)">
                    <div
                        class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                    </div>
                    <span id="statusText" class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                        <?php echo ucfirst($status); ?>
                    </span>
                </label>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeEditModal()"
                    class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" name="update" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>


    

    <script>
    // Edit Modal
    function openEditModal() {
        document.getElementById("editModal").classList.remove("hidden");
    }

    function closeEditModal() {
        document.getElementById("editModal").classList.add("hidden");
    }

       // Dynamically update the status text based on the toggle state
    function updateStatusText(toggle) {
        const statusText = document.getElementById('statusText');
        statusText.textContent = toggle.checked ? 'Available' : 'Unavailable';
    }

    function fetchDeveloperNotifications() {
    $.ajax({
        url: "../includes/fetch_notifications.php",
        type: "GET",
        success: function (response) {
            if (response.success && response.notifications.length > 0) {
                const notificationList = $("#developerNotificationsList");
                notificationList.empty();

                response.notifications.forEach(notification => {
                    // For Join Call
                    if (notification.message.includes("Join the call")) {
                        notificationList.append(`
                            <div class="bg-green-50 p-4 rounded-md shadow">
                                <p>${notification.message}</p><br>
                                <a href="${notification.room_link}" 
                                   class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600" 
                                   target="_blank">Join Call</a>
                            </div>
                        `);
                    } 
                    // For Pending Notifications
                    else if (notification.status === "pending") {
                        notificationList.append(`
                            <div class="bg-gray-50 p-4 rounded-md shadow">
                                <p><strong>${notification.name}</strong> sent a booking request.</p>
                                <p>Project: ${notification.project_title}</p>
                                <p>Description: ${notification.project_description}</p>
                                <div class="flex space-x-4 mt-2">
                                    <button onclick="handleDeveloperAction(${notification.notification_id}, 'accept')" 
                                            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                        Accept
                                    </button>
                                    <button onclick="handleDeveloperAction(${notification.notification_id}, 'reject')" 
                                            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                        Reject
                                    </button>
                                </div>
                            </div>
                        `);
                    }
                    // For Other Notifications (e.g., Rejected or Read)
                    else {
                        
                    }
                });
            } else {
                $("#developerNotificationsList").html(`<p class="text-gray-500">No new notifications.</p>`);
            }
        },
        error: function (error) {
            console.error("Error fetching notifications:", error);
        }
    });
}

// Poll notifications every 10 seconds
setInterval(fetchDeveloperNotifications, 2000);

/**
 * Handles developer's actions (Accept or Reject) for booking requests.
 * @param {number} notificationId - The ID of the notification.
 * @param {string} action - The action to perform ("accept" or "reject").
 */
function handleDeveloperAction(notificationId, action) {
    $.ajax({
        url: "../includes/update_booking_status.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({ notification_id: notificationId, action: action }),
        success: function (data) {
            if (data.success) {
                if (action === 'accept') {
                    alert("Booking accepted successfully. The client has been notified.");
                } else if (action === 'reject') {
                    alert("Booking rejected successfully. The client has been notified.");
                }
                fetchDeveloperNotifications(); // Refresh notifications
            } else {
                alert(`Failed to ${action} the booking. Please try again.`);
            }
        },
        error: function (error) {
            console.error("Error handling action:", error);
            alert("An error occurred. Please try again.");
        }
    });
}


    </script>
</body>

</html>