<?php
session_start(); // If user authentication is required, session management is necessary

// Example: Displaying the user's name if logged in
$user_name = strtoupper($_SESSION['user_name']); // Retrieve from session after login
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your external CSS file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-left">
            <button class="open-btn" onclick="toggleSidebar()">☰</button> <!-- Sidebar toggle button -->
            QalamiQuest
        </div>
        <div class="navbar-right">
            <i class="fas fa-bell bell-icon"></i> <!-- Bell icon -->
            <span><?php echo $user_name; ?></span> <!-- Display logged in user's name -->
            <i class="fas fa-user"></i> <!-- Profile icon -->
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="#"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#"><i class="fas fa-users"></i> Lecturer/Supervisor</a>
        <a href="#"><i class="fas fa-bookmark"></i> Bookmark</a>
        <a href="edit_profile.php"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a> <!-- Updated Logout link -->

    </div>

    <!-- Main Dashboard Content -->
    <div id="main-content">
        <div class="service-grid">
            <div class="service-box">
                <i class="fas fa-search"></i>
                <h3>Islamic Explorer</h3>
                <p>Providing verified resources from Quran, Hadith and Islamic Scholar.</p>
            </div>
            <div class="service-box">
                <i class="fas fa-comments"></i>
                <h3>Student Lounge</h3>
                <p>Connecting with others and exchanging ideas.</p>
            </div>
            <div class="service-box">
                <i class="fas fa-edit"></i>
                <h3>Add/Edit Proposal</h3>
                <p>Guiding you to write a proper proposal.</p>
            </div>
            <div class="service-box">
                <i class="fas fa-chart-line"></i>
                <h3>Qualitative Data Analysis</h3>
                <p>Proceeding your qualitative research here in an easier way.</p>
            </div>
        </div>

        <!-- Recent Section -->
        <div class="recent-section">
            <h3>Recent</h3>
            <table class="recent-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date Modified</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>No recent activity</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript to toggle sidebar -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.getElementById("main-content");

            // Check if the sidebar is currently open or closed
            if (sidebar.style.left === "0px") {
                sidebar.style.left = "-300px"; // Close the sidebar
                mainContent.style.marginLeft = "0"; // Reset the main content margin
            } else {
                sidebar.style.left = "0"; // Open the sidebar
                mainContent.style.marginLeft = "240px"; // Shift the main content
            }
        }
    </script>

</body>

</html>