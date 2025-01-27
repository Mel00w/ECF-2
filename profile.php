<?php
session_start();
include_once './connexion.php';
$req = $bdd->query('SELECT post.`id_post`, post.`date_published`, post.`title`, post.`under_title`, post.`intro`, post.`contain`, post.`pic`, post.`id_user`, user.`id_user`, user.`username` , `user`.`profile_picture`, `user`.`password`, `user`.`email` FROM `post` INNER JOIN `user` ON post.`id_user` = user.`id_user`;');
$user = $bdd->prepare('SELECT `id_user`, `username`, `profile_picture`, `email`, `password` FROM `user` WHERE `id_user`= :id;');
$user->bindParam("id", $_SESSION['id_user'], PDO::PARAM_INT);
$user->execute();
$user = $user->fetch(PDO::FETCH_ASSOC);
// Vérifier si un utilisateur est connecté
$isConnected = isset($_SESSION['id_user']); // Vérifie si l'ID utilisateur est stocké dans la session
$username = $isConnected ? $_SESSION['username'] : null; // Récupère le nom d'utilisateur de la session s'il est connecté

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <meta name="description" content="SkyDiary est une plateforme de blog en ligne.">
    <title>SkyDiary | <?= $username; ?>'s profile </title>
    <link rel="icon" href="./img/Logo.svg">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>

    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid container mt-4">
                <a class="navbar-brand" href="./index.php">
                    <img class="montserrat-semi-bold" src="./img/Logo.svg" alt="">SkyDiary
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <?php if ($isConnected): ?>
                            <li class="nav-item">
                                <a class="nav-link active" href="./profile.php">
                                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Photo de profil" style="width: 30px; height: 30px; border-radius: 50%;">
                                    <?= htmlspecialchars($username) ?>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link active" href="./login.php">Login/Register</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="./index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="./publish.php">Publish</a></li>
                        <?php if ($isConnected): ?>
                            <li class="nav-item"><a class="nav-link" href="./disconnected.php">Disconnect</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="container mt-4">
        <h2 class="pagination justify-content-center source-serif-4">Modify My Profile 😊 </h2>
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3 ">
                <label for="profile_picture" class="form-label">Profile Picture</label>
                <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Photo de profil" class="mb-4">
                <input type="file" class="form-control" id="profile_picture" name="profile_picture">
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>

    </main>
    <?php include "./footer.php" ?>