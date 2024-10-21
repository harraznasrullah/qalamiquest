<?php
session_start(); // Ensure session is started to retrieve user data

// Example: Displaying the lecturer's name if logged in
$lecturer_name = strtoupper($_SESSION['fullname']); // Retrieve from session after login
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - QalamiQuest</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your external CSS file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: linear-gradient(to bottom, #ffffff, #EDFFFF);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #6A1B9A;
            padding: 10px 20px;
            height: 60px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .navbar-left {
            color: white;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .navbar-right {
            display: flex;
        }

        .navbar-right i {
            font-size: 24px;
            margin-left: 20px;
            cursor: pointer;
            color: white;
        }

        .navbar-right span {
            margin-left: 10px;
            font-size: 18px;
            color: white;
        }

        /* Sidebar styles */
        .sidebar {
            height: calc(100% - 50px);
            width: 240px;
            position: fixed;
            top: 50px;
            left: -300px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            overflow-x: hidden;
            padding-top: 40px;
            z-index: 1;
        }

        .sidebar a {
            padding: 15px;
            text-decoration: none;
            font-size: 16px;
            color: #818181;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            color: black;
        }

        .sidebar a i {
            margin-right: 10px;
            vertical-align: middle;
            width: 30px;
            text-align: center;
        }

        #main-content {
            transition: margin-left 0.3s;
            margin-left: 0;
            padding: 30px;
            /* Default margin */
        }

        .overview-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px;
            /* Top and bottom margins to provide space */
        }

        .overview-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            padding: 15px 25px;
            background-color: #e6d8f3;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }

        .overview-title i {
            margin-right: 10px;
        }

        .overview-controls {
            display: flex;
            align-items: center;
        }

        .approval-btn {
            background-color: #6A1B9A;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-right: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* Add shadow */
            transition: box-shadow 0.3s ease;
            /* Smooth transition for shadow */
        }

        .approval-btn:hover {
            background-color: #571a7a;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            /* Slightly stronger shadow on hover */

        }

        .approval-btn span {
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            margin-left: 5px;
            font-size: 14px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            position: relative;
        }

        .search-bar input {
            padding: 10px 14px;
            width: 250px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }

        .search-bar i {
            position: absolute;
            right: 10px;
            font-size: 20px;
            color: #999;
        }

        .recent-section {
            padding: 30px;
            border-collapse: collapse;
        }

        .recent-table th,
        .recent-table td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .recent-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .recent-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .recent-table tr:hover {
            background-color: #e1f5fe;
        }

        .recent-table .status-approved {
            color: green;
            font-weight: bold;
        }

        .recent-table .status-rejected {
            color: red;
            font-weight: bold;
        }

        .bell-icon {
            margin-right: 10px;
            /* Adjust the value as needed */
        }
    </style>
</head>

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
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Overview Section -->
        <div class="overview-section">
            <div class="overview-title">
                <i class="fas fa-clipboard-list"></i> OVERVIEW
            </div>
            <div class="overview-controls">
                <button class="approval-btn">
                    Approval
                </button>
                <div class="search-bar">
                    <input type="text" placeholder="Search Title">
                    <i class="fas fa-search"></i>
                </div>
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