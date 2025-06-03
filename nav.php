<nav class="navbar fixed-top navbar-expand-lg bg-body-tertiary" >
      <div class="container-fluid">
        <a href="/marketplace/accueil.php" class="navbar-brand" style="padding-right:0;"><img img src="/marketplace/uploads/logo.png" width="25%" alt="logo" class="logo"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <?php
            require_once __DIR__ . '/connect/connect.php';

            $stmt = $db->query("SELECT id_category, nom_category FROM tbl_category WHERE active_category = 1 ORDER BY nom_category ASC");
            $categories = $stmt->fetchAll();
            ?>
            <li class="nav-item dropdown">
              <a class="d-flex align-items-center justify-content-center nav-link dropdown-toggle <?php if($page == 'categories') echo 'active'; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/marketplace/category/all.php">Tous Les Articles</a></li>
                <?php foreach ($categories as $cat): ?>
                  <li>
                    <a class="dropdown-item" href="/marketplace/category/all.php?categories[]=<?= htmlspecialchars($cat['id_category']) ?>">
                      <?= htmlspecialchars($cat['nom_category']) ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </li>
            <!-- Panier dynamique pour acheteur -->
            <?php if (isset($_SESSION['id_user']) && $_SESSION['role_user'] === 0): ?>
              <?php
              // Calcul dynamique du total panier pour l'utilisateur connecté
              $cart_total = 0;
              $stmt = $db->prepare("SELECT SUM(quantity_panier) FROM tbl_panier WHERE client_id = ?");
              $stmt->execute([$_SESSION['id_user']]);
              $cart_total = (int)$stmt->fetchColumn();
              ?>
              <li class="nav-item">
                <a class="d-flex align-items-center justify-content-center nav-link" href="/marketplace/connect/buyer/panier.php">
                  <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-cart2" viewBox="0 0 16 16"><path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5M3.14 5l1.25 5h8.22l1.25-5zM5 13a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0m9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0"/></svg>
                  <?php if ($cart_total > 0): ?>
                    <span class="badge bg-warning"><?= $cart_total ?></span>
                  <?php endif; ?>
                </a>
              </li>
            <?php endif; ?>
          </ul>
          <?php
          $unreadCount = 0;
          if (isset($_SESSION['id_user'])) {
              $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_message WHERE receive_id = ? AND statut_message = 0");
              $stmt->execute([$_SESSION['id_user']]);
              $unreadCount = $stmt->fetchColumn();
          }
          ?>
          <ul class="navbar-nav ms-auto d-flex align-items-center">
            <li class="nav-item d-flex align-items-center">
              <a style="margin-right:15px" class="d-flex align-items-center justify-content-center nav-link <?php if($page == 'Chat') {echo 'active';}?>" href="/marketplace/messagerie.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-chat-left" viewBox="0 0 16 16">
                  <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                </svg>   
                <?php if ($unreadCount > 0): ?>
                  <span style="margin-left:2px" class="badge bg-warning">  <?= $unreadCount ?></span>
                <?php endif; ?>
              </a>
            </li>
            <li>
            <form action = "/marketplace/category/bdr/verif-form.php" method = "get" class="w-100 me-3" style="max-width: 300px;">
              <div class="input-group connectsearch">
                <input class="connectform form-control me-2" type="search" name="terme" placeholder="Rechercher un article" aria-label="Search">
                <button type="submit" name="s" value="Rechercher" class="connectbtn btn btn-dark">
                  <i class="fa-solid fa-magnifying-glass"></i>
                </button>
              </div>
            </form>
            </li>
            <?php if (isset($_SESSION['id_user'])): ?>
            <?php
            // Avatar & infos utilisateur
            $stmt = $db->prepare("SELECT * FROM tbl_users WHERE id_user = ?");
            $stmt->execute([$_SESSION['id_user']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $storedPath = $user['avatar_user'] ?? '';
            if (!empty($storedPath) && strpos($storedPath, 'C:/wamp64/www') !== false) {
                $relativePath = str_replace('C:/wamp64/www', '', $storedPath);
            } else {
                $relativePath = "/marketplace/uploads/" . $storedPath;
            }
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $relativePath;
            if (!empty($storedPath) && file_exists($fullPath)) {
                $avatarUrl = $relativePath;
            } else {
                $avatarUrl = "https://cdn-icons-png.flaticon.com/512/63/63699.png";
            }
            ?>
            <li class="nav-item dropdown d-flex align-items-center me-3">
                <a class="nav-link dropdown-toggle p-0" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="border: none; background: none;">
                    <img src="<?= $avatarUrl ?>" alt="Profil" class="rounded-circle avatar" style="width:50px; height:50px; object-fit:cover;">
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <?php if ($_SESSION['role_user'] == 0): // Acheteur ?>
                        <li><a class="dropdown-item" href="/marketplace/connect/buyer/moncompte.php">Mon profil</a></li>
                        <li><a class="dropdown-item" href="/marketplace/connect/setting.php">Modifier mon profil</a></li>
                        <li><a class="dropdown-item" href="/marketplace/connect/logout.php">Déconnexion</a></li>
                    <?php elseif ($_SESSION['role_user'] == 1): // Vendeur ?>
                        <li><a class="dropdown-item" href="/marketplace/connect/seller/moncompte.php">Mon profil</a></li>
                        <li><a class="dropdown-item" href="/marketplace/connect/seller/article.php">Mes articles en vente</a></li>
                        <li><a class="dropdown-item" href="/marketplace/connect/setting.php">Modifier mon profil</a></li>
                        <li><a class="dropdown-item" href="/marketplace/connect/logout.php">Déconnexion</a></li>
                    <?php elseif ($_SESSION['role_user'] == 2): // Admin ?>
                        <li><a class="dropdown-item" href="/marketplace/connect/admin/compteadmin.php">Mon profil</a></li>
                        <li><a class="dropdown-item" href="/marketplace/connect/admin/admin.php">Administration</a></li>
                        <li><a class="dropdown-item" href="/marketplace/connect/setting.php">Modifier mon profil</a></li>
                        <li><a class="dropdown-item" href="/marketplace/connect/logout.php">Déconnexion</a></li>
                    <?php endif; ?>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item d-flex align-items-center">
                <a class="nav-link d-flex align-items-center justify-content-center" href="/marketplace/login.php"><svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
  <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
  <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
</svg></a>
            </li>
        <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
    