<?php
session_start();
$page = 'details';
$titre = "Article";
require_once('../connect/connect.php');

// Vérifie si un ID est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['erreur'] = "URL invalide";
    header('Location: ../connect/seller/article.php');
    exit();
}

$id_produit = strip_tags($_GET['id']);

// On récupère le produit, y compris vendeur_id
$sql = "SELECT * FROM tbl_produit WHERE id_produit = :id_produit";
$query = $db->prepare($sql);
$query->bindValue(':id_produit', $id_produit, PDO::PARAM_INT);
$query->execute();
$produit = $query->fetch(PDO::FETCH_ASSOC);

// Si le produit n'existe pas
if (!$produit) {
    $_SESSION['erreur'] = "Produit introuvable";
    header('Location: article.php');
    exit();
}

// Récupérer l'id vendeur depuis la colonne vendeur_id
$id_vendeur = $produit['vendeur_id'] ?? null;

?>

<?php include('../head.php'); ?>
<link rel="stylesheet" href="../css/market.css">
<br><br><br>
<main class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <img src="<?= htmlspecialchars($produit['photo_produit']) ?>" 
                     class="card-img-top img-fluid" 
                     alt="Image du produit" 
                     style="max-height: 400px; object-fit: cover;">
                
                <div class="card-body">
                    <h3 class="card-title"><?= htmlspecialchars($produit['nom_produit']) ?></h3>
                    <p class="card-text"><strong>ID :</strong> <?= $produit['id_produit'] ?></p>
                    <p class="card-text"><strong>Description :</strong> <?= htmlspecialchars($produit['desc_produit']) ?></p>
                    <p class="card-text"><strong>Stock :</strong> <?= htmlspecialchars($produit['stock_produit']) ?></p>
                    <p class="card-text fw-bold text-success"><strong>Prix :</strong> <?= htmlspecialchars($produit['prix_produit']) ?> €</p>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="all.php" class="btn btn-outline-secondary">Retour</a>

                    <?php if (isset($_SESSION['role_user']) && $_SESSION['role_user'] == 0): ?>
                        <a href="add_to_cart.php?id=<?= $produit['id_produit'] ?>" class="btn btn-success btn-sm">Ajouter à mon Panier</a>
                    <?php endif; ?>

                    <?php if ($id_vendeur): ?>
                        <?php if (isset($_SESSION['user_id']) || isset($_SESSION['id_user'])): ?>
                            <?php 
                            // Utilisez la variable de session correcte
                            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['id_user'];
                            // Empêcher de se contacter soi-même
                            if ($user_id != $id_vendeur): ?>
                                <a href="/marketplace/messagerie.php?user=<?= (int)$id_vendeur ?>&produit=<?= $id_produit ?>" 
                                class="btn btn-primary btn-sm">
                                    Contacter le vendeur
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" disabled>
                                    C'est votre produit
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="btn btn-primary btn-sm" 
                                    onclick="alert('Vous devez être connecté pour contacter le vendeur')">
                                Contacter le vendeur
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
<br><br>
<?php include('../footer.php'); ?>