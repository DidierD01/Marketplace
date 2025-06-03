<?php
session_start();
require_once('../connect.php');

// Vérifier la session du vendeur
if (!isset($_SESSION['vendeurid'])) {
    $_SESSION['erreur'] = "Accès refusé.";
    header('Location: ../connect.php');
    exit();
}

$id_vendeur = $_SESSION['vendeurid'];

// Vérifier l’ID dans l’URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['erreur'] = "URL invalide";
    header('Location: article.php');
    exit();
}

$id_produit = strip_tags($_GET['id']);

// Vérifier que le produit appartient bien au vendeur connecté
$sql = "SELECT * FROM tbl_produit WHERE id_produit = :id_produit AND vendeur_id = :id_vendeur";
$query = $db->prepare($sql);
$query->bindValue(':id_produit', $id_produit, PDO::PARAM_INT);
$query->bindValue(':id_vendeur', $id_vendeur, PDO::PARAM_INT);
$query->execute();

$produit = $query->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    $_SESSION['erreur'] = "Produit introuvable ou accès non autorisé";
    header('Location: article.php');
    exit();
}

// Basculer l’état active_produit
$newStatus = ($produit['active_produit'] == 1) ? 0 : 1;

$sql = "UPDATE tbl_produit SET active_produit = :newStatus WHERE id_produit = :id_produit";
$query = $db->prepare($sql);
$query->bindValue(':newStatus', $newStatus, PDO::PARAM_INT);
$query->bindValue(':id_produit', $id_produit, PDO::PARAM_INT);
$query->execute();

$_SESSION['message'] = $newStatus ? "Produit activé avec succès" : "Produit désactivé avec succès";
header('Location: article.php');
exit();
