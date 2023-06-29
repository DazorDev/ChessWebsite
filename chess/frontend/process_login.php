<?php
require "../backend/connect.php";

function tryLogin($username, $password) {
    $db = readUserDB($username);
    if (mysqli_num_rows($db) != 1) return;
    $data = mysqli_fetch_array($db);
    if (!password_verify($password, $data['password'])) return;
    $_SESSION["username"] = $data["username"];
    $_SESSION["user"]     = $data["id"];
}

function readUserDB($username) {
    global $connection;
    $query =
        "Select *
        from user
        where username = '$username'";
    return mysqli_query($connection, $query);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    session_start();
    $username = $_POST["username"];
    $password = $_POST["password"];
    tryLogin($username, $password);
}

echo $_SESSION["username"];


if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    return;
}
header("Location: dashboard.php");
