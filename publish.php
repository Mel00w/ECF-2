<?php
session_start();
include_once './connexion.php';

// Vérifier si un utilisateur est connecté
$isConnected = isset($_SESSION['id_user']); // Vérifie si l'ID utilisateur est stocké dans la session
$username = $isConnected ? $_SESSION['username'] : null; // Récupère le nom d'utilisateur de la session s'il est connecté

// Récupérer les informations de l'utilisateur connecté
if ($isConnected) {
    $id_user = $_SESSION['id_user']; // Récupérer l'ID utilisateur de la session
    $stmt = $bdd->prepare('SELECT `id_user`, `username`, `profile_picture`, `email`, `password` FROM `user` WHERE `id_user` = ?');
    $stmt->execute([$id_user]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Requête pour récupérer les posts et les informations des utilisateurs associés
$req = $bdd->query('SELECT post.`id_post`, post.`date_published`, post.`title`, post.`under_title`, post.`intro`, post.`contain`, post.`pic`, post.`id_user`, user.`id_user`, user.`username` , `user`.`profile_picture`, `user`.`password`, `user`.`email` FROM `post` INNER JOIN `user` ON post.`id_user` = user.`id_user`;');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <meta name="description" content="SkyDiary est une plateforme de blog en ligne.">
    <title>SkyDiary | Publish </title>
    <link rel="icon" href="./img/Logo.svg">
    <link rel="stylesheet" href="./css/style.css">
    <meta name="description" content="SkyDiary est une plateforme de blog en ligne.">
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
    <main class="container mt-5">
        <section>
            <?php if ($isConnected): ?>
                <h2 class="pagination justify-content-center source-serif-4">Publish your article</h2>
                <form action="submit_article.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="under_title" class="form-label">Under Title</label>
                        <input type="text" class="form-control" id="under_title" name="under_title" required>
                    </div>
                    <div class="mb-3">
                        <label for="intro" class="form-label">Introduction</label>
                        <textarea class="form-control" id="intro" name="intro" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="contain" class="form-label">Content</label>
                        <textarea class="form-control" id="contain" name="contain" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="pic" class="form-label">Picture</label>
                        <input type="file" id="pic" name="pic" accept=".webp" required class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Publish</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">Cancel</button>
                </form>

        </section>
    </main>

<?php else: ?>
    <p class="text-center">Please <a href="./login.php">login</a> to publish an article.</p>
<?php endif; ?>
<?php include "./footer.php" ?>