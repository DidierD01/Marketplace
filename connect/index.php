<?php
// On démarre une session
session_start();
$page = 'createaccount';
$titre = "Création de compte";
?>
<?php include('head.php'); ?>
<a href="../accueil.php" class="btn btn-link position-absolute top-0 start-0 m-3" style="color:darkorange; text-decoration: none;">
  <i class="fas fa-arrow-left me-1"></i> Accueil
</a>

<div class="d-flex justify-content-center align-items-center vh-100">
    <!-- From Uiverse.io by alexruix -->
    <div class="form-box">
        <!-- Messages d'erreur et de succès -->
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php } ?>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-succ ess" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php } ?>

        <form class="form" action="signup.php" method="post" onsubmit="return validatePassword()">
            <span class="title">S'enregistrer</span>
            <span class="subtitle">Créez un compte gratuit avec votre mail.</span>
            <div class="form-container">
                <input type="text" name="prenom_user" class="input" placeholder="Prénom" value="<?php echo (isset($_GET['prenom_user'])) ? $_GET['prenom_user'] : ''; ?>">
                <input type="text" name="nom_user" class="input" placeholder="Nom" value="<?php echo (isset($_GET['nom_user'])) ? $_GET['nom_user'] : ''; ?>">
                <input type="email" name="mail_user" class="input" placeholder="Adresse Email" value="<?php echo (isset($_GET['mail_user'])) ? $_GET['mail_user'] : ''; ?>">
                <input type="password" name="password_user" id="password_user" class="input" placeholder="Mot de Passe" value="<?php echo (isset($_GET['password_user'])) ? $_GET['password_user'] : ''; ?>">
                
                
            </div>
            <div class="filter-switch" style="background-color:white">
                <input checked id="option1" name="role_user" type="radio" value="0" />
                <label class="option" for="option1">Acheteur</label>

                <input id="option2" name="role_user" type="radio" value="1" />
                <label class="option" for="option2">Vendeur</label>

                <span class="background"></span>
            </div>

            <button type="submit" class="btn">S'inscrire</button>
        </form>

        <div class="form-section d-flex justify-content-center">
            <p>Avez-vous déjà un compte ? <br><a class="d-flex justify-content-center" href="../login.php">Se connecter</a></p>
        </div>
    </div>
</div>

<script>
function validatePassword() {
    const password = document.getElementById('password_user').value;
    const errors = [];

    // Longueur minimale
    if (password.length < 8) {
        errors.push("Le mot de passe doit contenir au moins 8 caractères.");
    }

    // Au moins un chiffre
    if (!/\d/.test(password)) {
        errors.push("Le mot de passe doit contenir au moins un chiffre.");
    }

    // Au moins une lettre majuscule
    if (!/[A-Z]/.test(password)) {
        errors.push("Le mot de passe doit contenir au moins une lettre majuscule.");
    }

    // Au moins une lettre minuscule
    if (!/[a-z]/.test(password)) {
        errors.push("Le mot de passe doit contenir au moins une lettre minuscule.");
    }

    // Au moins un caractère spécial
    if (!/[^a-zA-Z\d]/.test(password)) {
        errors.push("Le mot de passe doit contenir au moins un caractère spécial.");
    }

    // Si des erreurs sont trouvées
    if (errors.length > 0) {
        alert("Erreurs de mot de passe :\n" + errors.join("\n"));
        return false; // Empêche la soumission du formulaire
    }

    return true; // Autorise la soumission du formulaire
}
</script>

<?php include('footer.php'); ?>