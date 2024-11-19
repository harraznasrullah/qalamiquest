<?php
session_start(); // Ensure session is started to retrieve user data
include('../db_connection.php');

// Example: Displaying the lecturer's name if logged in
$lecturer_name = strtoupper($_SESSION['user_name']); // Retrieve from session after login

// Add a query to get the number of pending supervisor requests
// Note: Replace with your actual database connection and query
$query = "SELECT COUNT(*) as request_count FROM supervisors WHERE status = 'pending'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$pending_requests = $row['request_count'];

$query = "SELECT COUNT(*) as status_count FROM proposals WHERE status = 1";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$pending_status = $row['status_count'];

// Modified query to fetch proposals based on approval_date
$query = "SELECT p.proposal_id, p.title, p.approval_date, p.status, u.fullname AS student_name
          FROM proposals p
          JOIN users u ON p.user_id = u.id 
          WHERE p.approval_date IS NOT NULL 
          AND u.title = 'student'
          ORDER BY p.approval_date DESC"; // Added ORDER BY to show most recent first
$result = $conn->query($query);
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - QalamiQuest</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="lecturer_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Existing styles */
        .approval-btn:hover {
            background-color: #004d4d;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        /* New notification styles */
        .relative {
            position: relative;
        }

        .absolute {
            position: absolute;
        }

        .-top-2 {
            top: 0.5rem;
        }

        .-right-2 {
            right: -0.5rem;
        }

        .inline-flex {
            display: inline-flex;
        }

        .items-center {
            align-items: center;
        }

        .justify-center {
            justify-content: center;
        }

        .rounded-full {
            border-radius: 9999px;
        }

        .bg-red-500 {
            background-color: #ef4444;
        }

        .text-xs {
            font-size: 0.75rem;
            line-height: 1rem;
        }

        .text-white {
            color: #ffffff;
        }

        .h-5 {
            height: 1.25rem;
        }

        .w-5 {
            width: 1.25rem;
        }

        /* Additional styles for notification badge */
        .notification-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            min-width: 20px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .btn-view {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-view:hover {
            background-color: #0056b3;
        }

        .btn-view i {
            margin-right: 5px;
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
                <button class="approval-btn relative" onclick="window.location.href='approval.php'">
                    Approval
                    <?php if ($pending_status > 0): ?>
                        <div class="notification-badge">
                            <?php echo $pending_status; ?>
                        </div>
                    <?php endif; ?>
                </button>
                <button class="approval-btn relative" onclick="window.location.href='approve_sv.php'">
                    Supervisor Request
                    <?php if ($pending_requests > 0): ?>
                        <div class="notification-badge">
                            <?php echo $pending_requests; ?>
                        </div>
                    <?php endif; ?>
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
                        <th>Action</th>


                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            // Map status codes to human-readable text
                            $status_text = $row['status'] == 2 ? 'Approved' : 'Required Progress';
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars(ucwords(strtolower($row['title']))); ?></td>
                                <td><?php echo htmlspecialchars(ucwords(strtolower($row['student_name']))); ?></td>
                                <td><?php echo date('d-m-Y | h:i A', strtotime($row['approval_date'])); ?></td>
                                <td><?php echo $status_text; ?></td>
                                <td>
                                    <button type="button" onclick="viewProposal(<?php echo $row['proposal_id']; ?>)"
                                        class="btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="6">No proposals found.</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>

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
        function viewProposal(proposalId) {
            window.location.href = `../view_comment.php?proposal_id=${proposalId}`;
        }
    </script>

</body>

</html>