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
    <footer>

        <article class="container mt-4">
            <p class="decor_txt source-serif-4">Engage with ideas <span>that spark change and creativity. Stay informed on </span> what's shaping the world around you. <span> Discover the stories that
                </span> fuel inspiration and connection.</p>
            <figure>
                <a class="navbar-brand montserrat-semi-bold" href="./index.php"><img src="./img/Logo.svg" alt="Logo">SkyDiary</a>
            </figure>
        </article>
        <hr>
        <div class="container mt-4">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_17_63)">
                    <path d="M24 4.32187C30.4125 4.32187 31.1719 4.35 33.6938 4.4625C36.0375 4.56562 37.3031 4.95938 38.1469 5.2875C39.2625 5.71875 40.0688 6.24375 40.9031 7.07812C41.7469 7.92188 42.2625 8.71875 42.6938 9.83438C43.0219 10.6781 43.4156 11.9531 43.5188 14.2875C43.6313 16.8187 43.6594 17.5781 43.6594 23.9813C43.6594 30.3938 43.6313 31.1531 43.5188 33.675C43.4156 36.0188 43.0219 37.2844 42.6938 38.1281C42.2625 39.2438 41.7375 40.05 40.9031 40.8844C40.0594 41.7281 39.2625 42.2438 38.1469 42.675C37.3031 43.0031 36.0281 43.3969 33.6938 43.5C31.1625 43.6125 30.4031 43.6406 24 43.6406C17.5875 43.6406 16.8281 43.6125 14.3063 43.5C11.9625 43.3969 10.6969 43.0031 9.85313 42.675C8.7375 42.2438 7.93125 41.7188 7.09688 40.8844C6.25313 40.0406 5.7375 39.2438 5.30625 38.1281C4.97813 37.2844 4.58438 36.0094 4.48125 33.675C4.36875 31.1438 4.34063 30.3844 4.34063 23.9813C4.34063 17.5688 4.36875 16.8094 4.48125 14.2875C4.58438 11.9437 4.97813 10.6781 5.30625 9.83438C5.7375 8.71875 6.2625 7.9125 7.09688 7.07812C7.94063 6.23438 8.7375 5.71875 9.85313 5.2875C10.6969 4.95938 11.9719 4.56562 14.3063 4.4625C16.8281 4.35 17.5875 4.32187 24 4.32187ZM24 0C17.4844 0 16.6688 0.028125 14.1094 0.140625C11.5594 0.253125 9.80625 0.665625 8.2875 1.25625C6.70312 1.875 5.3625 2.69062 4.03125 4.03125C2.69063 5.3625 1.875 6.70313 1.25625 8.27813C0.665625 9.80625 0.253125 11.55 0.140625 14.1C0.028125 16.6687 0 17.4844 0 24C0 30.5156 0.028125 31.3312 0.140625 33.8906C0.253125 36.4406 0.665625 38.1938 1.25625 39.7125C1.875 41.2969 2.69063 42.6375 4.03125 43.9688C5.3625 45.3 6.70313 46.125 8.27813 46.7344C9.80625 47.325 11.55 47.7375 14.1 47.85C16.6594 47.9625 17.475 47.9906 23.9906 47.9906C30.5063 47.9906 31.3219 47.9625 33.8813 47.85C36.4313 47.7375 38.1844 47.325 39.7031 46.7344C41.2781 46.125 42.6188 45.3 43.95 43.9688C45.2812 42.6375 46.1063 41.2969 46.7156 39.7219C47.3063 38.1938 47.7188 36.45 47.8313 33.9C47.9438 31.3406 47.9719 30.525 47.9719 24.0094C47.9719 17.4938 47.9438 16.6781 47.8313 14.1188C47.7188 11.5688 47.3063 9.81563 46.7156 8.29688C46.125 6.70312 45.3094 5.3625 43.9688 4.03125C42.6375 2.7 41.2969 1.875 39.7219 1.26562C38.1938 0.675 36.45 0.2625 33.9 0.15C31.3313 0.028125 30.5156 0 24 0Z" fill="" />
                    <path d="M24 11.6719C17.1938 11.6719 11.6719 17.1938 11.6719 24C11.6719 30.8062 17.1938 36.3281 24 36.3281C30.8062 36.3281 36.3281 30.8062 36.3281 24C36.3281 17.1938 30.8062 11.6719 24 11.6719ZM24 31.9969C19.5844 31.9969 16.0031 28.4156 16.0031 24C16.0031 19.5844 19.5844 16.0031 24 16.0031C28.4156 16.0031 31.9969 19.5844 31.9969 24C31.9969 28.4156 28.4156 31.9969 24 31.9969Z" fill="" />
                    <path d="M39.6937 11.1843C39.6937 12.778 38.4 14.0624 36.8156 14.0624C35.2219 14.0624 33.9375 12.7687 33.9375 11.1843C33.9375 9.59053 35.2313 8.30615 36.8156 8.30615C38.4 8.30615 39.6937 9.5999 39.6937 11.1843Z" fill="" />
                </g>
                <defs>
                    <clipPath id="clip0_17_63">
                        <rect width="48" height="48" fill="" />
                    </clipPath>
                </defs>
            </svg>
            <svg width="44" height="41" viewBox="0 0 44 41" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M34.6526 0.8078H41.3995L26.6594 17.6548L44 40.5797H30.4225L19.7881 26.6759L7.61989 40.5797H0.868864L16.6349 22.56L0 0.8078H13.9222L23.5348 13.5165L34.6526 0.8078ZM32.2846 36.5414H36.0232L11.8908 4.63406H7.87892L32.2846 36.5414Z" fill="" />
            </svg>

            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M47.5219 14.4001C47.5219 14.4001 47.0531 11.0907 45.6094 9.6376C43.7812 7.7251 41.7375 7.71572 40.8 7.60322C34.0875 7.11572 24.0094 7.11572 24.0094 7.11572H23.9906C23.9906 7.11572 13.9125 7.11572 7.2 7.60322C6.2625 7.71572 4.21875 7.7251 2.39062 9.6376C0.946875 11.0907 0.4875 14.4001 0.4875 14.4001C0.4875 14.4001 0 18.2907 0 22.172V25.8095C0 29.6907 0.478125 33.5813 0.478125 33.5813C0.478125 33.5813 0.946875 36.8907 2.38125 38.3438C4.20937 40.2563 6.60938 40.1907 7.67813 40.397C11.5219 40.7626 24 40.8751 24 40.8751C24 40.8751 34.0875 40.8563 40.8 40.3782C41.7375 40.2657 43.7812 40.2563 45.6094 38.3438C47.0531 36.8907 47.5219 33.5813 47.5219 33.5813C47.5219 33.5813 48 29.7001 48 25.8095V22.172C48 18.2907 47.5219 14.4001 47.5219 14.4001ZM19.0406 30.2251V16.7345L32.0062 23.5032L19.0406 30.2251Z" fill="" />
            </svg>
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_17_68)">
                    <path d="M44.4469 0H3.54375C1.58437 0 0 1.54688 0 3.45938V44.5312C0 46.4437 1.58437 48 3.54375 48H44.4469C46.4062 48 48 46.4438 48 44.5406V3.45938C48 1.54688 46.4062 0 44.4469 0ZM14.2406 40.9031H7.11563V17.9906H14.2406V40.9031ZM10.6781 14.8688C8.39062 14.8688 6.54375 13.0219 6.54375 10.7437C6.54375 8.46562 8.39062 6.61875 10.6781 6.61875C12.9563 6.61875 14.8031 8.46562 14.8031 10.7437C14.8031 13.0125 12.9563 14.8688 10.6781 14.8688ZM40.9031 40.9031H33.7875V29.7656C33.7875 27.1125 33.7406 23.6906 30.0844 23.6906C26.3812 23.6906 25.8187 26.5875 25.8187 29.5781V40.9031H18.7125V17.9906H25.5375V21.1219H25.6312C26.5781 19.3219 28.9031 17.4188 32.3625 17.4188C39.5719 17.4188 40.9031 22.1625 40.9031 28.3313V40.9031Z" fill="" />
                </g>
                <defs>
                    <clipPath id="clip0_17_68">
                        <rect width="48" height="48" fill="" />
                    </clipPath>
                </defs>
            </svg>
            <p>© 2025 SkyDiary. All rights reserved.</p>
            <img src="./img/youtube.svg" alt="">
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="./js/main.js"></script>
</body>

</html>