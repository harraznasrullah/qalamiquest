<?php
session_start();
require_once('../db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proposal_id = $_POST['proposal_id'];
    $comments = $_POST['comments'];
    
    // For debugging
    // error_log(print_r($_POST['comments'], true));
    
    try {
        foreach ($comments as $section => $value) {
            if ($section === 'methodologies') {
                // Handle methodology comments
                if (is_array($value)) {
                    foreach ($value as $methodology_key => $comment) {
                        if (!empty($comment) && is_string($comment)) {
                            $query = "INSERT INTO proposal_comments 
                                    (proposal_id, section_name, subsection, comment, lecturer_id, created_at)
                                    VALUES (?, ?, ?, ?, ?, NOW())";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("isssi",
                                $proposal_id,
                                $section,
                                $methodology_key,
                                $comment,
                                $_SESSION['user_id']
                            );
                            $stmt->execute();
                            
                            if ($stmt->error) {
                                throw new Exception("Error saving methodology comment: " . $stmt->error);
                            }
                        }
                    }
                }
            } else {
                // Handle other regular comments
                if (!empty($value) && is_string($value)) {
                    $query = "INSERT INTO proposal_comments
                            (proposal_id, section_name, comment, lecturer_id, created_at)
                            VALUES (?, ?, ?, ?, NOW())";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("issi",
                        $proposal_id,
                        $section,
                        $value,
                        $_SESSION['user_id']
                    );
                    $stmt->execute();
                    
                    if ($stmt->error) {
                        throw new Exception("Error saving comment: " . $stmt->error);
                    }
                }
            }
        }
        
        // Success message
        $_SESSION['success_message'] = "Comments saved successfully!";
        // Change redirect to approval.php
        header("Location: approval.php");
        exit();
        
    } catch (Exception $e) {
        // Error handling
        $_SESSION['error_message'] = "Error saving comments: " . $e->getMessage();
        // Keep the error redirect to view_proposal.php
        header("Location: view_proposal.php?id=" . $proposal_id);
        exit();
    }
}
?>