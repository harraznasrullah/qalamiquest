<?php
session_start();
include('../db_connection.php'); // Replace with your DB connection file

// Fetch current user ID
$current_user_id = $_SESSION['user_id']; // Ensure the session holds the logged-in user ID

// Check progress for each section
$query = "SELECT * FROM submission WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$progress = [];
while ($row = $result->fetch_assoc()) {
    $progress[$row['section']] = 'submitted'; // Mark section as 'submitted' if it exists
}

// Default progress (if not available yet)
$sections = [
    [
        'key' => 'semi_structured',
        'title' => 'Semi-Structured Interview',
        'status' => isset($progress['semi_structured']) ? $progress['semi_structured'] : 'pending'
    ],
    [
        'key' => 'data_analysis',
        'title' => 'Data Analysis',
        'status' => isset($progress['data_analysis']) ? $progress['data_analysis'] : 'pending'
    ],
    [
        'key' => 'themes',
        'title' => 'Generating Themes',
        'status' => isset($progress['themes']) ? $progress['themes'] : 'pending'
    ]
];

if (isset($_GET['upload'])) {
    $uploadStatus = $_GET['upload'];
    $messageClass = '';
    $message = '';

    if ($uploadStatus === 'success') {
        $messageClass = 'upload-status upload-status-success';
        $message = 'File uploaded successfully.';
    } elseif ($uploadStatus === 'error') {
        $messageClass = 'upload-status upload-status-error';
        $message = 'Failed to upload the file. Please try again.';
    } elseif ($uploadStatus === 'invalid') {
        $messageClass = 'upload-status upload-status-error';
        $message = 'Invalid file upload. Please attach a valid file.';
    }

    echo "<div class='{$messageClass}'>{$message}</div>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qualitative Data Analysis</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
    <link rel="stylesheet" href="../styles.css"> <!-- Link to your CSS file -->
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
            <span><?php echo strtoupper($_SESSION['user_name']); ?></span> <!-- Display logged in user's name -->
            <i class="fas fa-user"></i> <!-- Profile icon -->
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="../student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="../assign_sv.php"><i class="fas fa-users"></i> Apply Supervisor</a>
        <a href="../islamicsearch/bookmark/view_bookmarks.php"><i class="fas fa-bookmark"></i> Bookmark</a>
        <a href="../edit_profile.php"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div id="main-content">
        <div class="container">
            <!-- Add back button -->
            <div class="page-header">
                <a href="../student_dashboard.php" class="btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                </a>
                <h1>Qualitative Data Analysis</h1>
            </div>

            <div class="sections">
                <?php foreach ($sections as $index => $section): ?>
                    <div class="section-card">
                        <h3><?php echo $section['title']; ?></h3>

                        <div class="btn-actions">
                            <div class="btn-actions-left">
                                <a href="download_template.php?section=<?php echo $section['key']; ?>" class="btn-primary">
                                    Download Template
                                </a>
                            </div>

                            <div class="btn-actions-right">
                                <form id="uploadForm-<?php echo $section['key']; ?>" method="POST" action="upload.php"
                                    enctype="multipart/form-data" data-section="<?php echo $section['key']; ?>">
                                    <input type="hidden" name="section" value="<?php echo $section['key']; ?>">

                                    <!-- Hidden file input -->
                                    <label for="file-<?php echo $section['key']; ?>" class="attach-label">Attach</label>
                                    <input type="file" id="file-<?php echo $section['key']; ?>" name="file"
                                        class="file-input" hidden>

                                    <!-- Display file name -->
                                    <span id="file-name-<?php echo $section['key']; ?>" class="file-name-display">No file
                                        chosen</span>

                                    <button type="button" class="submit-btn"
                                        onclick="validateFile('<?php echo $section['key']; ?>')">
                                        Submit
                                    </button>
                                </form>

                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
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

        document.addEventListener('DOMContentLoaded', function () {
            // Select all file inputs
            const fileInputs = document.querySelectorAll('.file-input');

            fileInputs.forEach(input => {
                input.addEventListener('change', function () {
                    const fileNameDisplay = document.getElementById(`file-name-${this.id.split('-')[1]}`);

                    // Display file name or "No file chosen" if none is selected
                    if (this.files.length > 0) {
                        fileNameDisplay.textContent = this.files[0].name;
                    } else {
                        fileNameDisplay.textContent = 'No file chosen';
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const fileInputs = document.querySelectorAll('.file-input');
            const submitButtons = document.querySelectorAll('.submit-btn');

            fileInputs.forEach(input => {
                input.addEventListener('change', function () {
                    const sectionKey = this.id.split('-')[1];
                    const submitButton = document.querySelector(`#uploadForm-${sectionKey} .submit-btn`);

                    if (this.files.length > 0) {
                        submitButton.disabled = false;
                    } else {
                        submitButton.disabled = true;
                    }
                });
            });
        });
        function validateFile(sectionKey) {
            // Get the file input and its value
            const fileInput = document.querySelector(`#file-${sectionKey}`);
            const file = fileInput.files[0];

            if (!file) {
                // Display an alert if no file is selected
                alert("No file attached. Please attach a file before submitting.");
                return;
            }

            // If a file is selected, submit the form
            const form = document.querySelector(`#uploadForm-${sectionKey}`);
            form.submit();
        }

    </script>
</body>

</html>