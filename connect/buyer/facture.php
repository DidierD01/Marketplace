<?php
session_start();
require_once __DIR__.'/../connect.php';

if (!isset($_GET['order_id'])) {
    die("Erreur : ID de commande manquant.");
}

$order_id = $_GET['order_id'];

// Récupérer la commande
$stmt = $db->prepare("SELECT * FROM tbl_order WHERE id_order = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les détails de l'acheteur
$stmt_user = $db->prepare("SELECT * FROM tbl_users WHERE id_user = ?");
$stmt_user->execute([$order['acheteur_id']]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Récupérer les produits de la commande
$stmt_items = $db->prepare("
    SELECT oi.quantity, oi.prix, pr.nom_produit
    FROM tbl_order_items oi
    JOIN tbl_produit pr ON oi.produit_id = pr.id_produit
    WHERE oi.order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('../head.php'); ?>

<style>
    .facture-container {
        max-width: 800px;
        margin: auto;
        background: #fffaf5;
        padding: 40px;
        border: 1px solid #ffa726;
        border-radius: 10px;
        font-family: Arial, sans-serif;
        color: #333;
    }

    .facture-header {
        border-bottom: 2px solid #ffa726;
        margin-bottom: 20px;
    }

    .facture-header h1 {
        color: #ef6c00;
    }

    .facture-details p {
        margin: 4px 0;
    }

    .facture-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .facture-table th {
        background-color: #ffa726;
        color: white;
        padding: 10px;
    }

    .facture-table td {
        border: 1px solid #ddd;
        padding: 10px;
    }

    .facture-footer {
        margin-top: 30px;
        font-size: 0.9em;
        color: #555;
        text-align: center;
    }

    .btn-print {
        background-color: #ef6c00;
        color: white;
        border: none;
        padding: 10px 20px;
        margin-top: 20px;
        cursor: pointer;
        border-radius: 5px;
    }

    .btn-print:hover {
        background-color: #d65b00;
    }

    @media print {
        .btn-print, .back-link {
            display: none;
        }

        body {
            background: white;
        }

        .facture-container {
            border: none;
        }
    }
</style>

<a href="moncompte.php" class="btn btn-dark mt-3 back-link">⬅ Retour à mon compte</a>

<div class="facture-container">
    <div class="facture-header">
        <h1>Facture</h1>
        <p>Vendeo 🛒</p>
        <p>Date : <?= date('d/m/Y H:i', strtotime($order['createdate_order'])) ?></p>
        <p>Commande n° : <strong>#<?= $order['id_order'] ?></strong></p>
    </div>

    <div class="facture-details">
        <h3>Informations du client</h3>
        <p><strong>Nom :</strong> <?= htmlspecialchars($user['prenom_user'] . ' ' . $user['nom_user']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($user['mail_user']) ?></p>
        <p><strong>Statut :</strong> <?= htmlspecialchars($order['statut_order']) ?></p>
    </div>

    <table class="facture-table">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['nom_produit']) ?></td>
                    <td><?= intval($item['quantity']) ?></td>
                    <td><?= number_format($item['prix'], 2, ',', ' ') ?> €</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3 style="text-align: right; margin-top: 20px;">Total : <?= number_format($order['total_order'], 2) ?> €</h3>

    <button onclick="window.print()" class="btn-print">🖨️ Imprimer la facture</button>

    <div class="facture-footer">
        Merci pour votre commande chez Vendeo !<br>
        Une copie de cette facture vous a été envoyée par email si votre commande a été confirmée.
    </div>
</div>
