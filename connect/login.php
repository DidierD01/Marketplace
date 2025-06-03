<?php 
session_start();

if (isset($_POST['mail_user']) && isset($_POST['password_user'])) {

    // Inclusion de la connexion à la base de données
    include "connect.php";

    // Récupération des valeurs envoyées via POST
    $mail_user = $_POST['mail_user'];
    $password_user = $_POST['password_user'];

    // Initialisation de la variable pour la redirection
    $data = "mail_user=" . $mail_user;

    // Vérification des champs vides
    if (empty($mail_user)) {
        $em = "L'email est requis";
        header("Location: ../login.php?error=$em&$data");
        exit;
    } else if (empty($password_user)) {
        $em = "Le mot de passe est requis";
        header("Location: ../login.php?error=$em&$data");
        exit;
    } else {
        // Requête SQL pour vérifier l'utilisateur par email
        $sql = "SELECT id_user, mail_user, password_user, role_user FROM tbl_users WHERE mail_user = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$mail_user]);

        // Vérification si un utilisateur existe
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch();

            // Récupération des données de l'utilisateur
            $id_user = $user['id_user'];
            $mail_user_db = $user['mail_user'];
            $password_user_db = $user['password_user'];
            $role_user = $user['role_user'];

            // Vérification du mot de passe
            if (password_verify($password_user, $password_user_db)) {
                // Initialisation de la session
                $_SESSION['id_user'] = $id_user;
                $_SESSION['mail_user'] = $mail_user_db;
                $_SESSION['role_user'] = $role_user;

                // Redirection selon le type d'utilisateur
                switch ($role_user) {
                    case 0:
                        // Redirige vers la page du buyer
                        header("Location: buyer/moncompte.php");
                        break;
                    case 1:
                        // Redirige vers la page du seller
                        header("Location: seller/moncompte.php");
                        break;
                    case 2:
                        // Redirige vers la page de l'admin
                        header("Location: admin/compteadmin.php");
                        break;
                    default:
                        // Si le rôle n'est pas valide
                        $em = "Rôle utilisateur non valide.";
                        header("Location: ../login.php?error=$em&$data");
                        exit;
                }
                exit;
            } else {
                // Si le mot de passe est incorrect
                $em = "Mot de passe incorrect";
                header("Location: ../login.php?error=$em&$data");
                exit;
            }
        } else {
            // Si l'email n'est pas trouvé
            $em = "Aucun utilisateur trouvé avec cet email";
            header("Location: ../login.php?error=$em&$data");
            exit;
        }
    }
} else {
    // Redirige vers la page de login si aucune donnée n'est envoyée
    header("Location: ../login.php");
    exit;
}
?>
