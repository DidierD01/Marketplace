<?php
session_start();
$page = 'settingaccount';
$titre = "Modifier mon Profil";

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_market";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$user_id = $_SESSION['id_user'];

// Récupérer les infos actuelles (PDO)
$sql_user = "SELECT nom_user, prenom_user, surname_user, mail_user, phone_user, birthday_user, avatar_user FROM tbl_users WHERE id_user = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result = $stmt_user->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo '<div class="alert alert-danger" role="alert">Erreur : Utilisateur introuvable</div>';
    exit;
}

// Importation de PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$alert = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $birthday = !empty($_POST['birthday']) ? trim($_POST['birthday']) : null;

    // Validation de la date de naissance
    if ($birthday) {
        $minAge = 12; // Âge minimum requis
        $maxAge = 120; // Âge maximum plausible
        
        $birthDate = new DateTime($birthday);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        
        // Vérification que la date est dans le passé
        if ($birthDate > $today) {
            $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        La date de naissance ne peut pas être dans le futur.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
        } 
        // Vérification de l'âge minimum
        elseif ($age < $minAge) {
            $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Vous devez avoir au moins '.$minAge.' ans pour vous inscrire.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
        }
        // Vérification de l'âge maximum
        elseif ($age > $maxAge) {
            $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        La date de naissance semble invalide (plus de '.$maxAge.' ans).
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
        }
    }

    if (isset($_POST['reset_password'])) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 7200);

        $sql_token = "INSERT INTO password_resets (email, token, expires)
                      VALUES (?, ?, ?)
                      ON DUPLICATE KEY UPDATE token = VALUES(token), expires = VALUES(expires)";
        $stmt_token = $conn->prepare($sql_token);
        $stmt_token->execute([$user['mail_user'], $token, $expires]);

        $reset_link = "http://localhost/marketplace/connect/mdp.php?token=" . $token;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'vendeo.contact@gmail.com';
            $mail->Password = 'faxr ohgx vdvn tmix'; // Mot de passe d'application Gmail !
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('vendeo.contact@gmail.com', 'Marketplace');
            $mail->addAddress($user['mail_user']);

            $mail->isHTML(true); // Active le mode HTML
            $mail->CharSet = 'UTF-8'; // Support des accents
            $mail->Encoding = 'base64'; // Pour éviter les bugs d'encodage

            $prenom = htmlspecialchars($user['prenom_user']);

            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $mail->Body = "
                <div style='font-family:Arial, sans-serif; padding: 20px; background-color:#f9f9f9; border:1px solid #ddd; border-radius:8px; max-width:600px; margin:auto;'>
                    <h2 style='color:#1e88e5;'>Vendeo 🛍️</h2>
                    <p>Bonjour <strong>$prenom</strong>,</p>
                    <p>Tu as demandé à réinitialiser ton mot de passe.</p>
                    <p>Clique sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>
                    <p style='text-align:center;'>
                        <a href='$reset_link' style='display:inline-block; padding:10px 20px; background-color:#1e88e5; color:white; text-decoration:none; border-radius:5px;'>Réinitialiser mon mot de passe</a>
                    </p>
                    <hr>
                    <p style='font-size: 0.9em; color:#555;'>Ce lien est valide pendant <strong>1 heure</strong>.</p>
                    <p style='font-size: 0.9em; color:#555;'>⚠️ Ne communique jamais ton mot de passe. Vendeo ne te le demandera jamais par e-mail.</p>
                    <p style='font-size: 0.9em; color:#999;'>– L’équipe Vendeo 🛍️</p>
                </div>";
            $mail->send();
            $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            Un e-mail de réinitialisation a été envoyé.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        } catch (Exception $e) {
            $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Erreur mail : ' . htmlspecialchars($mail->ErrorInfo) . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
        }
    } else {
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $email = trim($_POST['email']);
        $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
        $birthday = !empty($_POST['birthday']) ? trim($_POST['birthday']) : null;
        $avatar = $user['avatar_user'];

        $errors = [];

        // Nom : lettres, espaces, tirets
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/u', $nom)) {
            $errors[] = "Le nom ne doit contenir que des lettres, espaces ou tirets.";
        }

        // Prénom : lettres, espaces, tirets
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/u', $prenom)) {
            $errors[] = "Le prénom ne doit contenir que des lettres, espaces ou tirets.";
        }

        // Téléphone : uniquement chiffres, 10 à 14 chiffres
        if (!preg_match('/^[0-9]{10,14}$/', $phone)) {
            $errors[] = "Le numéro de téléphone doit contenir uniquement des chiffres (10 à 14 chiffres).";
        }

        // Email : format classique
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse e-mail n'est pas valide.";
        }

        // Affichage des erreurs dans une alert box Bootstrap
        if (!empty($errors)) {
            $alert = '<div class="alert alert-danger" role="alert"><ul>';
            foreach ($errors as $err) {
                $alert .= '<li>' . htmlspecialchars($err) . '</li>';
            }
            $alert .= '</ul></div>';
        }

        // Gestion de l'upload du nouvel avatar
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/marketplace/uploads/';
            $fileExtension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $uniqueFileName = time() . '_' . uniqid() . '_' . $user_id . '.' . $fileExtension;
            $uploadFile = $uploadDir . $uniqueFileName;

            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            if ($_FILES["avatar"]["size"] > 5000000) {
                $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Erreur : L\'image est trop grande.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
            } elseif (!in_array($fileExtension, $allowedExtensions)) {
                $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Erreur : Seules les images JPG, JPEG et PNG sont autorisées.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
            } else {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Supprimer l'ancien avatar
                if (!empty($user['avatar_user'])) {
                    $oldAvatarPath = $uploadDir . $user['avatar_user'];
                    if (file_exists($oldAvatarPath)) {
                        unlink($oldAvatarPath);
                    }
                }

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadFile)) {
                    $avatar = $uniqueFileName;
                } else {
                    $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Erreur lors du téléchargement de l\'image.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
                }
            }
        }

        if (empty($alert)) {
            $sql = "UPDATE tbl_users 
                    SET nom_user = ?, prenom_user = ?, mail_user = ?, phone_user = ?, avatar_user = ?, birthday_user = ?, modifdate_user = NOW() 
                    WHERE id_user = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $nom, $prenom, $email, $phone, $avatar, $birthday, $user_id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    // Affiche un message de succès et reste sur la page
                    $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                Profil modifié avec succès !
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
                } else {
                    $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                Aucune modification effectuée.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
                }
            } else {
                $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Erreur SQL : ' . htmlspecialchars($stmt->error) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
            }            
        }
    }
}

