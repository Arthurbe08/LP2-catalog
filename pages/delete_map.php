<?php
session_start();
require_once "connection.php";

// Only logged-in users can delete maps
if (!isset($_SESSION["user_id"])) {
    header("Location: sign_in.php");
    exit;
}

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$mapId = filter_input(INPUT_POST, "id_map", FILTER_VALIDATE_INT);

if (!$mapId) {
    die("Invalid map ID.");
}

// Delete only maps belonging to the logged-in user
$stmt = $connection->prepare("
    DELETE FROM maps
    WHERE id_map = ?
    AND id_user = ?
");

$stmt->bind_param("ii", $mapId, $_SESSION["user_id"]);
$stmt->execute();

// Check whether a map was actually deleted
if ($stmt->affected_rows === 0) {
    $stmt->close();
    die("Map not found or you do not have permission to delete it.");
}

$stmt->close();

// Return to the user's profile
header("Location: user.php");
exit;
?>