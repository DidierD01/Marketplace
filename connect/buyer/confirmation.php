<?php
session_start();
$page = 'confirm';
$titre = "Confirmation de commande";

require_once __DIR__.'/../connect.php';

if (!isset($_GET['order_id'])) {
    die("Erreur : ID de commande manquant.");
}

$order_id = $_GET['order_id'];

// Récupérer la commande
$stmt = $db->prepare("SELECT * FROM tbl_order WHERE id_order = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les détails de l'acheteur
$stmt_user = $db->prepare("SELECT * FROM tbl_users WHERE id_user = ?");
$stmt_user->execute([$order['acheteur_id']]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Récupérer les produits de la commande
$stmt_items = $db->prepare("
    SELECT oi.quantity, oi.prix, pr.nom_produit
    FROM tbl_order_items oi
    JOIN tbl_produit pr ON oi.produit_id = pr.id_produit
    WHERE oi.order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Générer le tableau HTML
$orderTable = '<h4>Détails de votre commande :</h4>';
$orderTable .= '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse;">';
$orderTable .= '<tr><th>Produit</th><th>Quantité</th><th>Prix</th></tr>';

foreach ($items as $item) {
    $orderTable .= '<tr>';
    $orderTable .= '<td>' . htmlspecialchars($item['nom_produit']) . '</td>';
    $orderTable .= '<td>' . intval($item['quantity']) . '</td>';
    $orderTable .= '<td>' . number_format($item['prix'], 2, ',', ' ') . ' €</td>';
    $orderTable .= '</tr>';
}

$orderTable .= '</table>';

// Envoi de l'email avec PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info.vendeo@gmail.com';      // Ton email
    $mail->Password   = 'qams lhzb spiu dsyb';        // Mot de passe ou mot de passe d'application
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('info.vendeo@gmail.com', 'Vendeo');
    $mail->addAddress($user['mail_user']); // Email de l'acheteur

    $mail->isHTML(true);
    $mail->Subject = 'Confirmation de votre commande';
    $mail->Body = "
        <div style='font-family:Arial, sans-serif; padding: 20px; background-color:#f9f9f9; border:1px solid #ddd; border-radius:8px; max-width:600px; margin:auto;'>
            <h2 style='color:#1e88e5;'>Vendeo 🛍️</h2>
            <p>Bonjour <strong>" . htmlspecialchars($user['prenom_user']) . "</strong>,</p>
            <p>Merci pour votre commande !</p>
            <p><strong>Numéro de commande :</strong> #" . $order['id_order'] . "</p>
            <p><strong>Date de commande :</strong> " . date('d/m/Y H:i', strtotime($order['createdate_order'])) . "</p>
            <p><strong>Total :</strong> " . number_format($order['total_order'], 2) . " €</p>
            <p><strong>Statut :</strong> " . $order['statut_order'] . "</p>
            <br>
            $orderTable
            <hr>
            <p style='font-size: 0.9em; color:#555;'>Nous vous contacterons par email lorsque votre commande sera traitée.</p>
            <p style='font-size: 0.9em; color:#999;'>– L’équipe Vendeo 🛍️</p>
        </div>";

    $mail->send();

    // Mettre à jour le statut de la commande
    $new_status = 'Confirmée';
    $stmt_update = $db->prepare("UPDATE tbl_order SET statut_order = ? WHERE id_order = ?");
    $stmt_update->execute([$new_status, $order_id]);

    $_SESSION['message'] = "✅ Un email de confirmation a été envoyé à votre adresse email.";
} catch (Exception $e) {
    $_SESSION['message'] = "❌ Erreur lors de l'envoi du mail : " . $mail->ErrorInfo;
}

$db = null;
?>

<?php include('../head.php'); ?>
<a href="moncompte.php" class="btn btn-dark mt-3">⬅ Retour à mon compte</a>

<div class="container" style="padding-top: 80px;">
    <h1>Confirmation de la commande</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-info"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <p>Merci pour votre commande !</p>
    <p><strong>Numéro de commande : </strong>#<?= $order['id_order'] ?></p>
    <p><strong>Date de commande : </strong><?= date('d/m/Y H:i', strtotime($order['createdate_order'])) ?></p>
    <p><strong>Total : </strong><?= number_format($order['total_order'], 2) ?> €</p>
    <p><strong>Statut : </strong><span class="badge bg-warning"><?= $order['statut_order'] ?></span></p>

    <p>Nous vous enverrons un email de confirmation lorsque votre commande sera traitée.</p>
</div>

<?php include('../../footer.php'); ?>
