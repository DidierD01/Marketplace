<?php
session_start();
include '../connect.php'; // Assure-toi que $db (PDO) est bien défini ici

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Préparer et exécuter la suppression de l'utilisateur
    $stmt = $db->prepare("DELETE FROM tbl_users WHERE id_user = ?");
    $stmt->execute([$id]);

    // Redirection après suppression
    header("Location: admin.php");
    exit();
} else {
    echo "ID d'utilisateur manquant.";
}
