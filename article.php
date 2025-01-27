<?php
session_start();
include_once './connexion.php';

// Récupère l'ID de l'utilisateur connecté (s'il existe) depuis la session
$id_user = $_SESSION['id_user'] ?? null;

// Vérifie si un utilisateur est connecté
$isConnected = isset($_SESSION['id_user']);

// Récupère le nom d'utilisateur s'il est connecté
$username = $isConnected ? $_SESSION['username'] : null;

// Requête préparée pour récupérer les informations de l'utilisateur connecté
$stmt = $bdd->prepare('SELECT `id_user`, `username`, `profile_picture`, `email`, `password` FROM `user` WHERE `id_user` = :id_user');
$stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC); // Récupère les données sous forme de tableau associatif

// Vérifie si un ID d'article est passé dans l'URL et qu'il est valide
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id']; // Cast pour s'assurer qu'il s'agit d'un entier

    // Prépare et exécute la requête pour récupérer les détails de l'article
    $req = $bdd->prepare('SELECT post.`id_post`, post.`date_published`, post.`title`, post.`under_title`, post.`intro`, post.`contain`, post.`pic`, post.`id_user`, user.`id_user`, user.`username`, user.`profile_picture` FROM `post`INNER JOIN `user` ON post.`id_user` = user.`id_user`WHERE post.`id_post` = :id');
    $req->bindValue(':id', $id, PDO::PARAM_INT);
    $req->execute();

    // Récupère les données de l'article
    $article = $req->fetch(PDO::FETCH_ASSOC);

    // Si l'article n'existe pas, afficher un message et arrêter l'exécution
    if (!$article) {
        echo "Article introuvable.";
        exit;
    }
} else {
    // Si l'ID est invalide ou absent, afficher un message et arrêter l'exécution
    echo "ID invalide.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyDiary | <?= htmlspecialchars($article['title']) ?> </title>
    <link rel="icon" href="./img/Logo.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css">
    <meta name="description" content="SkyDiary est une plateforme de blog en ligne.">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid container mt-4">
                <a class="navbar-brand" href="./index.php"><img src="./img/Logo.svg" alt="Logo">SkyDiary</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <?php if ($isConnected): ?>
                            <li class="nav-item">
                                <!-- Affiche le profil de l'utilisateur connecté -->
                                <a class="nav-link active" href="./profile.php">
                                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" style="width: 30px; height: 30px; border-radius: 50%;">
                                    <?= htmlspecialchars($username) ?>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <!-- Lien vers la page de connexion -->
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
        <article>
            <h1 class="pagination justify-content-center source-serif-4"><?= htmlspecialchars($article['title']) ?></h1>
            <figure class="pagination justify-content-center mb-4 mt-4 "><img src="<?= htmlspecialchars($article['pic']) ?>" alt="Photo de l'article" class="img-fluid rounded mx-auto d-block img-fluid-custom"></figure>
            <p class="montserrat fst-italic"><?= htmlspecialchars($article['intro']) ?></p>
            <h3 class="montserrat-semi-bold pagination justify-content-center mb-4 mt-4"><?= htmlspecialchars($article['under_title']) ?></h3>
            <p class="montserrat mb-4"><?= htmlspecialchars($article['contain']) ?></p>
            <div class="d-flex align-items-center">
                <a href="profile_article.php?id=<?= htmlspecialchars($article['id_user']) ?>"><img class="mr-1" src="<?= htmlspecialchars($article['profile_picture']) ?>" alt="Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;"></a>
                <a href="profile_article.php?id=<?= htmlspecialchars($article['id_user']) ?>">
                    <h2 class="poppins-regular profile-pic-name me-2 "><?= htmlspecialchars($article['username']) ?></h2>
                </a>
                <p class="fst-italic">
                published on <?= htmlspecialchars($article['date_published']) ?></p>
            </div>


            <!-- Affiche les boutons d'édition et de suppression si l'utilisateur est l'auteur de l'article -->
            <?php if ($id_user === $article['id_user']): ?>
                <a href="edit_article.php?id=<?= $article['id_post'] ?>" class="btn btn-primary">Modifier l'article</a>
                <a href="delete_article.php?id=<?= $article['id_post'] ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">Supprimer l'article</a>
            <?php endif; ?>
        </article>
    </main>
    <?php include "./footer.php" ?>