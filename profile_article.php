<?php
session_start();
include_once './connexion.php';

// Récupérer l'ID de l'utilisateur à partir de l'URL
$id_user = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Vérifier si l'ID de l'utilisateur est valide
if ($id_user <= 0) {
    echo "Utilisateur introuvable.";
    exit;
}

// Requête préparée pour récupérer les informations de l'utilisateur
$stmt = $bdd->prepare('SELECT `id_user`, `username`, `profile_picture`, `email` FROM `user` WHERE `id_user` = :id_user');
$stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Requête préparée pour récupérer les articles de l'utilisateur
$req = $bdd->prepare(
    'SELECT 
        `id_post`, `title`, `intro`, `date_published`, `pic`
     FROM `post`
     WHERE `id_user` = :id_user
     ORDER BY `date_published` DESC'
);
$req->bindValue(':id_user', $id_user, PDO::PARAM_INT);
$req->execute();
$articles = $req->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur connecté
$isConnected = isset($_SESSION['id_user']);
if ($isConnected) {
    $currentUser = $bdd->prepare('SELECT id_user, username, profile_picture, email FROM user WHERE id_user = :id');
    $currentUser->bindParam(':id', $_SESSION['id_user'], PDO::PARAM_INT);
    $currentUser->execute();
    $currentUser = $currentUser->fetch(PDO::FETCH_ASSOC);
    $username = $currentUser['username'];
} else {
    $username = null;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SkyDiary est une plateforme de blog en ligne.">
    <title>SkyDiary | Profil de <?= htmlspecialchars($user['username']) ?></title>
    <link rel="icon" href="./img/Logo.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
                                    <img src="<?= htmlspecialchars($currentUser['profile_picture']) ?>" alt="Photo de profil" style="width: 30px; height: 30px; border-radius: 50%;">
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
        <div class="profile-header d-flex align-items-center mb-4">
            <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" style="width: 100px; height: 100px; border-radius: 50%;">
            <h1 class="ms-4"><?= htmlspecialchars($user['username']) ?>'s posts</h1>
        </div>

        <div class="row d-flex flex-column align-items-center">
            <?php foreach ($articles as $article): ?>
                <div class="col-md-8 mb-4 zoom-out-container">
                    <a href="article.php?id=<?= $article['id_post'] ?>">
                        <img src="<?= htmlspecialchars($article['pic']) ?>" alt="Photo de l'article" class="img-fluid rounded mx-auto d-block img-fluid-custom">
                    </a>
                    <h2 class="source-serif-4 text-center mb-4"><?= htmlspecialchars($article['title']) ?></h2>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <?php include "./footer.php" ?>
</body>

</html>