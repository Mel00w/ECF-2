<?php
session_start();
include_once './connexion.php';

// Définir le nombre d'articles à afficher par page
$articlesPerPage = 5;

// Récupérer le numéro de la page courante depuis l'URL, ou par défaut 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // S'assurer que le numéro de page est au moins 1

// Calculer l'offset pour la requête SQL (décalage des résultats pour la pagination)
$offset = ($page - 1) * $articlesPerPage;

// Récupérer le nombre total d'articles dans la table "post"
$totalArticlesQuery = $bdd->query('SELECT COUNT(*) AS total FROM `post`');
$totalArticles = $totalArticlesQuery->fetch(PDO::FETCH_ASSOC)['total'];

// Calculer le nombre total de pages nécessaires
$totalPages = ceil($totalArticles / $articlesPerPage);

// Récupérer les articles pour la page actuelle avec une requête préparée
$req = $bdd->prepare(
    'SELECT 
        post.`id_post`, post.`date_published`, post.`title`, post.`under_title`, 
        post.`intro`, post.`contain`, post.`pic`, post.`id_user`, 
        user.`username`, user.`profile_picture` 
     FROM `post`
     INNER JOIN `user` ON post.`id_user` = user.`id_user`
     ORDER BY post.`date_published` DESC
     LIMIT :limit OFFSET :offset'
);
$req->bindValue(':limit', $articlesPerPage, PDO::PARAM_INT); // Limite des articles
$req->bindValue(':offset', $offset, PDO::PARAM_INT); // Décalage pour la pagination
$req->execute();

// Si l'utilisateur est connecté, récupérer ses informations
$isConnected = isset($_SESSION['id_user']);
if ($isConnected) {
    $user = $bdd->prepare('SELECT id_user, username, profile_picture, email FROM user WHERE id_user = :id');
    $user->bindParam(':id', $_SESSION['id_user'], PDO::PARAM_INT);
    $user->execute();
    $user = $user->fetch(PDO::FETCH_ASSOC);
    $username = $user['username'];
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
    <title>SkyDiary | Home</title>
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
        <!-- Code de la publicité commence ici -->
        <div>
            <script type="text/javascript">
                // Votre code de publicité ici
            </script>
        </div>
        <!-- Code de la publicité se termine ici -->
        <?php if ($isConnected): ?>
            <p class="decor_txt source-serif-4 text-center">Engage with ideas <span>that spark change and creativity. Stay informed on </span> what's shaping the world around you. <span> Discover the stories that </span> fuel inspiration and connection.</p>

            <!-- Liste des articles -->
            <div class="row d-flex flex-column align-items-center">
                <?php while ($article = $req->fetch(PDO::FETCH_ASSOC)) { ?>
                    <div class="col-md-8 mb-4 zoom-out-container">
                        <a href="article.php?id=<?= $article['id_post'] ?>">
                            <img src="<?= htmlspecialchars($article['pic']) ?>" alt="Photo de l'article" class="img-fluid rounded mx-auto d-block img-fluid-custom ">
                        </a>
                        <h2 class="source-serif-4 text-center mb-4"><?= htmlspecialchars($article['title']) ?></h2>
                    </div>
                <?php } ?>
            </div>
            <nav>
                <ul class="pagination justify-content-center">
                    <!-- Lien vers la page précédente -->
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <!-- Liens vers les pages -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Lien vers la page suivante -->
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

    </main>
<?php else: ?>
    <p class="text-center">Please <a href="./login.php">login</a> to publish an article.</p>
<?php endif; ?>
<?php include "./footer.php" ?>