<?php
session_start();
$page = 'forgotpassword';
$titre = "Mot de passe oublié";

require_once 'connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $db = (new Database())->getConnection();
    
    // Vérifie si l'utilisateur existe dans tbl_users
    $stmt = $db->prepare("SELECT * FROM tbl_users WHERE mail_user = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 10800); // 3 heures de validité

        // Insère ou met à jour le token dans password_resets
        $stmt_token = $db->prepare("
            INSERT INTO password_resets (email, token, expires)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE token = VALUES(token), expires = VALUES(expires)
        ");
        $stmt_token->execute([$email, $token, $expires]);

        $reset_link = "http://localhost/marketplace/connect/mdp.php?token=$token";

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'info.vendeo@gmail.com';
            $mail->Password = 'MDP gmail';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('info.vendeo@gmail.com', 'Vendeo');
            $mail->addAddress($email);

            $prenom = htmlspecialchars($user['prenom_user']);

            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $mail->Body = "
                <div style='font-family:Arial, sans-serif; padding: 20px; background-color:#f9f9f9; border:1px solid #ddd; border-radius:8px; max-width:600px; margin:auto;'>
                    <h2 style='color:#1e88e5;'>Vendeo 🛍️</h2>
                    <p>Bonjour<strong> $prenom</strong>,</p>
                    <p>Tu as demandé à réinitialiser ton mot de passe.</p>
                    <p>Clique sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>
                    <p style='text-align:center;'>
                        <a href='$reset_link' style='display:inline-block; padding:10px 20px; background-color:#1e88e5; color:white; text-decoration:none; border-radius:5px;'>Réinitialiser mon mot de passe</a>
                    </p>
                    <hr>
                    <p style='font-size: 0.9em; color:#555;'>Ce lien est valide pendant <strong>1 heures</strong>.</p>
                    <p style='font-size: 0.9em; color:#555;'>⚠️ Ne communique jamais ton mot de passe. Vendeo ne te le demandera jamais par e-mail.</p>
                    <p style='font-size: 0.9em; color:#999;'>– L’équipe Vendeo 🛍️</p>
                </div>";

            $mail->send();
            $message = "✅ Un lien de réinitialisation a été envoyé à votre adresse email.";
        } catch (Exception $e) {
            $message = "❌ Erreur lors de l'envoi du mail : " . $mail->ErrorInfo;
        }
    } else {
        $message = "❌ Adresse email non reconnue.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="http://localhost/marketplace/uploads/icon.png" type="image/png">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/connect.css">
</head>
<body>
<a href="../accueil.php" class="btn btn-link position-absolute top-0 start-0 m-3" style="color:darkorange; text-decoration: none;">
  <i class="fas fa-arrow-left me-1"></i> Accueil
</a>
<div class="d-flex justify-content-center align-items-center vh-100">
  <!-- From Uiverse.io style adapted for "Mot de passe oublié" -->
<div class="form-box">
    <form class="form" method="POST">
        <span class="title">Mot de passe oublié</span>
        <span class="subtitle">Entrez votre email pour recevoir un lien de réinitialisation.</span>
        
        <div class="form-container">
            <input type="email" name="email" class="input" placeholder="Votre adresse email" required>
        </div>
        
        <button type="submit" class="btn btn-warning">Envoyer le lien</button>

        <?php if ($message): ?>
            <div class="alert alert-info mt-3"><?= $message ?></div>
        <?php endif; ?>
    </form>
    
    <div class="form-section d-flex justify-content-center">
        <p>Vous avez déjà un compte ? <a class="form-section d-flex justify-content-center" href="login.php">Se connecter</a></p>
    </div>
</div>
</div>
</body>
</html>
