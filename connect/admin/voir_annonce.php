<?php
include '../connect.php';

if (!isset($_GET['id'])) {
    echo "Aucune annonce spécifiée.";
    exit;
}

$id = $_GET['id'];

// Récupère les infos de l’annonce
$stmt = $db->prepare("
    SELECT tbl_produit.*, tbl_users.nom_user AS utilisateur 
    FROM tbl_produit 
    JOIN tbl_users ON tbl_produit.vendeur_id = tbl_users.id_user 
    WHERE id_produit = ?
");
$stmt->execute([$id]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$annonce) {
    echo "Annonce introuvable.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'annonce</title>
    <link rel="icon" href="http://localhost/marketplace/uploads/icon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h1>Détails de l'annonce</h1>
        <ul class="list-group">
        <?php if (!empty($annonce['photo_produit'])): ?>
            <?php
                $photo = $annonce['photo_produit'];
                // Si c'est une URL complète, on l'utilise telle quelle, sinon on la fait précéder du dossier local
                $isUrl = filter_var($photo, FILTER_VALIDATE_URL);
                $src = $isUrl ? $photo : '../uploads/' . $photo;
            ?>
            <div class="mb-4">
                <img src="<?= htmlspecialchars($src) ?>" alt="Photo du produit" class="img-fluid rounded shadow" style="max-width: 400px;">
            </div>
        <?php else: ?>
            <p class="text-muted">Aucune photo disponible.</p>
        <?php endif; ?>
            <li class="list-group-item"><strong>ID :</strong> <?= $annonce['id_produit'] ?></li>
            <li class="list-group-item"><strong>Nom :</strong> <?= htmlspecialchars($annonce['nom_produit']) ?></li>
            <li class="list-group-item"><strong>Description :</strong> <?= nl2br(htmlspecialchars($annonce['desc_produit'])) ?></li>
            <li class="list-group-item"><strong>Catégorie :</strong> <?= htmlspecialchars($annonce['category_id']) ?></li>
            <li class="list-group-item"><strong>Statut :</strong> <?= $annonce['active_produit'] ? 'Publiée' : 'En attente' ?></li>
            <li class="list-group-item"><strong>Vendeur :</strong> <?= htmlspecialchars($annonce['utilisateur']) ?></li>
            <li class="list-group-item"><strong>Date de création :</strong> <?= $annonce['createdate_produit'] ?></li>
        </ul>
        <a href="javascript:history.back()" class="btn btn-secondary mt-3">Retour</a>
        <a class="btn btn-danger btn-sm" href="supprimer_annonce.php?id=<?= $annonce['id_produit'] ?>">Supprimer</a>
                    
    </div>
</body>
</html>
