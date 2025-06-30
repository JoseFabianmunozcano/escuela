<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: views/login.php");
    exit;
}
?>
