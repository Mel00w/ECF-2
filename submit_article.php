<?php
session_start();
include_once './connexion.php';

$id_user = $_SESSION['id_user'] ?? null; // si la session n'existe pas, $id_user est null

if (!$id_user) {
    echo "Vous devez être connecté pour publier un article.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $under_title = $_POST['under_title'];
    $intro = $_POST['intro'];
    $contain = $_POST['contain'];
    $pic = $_FILES['pic']['name'];

    // Renommer l'image avec un séparateur temporaire
    $extension = pathinfo($_FILES["pic"]["name"], PATHINFO_EXTENSION);
    $new_pic_name = "img\\" . $pic ;

    // Télécharger l'image
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($new_pic_name);
    move_uploaded_file($_FILES["pic"]["tmp_name"], $target_file);

    // Insérer l'article dans la base de données
    $req = $bdd->prepare('INSERT INTO post (title, under_title, intro, contain, pic, id_user) VALUES (?, ?, ?, ?, ?, ?)');
    $req->execute([$title, $under_title, $intro, $contain, $new_pic_name, $id_user]);

    // Obtenir l'ID du nouvel article créé
    $article_id = $bdd->lastInsertId();

    // Rediriger vers article.php avec l'ID de l'article
    header('Location: article.php?id=' . $article_id);
    exit;
}
