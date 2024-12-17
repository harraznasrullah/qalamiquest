<?php
session_start();
require_once('db_connection.php');

// Check if logged-in user is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['title']) || $_SESSION['title'] !== 'student') {
    header("Location: login.php");
    exit();
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supervisor_id = $_POST['supervisor'];
    $student_id = $_SESSION['user_id'];

    // Check if student already has a supervisor
    $check_query = "SELECT id FROM supervisors WHERE student_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "You already have a supervisor assigned.";
        $messageType = 'error';
    } else {
        // Insert new supervisor assignment
        $insert_query = "INSERT INTO supervisors (student_id, supervisor_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $student_id, $supervisor_id);

        if ($stmt->execute()) {
            $message = "Supervisor assigned successfully!";
            $messageType = 'success';
        } else {
            $message = "Failed to assign supervisor. Please try again.";
            $messageType = 'error';
        }
    }
}

// Fetch available supervisors
$supervisors_query = "SELECT id, fullname FROM users WHERE title = 'lecturer' ORDER BY fullname";
$supervisors_result = $conn->query($supervisors_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your external CSS file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Assign Supervisor</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap');

        /* Global styles for body */
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: linear-gradient(to bottom, #ffffff, #EDFFFF);
            /* Background gradient */
        }

        .container {
            max-width: 100%;
            margin: 15px 300px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            color: #555;
        }

        select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            font-size: 16px;
        }

        .button-group {
            display: flex;
            /* Use flexbox for alignment */
            justify-content: space-between;
            /* Space buttons evenly */
            gap: 10px;
            /* Add a gap between buttons */
        }

        button {
            flex: 1;
            /* Each button takes equal width */
            background-color: #007bff;
            /* Default button color */
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
        }

        .submit:hover {
            background-color: #0056b3;
        }

        .back-button {
            background-color: #6c757d;
        }

        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
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

    <div class="sidebar" id="sidebar">
        <a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="assign_sv.php"><i class="fas fa-users"></i> Apply Supervisor</a>
        <a href="islamicsearch/bookmark/view_bookmarks.php"><i class="fas fa-bookmark"></i> Bookmark</a>
        <a href="edit_profile.php"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="container">
        <h1>Assign Supervisor</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div>
                <label for="supervisor">Select Supervisor:</label>
                <select name="supervisor" id="supervisor" required>
                    <option value="">-- Select Supervisor --</option>
                    <?php while ($row = $supervisors_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['fullname']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="button-group">
            <button type="button" class="back-button" onclick="history.back()">Cancel</button>


                <button type="submit" class="submit">Submit Request</button>
            </div>

        </form>
    </div>
</body>
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

</html>