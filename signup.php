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
        $_SESSION['form_data'] = $_POST; // Store form data to preserve it after reload
        header("Location: signup.php");
        exit();
    }

    // Check if the email or matric number already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR matric = ?");
    $stmt->bind_param("ss", $email, $matric);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $_SESSION['error'] = "Email or Matric Number already exists. Please use a different one.";
        $_SESSION['form_data'] = $_POST; // Store form data to preserve it after reload
        header("Location: signup.php");
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare an SQL statement to insert the user into the database
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, matric, password, title) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $email, $matric, $hashed_password, $title);

    // Execute the statement
    if ($stmt->execute()) {
        // Store the user's name and title in the session
        $_SESSION['user_name'] = $fullname; // Store full name in session
        $_SESSION['user_email'] = $email; // Optionally store the user's email
        $_SESSION['title'] = $title; // Store user title (student/lecturer)

        // Redirect based on the user's title
        $_SESSION['success'] = "Registration successful!";
        if ($title === 'student') {
            header("Location: student_dashboard.php");
        } elseif ($title === 'lecturer') {
            header("Location: lecturer_dashboard.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        $_SESSION['form_data'] = $_POST; // Store form data to preserve it after reload
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

    <!-- Modal for error message -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p id="errorMessage"></p>
        </div>
    </div>

    <!-- Registration Form -->
    <div class="register-content">
        <div class="register-form">
            <h2>Create your account</h2>
            <p>Registration is easy.</p>

            <form action="signup.php" method="POST">
                <label for="fullname">* Full Name</label>
                <input type="text" id="fullname" name="fullname" 
                    value="<?php echo isset($_SESSION['form_data']['fullname']) ? htmlspecialchars($_SESSION['form_data']['fullname']) : ''; ?>" 
                    required>

                <label for="email">* Email</label>
                <input type="email" id="email" name="email" 
                    value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>" 
                    required>

                <label for="matric">* Matric No.</label>
                <input type="text" id="matric" name="matric" 
                    value="<?php echo isset($_SESSION['form_data']['matric']) ? htmlspecialchars($_SESSION['form_data']['matric']) : ''; ?>" 
                    required>

                <label for="password">* Password</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">* Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <label for="title">* Title</label>
                <div class="radio-group">
                    <div>
                        <input type="radio" id="student" name="title" value="student" 
                            <?php echo (isset($_SESSION['form_data']['title']) && $_SESSION['form_data']['title'] === 'student') ? 'checked' : ''; ?> 
                            required>
                        <label for="student">Student</label>
                    </div>
                    <div>
                        <input type="radio" id="lecturer" name="title" value="lecturer" 
                            <?php echo (isset($_SESSION['form_data']['title']) && $_SESSION['form_data']['title'] === 'lecturer') ? 'checked' : ''; ?> 
                            required>
                        <label for="lecturer">Lecturer</label>
                    </div>
                </div>

                <button type="submit">Sign Up</button>
            </form>
        </div>
    </div>

    <script>
        // Show the modal if there's an error
        window.onload = function () {
            <?php if (isset($_SESSION['error'])) { ?>
                var errorMessage = "<?php echo $_SESSION['error']; ?>";
                showModal(errorMessage);
            <?php unset($_SESSION['error']); } // Clear the error session data ?>
        }

        function showModal(message) {
            document.getElementById("errorMessage").innerText = message;
            document.getElementById("errorModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("errorModal").style.display = "none";
        }

        // Close the modal when the user clicks outside of the modal content
        window.onclick = function(event) {
            var modal = document.getElementById("errorModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>

</html>
<?php
// Clear the form data after rendering the page
unset($_SESSION['form_data']);
?>
