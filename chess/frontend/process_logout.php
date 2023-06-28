<?php
session_start();
echo session_destroy();
header("Location: login.php");
