<?php
include_once './connexion.php';
session_start();
$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user) {
    echo "Vous devez être connecté pour supprimer un article.";
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Récupère l'article pour vérifier que l'utilisateur est bien l'auteur
    $req = $bdd->prepare('SELECT id_user FROM post WHERE id_post = :id');
    $req->bindValue(':id', $id, PDO::PARAM_INT);
    $req->execute();
    $article = $req->fetch(PDO::FETCH_ASSOC);

    if (!$article || $article['id_user'] !== $id_user) {
        echo "Vous n'avez pas l'autorisation de supprimer cet article.";
        exit;
    }

    // Supprime l'article
    $req = $bdd->prepare('DELETE FROM post WHERE id_post = :id');
    $req->bindValue(':id', $id, PDO::PARAM_INT);
    $req->execute();

    echo "Article supprimé avec succès.";
} else {
    echo "ID invalide.";
}
?>
