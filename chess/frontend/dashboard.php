<!DOCTYPE html>
<html>
<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
}
?>

<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="dashboard-container">
        <h2>Welcome, <?php echo $_SESSION["username"]; ?>!</h2>
        <h3>Activities</h3>
        <ul>
            <li><a href="play.php">Play Chess</a></li>
            <li><a href="process_logout.php">Log out</a></li>
            <!-- Add more activity links as needed -->
        </ul>
    </div>
</body>

</html>