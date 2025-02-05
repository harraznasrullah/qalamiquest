<?php
session_start();

if (isset($_SESSION['proposal_submitted'])) {
    unset($_SESSION['proposal_submitted']); // Remove it if it exists
}

// Check if user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Check if preliminary review exists
if (!isset($_SESSION['proposal']['preliminary_review'])) {
    header("Location: step6.php");
    exit();
}

// Include database connection
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];

    // Check if "Next Step" button was clicked
    $isNextStep = isset($_POST['next_step']);

    // Get and validate form data
    $research_design = trim($_POST['research_design'] ?? '');
    $data_collection = trim($_POST['data_collection'] ?? '');
    $data_analysis = trim($_POST['data_analysis'] ?? '');
    $sampling_method = trim($_POST['sampling_method'] ?? '');
    $ethical_considerations = trim($_POST['ethical_considerations'] ?? '');

    // Validation rules (only for "Next Step")
    if ($isNextStep) {
        if (strlen($research_design) < 200) {
            $errors['research_design'] = "Research design description must be at least 200 characters";
        }
        if (strlen($data_collection) < 200) {
            $errors['data_collection'] = "Data collection methods must be at least 200 characters";
        }
        if (strlen($data_analysis) < 200) {
            $errors['data_analysis'] = "Data analysis approach must be at least 200 characters";
        }
        if (strlen($sampling_method) < 150) {
            $errors['sampling_method'] = "Sampling method must be at least 150 characters";
        }
        if (strlen($ethical_considerations) < 150) {
            $errors['ethical_considerations'] = "Ethical considerations must be at least 150 characters";
        }
    }

    // If validation passes, save data to session
    if (empty($errors)) {
        // Encode methodology data as JSON for saving in the database
        $methodologyData = json_encode([
            'research_design' => $research_design,
            'data_collection' => $data_collection,
            'data_analysis' => $data_analysis,
            'sampling_method' => $sampling_method,
            'ethical_considerations' => $ethical_considerations
        ]);

        // Save the methodology data in the session for use in other steps
        $_SESSION['proposal']['methodology'] = $methodologyData;

        // Check for "save_and_quit" or "previous_step"
        $user_id = $_SESSION['user_id'];
        $proposal_id = $_SESSION['proposal']['proposal_id'] ?? null;

        if ($proposal_id) {
            // Update existing proposal
            $stmt = $conn->prepare("UPDATE proposals SET methodologies = ?, status = 0, last_saved = NOW() WHERE proposal_id = ? AND user_id = ?");
            $stmt->bind_param("sii", $methodologyData, $proposal_id, $user_id);
        } else {
            // Insert a new proposal if no proposal_id exists
            $stmt = $conn->prepare("INSERT INTO proposals (user_id, methodologies, status, last_saved) VALUES (?, ?, 0, NOW())");
            $stmt->bind_param("is", $user_id, $methodologyData);
        }

        if ($stmt->execute()) {
            if (!$proposal_id) {
                $proposal_id = $stmt->insert_id;
                $_SESSION['proposal']['proposal_id'] = $proposal_id;
            }
            // Add proper redirection for all cases
            if (isset($_POST['save_and_quit'])) {
                header("Location: ../student_dashboard.php");
                exit();
            } else if (isset($_POST['previous_step'])) {
                header("Location: step6.php");
                exit();
            } else if (isset($_POST['next_step'])) {
                header("Location: step8.php");
                exit();
            }
        } else {
            $errors['database'] = "Error saving data: " . $stmt->error;
        }
    }
}

// Retrieve saved data from the database if it exists and hasn't been loaded into session
if (!isset($_SESSION['proposal']['methodology']) && isset($_SESSION['proposal']['proposal_id'])) {
    $proposal_id = $_SESSION['proposal']['proposal_id'];
    $stmt = $conn->prepare("SELECT methodologies FROM proposals WHERE proposal_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $proposal_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $proposal = $result->fetch_assoc();
        $_SESSION['proposal']['methodology'] = $proposal['methodologies'];
    }
}

