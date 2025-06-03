<?php
// On démarre une session
session_start();
$page = 'Accueil';
$titre = "Vendeo";
?>
<?php include('head.php'); ?>
<?php include('nav.php'); ?>
<br><br><br>
<section class="hero-newsletter">
  <div class="text-content">
    <h1 class="title mb-3">Vendeo
    <p class="subtitle">Le bon plan qu'il vous faut, zéro blabla,<br>c'est cadeau.</p></h1>
    <a href="connect/index.php" class="btn btn-orange btn-lg mt-4 shadow">Créer un compte gratuitement</a>
    <div class="hero-links mt-4">
      <a href="category/all.php" class="hero-link">Voir les annonces</a>
      <span class="mx-2 text-white-50">|</span>
      <a href="html/faq.html" class="hero-link">Des Questions?</a>
    </div>
  </div>
</section>

<?php include('footer.php'); ?>