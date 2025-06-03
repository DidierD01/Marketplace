<?php
session_start();
include '../connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Supprimer le produit
    $stmt = $db->prepare("DELETE FROM tbl_produit WHERE id_produit = ?");
    $stmt->execute([$id]);

    // Redirection vers l’admin après suppression
    header("Location: admin.php");
    exit();
} else {
    echo "ID d'annonce manquant.";
}
