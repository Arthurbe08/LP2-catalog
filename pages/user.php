
<?php

require_once "auth.php";
require_once "connection.php";

// FETCH USER MAPS

$maps = [];

$userId = (int) $_SESSION["user_id"];

$sql = "
    SELECT
        m.id_map,
        m.name,
        m.description,
        m.author,
        m.rating,
        m.duration,

        u.username,

        c.id_collectable,
        c.name AS collectable_name,
        c.image,
        c.current_amount,
        c.max_amount

    FROM maps m

    INNER JOIN users u
        ON m.id_user = u.id_user

    LEFT JOIN collectables c
        ON m.id_map = c.id_map

    WHERE m.id_user = ?

    ORDER BY m.id_map DESC
";

$stmt = $connection->prepare($sql);

if (!$stmt) {
    die("Could not prepare map query: " . $connection->error);
}

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $mapId = $row["id_map"];

    // Create map entry if it doesn't exist.

    if (!isset($maps[$mapId])) {

        $maps[$mapId] = [
            "id_map" => $mapId,
            "username" => $row["username"],
            "name" => $row["name"],
            "description" => $row["description"],
            "author" => $row["author"],
            "rating" => $row["rating"],
            "duration" => $row["duration"],
            "collectables" => []
        ];

    }

    // Add collectables.

    if ($row["id_collectable"] !== null) {

        $maps[$mapId]["collectables"][] = [
            "name" => $row["collectable_name"],
            "image" => $row["image"],
            "current_amount" => $row["current_amount"],
            "max_amount" => $row["max_amount"]
        ];

    }

}

$stmt->close();

// Convert associative array into a regular indexed array.

$maps = array_values($maps);

$mapCount = count($maps);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/index.css">
    <link rel="stylesheet" href="../style/user.css">

    <script src="../script/user.js" defer></script>

    <title>My Profile - Golden Catalog</title>
</head>

<body>

    <!-- HEADER -->

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

            <div class="nav-user">

                <a href="user.php" class="nav-profile">
                    My Profile
                </a>

                <a href="logout.php" class="nav-logout">
                    Log-out
                </a>

            </div>

        </nav>

    </header>


    <!-- MAIN -->

    <main class="user-main">

        <!-- PROFILE OVERVIEW -->

        <section class="profile-section">

            <div class="profile-picture">
                <span>?</span>
            </div>

            <div class="profile-info">

                <div class="profile-identity">

                    <p class="profile-label">PLAYER PROFILE</p>

                    <h2>
                        <?= htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8") ?>
                    </h2>

                </div>

                <div class="profile-stats">

                    <div class="profile-stat">

                        <span class="stat-number">
                            <?= $mapCount ?>
                        </span>

                        <span class="stat-label">
                            Maps registered
                        </span>

                    </div>

                </div>

            </div>

        </section>


        <!-- PERSONAL MAP CATALOG -->

        <section class="user-catalog">

            <div class="user-catalog-header">

                <div>
                    <h2>My Maps</h2>
                    <p>Your personal collection of achievements.</p>
                </div>

            </div>


            <div class="user-catalog-toolbar">

                <input
                    type="text"
                    id="map-search"
                    placeholder="Search by map name..."
                    autocomplete="off"
                >

            </div>


            <div id="user-map-list" class="user-map-grid map-grid">

                <!-- Maps will be inserted here by JavaScript. -->

            </div>

        </section>

    </main>


    <!-- SEND DATABASE DATA TO JAVASCRIPT -->

    <script>
        const maps = <?= json_encode(
            $maps,
            JSON_HEX_TAG |
            JSON_HEX_APOS |
            JSON_HEX_QUOT |
            JSON_HEX_AMP
        ) ?>;
    </script>

</body>
</html>
