<?php
include_once './connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $username = trim(htmlspecialchars($_POST['username']));
    $email = trim(htmlspecialchars($_POST['email']));
    $password = trim($_POST['password']);
    $error = null;

    // Vérifier si un fichier a été envoyé
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        // Vérification du type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            $error = "Le fichier doit être une image (JPEG, PNG ou GIF).";
        } else {
            // Définir le répertoire d'upload
            $uploadDir = './img/'; // Répertoire où les images seront sauvegardées
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true); // Crée le dossier si nécessaire
            }

            // Générer un nom unique pour le fichier
            $fileName = uniqid() . '-' . basename($_FILES['profile_picture']['name']);
            $filePath = $uploadDir . $fileName;

            // Déplacer le fichier vers le dossier de destination
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filePath)) {
                $error = "Échec de l'enregistrement de la photo de profil.";
            }
        }
    } else {
        $filePath = null; // Aucun fichier envoyé
    }

    // Validation des champs
    if (!$error) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "The email address is invalid.";
        } elseif (strlen($password) < 6) {
            $error = "The password must contain at least 6 characters.";
        } elseif (empty($username)) {
            $error = "Username cannot be empty.";
        } else {
            // Vérifier si l'email existe déjà dans la base de données
            $stmt = $bdd->prepare('SELECT COUNT(*) FROM `user` WHERE `email` = :email');
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $emailExists = $stmt->fetchColumn();

            if ($emailExists) {
                $error = "This email is already in use. Please choose another one.";
            } else {
                // Insérer l'utilisateur dans la base de données
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                // Le chemin du fichier doit être relatif (img/monimage.png)
                $stmt = $bdd->prepare('INSERT INTO `user` (`username`, `email`, `password`, `profile_picture`) VALUES (:username, :email, :password, :profile_picture)');
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                $stmt->bindParam(':profile_picture', $filePath, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    // Redirection après succès
                    header('Location: login.php');
                    exit();
                } else {
                    $error = "An error occurred during registration. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SkyDiary est une plateforme de blog en ligne.">
    <title>SkyDiary | Register</title>
    <link rel="icon" href="./img/Logo.svg">
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid container mt-4">
                <a class="navbar-brand" href="./index.php">
                    <img class="montserrat-semi-bold" src="./img/Logo.svg" alt="">SkyDiary
                </a>
                </ul>
            </div>
            </div>
        </nav>
    </header>
    <main>
        <div class="container mt-5">
            <h1 class="mb-4">Subscribe to SkyDiary!</h1>
            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form action="register.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="profile_picture" class="form-label">Profile picture</label>
                    <input type="file" class="form-control" id="profile_picture" name="profile_picture" required>
                    <?php if (!empty($filePath)): ?>
                        <img src="<?= htmlspecialchars($filePath) ?>" alt="Photo de profil" class="profile-picture mt-3">
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <p class="mt-3">Already have an account? <a href="login.php">Log in here</a>.</p>
        </div>
    </main>
    <?php include "./footer.php" ?>