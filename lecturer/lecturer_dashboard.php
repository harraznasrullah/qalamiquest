<?php
session_start(); // Ensure session is started to retrieve user data

// Example: Displaying the lecturer's name if logged in
$lecturer_name = strtoupper($_SESSION['user_name']); // Retrieve from session after login
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - QalamiQuest</title>
    <link rel="stylesheet" href="../styles.css"> <!-- Link to your external CSS file -->
    <link rel="stylesheet" href="lecturer_style.css"> <!-- Link to your external CSS file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<style>
    .approval-btn:hover {
        background-color: #004d4d;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        /* Slightly stronger shadow on hover */
    }
</style>

<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-left">
            <button class="open-btn" onclick="toggleSidebar()">☰</button>
            QalamiQuest
        </div>
        <div class="navbar-right">
            <i class="fas fa-bell bell-icon"></i>
            <span><?php echo $lecturer_name; ?></span>
            <i class="fas fa-user"></i>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="lecturer_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="approval.php"><i class="fas fa-check-circle"></i> Approval</a>
        <a href="../edit_profile.php"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Overview Section -->
        <div class="overview-section">
            <div class="overview-title">
                <i class="fas fa-clipboard-list"></i> OVERVIEW
            </div>
            <div class="overview-controls">
                <button class="approval-btn" onclick="window.location.href='approval.php'">
                    Approval
                </button>
                <button class="approval-btn" onclick="window.location.href='approve_sv.php'">
                    Supervisor Request
                </button>
            </div>
        </div>

        <!-- Table Section -->
        <div class="recent-section">
            <table class="recent-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Title</th>
                        <th>Student</th>
                        <th>Approval Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>The Impact of Islamic Teachings on Environmental Sustainability</td>
                        <td>Kamarul bin Rahim</td>
                        <td>3/7/2023</td>
                        <td class="status-approved">Approved</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Nurturing Taqwa in Modern Society</td>
                        <td>Rahimah binti Azman</td>
                        <td>7/9/2023</td>
                        <td class="status-rejected">Rejected</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Revitalizing Traditional Islamic Arts</td>
                        <td>Andre Alaba</td>
                        <td>4/10/2023</td>
                        <td class="status-approved">Approved</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <!-- JavaScript to toggle sidebar -->
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            var mainContent = document.getElementById("main-content");

            if (sidebar.style.left === "0px") {
                sidebar.style.left = "-250px";
                mainContent.style.marginLeft = "0";
            } else {
                sidebar.style.left = "0";
                mainContent.style.marginLeft = "250px";
            }
        }
    </script>

</body>

</html>