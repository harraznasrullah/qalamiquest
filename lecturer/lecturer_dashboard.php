<?php
session_start();
include('../db_connection.php');

$lecturer_name = strtoupper($_SESSION['user_name']);
$lecturer_id = $_SESSION['user_id']; // Assuming the lecturer's ID is stored in the session

// Handle proposal archiving/deactivation
if (isset($_GET['action']) && $_GET['action'] == 'archive' && isset($_GET['proposal_id'])) {
    $proposal_id = intval($_GET['proposal_id']);

    // Update the proposal status to indicate it's archived/hidden for lecturer
    $archive_query = "UPDATE proposals SET lecturer_visibility = 0 WHERE proposal_id = ?";
    $stmt = $conn->prepare($archive_query);
    $stmt->bind_param("i", $proposal_id);

    if ($stmt->execute()) {
        // Redirect to prevent form resubmission
        header("Location: lecturer_dashboard.php?archive_success=1");
        exit();
    } else {
        // Handle error
        $archive_error = "Failed to archive proposal.";
    }
}

// Pagination setup
$results_per_page = 6;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $results_per_page;

// Total proposals count with the same filters as the main query
$total_query = "SELECT COUNT(*) as total 
                FROM proposals p
                JOIN users u ON p.user_id = u.id 
                JOIN supervisors s ON p.user_id = s.student_id
                WHERE p.approval_date IS NOT NULL 
                AND u.title = 'student'
                AND p.is_deleted = 0
                AND (p.lecturer_visibility IS NULL OR p.lecturer_visibility = 1)
                AND s.supervisor_id = ?";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param("i", $lecturer_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_proposals = $total_row['total'];
$total_pages = ceil($total_proposals / $results_per_page);

// Main query to fetch proposals
$query = "SELECT p.proposal_id, p.title, p.approval_date, p.status, p.is_deleted, u.fullname AS student_name
          FROM proposals p
          JOIN users u ON p.user_id = u.id 
          JOIN supervisors s ON p.user_id = s.student_id
          WHERE p.approval_date IS NOT NULL 
          AND u.title = 'student'
          AND p.is_deleted = 0
          AND (p.lecturer_visibility IS NULL OR p.lecturer_visibility = 1)
          AND s.supervisor_id = ?
          ORDER BY p.approval_date DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $lecturer_id, $results_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Pending approvals count (filtered by supervisor_id)
$status_query = "SELECT COUNT(*) as status_count 
                 FROM proposals p
                 JOIN supervisors s ON p.user_id = s.student_id
                 WHERE p.status = 1
                 AND s.supervisor_id = ?";
$status_stmt = $conn->prepare($status_query);
$status_stmt->bind_param("i", $lecturer_id);
$status_stmt->execute();
$status_result = $status_stmt->get_result();
$row_status = $status_result->fetch_assoc();
$pending_status = $row_status['status_count'];

// Pending student requests count (filtered by supervisor_id)
$request_query = "SELECT COUNT(*) as request_count 
                  FROM supervisors 
                  WHERE status = 'pending' 
                  AND supervisor_id = ?";
$request_stmt = $conn->prepare($request_query);
$request_stmt->bind_param("i", $lecturer_id);
$request_stmt->execute();
$request_result = $request_stmt->get_result();
$row_requests = $request_result->fetch_assoc();
$pending_requests = $row_requests['request_count'];
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

</head>
<style>
    <style>.overview-title {
        display: flex;
        align-items: center;
        font-size: 1.5rem;
        font-weight: bold;
        color: white;
        margin-bottom: 15px;
        padding-bottom: 10px;
    }

    .overview-title i {
        margin-right: 10px;
    }

    .overview-controls {
        display: flex;
        gap: 15px;
        justify-content: flex-start;
    }

    .approval-btn {
        background-color: #fff;
        border: 2px solid #007bff;
        color: #007bff;
        padding: 10px 15px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .approval-btn:hover {
        background-color: #007bff;
        color: white;
        box-shadow: 0 4px 8px rgba(0, 77, 77, 0.2);
    }

    .approval-btn .notification-badge {
        position: absolute;
        top: -10px;
        right: -10px;
        background-color: #dc3545;
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

    /* Archive Button Styles */
    .btn-archive {
        background-color: #d3d3d3;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s;
        margin-left: 5px;
    }

    .btn-archive:hover {
        background-color: #c0c0c0;
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

    .btn-archive i {
        margin-right: 5px;
    }

    /* Confirmation Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: white;
        margin: 15% auto;
        padding: 20px;
        border-radius: 5px;
        width: 300px;
        text-align: center;
    }

    .modal-buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
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
            <span><?php echo $lecturer_name; ?></span>
            <i class="fas fa-user"></i>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="lecturer_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="approval.php"><i class="fas fa-check-circle"></i> Approval</a>
        <a href="lecturer_archive.php"><i class="fas fa-archive"></i> Archive Proposals</a>
        <a href="../edit_profile.php"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Overview Section -->
        <div class="overview-section">
            <div class="overview-title">
                <i class="fas fa-clipboard-list"></i> PROPOSAL'S OVERVIEW
            </div>
            <div class="overview-controls">
                <button class="approval-btn relative"
                    onclick="window.location.href='view_qualitative_data_analysis.php'">
                    Other Submission
                </button>
                <button class="approval-btn relative" onclick="window.location.href='approval.php'">
                    Approval
                    <?php if ($pending_status > 0): ?>
                        <div class="notification-badge">
                            <?php echo $pending_status; ?>
                        </div>
                    <?php endif; ?>
                </button>
                <button class="approval-btn relative" onclick="window.location.href='approve_sv.php'">
                    Student Request
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
                                    <button type="button" onclick="confirmArchive(<?php echo $row['proposal_id']; ?>)"
                                        class="btn-archive">
                                        <i class="fas fa-archive"></i> Archive
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

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($total_pages > 1): ?>
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
                <?php endif; ?>
            </div>

            <!-- Archive Confirmation Modal -->
            <div id="archiveModal" class="modal">
                <div class="modal-content">
                    <h2>Confirm Archiving</h2>
                    <p>Are you sure you want to archive this proposal?
                        It will be hidden from your dashboard but the student can still access it.</p>
                    <div class="modal-buttons">
                        <button onclick="cancelArchive()" class="btn-cancel">Cancel</button>
                        <button onclick="proceedArchive()" class="btn-archive">Archive</button>
                    </div>
                </div>
            </div>

            <script>
                let proposalToArchive = null;

                function confirmArchive(proposalId) {
                    proposalToArchive = proposalId;
                    document.getElementById('archiveModal').style.display = 'block';
                }


                function cancelArchive() {
                    proposalToArchive = null;
                    document.getElementById('archiveModal').style.display = 'none';
                }

                function proceedArchive() {
                    if (proposalToArchive) {
                        window.location.href = `lecturer_dashboard.php?action=archive&proposal_id=${proposalToArchive}`;
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