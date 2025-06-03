<?php
session_start();
include '../connect.php';

if (!isset($_GET['id'])) {
    header('Location: admin.php'); // ou la page admin correspondante
    exit;
}

$id = (int) $_GET['id'];

// Récupérer l'état actuel
$stmt = $db->prepare("SELECT active_produit FROM tbl_produit WHERE id_produit = ?");
$stmt->execute([$id]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$annonce) {
    // annonce non trouvée
    header('Location: admin.php');
    exit;
}

$nouveauStatut = $annonce['active_produit'] ? 0 : 1;

// Mise à jour du statut
$stmt = $db->prepare("UPDATE tbl_produit SET active_produit = ? WHERE id_produit = ?");
$stmt->execute([$nouveauStatut, $id]);

header('Location: admin.php'); // Retour à la page admin ou liste annonces
exit;
