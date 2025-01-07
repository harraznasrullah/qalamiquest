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
    header("Location: step1.php"); // Redirect to Step 1 if no proposal data exists
    exit();
}

// Handle form submission for saving research questions in the session or database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $questions = isset($_POST['questions']) ? array_filter($_POST['questions'], function ($q) {
        return !empty(trim($q)); // Ensure empty questions are not included
    }) : [];

    // Check if "Next Step" button was clicked
    $isNextStep = isset($_POST['next_step']);

    // Validate at least 2 questions are filled only for "Next Step"
    if ($isNextStep && count($questions) < 2) {
        $errors['questions'] = "You must fill at least 2 research questions to proceed to the next step.";
    }

    // Validate questions end with a question mark
    foreach ($questions as $index => $question) {
        if (!preg_match('/\?$/', trim($question))) {
            $errors['question_' . $index] = "Question must end with a question mark";
        }
    }

    // If no errors, proceed
    if (empty($errors)) {
        // Store research questions in session
        $_SESSION['proposal']['research_questions'] = $questions;

        // Other session data (same as before)
        $title = $_SESSION['proposal']['title'];
        $introduction = $_SESSION['proposal']['introduction'];
        $problem_statement = $_SESSION['proposal']['problem_statement'];
        $researchQuestionsData = json_encode($questions);  // Serialize research questions

        // Handle saving to the database
        $user_id = $_SESSION['user_id'];
        $proposal_id = $_SESSION['proposal']['proposal_id'] ?? null;

        if ($proposal_id) {
            // Update existing proposal
            $stmt = $conn->prepare("UPDATE proposals SET title = ?, introduction = ?, problem_statement = ?, research_questions = ?, status = 0, last_saved = NOW() WHERE proposal_id = ? AND user_id = ?");
            $stmt->bind_param("ssssii", $title, $introduction, $problem_statement, $researchQuestionsData, $proposal_id, $user_id);
        } else {
            // Insert a new proposal
            $stmt = $conn->prepare("INSERT INTO proposals (user_id, title, introduction, problem_statement, research_questions, status, last_saved) VALUES (?, ?, ?, ?, ?, 0, NOW())");
            $stmt->bind_param("issss", $user_id, $title, $introduction, $problem_statement, $researchQuestionsData);
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
                // Clear session data for this step to avoid lingering data
                unset($_SESSION['proposal']['research_questions']); // Clear outdated data for step4
                header("Location: step3.php");
                exit();
            } else {
                // Proceed to next step (Step 5)
                header("Location: step5.php");
                exit();
            }
        } else {
            $errors['database'] = "Error saving data: " . $stmt->error;
        }
    }
}

// Retrieve research questions from the database if they exist (for pre-filling)
if (!isset($_SESSION['proposal']['research_questions']) && isset($_SESSION['proposal']['proposal_id'])) {
    $proposal_id = $_SESSION['proposal']['proposal_id'];
    $stmt = $conn->prepare("SELECT research_questions FROM proposals WHERE proposal_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $proposal_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $proposal = $result->fetch_assoc();
        // Decode research questions if they are saved in JSON format
        $_SESSION['proposal']['research_questions'] = json_decode($proposal['research_questions'], true);
    }
}

// Set the saved research questions from session or default to exactly 2 empty textareas
$savedQuestions = $_SESSION['proposal']['research_questions'] ?? ['', ''];
// Ensure there are exactly 2 questions
if (count($savedQuestions) < 2) {
    $savedQuestions = array_pad($savedQuestions, 2, '');
} elseif (count($savedQuestions) > 2) {
    $savedQuestions = array_slice($savedQuestions, 0, 2);
}

$_SESSION['proposal']['step4_completed'] = true;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Research Proposal Step 4</title>
    <link rel="stylesheet" href="proposal_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
    .remove-question {
        position: absolute;
        right: 1rem;
        top: 1rem;
        color: var(--error-color);
        background: none;
        border: none;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.3s ease;
        padding: 0.5rem;
    }

    .remove-question:hover {
        opacity: 1;
    }
