<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'connect/PHPMailer/src/PHPMailer.php';
require 'connect/PHPMailer/src/SMTP.php';
require 'connect/PHPMailer/src/Exception.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Vérification simple de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse email invalide.";
    } else {
        // Ici tu peux ajouter l'email à ta base de données si tu veux stocker les abonnés

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'info.vendeo@gmail.com';
            $mail->Password = 'qams lhzb spiu dsyb';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('info.vendeo@gmail.com', 'Vendeo');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Bienvenue sur la newsletter Vendeo !';
            $mail->Body = "
                <div style='font-family:Arial, sans-serif; padding: 20px; background-color:#f9f9f9; border:1px solid #ddd; border-radius:8px; max-width:600px; margin:auto;'>
                    <h2 style='color:#1e88e5;'>Bienvenue chez Vendeo 🛍️</h2>
                    <p>Merci de vous être abonné à notre newsletter !</p>
                    <p>Vous recevrez bientôt nos actualités, offres et conseils exclusifs.</p>
                    <hr>
                    <p style='font-size: 0.9em; color:#555;'>Vous pouvez vous désabonner à tout moment via le lien présent dans nos emails.</p>
                    <p style='font-size: 0.9em; color:#999;'>– L’équipe Vendeo 🛍️</p>
                </div>";

            $mail->send();
            $message = "Merci pour votre inscription ! Un email de bienvenue vient de vous être envoyé.";
        } catch (Exception $e) {
            $message = "Erreur lors de l'envoi de l'email : " . $mail->ErrorInfo;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Newsletter Vendeo</title>
    <link rel="icon" href="http://localhost/marketplace/uploads/icon.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
      body {
        background: #fff7f0;
        min-height: 100vh;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
      }
    </style>
</head>
<body>
<div class="container mt-5">
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
        <a href="/marketplace/accueil.php" class="btn btn-primary">Retour à l'accueil</a>
    <?php endif; ?>
</div>
</body>
</html>
