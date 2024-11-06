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
        header("Location: step2.php");
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
    <link rel="stylesheet" href="proposal_style.css">
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
                <button type="submit" class="btn btn-primary"></button>
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