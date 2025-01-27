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
    $req = $bdd->prepare('SELECT post.`id_post`, post.`date_published`, post.`title`, post.`under_title`, post.`intro`, post.`contain`, post.`pic`, post.`id_user`, user.`id_user`, user.`username`, user.`profile_picture` FROM `post`INNER JOIN `user` ON post.`id_user` = user.`id_user` WHERE post.`id_post` = :id');
    $req->bindValue(':id', $id, PDO::PARAM_INT);
    $req->execute();

    // Récupère les données de l'article
    $article = $req->fetch(PDO::FETCH_ASSOC);

    // Si l'article n'existe pas, afficher un message et arrêter l'exécution
    if (!$article) {
        echo "Article introuvable.";
        exit;
    }

    // Gestion du formulaire de modification
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $under_title = $_POST['under_title'];
        $intro = $_POST['intro'];
        $contain = $_POST['contain'];

        // Gestion de l'image (si une nouvelle image est ajoutée)
        if (!empty($_FILES['pic']['name'])) {
            $pic = './uploads/' . basename($_FILES['pic']['name']);
            move_uploaded_file($_FILES['pic']['tmp_name'], $pic);
        } else {
            $pic = $article['pic']; // Utilise l'image existante si aucune nouvelle image n'est ajoutée
        }

        // Mise à jour de l'article dans la base de données
        $update = $bdd->prepare('UPDATE `post` SET `title` = :title, `under_title` = :under_title, `intro` = :intro, `contain` = :contain, `pic` = :pic WHERE `id_post` = :id');
        $update->bindValue(':title', $title, PDO::PARAM_STR);
        $update->bindValue(':under_title', $under_title, PDO::PARAM_STR);
        $update->bindValue(':intro', $intro, PDO::PARAM_STR);
        $update->bindValue(':contain', $contain, PDO::PARAM_STR);
        $update->bindValue(':pic', $pic, PDO::PARAM_STR);
        $update->bindValue(':id', $id, PDO::PARAM_INT);

        if ($update->execute()) {
            echo "Article mis à jour avec succès !";
            header('Location: article.php?id=' . $id);
            exit;
        } else {
            echo "Erreur lors de la mise à jour de l'article.";
        }
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
                <a class="navbar-brand" href="./index.php">
                    <img src="./img/Logo.svg" alt="Logo">SkyDiary
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <?php if ($isConnected): ?>
                            <li class="nav-item">
                                <a class="nav-link active" href="./profile.php">
                                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" style="width: 30px; height: 30px; border-radius: 50%;">
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

    <main>
        <section class="container mt-4">
            <h2>Modifier l'article</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($article['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="under_title" class="form-label">Under Title</label>
                    <input type="text" class="form-control" id="under_title" name="under_title" value="<?= htmlspecialchars($article['under_title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="intro" class="form-label">Introduction</label>
                    <textarea class="form-control" id="intro" name="intro" rows="3" required><?= htmlspecialchars($article['intro']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="contain" class="form-label">Content</label>
                    <textarea class="form-control" id="contain" name="contain" rows="5" required><?= htmlspecialchars($article['contain']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="pic" class="form-label">Picture</label>
                    <input type="file" class="form-control" id="pic" name="pic">
                    <img src="<?= htmlspecialchars($article['pic']) ?>" alt="Image actuelle" class="img-thumbnail mt-2" style="max-width: 200px;">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">Cancel</button>
            </form>
        </section>
    </main>
    <?php include "./footer.php" ?>