$profileUrl = '';
if (isset($_SESSION['role_user'])) {
    if ($_SESSION['role_user'] == 0) {
        $profileUrl = '/marketplace/connect/buyer/moncompte.php';
    } elseif ($_SESSION['role_user'] == 1) {
        $profileUrl = '/marketplace/connect/seller/moncompte.php';
    } elseif ($_SESSION['role_user'] == 2) {
        $profileUrl = '/marketplace/connect/admin/compteadmin.php';
    }
}
$conn = null;
?>

<?php include('head.php'); ?>
<?php include('../nav.php'); ?>
<br><br><br><br>
<a href="<?= $profileUrl ?>" class="btn btn-link top-1 start-0 m-3" style="color:darkorange; text-decoration: none;"><i class="fas fa-arrow-left me-1"></i> Retour à mon profil</a>
<div class="container mt-5">
    <h2>Modifier Mon Profil</h2>

    <?php if (!empty($alert)) echo $alert; ?>

    <form action="setting.php" method="POST" enctype="multipart/form-data">
        <div class="coolinput">
            <label for="nom">Nom:</label>
            <input type="text" name="nom" class="input" value="<?php echo htmlspecialchars($user['nom_user'] ?? ''); ?>" required>
        </div>

        <div class="coolinput">
            <label for="prenom">Prénom:</label>
            <input type="text" name="prenom" class="input" value="<?php echo htmlspecialchars($user['prenom_user'] ?? ''); ?>" required>
        </div>

        <div class="coolinput">
            <label for="email">E-mail:</label>
            <input type="email" name="email" class="input" value="<?php echo htmlspecialchars($user['mail_user'] ?? ''); ?>" required>
        </div>

        <div class="coolinput">
            <label for="phone">Téléphone:</label>
            <input type="text" name="phone" class="input" value="<?php echo htmlspecialchars($user['phone_user'] ?? ''); ?>">
        </div>

        <div class="coolinput">
            <label for="birthday">Date de naissance:</label>
            <input type="date" name="birthday" class="input" 
                value="<?php echo htmlspecialchars($user['birthday_user'] ?? ''); ?>"
                max="<?php echo date('Y-m-d', strtotime('-12 years')); ?>"
                min="<?php echo date('Y-m-d', strtotime('-120 years')); ?>">
        </div>

        <div class="coolinput">
            <label for="avatar">Photo de profil:</label>
            <?php if (!empty($user['avatar_user'])) : ?>
                <img src="/marketplace/uploads/<?php echo htmlspecialchars($user['avatar_user']); ?>" alt="Avatar actuel" width="100">
            <?php endif; ?>
            <input type="file" name="avatar" class="input">
        </div>

        <button type="submit" class="btn btn-primary">Modifier</button>
        <button type="submit" name="reset_password" class="btn btn-warning">Modifier le mot de passe</button>
    </form>
</div>
<br><br><br>

<?php include('../footer.php'); ?>