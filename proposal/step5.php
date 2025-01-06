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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $interview_questions = [];
    
    // Process each research question's interview questions
    foreach ($_SESSION['proposal']['research_questions'] as $index => $research_question) {
        $questions = array_filter($_POST['interview_questions'][$index] ?? [], function($q) {
            return !empty(trim($q));
        });

        // Validate minimum 2 interview questions per research question
        if (count($questions) < 2) {
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
            if (isset($_POST['next_step'])) {
                header("Location: step6.php");
                exit();
            } elseif (isset($_POST['previous_step'])) {
                header("Location: step4.php");
                exit();
            } elseif (isset($_POST['save_and_quit'])) {
                header("Location: ../student_dashboard.php");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="proposal_style.css">
</head>
<body>
    <div class="proposal-container">
        <div class="header">
            <h1>Interview Questions</h1>
            <p>Create interview questions that align with your research questions</p>
        </div>

        <div class="progress-bar">
            <?php for($i = 1; $i <= 8; $i++): ?>
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
            <?php foreach($_SESSION['proposal']['research_questions'] as $index => $research_question): ?>
                <div class="research-question-section">
                    <div class="research-question-header">
                        <span class="research-question-number"><?php echo $index + 1; ?></span>
                        <div class="research-question-text">
                            <?php echo htmlspecialchars($research_question); ?>
                        </div>
                    </div>

                    <div class="interview-questions-container" data-research-index="<?php echo $index; ?>">
                        <?php 
                        $savedInterviewQuestions = $savedQuestions[$index] ?? ['', ''];
                        foreach($savedInterviewQuestions as $q_index => $question):
                        ?>
                            <div class="interview-question">
                                <div class="interview-question-header">
                                    <span class="question-number">Q<?php echo $q_index + 1; ?></span>
                                    <?php if($q_index > 1): ?>
                                        <button type="button" class="remove-question" onclick="removeInterviewQuestion(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <textarea 
                                    class="question-input" 
                                    name="interview_questions[<?php echo $index; ?>][]" 
                                    placeholder="Enter your interview question..."
                                    required><?php echo htmlspecialchars($question); ?></textarea>
                                <?php if (isset($errors["question_{$index}_{$q_index}"])): ?>
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo $errors["question_{$index}_{$q_index}"]; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="add-question-btn" onclick="addInterviewQuestion(<?php echo $index; ?>)">
                        <i class="fas fa-plus"></i> Add Interview Question
                    </button>

                    <?php if (isset($errors["research_$index"])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors["research_$index"]; ?>
                        </div>
                    <?php endif; ?>
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
        // Add new interview question
        function addInterviewQuestion(researchIndex) {
            const container = document.querySelector(.interview-questions-container[data-research-index="${researchIndex}"]);
            const newQuestion = document.createElement('div');
            newQuestion.classList.add('interview-question');
            newQuestion.innerHTML = `
                <div class="interview-question-header">
                    <span class="question-number">Q3</span>
                    <button type="button" class="remove-question" onclick="removeInterviewQuestion(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <textarea 
                    class="question-input" 
                    name="interview_questions[${researchIndex}][]" 
                    placeholder="Enter your interview question..."
                    required></textarea>
            `;
            container.appendChild(newQuestion);
        }

        // Remove an interview question
        function removeInterviewQuestion(button) {
            button.closest('.interview-question').remove();
        }
    </script>
</body>
</html>