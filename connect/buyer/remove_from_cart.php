<?php
session_start();
require_once __DIR__.'/../connect.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role_user'] !== 0) {
    header('Location: /marketplace/connect/login.php');
    exit;
}

$panier_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$client_id = $_SESSION['id_user'];

if (!$panier_id) {
    $_SESSION['error'] = "ID panier invalide";
    header('Location: panier.php');
    exit;
}

try {
    // Récupérer le produit_id avant suppression
    $stmt = $db->prepare("SELECT produit_id FROM tbl_panier WHERE id_panier = ? AND client_id = ?");
    $stmt->execute([$panier_id, $client_id]);
    $item = $stmt->fetch();

    if (!$item) {
        $_SESSION['error'] = "Article introuvable";
        header('Location: panier.php');
        exit;
    }

    // Suppression de l'item
    $stmt = $db->prepare("DELETE FROM tbl_panier WHERE id_panier = ?");
    $stmt->execute([$panier_id]);

    // Mise à jour de la session
    if (isset($_SESSION['cart'][$item['produit_id']])) {
        unset($_SESSION['cart'][$item['produit_id']]);
    }

    // Mise à jour du total
    $stmt = $db->prepare("SELECT SUM(quantity_panier) AS total FROM tbl_panier WHERE client_id = ?");
    $stmt->execute([$client_id]);
    $_SESSION['cart_total'] = $stmt->fetch()['total'] ?? 0;

    $_SESSION['success'] = "Article retiré du panier";
    header('Location: panier.php');
    exit;

} catch (PDOException $e) {
    error_log("Erreur suppression: " . $e->getMessage());
    $_SESSION['error'] = "Erreur technique";
    header('Location: panier.php');
    exit;
}
?>