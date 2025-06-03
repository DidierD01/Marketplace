<?php
session_start();
$page = 'paiement';
$titre = "Paiement de la commande";

// Vérification de la connexion et du rôle
if (!isset($_SESSION['id_user']) || $_SESSION['role_user'] !== 0) {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

require_once('../connect.php');

// Récupérer les articles du panier pour affichage et calcul du total
$stmt = $db->prepare("
    SELECT pr.nom_produit, p.quantity_panier, pr.prix_produit, p.produit_id
    FROM tbl_panier p
    JOIN tbl_produit pr ON p.produit_id = pr.id_produit
    WHERE p.client_id = ?
");
$stmt->execute([$id_user]);
$produits_panier = $stmt->fetchAll();

$total = 0;
foreach ($produits_panier as $item) {
    $total += $item['prix_produit'] * $item['quantity_panier'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($produits_panier) || $total <= 0) {
        $_SESSION['error'] = "Votre panier est vide, vous ne pouvez pas valider la commande";
        header("Location: paiement.php");
        exit;
    }

    // Validation des données de paiement
    $required_fields = [
        'nom_paiement', 
        'rue',
        'numero',
        'cp',
        'localité', 
        'email_paiement',
        'carte_numero',
        'carte_expiration',
        'carte_cvc'
    ];
    $boite_lettres = trim($_POST['boite_lettres'] ?? '');
    $boite_chiffres = trim($_POST['boite_chiffres'] ?? '');

    if ($boite_lettres !== '' && !preg_match('/^[A-Za-z]{1,3}$/', $boite_lettres)) {
        $_SESSION['error'] = "Le champ Boite lettres est invalide";
        header("Location: paiement.php");
        exit;
    }
    if ($boite_chiffres !== '' && !preg_match('/^\d{1,4}$/', $boite_chiffres)) {
        $_SESSION['error'] = "Le champ Boite chiffres est invalide";
        header("Location: paiement.php");
        exit;
    }
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Le champ " . str_replace('_', ' ', $field) . " est requis";
            header("Location: paiement.php");
            exit;
        }
    }

    try {
        // 1. Insertion de la commande
        $stmt_order = $db->prepare("
            INSERT INTO tbl_order 
            (acheteur_id, total_order, createdate_order, statut_order) 
            VALUES (?, ?, NOW(), 'En attente')
        ");
        $stmt_order->execute([$id_user, $total]);
        $order_id = $db->lastInsertId();

        // 2. Insertion des articles de la commande + mise à jour du stock
        foreach ($produits_panier as $item) {
            $stmt_items = $db->prepare("
                INSERT INTO tbl_order_items 
                (order_id, produit_id, quantity, prix) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt_items->execute([
                $order_id,
                $item['produit_id'],
                $item['quantity_panier'],
                $item['prix_produit']
            ]);
            $stmt_update = $db->prepare("
                UPDATE tbl_produit 
                SET stock_produit = stock_produit - ? 
                WHERE id_produit = ?
            ");
            $stmt_update->execute([$item['quantity_panier'], $item['produit_id']]);
        }

        // 3. Vider le panier
        $stmt_delete = $db->prepare("DELETE FROM tbl_panier WHERE client_id = ?");
        $stmt_delete->execute([$id_user]);
        unset($_SESSION['cart']);

        // 4. Redirection vers confirmation
        header("Location: confirmation.php?order_id=$order_id");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors du traitement de la commande: " . $e->getMessage();
        header("Location: paiement.php");
        exit;
    }
}
?>

<?php include('../head.php'); ?>
<?php include('../../nav.php'); ?>

<div class="container" style="padding-top: 100px;">
    <h1>Paiement de la commande</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5>Récapitulatif de la commande</h5>
        </div>
        <div class="card-body">
    <p>Total à payer: <strong><?= number_format($total, 2) ?> €</strong></p>
    <!-- AJOUTE ICI LE TABLEAU DES ARTICLES -->
    <?php if (!empty($produits_panier)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Sous-total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produits_panier as $prod): ?>
                    <tr>
                        <td><?= htmlspecialchars($prod['nom_produit']) ?></td>
                        <td><?= (int)$prod['quantity_panier'] ?></td>
                        <td><?= number_format($prod['prix_produit'], 2) ?> €</td>
                        <td><?= number_format($prod['prix_produit'] * $prod['quantity_panier'], 2) ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Votre panier est vide.</div>
    <?php endif; ?>
</div>
    </div>

    <form method="POST" action="paiement.php">
        <h4>Informations de paiement</h4>
        <br>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="nom_paiement" class="form-label">Nom complet</label>
                    <input type="text" name="nom_paiement" placeholder="Prénom Nom" class="form-control" id="nom_paiement" pattern="^[A-Za-zÀ-ÿ]+(?:[- ][A-Za-zÀ-ÿ]+)*$" title="Le nom ne peut contenir que des lettres, un seul espace ou tiret entre les mots." required>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="email_paiement" class="form-label">Email</label>
                    <input type="email" name="email_paiement" placeholder="exemple@domaine.com" class="form-control" id="email_paiement" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="rue" class="form-label">Rue</label>
                    <input type="text" name="rue" class="form-control" placeholder="Adresse" id="rue" pattern="^[A-Za-zÀ-ÿ\s\-]+$" title="La rue ne doit contenir que des lettres." required>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group mb-3">
                    <label for="numero" class="form-label">N°</label>
                    <input type="text" name="numero" class="form-control" id="numero" placeholder="Ex: 123" pattern="\d+" inputmode="numeric" required>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group mb-3">
                    <label class="form-label">Boite</label>
                    <div class="d-flex gap-2">
                        <input type="text" name="boite_lettres" class="form-control" maxlength="3" placeholder="A, B..." pattern="[A-Za-z]*" title="Lettres uniquement">
                        <input type="text" name="boite_chiffres" class="form-control" maxlength="4" placeholder="123..." pattern="[0-9]*" title="Chiffres uniquement">
                    </div>
                </div>
            </div>

            <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="cp" class="form-label">Code Postal</label>
                    <input type="text" name="cp" class="form-control" id="cp" placeholder="1234" maxlength="4" pattern="\d{4}" inputmode="numeric" required>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="localité" class="form-label">Localité</label>
                    <input type="text" name="localité" placeholder="Ville" class="form-control" id="localité" pattern="[A-Za-zÀ-ÿ\s\-]+" title="Seules les lettres sont autorisées" required>
                </div>
            </div>
        </div>
        <br>
        <div class="card mb-3">
            <div class="card-header">
                <h5>Carte de crédit <i class="fa-solid fa-credit-card"></i></h5>
            </div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="carte_numero" class="form-label">Numéro de carte</label>
                    <input type="text" maxlength="16" pattern="\d{16}" name="carte_numero" class="form-control" id="carte_numero" placeholder="1234123412341234" required>
                </div>
                <div class="row">
                    <?php
                    $mois_actuel = date('Y-m');
                    ?>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="carte_expiration" class="form-label">Date d'expiration</label>
                            <input type="month" name="carte_expiration" class="form-control" id="carte_expiration" min="<?= $mois_actuel ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="carte_cvc" class="form-label">CVC</label>
                            <input type="text" name="carte_cvc" class="form-control" id="carte_cvc" placeholder="123" maxlength="3" pattern="\d{3}" inputmode="numeric" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!empty($produits_panier) && $total > 0): ?>
            <div class="d-grid gap-2" style="margin-bottom:80px">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check-circle"></i> Payer <?= number_format($total, 2) ?> €
                </button>
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-2">
                Vous ne pouvez pas valider une commande vide.
            </div>
            <div class="d-grid gap-2" style="margin-bottom:80px">
                <a href="../../category/all.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-cart"></i> Continuer vos achats
                </a>
            </div>
        <?php endif; ?>
    </form>
</div>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    if (<?= empty($produits_panier) ? 'true' : 'false' ?>) {
        e.preventDefault();
        alert('Votre panier est vide, vous ne pouvez pas valider la commande');
    }
});
</script>
<?php include('../footer.php'); ?>