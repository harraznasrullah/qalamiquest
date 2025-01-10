<?php
// Start session and include database connection
session_start();
include '../db_connection.php';

// Initialize error handling and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security: Check if user is logged in and is a lecturer
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

    // Pagination setup
    $results_per_page = 10; // Number of results per page
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Current page
    $offset = ($page - 1) * $results_per_page;

    // Prepare query to fetch submissions with pagination
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
        ORDER BY s.uploaded_at DESC
        LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare submissions query: " . $conn->error);
    }

    // Dynamically bind parameters based on number of students
    $param_types = str_repeat('i', count($student_ids)) . 'ii'; // Add 'ii' for LIMIT and OFFSET
    $bind_names = [];
    $bind_names[] = $param_types;

    // Add student IDs to bind parameters
    for ($i = 0; $i < count($student_ids); $i++) {
        $bind_names[] = &$student_ids[$i];
    }

    // Add LIMIT and OFFSET to bind parameters
    $bind_names[] = &$results_per_page;
    $bind_names[] = &$offset;

    // Bind all parameters dynamically
    call_user_func_array([$stmt, 'bind_param'], $bind_names);

    $stmt->execute();
    $submissions_result = $stmt->get_result();

    // Count total submissions for pagination
    $total_query = "
        SELECT COUNT(*) as total 
        FROM submission s
        JOIN users u ON s.student_id = u.id
        WHERE s.student_id IN ($placeholders)";
    $total_stmt = $conn->prepare($total_query);
    if (!$total_stmt) {
        throw new Exception("Failed to prepare total submissions query: " . $conn->error);
    }

    // Bind student IDs for total count query
    $total_bind_names = [];
    $total_bind_names[] = str_repeat('i', count($student_ids));
    for ($i = 0; $i < count($student_ids); $i++) {
        $total_bind_names[] = &$student_ids[$i];
    }
    call_user_func_array([$total_stmt, 'bind_param'], $total_bind_names);

    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_submissions = $total_row['total'];
    $total_pages = ceil($total_submissions / $results_per_page);

    // Start output buffering for clean error handling
    ob_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Submissions</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h2 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #2c3e50; /* Darker header */
            color: #fff;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* Button Styles */
        .view-file {
            text-decoration: none;
            color: #3498db;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .view-file:hover {
            background-color: #3498db;
            color: #fff;
        }

        /* No Submissions Message */
        .no-submissions {
            text-align: center;
            color: #666;
            margin-top: 30px;
            font-size: 18px;
        }

        /* Pagination Styles */
        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            color: #3498db;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .pagination a:hover {
            background-color: #3498db;
            color: #fff;
        }

        .pagination .current {
            background-color: #2c3e50;
            color: #fff;
            padding: 8px 16px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <h2>Student Submissions</h2>

    <?php if ($submissions_result->num_rows > 0): ?>
        <table aria-label="Student Submissions">
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
                            <?php if (file_exists('uploads/' . $row['file_name'])): ?>
                                <a href='uploads/<?= htmlspecialchars($row['file_name']) ?>' target='_blank' class='view-file' aria-label="View File">View File</a>
                            <?php else: ?>
                                <span class="file-missing">File Missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1">First</a>
                <a href="?page=<?= $page - 1 ?>">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>">Next</a>
                <a href="?page=<?= $total_pages ?>">Last</a>
            <?php endif; ?>
        </div>
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
    echo "<p style='color: black; text-align: center; margin-top: 20px;'>" . htmlspecialchars($e->getMessage()) . "</p>";
} finally {
    // Ensure database connection is closed
    if (isset($conn)) {
        $conn->close();
    }
}
?>