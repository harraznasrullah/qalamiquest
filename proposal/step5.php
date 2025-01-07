<?php
session_start();
include('db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Check if research questions exist
if (!isset($_SESSION['proposal']['research_questions'])) {
    header("Location: step4.php");
    exit();
}

// Retrieve interview questions from the database if not already in the session
if (!isset($_SESSION['proposal']['interview_questions']) && isset($_SESSION['proposal']['proposal_id'])) {
    $proposal_id = $_SESSION['proposal']['proposal_id'];
    $stmt = $conn->prepare("SELECT interview_questions FROM proposals WHERE proposal_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $proposal_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $proposal = $result->fetch_assoc();
        // Decode interview questions if they are saved in JSON format
        $_SESSION['proposal']['interview_questions'] = json_decode($proposal['interview_questions'], true);
    }
}

// Retrieve saved interview questions from the session or set defaults
$savedQuestions = $_SESSION['proposal']['interview_questions'] ?? [];

// Ensure there are exactly 2 interview questions for each research question
foreach ($_SESSION['proposal']['research_questions'] as $index => $research_question) {
    if (!isset($savedQuestions[$index]) || count($savedQuestions[$index]) < 2) {
        $savedQuestions[$index] = array_pad($savedQuestions[$index] ?? [], 2, '');
    } elseif (count($savedQuestions[$index]) > 2) {
        $savedQuestions[$index] = array_slice($savedQuestions[$index], 0, 2);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $interview_questions = [];

    // Check if "Next Step" button was clicked
    $isNextStep = isset($_POST['next_step']);

    // Process each research question's interview questions
    foreach ($_SESSION['proposal']['research_questions'] as $index => $research_question) {
        $questions = array_filter($_POST['interview_questions'][$index] ?? [], function ($q) {
            return !empty(trim($q));
        });

        // Validate minimum 2 interview questions per research question (only for "Next Step")
        if ($isNextStep && count($questions) < 2) {
            $errors["research_$index"] = "Please provide at least 2 interview questions for Research Question " . ($index + 1);
        }

        // Validate each question ends with a question mark
        foreach ($questions as $q_index => $question) {
            if (!preg_match('/\?$/', trim($question))) {
                $errors["question_{$index}_{$q_index}"] = "Question must end with a question mark";
            }
        }

        $interview_questions[$index] = $questions;
    }

    if (empty($errors)) {
        $_SESSION['proposal']['interview_questions'] = $interview_questions;
        $user_id = $_SESSION['user_id'];
        $proposal_id = $_SESSION['proposal']['proposal_id'] ?? null;
        $interviewQuestionsData = json_encode($interview_questions);

        // Save data to database when navigating or saving
        if ($proposal_id) {
            $stmt = $conn->prepare("UPDATE proposals SET interview_questions = ?, last_saved = NOW() WHERE proposal_id = ? AND user_id = ?");
            $stmt->bind_param("sii", $interviewQuestionsData, $proposal_id, $user_id);
        } else {
            // Insert if a new proposal is being created
            $stmt = $conn->prepare("INSERT INTO proposals (user_id, interview_questions, last_saved) VALUES (?, ?, NOW())");
            $stmt->bind_param("is", $user_id, $interviewQuestionsData);
        }

        if ($stmt->execute()) {
            if (!$proposal_id) {
                $_SESSION['proposal']['proposal_id'] = $stmt->insert_id;
            }

            // Handle navigation based on the clicked button
            if (isset($_POST['save_and_quit'])) {
                header("Location: ../student_dashboard.php");
                exit();
            } elseif (isset($_POST['previous_step'])) {
                header("Location: step4.php");
                exit();
            } else {
                header("Location: step6.php");
                exit();
            }
        } else {
            $errors['database'] = "Error saving data: " . $stmt->error;
        }
    }
}

$_SESSION['proposal']['step5_completed'] = true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Research Proposal Step 5</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <link rel="stylesheet" href="proposal_style.css">
</head>
<style>
    .interview-question {
        position: relative;
        /* Ensure the container is the reference for absolute positioning */
        margin-bottom: 1rem;
        /* Add spacing between questions */
        padding: 1rem;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #f9f9f9;
    }

    .remove-question {
        position: absolute;
        right: 0.5rem;
        /* Adjust as needed */
        top: 0.5rem;
        /* Adjust as needed */
        color: var(--error-color);
        /* Red color for the close button */
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
            <h1>Interview Questions</h1>
            <p>Create interview questions that align with your research questions</p>
        </div>

        <div class="progress-bar">
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 5 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="guidelines">
            <h3>Writing Effective Interview Questions</h3>
            <ul>
                <li>
                    <i class="fas fa-bullseye"></i>
                    <span>Each question should directly relate to your research questions</span>
                </li>
                <li>
                    <i class="fas fa-comments"></i>
                    <span>Use open-ended questions to encourage detailed responses</span>
                </li>
                <li>
                    <i class="fas fa-user-check"></i>
                    <span>Avoid leading questions that might bias responses</span>
                </li>
                <li>
                    <i class="fas fa-language"></i>
                    <span>Use clear, simple language appropriate for your participants</span>
                </li>
            </ul>
        </div>

        <form action="step5.php" method="POST" id="interviewQuestionsForm">
            <?php foreach ($_SESSION['proposal']['research_questions'] as $index => $research_question): ?>
                <div class="research-question-section">
                    <div class="research-question-header">
                        <span class="research-question-number"><?php echo $index + 1; ?></span>
                        <div class="research-question-text">
                            <?php echo htmlspecialchars($research_question); ?>
                        </div>
                    </div>

                    <div class="interview-questions-container" data-research-index="<?php echo $index; ?>">
                        <?php foreach ($savedQuestions[$index] as $q_index => $question): ?>
                            <div class="interview-question">
                                <div class="interview-question-header">
                                    <span class="question-number">Q<?php echo $q_index + 1; ?></span>
                                </div>
                                <textarea class="question-input" name="interview_questions[<?php echo $index; ?>][]"
                                    placeholder="Enter your interview question..."><?php echo htmlspecialchars($question); ?></textarea>
                                <!-- No close button for default questions -->
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="add-question-btn" onclick="addQuestion(<?php echo $index; ?>)">
                        <i class="fas fa-plus"></i> Add Interview Question
                    </button>
                </div>
            <?php endforeach; ?>

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
        // Add logic for dynamically adding/removing questions
        function addQuestion(researchIndex) {
            const container = document.querySelector(`.interview-questions-container[data-research-index="${researchIndex}"]`);
            const questionCount = container.children.length + 1;

            const newQuestion = document.createElement('div');
            newQuestion.classList.add('interview-question');
            newQuestion.innerHTML = `
            <div class="interview-question-header">
                <span class="question-number">Q${questionCount}</span>
            </div>
            <textarea 
                class="question-input" 
                name="interview_questions[${researchIndex}][]" 
                placeholder="Enter your interview question..."></textarea>
            <button type="button" class="remove-question" onclick="removeQuestion(this)">
                <i class="fas fa-times"></i>
            </button>
        `;

            container.appendChild(newQuestion);
        }

        function removeQuestion(button) {
            button.closest('.interview-question').remove();
        }

        // Validate form before submission for "Next Step" button only
        document.getElementById('interviewQuestionsForm').addEventListener('submit', function (event) {
            const nextStepButton = event.submitter && event.submitter.classList.contains('btn-primary'); // Check if "Next Step" button was clicked

            if (nextStepButton) {
                let isValid = true;

                // Check each research question's interview questions
                document.querySelectorAll('.interview-questions-container').forEach(container => {
                    const questions = container.querySelectorAll('.question-input');
                    let filledCount = 0;

                    questions.forEach(input => {
                        if (input.value.trim() !== '') {
                            filledCount++;
                        }
                    });

                    // Ensure at least 2 questions are filled per research question
                    if (filledCount < 2) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    event.preventDefault(); // Prevent form submission
                    alert('Please provide at least 2 interview questions for each Research Question.');
                }
            }
        });
    </script>
</body>

</html>