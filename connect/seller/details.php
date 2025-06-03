<?php
session_start();
require_once('../connect.php');

// Vérifie si un ID est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['erreur'] = "URL invalide";
    header('Location: article.php');
    exit();
}

$id_produit = strip_tags($_GET['id']);

// On récupère le produit
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
?>

<?php include('../head.php'); ?>
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
                    <p class="card-text"><strong>Catégorie ID :</strong> <?= $produit['category_id'] ?></p>
                    <p class="card-text fw-bold text-success"><strong>Prix :</strong> <?= htmlspecialchars($produit['prix_produit']) ?> €</p>
                    <p class="card-text"><strong>Actif :</strong> <?= $produit['active_produit'] ? 'Oui' : 'Non' ?></p>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="article.php" class="btn btn-outline-secondary">Retour</a>
                    <a href="edit.php?id=<?= $produit['id_produit'] ?>" class="btn btn-primary">Modifier</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include('../footer.php'); ?>
