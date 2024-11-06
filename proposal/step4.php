<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $questions = array_filter($_POST['questions'], function($q) {
        return !empty(trim($q));
    });

    // Validate minimum 2 research questions
    if (count($questions) < 2) {
        $errors['questions'] = "Please provide at least 2 research questions";
    }

    // Validate each question ends with a question mark
    foreach ($questions as $index => $question) {
        if (!preg_match('/\?$/', trim($question))) {
            $errors['question_' . $index] = "Question must end with a question mark";
        }
    }

    if (empty($errors)) {
        $_SESSION['proposal']['research_questions'] = $questions;
        header("Location: step5.php");
        exit();
    }
}

// Retrieve saved questions if they exist
$savedQuestions = $_SESSION['proposal']['research_questions'] ?? ['', ''];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Research Proposal Step 4</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="proposal_style.css">
</head>
<body>
    <div class="proposal-container">
        <div class="header">
            <h1>Research Questions</h1>
            <p>Define specific questions that support your central research question</p>
        </div>

        <div class="progress-bar">
            <?php for($i = 1; $i <= 8; $i++): ?>
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
            <p><?php echo htmlspecialchars($_SESSION['proposal']['research_question'] ?? 'No central research question defined'); ?></p>
        </div>

        <form action="step4.php" method="POST" id="researchQuestionsForm">
            <div class="questions-container">
                <?php foreach($savedQuestions as $index => $question): ?>
                    <div class="question-entry">
                        <div class="question-header">
                            <span class="question-number"><?php echo $index + 1; ?></span>
                            <label>Research Question</label>
                        </div>
                        <textarea 
                            class="question-input" 
                            name="questions[]" 
                            placeholder="Enter your research question..."
                            required><?php echo htmlspecialchars($question); ?></textarea>
                        <?php if($index > 1): ?>
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
                <button type="button" class="btn btn-secondary" onclick="window.location.href='step3.php'">
                    <i class="fas fa-arrow-left"></i> Previous Step
                </button>
                <button type="submit" class="btn btn-primary">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        function addQuestion() {
            const container = document.querySelector('.questions-container');
            const questionCount = container.children.length + 1;
            
            const questionEntry = document.createElement('div');
            questionEntry.className = 'question-entry';
            questionEntry.innerHTML = `
                <div class="question-header">
                    <span class="question-number">${questionCount}</span>
            <label>Research Question</label>
        </div>
        <textarea 
            class="question-input" 
            name="questions[]" 
            placeholder="Enter your research question..."
            required></textarea>
        <button type="button" class="remove-question" onclick="removeQuestion(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(questionEntry);
    updateQuestionNumbers();
}

function removeQuestion(button) {
    const entry = button.closest('.question-entry');
    if (document.querySelectorAll('.question-entry').length > 2) {
        entry.remove();
        updateQuestionNumbers();
    } else {
        showError("You must have at least 2 research questions.");
    }
}

function updateQuestionNumbers() {
    const questions = document.querySelectorAll('.question-entry');
    questions.forEach((question, index) => {
        const numberSpan = question.querySelector('.question-number');
        numberSpan.textContent = index + 1;
    });
}

function showError(message) {
    // Create error message element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        ${message}
    `;
    
    // Remove existing error message if any
    const existingError = document.querySelector('.temporary-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add temporary class for removal
    errorDiv.classList.add('temporary-error');
    
    // Insert error after the questions container
    const questionsContainer = document.querySelector('.questions-container');
    questionsContainer.parentNode.insertBefore(errorDiv, questionsContainer.nextSibling);
    
    // Remove error message after 3 seconds
    setTimeout(() => {
        errorDiv.remove();
    }, 3000);
}

// Form validation before submission
document.getElementById('researchQuestionsForm').addEventListener('submit', function(e) {
    const questions = document.querySelectorAll('.question-input');
    let isValid = true;
    
    // Remove any existing error messages
    document.querySelectorAll('.error-message').forEach(error => error.remove());
    
    // Validate each question
    questions.forEach((question, index) => {
        const value = question.value.trim();
        
        // Check if question ends with question mark
        if (!value.endsWith('?')) {
            isValid = false;
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                Question ${index + 1} must end with a question mark
            `;
            question.parentNode.appendChild(errorDiv);
        }
        
        // Check if question is empty
        if (value === '') {
            isValid = false;
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                Question ${index + 1} cannot be empty
            `;
            question.parentNode.appendChild(errorDiv);
        }
    });
    
    // Check minimum number of questions
    if (questions.length < 2) {
        isValid = false;
        showError("Please provide at least 2 research questions");
    }
    
    if (!isValid) {
        e.preventDefault();
    }
});
</script>