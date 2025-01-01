<?php 
session_start(); 
require '../db_connection.php';  

// Pagination settings
$topicsPerPage = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure the page is at least 1
$offset = ($page - 1) * $topicsPerPage;

// Search feature
$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $searchCondition = "WHERE discussion_topics.title LIKE '%$searchQuery%'";
} else {
    $searchCondition = "";
}

// Fetch total number of topics
$totalTopicsResult = $conn->query("SELECT COUNT(*) AS total FROM discussion_topics $searchCondition");
$totalTopics = $totalTopicsResult->fetch_assoc()['total'];
$totalPages = ceil($totalTopics / $topicsPerPage);

// Fetch topics with pagination and optional search
$result = $conn->query("
    SELECT discussion_topics.*, users.fullname AS created_by_name
    FROM discussion_topics
    JOIN users ON discussion_topics.created_by = users.id
    $searchCondition
    ORDER BY discussion_topics.created_at DESC 
    LIMIT $topicsPerPage OFFSET $offset
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Discussion Lounge</title>
    <link rel="stylesheet" href="student_discussion.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<style>
    /* Search Form Styling */
.search-form input[type="text"] {
    padding: 10px;
    width: 300px;
    border: 2px solid #4a4a4a;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.search-form input[type="text"]:focus {
    outline: none;
    border-color: #007bff;
}

.search-form button {
    padding: 10px 15px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.search-form button:hover {
    background-color: #0056b3;
}

/* Pagination Styling */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.pagination a {
    margin: 0 5px;
    padding: 8px 12px;
    text-decoration: none;
    border: 1px solid #ddd;
    color: #007bff;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.pagination a.page-number {
    background-color: white;
}

.pagination a.page-number.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.pagination a:hover {
    background-color: #f0f0f0;
}

.pagination .prev-btn,
.pagination .next-btn {
    background-color: #f8f9fa;
}

.create-topic-btn {
    margin-top: 15px ;
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
            <span><?php echo strtoupper($_SESSION['user_name']); ?></span>
            <i class="fas fa-user"></i>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="../student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div id="main-content">
        <h1>Student Hub</h1>

        <!-- Search Form -->
        <form method="GET" action="student_discussion.php" class="search-form">
            <input type="text" name="search" placeholder="Search topics..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>

        <a href="create_topic.php" class="create-topic-btn">Create New Topic</a>
        
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="topic-container">
                <div class="topic-title">
                    <a href="topic.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a>
                </div>
                <p class="topic-description"><?= htmlspecialchars(substr($row['description'], 0, 100)) ?>...</p>
                <small class="topic-meta">
                    Created by: <?= htmlspecialchars($row['created_by_name']) ?> 
                    on <?= date('F j, Y', strtotime($row['created_at'])) ?>
                </small>
            </div>
        <?php endwhile; ?>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($searchQuery) ?>" class="prev-btn">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($searchQuery) ?>" 
                   class="page-number <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($searchQuery) ?>" class="next-btn">Next</a>
            <?php endif; ?>
        </div>
    </div>

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
    </script>
</body>
</html>
