<?php
session_start();
$page = 'buyeraccount';
$titre = "Mon Profil";

// Vérification de la connexion utilisateur
if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_market";

// Connexion BDD
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$id_user = $_SESSION['id_user'];

// Récupération des infos utilisateur
$sql_user = "SELECT nom_user, prenom_user, mail_user, phone_user, birthday_user, avatar_user FROM tbl_users WHERE id_user = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $id_user);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
} else {
    die("Erreur : utilisateur introuvable.");
}
$stmt_user->close();
?>

<?php include('../head.php'); ?>
<?php include('../../nav.php'); ?>

<div class="profile-container container" style="padding-top:100px;">
    <div class="profile-header">
        <?php
        // Traitement du chemin de l'avatar
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
        <img class="avatar" style="width:120px" src="<?php echo $avatarUrl; ?>" alt="Photo de profil" class="profile-photo">

        <div class="profile-info">
            <h1 class="name"><?php echo htmlspecialchars($user['prenom_user'] . ' ' . $user['nom_user']); ?></h1>
        </div>
    </div>
</div>

<!-- Historique des commandes -->
<div class="container mt-5" style="margin-bottom:100px">
    <h2>Historique des commandes</h2>
    <?php
    $stmt = $conn->prepare("
        SELECT id_order, total_order, createdate_order, statut_order
        FROM tbl_order
        WHERE acheteur_id = ?
        ORDER BY createdate_order DESC
    ");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $orders = $stmt->get_result();
    $orderCount = $orders->num_rows;
    ?>

    <?php if ($orderCount === 0): ?>
        <div class="alert alert-info">Vous n’avez encore passé aucune commande.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Commande n°</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $index = 0;
                while ($order = $orders->fetch_assoc()):
                    $index++;
                    $extraClass = $index > 5 ? 'extra-order' : '';
                ?>
                <tr class="<?= $extraClass ?>" <?= $index > 5 ? 'style="display:none;"' : '' ?>>
                    <td>
                        <a class="btn btn-warning" href="facture.php?order_id=<?= urlencode($order['id_order']) ?>">
                            #<?= htmlspecialchars($order['id_order']) ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark" viewBox="0 0 16 16"><path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/></svg>
                        </a>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($order['createdate_order'])) ?></td>
                    <td><?= number_format($order['total_order'], 2) ?> €</td>
                    <td>
                        <?php
                            $statut = htmlspecialchars($order['statut_order']);
                            $badge = 'success';
                            if ($statut === 'En attente') $badge = 'warning';
                            elseif ($statut === 'Terminée') $badge = 'success';
                            elseif ($statut === 'Annulée') $badge = 'danger';
                        ?>
                        <span class="badge bg-<?= $badge ?>"><?= $statut ?></span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <?php if ($orderCount > 5): ?>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-center">
                        <button id="showMoreOrders">Afficher plus</button>
                    </td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    <?php endif; ?>
    <?php $stmt->close(); $conn->close(); ?>
</div>

<?php include('../../footer.php'); ?>

<!-- Script pour afficher plus de commandes -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('showMoreOrders');
    if (btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.extra-order').forEach(function(row) {
                row.style.display = '';
            });
            btn.style.display = 'none';
        });
    }
});
</script>
