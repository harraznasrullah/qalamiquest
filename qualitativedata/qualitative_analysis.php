<?php
session_start();
require '../db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Function to check if the previous activity is submitted
function isPreviousSubmitted($activityId) {
    global $conn;

    // Validate session user_id
    if (!isset($_SESSION['user_id'])) {
        die("User ID is not set in the session.");
    }

    if ($activityId == 1) return true; // First activity does not need a previous submission

    // Prepare the query
    $previousActivityId = $activityId - 1;
    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT submission_file FROM qualitative_submissions WHERE activity_id = ? AND user_id = ?");
    $stmt->bind_param('ii', $previousActivityId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

// Handle file submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['submission'])) {
    $activityId = $_POST['activity_id'];

    // Validate that previous activity is submitted
    if (!isPreviousSubmitted($activityId)) {
        $_SESSION['error'] = "Please submit the previous activity first.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Handle file upload
    $uploadDir = 'uploads/qualitative_analysis/';
    $fileExtension = pathinfo($_FILES['submission']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['submission']['tmp_name'], $targetPath)) {
        $userId = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO qualitative_submissions (user_id, activity_id, submission_file, submitted_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('iis', $userId, $activityId, $fileName);
        $stmt->execute();
        $_SESSION['success'] = "File submitted successfully!";
    } else {
        $_SESSION['error'] = "Error uploading file.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle template download
if (isset($_GET['download'])) {
    $templates = [
        1 => 'templates/interview_template.docx',
        2 => 'templates/analysis_template.docx',
        3 => 'templates/themes_template.docx'
    ];

    $templateId = $_GET['download'];
    if (isset($templates[$templateId]) && file_exists($templates[$templateId])) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . basename($templates[$templateId]) . '"');
        readfile($templates[$templateId]);
        exit;
    }
}

// Get user's submissions
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT activity_id, submission_file FROM qualitative_submissions WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$submissions = [];
while ($row = $result->fetch_assoc()) {
    $submissions[$row['activity_id']] = $row['submission_file'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Qualitative Data Analysis</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-left">
            <button class="open-btn" onclick="toggleSidebar()">☰</button>
            QalamiQuest
        </div>
        <div class="navbar-right">
            <i class="fas fa-bell bell-icon"></i>
            <span><?php echo strtoupper($_SESSION['user_name']); ?></span>
            <i class="fas fa-user"></i>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="../student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="../assign_sv.php"><i class="fas fa-users"></i> Apply Supervisor</a>
        <a href="#"><i class="fas fa-bookmark"></i> Bookmark</a>
        <a href="../edit_profile.php"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div id="main-content">
        <div class="content-header">
            <h1><i class="fas fa-chart-line"></i> Qualitative Data Analysis</h1>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="qualitative-grid">
            <?php
            $activities = [
                1 => 'Semi Structured Interview',
                2 => 'Data Analysis',
                3 => 'Generating Themes'
            ];

            foreach ($activities as $id => $name):
                $isEnabled = isPreviousSubmitted($id);
                $hasSubmission = isset($submissions[$id]);
            ?>
                <div class="activity-box <?php echo !$isEnabled ? 'disabled' : ''; ?>">
                    <div class="activity-header">
                        <h3><?php echo $id . ". " . $name; ?></h3>
                    </div>
                    
                    <div class="activity-actions">
                        <a href="?download=<?php echo $id; ?>" class="btn btn-download">
                            <i class="fas fa-download"></i> Download Template
                        </a>

                        <?php if (!$isEnabled): ?>
                            <div class="warning-message">
                                <i class="fas fa-exclamation-triangle"></i>
                                Submit Activity <?php echo ($id - 1); ?> first
                            </div>
                        <?php else: ?>
                            <form action="" method="POST" enctype="multipart/form-data" class="upload-form">
                                <input type="hidden" name="activity_id" value="<?php echo $id; ?>">
                                <input type="file" name="submission" id="file-<?php echo $id; ?>" class="hidden" 
                                       onchange="this.form.submit()">
                                <button type="button" 
                                        onclick="document.getElementById('file-<?php echo $id; ?>').click()"
                                        class="btn btn-upload">
                                    <i class="fas fa-upload"></i> Select File
                                </button>
                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-submit">
                                    <i class="fas fa-check-circle"></i> Submit File
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($hasSubmission): ?>
                            <div class="submission-status">
                                <i class="fas fa-check-circle"></i> Submitted
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
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
