<?php
session_start();
require 'db_connection.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Function to safely parse JSON-like string to array
function parseArrayString($input)
{
    // Trim and remove outer quotes
    $input = trim($input, '"');

    // Try JSON decoding first
    $jsonDecoded = json_decode($input, true);
    if ($jsonDecoded !== null) {
        return is_array($jsonDecoded) ? $jsonDecoded : [$jsonDecoded];
    }

    // If JSON decoding fails, return as single-item array
    return [$input];
}

// Validate and sanitize proposal ID
$proposalId = filter_input(INPUT_GET, 'proposal_id', FILTER_VALIDATE_INT);
if (!$proposalId) {
    die("Invalid proposal ID.");
}

// Fetch proposal details with prepared statement
$query = "SELECT title, introduction, problem_statement, objectives, central_research_question, 
          research_questions, interview_questions, methodologies, preliminary_review, reference, feedback 
          FROM proposals WHERE proposal_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $proposalId);
$stmt->execute();
$proposalResult = $stmt->get_result();
$proposal = $proposalResult->fetch_assoc();
$stmt->close();

// Parse complex fields
$objectives = parseArrayString($proposal['objectives']);
$researchQuestions = parseArrayString($proposal['research_questions']);
$interviewQuestions = json_decode($proposal['interview_questions'], true) ?? [[]];
$preliminaryReview = trim($proposal['preliminary_review'], '"\'');
$methodologies = json_decode($proposal['methodologies'], true) ?? [];
$cleanReference = trim($proposal['reference'], characters: '[]"\\/');


// Fetch comments with prepared statement
$query = "SELECT section_name, subsection, comment 
          FROM proposal_comments 
          WHERE proposal_id = ? 
          ORDER BY section_name, created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $proposalId);
