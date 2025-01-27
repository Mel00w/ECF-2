<?php
session_start();
include_once './connexion.php';

// Vérifie si l'utilisateur est connecté
if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];

    try {
        // Supprime les publications associées à cet utilisateur (si nécessaire)
        $deletePosts = $bdd->prepare('DELETE FROM post WHERE id_user = :id_user');
        $deletePosts->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $deletePosts->execute();

        // Supprime l'utilisateur de la base de données
        $deleteUser = $bdd->prepare('DELETE FROM user WHERE id_user = :id_user');
        $deleteUser->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $deleteUser->execute();

        // Détruit la session
        session_destroy();

        // Redirige l'utilisateur vers la page d'accueil avec un message
        header('Location: index.php?account_deleted=true');
        exit;
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    // Redirige l'utilisateur non connecté vers la page de connexion
    header('Location: login.php');
    exit;
}
