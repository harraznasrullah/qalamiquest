<?php
session_start();
require_once('db_connection.php');

// Check if logged-in user is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['title']) || $_SESSION['title'] !== 'student') {
    header("Location: login.php");
    exit();
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supervisor_id = $_POST['supervisor'];
    $student_id = $_SESSION['user_id'];

    // Check if student already has a supervisor
    $check_query = "SELECT id FROM supervisors WHERE student_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "You already have a supervisor assigned.";
        $messageType = 'error';
    } else {
        // Insert new supervisor assignment
        $insert_query = "INSERT INTO supervisors (student_id, supervisor_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $student_id, $supervisor_id);

        if ($stmt->execute()) {
            $message = "Supervisor assigned successfully!";
            $messageType = 'success';
        } else {
            $message = "Failed to assign supervisor. Please try again.";
            $messageType = 'error';
        }
    }
}

// Fetch available supervisors
$supervisors_query = "SELECT id, fullname FROM users WHERE title = 'lecturer' ORDER BY fullname";
$supervisors_result = $conn->query($supervisors_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Supervisor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            color: #555;
        }

        select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            font-size: 16px;
        }

        .button-group {
            display: flex;
            /* Use flexbox for alignment */
            justify-content: space-between;
            /* Space buttons evenly */
            gap: 10px;
            /* Add a gap between buttons */
        }

        button {
            flex: 1;
            /* Each button takes equal width */
            background-color: #007bff;
            /* Default button color */
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-button {
            background-color: #6c757d;
        }

        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Assign Supervisor</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div>
                <label for="supervisor">Select Supervisor:</label>
                <select name="supervisor" id="supervisor" required>
                    <option value="">-- Select Supervisor --</option>
                    <?php while ($row = $supervisors_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['fullname']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="button-group">
                <button type="button" class="back-button"
                    onclick="window.location.href='student_dashboard.php'">Back</button>
                <button type="submit">Submit Request</button>
            </div>

        </form>
    </div>
</body>

</html>