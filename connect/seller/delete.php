<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: ../connect.php');
    exit();
}

require_once('../connect.php');

// Vérifie que l'ID est présent dans l'URL
if (isset($_GET['id']) && !empty($_GET['id'])) {

    $id_produit = (int) strip_tags($_GET['id']);

    // Vérifie si le produit existe et appartient au vendeur connecté
    $sql = "SELECT * FROM tbl_produit WHERE id_produit = :id_produit AND vendeur_id = :vendeur_id";
    $query = $db->prepare($sql);
    $query->bindValue(':id_produit', $id_produit, PDO::PARAM_INT);
    $query->bindValue(':vendeur_id', $_SESSION['id_user'], PDO::PARAM_INT);
    $query->execute();
    $produit = $query->fetch(PDO::FETCH_ASSOC);

    if (!$produit) {
        $_SESSION['erreur'] = "Produit introuvable ou accès interdit.";
        header('Location: article.php');
        exit();
    }

    // Suppression du fichier image si présent
    if (!empty($produit['photo_produit'])) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . $produit['photo_produit'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Suppression du produit
    $sql = "DELETE FROM tbl_produit WHERE id_produit = :id_produit";
    $query = $db->prepare($sql);
    $query->bindValue(':id_produit', $id_produit, PDO::PARAM_INT);
    $query->execute();

    $_SESSION['success'] = "Produit supprimé avec succès.";
    header('Location: article.php');
    exit();

} else {
    $_SESSION['erreur'] = "ID du produit manquant.";
    header('Location: article.php');
    exit();
}
?>
