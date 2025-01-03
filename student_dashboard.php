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
    $userId = $_SESSION['user_id'];
    $query = "INSERT INTO proposals (user_id, title, status, last_saved) VALUES (?, 'New Proposal (Rename)', 0, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    if ($stmt->execute()) {
        $proposalId = $stmt->insert_id;
        $stmt->close();

        header("Location: proposal/step1.php?proposal_id=$proposalId");
        exit;
    } else {
        $error = "Failed to create a new proposal. Please try again.";
    }
}

// Pagination setup
$results_per_page = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $results_per_page;

// Total proposals count
$total_query = "SELECT COUNT(*) as total 
                FROM proposals p
                JOIN users u ON p.user_id = u.id 
                WHERE p.approval_date IS NOT NULL 
                AND u.title = 'student'";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_proposals = $total_row['total'];
$total_pages = ceil($total_proposals / $results_per_page);

// Fetch total number of proposals
$userId = $_SESSION['user_id'];
$total_proposals_query = "SELECT COUNT(*) as total FROM proposals WHERE user_id = ?";
$stmt = $conn->prepare($total_proposals_query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$total_result = $stmt->get_result();
$total_proposals = $total_result->fetch_assoc()['total'];
$stmt->close();

// Calculate total pages
$total_pages = ceil($total_proposals / $results_per_page);

// Fetch recent proposals for the logged-in user with pagination
$query = "SELECT proposal_id, title, status, last_saved FROM proposals WHERE user_id = ? ORDER BY last_saved DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('iii', $userId, $results_per_page, $offset);
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
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Existing styles from the previous code */
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

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            color: #007bff;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background-color: #f0f0f0;
        }

        .pagination .current {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .pagination .disabled {
            color: #6c757d;
            pointer-events: none;
            opacity: 0.6;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-left">
            <button class="open-btn" onclick="toggleSidebar()">☰</button> <!-- Sidebar toggle button -->
            QalamiQuest
        </div>
        <div class="navbar-right">
            <span><?php echo strtoupper($_SESSION['user_name']); ?></span> <!-- Display logged in user's name -->
            <i class="fas fa-user"></i> <!-- Profile icon -->
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="assign_sv.php"><i class="fas fa-users"></i> Apply Supervisor</a>
        <a href="islamicsearch/bookmark/view_bookmarks.php"><i class="fas fa-bookmark"></i> Bookmark</a>
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
            <div class="service-box" onclick="window.location.href='student_lounge/student_discussion.php'">
                <i class="fas fa-comments"></i>
                <h3>Student Hub</h3>
                <p>Connecting with others and exchanging ideas.</p>
            </div>
            <div class="service-box" onclick="window.location.href='student_dashboard.php?action=add_proposal'">
                <i class="fas fa-edit"></i>
                <h3>Add/Edit Proposal</h3>
                <p>Guiding you to write a proper proposal.</p>
            </div>
            <div class="service-box"
                onclick="window.location.href='qualitative_data_analysis/qualitative_data_analysis.php'">
                <i class="fas fa-chart-line"></i>
                <h3>Qualitative Data Analysis</h3>
                <p>Proceeding your qualitative research here in an easier way.</p>
            </div>
        </div>

        <!-- Recent Section -->
        <div class="recent-section">
            <h2>Recent Proposals</h2>
            <table class="recent-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date Modified</th>
                        <th>Status</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentProposals)): ?>
                        <tr>
                            <td colspan="4">No recent activity</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentProposals as $proposal): ?>
                            <tr>
                                <td>
                                    <p style="cursor: pointer;"
                                        onclick="checkProposalStatus(<?php echo $proposal['status']; ?>, <?php echo $proposal['proposal_id']; ?>)">
                                        <i class="fas fa-file"></i>
                                        <?php echo $proposal['title']; ?>
                                    </p>
                                </td>
                                <td><?php echo date('d-m-Y | h:i:s A', strtotime($proposal['last_saved'])); ?></td>
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
                                <td>
                                    <?php if ($proposal['status'] >= 0): ?>
                                        <button class="btn-view"
                                            onclick="window.location.href='view_comment.php?proposal_id=<?php echo $proposal['proposal_id']; ?>'">
                                            View
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

           <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1">First</a>
                    <a href="?page=<?php echo $page - 1; ?>">Previous</a>
                <?php endif; ?>

                <?php
                // Show page numbers with ellipsis for large number of pages
                $start = max(1, $page - 1);
                $end = min($total_pages, $page + 1);

                for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next</a>
                    <a href="?page=<?php echo $total_pages; ?>">Last</a>
                <?php endif; ?>
            </div>
        </div>


    <!-- Previous JavaScript remains the same -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.getElementById("main-content");

            if (sidebar.style.left === "0px") {
                sidebar.style.left = "-300px";
                mainContent.style.marginLeft = "0";
            } else {
                sidebar.style.left = "0";
                mainContent.style.marginLeft = "240px";
            }
        }

        function checkProposalStatus(status, proposalId) {
            if (status === 1 || status === 2) {
                alert("This proposal cannot be edited because it has been submitted or approved.");
            } else {
                window.location.href = "proposal/step1.php?proposal_id=" + proposalId;
            }
        }
    </script>
</body>

</html>