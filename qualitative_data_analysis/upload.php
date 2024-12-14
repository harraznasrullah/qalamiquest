<?php
session_start();
include '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id']; // Assuming session stores user ID
    $section = $_POST['section'];

    // Validate section
    $valid_sections = ['semi_structured', 'data_analysis', 'generating_themes'];
    if (!in_array($section, $valid_sections)) {
        header("Location: qualitative_data_analysis.php?upload=invalid");
        exit;
    }

    // Handle file upload
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        header("Location: qualitative_data_analysis.php?upload=error");
        exit;
    }

    $file = $_FILES['file'];
    $upload_dir = __DIR__ . '/uploads/';
    $file_name = basename($file['name']);
    $file_path = $upload_dir . $file_name;

    // Create upload directory if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Insert into the `submission` table
        $stmt = $conn->prepare("INSERT INTO submission (student_id, section, file_name) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $student_id, $section, $file_name);

        if ($stmt->execute()) {
            header("Location: qualitative_data_analysis.php?upload=success&section=$section");
            exit;
        } else {
            header("Location: qualitative_data_analysis.php?upload=error");
            exit;
        }
        $stmt->close();
    } else {
        header("Location: qualitative_data_analysis.php?upload=error");
        exit;
    }
} else {
    header("Location: qualitative_data_analysis.php?upload=invalid");
    exit;
}
?>
