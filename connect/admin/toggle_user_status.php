<?php
session_start();
include '../connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Récupérer le statut actuel de l'utilisateur
    $stmt = $db->prepare("SELECT active_user FROM tbl_users WHERE id_user = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $newStatus = $user['active_user'] == 1 ? 0 : 1;

        // Mise à jour du statut
        $update = $db->prepare("UPDATE tbl_users SET active_user = ? WHERE id_user = ?");
        $update->execute([$newStatus, $id]);
    }
}

header('Location: admin.php');
exit();
