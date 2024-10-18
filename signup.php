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
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            text-align: center;
            border-radius: 5px;
        }

        .close {
            color: red;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover, .close:focus {
            color: darkred;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
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

    <!-- Modal for error message -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p id="errorMessage"></p>
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
