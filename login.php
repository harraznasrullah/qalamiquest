<?php
session_start(); // Start the session

// Include database connection file
include 'db_connection.php';

$email = ''; // Initialize email variable
$email_exists = true; // Flag to check if email exists

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email']; // Capture the email input
    $password = $_POST['password'];

    // Prepare an SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Check if email exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Password is correct, set session variables
            $_SESSION['email'] = $email; // Store email in session
            header("Location: dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            $_SESSION['error'] = "Invalid password.";
        }
    } else {
        $email = ''; // Clear the email input field if email is not found
        $email_exists = false; // Set flag to false if email is not found
        $_SESSION['error'] = "Invalid email.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QalamiQuest - Login</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-left">QalamiQuest</div>
        <div class="navbar-right">
            <button onclick="location.href='index.php'">Home</button>
            <button onclick="location.href='signup.php'">Signup</button>
        </div>
    </div>
    <!-- Modal for error message -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p id="errorMessage"></p>
        </div>
    </div>
    <!-- Login Form -->
    <div class="login-content">
        <div class="login-form">
            <h2>Log In</h2>
            <p>Log in is easy.</p>

            <form action="login.php" method="POST">
                <input type="email" id="email" name="email" placeholder="Email" required
                    value="<?php echo htmlspecialchars($email); ?>">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="create-account-link">
                <a href="signup.php">Create a new account</a>
            </div>
        </div>
    </div>

    <script>
        // Show the modal if there's an error
        window.onload = function () {
            <?php if (isset($_SESSION['error'])) { ?>
                var errorMessage = "<?php echo $_SESSION['error']; ?>";
                showModal(errorMessage);
                <?php unset($_SESSION['error']);
            } // Clear the error session data ?>
        }

        function showModal(message) {
            document.getElementById("errorMessage").innerText = message;
            document.getElementById("errorModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("errorModal").style.display = "none";
        }

        // Close the modal when the user clicks outside of the modal content
        window.onclick = function (event) {
            var modal = document.getElementById("errorModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>

</html>
<?php
// Clear the session error after rendering the page
unset($_SESSION['error']);
?>