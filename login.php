<?php
session_start(); // Start the session

// Include database connection
include 'db_connection.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the user exists in the database
    $stmt = $conn->prepare("SELECT id, fullname, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $fullname, $hashed_password);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['fullname'] = $fullname;

            // Redirect to the dashboard or homepage
            header("Location: dashboard.php");
            exit();
        } else {
            // Password is incorrect
            $_SESSION['error'] = "Email or Password is incorrect.";
        }
    } else {
        // Email not found
        $_SESSION['error'] = "Email or Password is incorrect.";
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
                <input type="email" id="email" name="email" placeholder="Email" 
                    value="<?php echo isset($_SESSION['email_input']) ? htmlspecialchars($_SESSION['email_input']) : ''; ?>" 
                    required>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="create-account-link">
                <a href="signup.php">Create a new account</a>
            </div>
        </div>
    </div>

    <script>
        // Show modal if there's an error
        window.onload = function () {
            <?php if (isset($_SESSION['error'])) { ?>
                var errorMessage = "<?php echo $_SESSION['error']; ?>";
                showModal(errorMessage);
                <?php unset($_SESSION['error']); // Clear the error session data ?>
            <?php } ?>
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
// Clear the session error after rendering the page
unset($_SESSION['error']);
?>