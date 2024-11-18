<?php session_start(); 
include('../db_connection.php'); 

// Check if logged-in user is a lecturer
if ($_SESSION['title'] !== 'lecturer') { 
    header("Location: login.php"); 
    exit(); 
}

$supervisor_id = $_SESSION['user_id'];
$message = '';

// Process form submission first
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supervision_id = $_POST['supervision_id'];
    $action = $_POST['action'];
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    // Update status in the database
    $stmt = $conn->prepare("UPDATE supervisors SET status = ?, approval_date = NOW() WHERE id = ?");
    $stmt->bind_param("si", $status, $supervision_id);
    if ($stmt->execute()) {
        $message = "Request has been " . $status;
        // Redirect to the same page to refresh the list
        header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
        exit();
    }
}

// Get message from URL if exists
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Fetch pending requests for the logged-in supervisor
$sql = "SELECT s.id, u.fullname AS student_name, s.application_date, s.status 
        FROM supervisors s
        JOIN users u ON s.student_id = u.id
        WHERE s.supervisor_id = ? AND s.status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Supervisor Requests</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(to bottom, #ffffff, #EDFFFF);
            padding: 2rem;
            color: #333;
           
    min-height: 100vh;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 2rem;
            text-align: center;
            font-size: 2.2rem;
        }

        .message {
            background-color: #4CAF50;
            color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            text-align: center;
            animation: fadeOut 5s forwards;
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background-color: white;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th:nth-child(1), td:nth-child(1) { width: 8%; }
        th:nth-child(2), td:nth-child(2) { width: 30%; }
        th:nth-child(3), td:nth-child(3) { width: 20%; }
        th:nth-child(4), td:nth-child(4) { width: 15%; }
        th:nth-child(5), td:nth-child(5) { width: 27%; text-align: center; }

        th {
            background-color: #2c3e50;
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        form {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin: 0;
        }

        button {
            min-width: 90px;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        button[value="approve"] {
            background-color: #4CAF50;
            color: white;
        }

        button[value="approve"]:hover {
            background-color: #45a049;
        }

        button[value="reject"] {
            background-color: #f44336;
            color: white;
        }

        button[value="reject"]:hover {
            background-color: #da190b;
        }

        .no-requests {
            text-align: center;
            padding: 3rem 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 2rem;
        }

        .no-requests i {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
            display: block;
        }

        .no-requests p {
            color: #64748b;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .no-requests small {
            color: #94a3b8;
            display: block;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            th, td {
                padding: 0.75rem;
            }

            button {
                padding: 0.4rem 0.8rem;
                min-width: 80px;
            }

            .no-requests {
                padding: 2rem 1rem;
            }
            
            th:nth-child(n), td:nth-child(n) {
                width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Supervisor Requests</h1>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Student Name</th>
                        <th>Application Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['application_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="supervision_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="action" value="approve">Approve</button>
                                    <button type="submit" name="action" value="reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-requests">
                <i>📋</i>
                <p>No Student Requests Yet</p>
                <small>When students submit supervision requests, they will appear here</small>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>