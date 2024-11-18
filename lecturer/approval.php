<?php
session_start();
include('../db_connection.php');

// Check if lecturer is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$lecturer_name = strtoupper($_SESSION['user_name']);

// Fetch proposals with filtering and pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '1';

// Update the WHERE clause to use numeric status values
$where_clause = "WHERE proposals.status = ?";
if ($search) {
    $where_clause .= " AND (proposals.title LIKE ? OR users.fullname LIKE ?)";
}

$sql = "SELECT proposals.proposal_id, proposals.title, users.fullname AS student_name, 
        proposals.last_saved, users.email
        FROM proposals 
        JOIN users ON proposals.user_id = users.id 
        $where_clause 
        ORDER BY proposals.last_saved DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

if ($search) {
    $search_param = "%$search%";
    $stmt->bind_param("sssii", $status_filter, $search_param, $search_param, $limit, $offset);
} else {
    $stmt->bind_param("sii", $status_filter, $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as count FROM proposals JOIN users ON proposals.user_id = users.id $where_clause";
$count_stmt = $conn->prepare($count_sql);
if ($search) {
    $count_stmt->bind_param("sss", $status_filter, $search_param, $search_param);
} else {
    $count_stmt->bind_param("s", $status_filter);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['count'];
$total_pages = ceil($total_records / $limit);

// Handle actions with numeric status values
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $proposal_id = $_POST['proposal_id'];
    $feedback = isset($_POST['feedback']) ? $_POST['feedback'] : '';

    // Set numeric status values
    if ($action === 'approve') {
        $status = 2;  // Approved
    } elseif ($action === 'require_progress') {
        $status = 3;  // Progress Required
    } else {
        $status = null;
    }

    if ($status) {
        $stmt = $conn->prepare("UPDATE proposals SET status = ?, feedback = ? WHERE proposal_id = ?");
        $stmt->bind_param("isi", $status, $feedback, $proposal_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Proposal status updated successfully!";
            header("Location: approval.php");
            exit();
        } else {
            $error = "Error updating proposal status.";
        }
    }
}

// Update the status filter dropdown in HTML
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Approval - QalamiQuest</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="lecturer_style.css"> <!-- Link to your external CSS file -->
</head>

<body>
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

    <div class="sidebar" id="sidebar">
        <a href="lecturer_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="approval.php" class="active"><i class="fas fa-check-circle"></i> Approval</a>
        <a href="../edit_profile.php"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content" id="main-content">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h1>Proposal Submissions</h1>

            <div class="filters">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search proposals..."
                        value="<?php echo htmlspecialchars($search); ?>" onkeyup="handleSearch(this.value)">
                </div>
                <select class="btn" onchange="handleStatusFilter(this.value)">
    <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Pending</option>
    <option value="2" <?php echo $status_filter === '2' ? 'selected' : ''; ?>>Approved</option>
    <option value="3" <?php echo $status_filter === '3' ? 'selected' : ''; ?>>Progress Required</option>
</select>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="proposal-card">
                        <div class="proposal-header">
                            <div>
                                <h3 class="proposal-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                                <div class="proposal-meta">
                                    <p>
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars(ucwords(strtolower($row['student_name']))); ?>
                                        <i class="fas fa-envelope" style="margin-left: 20px;"></i>
                                        <?php echo htmlspecialchars($row['email']); ?>

                                    </p>
                                    <p>
                                        <i class="fas fa-clock"></i> Submitted:
                                        <?php echo date('d M Y, h:i A', strtotime($row['last_saved'])); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="action-buttons">
                                <button class="btn btn-view" onclick="viewProposal(<?php echo $row['proposal_id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn btn-approve"
                                    onclick="showFeedbackModal(<?php echo $row['proposal_id']; ?>, 'approve')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn btn-require"
                                    onclick="showFeedbackModal(<?php echo $row['proposal_id']; ?>, 'require_progress')">
                                    <i class="fas fa-exclamation-circle"></i> Require Progress
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>

                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"
                            class="page-link <?php echo $page === $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php else: ?>
                <div class="proposal-card">
                    <p>No proposals available for review.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content">
            <h2>Provide Feedback</h2>
            <form id="feedbackForm" method="POST">
                <input type="hidden" name="proposal_id" id="modal_proposal_id">
                <input type="hidden" name="action" id="modal_action">
                <textarea name="feedback" placeholder="Enter your feedback for the student..." required></textarea>
                <div class="action-buttons">
                    <button type="submit" class="btn btn-approve">Submit Feedback</button>
                    <button type="button" class="btn btn-require" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Sidebar Toggle
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

        // Search Functionality
        let searchTimeout;
        function handleSearch(value) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('search', value);
                currentUrl.searchParams.set('page', '1');
                window.location.href = currentUrl.toString();
            }, 500);
        }

        // Status Filter
        function handleStatusFilter(value) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('status', value);
            currentUrl.searchParams.set('page', '1');
            window.location.href = currentUrl.toString();
        }

        // Modal Functions
        function showFeedbackModal(proposalId, action) {
            const modal = document.getElementById('feedbackModal');
            const modalProposalId = document.getElementById('modal_proposal_id');
            const modalAction = document.getElementById('modal_action');

            modalProposalId.value = proposalId;
            modalAction.value = action;

            // Update modal title and button text based on action
            const modalTitle = modal.querySelector('h2');
            const submitButton = modal.querySelector('button[type="submit"]');

            if (action === 'approve') {
                modalTitle.textContent = 'Approve Proposal';
                submitButton.className = 'btn btn-approve';
                submitButton.textContent = 'Approve';
            } else {
                modalTitle.textContent = 'Request Progress Update';
                submitButton.className = 'btn btn-require';
                submitButton.textContent = 'Request Update';
            }

            modal.style.display = 'block';
            modal.querySelector('textarea').focus();
        }

        function closeModal() {
            const modal = document.getElementById('feedbackModal');
            modal.style.display = 'none';
            modal.querySelector('form').reset();
        }

        // View Proposal Function
        function viewProposal(proposalId) {
            // Redirect to proposal detail page
            window.location.href = `view_proposal.php?id=${proposalId}`;
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            const modal = document.getElementById('feedbackModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Form Validation
        document.getElementById('feedbackForm').onsubmit = function (e) {
            const feedback = this.querySelector('textarea').value.trim();
            if (feedback.length < 10) {
                e.preventDefault();
                alert('Please provide more detailed feedback (minimum 10 characters).');
                return false;
            }
            return true;
        }

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Add loading indicator for actions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function () {
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitButton.disabled = true;

                // Re-enable button after 5 seconds in case of error
                setTimeout(() => {
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                }, 5000);
            });
        });
    </script>
</body>

</html>