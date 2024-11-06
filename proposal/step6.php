<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Check if interview questions exist
if (!isset($_SESSION['proposal']['interview_questions'])) {
    header("Location: step5.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    // Validate literature review summary
    $literature_summary = trim($_POST['literature_summary'] ?? '');
    $research_gaps = trim($_POST['research_gaps'] ?? '');
    
    // Validation rules
    if (strlen($literature_summary) < 300) {
        $errors['literature_summary'] = "Literature summary must be at least 300 characters long";
    }
    
    if (strlen($research_gaps) < 150) {
        $errors['research_gaps'] = "Research gaps description must be at least 150 characters long";
    }
    
    // If validation passes, save and proceed
    if (empty($errors)) {
        $_SESSION['proposal']['preliminary_review'] = [
            'literature_summary' => $literature_summary,
            'research_gaps' => $research_gaps
        ];
        
        header("Location: step7.php");
        exit();
    }
}

// Retrieve saved data if it exists
$saved_review = $_SESSION['proposal']['preliminary_review'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Research Proposal Step 6</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="proposal_style.css">
    <style>
        .literature-section {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .literature-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2em;
        }

        .literature-section textarea {
            width: 100%;
            min-height: 200px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            font-size: 1em;
            line-height: 1.5;
        }

        .character-count {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 8px;
            text-align: right;
        }

        .guidelines {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
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

        .error-message {
            background-color: #fff3f3;
            color: #dc3545;
            padding: 10px;
            border-radius: 4px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="proposal-container">
        <div class="header">
            <h1>Preliminary Review</h1>
            <p>Summarize existing research related to your study</p>
        </div>

        <div class="progress-bar">
            <?php for($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 6 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="guidelines">
            <h4>Writing an Effective Literature Review</h4>
            <ul>
                <li>
                    <i class="fas fa-search"></i>
                    <span>Summarize the main findings from existing research in your field</span>
                </li>
                <li>
                    <i class="fas fa-history"></i>
                    <span>Include both historical context and current developments</span>
                </li>
                <li>
                    <i class="fas fa-puzzle-piece"></i>
                    <span>Identify gaps in existing research that your study will address</span>
                </li>
                <li>
                    <i class="fas fa-lightbulb"></i>
                    <span>Explain how your research will contribute to the field</span>
                </li>
            </ul>
        </div>

        <form action="step6.php" method="POST" id="preliminaryReviewForm">
            <div class="literature-section">
                <h3>Literature Summary</h3>
                <textarea 
                    name="literature_summary" 
                    placeholder="Provide a comprehensive summary of existing research related to your topic. What have other researchers found? What are the key theories and findings in your field?"
                    required><?php echo htmlspecialchars($saved_review['literature_summary'] ?? ''); ?></textarea>
                <div class="character-count">
                    <span class="current">0</span>/300 characters minimum
                </div>
                <?php if (isset($errors['literature_summary'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $errors['literature_summary']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="literature-section">
                <h3>Research Gaps</h3>
                <textarea 
                    name="research_gaps" 
                    placeholder="What aspects of this topic haven't been fully explored? What questions remain unanswered? How will your research address these gaps?"
                    required><?php echo htmlspecialchars($saved_review['research_gaps'] ?? ''); ?></textarea>
                <div class="character-count">
                    <span class="current">0</span>/150 characters minimum
                </div>
                <?php if (isset($errors['research_gaps'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $errors['research_gaps']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="button-group">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='step5.php'">
                    <i class="fas fa-arrow-left"></i> Previous Step
                </button>
                <button type="submit" class="btn btn-primary">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        // Update character count for textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            const counter = textarea.closest('.literature-section').querySelector('.current');
            
            // Update initial count
            counter.textContent = textarea.value.length;
            
            // Update count on input
            textarea.addEventListener('input', function() {
                counter.textContent = this.value.length;
            });
        });

        // Form validation
        document.getElementById('preliminaryReviewForm').addEventListener('submit', function(e) {
            const literatureSummary = document.querySelector('textarea[name="literature_summary"]');
            const researchGaps = document.querySelector('textarea[name="research_gaps"]');
            let isValid = true;

            if (literatureSummary.value.trim().length < 300) {
                isValid = false;
                showError("Literature summary must be at least 300 characters long", literatureSummary);
            }

            if (researchGaps.value.trim().length < 150) {
                isValid = false;
                showError("Research gaps must be at least 150 characters long", researchGaps);
            }

            if (!isValid) {
                e.preventDefault();
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