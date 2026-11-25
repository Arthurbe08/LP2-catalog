
<?php

session_start();

require_once "connection.php";

// Already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: user.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $sql = "SELECT id_user, username, password
            FROM users
            WHERE username = ?";

    $stmt = $connection->prepare($sql);

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id_user"];
            $_SESSION["username"] = $user["username"];

            header("Location: index.php");
            exit;

        }

    }

    // Same message for invalid username or password
    $error = "Invalid username or password.";

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/sin.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Catalog - Sign In</title>
</head>

<body>

    <main class="sup-separator">

        <section class="sup-intro">

            <img
                src="../images/golden-berry.png"
                class="sup-berry"
                alt="Golden Berry"
            >

            <div class="sup-intro-text">
                <h1>Log-in</h1>
                <p>Just making sure you are you.</p>
            </div>

        </section>


        <section class="sup-form-container">

            <h1>Sign-in</h1>
            <p>Welcome back!</p>

            <?php if ($error !== ""): ?>
                <p class="form-error">
                    <?= htmlspecialchars($error) ?>
                </p>
            <?php endif; ?>

            <form action="" method="POST" class="sup-form">

                <input
                    type="text"
                    name="username"
                    placeholder="Username"
                    required
                >

                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                >

                <button type="submit">
                    Log In
                </button>

            </form>

            <p class="sup-redirect">
                Don't have an account yet?
                <a href="sign_up.php">Sign-up</a>
            </p>

        </section>

    </main>

</body>
</html>
