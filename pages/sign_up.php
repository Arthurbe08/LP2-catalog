
<?php

session_start();

require_once "connection.php";

if (isset($_SESSION["user_id"])) {
    header("Location: user.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];


    // Check whether passwords match

    if ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        // Check whether username already exists

        $sql = "SELECT id_user FROM users WHERE username = ?";

        $stmt = $connection->prepare($sql);

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows > 0) {

            $error = "Username already exists.";

        } else {

            // Hash password

            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            // Insert user

            $sql = "INSERT INTO users (username, password)
                    VALUES (?, ?)";

            $stmt = $connection->prepare($sql);

            $stmt->bind_param(
                "ss",
                $username,
                $password_hash
            );


            if ($stmt->execute()) {
                header("Location: sign_in.php");
                exit;
            } else {
                $error = "Something went wrong while creating your account.";
            }

        }

        $stmt->close();

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="../style/sup.css">
    <link rel="stylesheet" href="../style/main.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Golden Catalog - Sign Up</title>
</head>

<body>

    <main class="sup-separator">

        <section class="sup-intro">

            <img
                src="../images/golden-berry.png"
                alt="Golden Strawberry"
                class="sup-berry"
            >

            <div class="sup-intro-text">

                <p class="small-title">Golden Catalog</p>

                <p>Want to show off to your friends?</p>

                <p>
                    A slightly more personal catalog
                    for your achievements.
                </p>

            </div>

            <img
                src="../images/strawberry.png"
                alt=""
                class="sup-strawberry"
            >

        </section>


        <section class="sup-content">

            <div class="sup-form-container">

                <h1>Sign-up</h1>
                <p>It's annoying, but it is needed.</p>

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
                        maxlength="255"
                        required
                    >

                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        required
                    >

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Confirm Password"
                        required
                    >

                    <button type="submit">
                        Create Account
                    </button>

                </form>

                <p class="sup-redirect">
                    Already have an account?
                    <a href="sign_in.php">Sign-in</a>
                </p>

            </div>

        </section>

    </main>

</body>
</html>
