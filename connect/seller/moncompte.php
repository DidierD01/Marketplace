<?php
session_start();
$page = 'settingaccount';
$titre = "Mon Profil";

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['id_user'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_market";

// Connexion BDD
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Récupération de l'utilisateur
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

// Requête pour les articles - création d'une nouvelle requête préparée
$sql_articles = "
SELECT 
    p.id_produit,
    p.nom_produit,
    p.stock_produit,
    COALESCE(SUM(oi.quantity), 0) AS total_vendu
FROM tbl_produit p
LEFT JOIN tbl_order_items oi ON oi.produit_id = p.id_produit
LEFT JOIN tbl_order o ON o.id_order = oi.order_id AND o.statut_order = 'Confirmée'
WHERE p.vendeur_id = ?
GROUP BY p.id_produit, p.nom_produit, p.stock_produit
ORDER BY p.nom_produit ASC
";

$stmt_articles = $conn->prepare($sql_articles);
$stmt_articles->bind_param("i", $user_id);
$stmt_articles->execute();
$result_articles = $stmt_articles->get_result();
$articles = [];

while ($row = $result_articles->fetch_assoc()) {
    $articles[] = $row;
}

$stmt_user->close();
$stmt_articles->close();
$conn->close();
?>

<?php include('../head.php'); ?>
<?php include('../../nav.php'); ?>

<div class="profile-container container" style="padding-top:100px;">
    <div class="profile-header">
        <?php
        // Traitement du chemin de l'avatar
        $storedPath = $user['avatar_user'] ?? '';

        // Convertit en chemin relatif si chemin absolu détecté
        if (!empty($storedPath) && strpos($storedPath, 'C:/wamp64/www') !== false) {
            $relativePath = str_replace('C:/wamp64/www', '', $storedPath);
        } else {
            $relativePath = "/marketplace/uploads/" . $storedPath;
        }

        // Vérifie l'existence réelle du fichier
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $relativePath;
        if (!empty($relativePath) && file_exists($fullPath)) {
            $avatarUrl = $relativePath;
        } else {
            $avatarUrl = "https://cdn-icons-png.flaticon.com/512/63/63699.png";
        }
        ?>
        <img class="avatar" style="width:120px" src="<?php echo $avatarUrl; ?>" alt="Photo de profil" class="profile-photo">

        <div class="profile-info">
            <h1 class="name"><?php echo $user['prenom_user'] . ' ' . $user['nom_user']; ?></h1>
        </div>
    </div>
    <h3 class="mt-5 mb-3">Vos articles en vente</h3>
<?php if (empty($articles)): ?>
    <div class="alert alert-info">Vous n'avez aucun article en vente actuellement.</div>
<?php else: ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Article</th>
                <th>Stock</th>
                <th>Ventes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = 0;
            foreach ($articles as $article):
                $index++;
                $extraClass = $index > 5 ? 'extra-article' : '';
                $stockClass = $article['stock_produit'] <= 2 ? 'text-danger fw-bold' : '';
            ?>
            <tr class="<?= $extraClass ?>" <?= $index > 5 ? 'style="display:none;"' : '' ?>>
                <td><?= htmlspecialchars($article['nom_produit']) ?></td>
                <td class="<?= $stockClass ?>">
                    <?= (int)$article['stock_produit'] ?>
                    <?php if ($article['stock_produit'] <= 2): ?>
                        <span class="badge bg-danger ms-2">Stock faible</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge bg-success"><?= (int)$article['total_vendu'] ?> vendus</span>
                </td>
                <td>
                    <a class="btn btn-warning btn-sm" href="edit.php?id=<?= $article['id_produit'] ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                            <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-5-.207L10 9.293V10H9.5a.5.5 0 0 1-.5.5V11H8.5a.5.5 0 0 1-.5.5V11h-.5a.5.5 0 0 1-.5-.5V10.293z"/>
                        </svg>
                        Modifier
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <?php if (count($articles) > 5): ?>
        <tfoot>
            <tr>
                <td colspan="4" class="text-center">
                    <button id="showMoreArticles" class="btn btn-outline-secondary">Afficher plus d'articles</button>
                </td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
<?php endif; ?>
</div>
<footer class="footer">
  <div class="footer-container">
    <!-- Logo et description -->
    <div class="footer-section footer-logo">
      <h1>Vendeo</h1>
      <p>Le marketplace où vous pouvez vendre, acheter et échanger en toute sécurité.</p>
    </div>
    
    <!-- Liens de navigation -->
    <div class="footer-section footer-links">
      <h3>Liens Utiles</h3>
      <ul>
        <li><a href="/marketplace/accueil.php">Accueil</a></li>
        <li><a href="/marketplace/category/all.php">Catalogue</a></li>
        <li><a href="/marketplace/html/apropos.html">À propos de nous</a></li>
        <li><a href="/marketplace/html/faq.html">FAQ</a></li>
        <li><a href="/marketplace/html/CG.HTML">Conditions Générales</a></li>
        <li><a href="/marketplace/html/politique">Politique de confidentialité</a></li>
      </ul>
    </div>

    <!-- Liens sociaux -->
    <div class="footer-section footer-socials">
      <h3>Suivez-nous</h3>
      <ul>
        <li><a href="https://www.facebook.com/">Facebook</a></li>
        <li><a href="https://www.instagram.com/">Instagram</a></li>
        <li><a href="https://x.com/">Twitter</a></li>
        <li><a href="https://linkedin.com">LinkedIn</a></li>
      </ul>
    </div>

    <!-- Newsletter -->
    <div class="footer-section footer-newsletter">
      <h3>Abonnez-vous à notre Newsletter</h3>
      <!-- Footer Newsletter Form -->
      <form action="/marketplace/newsletter.php" method="POST" class="newsletter-form">
        <input type="email" name="email" placeholder="Votre email" required>
        <button type="submit">S'abonner</button>
      </form>
    </div>
  </div>

  <!-- Informations légales -->
  <div class="footer-bottom">
    <p>&copy; 2025 Vendeo. Tous droits réservés. | <a href="/marketplace/html/mention.html">Mentions légales</a> | <a href="/marketplace/html/CG.HTML">Conditions Générales</a></p>
  </div>
</footer>

<script>
// Script pour afficher/masquer les articles supplémentaires
document.getElementById('showMoreArticles')?.addEventListener('click', function() {
    const extraArticles = document.querySelectorAll('.extra-article');
    const button = this;
    
    extraArticles.forEach(article => {
        if (article.style.display === 'none') {
            article.style.display = 'table-row';
            button.textContent = 'Afficher moins';
        } else {
            article.style.display = 'none';
            button.textContent = 'Afficher plus';
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script> const carteInput = document.getElementById('carte_numero'); carteInput.addEventListener('input', function (e) { let value = e.target.value; value = value.replace(/\D/g, ''); value = value.replace(/(.{4})/g, '$1 ').trim(); e.target.value = value; });</script>
</body>
</html>

