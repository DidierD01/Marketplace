<?php
// Configuration de la base de données
$config = [
    'host'     => 'localhost:3306',       // Adresse du serveur de base de données
    'dbname'   => 'db_market',       // Nom de la base de données
    'username' => 'root', // Nom d'utilisateur de la base de données
    'password' => '', // Mot de passe de la base de données
    'charset'  => 'utf8',            // Encodage des caractères
];

try {
    // Connexion à la base de données avec PDO
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $db = new PDO($dsn, $config['username'], $config['password']);

    // Configuration des options PDO
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Activer les exceptions pour les erreurs
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Retourner les résultats sous forme de tableau associatif
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Désactiver l'émulation des requêtes préparées pour plus de sécurité

} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Fonction utilitaire pour exécuter des requêtes préparées
function executeQuery($db, $sql, $params = []) {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Fonction utilitaire pour récupérer un seul enregistrement
function fetchSingle($db, $sql, $params = []) {
    $stmt = executeQuery($db, $sql, $params);
    return $stmt->fetch();
}

// Fonction utilitaire pour récupérer tous les enregistrements
function fetchAll($db, $sql, $params = []) {
    $stmt = executeQuery($db, $sql, $params);
    return $stmt->fetchAll();
}

// ✅ Classe ajoutée pour compatibilité avec forgot.php
class Database {
    public function getConnection() {
        global $db;
        return $db;
    }
}

?>