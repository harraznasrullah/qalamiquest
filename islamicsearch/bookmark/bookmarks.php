<?php
session_start();
header('Content-Type: application/json');

// Include your database connection
include(__DIR__ . '/../../db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle POST request (Adding bookmarks)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $surah = $_POST['surah'];
        $ayat = $_POST['ayat'];
        $text = $_POST['text'];
        $translation = $_POST['translation'];
        
        // Check if bookmark already exists
        $checkQuery = "SELECT id FROM bookmarks WHERE user_id = ? AND surah = ? AND ayat = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param('iii', $user_id, $surah, $ayat);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Bookmark already exists']);
            exit;
        }
        
        // Insert new bookmark
        $query = "INSERT INTO bookmarks (user_id, surah, ayat, text, translation) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iiiss', $user_id, $surah, $ayat, $text, $translation);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Bookmark saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save bookmark']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
// Handle GET request (Displaying bookmarks)
// Handle DELETE request (Deleting bookmarks)
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        error_log("Processing GET request for user_id: " . $user_id);
        
        $query = "SELECT * FROM bookmarks WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $bookmarks = [];
        while ($row = $result->fetch_assoc()) {
            $bookmarks[] = $row;
        }
        
        error_log("Found " . count($bookmarks) . " bookmarks");
        
        echo json_encode([
            'success' => true,
            'data' => $bookmarks
        ]);
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error in bookmarks.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving bookmarks: ' . $e->getMessage()
        ]);
    }
}

$conn->close();
?>