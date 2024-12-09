<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic_id = $_POST['topic_id'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];
    $parent_id = isset($_POST['parent_id']) ? $_POST['parent_id'] : null;

    $stmt = $conn->prepare("
        INSERT INTO discussion_comments (topic_id, comment, commented_by, parent_id)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('isii', $topic_id, $comment, $user_id, $parent_id);
    $stmt->execute();

    header("Location: topic.php?id=$topic_id");
    exit();
}
?>
