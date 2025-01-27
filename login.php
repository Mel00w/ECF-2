<?php
session_start();
include_once './connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $stmt = $bdd->prepare('SELECT `id_user`, `username`, `profile_picture`, `password` FROM `user` WHERE `email` = :email');
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['profile_picture'] = $user['profile_picture'];

        header('Location: index.php');
        exit();
    } else {
        $error = 'Email ou mot de passe incorrect.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <meta name="description" content="SkyDiary est une plateforme de blog en ligne.">
    <title>SkyDiary | LogIn </title>
    <link rel="icon" href="./img/Logo.svg">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>

    <header class="navbar navbar-expand-lg bg-body-tertiary" >
        <nav class="navbar navbar-expand-lg bg-body-tertiary container mt-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="./index.php"><img src="./img/Logo.svg" alt="">SkyDiary</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="./login.php">Login/Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./publish.php">Publish</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./disconnected.php">Disconnected</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="container mt-4 mb-5">
        <h1 class="mb-3">Connection</h1>
        <form action="login.php" method="POST">
            <label class="mb-2" for="email">Email :</label>
            <input type="email" id="email" name="email" required> 
            <br>

            <label for="password">Password :</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Log in</button>
        </form>

        <?php if (isset($error)) : ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <p>Not registered yet? <a href="register.php">Create an account</a></p>
    </main>
    <?php include "./footer.php" ?>