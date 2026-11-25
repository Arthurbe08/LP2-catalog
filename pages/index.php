
<?php

session_start();

require_once "connection.php";

// FETCH MAPS

$maps = [];

$sql = "
    SELECT
        m.id_map,
        m.name,
        m.description,
        m.author,
        m.rating,

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

    ORDER BY m.id_map DESC
";

$result = $connection->query($sql);

if (!$result) {
    die("Could not retrieve maps: " . $connection->error);
}

while ($row = $result->fetch_assoc()) {

    $mapId = $row["id_map"];

    // Create the map entry if it doesn't exist yet.

    if (!isset($maps[$mapId])) {

        $maps[$mapId] = [
            "id_map" => $mapId,
            "username" => $row["username"],
            "name" => $row["name"],
            "description" => $row["description"],
            "author" => $row["author"],
            "rating" => $row["rating"],
            "collectables" => []
        ];

    }

    // Add collectables belonging to this map.

    if ($row["id_collectable"] !== null) {

        $maps[$mapId]["collectables"][] = [
            "name" => $row["collectable_name"],
            "image" => $row["image"],
            "current_amount" => $row["current_amount"],
            "max_amount" => $row["max_amount"]
        ];

    }

}

// Convert associative array into a regular indexed array.

$maps = array_values($maps);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/index.css">

    <script src="../script/index.js" defer></script>

    <title>Golden Catalog</title>
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


    <main class="index-main">

        <div class="catalog-top">

            <div>
                <h2>Community Achievements</h2>
                <p>Explore the maps conquered by our community.</p>
            </div>

            <a href="addMap.php" class="add-map">
                + Add Map
            </a>

        </div>

        <div class="catalog-toolbar">

            <input
                type="text"
                id="username-search"
                placeholder="Search by username..."
                autocomplete="off"
            >

        </div>

        <section id="map-list" class="map-grid">
            <!-- Achievement cards will be inserted here by JavaScript. -->
        </section>

    </main>


    <!-- Send PHP database data to JavaScript. -->

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
