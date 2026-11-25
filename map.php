
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/index.css">
    <link rel="stylesheet" href="../style/map.css">

    <title>Map Details - Golden Catalog</title>
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


    <!-- MAIN -->
    <main class="map-main">

        <!-- MAP TITLE -->
        <section class="map-title">
            <p class="map-label">MAP DETAILS</p>
            <h2>Farewell</h2>
        </section>


        <!-- MAP OVERVIEW -->
        <section class="map-overview">

            <div class="map-overview-description">
                <h3>Description</h3>

                <p>
                    A challenging journey through the final chapter
                    of Celeste, featuring difficult platforming
                    sections and demanding mechanics.
                </p>
            </div>

            <div class="map-overview-info">

                <div class="overview-item">
                    <span class="overview-label">Author</span>
                    <span class="overview-value">Maddy Thorson</span>
                </div>

                <div class="overview-item">
                    <span class="overview-label">Personal rating</span>
                    <span class="overview-value">9.5 / 10</span>
                </div>

                <div class="overview-item">
                    <span class="overview-label">Duration</span>
                    <span class="overview-value">120 minutes</span>
                </div>

            </div>

        </section>


        <!-- COLLECTABLES -->
        <section class="map-section">

            <div class="map-section-header">
                <div>
                    <h2>Collectables</h2>
                    <p>Track your progress through the map.</p>
                </div>
            </div>

            <div class="collectable-grid">

                <!-- Collectable 1 -->
                <article class="collectable-card">

                    <div class="collectable-image">
                        <img
                            src="../images/golden-berry.png"
                            alt="Golden Berry"
                        >
                    </div>

                    <div class="collectable-info">

                        <h3>Golden Berry</h3>

                        <p class="collectable-progress">
                            1 / 1
                        </p>

                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 100%"></div>
                        </div>

                    </div>

                </article>


                <!-- Collectable 2 -->
                <article class="collectable-card">

                    <div class="collectable-image">
                        <img
                            src="../images/golden-berry.png"
                            alt="Strawberries"
                        >
                    </div>

                    <div class="collectable-info">

                        <h3>Strawberries</h3>

                        <p class="collectable-progress">
                            12 / 15
                        </p>

                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 80%"></div>
                        </div>

                    </div>

                </article>


                <!-- Collectable without an image -->
                <article class="collectable-card">

                    <div class="collectable-image collectable-placeholder">
                        <span>?</span>
                    </div>

                    <div class="collectable-info">

                        <h3>Crystal Hearts</h3>

                        <p class="collectable-progress">
                            1 / 2
                        </p>

                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 50%"></div>
                        </div>

                    </div>

                </article>

            </div>

        </section>


        <!-- CHALLENGES -->
        <section class="map-section">

            <div class="map-section-header">
                <div>
                    <h2>Challenges</h2>
                    <p>Check your progress and individual challenge statistics.</p>
                </div>
            </div>


            <div class="challenge-grid">

                <!-- MAIN CHALLENGE -->
                <details class="challenge-card main-challenge">

                    <summary>

                        <div class="challenge-heading">
                            <span class="challenge-name">
                                Complete the Map
                            </span>

                            <span class="challenge-badge">
                                Main Challenge
                            </span>
                        </div>

                        <div class="challenge-quick-info">
                            <span class="challenge-difficulty">
                                Difficulty: 9.0
                            </span>

                            <span class="challenge-completion completed">
                                Completed
                            </span>
                        </div>

                        <span class="challenge-arrow">▼</span>

                    </summary>

                    <div class="challenge-expanded">

                        <p>
                            Complete all required sections of the map.
                        </p>

                        <div class="challenge-statistics">
                            <span>Deaths: 245</span>
                            <span>Difficulty: 9.0</span>
                        </div>

                    </div>

                </details>


                <!-- OPTIONAL CHALLENGE -->
                <details class="challenge-card">

                    <summary>

                        <div class="challenge-heading">
                            <span class="challenge-name">
                                Collect all Strawberries
                            </span>
                        </div>

                        <div class="challenge-quick-info">
                            <span class="challenge-difficulty">
                                Difficulty: 7.5
                            </span>

                            <span class="challenge-completion completed">
                                Completed
                            </span>
                        </div>

                        <span class="challenge-arrow">▼</span>

                    </summary>

                    <div class="challenge-expanded">

                        <p>
                            Find every strawberry hidden throughout the map.
                        </p>

                        <div class="challenge-statistics">
                            <span>Deaths: 83</span>
                            <span>Difficulty: 7.5</span>
                        </div>

                    </div>

                </details>


                <!-- OPTIONAL CHALLENGE -->
                <details class="challenge-card">

                    <summary>

                        <div class="challenge-heading">
                            <span class="challenge-name">
                                Golden Berry
                            </span>
                        </div>

                        <div class="challenge-quick-info">
                            <span class="challenge-difficulty">
                                Difficulty: 10.0
                            </span>

                            <span class="challenge-completion incomplete">
                                Incomplete
                            </span>
                        </div>

                        <span class="challenge-arrow">▼</span>

                    </summary>

                    <div class="challenge-expanded">

                        <p>
                            Complete the entire map without dying.
                        </p>

                        <div class="challenge-statistics">
                            <span>Deaths: 42</span>
                            <span>Difficulty: 10.0</span>
                        </div>

                    </div>

                </details>

            </div>

        </section>

    </main>

</body>
</html>