$stmt->execute();
$commentsResult = $stmt->get_result();
$comments = $commentsResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Organize comments by section
$commentsBySection = [];
foreach ($comments as $comment) {
    $commentsBySection[$comment['section_name']][] = $comment;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Comments - <?php echo htmlspecialchars($proposal['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .comment-badge {
            background-color: rgba(59, 130, 246, 0.1);
            border-left: 4px solid #3b82f6;
        }

        h3 {
            color: #00b8a9;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">

    <!-- Main Content -->
    <main class="pt-24 pl-0 transition-all duration-300 ease-in-out" id="main-content">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <!-- Feedback Section -->
                <?php if (!empty($proposal['feedback'])): ?>
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                        <h3 class="text-xl font-semibold text-blue-600 mb-2">Supervisor Feedback</h3>
                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($proposal['feedback'])); ?></p>
                    </div>
                <?php endif; ?>
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Proposal Comments:
                </h1>

                <div class="space-y-6">
                    <!-- Title Section -->
                    <div class="border-b pb-4">
                        <h3 class="text-xl font-semibold text-blue-600 mb-3">Title</h3>
                        <p class="text-gray-700 mb-2"><?php echo htmlspecialchars($proposal['title']); ?></p>
                        <?php if (isset($commentsBySection['Title'])): ?>
                            <div class="mt-2 space-y-2">
                                <?php foreach ($commentsBySection['Title'] as $comment): ?>
                                    <div class="comment-badge p-3 rounded-lg">
                                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Introduction Section -->
                    <div class="border-b pb-4">
                        <h3 class="text-xl font-semibold text-blue-600 mb-3">Introduction</h3>
                        <p class="text-gray-700 mb-2"><?php echo nl2br(htmlspecialchars($proposal['introduction'])); ?>
                        </p>
                        <?php if (!isset($commentsBySection['introduction']) || empty($commentsBySection['introduction'])): ?>
                            <p class="italic text-gray-500">There are no comments yet</p>
                        <?php else: ?>
                            <div class="mt-2 space-y-2">
                                <?php foreach ($commentsBySection['introduction'] as $comment): ?>
                                    <div class="comment-badge p-3 rounded-lg">
                                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Problem Statement Section -->
                    <div class="border-b pb-4">
                        <h3 class="text-xl font-semibold text-blue-600 mb-3">Problem Statement</h3>
                        <p class="text-gray-700 mb-2">
                            <?php echo nl2br(htmlspecialchars($proposal['problem_statement'])); ?>
                        </p>
                        <?php if (!isset($commentsBySection['problem_statement']) || empty($commentsBySection['problem_statement'])): ?>
                            <p class="italic text-gray-500">There are no comments yet</p>
                        <?php else: ?>
                            <div class="mt-2 space-y-2">
                                <?php foreach ($commentsBySection['problem_statement'] as $comment): ?>
                                    <div class="comment-badge p-3 rounded-lg">
                                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Objectives Section -->
                    <div class="border-b pb-4">
                        <h3 class="text-xl font-semibold text-blue-600 mb-3">Objectives</h3>
                        <?php foreach ($objectives as $objective): ?>
                            <p class="text-gray-700 mb-2"><?php echo htmlspecialchars($objective); ?></p>
                        <?php endforeach; ?>
                        <?php if (!isset($commentsBySection['objectives']) || empty($commentsBySection['objectives'])): ?>
                            <p class="italic text-gray-500">There are no comments yet</p>
                        <?php else: ?>
                            <div class="mt-2 space-y-2">
                                <?php foreach ($commentsBySection['objectives'] as $comment): ?>
                                    <div class="comment-badge p-3 rounded-lg">
                                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Central Research Question Section -->
                    <div class="border-b pb-4">
                        <h3 class="text-xl font-semibold text-blue-600 mb-3">Central Research Question</h3>
                        <p class="text-gray-700 mb-2">
                            <?php echo nl2br(htmlspecialchars($proposal['central_research_question'])); ?>
                        </p>
                        <?php if (!isset($commentsBySection['central_research_question']) || empty($commentsBySection['central_research_question'])): ?>
                            <p class="italic text-gray-500">There are no comments yet</p>
                        <?php else: ?>
                            <div class="mt-2 space-y-2">
                                <?php foreach ($commentsBySection['central_research_question'] as $comment): ?>
                                    <div class="comment-badge p-3 rounded-lg">
                                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Research and Interview Questions Section -->
                    <div class="border-b pb-4">
                        <h3 class="text-xl font-semibold text-blue-600 mb-3">Research and Interview Questions</h3>
                        <?php
                        // Combine research questions and interview questions
                        $combinedQuestions = [];
                        foreach ($researchQuestions as $researchIndex => $researchQuestion) {
                            $combinedQuestions[] = ['type' => 'research', 'question' => $researchQuestion];

                            // Add corresponding interview questions
                            if (isset($interviewQuestions[$researchIndex])) {
                                foreach ($interviewQuestions[$researchIndex] as $interviewQuestion) {
                                    $combinedQuestions[] = ['type' => 'interview', 'question' => $interviewQuestion];
                                }
                            }
                        }

                        // Display combined questions
                        foreach ($combinedQuestions as $questionItem): ?>
                            <p class="text-gray-700 mb-2 <?php
                            echo $questionItem['type'] == 'research'
                                ? 'font-semibold text-blue-700'
                                : 'ml-4 text-gray-600';
                            ?>">
                                <?php
                                echo $questionItem['type'] == 'interview' ? '• ' : '';
                                echo htmlspecialchars($questionItem['question']);
                                ?>
                            </p>
                        <?php endforeach; ?>

                        <?php
                        // Combine comments for research_questions and interview_questions
                        $combinedComments = array_merge(
                            $commentsBySection['research_questions'] ?? [],
                            $commentsBySection['interview_questions'] ?? []
                        );

                        if (!empty($combinedComments)): ?>
                            <div class="mt-2 space-y-2">
                                <?php foreach ($combinedComments as $comment): ?>
                                    <div class="comment-badge p-3 rounded-lg">
                                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="italic text-gray-500 mt-2">There are no comments yet</p>
                        <?php endif; ?>
                    </div>

                    <!-- Methodologies Section -->
                    <div class="border-b pb-4">
                        <h3 class="text-xl font-semibold text-blue-600 mb-3">Methodologies</h3>
                        <?php if (!empty($methodologies)): ?>
                            <div class="space-y-4">
                                <?php foreach ($methodologies as $key => $value): ?>
                                    <div>
                                        <h4 class="text-lg font-medium text-gray-700 mb-2">
                                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>
                                        </h4>
                                        <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($value)); ?></p>

                                        <?php
                                        // Find comments specific to this sub-methodology
                                        $subMethodologyComments = array_filter($commentsBySection['methodologies'] ?? [], function ($comment) use ($key) {
                                            return isset($comment['subsection']) && $comment['subsection'] === $key;
                                        });

                                        if (!empty($subMethodologyComments)): ?>
                                            <div class="mt-2 space-y-2">
                                                <?php foreach ($subMethodologyComments as $comment): ?>
                                                    <div class="comment-badge p-3 rounded-lg">
                                                        <p class="text-gray-700">
                                                            <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                                        </p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="italic text-gray-500 mt-2">No comments for this methodology yet</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-700">No methodologies details available.</p>
                        <?php endif; ?>

                        <?php
                        // Display any general methodology comments (without specific subsection)
                        $generalMethodologyComments = array_filter($commentsBySection['methodologies'] ?? [], function ($comment) {
                            return !isset($comment['subsection']) || $comment['subsection'] === '';
                        });

                        if (!empty($generalMethodologyComments)): ?>
                            <div class="mt-4 space-y-2">
                                <h4 class="text-lg font-medium text-gray-700 mb-2">General Comments</h4>
                                <?php foreach ($generalMethodologyComments as $comment): ?>
                                    <div class="comment-badge p-3 rounded-lg">
                                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Preliminary Review Section -->
                    <div class="border-b pb-4">
                        <h3 class="text-xl font-semibold text-blue-600 mb-3">Preliminary Review</h3>
                        <p class="text-gray-700 mb-2"><?php echo nl2br(htmlspecialchars($preliminaryReview)); ?></p>
                        <?php if (!isset($commentsBySection['preliminary_review']) || empty($commentsBySection['preliminary_review'])): ?>
                            <p class="italic text-gray-500">There are no comments yet</p>
                        <?php else: ?>
                            <div class="mt-2 space-y-2">
                                <?php foreach ($commentsBySection['preliminary_review'] as $comment): ?>
                                    <div class="comment-badge p-3 rounded-lg">
                                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- References Section -->
                    <div>
                        <h3 class="text-xl font-semibold text-blue-600 mb-3">References</h3>
                        <p class="text-gray-700 mb-2">
                            <?php
                            $cleanReference = trim($proposal['reference'], '[]"');
                            echo nl2br(htmlspecialchars($cleanReference));
                            ?>
                        </p>
                        <?php if (!isset($commentsBySection['reference']) || empty($commentsBySection['reference'])): ?>
                            <p class="italic text-gray-500">There are no comments yet</p>
                        <?php else: ?>
                            <div class="mt-2 space-y-2">
                                <?php foreach ($commentsBySection['reference'] as $comment): ?>
                                    <div class="comment-badge p-3 rounded-lg">
                                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.getElementById("main-content");

            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                mainContent.classList.add('lg:pl-64');
            } else {
                sidebar.classList.add('-translate-x-full');
                mainContent.classList.remove('lg:pl-64');
            }
        }
    </script>
</body>

</html>