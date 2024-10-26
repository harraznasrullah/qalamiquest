<?php
session_start();
// Check if the user is logged in (add your authentication logic here)
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission for Step 1 & Step 2
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate inputs
    $errors = [];
    if (empty($_POST['title'])) {
        $errors['title'] = "Title is required";
    }
    if (empty($_POST['introduction'])) {
        $errors['introduction'] = "Introduction is required";
    }
    if (empty($_POST['problem_statement'])) {
        $errors['problem_statement'] = "Problem statement is required";
    }

    if (empty($errors)) {
        $_SESSION['proposal']['title'] = $_POST['title'];
        $_SESSION['proposal']['introduction'] = $_POST['introduction'];
        $_SESSION['proposal']['problem_statement'] = $_POST['problem_statement'];

        // Redirect to Step 3
        header("Location: step3.php");
        exit();
    }
}

// Retrieve saved data if exists
$savedTitle = $_SESSION['proposal']['title'] ?? '';
$savedIntro = $_SESSION['proposal']['introduction'] ?? '';
$savedProblem = $_SESSION['proposal']['problem_statement'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Research Proposal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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

        .step-label {
            font-size: 0.85rem;
            color: #666;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
        }

        .form-section h3 i {
            margin-right: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(47, 156, 149, 0.1);
            outline: none;
        }

        .char-counter {
            position: absolute;
            bottom: -20px;
            right: 0;
            font-size: 0.8rem;
            color: #666;
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

        .error-message {
            color: var(--error-color);
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        /* Ensure form elements don't overlap tooltip */
        .form-section {
            position: relative;
            z-index: 1;
        }

        .form-section h3 {
            display: flex;
            align-items: center;
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
            gap: 0.5rem; /* Space between elements */
        }
    </style>
</head>

<body>
    <div class="proposal-container">
        <div class="header">
            <h1>Research Proposal</h1>
            <p>Complete your proposal step by step</p>
        </div>

        <div class="progress-bar">
            <?php for($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 1 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <form action="step1.php" method="POST" id="proposalForm">
            <div class="form-section">
                <h3>
                    <i class="fas fa-heading"></i>
                    Title
                </h3>
                <div class="form-group">
                    <label for="title">Main topic of your study</label>
                    <div class="input-wrapper">
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($savedTitle); ?>" maxlength="200" required>
                        <div class="char-counter"><span id="titleCount">0</span>/200</div>
                    </div>
                    <?php if (isset($errors['title'])): ?>
                        <div class="error-message"><?php echo $errors['title']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-section">
                <h3>
                    <i class="fas fa-book-open"></i>
                    Introduction
                </h3>
                <div class="form-group">
                    <label for="introduction">Brief overview of your study</label>
                    <div class="input-wrapper">
                        <textarea id="introduction" name="introduction" rows="4" maxlength="1000" required><?php echo htmlspecialchars($savedIntro); ?></textarea>
                        <div class="char-counter"><span id="introCount">0</span>/1000</div>
                    </div>
                    <?php if (isset($errors['introduction'])): ?>
                        <div class="error-message"><?php echo $errors['introduction']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-section">
                <h3>
                    <i class="fas fa-exclamation-circle"></i>
                    Problem Statement
                </h3>
                <div class="form-group">
                    <label for="problem_statement">What issue or gap does your study address?</label>
                    <div class="input-wrapper">
                        <textarea id="problem_statement" name="problem_statement" rows="4" maxlength="1000" required><?php echo htmlspecialchars($savedProblem); ?></textarea>
                        <div class="char-counter"><span id="problemCount">0</span>/1000</div>
                    </div>
                    <?php if (isset($errors['problem_statement'])): ?>
                        <div class="error-message"><?php echo $errors['problem_statement']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='../student_dashboard.php'">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </button>
                <button type="submit" class="btn btn-primary" onclick="window.location.href='step2.php'">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        // Character counter functionality
        function updateCharCount(input, counter) {
            const count = input.value.length;
            counter.textContent = count;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const inputs = [
                { input: document.getElementById('title'), counter: document.getElementById('titleCount') },
                { input: document.getElementById('introduction'), counter: document.getElementById('introCount') },
                { input: document.getElementById('problem_statement'), counter: document.getElementById('problemCount') }
            ];

            inputs.forEach(({input, counter}) => {
                updateCharCount(input, counter);
                input.addEventListener('input', () => updateCharCount(input, counter));
            });

            // Form validation
            const form = document.getElementById('proposalForm');
            form.addEventListener('submit', function(e) {
                let isValid = true;
                inputs.forEach(({input}) => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('error');
                    } else {
                        input.classList.remove('error');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields');
                }
            });
        });
    </script>
</body>

</html>