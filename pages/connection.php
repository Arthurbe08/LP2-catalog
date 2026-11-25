<?php

$connection = new mysqli(
    "127.0.0.1",
    "root",
    "",
    "LP2-catalog",
    3306
);

if ($connection->connect_error) {
    die("Database connection failed: " . $connection->connect_error);
}

$connection->set_charset("utf8mb4");

?>