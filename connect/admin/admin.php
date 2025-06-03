<?php
session_start();
include '../connect.php';

// --- Filtrage utilisateurs ---
$filtre = isset($_GET['statut']) ? $_GET['statut'] : 'tous';

function getUsersByRoleAndStatus($db, $role, $filtre) {
    $sql = "SELECT * FROM tbl_users WHERE role_user = :role";
    if ($filtre === 'actifs') {
        $sql .= " AND active_user = 1";
    } elseif ($filtre === 'inactifs') {
        $sql .= " AND active_user = 0";
    }
    $stmt = $db->prepare($sql);
    $stmt->execute(['role' => $role]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$vendeurs = getUsersByRoleAndStatus($db, 1, $filtre);
$acheteurs = getUsersByRoleAndStatus($db, 0, $filtre);
$admins = getUsersByRoleAndStatus($db, 2, $filtre);

// Récupération des catégories actives
$categories = $db->query("SELECT id_category, nom_category FROM tbl_category WHERE active_category = 1")->fetchAll(PDO::FETCH_ASSOC);

$annoncesParCategorie = [];

foreach ($categories as $categorie) {
    $catId = $categorie['id_category'];
    $catNom = $categorie['nom_category'];

    $stmt = $db->prepare("
        SELECT tbl_produit.*, tbl_users.prenom_user AS utilisateur
        FROM tbl_produit
        JOIN tbl_users ON tbl_produit.vendeur_id = tbl_users.id_user
        WHERE category_id = ?
    ");
    $stmt->execute([$catId]);
    $annoncesParCategorie[$catNom] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Statistiques ---
$totalUsers = $db->query("SELECT COUNT(*) FROM tbl_users")->fetchColumn();
$actifs = $db->query("SELECT COUNT(*) FROM tbl_users WHERE active_user = 1")->fetchColumn();
$inactifs = $db->query("SELECT COUNT(*) FROM tbl_users WHERE active_user = 0")->fetchColumn();
$totalAnnonces = $db->query("SELECT COUNT(*) FROM tbl_produit")->fetchColumn();
$enAttente = $db->query("SELECT COUNT(*) FROM tbl_produit WHERE active_produit = 0")->fetchColumn();
$publiées = $db->query("SELECT COUNT(*) FROM tbl_produit WHERE active_produit = 1")->fetchColumn();

// --- Fonction d'affichage utilisateurs par rôle ---
function afficherTableauUtilisateurs($utilisateurs, $titre) {
    ?>
    <h3 class="mb-3"><i class="bi bi-people-fill text-primary me-2"></i><?= $titre ?></h3>
        <div class="table-responsive mb-4">
        <table class="table align-middle table-hover shadow-sm rounded">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Date d'inscription</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($utilisateurs as $user): ?>
            <tr>
                <td><?= $user['id_user'] ?></td>
                <td><?= htmlspecialchars($user['nom_user']) ?></td>
                <td><?= htmlspecialchars($user['mail_user']) ?></td>
                <td><?= $user['createdate_user'] ?></td>
                <td>
                <span class="badge bg-<?= $user['active_user'] ? 'success' : 'secondary' ?>">
                    <?= $user['active_user'] ? 'Actif' : 'Inactif' ?>
                </span>
                </td>
                <td>
                <div class="d-flex flex-wrap gap-1">
                    <?php if ($user['active_user']): ?>
                    <a class="btn btn-warning btn-sm d-flex align-items-center" href="toggle_user_status.php?id=<?= $user['id_user'] ?>">
                        <i class="bi bi-pause-fill me-1"></i>Suspendre
                    </a>
                    <?php else: ?>
                    <a class="btn btn-success btn-sm d-flex align-items-center" href="toggle_user_status.php?id=<?= $user['id_user'] ?>">
                        <i class="bi bi-play-fill me-1"></i>Réactiver
                    </a>
                    <?php endif; ?>
                    <a class="btn btn-danger btn-sm d-flex align-items-center" href="supprimer_user.php?id=<?= $user['id_user'] ?>">
                    <i class="bi bi-trash-fill me-1"></i>Supprimer
                    </a>
                    <form method="post" action="changer_role.php" class="d-inline">
                    <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                    <select name="role_user" class="form-select form-select d-inline w-auto" onchange="this.form.submit()">
                        <option value="0" <?= $user['role_user'] == 0 ? 'selected' : '' ?>>Acheteur</option>
                        <option value="1" <?= $user['role_user'] == 1 ? 'selected' : '' ?>>Vendeur</option>
                        <option value="2" <?= $user['role_user'] == 2 ? 'selected' : '' ?>>Admin</option>
                    </select>
                    </form>
                </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php
}

function afficherTableauAnnoncesParCategorie($annonces, $categorie) {
    ?>
    <h3 class="mt-5 mb-3"><i class="bi bi-megaphone-fill text-warning me-2"></i>Annonces - <?= ucfirst(htmlspecialchars($categorie)) ?></h3>
        <div class="table-responsive mb-4">
        <table class="table align-middle table-hover shadow-sm rounded">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Utilisateur</th>
                <th>Statut</th>
                <th>Date de création</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($annonces as $annonce): ?>
            <tr>
                <td><?= $annonce['id_produit'] ?></td>
                <td><?= htmlspecialchars($annonce['nom_produit']) ?></td>
                <td><?= htmlspecialchars($annonce['utilisateur']) ?></td>
                <td>
                <span class="badge bg-<?= $annonce['active_produit'] ? 'primary' : 'warning' ?>">
                    <?= $annonce['active_produit'] ? 'Publiée' : 'En attente' ?>
                </span>
                </td>
                <td><?= $annonce['createdate_produit'] ?></td>
                <td>
                <div class="d-flex flex-wrap gap-1">
                    <a class="btn btn-info btn-sm d-flex align-items-center" href="voir_annonce.php?id=<?= $annonce['id_produit'] ?>">
                    <i class="bi bi-eye-fill me-1"></i>Voir
                    </a>
                    <?php if ($annonce['active_produit']): ?>
                    <a class="btn btn-warning btn-sm d-flex align-items-center" href="toggle_annonce_status.php?id=<?= $annonce['id_produit'] ?>">
                        <i class="bi bi-pause-fill me-1"></i>Désactiver
                    </a>
                    <?php else: ?>
                    <a class="btn btn-success btn-sm d-flex align-items-center" href="toggle_annonce_status.php?id=<?= $annonce['id_produit'] ?>">
                        <i class="bi bi-play-fill me-1"></i>Activer
                    </a>
                    <?php endif; ?>
                    <a class="btn btn-danger btn-sm d-flex align-items-center" href="supprimer_annonce.php?id=<?= $annonce['id_produit'] ?>">
                    <i class="bi bi-trash-fill me-1"></i>Supprimer
                    </a>
                </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/marketplace/uploads/icon.png" type="image/png">
    <title>Admin - Vendeo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="http://localhost/marketplace/uploads/icon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body class="p-4">
<a href="compteadmin.php" class="btn btn-secondary mt-3">⬅ Retour à l'administration</a>

    <div class="container">
        <h1 class="mb-4">Administration - Vendeo</h1>

        <!-- 🔢 Bandeau statistiques moderne -->
        <div class="row mb-4 g-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                <i class="bi bi-people-fill fs-2 text-primary"></i>
                </div>
                <div>
                <h6 class="mb-1 fw-bold">Utilisateurs</h6>
                <div class="fw-bold fs-4"><?= $totalUsers ?></div>
                <small class="text-success">Actifs : <?= $actifs ?></small><br>
                <small class="text-muted">Inactifs : <?= $inactifs ?></small>
                </div>
            </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                <i class="bi bi-megaphone-fill fs-2 text-warning"></i>
                </div>
                <div>
                <h6 class="mb-1 fw-bold">Annonces</h6>
                <div class="fw-bold fs-4"><?= $totalAnnonces ?></div>
                <small class="text-warning">En attente : <?= $enAttente ?></small><br>
                <small class="text-primary">Publiées : <?= $publiées ?></small>
                </div>
            </div>
            </div>
        </div>
        </div>

        <!-- 👥 Tableaux utilisateurs -->
         <h2><u>Gestion des Utilisateurs</u></h2><br>
         

        <!-- 🔍 Filtrer les utilisateurs -->
        <form method="get" class="mb-4">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                <label for="statut" class="col-form-label fw-bold">
                    <i class="bi bi-funnel-fill text-primary me-2"></i>
                    Filtrer les utilisateurs :
                </label>
                </div>
                <div class="col-auto">
                <select class="form-select shadow-sm border-primary" name="statut" id="statut" onchange="this.form.submit()" style="min-width: 140px;">
                    <option value="tous" <?= $filtre === 'tous' ? 'selected' : '' ?>>Tous</option>
                    <option value="actifs" <?= $filtre === 'actifs' ? 'selected' : '' ?>>Actifs</option>
                    <option value="inactifs" <?= $filtre === 'inactifs' ? 'selected' : '' ?>>Inactifs</option>
                </select>
                </div>
            </div>
        </form>
        <?php
            afficherTableauUtilisateurs($vendeurs, 'Vendeurs');
            afficherTableauUtilisateurs($acheteurs, 'Acheteurs');
            afficherTableauUtilisateurs($admins, 'Administrateurs');
        ?>
        <br>
        <!-- 📦 Tableau annonces -->
       <!-- 📦 Tableaux des annonces par catégorie -->
        <h2><u>Gestion des Annonces</u></h2>
        <?php
        foreach ($annoncesParCategorie as $categorie => $annonces) {
            afficherTableauAnnoncesParCategorie($annonces, $categorie);
        }
        ?>
    </div>
</body>
</html>
