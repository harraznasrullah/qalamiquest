<?php
// Start session and include database connection
session_start();
include '../db_connection.php';

// Initialize error handling and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security: Check if user is logged in and is a supervisor
if (!isset($_SESSION['user_id']) || !isset($_SESSION['title']) || $_SESSION['title'] !== 'lecturer') {
    die("Unauthorized access");
}

$supervisor_id = $_SESSION['user_id'];

try {
    // Prepare query to fetch students assigned to the supervisor
    $query = "
        SELECT student_id 
        FROM supervisors 
        WHERE supervisor_id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare student query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $supervisor_id);
    $stmt->execute();
    $students_result = $stmt->get_result();
    
    $student_ids = [];
    while ($row = $students_result->fetch_assoc()) {
        $student_ids[] = $row['student_id'];
    }
    $stmt->close();

    // Section title mapping with more comprehensive sections
    $section_titles = [
        'semi_structured' => 'Semi-Structured Interview',
        'data_analysis' => 'Data Analysis',
        'generating_themes' => 'Generating Themes',
        'literature_review' => 'Literature Review',
        'methodology' => 'Research Methodology',
        'conclusion' => 'Conclusion'
    ];

    // Check if supervisor has any assigned students
    if (empty($student_ids)) {
        throw new Exception("No students are currently assigned to you.");
    }

    // Prepare query to fetch submissions with robust error checking
    $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    $query = "
        SELECT 
            s.id, 
            s.student_id, 
            s.section, 
            s.file_name, 
            s.uploaded_at, 
            u.fullname AS student_name
        FROM submission s
        JOIN users u ON s.student_id = u.id
        WHERE s.student_id IN ($placeholders)
        ORDER BY s.uploaded_at DESC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare submissions query: " . $conn->error);
    }

    // Dynamically bind parameters based on number of students
    $param_types = str_repeat('i', count($student_ids));
    $bind_names = [];
    $bind_names[] = $param_types;
    
    for ($i = 0; $i < count($student_ids); $i++) {
        $bind_names[] = &$student_ids[$i];
    }
    
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
    
    $stmt->execute();
    $submissions_result = $stmt->get_result();

    // Start output buffering for clean error handling
    ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Submissions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
        }
        .no-submissions { color: #666; margin-top: 20px; }
        .view-file { 
            text-decoration: none; 
            color: #007bff; 
            font-weight: bold; 
        }
        .view-file:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h2>Student Submissions</h2>
    
    <?php if ($submissions_result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Section</th>
                    <th>File Name</th>
                    <th>Uploaded At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $submissions_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars($section_titles[$row['section']] ?? 'Unknown Section') ?></td>
                        <td><?= htmlspecialchars($row['file_name']) ?></td>
                        <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
                        <td>
                            <a href='uploads/<?= htmlspecialchars($row['file_name']) ?>' 
                               target='_blank' 
                               class='view-file'>View File</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-submissions">No submissions found for your students.</p>
    <?php endif; ?>
</body>
</html>

<?php
    // Output the buffered content
    ob_end_flush();
    $stmt->close();

} catch (Exception $e) {
    // Log the error and show a user-friendly message
    error_log($e->getMessage());
    ob_end_clean();
    echo "<p style='color: red;'>An error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
} finally {
    // Ensure database connection is closed
    if (isset($conn)) {
        $conn->close();
    }
}
?>