<?php
session_start();
header('Content-Type: application/json');

// Include database connection
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
        $surah = $_POST['surah'] ?? null;
        $ayat = $_POST['ayat'] ?? null;
        $reference = $_POST['reference'] ?? null;
        $arabic_text = $_POST['arabic_text'] ?? null;
        $text = $_POST['text'] ?? null;
        $translation = $_POST['translation'] ?? null;

        // Debug: Log the received data
        error_log("Received data - Surah: $surah, Ayat: $ayat, Reference: $reference, Arabic Text: $arabic_text, Text: $text, Translation: $translation");

        if ((!$surah || !$ayat) && (!$reference || !$arabic_text)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data']);
            exit;
        }

        // Check if bookmark already exists
        if ($surah && $ayat) {
            $checkQuery = "SELECT id FROM bookmarks WHERE user_id = ? AND surah = ? AND ayat = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param('iii', $user_id, $surah, $ayat);
        } else {
            $checkQuery = "SELECT id FROM bookmarks WHERE user_id = ? AND reference = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param('is', $user_id, $reference);
        }
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Bookmark already exists']);
            exit;
        }

        // Insert new bookmark
        if ($surah && $ayat) {
            $query = "INSERT INTO bookmarks (user_id, surah, ayat, text, english_translation) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('iisss', $user_id, $surah, $ayat, $text, $translation);
        } else {
            $query = "INSERT INTO bookmarks (user_id, reference, arabic_text, text, english_translation) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('issss', $user_id, $reference, $arabic_text, $text, $translation);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Bookmark saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save bookmark']);
        }

        $stmt->close();
    } catch (Exception $e) {
        error_log("POST error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Handle GET request (Displaying bookmarks)
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT id, surah, ayat, reference, arabic_text, text, english_translation, created_at FROM bookmarks WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $bookmarks = [];
        while ($row = $result->fetch_assoc()) {
            $bookmarks[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $bookmarks
        ]);

        $stmt->close();
    } catch (Exception $e) {
        error_log("GET error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving bookmarks: ' . $e->getMessage()
        ]);
    }
}

// Handle DELETE request (Deleting bookmarks)
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid bookmark ID']);
            exit;
        }

        $query = "DELETE FROM bookmarks WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $id, $user_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Bookmark deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete bookmark']);
        }

        $stmt->close();
    } catch (Exception $e) {
        error_log("DELETE error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting bookmark: ' . $e->getMessage()
        ]);
    }
}

$conn->close();
?>