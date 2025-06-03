<?php 
// On démarre une session
session_start();
$page = 'search';
$titre = "Recherche";

try {
    $bdd = new PDO("mysql:host=localhost;dbname=db_market", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $bdd->query("SET NAMES UTF8");
} catch(Exception $e) {
    die("Une erreur a été trouvée : " . $e->getMessage());
}

$produits = [];

if (isset($_GET["s"]) && $_GET["s"] == "Rechercher") 
    {$terme = htmlspecialchars(trim(strip_tags($_GET["terme"])));

    // Supprimer les tirets, apostrophes, espaces, etc.
    $terme = str_replace(['-', '’', "'", ' '], '', $terme);
    
    // Supprimer les accents (ex: canapé => canape)
    $terme = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $terme);
    
    // Mettre en minuscule
    $terme = strtolower($terme);
    

    if (isset($_GET['terme'])) {
        $terme = trim($_GET['terme']);
        if ($terme !== '') {
            // Recherche filtrée
            $requete = $bdd->prepare("
                SELECT * FROM tbl_produit 
                WHERE 
                LOWER(REPLACE(REPLACE(REPLACE(REPLACE(nom_produit, '-', ''), ' ', ''), '''', ''), '’', '')) 
                LIKE ?
            ");
            $requete->execute(["%".strtolower($terme)."%"]);
            $produits = $requete->fetchAll();
    
            if (count($produits) === 0) {
                // Aucun résultat : on affiche tous les produits
                $requete = $bdd->query("SELECT * FROM tbl_produit");
                $produits = $requete->fetchAll();
                $message = "Aucun produit trouvé pour \"".htmlspecialchars($terme)."\". Voici d'autres articles disponibles :";
            }
        } else {
            // Champ vide : on affiche tout
            $requete = $bdd->query("SELECT * FROM tbl_produit");
            $produits = $requete->fetchAll();
        }
    } else {
        // Pas de recherche : on affiche tout
        $requete = $bdd->query("SELECT * FROM tbl_produit");
        $produits = $requete->fetchAll();
    }  
}

if (empty($produits) && !empty($terme)) {
    // Récupère tous les produits pour comparer
    $allProducts = $bdd->query("SELECT * FROM tbl_produit")->fetchAll();
    $suggested = [];

    foreach ($allProducts as $prod) {
        // Nettoie le nom du produit comme tu as nettoyé le terme
        $nomClean = $prod['nom_produit'];
        $nomClean = str_replace(['-', '’', "'", ' '], '', $nomClean);
        $nomClean = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nomClean);
        $nomClean = strtolower($nomClean);

        // Calcule la distance de Levenshtein
        $distance = levenshtein($terme, $nomClean);

        // Si la distance est faible, on considère que c'est une suggestion
        if ($distance <= 2) {
            $suggested[] = $prod;
        }
    }

    // Si on a des suggestions, on les affiche à la place du message d'erreur
    if (!empty($suggested)) {
        $produits = $suggested;
        $message = "Aucun résultat exact, mais voici des produits similaires à \"{$_GET["terme"]}\" :";
    }
}

?>
<?php include('../head.php'); ?>
<?php include('../../nav.php'); ?>
<br><br><br><br><br>
<main class="container-fluid menu">
    <div class="row">
        <section class="col-12">
            <h1 style="background-color:#FEBF5B;color:black;display:inline;border-radius:5px;padding:10px;">
                <?= $titre ?>
            </h1>
            <br><br>
            <hr>

            <!-- Affichage du message d'information -->
            <?php if (isset($message)): ?>
                <div class="col-12">
                    <p><?= $message ?></p>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Affichage des produits trouvés -->
                <?php if (!empty($produits)): ?>
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
                                    <a href="../details.php?id=<?= $produit['id_produit'] ?>" class="btn btn-sm btn-info">Voir</a>
                                    <?php if (isset($_SESSION['role_user']) && $_SESSION['role_user'] == 0): ?>
                                        <a href="../add_to_cart.php?id=<?= $produit['id_produit'] ?>" class="btn btn-success btn-sm">Ajouter à mon Panier</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php elseif (isset($_GET["s"]) && empty($produits) && empty($message)): ?>
                    <div class="col-12">
                        <p>Aucun produit trouvé pour "<?= isset($_GET["terme"]) ? htmlspecialchars($_GET["terme"]) : '' ?>"</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>
<?php include('../../footer.php'); ?>