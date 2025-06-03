<?php  
// Démarrer la session
session_start();

// Détruire toutes les variables de session
session_unset(); 

// Détruire la session elle-même
session_destroy(); 

// Rediriger l'utilisateur vers la page de login
header("Location: ../login.php"); 
exit;  // Assurez-vous que l'exécution est arrêtée ici
?>
