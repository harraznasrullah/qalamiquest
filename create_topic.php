<?php 
session_start(); 
require 'db_connection.php';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO discussion_topics (title, description, created_by) VALUES (?, ?, ?)");
    $stmt->bind_param('ssi', $title, $description, $user_id);
    $stmt->execute();
    
    header('Location: student_discussion.php');
    exit(); 
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
    font-family: Arial, sans-serif;
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f4f4f4;
    line-height: 1.6;
}

h1 {
    text-align: center;
    color: #333;
    border-bottom: 2px solid #4a4a4a;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.form-container {
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.button-group {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

label {
    display: block;
    margin-bottom: 5px;
    color: #555;
    font-weight: bold;
}

input[type="text"],
textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
}

textarea {
    resize: vertical;
    min-height: 100px;
}

.btn {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
    text-align: center;
    text-decoration: none;
}

.btn-create {
    background-color: #4CAF50;
    color: white;
}

.btn-create:hover {
    background-color: #45a049;
}

.btn-back {
    background-color: #f0f0f0;
    color: #333;
    border: 1px solid #ddd;
}

.btn-back:hover {
    background-color: #e0e0e0;
}
    </style>
    <title>Create New Topic</title>
    <link rel="stylesheet" href="create_topic.css">
</head>
<body>
    <h1>Create a New Topic</h1>
    <div class="form-container">
        <form action="create_topic.php" method="POST">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" required>
            
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="5" required></textarea>
            
            <div class="button-group">
                <a href="student_discussion.php" class="btn btn-back">Back to Discussions</a>
                <button type="submit" class="btn btn-create">Create Topic</button>
            </div>
        </form>
    </div>
</body>
</html>