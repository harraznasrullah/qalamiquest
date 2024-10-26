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

        /* Base styles from previous page */
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

        /* Header and Progress Bar styles from previous page */
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

        /* New styles for research question section */
        .question-container {
            background: var(--light-gray);
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
        }

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

        .question-form-group {
            margin-bottom: 1.5rem;
        }

        .question-label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .question-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            transition: all 0.3s ease;
            min-height: 100px;
            resize: vertical;
        }

        .question-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(47, 156, 149, 0.1);
            outline: none;
        }

        .character-count {
            margin-top: 0.5rem;
            color: #666;
            font-size: 0.9rem;
            text-align: right;
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