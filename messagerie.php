<?php
session_start();
require_once 'connect/connect.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: /marketplace/login.php');
    exit;
}
$user_id = $_SESSION['id_user'];

// Récupérer le rôle numérique de l'utilisateur connecté
$sql = "SELECT role_user FROM tbl_users WHERE id_user = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);
$user_role_num = $stmt->fetchColumn();

if ($user_role_num === false) {
    die("Utilisateur introuvable.");
}

// Convertir en texte pour affichage éventuel
$user_role = match($user_role_num) {
    0 => 'acheteur',
    1 => 'vendeur',
    2 => 'admin',
    default => 'inconnu'
};

// Recherche utilisateur
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construire la condition selon le rôle numérique
$role_condition = '';
switch ($user_role_num) {
    case 0: // Acheteur voit vendeurs
        $role_condition = 'WHERE u.role_user = 1 AND u.id_user != ?';
        break;
    case 1: // Vendeur voit acheteurs
        $role_condition = 'WHERE u.role_user = 0 AND u.id_user != ?';
        break;
    case 2: // Admin voit tout sauf lui-même
        $role_condition = 'WHERE u.id_user != ?';
        break;
    default:
        die("Erreur : rôle utilisateur inconnu.");
}

$search_clause = '';
$search_params = [];
if ($search !== '') {
    $search_clause = ' AND (u.nom_user LIKE ? OR u.prenom_user LIKE ?)';
    $search_params = ["%$search%", "%$search%"];
}

$sql = "
SELECT
    u.id_user,
    u.nom_user,
    u.prenom_user,
    u.avatar_user,
    u.role_user,
    m.content_message,
    m.createdate_message,
    (
        SELECT COUNT(*) FROM tbl_message
        WHERE exp_id = u.id_user AND receive_id = ? AND statut_message = 0
    ) AS unread_count
FROM tbl_users u
LEFT JOIN (
    SELECT 
        CASE 
            WHEN exp_id = ? THEN receive_id
            ELSE exp_id
        END AS other_user_id,
        MAX(createdate_message) AS last_message_date
    FROM tbl_message
    WHERE exp_id = ? OR receive_id = ?
    GROUP BY other_user_id
) last_msg ON u.id_user = last_msg.other_user_id
LEFT JOIN tbl_message m ON (
    ((m.exp_id = ? AND m.receive_id = u.id_user) OR (m.exp_id = u.id_user AND m.receive_id = ?))
    AND m.createdate_message = last_msg.last_message_date
)
$role_condition
$search_clause
ORDER BY
    CASE WHEN m.createdate_message IS NULL THEN 1 ELSE 0 END,
    m.createdate_message DESC,
    u.prenom_user ASC,
    u.nom_user ASC
";

$params = [
    $user_id,    // unread_count receive_id = ?
    $user_id,    // last_msg exp_id = ?
    $user_id,    // last_msg receive_id = ?
    $user_id,    // last_msg exp_id or receive_id
    $user_id,    // m.exp_id = ?
    $user_id     // m.receive_id = ?
];
// Ajouter le paramètre pour exclure l'utilisateur courant dans WHERE
$params[] = $user_id;
// Ajouter les paramètres de recherche si présents
$params = array_merge($params, $search_params);

$stmt = $db->prepare($sql);
$stmt->execute($params);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sélection d'un utilisateur pour la conversation
$selected_user = isset($_GET['user']) ? intval($_GET['user']) : null;

// Vérification du droit de discuter avec l'utilisateur sélectionné
$can_talk = false;
$selected_role_num = null;
if ($selected_user) {
    $stmt_selected = $db->prepare("SELECT role_user FROM tbl_users WHERE id_user = ?");
    $stmt_selected->execute([$selected_user]);
    $selected_role_num = $stmt_selected->fetchColumn();

    if ($user_role_num === 2) {
        $can_talk = true; // Admin peut parler à tout le monde
    } elseif ($user_role_num === 0 && $selected_role_num === 1) {
        $can_talk = true; // Acheteur peut parler aux vendeurs
    } elseif ($user_role_num === 1 && $selected_role_num === 0) {
        $can_talk = true; // Vendeur peut parler aux acheteurs
    }

    if (!$can_talk) {
        $selected_user = null; // Annuler la sélection si non autorisé
    }
}

