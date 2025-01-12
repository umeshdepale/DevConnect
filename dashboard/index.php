<?php
session_start();
require '../includes/db.php'; // Include database connection
$user_id = $_SESSION['user_id'];
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); // Redirect to login if not logged in
    exit(); 
}

$balance = "0.00";
// Fetch balance from the database
$query = "SELECT balance FROM users WHERE id = '$user_id'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
$row = $result->fetch_assoc();
$balance = $row['balance'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevConnect - Find Your Tech Co-Founder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-slate-50 font-['Inter']">
    <!-- Navigation -->
    <?php include("../includes/header.php"); ?>  

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Balance Section -->
        <div class="bg-blue-50 border border-blue-200 text-blue-900 p-4 rounded mb-4">
        <p>Welcome <?php echo $_SESSION['full_name']; ?>! Your Balance is <b>$<?php echo number_format($balance, 2); ?></b></p>
        </div>

<div id="clientNotificationsList" class="space-y-4">
    <!-- Notifications will be dynamically loaded here -->
</div>

        <!-- Other content here -->
       
    </main>

    <!-- Main Content -->

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div id="developersList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php 
        $get_profile = "SELECT profiles.*, users.full_name
        FROM profiles
        JOIN users ON profiles.user_id = users.id AND profiles.user_id!='$user_id';";
        $result = $conn->query($get_profile);
        while($row = $result->fetch_assoc()) { ?>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold"><?php echo $row['full_name']; ?></h3>
                <?php if($row['status'] == 'available'){ ?>
                    <span class="px-2 py-1 text-sm bg-green-100 text-green-800 rounded">Available</span>
                <?php } else { ?>
                    <span class="px-2 py-1 text-sm bg-red-100 text-red-800 rounded">Unavailable</span>
                <?php } ?>
            </div>
            <p class="text-gray-600 mb-2">$<?php echo $row['charges_per_min']; ?></p>
            <p class="text-sm text-gray-500 mb-4"><?php echo $row['skills']; ?></p>
            <div class="flex justify-between items-center">
                <a href="<?php echo $row['github_link']; ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                    <i class="bi bi-github"></i> GitHub
                </a>
                <!-- Pass developer's unique user_id to the modal -->
                <button onclick="openBooking1('<?php echo $row['user_id']; ?>', '<?php echo $row['full_name']; ?>', '<?php echo $row['charges_per_min']; ?>')" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700" <?php if($row['status'] == 'unavailable'){ ?> disabled='true' <?php } ?>>
                    Book Now
                </button>
            </div>
        </div>
        <?php } ?>
    </div>
</main>

<!-- Modal for Project Posting -->
<div id="openBooking" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 id="modalTitle" class="text-lg font-medium text-gray-900 mb-4">Start Video Call</h3>
            <form id="projectForm" method="POST">
                <!-- Hidden input to store developer ID -->
                <input type="hidden" id="developerId" name="developer_id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Project Title</label>
                    <input type="text" name="project_title" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="project_description" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                              rows="4" required></textarea>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mt-4">Developer Charges Per Minute: <span id="developerCharges">$0.00</span></p>
                </div>
                <div id="balanceWarning" class="hidden text-red-600 text-sm mt-4">You do not have enough balance to start this call.</div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" onclick="closeBooking1()" 
                            class="closeModal bg-gray-200 px-4 py-2 rounded-md hover:bg-gray-300">Cancel</button>
                    <button type="submit" 
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Start</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
// Open the booking modal
function openBooking1(developerId, fullName, chargesPerMin) {
    // Set the developer ID in the hidden input field
    $("#developerId").val(developerId);

    // Set the modal title dynamically
    $("#modalTitle").text(`Start Video Call with ${fullName}`);

    // Display developer's charges per minute
    $("#developerCharges").text(`$${chargesPerMin}`);

    // Show the modal
    $("#openBooking").removeClass("hidden");
}

// Close the booking modal
function closeBooking1() {
    $("#openBooking").addClass("hidden");
}

// Handle form submission
$("#projectForm").on("submit", function (e) {
    e.preventDefault();

    const developerId = $("#developerId").val();
    const projectTitle = $("input[name='project_title']").val();
    const projectDescription = $("textarea[name='project_description']").val();

    // Fetch balance and charges (make an AJAX call)
    $.ajax({
    url: "../includes/check_balance.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({ developer_id: developerId }),
    success: function (data) {
        console.log(data); // Debug the response
        if (data.enough_balance) {
             sendBookingRequest(developerId, projectTitle, projectDescription);
        } else {
            $("#balanceWarning").removeClass("hidden");
        }
    },
    error: function (error) {
        console.error("Error:", error);
    }
});

});

// Send the booking request
function sendBookingRequest(developerId, projectTitle, projectDescription) {
    $.ajax({
        url: "../includes/send_booking.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            developer_id: developerId,
            project_title: projectTitle,
            project_description: projectDescription,
        }),
        success: function (data) {
            console.log(data); // Log the response for debugging

            if (data.success) {
                alert("Request sent to developer. Please wait for their response.");
                closeBooking1();
            } else {
                alert("Failed to send the request: " + (data.error || "Unknown error"));
            }
        },
        error: function (error) {
            console.error("Error:", error);
            alert("Failed to send the request. Please try again.");
        }
    });
}

