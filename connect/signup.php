<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (
    isset($_POST['nom_user']) && 
    isset($_POST['prenom_user']) && 
    isset($_POST['mail_user']) &&
    isset($_POST['password_user']) &&
    isset($_POST['role_user'])
) {
    include 'connect.php';

    $nom_user = $_POST['nom_user'];
    $prenom_user = $_POST['prenom_user'];
    $mail_user = $_POST['mail_user'];
    $password_user = $_POST['password_user']; // Mot de passe non hashé pour la validation
    $role_user = (int)$_POST['role_user']; // Conversion explicite en entier
    $createdate_user = date('Y-m-d H:i:s');
    $modifdate_user = date('Y-m-d H:i:s');

    $data = "&nom_user=$nom_user&prenom_user=$prenom_user&mail_user=$mail_user";

    if (empty($nom_user)) {
        $em = "Le champ nom est requis";
        header("Location: index.php?error=$em&$data");
        exit;
    } elseif (empty($prenom_user)) {
        $em = "Le champ prénom est requis";
        header("Location: index.php?error=$em&$data");
        exit;
    } elseif (empty($mail_user)) {
        $em = "Le champ email est requis";
        header("Location: index.php?error=$em&$data");
        exit;
    } elseif (empty($password_user)) {
        $em = "Le champ mot de passe est requis";
        header("Location: index.php?error=$em&$data");
        exit;
    } else {
        // Vérifier si l'adresse e-mail existe déjà
        $sql_check_email = "SELECT mail_user FROM tbl_users WHERE mail_user = ?";
        $query_check_email = $db->prepare($sql_check_email);
        $query_check_email->execute([$mail_user]);

        if ($query_check_email->rowCount() > 0) {
            // L'adresse e-mail existe déjà
            $em = "Cette adresse e-mail est déjà utilisée";
            header("Location: index.php?error=$em&$data");
            exit;
        } else {
            // Vérifier la sécurité du mot de passe
            $password_errors = [];

            // Longueur minimale
            if (strlen($password_user) < 8) {
                $password_errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
            }

            // Au moins un chiffre
            if (!preg_match('/\d/', $password_user)) {
                $password_errors[] = "Le mot de passe doit contenir au moins un chiffre.";
            }

            // Au moins une lettre majuscule
            if (!preg_match('/[A-Z]/', $password_user)) {
                $password_errors[] = "Le mot de passe doit contenir au moins une lettre majuscule.";
            }

            // Au moins une lettre minuscule
            if (!preg_match('/[a-z]/', $password_user)) {
                $password_errors[] = "Le mot de passe doit contenir au moins une lettre minuscule.";
            }

            // Au moins un caractère spécial
            if (!preg_match('/[^a-zA-Z\d]/', $password_user)) {
                $password_errors[] = "Le mot de passe doit contenir au moins un caractère spécial.";
            }

            // Si des erreurs sont trouvées
            if (!empty($password_errors)) {
                $em = "Le mot de passe n'est pas sécurisé : " . implode(" ", $password_errors);
                header("Location: index.php?error=$em&$data");
                exit;
            }

            // Si le mot de passe est valide, procéder à l'insertion
            $hashed_password = password_hash($password_user, PASSWORD_DEFAULT); // Hashage du mot de passe

            $sql = "INSERT INTO tbl_users(nom_user, prenom_user, mail_user, password_user, role_user, createdate_user, modifdate_user) 
                    VALUES(?, ?, ?, ?, ?, ?, ?)";
            $query = $db->prepare($sql);

            if ($query->execute([$nom_user, $prenom_user, $mail_user, $hashed_password, $role_user, $createdate_user, $modifdate_user])) {
                header('Location: login.php?success=Votre compte a été créé avec succès');
                exit;
            } else {
                header('Location: index.php?error=Erreur lors de la création du compte');
                exit;
            }
        }
    }
}
?>