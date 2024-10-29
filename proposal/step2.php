<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $objectives = array_filter($_POST['objectives'], function($obj) {
        return !empty(trim($obj));
    });

    // Validate minimum 3 objectives
    if (count($objectives) < 3) {
        $errors['objectives'] = "Please provide at least 3 objectives";
    }

    if (empty($errors)) {
        $_SESSION['proposal']['objectives'] = $objectives;
        header("Location: proposal_step3.php");
        exit();
    }
}

// Retrieve saved objectives if they exist
$savedObjectives = $_SESSION['proposal']['objectives'] ?? ['', '', ''];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Research Proposal Step 2</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="proposal_style.css">
</head>

<body>
    <div class="proposal-container">
        <div class="header">
            <h1>Research Objectives</h1>
            <p>Define the specific goals of your research</p>
        </div>

        <div class="progress-bar">
            <?php for($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 2 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <form action="step2.php" method="POST" id="objectivesForm">
            <div class="objectives-container">
                <?php foreach($savedObjectives as $index => $objective): ?>
                    <div class="objective-entry">
                        <div class="objective-header">
                            <span class="objective-number"><?php echo $index + 1; ?></span>
                            <label>Research Objective</label>
                        </div>
                        <input type="text" 
                               class="objective-input" 
                               name="objectives[]" 
                               value="<?php echo htmlspecialchars($objective); ?>"
                               placeholder="Enter your research objective..."
                               required>
                        <?php if($index > 2): ?>
                            <button type="button" class="remove-objective" onclick="removeObjective(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="add-objective-btn" onclick="addObjective()">
                <i class="fas fa-plus"></i> Add Another Objective
            </button>

            <?php if (isset($errors['objectives'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $errors['objectives']; ?>
                </div>
            <?php endif; ?>

            <div class="button-group">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='step1.php'">
                    <i class="fas fa-arrow-left"></i> Previous Step
                </button>
                <button type="submit" class="btn btn-primary" onclick="window.location.href='step3.php'">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        function addObjective() {
            const container = document.querySelector('.objectives-container');
            const objectiveCount = container.children.length + 1;
            
            const objectiveEntry = document.createElement('div');
            objectiveEntry.className = 'objective-entry';
            objectiveEntry.innerHTML = `
                <div class="objective-header">
                    <span class="objective-number">${objectiveCount}</span>
                    <label>Research Objective</label>
                </div>
                <input type="text" 
                       class="objective-input" 
                       name="objectives[]" 
                       placeholder="Enter your research objective..."
                       required>
                <button type="button" class="remove-objective" onclick="removeObjective(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(objectiveEntry);
            updateObjectiveNumbers();
        }

        function removeObjective(button) {
            const container = document.querySelector('.objectives-container');
            if (container.children.length > 3) {
                button.closest('.objective-entry').remove();
                updateObjectiveNumbers();
            }
        }

        function updateObjectiveNumbers() {
            const objectives = document.querySelectorAll('.objective-entry');
            objectives.forEach((obj, index) => {
                obj.querySelector('.objective-number').textContent = index + 1;
            });
        }

        // Form validation
        document.getElementById('objectivesForm').addEventListener('submit', function(e) {
            const objectives = document.querySelectorAll('.objective-input');
            const filledObjectives = Array.from(objectives).filter(obj => obj.value.trim() !== '');
            
            if (filledObjectives.length < 3) {
                e.preventDefault();
                alert('Please provide at least 3 research objectives');
            }
        });
    </script>
</body>
</html>