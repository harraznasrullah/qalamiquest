<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Check if methodology exists
if (!isset($_SESSION['proposal']['methodology'])) {
    header("Location: step7.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    // Get references array
    $references = $_POST['references'] ?? [];
    
    // Filter out empty references
    $references = array_filter($references, function($ref) {
        return trim($ref) !== '';
    });
    
    // Validate at least one reference exists
    if (empty($references)) {
        $errors['references'] = "At least one reference is required";
    }
    
    // If validation passes, save and submit
    if (empty($errors)) {
        $_SESSION['proposal']['references'] = $references;
        
        // Here you would typically save the complete proposal to database
        // For now, we'll just set a success message
        $_SESSION['submission_success'] = true;
        
        // You could add database insertion here
        // Example: saveProposalToDatabase($_SESSION['proposal']);
        
        // Clear proposal from session after saving
        unset($_SESSION['proposal']);
        
        // Return success response for AJAX
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true]);
            exit;
        }
        
        // Redirect for non-AJAX
        header("Location: student_dashboard.php");
        exit();
    }
}

// Retrieve saved references if they exist
$saved_references = $_SESSION['proposal']['references'] ?? [''];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Research Proposal Step 8</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="proposal_style.css">
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

        .guidelines {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .guidelines h4 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .guidelines ul {
            list-style-type: none;
            padding: 0;
        }

        .guidelines li {
            margin-bottom: 12px;
            display: flex;
            align-items: start;
            gap: 12px;
        }

        .guidelines i {
            color: #007bff;
            margin-top: 3px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            text-align: center;
        }

        .modal h3 {
            color: #2c3e50;
            margin-bottom: 15px;
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
</head>
<body>
    <div class="proposal-container">
        <div class="header">
            <h1>References</h1>
            <p>List all sources cited in your research proposal</p>
        </div>

        <div class="progress-bar">
            <?php for($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 8 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="guidelines">
            <h4>Reference Guidelines</h4>
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

        <form id="referenceForm" action="step8.php" method="POST">
            <div class="references-container">
                <div class="reference-controls">
                    <h3>Reference List</h3>
                    <button type="button" class="add-reference-btn" onclick="addReference()">
                        <i class="fas fa-plus"></i> Add Reference
                    </button>
                </div>
                
                <div id="referencesList">
                    <?php foreach($saved_references as $index => $reference): ?>
                        <div class="reference-entry">
                            <textarea 
                                name="references[]" 
                                placeholder="Enter reference in your chosen citation format..."
                                required><?php echo htmlspecialchars($reference); ?></textarea>
                            <?php if($index > 0): ?>
                                <button type="button" class="remove-reference-btn" onclick="removeReference(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (isset($errors['references'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $errors['references']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="button-group">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='step7.php'">
                    <i class="fas fa-arrow-left"></i> Previous Step
                </button>
                <button type="submit" class="btn btn-primary">
                    Submit Proposal <i class="fas fa-check"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <h3>Success!</h3>
            <p>Your proposal has been submitted. Wait for the approval.</p>
            <button class="close-btn" onclick="window.location.href='student_dashboard.php'">Close</button>
        </div>
    </div>

    <script>
        function addReference() {
            const referencesList = document.getElementById('referencesList');
            const newReference = document.createElement('div');
            newReference.className = 'reference-entry';
            newReference.innerHTML = `
                <textarea name="references[]" placeholder="Enter reference in your chosen citation format..." required></textarea>
                <button type="button" class="remove-reference-btn" onclick="removeReference(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            referencesList.appendChild(newReference);
        }

        function removeReference(button) {
            button.closest('.reference-entry').remove();
        }

        // Form submission handling
        document.getElementById('referenceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get all non-empty references
            const references = Array.from(document.getElementsByName('references[]'))
                .map(textarea => textarea.value.trim())
                .filter(value => value !== '');

            if (references.length === 0) {
                alert('Please add at least one reference');
                return;
            }

            // Submit form via AJAX
            const formData = new FormData(this);
            
            fetch('step8.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('successModal').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the proposal');
            });
        });
    </script>
</body>
</html>