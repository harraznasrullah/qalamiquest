<?php
session_start();
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

// Handle form submission
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
        header("Location: step6.php");
        exit();
    }
}

// Retrieve saved interview questions if they exist
$savedQuestions = $_SESSION['proposal']['interview_questions'] ?? [];
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
                <button type="button" class="btn btn-secondary" onclick="window.location.href='step4.php'">
                    <i class="fas fa-arrow-left"></i> Previous Step
                </button>
                <button type="submit" class="btn btn-primary">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        function addInterviewQuestion(researchIndex) {
            const container = document.querySelector(`.interview-questions-container[data-research-index="${researchIndex}"]`);
            const questionCount = container.children.length + 1;
            
            const questionDiv = document.createElement('div');
            questionDiv.className = 'interview-question';
            questionDiv.innerHTML = `
                <div class="interview-question-header">
                    <span class="question-number">Q${questionCount}</span>
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
            
            container.appendChild(questionDiv);
            updateQuestionNumbers(container);
        }

        function removeInterviewQuestion(button) {
            const questionDiv = button.closest('.interview-question');
            const container = questionDiv.closest('.interview-questions-container');
            
            if (container.children.length > 2) {
                questionDiv.remove();
                updateQuestionNumbers(container);
            } else {
                showError("You must have at least 2 interview questions", container);
            }
        }

        function updateQuestionNumbers(container) {
    const questions = container.querySelectorAll('.interview-question');
    questions.forEach((question, index) => {
        const numberSpan = question.querySelector('.question-number');
        numberSpan.textContent = `Q${index + 1}`;
        
        // Update remove button visibility
        const removeButton = question.querySelector('.remove-question');
        if (removeButton) {
            removeButton.style.display = index < 2 ? 'none' : 'block';
        }
    });
}

function showError(message, container) {
    // Remove any existing error messages
    const existingError = container.querySelector('.temp-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Create and show new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message temp-error';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        ${message}
    `;
    
    // Insert error after the last question
    container.appendChild(errorDiv);
    
    // Remove error message after 3 seconds
    setTimeout(() => {
        errorDiv.remove();
    }, 3000);
}

// Form validation before submission
document.getElementById('interviewQuestionsForm').addEventListener('submit', function(e) {
    let isValid = true;
    const containers = document.querySelectorAll('.interview-questions-container');
    
    containers.forEach(container => {
        const questions = container.querySelectorAll('.question-input');
        const filledQuestions = Array.from(questions).filter(q => q.value.trim() !== '');
        
        // Check minimum question requirement
        if (filledQuestions.length < 2) {
            isValid = false;
            showError("Please provide at least 2 interview questions", container);
        }
        
        // Check if questions end with question mark
        filledQuestions.forEach(question => {
            if (!question.value.trim().endsWith('?')) {
                isValid = false;
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-circle"></i>
                    Question must end with a question mark
                `;
                question.parentElement.appendChild(errorDiv);
            }
        });
    });
    
    if (!isValid) {
        e.preventDefault();
    }
});

// Auto-resize textareas as content grows
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('question-input')) {
        e.target.style.height = 'auto';
        e.target.style.height = (e.target.scrollHeight) + 'px';
    }
});

// Initialize textarea heights on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.question-input').forEach(textarea => {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    });
});