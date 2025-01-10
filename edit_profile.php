<?php
session_start();

// Include database connection file
include 'db_connection.php';

// Unset success and error messages at the beginning to avoid persistence
unset($_SESSION['success']);
unset($_SESSION['error']);

// Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user's current profile information from the database
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT fullname, email, title FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($fullname, $email, $title);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_fullname = $_POST['fullname'];
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];

    // Check if the confirm_password is set in the POST request
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validation
    if (empty($new_fullname) || empty($new_email)) {
        $_SESSION['error'] = "Full Name and Email cannot be empty.";
    } else {
        // Initialize error variable
        $update_success = false;

        // Update password only if it's provided and matches confirmation
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $_SESSION['error'] = "Passwords do not match.";
            } else {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Prepare SQL query to update fullname, email, and password
                $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $new_fullname, $new_email, $hashed_password, $user_id);

                // Execute the query and check if successful
                if ($stmt->execute()) {
                    $update_success = true;
                } else {
                    $_SESSION['error'] = "Error updating profile: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            // Update fullname and email only if password is not changed
            $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_fullname, $new_email, $user_id);

            // Execute the query and check if successful
            if ($stmt->execute()) {
                $update_success = true;
            } else {
                $_SESSION['error'] = "Error updating profile: " . $stmt->error;
            }
            $stmt->close();
        }

        // If the update is successful, update session and redirect
        if ($update_success) {
            $_SESSION['success'] = "Profile updated successfully!";
            $_SESSION['user_name'] = $new_fullname;

            // Redirect user based on their role
            if ($title === 'student') {
                header("Location: student_dashboard.php");
            } elseif ($title === 'lecturer') {
                header("Location: lecturer/lecturer_dashboard.php");
            }
            exit();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Global styling */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .profile-content {
            max-width: 500px;
            background-color: white;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        .profile-form h2 {
            font-size: 26px;
            color: #333;
            margin-bottom: 20px;
        }

        .profile-form label {
            font-size: 14px;
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
            display: block;
            text-align: left;
        }

        .profile-form input[type="text"],
        .profile-form input[type="email"],
        .profile-form input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            transition: all 0.2s;
        }

        .profile-form input:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(0, 255, 0, 0.3);
        }

        .submit-button,
        .cancel-button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .submit-button:hover {
            background-color: #43a047;
        }

        .cancel-button {
            background-color: #e74c3c;
            margin-top: 10px;
        }

        .cancel-button:hover {
            background-color: #c0392b;
        }

        /* Success and error messages */
        .success-message,
        .error-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>

    <!-- Edit Profile Form -->
    <div class="profile-content">
        <div class="profile-form">
            <h2>Edit Your Profile</h2>

            <!-- Display success or error messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php elseif (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="edit_profile.php" method="POST">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>"
                    required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

                <label for="password">New Password (optional)</label>
                <input type="password" id="password" name="password">

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">

                <button type="submit" class="submit-button">Update Profile</button>
                <button type="button" class="cancel-button" onclick="history.back()">Cancel</button>
            </form>
        </div>
    </div>

</body>

</html>