// Récupérer la conversation avec l'utilisateur sélectionné
$messages = [];
if ($selected_user) {
    $sql = "SELECT * FROM tbl_message WHERE (exp_id = ? AND receive_id = ?) OR (exp_id = ? AND receive_id = ?) ORDER BY createdate_message ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id, $selected_user, $selected_user, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Marquer les messages reçus comme lus
    $sql = "UPDATE tbl_message SET statut_message = 1 WHERE exp_id = ? AND receive_id = ? AND statut_message = 0";
    $stmt = $db->prepare($sql);
    $stmt->execute([$selected_user, $user_id]);
}

// Envoi d'un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message']) && $selected_user) {
    $content = trim($_POST['message']);
    $now = date('Y-m-d H:i:s');
    $sql = "INSERT INTO tbl_message (exp_id, receive_id, content_message, statut_message, createdate_message) VALUES (?, ?, ?, 0, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id, $selected_user, $content, $now]);
    header("Location: ?user=$selected_user");
    exit;
}

$page = 'Messagerie';
$titre = "Vendeo";
?>
<?php include('head.php'); ?>
<link rel="stylesheet" href="css/message.css">
<style>
    .unread-badge {
    display: inline-block;
    min-width: 12px;
    height: 12px;
    background: #ff8800; /* orange du site */
    border-radius: 50%;
    margin-left: 10px;
    vertical-align: middle;
    box-shadow: 0 1px 4px rgba(255,136,0,0.15);
}
</style>
<?php include('nav.php'); ?>
<div class="messenger-container" style="margin-top:100px">
    <div class="users-list">
        <form method="get">
            <input type="text" name="search" placeholder="Rechercher un utilisateur..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit">Rechercher</button>
            <?php if (isset($_GET['search']) && $_GET['search'] !== ''): ?>
                <a href="messagerie.php">Annuler</a>
            <?php endif; ?>
        </form>
        <?php
            $roles_map = [
                0 => 'acheteur',
                1 => 'vendeur',
                2 => 'admin',
            ];
        ?>
        <?php foreach ($conversations as $conv): ?>
            <a href="?user=<?= $conv['id_user'] ?>" class="user<?= ($selected_user == $conv['id_user']) ? ' selected' : '' ?>">
                <img src="/marketplace/uploads/<?= htmlspecialchars($conv['avatar_user'] ?? 'default-avatar.png') ?>" alt="Avatar de l'utilisateur">
                <span><?= htmlspecialchars($conv['prenom_user'].' '.$conv['nom_user']) ?></span>
                <?php if ($conv['unread_count'] > 0): ?>
                    <span class="unread-badge" title="Nouveau message"></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="chat">
        <div class="messages" id="messages">
            <?php if ($selected_user): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= $msg['exp_id'] == $user_id ? 'sent' : 'received' ?>">
                        <div class="bubble">
                            <?= nl2br(htmlspecialchars($msg['content_message'])) ?>
                            <div class="message-time"><?= date('d/m/Y H:i', strtotime($msg['createdate_message'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif (isset($_GET['user'])): ?>
                <p class="warning">Vous n'êtes pas autorisé à discuter avec cet utilisateur.</p>
            <?php else: ?>
                <p class="info">Sélectionnez une conversation à gauche pour commencer à discuter.</p>
            <?php endif; ?>
        </div>
        <?php if ($selected_user): ?>
        <form class="send-form" method="post" autocomplete="off">
            <textarea name="message" rows="2" placeholder="Écrivez un message..." required></textarea>
            <button type="submit">Envoyer</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<script>
    window.onload = function() {
        var messagesDiv = document.getElementById('messages');
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        
        // Auto-focus sur le textarea si une conversation est sélectionnée
        <?php if ($selected_user): ?>
            document.querySelector('.send-form textarea').focus();
        <?php endif; ?>
    }
</script>
<?php include('footer.php'); ?>