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
    header("Location: step1.php");
    exit();
}

// Handle form submission for saving references in the session or database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $references = array_filter($_POST['references'], function ($ref) {
        return !empty(trim($ref));
    });

    // Validate that references are added
    if (count($references) < 1) {
        $errors['references'] = "Please provide at least one reference.";
    }

    if (empty($errors)) {
        // Store references in session
        $_SESSION['proposal']['references'] = $references;

        // Get data from other steps
        $title = $_SESSION['proposal']['title'];
        $introduction = $_SESSION['proposal']['introduction'];
        $problem_statement = $_SESSION['proposal']['problem_statement'];
        $research_questions = json_encode($_SESSION['proposal']['research_questions']);
        $methodologies = $_SESSION['proposal']['methodology'];
        $referencesData = json_encode($references);
        $user_id = $_SESSION['user_id'];
        $proposal_id = $_SESSION['proposal']['proposal_id'] ?? null;

        // Preserve the current status if it exists in the session or database
        if (!isset($_SESSION['proposal']['status']) && $proposal_id) {
            $stmt = $conn->prepare("SELECT status FROM proposals WHERE proposal_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $proposal_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $_SESSION['proposal']['status'] = $row['status'];
            }
        }
        $status = $_SESSION['proposal']['status'] ?? 0;

        if (isset($_POST['submit'])) {
            $status = 1; // Mark as submitted
        } elseif (isset($_POST['save_and_quit'])) {
            // Retain current status without changing it
            $status = $_SESSION['proposal']['status'] ?? $status;
        }

        if ($proposal_id) {
            // Update existing proposal
            $stmt = $conn->prepare("UPDATE proposals SET title = ?, introduction = ?, problem_statement = ?, research_questions = ?, methodologies = ?, reference = ?, status = ?, last_saved = NOW() WHERE proposal_id = ? AND user_id = ?");
            $stmt->bind_param("ssssssiii", $title, $introduction, $problem_statement, $research_questions, $methodologies, $referencesData, $status, $proposal_id, $user_id);
        } else {
            // Insert a new proposal
            $stmt = $conn->prepare("INSERT INTO proposals (user_id, title, introduction, problem_statement, research_questions, methodologies, reference, status, last_saved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issssssi", $user_id, $title, $introduction, $problem_statement, $research_questions, $methodologies, $referencesData, $status);
        }

        if ($stmt->execute()) {
            if (!$proposal_id) {
                $proposal_id = $stmt->insert_id;
                $_SESSION['proposal']['proposal_id'] = $proposal_id;
            }

            if (isset($_POST['submit'])) {
                $_SESSION['proposal_submitted'] = true; // Set pop-up session
                echo "<script>
                alert('Your proposal has been submitted. Wait for the approval.');
                window.location.href = '../student_dashboard.php';
            </script>";
                exit();
            } elseif (isset($_POST['save_and_quit'])) {
                header("Location: ../student_dashboard.php");
                exit();
            } elseif (isset($_POST['previous_step'])) {
                header("Location: step7.php");
                exit();
            }
        } else {
            $errors['database'] = "Error saving data: " . $stmt->error;
        }
    }
}

// Retrieve references from the database if they exist
if (!isset($_SESSION['proposal']['references']) && isset($_SESSION['proposal']['proposal_id'])) {
    $proposal_id = $_SESSION['proposal']['proposal_id'];
    $stmt = $conn->prepare("SELECT reference FROM proposals WHERE proposal_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $proposal_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $proposal = $result->fetch_assoc();
        $_SESSION['proposal']['references'] = json_decode($proposal['reference'], true);
    }
}

// Set the saved references from session or default to empty
$savedReferences = $_SESSION['proposal']['references'] ?? [''];
$_SESSION['proposal']['step8_completed'] = true;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Research Proposal Step 8</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="proposal_style.css">

</head>
<style>
    .references-container {
        background-color: #f8f9fa;
        padding: 25px;
        border-radius: 8px;
        margin-bottom: 30px;
    }

    .reference-entry {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }

    .reference-entry textarea {
        flex-grow: 1;
        min-height: 100px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        resize: vertical;
        font-size: 1em;
        line-height: 1.5;
    }

    .reference-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .add-reference-btn {
        color: #007bff;
        background: none;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.9em;
    }

    .remove-reference-btn {
        color: #dc3545;
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
    }

    .close-btn {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 15px;
    }

    .close-btn:hover {
        background-color: #0056b3;
    }
</style>

<body>
    <div class="proposal-container">
        <div class="header">
            <h1>References</h1>
            <p>List the references that will support your research proposal.</p>
        </div>

        <div class="progress-bar">
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 8 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="guidelines">
            <h3>Reference Guidelines</h3>
            <ul>
                <li>
                    <i class="fas fa-info-circle"></i>
                    <span>Include all sources cited throughout your proposal</span>
                </li>
                <li>
                    <i class="fas fa-book"></i>
                    <span>Use consistent citation format (APA, MLA, etc.)</span>
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    <span>Ensure all citations in the text have corresponding references</span>
                </li>
            </ul>
        </div>

        <form action="step8.php" method="POST" id="referencesForm">
            <div class="references-container">
                <?php foreach ($savedReferences as $index => $reference): ?>
                    <div class="reference-entry">
                        <div class="reference-header">
                            <span class="reference-number"></span>
                        </div>
                        <textarea class="reference-input" name="references[]" placeholder="Enter your reference..."
                            required><?php echo htmlspecialchars($reference); ?></textarea>
                        <?php if ($index > 0): ?>
                            <button type="button" class="remove-reference" onclick="removeReference(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        <?php endif; ?>
                        <?php if (isset($errors['reference_' . $index])): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo $errors['reference_' . $index]; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="add-reference-btn" onclick="addReference()">
                <i class="fas fa-plus"></i> Add Another Reference
            </button>

            <?php if (isset($errors['references'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $errors['references']; ?>
                </div>
            <?php endif; ?>
            <div class="button-group">
                <button type="submit" name="previous_step" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Previous Step
                </button>
                <button type="submit" class="btn btn-primary" name="submit" onclick="return confirmSubmission()">
                    <?php
                    // Check if the proposal is submitted and needs re-submission
                    if (isset($_SESSION['proposal']['status']) && $_SESSION['proposal']['status'] == 3) {
                        echo "Re-submit <i class='fas fa-redo'></i>";
                    } else {
                        echo "Submit <i class='fas fa-check'></i>";
                    }
                    ?>
                </button>
                <button type="submit" name="save_and_quit" class="btn btn-secondary">
                    <i class="fas fa-save"></i> Save and Quit
                </button>
            </div>
        </form>
    </div>

    <script>
        function confirmSubmission() {
            return confirm("Are you sure you want to submit? Once submitted, you cannot edit or change the proposal.");
        }

        function addReference() {
            const container = document.querySelector('.references-container');
            const newReference = `
                <div class="reference-entry">
                    <div class="reference-header">
                        <span class="reference-number"></span>
                    </div>
                    <textarea class="reference-input" name="references[]" placeholder="Enter your reference..." required></textarea>
                    <button type="button" class="remove-reference-btn" onclick="removeReference(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', newReference);
        }

        function removeReference(button) {
            button.closest('.reference-entry').remove();
        }
    </script>
</body>

</html>