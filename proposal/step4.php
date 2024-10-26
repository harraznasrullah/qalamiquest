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
        header("Location: proposal_step5.php");
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
    <style>
        /* Reuse previous CSS variables and base styles */
        :root {
            --primary-color: #2f9c95;
            --primary-dark: #267a72;
            --secondary-color: #e0f7f5;
            --error-color: #dc3545;
            --success-color: #28a745;
            --text-color: #333;
            --light-gray: #f4f7f6;
            --border-radius: 8px;
        }

        /* Base styles remain the same */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--light-gray) 0%, #ffffff 100%);
            color: var(--text-color);
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .proposal-container {
            width: 90%;
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        /* Previous styles for header and progress bar */
        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            position: relative;
            padding: 0 10px;
        }

        .progress-bar::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--secondary-color);
            transform: translateY(-50%);
            z-index: 1;
        }

        .step {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .step.active .step-circle {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }

        /* Guidelines section */
        .guidelines {
            background: #fff;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-color);
        }

        .guidelines h3 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .guidelines ul {
            list-style-type: none;
            padding-left: 0;
            margin-bottom: 1.5rem;
        }

        .guidelines li {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            line-height: 1.4;
            padding-right: 1rem;
        }

        .guidelines li i {
            color: var(--primary-color);
            margin-top: 0.2rem;
            width: 24px;
            font-size: 1.1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.8rem;
        }
        .guidelines li span {
    flex: 1;               /* Allow text to take remaining space */
    padding-top: 0.1rem;   /* Slight adjustment to align with icon */
    padding-left: 10px;
}

        /* Current research question display */
        .current-crq {
            background: var(--secondary-color);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
        }

        .current-crq h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .current-crq p {
            font-style: italic;
            line-height: 1.6;
            color: var(--text-color);
        }

        /* Question entries section */
        .questions-container {
            margin-top: 2rem;
        }

        .question-entry {
            position: relative;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            background: #fff;
            transition: all 0.3s ease;
        }

        .question-entry:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .question-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .question-number {
            background: var(--primary-color);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            margin-right: 1rem;
        }

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

        .question-input {
            width: 100%;
            padding: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.3s ease;
            resize: vertical;
            min-height: 60px;
        }

        .question-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(47, 156, 149, 0.1);
            outline: none;
        }

        .add-question-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 1rem;
            background: var(--secondary-color);
            color: var(--primary-color);
            border: 2px dashed var(--primary-color);
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .add-question-btn:hover {
            background: var(--primary-color);
            color: white;
            border-style: solid;
        }

        .error-message {
            color: var(--error-color);
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }

        .btn {
            padding: 0.8rem 2rem;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary {
            background: #fff;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-primary {
            background: var(--primary-color);
            color: #fff;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }
    </style>
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
                <button type="submit" class="btn btn-primary" onclick="window.location.href='step5.php'">
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