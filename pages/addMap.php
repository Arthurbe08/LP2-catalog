
<?php
require_once "auth.php";
require_once "connection.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $author = trim($_POST["author"] ?? "");

    $ratingInput = $_POST["rating"] ?? "";
    $durationInput = $_POST["duration"] ?? "";

    $collectables = $_POST["collectables"] ?? [];
    $challenges = $_POST["challenges"] ?? [];
    $mainChallenge = $_POST["main_challenge"] ?? null;

    // ---------------- VALIDATION ----------------

    if ($name === "") {
        $error = "Map name is required.";
    } elseif (!is_array($collectables) || !is_array($challenges)) {
        $error = "Invalid form data.";
    } elseif (empty($challenges)) {
        $error = "At least one challenge is required.";
    } elseif ($mainChallenge === null || !isset($challenges[$mainChallenge])) {
        $error = "Please select a main challenge.";
    } elseif ($ratingInput !== "" && (!is_numeric($ratingInput) || $ratingInput < 0 || $ratingInput > 100)) {
        $error = "Rating must be between 0 and 100.";
    } elseif ($durationInput !== "" && (!is_numeric($durationInput) || $durationInput < 0)) {
        $error = "Duration must be a non-negative number.";
    }

    // ---------------- DATABASE INSERTION ----------------

    if ($error === "") {

        $connection->begin_transaction();

        $uploadedFiles = [];

        try {

            $userId = (int) $_SESSION["user_id"];

            $rating = $ratingInput === "" ? null : (float) $ratingInput;
            $duration = $durationInput === "" ? null : (int) $durationInput;

            // Insert map
            $stmt = $connection->prepare("
                INSERT INTO maps
                (id_user, name, description, rating, author, duration)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "issdsi",
                $userId,
                $name,
                $description,
                $rating,
                $author,
                $duration
            );

            $stmt->execute();

            $mapId = $connection->insert_id;

            $stmt->close();

            // ---------------- COLLECTABLES ----------------

            $collectableStmt = $connection->prepare("
                INSERT INTO collectables
                (id_map, current_amount, max_amount, image, name)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($collectables as $index => $collectable) {

                $collectableName = ($collectable["name"] ?? "");
                $currentAmount = (int) ($collectable["current_amount"] ?? 0);
                $maxAmount = (int) ($collectable["max_amount"] ?? 0);

                if ($collectableName === "") {
                    throw new Exception("A collectable name is required.");
                }

                if ($currentAmount < 0 || $maxAmount < 0 || $currentAmount > $maxAmount) {
                    throw new Exception("Invalid collectable amounts.");
                }

                $imagePath = null;

                // Handle optional image upload
                if (
                    isset($_FILES["collectables"]["error"][$index]["image"]) &&
                    $_FILES["collectables"]["error"][$index]["image"] !== UPLOAD_ERR_NO_FILE
                ) {

                    $uploadError = $_FILES["collectables"]["error"][$index]["image"];

                    if ($uploadError !== UPLOAD_ERR_OK) {
                        throw new Exception("Image upload failed.");
                    }

                    $tmpName = $_FILES["collectables"]["tmp_name"][$index]["image"];

                    // Validate image contents
                    $imageInfo = getimagesize($tmpName);

                    if ($imageInfo === false) {
                        throw new Exception("Uploaded file is not a valid image.");
                    }

                    $allowedTypes = [
                        IMAGETYPE_JPEG => "jpg",
                        IMAGETYPE_PNG => "png",
                        IMAGETYPE_WEBP => "webp"
                    ];

                    if (!isset($allowedTypes[$imageInfo[2]])) {
                        throw new Exception("Only JPG, PNG and WEBP images are allowed.");
                    }

                    $extension = $allowedTypes[$imageInfo[2]];

                    $fileName = bin2hex(random_bytes(16)) . "." . $extension;

                    $uploadDirectory = __DIR__ . "/../images/uploads/";

                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0755, true);
                    }

                    $destination = $uploadDirectory . $fileName;

                    if (!move_uploaded_file($tmpName, $destination)) {
                        throw new Exception("Could not save uploaded image.");
                    }

                    $uploadedFiles[] = $destination;

                    // Relative path stored in the database
                    $imagePath = "../images/uploads/" . $fileName;
                }

                $collectableStmt->bind_param(
                    "iiiss",
                    $mapId,
                    $currentAmount,
                    $maxAmount,
                    $imagePath,
                    $collectableName
                );

                $collectableStmt->execute();
            }

            $collectableStmt->close();

            // ---------------- CHALLENGES ----------------

            $challengeStmt = $connection->prepare("
                INSERT INTO challenges
                (id_map, difficulty, name, completion, deaths, description, main_challenge)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($challenges as $index => $challenge) {

                $challengeName = trim($challenge["name"] ?? "");

                $difficultyInput = $challenge["difficulty"] ?? "";
                $difficulty = $difficultyInput === "" ? null : (float) $difficultyInput;

                $completion = isset($challenge["completion"]) ? 1 : 0;
                $deaths = (int) ($challenge["deaths"] ?? 0);

                $challengeDescription = trim($challenge["description"] ?? "");

                $isMainChallenge = ((string) $index === (string) $mainChallenge) ? 1 : 0;

                if ($challengeName === "") {
                    throw new Exception("A challenge name is required.");
                }

                if ($deaths < 0) {
                    throw new Exception("Deaths cannot be negative.");
                }

                if ($difficulty !== null && $difficulty < 0) {
                    throw new Exception("Difficulty cannot be negative.");
                }

                $challengeStmt->bind_param(
                    "idsiisi",
                    $mapId,
                    $difficulty,
                    $challengeName,
                    $completion,
                    $deaths,
                    $challengeDescription,
                    $isMainChallenge
                );

                $challengeStmt->execute();
            }

            $challengeStmt->close();

            // ---------------- COMMIT ----------------

            $connection->commit();

            header("Location: user.php");
            exit;

        } catch (Throwable $e) {

            $connection->rollback();

            // Remove files if the database transaction fails
            foreach ($uploadedFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }

            error_log($e->getMessage());

            $error = "Something went wrong while saving the map.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/map.css">
    <link rel="stylesheet" href="../style/addMap.css">

    <script src="../script/addMap.js" defer></script>

    <title>Add Map - Golden Catalog</title>
</head>

<body>

    <header class="index-header">

        <a href="index.php" class="brand">

            <img
                src="../images/golden-berry.png"
                alt=""
                class="nav-berry"
            >

            <h1>Golden Catalog</h1>

        </a>

        <nav class="index-nav">

            <?php if (isset($_SESSION["user_id"])): ?>

                <div class="nav-user">

                    <a href="user.php" class="nav-profile">
                        My Profile
                    </a>

                    <a href="logout.php" class="nav-logout">
                        Log-out
                    </a>

                </div>

            <?php else: ?>

                <div class="nav-guest">

                    <a href="sign_in.php" class="nav-signin">
                        Sign-in
                    </a>

                    <a href="sign_up.php" class="nav-signup">
                        Sign-up
                    </a>

                </div>

            <?php endif; ?>

        </nav>

    </header>

<main class="map-main">

    <section class="map-title">
        <p class="map-label">COMMUNITY CATALOG</p>
        <h2>Add a Map</h2>
        <p class="add-map-subtitle">
            Register a new map and track your completion progress.
        </p>
    </section>
                
    <form
        id="map-form"
        action="addMap.php"
        method="POST"
        enctype="multipart/form-data"
    >

        <!-- MAP OVERVIEW -->

        <section class="map-overview add-map-overview">

            <div class="map-overview-description">

                <div class="form-field">
                    <label for="map-name">Map Name</label>

                    <input
                        type="text"
                        id="map-name"
                        name="name"
                        maxlength="255"
                        placeholder="Map name..."
                        required
                        value="<?= htmlspecialchars($_POST["name"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    >
                </div>

                <div class="form-field">
                    <label for="map-description">Description</label>

                    <textarea
                        id="map-description"
                        name="description"
                        maxlength="255"
                        rows="3"
                        placeholder="Describe the map..."
                    ><?= htmlspecialchars($_POST["description"] ?? "", ENT_QUOTES, "UTF-8") ?></textarea>
                </div>

            </div>

            <div class="map-overview-info">

                <div class="form-field">
                    <label for="map-author">Author</label>

                    <input
                        type="text"
                        id="map-author"
                        name="author"
                        maxlength="255"
                        placeholder="Map author..."
                        value="<?= htmlspecialchars($_POST["author"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    >
                </div>

                <div class="form-field">
                    <label for="map-rating">Personal Rating</label>

                    <input
                        type="number"
                        id="map-rating"
                        name="rating"
                        min="0"
                        max="10"
                        step="0.1"
                        placeholder="0 - 10"
                        value="<?= htmlspecialchars($_POST["rating"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    >
                </div>

                <div class="form-field">
                    <label for="map-duration">Duration (minutes)</label>

                    <input
                        type="number"
                        id="map-duration"
                        name="duration"
                        min="0"
                        step="1"
                        placeholder="Duration in minutes..."
                        value="<?= htmlspecialchars($_POST["duration"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    >
                </div>

            </div>

        </section>

        <!-- COLLECTABLES -->

        <section class="map-section">

            <div class="map-section-header add-section-header">
                <h3>Collectables</h3>

                <button
                    type="button"
                    id="add-collectable"
                    class="add-item-button"
                >
                    + Add Collectable
                </button>
            </div>

            <div id="collectable-list" class="collectable-grid"></div>

        </section>

        <!-- CHALLENGES -->

        <section class="map-section">

            <div class="map-section-header add-section-header">
                <h3>Challenges</h3>

                <button
                    type="button"
                    id="add-challenge"
                    class="add-item-button"
                >
                    + Add Challenge
                </button>
            </div>

            <div id="challenge-list" class="challenge-grid"></div>

        </section>

        <!-- ACTIONS -->

        <div class="form-actions">

            <a href="user.php" class="cancel-button">
                Cancel
            </a>

            <button type="submit" class="submit-map-button">
                Save Map
            </button>

        </div>

    </form>

</main>

<!-- COLLECTABLE TEMPLATE -->

<template id="collectable-template">

    <article class="collectable-card add-collectable-card">

        <button
            type="button"
            class="remove-item-button"
            aria-label="Remove collectable"
        >
            &times;
        </button>

        <div class="collectable-image collectable-placeholder">
            <span>?</span>
        </div>

        <div class="collectable-info">

            <div class="form-field">
                <label>Collectable Name</label>

                <input
                    type="text"
                    name="collectables[__INDEX__][name]"
                    maxlength="255"
                    placeholder="Collectable name..."
                    required
                >
            </div>

            <div class="form-field">
                <label>Image (optional)</label>

                <input
                    type="file"
                    name="collectables[__INDEX__][image]"
                    accept="image/*"
                >
            </div>

            <div class="collectable-amount-fields">

                <div class="form-field">
                    <label>Current</label>

                    <input
                        type="number"
                        name="collectables[__INDEX__][current_amount]"
                        min="0"
                        value="0"
                        required
                    >
                </div>

                <span>/</span>

                <div class="form-field">
                    <label>Maximum</label>

                    <input
                        type="number"
                        name="collectables[__INDEX__][max_amount]"
                        min="0"
                        value="1"
                        required
                    >
                </div>

            </div>

        </div>

    </article>

</template>

<!-- CHALLENGE TEMPLATE -->

<template id="challenge-template">

    <article class="challenge-card add-challenge-card">

        <button
            type="button"
            class="remove-item-button"
            aria-label="Remove challenge"
        >
            &times;
        </button>

        <div class="challenge-form-content">

            <div class="form-field">
                <label>Challenge Name</label>

                <input
                    type="text"
                    name="challenges[__INDEX__][name]"
                    maxlength="255"
                    placeholder="Challenge name..."
                    required
                >
            </div>

            <div class="challenge-form-row">

                <div class="form-field">
                    <label>Difficulty</label>

                    <input
                        type="number"
                        name="challenges[__INDEX__][difficulty]"
                        min="0"
                        step="0.1"
                        placeholder="0.0"
                    >
                </div>

                <div class="form-field">
                    <label>Deaths</label>

                    <input
                        type="number"
                        name="challenges[__INDEX__][deaths]"
                        min="0"
                        value="0"
                    >
                </div>

            </div>

            <div class="form-field">
                <label>Description</label>

                <textarea
                    name="challenges[__INDEX__][description]"
                    maxlength="1000"
                    rows="3"
                    placeholder="Describe the challenge..."
                ></textarea>
            </div>

            <div class="challenge-form-options">

                <label class="form-checkbox">
                    <input
                        type="checkbox"
                        name="challenges[__INDEX__][completion]"
                        value="1"
                    >
                    Completed
                </label>

                <label class="form-radio">
                    <input
                        type="radio"
                        name="main_challenge"
                        value="__INDEX__"
                    >
                    Main Challenge
                </label>

            </div>

        </div>

    </article>

</template>

</body>
</html>
