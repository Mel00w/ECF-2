<?php
session_start();
include_once './connexion.php';

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

// Vérifiez si l'ID de l'article est présent dans l'URL et est valide
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Prépare et exécute la requête pour récupérer l'article spécifique
    $req = $bdd->prepare('
        SELECT post.`id_post`, post.`title`, post.`under_title`, post.`intro`, 
               post.`contain`, post.`pic`, post.`id_user`
        FROM `post` 
        WHERE post.`id_post` = :id
    ');
    $req->bindValue(':id', $id, PDO::PARAM_INT);
    $req->execute();

    // Récupère l'article
    $article = $req->fetch(PDO::FETCH_ASSOC);

    // Vérifie si l'article existe et si l'utilisateur connecté est l'auteur
    if (!$article || $article['id_user'] !== $_SESSION['id_user']) {
        echo "Accès refusé ou article introuvable.";
        exit;
    }
} else {
    echo "ID invalide.";
    exit;
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $under_title = $_POST['under_title'];
    $intro = $_POST['intro'];
    $contain = $_POST['contain'];
    $pic = $article['pic'];

    // Gérer l'upload de la nouvelle image si une image est sélectionnée
    if (!empty($_FILES['pic']['name'])) {
        $pic = 'uploads/' . basename($_FILES['pic']['name']);
        move_uploaded_file($_FILES['pic']['tmp_name'], $pic);
    }

    // Mettre à jour l'article dans la base de données
    $stmt = $bdd->prepare('
        UPDATE `post` 
        SET `title` = :title, `under_title` = :under_title, `intro` = :intro, 
            `contain` = :contain, `pic` = :pic
        WHERE `id_post` = :id
    ');
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':under_title', $under_title, PDO::PARAM_STR);
    $stmt->bindValue(':intro', $intro, PDO::PARAM_STR);
    $stmt->bindValue(':contain', $contain, PDO::PARAM_STR);
    $stmt->bindValue(':pic', $pic, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: article.php?id=' . $id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Modifier l'article | SkyDiary</title>
    <link rel="stylesheet" href="./css/style.css">
    <meta name="description" content="SkyDiary est une plateforme de blog en ligne.">
</head>

<body>
<header>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="./index.php">
                <img src="./img/Logo.svg" alt="">SkyDiary
            </a>
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
                        <a class="nav-link" href="./disconnected.php">Disconnect</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main>
    <section>
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
        </form>
    </section>
</main>  
</body>
</html>