</style>

<body>
    <div class="proposal-container">
        <div class="header">
            <h1>Research Questions</h1>
            <p>Define specific questions that support your central research question</p>
        </div>

        <div class="progress-bar">
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 4 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="guidelines">
            <h3>Writing Effective Research Questions</h3>
            <ul>
                <li>
                    <i class="fas fa-sitemap"></i>
                    <span>Each question should address a specific aspect of your central research question</span>
                </li>
                <li>
                    <i class="fas fa-bullseye"></i>
                    <span>Be specific and focused on one aspect per question</span>
                </li>
                <li>
                    <i class="fas fa-tasks"></i>
                    <span>Should be answerable within your research scope and timeframe</span>
                </li>
                <li>
                    <i class="fas fa-check-double"></i>
                    <span>Must align with your research objectives</span>
                </li>
                <li>
                    <i class="fas fa-search"></i>
                    <span>Use clear, precise language and end with a question mark</span>
                </li>
            </ul>
        </div>

        <div class="current-crq">
            <h4><i class="fas fa-quote-left"></i> Your Central Research Question</h4>
            <p><?php echo htmlspecialchars($_SESSION['proposal']['research_question'] ?? 'No central research question defined'); ?>
            </p>
        </div>

        <form action="step4.php" method="POST" id="researchQuestionsForm">
            <div class="questions-container">
                <?php foreach ($savedQuestions as $index => $question): ?>
                    <div class="question-entry">
                        <div class="question-header">
                            <span class="question-number"><?php echo $index + 1; ?></span>
                            <label>Research Question</label>
                        </div>
                        <textarea class="question-input" name="questions[]"
                            placeholder="Enter your research question..."><?php echo htmlspecialchars($question); ?></textarea>

                        <?php if ($index > 1): ?>
                            <button type="button" class="remove-question" onclick="removeQuestion(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        <?php endif; ?>

                        <?php if (isset($errors['question_' . $index])): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo $errors['question_' . $index]; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="add-question-btn" onclick="addQuestion()">
                <i class="fas fa-plus"></i> Add Another Question
            </button>

            <?php if (isset($errors['questions'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $errors['questions']; ?>
                </div>
            <?php endif; ?>

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
        <script>
            // Add logic for dynamically adding/removing questions
            function addQuestion() {
                const container = document.querySelector('.questions-container');
                const newEntry = document.createElement('div');
                const questionCount = container.children.length + 1;
                newEntry.classList.add('question-entry');
                newEntry.innerHTML = `
            <div class="question-header">
                <span class="question-number">${questionCount}</span>
                <label>Research Question</label>
            </div>
            <textarea class="question-input" name="questions[]" placeholder="Enter your research question..."></textarea>
            <button type="button" class="remove-question" onclick="removeQuestion(this)">
                <i class="fas fa-times"></i>
            </button>
        `;
                container.appendChild(newEntry);
            }

            function removeQuestion(button) {
                const questionEntry = button.closest('.question-entry');
                questionEntry.remove();
            }

            // Validate form before submission for "Next Step" button only
            document.getElementById('researchQuestionsForm').addEventListener('submit', function (event) {
                const nextStepButton = event.submitter && event.submitter.classList.contains('btn-primary'); // Check if "Next Step" button was clicked

                if (nextStepButton) {
                    const questionInputs = document.querySelectorAll('.question-input');
                    let filledCount = 0;

                    questionInputs.forEach(input => {
                        if (input.value.trim() !== '') {
                            filledCount++;
                        }
                    });

                    if (filledCount < 2) {
                        event.preventDefault(); // Prevent form submission
                        alert('You must fill at least 2 research questions to proceed to the next step.');
                    }
                }
            });
        </script>