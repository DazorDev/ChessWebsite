<?php
require "../backend/connect.php";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate the form data (add your own validation rules)
    $errors = [];

    if (empty($username)) {
        $errors[] = 'Username is required.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    // If there are no validation errors, proceed with registration
    if (!empty($errors)) {
        header("Location: register.php");
        return;
    }
    registerUser($username, $password);
    header("Location: login.php");
}

function reqRegisterUser($user, $password) {
    $pswHash = password_hash($password, PASSWORD_DEFAULT);
    return "INSERT INTO user (username, password) VALUES ('$user', '$pswHash')";
}

function registerUser($user, $password) {
    global $connection;
    mysqli_query($connection, reqRegisterUser($user, $password));
    if (mysqli_error($connection)) {
        header("Location: register.php");
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Registration Page</title>
    <link rel="stylesheet" href="styles.css">
</head>


</html>