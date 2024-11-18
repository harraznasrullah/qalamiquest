<?php
session_start();
require_once('../db_connection.php');

// Initialize variables
$errors = [];
$savedTitle = '';
$savedIntro = '';
$savedProblem = '';
$proposalId = '';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get proposal ID from URL or session
if (isset($_GET['proposal_id'])) {
    $proposalId = $_GET['proposal_id'];
} elseif (isset($_SESSION['proposal']['proposal_id'])) {
    $proposalId = $_SESSION['proposal']['proposal_id'];
    // Redirect to include proposal_id in URL for consistency
    header("Location: step1.php?proposal_id=" . $proposalId);
    exit();
} else {
    // If no proposal ID is found, redirect to dashboard
    $_SESSION['error_message'] = "Please create a new proposal from the dashboard.";
    header('Location: ../student_dashboard.php');
    exit();
}

// Verify this proposal belongs to the logged-in user
$stmt = $conn->prepare("SELECT title, introduction, problem_statement, status FROM proposals WHERE proposal_id = ? AND user_id = ?");
$stmt->bind_param("ii", $proposalId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $savedTitle = $row['title'];
    $savedIntro = $row['introduction'];
    $savedProblem = $row['problem_statement'];

    // Store proposal data in session
    $_SESSION['proposal'] = [
        'proposal_id' => $proposalId,
        'title' => $savedTitle,
        'introduction' => $savedIntro,
        'problem_statement' => $savedProblem,
    ];

    // Check if proposal is already submitted or approved
   // Allow editing only for proposals with status 0 (draft) or 3 (resubmission required)
if ($row['status'] > 0 && $row['status'] != 3) {
    $_SESSION['error_message'] = "Cannot edit proposal that has been submitted or approved.";
    header('Location: ../student_dashboard.php');
    exit();
}

// Set editability flag
$isEditable = ($row['status'] == 0 || $row['status'] == 3);

} else {
    // If proposal doesn't exist or doesn't belong to user
    $_SESSION['error_message'] = "Invalid proposal access.";
    header('Location: ../student_dashboard.php');
    exit();
}
$stmt->close();

// Form submission handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $title = trim($_POST['title']);
    $introduction = trim($_POST['introduction']);
    $problem_statement = trim($_POST['problem_statement']);

    // Validate input
    if (empty($title)) {
        $errors['title'] = "Title is required";
    } elseif (strlen($title) > 200) {
        $errors['title'] = "Title must not exceed 200 characters";
    }

    if (empty($introduction)) {
        $errors['introduction'] = "Introduction is required";
    } elseif (strlen($introduction) > 1000) {
        $errors['introduction'] = "Introduction must not exceed 1000 characters";
    }

    if (empty($problem_statement)) {
        $errors['problem_statement'] = "Problem statement is required";
    } elseif (strlen($problem_statement) > 1000) {
        $errors['problem_statement'] = "Problem statement must not exceed 1000 characters";
    }

    // If no errors, proceed with saving
    if (empty($errors)) {
        // Update session data
        $_SESSION['proposal'] = [
            'proposal_id' => $proposalId,
            'title' => $title,
            'introduction' => $introduction,
            'problem_statement' => $problem_statement
        ];

        $stmt = $conn->prepare("
    UPDATE proposals SET 
        title = ?, 
        introduction = ?, 
        problem_statement = ?, 
        last_saved = NOW()
    WHERE proposal_id = ? 
        AND user_id = ? 
        AND (status = 0 OR status = 3)
");


        $stmt->bind_param(
            "sssii",
            $title,
            $introduction,
            $problem_statement,
            $proposalId,
            $_SESSION['user_id']
        );

        if ($stmt->execute()) {
            // If save_and_quit button was clicked
            if (isset($_POST['save_and_quit'])) {
                header('Location: ../student_dashboard.php');
                exit();
            }

            // If normal submit (next step)
            header('Location: step2.php?proposal_id=' . $proposalId);
            exit();
        } else {
            $errors['database'] = "Error saving data: " . $conn->error;
        }
        $stmt->close();
    }

    // If there are errors, the form will be re-displayed with error messages
    $savedTitle = $title;
    $savedIntro = $introduction;
    $savedProblem = $problem_statement;

}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Research Proposal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="proposal_style.css">
</head>

