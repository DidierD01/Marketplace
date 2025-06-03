<?php
session_start();
require_once __DIR__.'/../connect/connect.php';

// Vérification du rôle acheteur
if (!isset($_SESSION['id_user']) || $_SESSION['role_user'] !== 0) {
    $_SESSION['error'] = "Vous devez être connecté en tant qu'acheteur";
    header('Location: /marketplace/connect/login.php');
    exit;
}

$client_id = (int)$_SESSION['id_user'];
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$product_id) {
    $_SESSION['error'] = "Produit invalide";
    header('Location: all.php');
    exit;
}

// Récupération du produit
$stmt = $db->prepare("SELECT * FROM tbl_produit WHERE id_produit = ? AND stock_produit > 0");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = "Produit indisponible";
    header('Location: all.php');
    exit;
}

// 1. Mise à jour de la base de données
try {
    // Vérifie si le produit est déjà dans le panier
    $stmt = $db->prepare("SELECT * FROM tbl_panier WHERE client_id = ? AND produit_id = ?");
    $stmt->execute([$client_id, $product_id]);
    $existingItem = $stmt->fetch();

    if ($existingItem) {
        // Mise à jour de la quantité
        $newQuantity = $existingItem['quantity_panier'] + 1;
        $stmt = $db->prepare("UPDATE tbl_panier SET quantity_panier = ? WHERE id_panier = ?");
        $stmt->execute([$newQuantity, $existingItem['id_panier']]);
    } else {
        // Ajout d'un nouvel item
        $stmt = $db->prepare("INSERT INTO tbl_panier (client_id, produit_id, quantity_panier) VALUES (?, ?, 1)");
        $stmt->execute([$client_id, $product_id]);
    }

    // 2. Mise à jour de la session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity']++;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product_id,
            'name' => $product['nom_produit'],
            'price' => $product['prix_produit'],
            'quantity' => 1,
            'image' => $product['photo_produit']
        ];
    }

    // 3. Calcul du nombre total d'articles pour le badge
    $stmt = $db->prepare("SELECT SUM(quantity_panier) AS total FROM tbl_panier WHERE client_id = ?");
    $stmt->execute([$client_id]);
    $total = $stmt->fetch()['total'];
    $_SESSION['cart_total'] = $total ?: 0;

    $_SESSION['success'] = "Produit ajouté au panier";
    header('Location: /marketplace/connect/buyer/panier.php');
    exit;

} catch (PDOException $e) {
    error_log("Erreur panier: " . $e->getMessage());
    $_SESSION['error'] = "Erreur technique";
    header('Location: all.php');
    exit;
}
?>