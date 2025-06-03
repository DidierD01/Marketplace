<?php
session_start();
$page = 'addproduct';
$titre = "Ajouter un produit";

require_once('../connect.php');

// Vérification de connexion
if (!isset($_SESSION['id_user'])) {
    header('Location: ../connect.php');
    exit();
}

// Vérification du rôle vendeur
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

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['nom_produit', 'prix_produit', 'category_id', 'desc_produit', 'stock_produit'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['erreur'] = "Le champ " . str_replace('_', ' ', $field) . " est requis";
            header('Location: add.php');
            exit();
        }
    }

    $nom_produit = strip_tags($_POST['nom_produit']);
    $desc_produit = strip_tags($_POST['desc_produit']);
    $prix_produit = (float)$_POST['prix_produit'];
    $stock_produit = (int)$_POST['stock_produit'];
    $category_id = (int)$_POST['category_id'];
    $current_date = date('Y-m-d H:i:s');

    // Gestion de l'image
    $photo_produit = null;
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Option 1 : URL d'image
    if (!empty($_POST['image_url'])) {
        $image_url = filter_var($_POST['image_url'], FILTER_VALIDATE_URL);
        if ($image_url) {
            $url_extension = strtolower(pathinfo(parse_url($image_url, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (in_array($url_extension, $allowed_extensions)) {
                $photo_produit = $image_url;
            } else {
                $_SESSION['erreur'] = "L'URL doit pointer vers une image (JPG, PNG, GIF, WEBP)";
                header('Location: add.php');
                exit();
            }
        } else {
            $_SESSION['erreur'] = "URL d'image invalide";
            header('Location: add.php');
            exit();
        }
    }
    // Option 2 : upload d'image
    elseif (isset($_FILES['photo_produit']) && $_FILES['photo_produit']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/marketplace/uploads/article/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $tmpName = $_FILES['photo_produit']['tmp_name'];
        $originalName = $_FILES['photo_produit']['name'];
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $safeFileName = preg_replace('/[^a-zA-Z0-9-_\.]/', '', pathinfo($originalName, PATHINFO_FILENAME));
        $uniqueName = uniqid('prod_', true) . '_' . $safeFileName . '.' . $fileExtension;
        $uploadPath = $uploadDir . $uniqueName;

        if (in_array($fileExtension, $allowed_extensions)) {
            if ($_FILES['photo_produit']['size'] > 2097152) {
                $_SESSION['erreur'] = "Le fichier est trop volumineux (max 2MB)";
                header('Location: add.php');
                exit();
            }
            if (move_uploaded_file($tmpName, $uploadPath)) {
                $photo_produit = '/marketplace/uploads/article/' . $uniqueName;
            } else {
                $_SESSION['erreur'] = "Erreur lors de l'enregistrement de l'image";
                header('Location: add.php');
                exit();
            }
        } else {
            $_SESSION['erreur'] = "Format non autorisé. Formats valides: " . implode(', ', $allowed_extensions);
            header('Location: add.php');
            exit();
        }
    } else {
        $_SESSION['erreur'] = "Vous devez soit fournir une URL d'image, soit uploader un fichier image";
        header('Location: add.php');
        exit();
    }

    // Insertion en BDD
    try {
        $sql = 'INSERT INTO tbl_produit 
            (vendeur_id, nom_produit, desc_produit, prix_produit, photo_produit, category_id, stock_produit, createdate_produit, majdate_produit) 
            VALUES 
            (:vendeur_id, :nom_produit, :desc_produit, :prix_produit, :photo_produit, :category_id, :stock_produit, :createdate, :majdate)';
        $query = $db->prepare($sql);
        $query->execute([
            ':vendeur_id' => $id_user,
            ':nom_produit' => $nom_produit,
            ':desc_produit' => $desc_produit,
            ':prix_produit' => $prix_produit,
            ':photo_produit' => $photo_produit,
            ':category_id' => $category_id,
            ':stock_produit' => $stock_produit,
            ':createdate' => $current_date,
            ':majdate' => $current_date
        ]);
        $_SESSION['success'] = "Produit ajouté avec succès";
        header('Location: article.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['erreur'] = "Erreur technique: " . $e->getMessage();
        header('Location: add.php');
        exit();
    }
}

// Récupération des catégories
$categories = $db->query("SELECT * FROM tbl_category WHERE active_category = 1")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="http://localhost/marketplace/uploads/icon.png" type="image/png">
    <title>Ajouter un produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/marketplace/css/connect.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 100px auto;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .image-option {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .option-divider {
            text-align: center;
            margin: 15px 0;
            position: relative;
        }
        .option-divider::before, .option-divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #dee2e6;
            margin: auto;
        }
        .option-divider span {
            padding: 0 10px;
        }
    </style>
</head>
<body>
    <?php include('../../nav.php'); ?>
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4">Ajouter un nouveau produit</h2>
            <?php if(isset($_SESSION['erreur'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['erreur']; unset($_SESSION['erreur']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom du produit *</label>
                        <input type="text" name="nom_produit" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prix (€) *</label>
                        <input type="number" name="prix_produit" class="form-control" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea name="desc_produit" class="form-control" rows="3" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stock disponible *</label>
                        <input type="number" name="stock_produit" class="form-control" min="0" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Catégorie *</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Choisir...</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id_category'] ?>"><?= $cat['nom_category'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Image du produit *</label>
                    <div class="image-option">
                        <h5>Option 1 : URL de l'image</h5>
                        <input type="url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
                        <small class="text-muted">Doit être une URL directe vers une image (JPG, PNG, GIF, WEBP)</small>
                    </div>
                    <div class="option-divider">
                        <span>OU</span>
                    </div>
                    <div class="image-option">
                        <h5>Option 2 : Téléverser une image</h5>
                        <input type="file" name="photo_produit" class="form-control" accept="image/*">
                        <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WEBP (Taille max: 2MB)</small>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="article.php" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-warning">Ajouter le produit</button>
                </div>
            </form>
        </div>
    </div>
    <?php include('../../footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Désactiver l'autre option quand une est sélectionnée
        document.querySelector('input[name="image_url"]').addEventListener('input', function() {
            document.querySelector('input[name="photo_produit"]').disabled = this.value.trim() !== '';
        });
        document.querySelector('input[name="photo_produit"]').addEventListener('change', function() {
            document.querySelector('input[name="image_url"]').disabled = this.files.length > 0;
        });
    </script>
</body>
</html>
