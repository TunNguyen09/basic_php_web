<?php
    session_start();
    unset($_SESSION['myapp_S_ID']);

    header("Location: login.php");
    die;
?>