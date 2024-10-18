<?php
session_start(); // Start the session

// Include database connection file
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $matric = $_POST['matric'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $title = $_POST['title'];

    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: signup.php");
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare an SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, matric, password, title) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $email, $matric, $hashed_password, $title);

    // Execute the statement
    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful!";
        header("Location: dashboard.php"); // Change this to your dashboard page
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: signup.php");
        exit();
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Register</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-left">QalamiQuest</div>
        <div class="navbar-right">
            <button onclick="location.href='index.php'">Home</button>
            <button onclick="location.href='login.php'">Login</button>
        </div>
    </div>

    <!-- Registration Form -->
    <div class="register-content">
        <div class="register-form">
            <h2>Create your account</h2>
            <p>Registration is easy.</p>

            <!-- Display error message -->
            <?php
            if (isset($_SESSION['error'])) {
                echo "<p style='color: red;'>" . $_SESSION['error'] . "</p>";
                unset($_SESSION['error']); // Clear the error message after displaying it
            }
            ?>

            <form action="signup.php" method="POST">
                <label for="fullname">* Full Name</label>
                <input type="text" id="fullname" name="fullname" required>

                <label for="email">* Email</label>
                <input type="email" id="email" name="email" required>

                <label for="matric">* Matric No.</label>
                <input type="text" id="matric" name="matric" required>

                <label for="password">* Password</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">* Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <label for="title">* Title</label>
                <div class="radio-group">
                    <div>
                        <input type="radio" id="student" name="title" value="student" required>
                        <label for="student">Student</label>
                    </div>
                    <div>
                        <input type="radio" id="lecturer" name="title" value="lecturer" required>
                        <label for="lecturer">Lecturer</label>
                    </div>
                </div>

                <button type="submit">Sign Up</button>
            </form>
        </div>
    </div>

</body>

</html>
