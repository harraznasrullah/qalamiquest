<?php
session_start();
include(__DIR__ . '/../db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Retrieve the logged-in user's ID from the session
$user_name = strtoupper($_SESSION['user_name']); // Retrieve the logged-in user's name from the session
$bookmarks = [];

$query = "SELECT surah, ayat, text, english_translation FROM bookmarks WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $bookmarks[] = $row;
}



$stmt->close();
$conn->close();

// Remove any HTML output before this
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quran Bookmark</title>
    <link rel="stylesheet" href="../islamicsearch/islamicsearchstyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .ayat {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
            text-align: center;
        }
        .arabic-text {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .translation {
            font-size: 16px;
        }
        h1 {
            text-align: center;
        }
        p {
            text-align: center;
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
            <i class="fas fa-bell bell-icon"></i> <!-- Bell icon -->
            <span><?php echo $user_name; ?></span> <!-- Display logged-in user's name -->
            <i class="fas fa-user"></i> <!-- Profile icon -->
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="/../student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#"><i class="fas fa-users"></i> Lecturer/Supervisor</a>
        <a href="bookmarks.php"><i class="fas fa-bookmark"></i> Quran Bookmark</a>
        <a href="edit_profile.php"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a> <!-- Updated Logout link -->
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

    <div class="container">
        <h1>Quran Bookmark</h1>
        
        <?php if (!empty($bookmarks)): ?>
            <?php foreach ($bookmarks as $bookmark): ?>
                <div class="ayat">
                    <strong>Surah <?php echo $bookmark['surah']; ?>, Ayat <?php echo $bookmark['ayat']; ?></strong>
                    <div class="arabic-text"><?php echo $bookmark['text']; ?></div>
                    <div class="translation"><?php echo $bookmark['english_translation']; ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No bookmarks found.</p>
        <?php endif; ?>
    </div>
</body>
</html>