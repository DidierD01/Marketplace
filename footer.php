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
        <li><a href="/marketplace/html/apropos.html">À propos de nous</a></li>
        <li><a href="/marketplace/html/faq.html">FAQ</a></li>
        <li><a href="/marketplace/html/politique">Politique de confidentialité</a></li>
      </ul>
    </div>

    <!-- Liens sociaux -->
    <div class="footer-section footer-socials">
      <h3>Suivez-nous</h3>
      <ul>
        <li><a href="https://www.facebook.com/">Facebook</a></li>
        <li><a href="https://www.instagram.com/">Instagram</a></li>
        <li><a href="https://x.com/">X</a></li>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script> const carteInput = document.getElementById('carte_numero'); carteInput.addEventListener('input', function (e) { let value = e.target.value; value = value.replace(/\D/g, ''); value = value.replace(/(.{4})/g, '$1 ').trim(); e.target.value = value; });</script>
</body>
</html>
