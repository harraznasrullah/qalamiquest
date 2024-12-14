<?php
session_start();
include('../db_connection.php'); // Replace with your DB connection file

// Fetch current user ID
$current_user_id = $_SESSION['user_id']; // Ensure the session holds the logged-in user ID

// Check progress for each section
$query = "SELECT * FROM progress WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$progress = $result->fetch_assoc();

// Default progress (if not available yet)
if (!$progress) {
    $progress = [
        'semi_structured_status' => 'pending',
        'data_analysis_status' => 'pending',
        'themes_status' => 'pending',
    ];
}

// Sections data
$sections = [
    [
        'key' => 'semi_structured',
        'title' => 'Semi-Structured Interview',
        'status' => $progress['semi_structured_interview']
    ],
    [
        'key' => 'data_analysis',
        'title' => 'Data Analysis',
        'status' => $progress['data_analysis']
    ],
    [
        'key' => 'themes',
        'title' => 'Generating Themes',
        'status' => $progress['generating_themes']
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
</head>

<body>
    <div class="container">
        <!-- Add back button -->
        <div class="page-header">
            <a href="../student_dashboard.php" class="btn-back">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
                Back to Dashboard
            </a>
            <h1>Qualitative Data Analysis</h1>
        </div>
        <p>Complete the sections in order. Each section must be submitted before unlocking the next.</p>

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
                enctype="multipart/form-data">
                <input type="hidden" name="section" value="<?php echo $section['key']; ?>">

                <label for="file-<?php echo $section['key']; ?>" class="attach-label">Attach</label>
                <input type="file" id="file-<?php echo $section['key']; ?>" name="file" class="file-input" hidden>

                <button type="submit" class="submit-btn" <?php if ($section['status'] !== 'pending' || ($index > 0 && $sections[$index - 1]['status'] !== 'submitted'))
                    echo 'disabled'; ?>>
                    Submit
                </button>
            </form>
        </div>
    </div>
</div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
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
    </script>
</body>

</html>