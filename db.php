<?php
// db.php : connexion sécurisée à la base de données via PDO
function getPDO() {
    static $pdo = null;
    if ($pdo === null) {
        $host    = 'djpreszsite.mysql.db';
        $db      = 'djpreszsite';
        $user    = 'djpreszsite';
        $pass    = 'Loiattraction1921';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base : " . $e->getMessage());
            die("❌ Erreur de connexion à la base de données.");
        }
    }
    return $pdo;
}
?>