// Decode methodologies JSON for pre-filling form if data exists in session
$saved_methodology = json_decode($_SESSION['proposal']['methodology'] ?? '{}', true);
$_SESSION['proposal']['step7_completed'] = true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Research Proposal Step 7</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="proposal_style.css">
    <style>
        .methodology-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .methodology-section textarea {
            width: 100%;
            min-height: 150px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            font-size: 1em;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="proposal-container">
        <div class="header">
            <h1>Research Methodology</h1>
            <p>Describe your research approach and techniques</p>
        </div>

        <div class="progress-bar">
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 7 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="guidelines">
            <h3>Writing an Effective Methodology</h3>
            <ul>
                <li>
                    <i class="fas fa-clipboard-check"></i>
                    <span>Be specific about your research design and methods</span>
                </li>
                <li>
                    <i class="fas fa-users"></i>
                    <span>Clearly describe your sampling strategy and participant selection</span>
                </li>
                <li>
                    <i class="fas fa-chart-line"></i>
                    <span>Explain how you will analyze your data</span>
                </li>
                <li>
                    <i class="fas fa-shield-alt"></i>
                    <span>Address ethical considerations and participant protection</span>
                </li>
            </ul>
        </div>

        <form action="step7.php" method="POST" id="methodologyForm">
            <div class="methodology-section">
                <h3><i class="fas fa-microscope"></i> Research Design</h3>
                <div class="help-text">Describe your overall research approach (e.g., qualitative, quantitative, mixed
                    methods) and justify your choice.</div>
                <textarea name="research_design"
                    placeholder="Explain your research design and why it's appropriate for your study..."><?php echo htmlspecialchars($saved_methodology['research_design'] ?? ''); ?></textarea>
                <div class="character-count">
                    <span class="current">0</span>/200 characters minimum
                </div>
                <?php if (isset($errors['research_design'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $errors['research_design']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="methodology-section">
                <h3><i class="fas fa-file-alt"></i> Data Collection Methods</h3>
                <div class="help-text">Describe how you will collect your data (e.g., interviews, surveys,
                    observations).</div>
                <textarea name="data_collection"
                    placeholder="Detail your data collection methods and tools..."><?php echo htmlspecialchars($saved_methodology['data_collection'] ?? ''); ?></textarea>
                <div class="character-count">
                    <span class="current">0</span>/200 characters minimum
                </div>
                <?php if (isset($errors['data_collection'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $errors['data_collection']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="methodology-section">
                <h3><i class="fas fa-chart-bar"></i> Data Analysis Approach</h3>
                <div class="help-text">Explain how you will analyze and interpret your data.</div>
                <textarea name="data_analysis"
                    placeholder="Describe your data analysis methods and techniques..."><?php echo htmlspecialchars($saved_methodology['data_analysis'] ?? ''); ?></textarea>
                <div class="character-count">
                    <span class="current">0</span>/200 characters minimum
                </div>
                <?php if (isset($errors['data_analysis'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $errors['data_analysis']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="methodology-section">
                <h3><i class="fas fa-users"></i> Sampling Method</h3>
                <div class="help-text">Describe your target population and how you will select participants.</div>
                <textarea name="sampling_method"
                    placeholder="Explain your sampling strategy and participant selection criteria..."><?php echo htmlspecialchars($saved_methodology['sampling_method'] ?? ''); ?></textarea>
                <div class="character-count">
                    <span class="current">0</span>/150 characters minimum
                </div>
                <?php if (isset($errors['sampling_method'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $errors['sampling_method']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="methodology-section">
                <h3><i class="fas fa-shield-alt"></i> Ethical Considerations</h3>
                <div class="help-text">Address how you will protect participants and handle ethical concerns.</div>
                <textarea name="ethical_considerations"
                    placeholder="Describe the ethical considerations and how you will address them..."><?php echo htmlspecialchars($saved_methodology['ethical_considerations'] ?? ''); ?></textarea>
                <div class="character-count">
                    <span class="current">0</span>/150 characters minimum
                </div>
                <?php if (isset($errors['ethical_considerations'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $errors['ethical_considerations']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-secondary" name="previous_step">
                    <i class="fas fa-arrow-left"></i> Previous Step
                </button>
                <button type="submit" class="btn btn-primary" name="next_step">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
                <button type="submit" name="save_and_quit" class="btn btn-secondary">
                    <i class="fas fa-save"></i> Save and Quit
                </button>
            </div>
        </form>
    </div>

    <script>
        // Update character count for textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            const counter = textarea.closest('.methodology-section').querySelector('.current');

            // Update initial count
            counter.textContent = textarea.value.length;

            // Update count on input
            textarea.addEventListener('input', function () {
                counter.textContent = this.value.length;
            });
        });

        // Form validation
        document.getElementById('methodologyForm').addEventListener('submit', function (e) {
            const nextStepButton = e.submitter && e.submitter.classList.contains('btn-primary'); // Check if "Next Step" button was clicked

            if (nextStepButton) {
                const sections = {
                    'research_design': 200,
                    'data_collection': 200,
                    'data_analysis': 200,
                    'sampling_method': 150,
                    'ethical_considerations': 150
                };

                let isValid = true;

                for (const [field, minLength] of Object.entries(sections)) {
                    const textarea = document.querySelector(`textarea[name="${field}"]`);
                    if (textarea.value.trim().length < minLength) {
                        isValid = false;
                        showError(`This section must be at least ${minLength} characters long`, textarea);
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill all sections with the required minimum characters.');
                }
            }
        });

        function showError(message, element) {
            // Remove any existing error messages
            const existingError = element.parentElement.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }

            // Create and show new error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            ${message}
        `;

            element.parentElement.appendChild(errorDiv);
        }
    </script>
</body>

</html>