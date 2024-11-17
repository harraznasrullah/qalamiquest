<?php
session_start(); // Start session
require 'db_connection.php'; // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle "Add Proposal" navigation
if (isset($_GET['action']) && $_GET['action'] === 'add_proposal') {
    // Insert a new proposal for the user in the database
    $userId = $_SESSION['user_id'];
    $query = "INSERT INTO proposals (user_id, title, status, last_saved) VALUES (?, 'New Proposal (Rename)', 0, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    if ($stmt->execute()) {
        // Get the ID of the newly created proposal
        $proposalId = $stmt->insert_id;
        $stmt->close();

        // Redirect to step1.php with the proposal ID
        header("Location: proposal/step1.php?proposal_id=$proposalId");
        exit;
    } else {
        $error = "Failed to create a new proposal. Please try again.";
    }
}

// Fetch recent proposals for the logged-in user
$userId = $_SESSION['user_id'];
$query = "SELECT proposal_id, title, status, last_saved FROM proposals WHERE user_id = ? ORDER BY last_saved DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$recentProposals = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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
            <span><?php echo strtoupper($_SESSION['user_name']); ?></span> <!-- Display logged in user's name -->
            <i class="fas fa-user"></i> <!-- Profile icon -->
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="assign_sv.php"><i class="fas fa-users"></i> Apply Supervisor</a>
        <a href="#"><i class="fas fa-bookmark"></i> Bookmark</a>
        <a href="edit_profile.php"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Dashboard Content -->
    <div id="main-content">
        <div class="service-grid">
            <div class="service-box" onclick="window.location.href='islamicsearch/islamicsearch.php'">
                <i class="fas fa-search"></i>
                <h3>Islamic Explorer</h3>
                <p>Providing verified resources from Quran, Hadith and Islamic Scholar.</p>
            </div>
            <div class="service-box">
                <i class="fas fa-comments"></i>
                <h3>Student Lounge</h3>
                <p>Connecting with others and exchanging ideas.</p>
            </div>
            <div class="service-box" onclick="window.location.href='student_dashboard.php?action=add_proposal'">
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
                    <?php if (empty($recentProposals)): ?>
                        <tr>
                            <td>No recent activity</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentProposals as $proposal): ?>
                            <tr>
                            <td><a href="proposal/step1.php?proposal_id=<?php echo $proposal['proposal_id']; ?>"><?php echo $proposal['title']; ?></a></td>
                
                                <td><?php echo date('d-m-Y', strtotime($proposal['last_saved'])) . ' | ' . date('h:i:s A', strtotime($proposal['last_saved'])); ?>
                                </td>
                                <td>
                                    <?php
                                    switch ($proposal['status']) {
                                        case 0:
                                            echo 'In Progress';
                                            break;
                                        case 1:
                                            echo 'Submitted';
                                            break;
                                        case 2:
                                            echo 'Approved';
                                            break;
                                        case 3:
                                            echo 'Required Progress';
                                            break;
                                        default:
                                            echo 'Unknown';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
