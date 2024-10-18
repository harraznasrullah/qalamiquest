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

    <!-- Login Form -->
    <div class="login-content">
        <div class="login-form">
            <h2>Log In</h2>
            <p>Log in is easy.</p>
            <form action="#" method="POST">
                <input type="email" id="email" name="email" placeholder="Email" required>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="create-account-link">
                <a href="signup.php">Create a new account</a>
            </div>
        </div>
    </div>

</body>

</html>