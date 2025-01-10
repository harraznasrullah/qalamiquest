<?php
session_start();
include('db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Check if proposal data exists in session
if (!isset($_SESSION['proposal'])) {
    header("Location: step1.php"); // Redirect to Step 1 if no proposal data exists
    exit();
}

// Handle form submission for saving objectives in the session or database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $objectives = array_filter($_POST['objectives'], function ($obj) {
        return !empty(trim($obj));
    });

    // Validate objectives only if "Next Step" or "Save and Quit" buttons are pressed
    if (isset($_POST['next_step']) && count($objectives) < 3) {
        $errors['objectives'] = "Please provide at least 3 objectives.";
    }

    if (empty($errors) || isset($_POST['previous_step'])) {
        // Store objectives in session
        $_SESSION['proposal']['objectives'] = $objectives;

        // Save data to the database
        $user_id = $_SESSION['user_id'];
        $proposal_id = $_SESSION['proposal']['proposal_id'] ?? null;
        $title = $_SESSION['proposal']['title'];
        $introduction = $_SESSION['proposal']['introduction'];
        $problem_statement = $_SESSION['proposal']['problem_statement'];
        $objectivesData = json_encode($objectives); // Serialize objectives

        if ($proposal_id) {
            // Update existing proposal
            $stmt = $conn->prepare("UPDATE proposals SET title = ?, introduction = ?, problem_statement = ?, objectives = ?, status = 0, last_saved = NOW() WHERE proposal_id = ? AND user_id = ?");
            $stmt->bind_param("ssssii", $title, $introduction, $problem_statement, $objectivesData, $proposal_id, $user_id);
        } else {
            // Insert new proposal
            $stmt = $conn->prepare("INSERT INTO proposals (user_id, title, introduction, problem_statement, objectives, status, last_saved) VALUES (?, ?, ?, ?, ?, 0, NOW())");
            $stmt->bind_param("issss", $user_id, $title, $introduction, $problem_statement, $objectivesData);
        }

        if ($stmt->execute()) {
            if (!$proposal_id) {
                $proposal_id = $stmt->insert_id;
                $_SESSION['proposal']['proposal_id'] = $proposal_id;
            }

            // Redirect based on button clicked
            if (isset($_POST['save_and_quit'])) {
                header("Location: ../student_dashboard.php");
                exit();
            } elseif (isset($_POST['next_step'])) {
                header("Location: step3.php");
                exit();
            } elseif (isset($_POST['previous_step'])) {
                header("Location: step1.php");
                exit();
            }
        } else {
            $errors['database'] = "Error saving data: " . $stmt->error;
        }
    }
}

// Retrieve proposal data from the database if it exists (for pre-filling)
if (!isset($_SESSION['proposal']['objectives']) && isset($_SESSION['proposal']['proposal_id'])) {
    $proposal_id = $_SESSION['proposal']['proposal_id'];
    $stmt = $conn->prepare("SELECT objectives FROM proposals WHERE proposal_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $proposal_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $proposal = $result->fetch_assoc();
        $_SESSION['proposal']['objectives'] = json_decode($proposal['objectives'], true);
    }
}

$savedObjectives = $_SESSION['proposal']['objectives'] ?? [];

// Ensure there are at least 3 objectives in the array for displaying input fields
for ($i = count($savedObjectives); $i < 3; $i++) {
    $savedObjectives[] = "";
}

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
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 2 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <form action="step2.php" method="POST" id="objectivesForm">
            <div class="objectives-container">
                <?php foreach ($savedObjectives as $index => $objective): ?>
                    <div class="objective-entry">
                        <div class="objective-header">
                            <span class="objective-number"><?php echo $index + 1; ?></span>
                            <label>Research Objective</label>
                        </div>
                        <input type="text" class="objective-input" name="objectives[]"
                            value="<?php echo htmlspecialchars($objective); ?>"
                            placeholder="Enter your research objective...">
                        <?php if ($index >= 3): ?>
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
                <button type="submit" class="btn btn-secondary" name="previous_step">
                    <i class="fas fa-arrow-left"></i> Previous Step
                </button>
                <button type="submit" class="btn btn-primary" name="next_step">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
                <button type="submit" class="btn btn-secondary" name="save_and_quit">
                    Save and Quit <i class="fas fa-save"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        // Function to add a new objective input field
        function addObjective() {
            const container = document.querySelector('.objectives-container');
            const newObjective = `
            <div class="objective-entry">
                <div class="objective-header">
                    <span class="objective-number">${container.children.length + 1}</span>
                    <label>Research Objective</label>
                </div>
                <input type="text" class="objective-input" name="objectives[]" placeholder="Enter your research objective...">
                <button type="button" class="remove-objective" onclick="removeObjective(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
            container.insertAdjacentHTML('beforeend', newObjective);
            updateObjectiveNumbers();
        }

        // Function to remove an objective input field
        function removeObjective(button) {
            const container = document.querySelector('.objectives-container');
            if (container.children.length > 3) {
                button.closest('.objective-entry').remove();
                updateObjectiveNumbers();
            }
        }

        // Function to update objective numbers
        function updateObjectiveNumbers() {
            const objectives = document.querySelectorAll('.objective-entry');
            objectives.forEach((obj, index) => {
                obj.querySelector('.objective-number').textContent = index + 1;
            });
        }

        // Handle form submission for "Save and Quit" or "Previous Step"
        document.getElementById('objectivesForm').addEventListener('submit', function (e) {
            const buttonClicked = document.activeElement.name; // Get the button that was clicked
            if (buttonClicked === 'save_and_quit' || buttonClicked === 'previous_step') {
                // Remove 'required' attribute from inputs
                const inputs = document.querySelectorAll('.objective-input');
                inputs.forEach(input => input.removeAttribute('required'));
            }
        });
    </script>
</body>

</html>