<?php
session_start();
include('db_connection.php'); // Include your DB connection file

// Get the proposal_id from the URL
$proposal_id = $_GET['proposal_id'];

// Ensure the proposal belongs to the logged-in student
$user_id = $_SESSION['user_id'];

// Check if the proposal exists and belongs to the logged-in user
$query = "SELECT * FROM proposals WHERE proposal_id = ? AND user_id = ? AND is_deleted = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $proposal_id, $user_id);  // 'ii' represents two integer parameters
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Proceed with marking the proposal as deleted (soft delete)
    $update_query = "UPDATE proposals SET is_deleted = 1 WHERE proposal_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('i', $proposal_id);  // 'i' represents an integer parameter

    if ($update_stmt->execute()) {
        // Redirect back to the dashboard with a success message
        header("Location: student_dashboard.php?message=Proposal successfully deleted.");
        exit();
    } else {
        // Handle error
        echo "Error deleting the proposal.";
    }
} else {
    // If the proposal is not found or doesn't belong to the student
    echo "Proposal not found or unauthorized action.";
}

$stmt->close();
$update_stmt->close();
?>
