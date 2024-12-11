<?php 
session_start(); 
require '../db_connection.php';  

// Fetch all topics with user names
$result = $conn->query("
    SELECT discussion_topics.*, users.fullname AS created_by_name
    FROM discussion_topics
    JOIN users ON discussion_topics.created_by = users.id
    ORDER BY discussion_topics.created_at DESC 
"); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Discussion Lounge</title>
    <link rel="stylesheet" href="student_discussion.css"> <!-- Link to your external CSS file -->
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
        <a href="../student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="../islamicsearch/bookmark/view_bookmarks.php"><i class="fas fa-bookmark"></i> Bookmark</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div id="main-content">
    <h1>Student Lounge</h1>
    
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
    </div>
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