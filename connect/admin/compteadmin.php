<?php
session_start();
$page = 'adminaccount';
$titre = "Mon Profil";

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_market";

// Connexion BDD
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$user_id = $_SESSION['id_user'];

// Récupération des infos utilisateur
$sql_user = "SELECT id_user, nom_user, prenom_user, mail_user, phone_user, birthday_user, avatar_user FROM tbl_users WHERE id_user = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
} else {
    die("Erreur : utilisateur introuvable.");
}
$stmt_user->close();

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
    $avatar = $user['avatar_user'];

    if (!empty($_FILES['avatar']['tmp_name'])) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/marketplace/uploads/';
        $fileExtension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $uniqueFileName = time() . '_' . uniqid() . '_' . $user_id . '.' . $fileExtension;
        $uploadFile = $uploadDir . $uniqueFileName;

        if ($_FILES["avatar"]["size"] > 5000000) {
            die("Erreur : L'image est trop grande.");
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            die("Erreur : Seules les images JPG, JPEG et PNG sont autorisées.");
        }

        if (!empty($user['avatar_user'])) {
            $oldAvatarPath = $uploadDir . $user['avatar_user'];
            if (file_exists($oldAvatarPath)) {
                unlink($oldAvatarPath);
            }
        }

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadFile)) {
            $avatar = $uniqueFileName;
        } else {
            echo "Erreur lors du téléchargement de l'image.";
        }
    }

    $sql = "UPDATE tbl_users SET 
        nom_user = ?, prenom_user = ?, mail_user = ?, phone_user = ?, birthday_user = ?, modifdate_user = NOW()";
    $params = [$nom, $prenom, $email, $phone, $birthday];
    $types = "ssssss";

    if ($password) {
        $sql .= ", password_user = ?";
        $params[] = $password;
        $types .= "s";
    }

    if ($avatar) {
        $sql .= ", avatar_user = ?";
        $params[] = $avatar;
        $types .= "s";
    }

    $sql .= " WHERE id_user = ?";
    $params[] = $user_id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo "Profil mis à jour avec succès.";
        header("Location: /marketplace/connect/admin/compteadmin.php");
        exit;
    } else {
        echo "Erreur lors de la mise à jour : " . $stmt->error;
    }
    $stmt->close();
}
// --- Filtrage utilisateurs ---
$filtre = isset($_GET['statut']) ? $_GET['statut'] : 'tous';

switch ($filtre) {
    case 'actifs':
        $result = $conn->query("SELECT * FROM tbl_users WHERE actif = 1");
        break;
    case 'inactifs':
        $result = $conn->query("SELECT * FROM tbl_users WHERE actif = 0");
        break;
    default:
        $result = $conn->query("SELECT * FROM tbl_users");
        break;
}

$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}


// --- Récupération utilisateurs par rôle ---
$result = $conn->query("SELECT * FROM tbl_users WHERE role_user = '1'");
$vendeurs = [];
while ($row = $result->fetch_assoc()) {
    $vendeurs[] = $row;
}
$result = $conn->query("SELECT * FROM tbl_users WHERE role_user = '0'");
$acheteurs = [];
while ($row = $result->fetch_assoc()) {
    $acheteurs[] = $row;
}
$result = $conn->query("SELECT * FROM tbl_users WHERE role_user = '2'");
$admins = [];
while ($row = $result->fetch_assoc()) {
    $admins[] = $row;
}
$result = $conn->query("SELECT id_category, nom_category FROM tbl_category WHERE active_category = 1");
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

$annoncesParCategorie = [];

foreach ($categories as $categorie) {
    $catId = $categorie['id_category'];
    $catNom = $categorie['nom_category'];

    $stmt = $conn->prepare("
    SELECT tbl_produit.*, tbl_users.prenom_user AS utilisateur
    FROM tbl_produit
    JOIN tbl_users ON tbl_produit.vendeur_id = tbl_users.id_user
    WHERE category_id = ?
");
$stmt->bind_param("i", $catId);
$stmt->execute();
$result = $stmt->get_result();

$annonces = [];
while ($row = $result->fetch_assoc()) {
    $annonces[] = $row;
}
$annoncesParCategorie[$catNom] = $annonces;

}

// --- Statistiques ---
$result = $conn->query("SELECT COUNT(*) FROM tbl_users");
$row = $result->fetch_row();
$totalUsers = $row[0];

$result = $conn->query("SELECT COUNT(*) FROM tbl_users WHERE active_user = 1");
$row = $result->fetch_row();
$actifs = $row[0];

$result = $conn->query("SELECT COUNT(*) FROM tbl_users WHERE active_user = 0");
$row = $result->fetch_row();
$inactifs = $row[0];

$result = $conn->query("SELECT COUNT(*) FROM tbl_produit");
$row = $result->fetch_row();
$totalAnnonces = $row[0];

$result = $conn->query("SELECT COUNT(*) FROM tbl_produit WHERE active_produit = 0");
$row = $result->fetch_row();
$enAttente = $row[0];

$result = $conn->query("SELECT COUNT(*) FROM tbl_produit WHERE active_produit = 1");
$row = $result->fetch_row();
$publiées = $row[0];


$conn->close();
?>

<?php include('../head.php'); ?>
<?php include('../../nav.php'); ?>

<div class="profile-container container" style="padding-top:100px;margin-bottom:100px">
    <div class="profile-header">
        <?php
        $storedPath = $user['avatar_user'] ?? '';

        if (!empty($storedPath) && strpos($storedPath, 'C:/wamp64/www') !== false) {
            $relativePath = str_replace('C:/wamp64/www', '', $storedPath);
        } else {
            $relativePath = "/marketplace/uploads/" . $storedPath;
        }        

        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $relativePath;
        $avatarUrl = (!empty($relativePath) && file_exists($fullPath)) ? $relativePath : "https://cdn-icons-png.flaticon.com/512/63/63699.png";
        ?>
        <img class="avatar" style="width:120px" src="<?php echo $avatarUrl; ?>" alt="Photo de profil" class="profile-photo">

        <div class="profile-info">
            <h1 class="name"><?php echo $user['prenom_user'] . ' ' . $user['nom_user']; ?></h1>
        </div>
    </div>
    <div class="container">
        <h1 class="mb-4">Administration - Statistiques rapide</h1>
    <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Utilisateurs</h5>
                        <p class="card-text">Total : <?= $totalUsers ?></p>
                        <p class="card-text text-success">Actifs : <?= $actifs ?></p>
                        <p class="card-text text-muted">Inactifs : <?= $inactifs ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Annonces</h5>
                        <p class="card-text">Total : <?= $totalAnnonces ?></p>
                        <p class="card-text text-warning">En attente : <?= $enAttente ?></p>
                        <p class="card-text text-primary">Publiées : <?= $publiées ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../../footer.php'); ?>
