<?php
session_start();
include '../connect.php';

if (isset($_POST['id_user'], $_POST['role_user'])) {
    $id = intval($_POST['id_user']);
    $role = intval($_POST['role_user']);

    // Met à jour le rôle de l'utilisateur
    $stmt = $db->prepare("UPDATE tbl_users SET role_user = ? WHERE id_user = ?");
    $stmt->execute([$role, $id]);

    // Redirection après mise à jour
    header("Location: admin.php");
    exit();
} else {
    echo "Données manquantes.";
}
