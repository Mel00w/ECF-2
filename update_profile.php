<?php
session_start();
include_once './connexion.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit();
}

// Récupère l'ID de l'utilisateur connecté
$id_user = $_SESSION['id_user'];

// Traite les données du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);

    // Gère le téléchargement de l'image de profil
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $profile_picture = $upload_dir . basename($_FILES['profile_picture']['name']);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture)) {
            // Fichier téléchargé avec succès
        } else {
            // Erreur lors du déplacement du fichier
            $error = 'Erreur lors du téléchargement de l\'image.';
        }
    } else {
        // Si aucune nouvelle image n'est téléchargée, garde l'ancienne
        $profile_picture = $bdd->query('SELECT profile_picture FROM user WHERE id_user = ' . $id_user)->fetchColumn();
    }

    // Met à jour les informations de profil dans la base de données
    $stmt = $bdd->prepare('UPDATE user SET username = ?, profile_picture = ? WHERE id_user = ?');
    $stmt->execute([$username, $profile_picture, $id_user]);

    // Redirige vers la page de profil après la mise à jour
    header('Location: profile.php');
    exit();
}
