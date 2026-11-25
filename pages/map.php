
<?php
session_start();
require_once "connection.php";

// GET MAP ID

$mapId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$mapId) {
    die("Invalid map ID.");
}

// HELPER FUNCTION

function escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// FETCH MAP INFORMATION

$mapQuery = $connection->prepare("
    SELECT
        maps.id_map,
        maps.id_user,
        maps.name,
        maps.description,
        maps.author,
        maps.rating,
        maps.duration,
        users.username
    FROM maps
    LEFT JOIN users ON maps.id_user = users.id_user
    WHERE maps.id_map = ?
");

$mapQuery->bind_param("i", $mapId);
$mapQuery->execute();

$mapResult = $mapQuery->get_result();
$map = $mapResult->fetch_assoc();

$mapQuery->close();

// Stop if the map doesn't exist
if (!$map) {
    die("Map not found.");
}

// FETCH COLLECTABLES

$collectables = [];

$collectableQuery = $connection->prepare("
    SELECT
        id_collectable,
        name,
        image,
        current_amount,
        max_amount
    FROM collectables
    WHERE id_map = ?
    ORDER BY id_collectable ASC
");

$collectableQuery->bind_param("i", $mapId);
$collectableQuery->execute();

$collectableResult = $collectableQuery->get_result();

while ($collectable = $collectableResult->fetch_assoc()) {
    $collectables[] = $collectable;
}

$collectableQuery->close();

// FETCH CHALLENGES

$challenges = [];

$challengeQuery = $connection->prepare("
    SELECT
        id_challenge,
        name,
        difficulty,
        completion,
        deaths,
        description,
        main_challenge
    FROM challenges
    WHERE id_map = ?
    ORDER BY main_challenge DESC, id_challenge ASC
");

$challengeQuery->bind_param("i", $mapId);
$challengeQuery->execute();

$challengeResult = $challengeQuery->get_result();

while ($challenge = $challengeResult->fetch_assoc()) {
    $challenges[] = $challenge;
}

$challengeQuery->close();

// BACK LINK

$fromUser = ($_GET["from"] ?? "") === "user";

$backUrl = $fromUser ? "user.php" : "index.php";
$backLabel = $fromUser ? "Back to my maps" : "Back to catalog";

// FORMATTED VALUES

$duration = (int)$map["duration"];

$hours = intdiv($duration, 60);
$minutes = $duration % 60;

if ($hours > 0) {
    $formattedDuration = $hours . "h " . $minutes . "min";
} else {
    $formattedDuration = $minutes . " min";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= escape($map["name"]) ?> - Golden Catalog</title>

    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/index.css">
    <link rel="stylesheet" href="../style/map.css">
</head>

<body>

    <!--NAVBAR-->

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

        <!--MAP OVERVIEW-->

        <section class="map-overview">

            <a href="<?= escape($backUrl) ?>" >
                <button class="cancel-button">
                    ← <?= escape($backLabel) ?>
                </button>
            </a>

            <div class="map-title">

                <p class="map-username">
                    Added by <?= escape($map["username"] ?? "Unknown user") ?>
                </p>

                <h2><?= escape($map["name"]) ?></h2>

            </div>

            <p class="map-overview-description">
                <?= nl2br(escape($map["description"])) ?>
            </p>

            <div class="map-overview-info">

                <div>
                    <strong>Author</strong>
                    <span><?= escape($map["author"] ?: "Unknown") ?></span>
                </div>

                <div>
                    <strong>Personal rating</strong>
                    <span>
                        <?= $map["rating"] !== null
                            ? escape($map["rating"]) . "/10"
                            : "Not rated" ?>
                    </span>
                </div>

                <div>
                    <strong>Duration</strong>
                    <span><?= escape($formattedDuration) ?></span>
                </div>

            </div>

        </section>


        <!--COLLECTABLES-->

        <section class="map-section">

            <h3>Collectables</h3>

            <?php if (count($collectables) > 0): ?>

                <div class="collectable-grid">

                    <?php foreach ($collectables as $collectable): ?>

                        <?php
                        $current = (int)$collectable["current_amount"];
                        $max = (int)$collectable["max_amount"];

                        $percentage = $max > 0
                            ? min(100, max(0, ($current / $max) * 100))
                            : 0;
                        ?>

                        <article class="collectable-card">

                            <?php if (!empty($collectable["image"])): ?>

                                <img
                                    src="<?= escape($collectable["image"]) ?>"
                                    alt="<?= escape($collectable["name"]) ?>"
                                    class="collectable-image"
                                >

                            <?php else: ?>

                                <div class="collectable-placeholder">
                                    ?
                                </div>

                            <?php endif; ?>

                            <div class="collectable-info">

                                <h4><?= escape($collectable["name"]) ?></h4>

                                <p class="collectable-progress">
                                    <?= $current ?> / <?= $max ?>
                                </p>

                                <div class="progress-bar">
                                    <div
                                        class="progress-fill"
                                        style="width: <?= $percentage ?>%;"
                                    ></div>
                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <p>No collectables registered for this map.</p>

            <?php endif; ?>

        </section>


        <!--CHALLENGES-->

        <section class="map-section">

            <h3>Challenges</h3>

            <?php if (count($challenges) > 0): ?>

                <div class="challenge-grid">

                    <?php foreach ($challenges as $challenge): ?>

                        <?php
                        $completed = (bool)$challenge["completion"];
                        $isMain = (bool)$challenge["main_challenge"];
                        ?>

                        <article class="challenge-card">

                            <div class="challenge-heading">

                                <div>

                                    <h4>
                                        <?= escape($challenge["name"]) ?>
                                    </h4>

                                    <?php if ($isMain): ?>

                                        <span class="main-challenge-badge">
                                            Main challenge
                                        </span>

                                    <?php endif; ?>

                                </div>

                                <span class="challenge-status <?= $completed ? "completed" : "incomplete" ?>">

                                    <?= $completed ? "Completed" : "Incomplete" ?>

                                </span>

                            </div>


                            <?php if (!empty($challenge["description"])): ?>

                                <p class="challenge-description">
                                    <?= nl2br(escape($challenge["description"])) ?>
                                </p>

                            <?php endif; ?>


                            <div class="challenge-statistics">

                                <span>
                                    Difficulty:
                                    <strong>
                                        <?= escape($challenge["difficulty"]) ?>
                                    </strong>
                                </span>

                                <span>
                                    Deaths:
                                    <strong>
                                        <?= escape($challenge["deaths"]) ?>
                                    </strong>
                                </span>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <p>No challenges registered for this map.</p>

            <?php endif; ?>

        </section>


        <!-- =============================
             RETURN BUTTON
        ============================== -->
        <div class="return-btns">
            <a  href="<?= escape($backUrl) ?>" >
                <button class="cancel-button">
                    ← <?= escape($backLabel) ?>
                </button>
            </a>
            
            <?php 
            //DELETE BUTTON
            if (
                isset($_SESSION["user_id"]) &&
                $_SESSION["user_id"] == $map["id_user"]
            ): 
            ?>
                <form action="delete_map.php" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this map? This action cannot be undone.');">

                    <input type="hidden" name="id_map" value="<?= escape($mapId) ?>">

                    <button type="submit" class="cancel-button">
                        Delete map
                    </button>

                </form>

            <?php endif; ?>
        </div>
   
    </main>

</body>
</html>