function deleteNotification(notificationId) {
    if (!notificationId) {
        alert("Notification ID is missing.");
        return;
    }

    // Confirm before deletion
    if (!confirm("Are you sure you want to delete this notification?")) {
        return;
    }

    $.ajax({
        url: "../includes/delete_notification.php", // Backend endpoint for deleting notifications
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({ notification_id: notificationId }),
        success: function (response) {
            if (response.success) {
                alert("Notification deleted successfully.");            
                location.reload(); // Refresh the entire page

            } else {
                alert("Error deleting notification: " + response.error);
            }
        },
        error: function (error) {
            console.error("Error deleting notification:", error);
            alert("An error occurred while deleting the notification.");
        }
    });
}


function fetchClientNotifications() {
    $.ajax({
        url: "../includes/fetch_notifications.php",
        type: "GET",
        success: function (response) {
            if (response.success && response.notifications.length > 0) {
                const notificationList = $("#clientNotificationsList");
                const processedBookingIds = new Set(); // Track processed booking IDs
                notificationList.empty();

                response.notifications.forEach(notification => {
                    if (!processedBookingIds.has(notification.booking_id)) {
                        processedBookingIds.add(notification.booking_id);

                        if (notification.message.includes("accepted")) {
                            // Show Join and End Meeting options
                            notificationList.append(`
                                <div class="bg-green-50 p-4 rounded-md shadow">
                                    <p>${notification.message}</p>
                                    <a href="${notification.room_link}" 
                                       class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600" 
                                       target="_blank" onclick="startMeeting(${notification.booking_id})">
                                       Join Call
                                    </a>
                                    <button onclick="endMeeting(${notification.booking_id})" 
                                            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 mt-2">
                                        End Meeting
                                    </button>
                                </div>
                            `);
                        } else if (notification.status === "pending") {
                            notificationList.append(`
                                <div class="bg-yellow-50 p-4 rounded-md shadow">
                                    <p>Waiting for developer acceptance.</p>
                                    <p>Project: ${notification.project_title}</p>
                                    <p>Description: ${notification.project_description}</p>
                                </div>
                            `);
                        } else {
                            notificationList.append(`
                                <div class="bg-gray-100 p-4 rounded-md shadow">
                                    <p>${notification.message}</p>
                                       <button onclick="deleteNotification(${notification.booking_id})" 
                class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
            Delete
        </button>
                                </div>
                            
                           
                            
                            `);
                        }
                    }
                });
            }
        },
        error: function (error) {
            console.error("Error fetching notifications:", error);
        }
    });
}

setInterval(fetchClientNotifications, 2000);

function startMeeting(bookingId) {
    if (!bookingId) {
        console.error("Booking ID is required to start the meeting.");
        return;
    }

    console.log("Starting meeting for booking ID:", bookingId);

    $.ajax({
        url: "../includes/start_meeting.php",
        type: "POST",
        data: { booking_id: bookingId },
        success: function (response) {
            if (response.success) {
                console.log("Meeting started successfully:", response);
                alert("Meeting started successfully!");
            } else {
                console.error("Failed to start the meeting:", response.error);
                alert("Failed to start the meeting: " + response.error);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error starting the meeting:", error);
            alert("An error occurred while starting the meeting. Please try again.");
        }
    });
}



function endMeeting(bookingId) {
    if (!bookingId) {
        alert("Booking ID is missing.");
        return;
    }

    $.ajax({
        url: "../includes/end_meeting.php", // Backend endpoint to handle the meeting end
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({ booking_id: bookingId }),
        success: function (response) {
            if (response.success) {
                alert("Meeting ended successfully. Logs have been updated.");
                fetchClientNotifications(); // Refresh notifications
            } else {
                alert("Error ending meeting: " + response.error);
            }
        },
        error: function (error) {
            console.error("Error ending meeting:", error);
            alert("An error occurred while ending the meeting.");
        }
    });
}



</script>


</body>
</html>