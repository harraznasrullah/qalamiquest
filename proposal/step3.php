<?php
session_start();
include('db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_name']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if proposal data exists in session with the correct proposal_id
if (!isset($_SESSION['proposal']) || !isset($_SESSION['proposal']['proposal_id'])) {
    header("Location: step1.php");
    exit();
}

// Verify that the loaded proposal belongs to the current user
$user_id = $_SESSION['user_id'];
$proposal_id = $_SESSION['proposal']['proposal_id'];

$stmt = $conn->prepare("SELECT proposal_id FROM proposals WHERE proposal_id = ? AND user_id = ?");
$stmt->bind_param("ii", $proposal_id, $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if (!$result->fetch_assoc()) {
        // If no matching proposal found, clear session and redirect
        unset($_SESSION['proposal']);
        header("Location: step1.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $research_question = trim($_POST['research_question']);

    // Identify the button clicked
    $is_save_and_quit = isset($_POST['save_and_quit']);
    $is_previous_step = isset($_POST['previous_step']);
    $is_next_step = isset($_POST['next_step']);

    // Perform validation only if "Next Step" is clicked
    if ($is_next_step) {
        if (empty($research_question)) {
            $errors['research_question'] = "Please provide your research question";
        } elseif (strlen($research_question) < 20) {
            $errors['research_question'] = "Your research question seems too short. Please provide more detail";
        } elseif (!preg_match('/\?$/', $research_question)) {
            $errors['research_question'] = "Your research question should end with a question mark";
        }
    }

    if (empty($errors) || $is_save_and_quit || $is_previous_step) {
        // Get all data from previous steps
        $title = $_SESSION['proposal']['title'];
        $introduction = $_SESSION['proposal']['introduction'];
        $problem_statement = $_SESSION['proposal']['problem_statement'];
        $objectivesData = json_encode($_SESSION['proposal']['objectives'] ?? []);

        // Save research question to session with both keys for compatibility
        $_SESSION['proposal']['research_question'] = $research_question;
        $_SESSION['proposal']['central_research_question'] = $research_question;

        // Save to database
        $user_id = $_SESSION['user_id'];
        $proposal_id = $_SESSION['proposal']['proposal_id'] ?? null;

        if ($proposal_id) {
            $stmt = $conn->prepare("UPDATE proposals SET title = ?, introduction = ?, problem_statement = ?, objectives = ?, central_research_question = ?, status = 0, last_saved = NOW() WHERE proposal_id = ? AND user_id = ?");
            $stmt->bind_param("sssssii", $title, $introduction, $problem_statement, $objectivesData, $research_question, $proposal_id, $user_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO proposals (user_id, title, introduction, problem_statement, objectives, central_research_question, status, last_saved) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())");
            $stmt->bind_param("isssss", $user_id, $title, $introduction, $problem_statement, $objectivesData, $research_question);
        }

        if ($stmt->execute()) {
            if (!$proposal_id) {
                $proposal_id = $stmt->insert_id;
                $_SESSION['proposal']['proposal_id'] = $proposal_id;
            }

            // Redirect based on the button clicked
            if ($is_save_and_quit) {
                header("Location: ../student_dashboard.php");
                exit();
            } elseif ($is_next_step) {
                header("Location: step4.php");
                exit();
            } elseif ($is_previous_step) {
                header("Location: step2.php");
                exit();
            }
        } else {
            $errors['database'] = "Error saving data: " . $stmt->error;
        }
    }
}



// Retrieve saved research question if it exists - check both possible session keys
$saved_question = $_SESSION['proposal']['research_question'] ?? $_SESSION['proposal']['central_research_question'] ?? '';

// If no question in session but we have a proposal_id, try to load from database
if (empty($saved_question) && isset($_SESSION['proposal']['proposal_id'])) {
    $proposal_id = $_SESSION['proposal']['proposal_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT central_research_question FROM proposals WHERE proposal_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $proposal_id, $user_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $saved_question = $row['central_research_question'];
            // Update session with both keys
            $_SESSION['proposal']['research_question'] = $saved_question;
            $_SESSION['proposal']['central_research_question'] = $saved_question;
        }
    }
}
$_SESSION['proposal']['step3_completed'] = true;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Research Proposal Step 3</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="proposal_style.css">
</head>

<body>
    <div class="proposal-container">
        <div class="header">
            <h1>Central Research Question</h1>
            <p>Define the primary question that your research aims to answer</p>
            <?php if (isset($_SESSION['proposal']['title'])): ?>
                <p class="current-proposal">Current Proposal:
                    <?php echo htmlspecialchars($_SESSION['proposal']['title']); ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="progress-bar">
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 3 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="guidelines">
            <h3>Crafting Your Central Research Question</h3>
            <ul>
                <li><i class="fas fa-bullseye"></i> <span>Turn your research purpose into one guiding
                        question</span></li>
                <li><i class="fas fa-search"></i> <span>Ensure it's broad enough to cover all objectives but stays
                        focused</span>
                </li>
                <li><i class="fas fa-link"></i><span> Align it directly with your research objectives</span></li>
                <li><i class="fas fa-balance-scale"></i><span> Balance it between being too general and too
                        specific</span></li>
                <li><i class="fas fa-lightbulb"></i> <span>Use precise language that clarifies your study's scope</span>
                </li>
            </ul>
        </div>

        <form action="step3.php" method="POST" id="researchQuestionForm">
            <div class="question-container">
                <div class="question-form-group">
                    <label for="research_question" class="question-label">Your Central Research Question:</label>
                    <textarea id="research_question" name="research_question" class="question-input"
                        placeholder="Type your research question here..." 
                        maxlength="1000"><?php echo htmlspecialchars($saved_question); ?></textarea>
                    <div class="character-count">
                        <span id="charCount">0</span> characters
                    </div>
                </div>

                <?php if (isset($errors['research_question'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['research_question']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($errors['database'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['database']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-secondary" name="previous_step">
                    <i class="fas fa-arrow-left"></i> Previous Step
                </button>
                <button type="submit" class="btn btn-primary" name="next_step">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
                <button type="submit" class="btn btn-secondary" name="save_and_quit">
                    Save and Quit <i class="fas fa-save"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        // Character count for textarea
        const charCount = document.getElementById('charCount');
        const researchQuestionInput = document.getElementById('research_question');
        researchQuestionInput.addEventListener('input', () => {
            charCount.textContent = researchQuestionInput.value.length;
        });
    </script>
</body>

</html>