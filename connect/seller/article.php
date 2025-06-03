<?php 
session_start();
$page = 'article';
$titre = "Articles en vente";

require_once('../connect.php');

// Vérification de la connexion avec id_user au lieu de vendeur_id
if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
    die("Erreur: Session ID utilisateur non défini. Valeur actuelle: " . (isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'NULL'));
    header('Location: ../login.php');
    exit();
}

$id_vendeur = $_SESSION['id_user'];

// Requête pour récupérer les produits du vendeur
$sql = "SELECT * FROM `tbl_produit` 
        JOIN `tbl_users` ON tbl_produit.vendeur_id = tbl_users.id_user 
        WHERE tbl_users.id_user = :id_vendeur";
$query = $db->prepare($sql);
$query->bindValue(':id_vendeur', $id_vendeur, PDO::PARAM_INT);
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('../head.php'); ?>
<?php include('../../nav.php'); ?>
<br><br><br><br><br>
<main class="container-fluid">
    <section class="row">
        <div class="col-12">
            <?php if(!empty($_SESSION['erreur'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['erreur']; unset($_SESSION['erreur']); ?></div>
            <?php endif; ?>

            <?php if(!empty($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>

            <h1 style="color:#ff6a00;display:inline;border-radius:10px;padding:5px;">
                Vos produits en vente
            </h1><br><br>

            <div class="row">
                <?php foreach($result as $produit): ?>
                    <div class="col-md-3 mb-5 d-flex">
                        <div class="card w-100 shadow-sm border-0">
                            <img src="<?= htmlspecialchars($produit['photo_produit']) ?>" 
                                 class="card-img-top img-fluid" 
                                 style="height:180px; object-fit:cover;" 
                                 alt="Image du produit">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($produit['nom_produit']) ?></h5>
                                <p class="card-text small"><?= htmlspecialchars($produit['desc_produit']) ?></p>
                                <p class="card-text"><strong>Stock :</strong> <?= htmlspecialchars($produit['stock_produit']) ?></p>
                                <p class="card-text"><strong>Actif :</strong> <?= $produit['active_produit'] ? 'Oui' : 'Non' ?></p>
                            </div>
                            <div class="card-footer d-flex justify-content-between flex-wrap gap-2">
                                <a href="disable.php?id=<?= $produit['id_produit'] ?>" class="btn btn-sm btn-warning">A/D</a>
                                <a href="details.php?id=<?= $produit['id_produit'] ?>" class="btn btn-sm btn-info">Voir</a>
                                <a href="edit.php?id=<?= $produit['id_produit'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                                <a href="delete.php?id=<?= $produit['id_produit'] ?>" class="btn btn-sm btn-danger">Supprimer</a>
                            </div>
                            <div class="card-footer text-end text-success fw-bold">
                                <?= htmlspecialchars($produit['prix_produit']) ?> €
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <a href="add.php" class="btn btn-warning mt-4">Ajouter un produit</a>
            <a href="moncompte.php" class="btn btn-secondary mt-4">Retour</a><br><br>
        </div>
    </section>
</main>
<?php include('../../footer.php'); ?>