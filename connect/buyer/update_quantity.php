<?php
session_start();
require_once __DIR__.'/../connect.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role_user'] !== 0) {
    header('Location: /marketplace/connect/login.php');
    exit;
}

$panier_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$client_id = $_SESSION['id_user'];

if (!$panier_id || !in_array($action, ['increase', 'decrease'])) {
    $_SESSION['error'] = "Action invalide";
    header('Location: panier.php');
    exit;
}

try {
    // Vérifier que l'item appartient bien au client
    $stmt = $db->prepare("SELECT * FROM tbl_panier WHERE id_panier = ? AND client_id = ?");
    $stmt->execute([$panier_id, $client_id]);
    $item = $stmt->fetch();

    if (!$item) {
        $_SESSION['error'] = "Article introuvable";
        header('Location: panier.php');
        exit;
    }

    // Mise à jour de la quantité
    $newQuantity = $action === 'increase' 
        ? $item['quantity_panier'] + 1 
        : max(1, $item['quantity_panier'] - 1);

    $stmt = $db->prepare("UPDATE tbl_panier SET quantity_panier = ? WHERE id_panier = ?");
    $stmt->execute([$newQuantity, $panier_id]);

    // Mise à jour de la session
    if (isset($_SESSION['cart'][$item['produit_id']])) {
        $_SESSION['cart'][$item['produit_id']]['quantity'] = $newQuantity;
    }

    // Mise à jour du total
    $stmt = $db->prepare("SELECT SUM(quantity_panier) AS total FROM tbl_panier WHERE client_id = ?");
    $stmt->execute([$client_id]);
    $_SESSION['cart_total'] = $stmt->fetch()['total'];

    $_SESSION['success'] = "Quantité mise à jour";
    header('Location: panier.php');
    exit;

} catch (PDOException $e) {
    error_log("Erreur quantité: " . $e->getMessage());
    $_SESSION['error'] = "Erreur technique";
    header('Location: panier.php');
    exit;
}
?>