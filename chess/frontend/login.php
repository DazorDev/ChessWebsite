<!DOCTYPE html>
<html>
<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
}
?>

<head>
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <form class="login-form" action="process_login.php" method="POST">
            <h1>Login</h1>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <p class="register-link">Don't have an account? <a href="register.php">Register</a></p>
        </form>
    </div>
</body>

</html>