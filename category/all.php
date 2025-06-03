<?php
// On démarre une session
session_start();
$page = 'allcat';
$produits = [];
$whereClause = "`active_produit` = 1 AND `statut_produit` = 1";
$selectedCategories = [];

// Connexion à la base de données
require_once('../connect/connect.php');

if (!empty($_GET['categories'])) {
    $selectedCategories = array_map('intval', $_GET['categories']);
    $placeholders = implode(',', array_fill(0, count($selectedCategories), '?'));
    $whereClause .= " AND `category_id` IN ($placeholders)";
}

$sql = "SELECT * FROM `tbl_produit` 
        WHERE $whereClause
        ORDER BY `createdate_produit` DESC";

$query = $db->prepare($sql);
$query->execute($selectedCategories);
$produits = $query->fetchAll(PDO::FETCH_ASSOC);

$sql_categories = "SELECT * FROM `tbl_category` WHERE `active_category` = 1";
$query_categories = $db->prepare($sql_categories);
$query_categories->execute();
$categories = $query_categories->fetchAll(PDO::FETCH_ASSOC);

$titre = "Tous Les Articles";

if (count($selectedCategories) === 1) {
    foreach ($categories as $cat) {
        if ($cat['id_category'] == $selectedCategories[0]) {
            $titre = "Articles : " . htmlspecialchars($cat['nom_category']);
        }
    }
}
?>

<?php include('head.php'); ?>
<?php include('../nav.php'); ?>
<br><br><br><br><br><br>
<main class="container-fluid menu">
    <div class="row">
        <section class="col-12">
        <h1 style="border: 2px solid #ff6a00;: orange;color:black;display:inline;border-radius:10px;padding:5px;"><?= $titre ?></h1><br><br>
            <!-- Bouton flottant pour afficher les filtres -->
        <div>
            <button
                class="btn btn-dark dropdown-toggle"
                style="color:black; position: absolute; top: 150px; right: 20px; z-index: 1000;"
                type="button"
                id="dropdownMenuButton"
                data-bs-toggle="dropdown"
                aria-expanded="false">
                Filtrer les catégories
            </button>
            <ul class="dropdown-menu p-3" aria-labelledby="dropdownMenuButton">
                <form method="GET">
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <div class="form-check">
                                <input 
                                    type="checkbox" 
                                    class="form-check-input" 
                                    name="categories[]" 
                                    value="<?= $cat['id_category'] ?>" 
                                    id="cat-<?= $cat['id_category'] ?>"
                                    <?= in_array($cat['id_category'], $selectedCategories) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cat-<?= $cat['id_category'] ?>">
                                    <?= htmlspecialchars($cat['nom_category']) ?>
                                </label>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <button type="submit" style="color:black!important" class="btn btn-dark mt-2 w-100">Appliquer le filtre</button>
                </form>
            </ul>
        </div>
        <br><hr>
        <!-- Affichage des produits -->
        <div class="row">
            <?php foreach ($produits as $produit): ?>
                <div class="col-md-2 mb-5 d-flex">
                    <div class="card w-100 shadow-sm border-0">
                        <img 
                            src="<?= htmlspecialchars($produit['photo_produit']) ?>" 
                            class="card-img-top img-fluid custom-img-size" 
                            alt="Image du produit">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($produit['nom_produit']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($produit['desc_produit']) ?></p>
                            <p class="card-text">Stock: <?= htmlspecialchars($produit['stock_produit']) ?></p>
                        </div>
                        <div class="card-footer fw-bold text-success">
                            <p class="text-start"><?= htmlspecialchars($produit['prix_produit']) ?> €</p>
                            <a href="details.php?id=<?= $produit['id_produit'] ?>" class="btn btn-sm btn-info">Voir</a>
                            <?php
                            if (isset($_SESSION['role_user']) && $_SESSION['role_user'] == 0): ?>
                                <a href="add_to_cart.php?id=<?= $produit['id_produit'] ?>" class="btn btn-success btn-sm">Ajouter à mon Panier</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        </section>
    </div>
</main>
<?php include('../footer.php'); ?>