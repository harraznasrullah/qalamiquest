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

        /* Previous base styles remain the same */
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

        /* Header and Progress Bar styles remain the same */
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

        /* Progress bar styles remain the same */
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

        /* New styles for objectives section */
        .objectives-container {
            margin-top: 2rem;
        }

        .objective-entry {
            position: relative;
            margin-bottom: 1.5rem;
            padding: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            background: #fff;
            transition: all 0.3s ease;
        }

        .objective-entry:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .objective-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .objective-number {
            background: var(--primary-color);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            margin-right: 0.8rem;
        }

        .remove-objective {
            position: absolute;
            right: 1rem;
            top: 1rem;
            color: var(--error-color);
            background: none;
            border: none;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .remove-objective:hover {
            opacity: 1;
        }

        .objective-input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .objective-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(47, 156, 149, 0.1);
            outline: none;
        }

        .add-objective-btn {
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
        }

        .add-objective-btn:hover {
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