<body>
    <div class="proposal-container">
        <div class="header">
            <h1>Research Proposal</h1>
            <p>Complete your proposal step by step</p>
        </div>
        <?php if (!$isEditable): ?>
    <p class="note">This proposal cannot be edited because it has been submitted or approved.</p>
<?php endif; ?>

        <!-- Progress Bar -->
        <div class="progress-bar">
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 1 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Form to fill out -->
        <form action="step1.php?proposal_id=<?php echo htmlspecialchars($proposalId); ?>" method="POST"
            id="proposalForm">
            <!-- Title Section -->
            <div class="form-section">
                <h3><i class="fas fa-heading"></i> Title</h3>
                <div class="form-group">
                    <label for="title">Main topic of your study</label>
                    <div class="input-wrapper">
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($savedTitle); ?>" 
       maxlength="200" <?php echo !$isEditable ? 'readonly' : ''; ?> required>

                        <div class="char-counter"><span id="titleCount">0</span>/200</div>
                    </div>
                    <?php if (isset($errors['title'])): ?>
                        <div class="error-message"><?php echo $errors['title']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Introduction Section -->
            <div class="form-section">
                <h3><i class="fas fa-book-open"></i> Introduction</h3>
                <div class="form-group">
                    <label for="introduction">Brief overview of your study</label>
                    <div class="input-wrapper">
                    <textarea id="introduction" name="introduction" rows="4" maxlength="1000" 
                    <?php echo !$isEditable ? 'readonly' : ''; ?> required><?php echo htmlspecialchars($savedIntro); ?></textarea>
                        <div class="char-counter"><span id="introCount">0</span>/1000</div>
                    </div>
                    <?php if (isset($errors['introduction'])): ?>
                        <div class="error-message"><?php echo $errors['introduction']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Problem Statement Section -->
            <div class="form-section">
                <h3><i class="fas fa-exclamation-circle"></i> Problem Statement</h3>
                <div class="form-group">
                    <label for="problem_statement">What issue or gap does your study address?</label>
                    <div class="input-wrapper">
                    <textarea id="problem_statement" name="problem_statement" rows="4" maxlength="1000" 
                    <?php echo !$isEditable ? 'readonly' : ''; ?> required><?php echo htmlspecialchars($savedProblem); ?></textarea>
                        <div class="char-counter"><span id="problemCount">0</span>/1000</div>
                    </div>
                    <?php if (isset($errors['problem_statement'])): ?>
                        <div class="error-message"><?php echo $errors['problem_statement']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Buttons -->
            <div class="button-group">
                <button type="button" class="btn btn-secondary"
                    onclick="window.location.href='../student_dashboard.php'">
                    <i class="fas fa-arrow-left"></i> Back To Dashboard
                </button>
                <button type="submit" class="btn btn-primary">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
                <button type="submit" name="save_and_quit" class="btn btn-secondary">
                    <i class="fas fa-save"></i> Save and Quit
                </button>
            </div>
        </form>
    </div>

    <script>
        // Set initial character counts
        window.onload = function() {
            document.getElementById('titleCount').textContent = document.getElementById('title').value.length;
            document.getElementById('introCount').textContent = document.getElementById('introduction').value.length;
            document.getElementById('problemCount').textContent = document.getElementById('problem_statement').value.length;
        };

        // Character counter for each input field
        document.getElementById('title').addEventListener('input', function () {
            document.getElementById('titleCount').textContent = this.value.length;
        });

        document.getElementById('introduction').addEventListener('input', function () {
            document.getElementById('introCount').textContent = this.value.length;
        });

        document.getElementById('problem_statement').addEventListener('input', function () {
            document.getElementById('problemCount').textContent = this.value.length;
        });
    </script>
</body>

</html>