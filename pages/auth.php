<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: sign_in.php");
    exit;
}

?>