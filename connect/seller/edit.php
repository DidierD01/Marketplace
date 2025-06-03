<?php
// On démarre la session
session_start();

// Vérification de la connexion
if (!isset($_SESSION['id_user'])) {
    header('Location: ../connect.php');
    exit();
}

// Connexion à la DB
require_once('../connect.php');

// Vérification du rôle de vendeur
$id_user = $_SESSION['id_user'];
$sql = "SELECT role_user FROM tbl_users WHERE id_user = :id_user";
$query = $db->prepare($sql);
$query->bindValue(':id_user', $id_user, PDO::PARAM_INT);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

if ($user['role_user'] != 1) {
    $_SESSION['erreur'] = "Accès réservé aux vendeurs";
    header('Location: ../index.php');
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des champs requis
    $required_fields = ['nom_produit', 'prix_produit', 'category_id', 'desc_produit', 'stock_produit'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['erreur'] = "Le champ " . str_replace('_', ' ', $field) . " est requis";
            header('Location: edit.php?id=' . $_POST['id_produit']);
            exit();
        }
    }

    // Récupération et nettoyage des données
    $id_produit    = strip_tags($_POST['id_produit']);
    $nom_produit   = strip_tags($_POST['nom_produit']);
    $desc_produit  = strip_tags($_POST['desc_produit']);
    $prix_produit  = (float) $_POST['prix_produit'];
    $category_id   = (int) $_POST['category_id'];
    $stock_produit = (int) $_POST['stock_produit'];
    $current_date  = date('Y-m-d H:i:s');

    // Récupération du produit existant pour conserver l'image si aucune nouvelle image n'est fournie
    $sql = "SELECT * FROM tbl_produit WHERE id_produit = :id_produit AND vendeur_id = :vendeur_id";
    $query = $db->prepare($sql);
    $query->bindValue(':id_produit', $id_produit, PDO::PARAM_INT);
    $query->bindValue(':vendeur_id', $id_user, PDO::PARAM_INT);
    $query->execute();
    $produit = $query->fetch(PDO::FETCH_ASSOC);
    if (!$produit) {
        $_SESSION['erreur'] = "Produit non trouvé ou accès non autorisé";
        header('Location: article.php');
        exit();
    }

    // Gestion de l'image : par défaut on conserve l'image existante
    $photo_produit = $produit['photo_produit'];
    // Option 1 : URL d'image fournie et valide
    if (!empty($_POST['image_url']) && filter_var($_POST['image_url'], FILTER_VALIDATE_URL)) {
        $photo_produit = $_POST['image_url'];
    }
    // Option 2 : Upload d'un nouveau fichier image
    elseif (isset($_FILES['photo_produit']) && $_FILES['photo_produit']['error'] === UPLOAD_ERR_OK) {
        $uploadDirectory = "../uploads/";
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }
        
        $fileName    = uniqid() . '_' . basename($_FILES['photo_produit']['name']);
        $fileTmpName = $_FILES['photo_produit']['tmp_name'];
        $uploadPath  = $uploadDirectory . $fileName;
        
        // Vérification du type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType     = mime_content_type($fileTmpName);
        
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                $photo_produit = 'http://localhost/marketplace/uploads/' . $fileName;
            } else {
                $_SESSION['erreur'] = "Erreur lors de l'upload du fichier";
                header('Location: edit.php?id=' . $id_produit);
                exit();
            }
        } else {
            $_SESSION['erreur'] = "Type de fichier non autorisé. Seuls JPEG, PNG et GIF sont acceptés";
            header('Location: edit.php?id=' . $id_produit);
            exit();
        }
    }

    // Mise à jour du produit dans la base
    $sql = "UPDATE tbl_produit SET 
                nom_produit    = :nom_produit,
                desc_produit   = :desc_produit,
                prix_produit   = :prix_produit,
                photo_produit  = :photo_produit,
                category_id    = :category_id,
                stock_produit  = :stock_produit,
                majdate_produit = :majdate
            WHERE id_produit = :id_produit AND vendeur_id = :vendeur_id";
    
    $query = $db->prepare($sql);
    $query->execute([
        ':nom_produit'    => $nom_produit,
        ':desc_produit'   => $desc_produit,
        ':prix_produit'   => $prix_produit,
        ':photo_produit'  => $photo_produit,
        ':category_id'    => $category_id,
        ':stock_produit'  => $stock_produit,
        ':majdate'        => $current_date,
        ':id_produit'     => $id_produit,
        ':vendeur_id'     => $id_user
    ]);

    $_SESSION['success'] = "Produit modifié avec succès";
    header('Location: article.php');
    exit();
}

// Récupération du produit à modifier via l'URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_produit = strip_tags($_GET['id']);
    
    $sql = "SELECT * FROM tbl_produit WHERE id_produit = :id_produit AND vendeur_id = :vendeur_id";
    $query = $db->prepare($sql);
    $query->bindValue(':id_produit', $id_produit, PDO::PARAM_INT);
    $query->bindValue(':vendeur_id', $id_user, PDO::PARAM_INT);
    $query->execute();
    $produit = $query->fetch(PDO::FETCH_ASSOC);
    
    if (!$produit) {
        $_SESSION['erreur'] = "Produit non trouvé ou accès non autorisé";
        header('Location: article.php');
        exit();
    }
} else {
    $_SESSION['erreur'] = "URL invalide";
    header('Location: article.php');
    exit();
}

// Récupération des catégories pour le select
$categories = $db->query("SELECT * FROM tbl_category WHERE active_category = 1")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="http://localhost/marketplace/uploads/icon.png" type="image/png">
    <title>Modifier un produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4">Modifier le produit</h2>
            
            <?php if(isset($_SESSION['erreur'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['erreur']; unset($_SESSION['erreur']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <!-- Champ caché pour l'id du produit -->
                <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom du produit *</label>
                        <input type="text" name="nom_produit" class="form-control" required
                               value="<?= htmlspecialchars($produit['nom_produit']) ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prix (€) *</label>
                        <input type="number" name="prix_produit" class="form-control" min="0" step="0.01" required
                               value="<?= htmlspecialchars($produit['prix_produit']) ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea name="desc_produit" class="form-control" rows="3" required><?= htmlspecialchars($produit['desc_produit']) ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stock disponible *</label>
                        <input type="number" name="stock_produit" class="form-control" min="0" required
                               value="<?= htmlspecialchars($produit['stock_produit']) ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Catégorie *</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Choisir...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id_category'] ?>"
                                    <?= ($cat['id_category'] == $produit['category_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nom_category']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Image du produit</label>
                    <div class="card p-3">
                        <div class="mb-3">
                            <label class="form-label">Option 1 : URL de l'image</label>
                            <input type="url" name="image_url" class="form-control"
                                   placeholder="https://example.com/image.jpg">
                        </div>
                        <div class="text-center my-2">OU</div>
                        <div>
                            <label class="form-label">Option 2 : Téléverser une image</label>
                            <input type="file" name="photo_produit" class="form-control" accept="image/*">
                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF (max 2MB)</small>
                        </div>
                        <?php if (!empty($produit['photo_produit'])): ?>
                            <div class="mt-3">
                                <img src="<?= $produit['photo_produit'] ?>" alt="Image du produit" style="max-width: 200px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="article.php" class="btn btn-outline-secondary">Retour aux Produits</a>
                    <button type="submit" class="btn btn-primary">Modifier le produit</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
