<?php
session_start(); // Start session
require 'db_connection.php'; // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'add_proposal') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['proposal_title'])) {
        $userId = $_SESSION['user_id'];
        $proposalTitle = htmlspecialchars($_POST['proposal_title']); // Sanitize input
        $query = "INSERT INTO proposals (user_id, title, status, last_saved) VALUES (?, ?, 0, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('is', $userId, $proposalTitle);
        if ($stmt->execute()) {
            $proposalId = $stmt->insert_id;
            $stmt->close();

            header("Location: proposal/step1.php?proposal_id=$proposalId");
            exit;
        } else {
            $error = "Failed to create a new proposal. Please try again.";
        }
    }
}


// Pagination setup
$results_per_page = 4;
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
$query = "SELECT proposal_id, title, status, last_saved FROM proposals WHERE user_id = ? AND is_deleted = 0 ORDER BY last_saved DESC LIMIT ? OFFSET ?";
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            /* Change this to 90% to ensure better responsiveness */
            max-width: 500px;
            /* Set a maximum width for the modal */
            border-radius: 8px;
            text-align: center;
            box-sizing: border-box;
            /* Ensure padding is included in the width calculation */
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        #proposalTitle {
            width: 100%;
            /* Ensure the input field uses the full available width */
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            /* Prevent padding from increasing the width */
        }

        .btn-view {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-view:hover {
            background-color: #0056b3;
        }

        .in-progress {
            color: black;
            font-weight: bold;
        }

        .submitted {
            color: #007bff;
            /* Blue for in progress */
            font-weight: bold;
        }

        .approved {
            color: #008000;
            /* Green for submitted */
            font-weight: bold;
        }

        .required-progress {
            color: #ff0000;
            /* Red for required progress */

            font-weight: bold;
        }

        .unknown {
            color: #6c757d;
            /* Gray for unknown */
            font-weight: bold;
        }

        /* Styling for the delete icon */
        .delete-btn {
            background-color: transparent;
            color: #f44336;
            /* Red color */
            border: none;
            cursor: pointer;
            font-size: 20px;
        }

        .delete-btn:hover {
            color: #d32f2f;
            /* Darker red on hover */
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

    <!-- Modal for Adding Proposal -->
    <div id="addProposalModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h1>Create New Proposal</h1>
            <form id="addProposalForm" action="student_dashboard.php?action=add_proposal" method="POST">
                <label for="proposalTitle">Proposal Title:</label>
                <input type="text" id="proposalTitle" name="proposal_title" required
                    placeholder="Enter your proposal title">
                <button type="submit" class="btn-view">Create</button>
            </form>
        </div>
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
            <div class="service-box" onclick="openModal()">
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
                                    $statusText = '';
                                    $statusClass = '';
                                    switch ($proposal['status']) {
                                        case 0:
                                            $statusText = 'In Progress';
                                            $statusClass = 'in-progress';
                                            break;
                                        case 1:
                                            $statusText = 'Submitted';
                                            $statusClass = 'submitted';
                                            break;
                                        case 2:
                                            $statusText = 'Approved';
                                            $statusClass = 'approved';
                                            break;
                                        case 3:
                                            $statusText = 'Required Progress';
                                            $statusClass = 'required-progress';
                                            break;
                                        default:
                                            $statusText = 'Unknown';
                                            $statusClass = 'unknown';
                                    }
                                    ?>
                                    <span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </td>
                                <td style="display: flex; justify-content: space-between; align-items: center;">
                                    <?php if ($proposal['status'] >= 0): ?>
                                        <!-- View Button -->
                                        <button class="btn-view"
                                            onclick="window.location.href='view_comment.php?proposal_id=<?php echo $proposal['proposal_id']; ?>'">
                                            View
                                        </button>

                                        <!-- Delete Confirmation Form (Trash bin icon) -->
                                        <form action="delete_proposal.php?proposal_id=<?php echo $proposal['proposal_id']; ?>"
                                            method="POST" onsubmit="return confirmDelete()" style="margin: 0;">
                                            <button type="submit" name="confirm_delete" class="delete-btn" title="Delete Proposal">
                                                <i class="fas fa-trash-alt"></i> <!-- Trash bin icon -->
                                            </button>
                                        </form>
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

            // Open Modal
            function openModal() {
                document.getElementById('addProposalModal').style.display = 'block';
            }

            // Close Modal
            function closeModal() {
                document.getElementById('addProposalModal').style.display = 'none';
            }

            // Close the modal if clicked outside
            window.onclick = function (event) {
                const modal = document.getElementById('addProposalModal');
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            };
            // JavaScript function to show confirmation dialog
            function confirmDelete() {
                return confirm('Are you sure you want to delete this proposal? This action cannot be undone.');
            }

        </script>
</body>

</html>