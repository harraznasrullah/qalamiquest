<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
require '../db_connection.php';

$topic_id = $_GET['id'];

// Updated query to include vote count
$topic_stmt = $conn->prepare("
    SELECT discussion_topics.*, users.fullname AS created_by_name
    FROM discussion_topics
    JOIN users ON discussion_topics.created_by = users.id
    WHERE discussion_topics.id = ?
");
$topic_stmt->bind_param('i', $topic_id);
$topic_stmt->execute();
$topic = $topic_stmt->get_result()->fetch_assoc();

// Updated comments query to include vote count
$comments_stmt = $conn->prepare("
    SELECT 
        discussion_comments.*, 
        users.fullname AS commented_by_name
    FROM discussion_comments
    JOIN users ON discussion_comments.commented_by = users.id
    WHERE discussion_comments.topic_id = ?
    ORDER BY discussion_comments.parent_id ASC, discussion_comments.created_at ASC
");
$comments_stmt->bind_param('i', $topic_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

// Helper function for time ago
function timeAgo($datetime)
{
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 0) {
        return 'Just now';
    } elseif ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } elseif ($diff < 2592000) {
        return floor($diff / 604800) . ' weeks ago';
    } else {
        return date('F j, Y', $time);
    }
}



function displayComments($comments, $parentId = null, $topicId = null)
{
    $displayComments = [];
    $replyCount = [];

    // First, count replies for each comment
    foreach ($comments as $comment) {
        if ($comment['parent_id'] !== null) {
            $replyCount[$comment['parent_id']] =
                isset($replyCount[$comment['parent_id']])
                ? $replyCount[$comment['parent_id']] + 1
                : 1;
        }
    }

    // Then display comments
    foreach ($comments as $comment) {
        if ($comment['parent_id'] == $parentId) {
            $commentReplies = isset($replyCount[$comment['id']]) ? $replyCount[$comment['id']] : 0;

            echo '<div class="comment" data-comment-id="' . $comment['id'] . '">';

            echo '<div class="comment-content">';
            echo '<div class="comment-header">';
            echo '<span class="comment-author">' . htmlspecialchars($comment['commented_by_name']) . '</span>';
            echo '<span class="comment-timestamp">' . timeAgo($comment['created_at']) . '</span>';
            echo '</div>';

            echo '<div class="comment-text">';
            echo htmlspecialchars($comment['comment']);
            echo '</div>';

            echo '<div class="comment-actions">';
            // Only show reply button for top-level comments
            if ($parentId === null) {
                echo '<button class="reply-button" onclick="showReplyForm(' . $comment['id'] . ')">Reply</button>';
            }

            // Show reply count if there are replies
            if ($commentReplies > 0) {
                echo '<span class="reply-count" onclick="toggleReplies(' . $comment['id'] . ')">' .
                    $commentReplies . ' ' . ($commentReplies === 1 ? 'reply' : 'replies') .
                    '</span>';
            }
            echo '</div>';

            // Reply form (hidden by default)
            if ($parentId === null) {
                echo '<div id="reply-form-' . $comment['id'] . '" class="reply-form" style="display: none;">';
                echo '<form action="discussion_comment.php" method="POST">';
                echo '<textarea name="comment" placeholder="Write a reply..." required></textarea>';
                echo '<input type="hidden" name="parent_id" value="' . $comment['id'] . '">';
                echo '<input type="hidden" name="topic_id" value="' . $topicId . '">';
                echo '<button type="submit">Submit Reply</button>';
                echo '</form>';
                echo '</div>';
            }

            // Nested comments container
            if ($commentReplies > 0) {
                echo '<div id="nested-comments-' . $comment['id'] . '" class="nested-comments">';
                // Recursive call to display replies
                displayComments($comments, $comment['id'], $topicId);
                echo '</div>';
            }

            echo '</div>'; // close comment-content
            echo '</div>'; // close comment
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="topic.css">


    <title><?= htmlspecialchars($topic['title']) ?></title>
    <script>
        function showReplyForm(commentId) {
            const replyForm = document.getElementById(`reply-form-${commentId}`);
            replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
        }

        function toggleReplies(commentId) {
            const nestedComments = document.getElementById(`nested-comments-${commentId}`);
            nestedComments.classList.toggle('show');
        }
    </script>
</head>

<body>
    <div class="topic-container">
        <div class="topic-header">
            <a href="student_discussion.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="topic-title"><?= htmlspecialchars($topic['title']) ?></div>
        </div>
        <div class="topic-meta">
            Posted by <?= htmlspecialchars($topic['created_by_name']) ?>
            on <?= date('F j, Y', strtotime($topic['created_at'])) ?>
        </div>
        <div class="topic-description">
            <?= htmlspecialchars($topic['description']) ?>
        </div>
    </div>

    <div class="comment-form">
        <form action="discussion_comment.php" method="POST">
            <textarea name="comment" placeholder="What are your thoughts?" required></textarea>
            <input type="hidden" name="topic_id" value="<?= $topic_id ?>">
            <button type="submit">Comment</button>
        </form>
    </div>

    <div class="comments-section">
        <?php
        $comments = [];
        while ($row = $comments_result->fetch_assoc()) {
            $comments[] = $row;
        }

        displayComments($comments, null, $topic_id);
        ?>
    </div>
</body>


</html>