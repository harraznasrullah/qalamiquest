<?php
session_start();
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    // Get and validate form data
    $research_design = trim($_POST['research_design'] ?? '');
    $data_collection = trim($_POST['data_collection'] ?? '');
    $data_analysis = trim($_POST['data_analysis'] ?? '');
    $sampling_method = trim($_POST['sampling_method'] ?? '');
    $ethical_considerations = trim($_POST['ethical_considerations'] ?? '');
    
    // Validation rules
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
    
    // If validation passes, save and proceed
    if (empty($errors)) {
        $_SESSION['proposal']['methodology'] = [
            'research_design' => $research_design,
            'data_collection' => $data_collection,
            'data_analysis' => $data_analysis,
            'sampling_method' => $sampling_method,
            'ethical_considerations' => $ethical_considerations
        ];
        
        header("Location: step8.php");
        exit();
    }
}

// Retrieve saved data if it exists
$saved_methodology = $_SESSION['proposal']['methodology'] ?? null;
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
        .methodology-section {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

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

        .help-text {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
            margin-bottom: 10px;
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
            <h1>Research Methodology</h1>
            <p>Describe your research approach and techniques</p>
        </div>

        <div class="progress-bar">
            <?php for($i = 1; $i <= 8; $i++): ?>
                <div class="step <?php echo $i == 7 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $i; ?></div>
                    <div class="step-label">Step <?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="guidelines">
            <h4>Writing an Effective Methodology</h4>
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
                <div class="help-text">Describe your overall research approach (e.g., qualitative, quantitative, mixed methods) and justify your choice.</div>
                <textarea 
                    name="research_design" 
                    placeholder="Explain your research design and why it's appropriate for your study..."
                    required><?php echo htmlspecialchars($saved_methodology['research_design'] ?? ''); ?></textarea>
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
                <div class="help-text">Describe how you will collect your data (e.g., interviews, surveys, observations).</div>
                <textarea 
                    name="data_collection" 
                    placeholder="Detail your data collection methods and tools..."
                    required><?php echo htmlspecialchars($saved_methodology['data_collection'] ?? ''); ?></textarea>
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
                <textarea 
                    name="data_analysis" 
                    placeholder="Describe your data analysis methods and techniques..."
                    required><?php echo htmlspecialchars($saved_methodology['data_analysis'] ?? ''); ?></textarea>
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
                <textarea 
                    name="sampling_method" 
                    placeholder="Explain your sampling strategy and participant selection criteria..."
                    required><?php echo htmlspecialchars($saved_methodology['sampling_method'] ?? ''); ?></textarea>
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
                <textarea 
                    name="ethical_considerations" 
                    placeholder="Describe the ethical considerations and how you will address them..."
                    required><?php echo htmlspecialchars($saved_methodology['ethical_considerations'] ?? ''); ?></textarea>
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
                <button type="button" class="btn btn-secondary" onclick="window.location.href='step6.php'">
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
            const counter = textarea.closest('.methodology-section').querySelector('.current');
            
            // Update initial count
            counter.textContent = textarea.value.length;
            
            // Update count on input
            textarea.addEventListener('input', function() {
                counter.textContent = this.value.length;
            });
        });

        // Form validation
        document.getElementById('methodologyForm').addEventListener('submit', function(e) {
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