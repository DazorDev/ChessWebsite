<!DOCTYPE html>
<html>
<?php
session_start();
if (!isset($_SESSION["username"])) {
    header('Location: login.php');
}
?>
<script type='text/javascript'>
    const user = "<?php echo $_SESSION['user'] ?>";
</script>

<head>
    <title>Play Chess</title>
    <link rel="stylesheet" href="styles.css">
    <script src="chess_but_not_a_clusterfuck.js"></script>
</head>

<body>
    <div class="play-container">
        <h2>Play Chess</h2>
        <div class="chessboard">
            <?php
            function generateSquare($attribute, $x, $y) {
                echo "<div class =\"square $attribute\" id=\"$x $y\"></div>";
            }
            function generateChessBoard() {
                for ($y = 0; $y < 8; $y++) {
                    for ($x = 0; $x < 8; $x++) {
                        if ($x % 2 == 0) {
                            generateSquare($y % 2 == 0 ? "odd" : "even", $x, $y);
                            continue;
                        }
                        generateSquare($y % 2 == 0 ? "even" : "odd", $x, $y);
                    }
                }
            }
            generateChessBoard();
            ?>
        </div>
    </div>
</body>

</html>