<?php
session_start();
include('../db_connection.php');

// Ensure only logged-in lecturers can access
if (!isset($_SESSION['title']) || $_SESSION['title'] !== 'lecturer') {
    header("Location: ../login.php");
    exit();
}

$lecturer_name = strtoupper($_SESSION['user_name']);

// Handle unarchive action
if (isset($_GET['action']) && $_GET['action'] == 'unarchive' && isset($_GET['proposal_id'])) {
    $proposal_id = intval($_GET['proposal_id']);

    // Update the proposal status to make it visible again
    $unarchive_query = "UPDATE proposals SET lecturer_visibility = 1 WHERE proposal_id = ?";
    $stmt = $conn->prepare($unarchive_query);
    $stmt->bind_param("i", $proposal_id);

    if ($stmt->execute()) {
        header("Location: lecturer_archive.php?unarchive_success=1");
        exit();
    } else {
        $unarchive_error = "Failed to unarchive proposal.";
    }
}

// Pagination setup
$results_per_page = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $results_per_page;

// Query to fetch archived proposals
$query = "SELECT p.proposal_id, p.title, p.approval_date, p.status, u.fullname AS student_name
          FROM proposals p
          JOIN users u ON p.user_id = u.id 
          WHERE p.approval_date IS NOT NULL 
          AND u.title = 'student'
          AND p.lecturer_visibility = 0
          ORDER BY p.approval_date DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $results_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Total archived proposals count
$total_query = "SELECT COUNT(*) as total 
                FROM proposals p
                JOIN users u ON p.user_id = u.id 
                WHERE p.approval_date IS NOT NULL 
                AND u.title = 'student'
                AND p.lecturer_visibility = 0";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_proposals = $total_row['total'];
$total_pages = ceil($total_proposals / $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Proposals - QalamiQuest</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="lecturer_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Existing styles from previous implementations */
        .archived-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .archived-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-unarchive {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            margin-left: 5px;
        }

        .btn-unarchive:hover {
            background-color: #218838;
        }
        .btn-cancel {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            margin-left: 5px;
        }

        .btn-cancel:hover {
            background-color: #c0392b;
        }

        .empty-archive {
            text-align: center;
            color: #6c757d;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .empty-archive i {
            margin-right: 8px;
            color: #a0a0a0;
        }

        .btn-view {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-view:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 123, 255, 0.3);
        }

        .btn-view:active {
            transform: translateY(1px);
            box-shadow: 0 1px 2px rgba(0, 123, 255, 0.2);
        }

        .btn-view i {
            margin-right: 5px;
        }
      
    </style>
</head>

<body>
    <!-- Navbar (same as lecturer dashboard) -->
    <div class="navbar">
        <div class="navbar-left">
            <button class="open-btn" onclick="toggleSidebar()">☰</button>
            QalamiQuest
        </div>
        <div class="navbar-right">
            <span><?php echo $lecturer_name; ?></span>
            <i class="fas fa-user"></i>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="lecturer_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="approval.php"><i class="fas fa-check-circle"></i> Approval</a>
        <a href="lecturer_archive.php"><i class="fas fa-archive"></i> Archived Proposals</a>
        <a href="../edit_profile.php"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <div class="archived-section">
            <div class="archived-header">
                <h1><i class="fas fa-archive"></i> Archived Proposals</h1>
            </div>

            <?php if (isset($_GET['unarchive_success'])): ?>
                <div class="alert alert-success">
                    Proposal successfully unarchived!
                </div>
            <?php endif; ?>

            <?php if ($total_proposals > 0): ?>
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
                            $no = 1 + (($page - 1) * $results_per_page);
                            while ($row = $result->fetch_assoc()) {
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
                                        <button type="button" onclick="confirmUnarchive(<?php echo $row['proposal_id']; ?>)"
                                            class="btn-unarchive">
                                            <i class="fas fa-undo"></i> Unarchive
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1">First</a>
                        <a href="?page=<?php echo $page - 1; ?>">Previous</a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);

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
            <?php else: ?>
                <div class="empty-archive">
                    <p><i class="fas fa-box-open"></i> No Archived Proposals</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Unarchive Confirmation Modal -->
    <div id="unarchiveModal" class="modal">
        <div class="modal-content">
            <h2>Confirm Unarchiving</h2>
            <p>Are you sure you want to bring this proposal back to your dashboard?</p>
            <div class="modal-buttons">
                <button onclick="cancelUnarchive()" class="btn-cancel">Cancel</button>
                <button onclick="proceedUnarchive()" class="btn-unarchive">Unarchive</button>
            </div>
        </div>
    </div>

    <script>
        let proposalToUnarchive = null;

        function confirmUnarchive(proposalId) {
            proposalToUnarchive = proposalId;
            document.getElementById('unarchiveModal').style.display = 'block';
        }

        function cancelUnarchive() {
            proposalToUnarchive = null;
            document.getElementById('unarchiveModal').style.display = 'none';
        }

        function proceedUnarchive() {
            if (proposalToUnarchive) {
                window.location.href = `lecturer_archive.php?action=unarchive&proposal_id=${proposalToUnarchive}`;
            }
        }

        function viewProposal(proposalId) {
            window.location.href = `../view_comment.php?proposal_id=${proposalId}`;
        }
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