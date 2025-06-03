<?php
session_start();
require_once __DIR__.'/../connect.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: /marketplace/login.php');
    exit;
}

$client_id = $_SESSION['id_user'];

// Récupérer les articles du panier
$stmt = $db->prepare("
    SELECT p.*, pr.nom_produit, pr.prix_produit 
    FROM tbl_panier p
    JOIN tbl_produit pr ON p.produit_id = pr.id_produit
    WHERE p.client_id = ?
");
$stmt->execute([$client_id]);
$panierItems = $stmt->fetchAll();

if (empty($panierItems)) {
    $_SESSION['error'] = "Votre panier est vide.";
    header('Location: panier.php');
    exit;
}

// Calcul du total
$total = 0;
foreach ($panierItems as $item) {
    $total += $item['prix_produit'] * $item['quantity_panier'];
}

// Insérer une seule ligne dans tbl_order
$stmt = $db->prepare("
    INSERT INTO tbl_order (acheteur_id, total_order, createdate_order, statut_order)
    VALUES (?, ?, NOW(), 'En attente')
");
$stmt->execute([$client_id, $total]);

// Vider le panier
$stmt = $db->prepare("DELETE FROM tbl_panier WHERE client_id = ?");
$stmt->execute([$client_id]);

unset($_SESSION['cart']); // au cas où tu gères un mini-panier en session

$db->commit();

$_SESSION['success'] = "Commande passée avec succès.";
header('Location: /marketplace/connect/buyer/moncompte.php');
exit;
?>
