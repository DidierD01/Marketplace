<?php
session_start();
require_once __DIR__.'/../connect.php';

$page = 'cart';
$titre = "Mon Panier";

if (!isset($_SESSION['id_user']) || $_SESSION['role_user'] !== 0) {
    header('Location: /marketplace/connect/login.php');
    exit;
}

$client_id = $_SESSION['id_user'];

// Récupération du panier depuis la base
$stmt = $db->prepare("
    SELECT p.*, pr.nom_produit, pr.prix_produit, pr.photo_produit 
    FROM tbl_panier p
    JOIN tbl_produit pr ON p.produit_id = pr.id_produit
    WHERE p.client_id = ?
");
$stmt->execute([$client_id]);
$panierItems = $stmt->fetchAll();

// Calcul du total
$total = 0;
$totalItems = 0;
foreach ($panierItems as $item) {
    $total += $item['prix_produit'] * $item['quantity_panier'];
    $totalItems += $item['quantity_panier'];
}

// Mise à jour de la session
$_SESSION['cart_total'] = $totalItems;

$page = 'panier';
require_once __DIR__.'/../head.php';
require_once __DIR__.'/../../nav.php';
?>

<main class="container" style="margin-top:100px">
    <h1>Mon Panier</h1>
    
    <?php if (empty($panierItems)): ?>
        <div class="alert alert-warning">Votre panier est vide</div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($panierItems as $item): ?>
                <tr>
                    <td>
                    <?php
                        $imageSrc = $item['photo_produit'];

                        if (filter_var($imageSrc, FILTER_VALIDATE_URL)) {
                            $finalSrc = $imageSrc;
                        } else {
                            $finalSrc = "/marketplace/uploads/" . ltrim($imageSrc, '/');
                        }
                        ?>
                        <img src="<?= htmlspecialchars($finalSrc) ?>" width="50" class="me-2">
                        <?= htmlspecialchars($item['nom_produit']) ?>
                    </td>
                    <td><?= number_format($item['prix_produit'], 2) ?> €</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <a href="update_quantity.php?id=<?= $item['id_panier'] ?>&action=decrease" class="btn btn-outline-secondary btn-sm me-1">-</a>
                            <?= $item['quantity_panier'] ?>
                            <a href="update_quantity.php?id=<?= $item['id_panier'] ?>&action=increase" class="btn btn-outline-secondary btn-sm ms-1">+</a>
                        </div>
                    </td>
                    <td><?= number_format($item['prix_produit'] * $item['quantity_panier'], 2) ?> €</td>
                    <td>
                        <a href="remove_from_cart.php?id=<?= $item['id_panier'] ?>" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                    <td colspan="2"><strong><?= number_format($total, 2) ?> €</strong></td>
                </tr>
            </tbody>
        </table>
        
        <div class="text-end">
            <a href="../../category/all.php" class="btn btn-success">Continuer les achats</a>
            <a href="paiement.php" class="btn btn-success">Passer la commande</a>
        </div>
    <?php endif; ?>

    <?php
    // Récupérer 8 produits aléatoires hors ceux déjà affichés (si besoin)
        $ids_affiches = [];
        if (!empty($panierItems)) {
            $ids_affiches = array_column($panierItems, 'produit_id');
        }
        $placeholders = implode(',', array_fill(0, count($ids_affiches), '?'));

        $sql_sugg = "SELECT * FROM tbl_produit 
                    WHERE active_produit = 1 
                    AND statut_produit = 1
                    " . (count($ids_affiches) ? "AND id_produit NOT IN ($placeholders)" : "") . "
                    ORDER BY RAND() LIMIT 8";
        $query_sugg = $db->prepare($sql_sugg);
        $query_sugg->execute($ids_affiches);
        $suggestions = $query_sugg->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("
        SELECT p.*, pr.id_produit, pr.nom_produit, pr.prix_produit, pr.photo_produit 
        FROM tbl_panier p
        JOIN tbl_produit pr ON p.produit_id = pr.id_produit
        WHERE p.client_id = ?
        ");
    ?>

<div class="container my-5">
    <h2 class="text-center mb-4" style="color:#ff8800;">Suggestions pour vous</h2>
    <?php if (!empty($suggestions)): ?>
    <div id="suggestionCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $chunks = array_chunk($suggestions, 4);
            foreach ($chunks as $i => $suggGroup): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <div class="row justify-content-center">
                        <?php foreach ($suggGroup as $sugg): ?>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card h-100 shadow-sm border-0">
                                <img src="<?= htmlspecialchars($sugg['photo_produit']) ?>" class="card-img-top" style="height:170px;object-fit:cover;" alt="Image du produit">
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($sugg['nom_produit']) ?></h6>
                                    <p class="card-text" style="font-size:0.95em;"><?= htmlspecialchars($sugg['desc_produit']) ?></p>
                                </div>
                                <div class="card-footer bg-white">
                                    <span class="fw-bold" style="color:#ff8800;"><?= htmlspecialchars($sugg['prix_produit']) ?> €</span>
                                    <a href="details.php?id=<?= $sugg['id_produit'] ?>" class="btn btn-sm btn-info float-end">Voir</a>
                                    <?php
                                    if (isset($_SESSION['role_user']) && $_SESSION['role_user'] == 0): ?>
                                        <a href="/marketplace/category/add_to_cart.php?id=<?= $sugg['id_produit'] ?>" class="btn btn-success btn-sm">Ajouter à mon Panier</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
            <span data-bs-target="#suggestionCarousel" data-bs-slide="prev" class="carousel-control-prev" aria-hidden="true"></span>
            <span class="visually-hidden"></span>
            <span class="carousel-control-next-icon" aria-hidden="true" data-bs-target="#suggestionCarousel" data-bs-slide="next"></span>
            <span class="visually-hidden"></span>
        </button>
    </div>
    <?php else: ?>
        <p class="text-center">Aucune suggestion pour le moment.</p>
    <?php endif; ?>
</div>


</main>
<?php require_once __DIR__.'/../footer.php'; ?>