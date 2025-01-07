<?php
session_start();
require('../db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic_id = $_POST['topic_id'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];
    $parent_id = isset($_POST['parent_id']) ? $_POST['parent_id'] : null;

    $stmt = $conn->prepare("INSERT INTO discussion_comments (comment, topic_id, commented_by, created_at, parent_id) VALUES (?, ?, ?, ?, ?)");
$now = (new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur')))->format('Y-m-d H:i:s');
$stmt->bind_param('siisi', $comment, $topic_id, $commented_by, $now,  $parent_id);
$stmt->execute();

    header("Location: topic.php?id=$topic_id");
    exit();
}
?>
