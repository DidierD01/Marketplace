<?php
session_start();
$page = 'changemdp';
$titre = "Changer le mot de passe";
require_once 'connect.php';

$db = (new Database())->getConnection(); // ✅ Utilise ta classe PDO existante

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['token']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        $token = $_POST['token'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            die("❌ Les mots de passe ne correspondent pas.");
        }

        // Vérifier le token et sa validité
        $stmt = $db->prepare("SELECT email FROM password_resets WHERE token = ? AND expires > NOW()");
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $email = $row['email'];

            // Vérification sécurité mot de passe
            $password_errors = [];

            if (strlen($new_password) < 8) $password_errors[] = "Au moins 8 caractères.";
            if (!preg_match('/\d/', $new_password)) $password_errors[] = "Au moins un chiffre.";
            if (!preg_match('/[A-Z]/', $new_password)) $password_errors[] = "Au moins une majuscule.";
            if (!preg_match('/[a-z]/', $new_password)) $password_errors[] = "Au moins une minuscule.";
            if (!preg_match('/[^a-zA-Z\d]/', $new_password)) $password_errors[] = "Au moins un caractère spécial.";

            if (!empty($password_errors)) {
                die("Mot de passe non sécurisé : " . implode(" ", $password_errors));
            }

            // Hacher et mettre à jour
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            $update = $db->prepare("UPDATE tbl_users SET password_user = ? WHERE mail_user = ?");
            if ($update->execute([$hashed_password, $email])) {
                echo "✅ Mot de passe mis à jour avec succès !";

                $delete = $db->prepare("DELETE FROM password_resets WHERE email = ?");
                $delete->execute([$email]);
            } else {
                die("❌ Erreur lors de la mise à jour du mot de passe.");
            }
        } else {
            die("❌ Lien invalide ou expiré.");
        }
    }
}
?>


<?php include('head.php'); ?>
<a href="../accueil.php" class="btn btn-link position-absolute top-0 start-0 m-3" style="color:darkorange; text-decoration: none;">
  <i class="fas fa-arrow-left me-1"></i> Accueil
</a>
<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="form-box">
    <form class="form" action="mdp.php" method="POST">
      <span class="title">Changer le mot de passe</span>
      <span class="subtitle">Saisis ton nouveau mot de passe ci-dessous.</span>

      <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">

      <div class="form-container">
        <input type="password" name="new_password" class="input" placeholder="Nouveau mot de passe" required>
        <input type="password" name="confirm_password" class="input" placeholder="Confirmer le mot de passe" required>
      </div>

      <button type="submit" class="input" style="background-color:#ff8800; color:white;">Changer le mot de passe</button>

      <?php if (isset($message)): ?>
        <div class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
    </form>

    <div class="form-section d-flex justify-content-center">
      <p>Vous vous souvenez de votre mot de passe ? <a class="form-section d-flex justify-content-center"href="login.php">Se connecter</a></p>
    </div>
  </div>
</div>


<?php include('../footer.php'); ?>
