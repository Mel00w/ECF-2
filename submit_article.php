<?php
session_start();
include_once './connexion.php';

$id_user = $_SESSION['id_user'] ?? null; // si la session n'existe pas, $id_user est null

if (!$id_user) {
    header('Location: publish.php?error=' . urlencode('Vous devez être connecté pour publier un article.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $under_title = $_POST['under_title'];
    $intro = $_POST['intro'];
    $contain = $_POST['contain'];
    $pic = $_FILES['pic']['name'];

    $extension = pathinfo($pic, PATHINFO_EXTENSION);

    // Renommer l'image avec un préfixe fixe et l'extension correcte
    $new_pic_name = "uploads\\" . pathinfo($pic, PATHINFO_FILENAME) . "." . $extension;

    // Télécharger l'image
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($new_pic_name);
    if (move_uploaded_file($_FILES["pic"]["tmp_name"], $target_file)) {
        echo "L'image a été téléchargée avec succès.";
    } else {
        echo "Échec du téléchargement de l'image. Erreur : ";
        switch ($_FILES["pic"]["error"]) {
            case UPLOAD_ERR_INI_SIZE:
                echo "La taille du fichier dépasse la limite autorisée.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                echo "La taille du fichier dépasse la limite autorisée par le formulaire.";
                break;
            case UPLOAD_ERR_PARTIAL:
                echo "Le fichier n'a été que partiellement téléchargé.";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "Aucun fichier n'a été téléchargé.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                echo "Dossier temporaire manquant.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                echo "Échec de l'écriture du fichier sur le disque.";
                break;
            case UPLOAD_ERR_EXTENSION:
                echo "Une extension PHP a arrêté le téléchargement de l'image.";
                break;
            default:
                echo "Erreur inconnue.";
                break;
        }
        exit;
    }

    // Ajouter la date de publication de l'article
    $date_published = date('Y-m-d H:i:s');

    // Insérer l'article dans la base de données avec la date de publication
    $req = $bdd->prepare('INSERT INTO post (title, under_title, intro, contain, pic, id_user, date_published) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $req->execute([$title, $under_title, $intro, $contain, $new_pic_name, $id_user, $date_published]);

    // Obtenir l'ID du nouvel article créé
    $article_id = $bdd->lastInsertId();

    // Rediriger vers article.php avec l'ID de l'article
    header('Location: article.php?id=' . $article_id);
    exit;
}
?>
