<?php
session_start();
require_once('../db_connection.php');

// Check if the user is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['title'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}

// Get the proposal ID from the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: lecturer_dashboard.php");
    exit();
}

$proposal_id = $_GET['id'];

// Fetch proposal details
$query = "
    SELECT 
        p.title, 
        p.introduction, 
        p.problem_statement, 
        p.objectives, 
        p.central_research_question, 
        p.research_questions, 
        p.interview_questions, 
        p.preliminary_review, 
        p.methodologies, 
        p.reference, 
        u.fullname AS student_name
    FROM proposals p
    JOIN users u ON p.user_id = u.id
    WHERE p.proposal_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $proposal_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Proposal not found.";
    exit();
}

$proposal = $result->fetch_assoc();

// Decode JSON fields
$objectives = json_decode($proposal['objectives'], true);
$research_questions = json_decode($proposal['research_questions'], true);
$interview_questions = json_decode($proposal['interview_questions'], true);
$methodologies = json_decode($proposal['methodologies'], true);
$references = json_decode($proposal['reference'], true);
$redirect_url = "approval.php"; // Define the redirect URL
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Review</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="view_proposal_style.css"> <!-- Link to your external CSS file -->

</head>

<body>
    <div class="container">
        <div class="card">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php
                    echo htmlspecialchars($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="header">
                <h1 class="title"><?php echo htmlspecialchars($proposal['title']); ?></h1>
                <p class="subtitle">
                    <i class="fas fa-user"></i>
                    Student: <?php echo htmlspecialchars(ucwords(strtolower(($proposal['student_name'])))); ?>
                </p>
            </div>

            <form method="POST" action="submit_comment.php">
                <input type="hidden" name="proposal_id" value="<?php echo $proposal_id; ?>">
                <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($redirect_url); ?>">

                <!-- Introduction Section -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-book-open"></i>
                        Introduction
                    </h3>
                    <div class="content">
                        <?php echo nl2br(htmlspecialchars($proposal['introduction'])); ?>
                    </div>
                    <div class="comment-box">
                        <textarea name="comments[introduction]"
                            placeholder="Enter your comments on the introduction..."></textarea>
                    </div>
                </div>

                <!-- Problem Statement Section -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-exclamation-circle"></i>
                        Problem Statement
                    </h3>
                    <div class="content">
                        <?php echo nl2br(htmlspecialchars($proposal['problem_statement'])); ?>
                    </div>
                    <div class="comment-box">
                        <textarea name="comments[problem_statement]"
                            placeholder="Enter your comments on the problem statement..."></textarea>
                    </div>
                </div>

                <!-- Objectives Section -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-bullseye"></i>
                        Objectives
                    </h3>
                    <div class="content">
                        <ul>
                            <?php foreach ($objectives as $objective): ?>
                                <li><?php echo htmlspecialchars($objective); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="comment-box">
                        <textarea name="comments[objectives]"
                            placeholder="Enter your comments on the objectives..."></textarea>
                    </div>
                </div>

                <!-- Central Research Question -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-question-circle"></i>
                        Central Research Question
                    </h3>
                    <div class="content">
                        <?php echo nl2br(htmlspecialchars($proposal['central_research_question'])); ?>
                    </div>
                    <div class="comment-box">
                        <textarea name="comments[central_research_question]"
                            placeholder="Enter your comments on the central research question..."></textarea>
                    </div>
                </div>

                <!-- Research and Interview Questions -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-clipboard-list"></i>
                        Research and Interview Questions
                    </h3>
                    <div class="content">
                        <ul>
                            <?php
                            if (is_array($research_questions) && is_array($interview_questions)) {
                                foreach ($research_questions as $index => $research_question) {
                                    echo "<li><strong>" . htmlspecialchars($research_question) . "</strong>";

                                    if (isset($interview_questions[$index]) && is_array($interview_questions[$index])) {
                                        echo "<ul>";
                                        foreach ($interview_questions[$index] as $interview_question) {
                                            echo "<li>" . htmlspecialchars($interview_question) . "</li>";
                                        }
                                        echo "</ul>";
                                    }
                                    echo "</li>";
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="comment-box">
                        <textarea name="comments[research_questions]"
                            placeholder="Enter your comments on the research and interview questions..."></textarea>
                    </div>
                </div>

                <!-- Preliminary Review Section -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-search"></i>
                        Preliminary Review
                    </h3>
                    <div class="content">
                        <?php $preliminary_review = $proposal['preliminary_review'];
                        // Remove both single and double quotes from the beginning and end
                        $preliminary_review = trim($preliminary_review, '"\'');
                        echo nl2br(htmlspecialchars($preliminary_review)); ?>
                    </div>
                    <div class="comment-box">
                        <textarea name="comments[preliminary_review]"
                            placeholder="Enter your comments on the preliminary review..."></textarea>
                    </div>
                </div>

                <!-- Methodologies Section -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-tasks"></i>
                        Methodologies
                    </h3>
                    <?php if (is_array($methodologies) && !empty($methodologies)): ?>
                        <?php foreach ($methodologies as $key => $value): ?>
                            <div class="methodology-item">
                                <h4 class="methodology-title">
                                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?></h4>
                                <div class="content">
                                    <?php echo nl2br(htmlspecialchars($value)); ?>
                                </div>
                                <div class="comment-box">
                                    <textarea name="comments[methodologies][<?php echo htmlspecialchars($key); ?>]"
                                        placeholder="Enter your comments on <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>..."></textarea>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- References Section -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-book"></i>
                        References
                    </h3>
                    <div class="content">
                        <ul class="reference-list">
                            <?php foreach ($references as $reference): ?>
                                <li>
                                    <a href="<?php echo htmlspecialchars($reference); ?>" target="_blank">
                                        <i class="fas fa-external-link-alt"></i>
                                        <?php echo htmlspecialchars($reference); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="comment-box">
                        <textarea name="comments[references]"
                            placeholder="Enter your comments on the references..."></textarea>
                    </div>
                </div>

                <button type="submit">
                    <i class="fas fa-paper-plane"></i>
                    Submit Review
                </button>
            </form>
        </div>
    </div>
</body>

</html>