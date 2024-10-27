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
    $research_question = trim($_POST['research_question']);
    
    // Validate research question
    if (empty($research_question)) {
        $errors['research_question'] = "Please provide your central research question";
    } elseif (strlen($research_question) < 20) {
        $errors['research_question'] = "Your research question seems too short. Please provide more detail";
    } elseif (!preg_match('/\?$/', $research_question)) {
        $errors['research_question'] = "Your research question should end with a question mark";
    }

    if (empty($errors)) {
        $_SESSION['proposal']['research_question'] = $research_question;
        header("Location: step4.php");
        exit();
    }
}

// Retrieve saved research question if it exists
$saved_question = $_SESSION['proposal']['research_question'] ?? '';
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
        </div>

        <div class="progress-bar">
            <?php for($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 3 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="guidelines">
        <h3>Crafting Your Central Research Question</h3>
    <ul>
        <li>
            <i class="fas fa-bullseye"></i>
            <span>Turn your research purpose into one guiding question</span>
        </li>
        <li>
            <i class="fas fa-search"></i>
            <span>Ensure it’s broad enough to cover all objectives but stays focused</span>
        </li>
        <li>
            <i class="fas fa-link"></i>
            <span>Align it directly with your research objectives</span>
        </li>
        <li>
            <i class="fas fa-balance-scale"></i>
            <span>Balance it between being too general and too specific</span>
        </li>
        <li>
            <i class="fas fa-lightbulb"></i>
            <span>Use precise language that clarifies your study’s scope</span>
        </li>
    </ul>
        </div>

        <form action="step3.php" method="POST" id="researchQuestionForm">
            <div class="question-container">
                <div class="question-form-group">
                    <label for="research_question" class="question-label">Your Central Research Question:</label>
                    <textarea 
                        id="research_question" 
                        name="research_question" 
                        class="question-input" 
                        placeholder="Type your research question here..."
                        required
                    ><?php echo htmlspecialchars($saved_question); ?></textarea>
                    <div class="character-count">
                        <span id="charCount">0</span> characters
                    </div>
                </div>

                <?php if (isset($errors['research_question'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $errors['research_question']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="button-group">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='step2.php'">
                    <i class="fas fa-arrow-left"></i> Previous Step
                </button>
                <button type="submit" class="btn btn-primary" onclick="window.location.href='step4.php'">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        // Character counter
        const textarea = document.getElementById('research_question');
        const charCount = document.getElementById('charCount');

        function updateCharCount() {
            const count = textarea.value.length;
            charCount.textContent = count;
        }

        textarea.addEventListener('input', updateCharCount);
        
        // Initial character count
        updateCharCount();

        // Form validation
        document.getElementById('researchQuestionForm').addEventListener('submit', function(e) {
            const question = textarea.value.trim();
            
            if (question.length < 20) {
                e.preventDefault();
                alert('Your research question seems too short. Please provide more detail.');
                return;
            }
            
            if (!question.endsWith('?')) {
                e.preventDefault();
                alert('Your research question should end with a question mark.');
                return;
            }
        });
    </script>
</body>
</html>