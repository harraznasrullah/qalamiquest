<?php
session_start();
include('db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Check if proposal data exists in session
if (!isset($_SESSION['proposal'])) {
    header("Location: step1.php");
    exit();
}

// If there's a proposal_id in the session, load the data from database
if (isset($_SESSION['proposal']['proposal_id']) && !isset($_SESSION['proposal']['preliminary_review'])) {
    $proposal_id = $_SESSION['proposal']['proposal_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT preliminary_review FROM proposals WHERE proposal_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $proposal_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Decode the JSON string from database
        $_SESSION['proposal']['preliminary_review'] = json_decode($row['preliminary_review'], true);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];

    // Check if "Next Step" button was clicked
    $isNextStep = isset($_POST['next_step']);

    // Validate preliminary review (only for "Next Step")
    $preliminary_review = trim($_POST['preliminary_review'] ?? '');

    if ($isNextStep && strlen($preliminary_review) < 450) {
        $errors['preliminary_review'] = "Preliminary review must be at least 450 characters long.";
    }

    if (empty($errors)) {
        // Store preliminary review in session
        $_SESSION['proposal']['preliminary_review'] = $preliminary_review;

        // Handle saving to the database
        $user_id = $_SESSION['user_id'];
        $proposal_id = $_SESSION['proposal']['proposal_id'] ?? null;
        $preliminaryReviewData = json_encode($preliminary_review);

        if ($proposal_id) {
            // Update existing proposal
            $stmt = $conn->prepare("UPDATE proposals SET preliminary_review = ?, last_saved = NOW() WHERE proposal_id = ? AND user_id = ?");
            $stmt->bind_param("sii", $preliminaryReviewData, $proposal_id, $user_id);
        } else {
            // Insert new proposal
            $stmt = $conn->prepare("INSERT INTO proposals (user_id, preliminary_review, last_saved) VALUES (?, ?, NOW())");
            $stmt->bind_param("is", $user_id, $preliminaryReviewData);
        }

        if ($stmt->execute()) {
            if (!$proposal_id) {
                $proposal_id = $stmt->insert_id;
                $_SESSION['proposal']['proposal_id'] = $proposal_id;
            }

            // Check which button was clicked and redirect accordingly
            if (isset($_POST['save_and_quit'])) {
                header("Location: ../student_dashboard.php");
                exit();
            } elseif (isset($_POST['previous_step'])) {
                // Go to Previous Step (Step 5)
                header("Location: step5.php");
                exit();
            } else {
                // Proceed to next step (Step 7)
                header("Location: step7.php");
                exit();
            }
        } else {
            $errors['database'] = "Error saving data: " . $stmt->error;
        }
    }
}

// Retrieve saved data if it exists
$saved_review = '';
if (isset($_SESSION['proposal']['preliminary_review'])) {
    $saved_review = $_SESSION['proposal']['preliminary_review'];
}
$_SESSION['proposal']['step6_completed'] = true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Research Proposal Step 6</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="proposal_style.css">
    <style>
        .literature-section textarea {
            width: 100%;
            min-height: 300px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            font-size: 1em;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="proposal-container">
        <div class="header">
            <h1>Preliminary Review</h1>
            <p>Analyze existing research and identify gaps in the literature</p>
        </div>

        <div class="progress-bar">
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 6 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="guidelines">
            <h3>Writing an Effective Preliminary Review</h3>
            <ul>
                <li>
                    <i class="fas fa-search"></i>
                    <span>Summarize the main findings from existing research in your field</span>
                </li>
                <li>
                    <i class="fas fa-history"></i>
                    <span>Include both historical context and current developments</span>
                </li>
                <li>
                    <i class="fas fa-puzzle-piece"></i>
                    <span>Identify and explain gaps in existing research that your study will address</span>
                </li>
                <li>
                    <i class="fas fa-lightbulb"></i>
                    <span>Explain how your research will contribute to filling these gaps</span>
                </li>
            </ul>
        </div>

        <form action="step6.php" method="POST" id="preliminaryReviewForm">
            <div class="literature-section">
                <h3>Preliminary Review</h3>
                <textarea name="preliminary_review"
                    placeholder="Provide a comprehensive review of existing research related to your topic. What have other researchers found? What are the key theories and findings in your field? What aspects haven't been fully explored? How will your research address these gaps?"
                    ><?php echo htmlspecialchars($saved_review); ?></textarea>
                <div class="character-count">
                    <span class="current">0</span>/450 characters minimum
                </div>
                <?php if (isset($errors['preliminary_review'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $errors['preliminary_review']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="button-group">
                <button type="submit" name="previous_step" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Previous Step
                </button>
                <button type="submit" name="next_step" class="btn btn-primary">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
                <button type="submit" name="save_and_quit" class="btn btn-secondary">
                    <i class="fas fa-save"></i> Save and Quit
                </button>
            </div>
        </form>
    </div>

    <script>
        // Update character count for textarea
        const textarea = document.querySelector('textarea');
        const counter = document.querySelector('.current');

        // Update initial count
        counter.textContent = textarea.value.length;

        // Update count on input
        textarea.addEventListener('input', function () {
            counter.textContent = this.value.length;
        });

        // Form validation
        document.getElementById('preliminaryReviewForm').addEventListener('submit', function (e) {
            const nextStepButton = e.submitter && e.submitter.classList.contains('btn-primary'); // Check if "Next Step" button was clicked

            if (nextStepButton) {
                const preliminaryReview = document.querySelector('textarea[name="preliminary_review"]');
                let isValid = true;

                if (preliminaryReview.value.trim().length < 450) {
                    isValid = false;
                    alert('Preliminary review must be at least 450 characters long.');
                }

                if (!isValid) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>

</html>