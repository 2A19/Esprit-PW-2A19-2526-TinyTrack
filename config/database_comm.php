<?php
/**
 * Module : Communication parents
 * @author Rajhi Amen Allah <amenallah.rajhi@esprit.tn>
 */
// Renomme en CommDatabase pour eviter conflit avec la classe Database
// existante de TinyTrack (config/db.php, pattern singleton).
class CommDatabase {
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $dbname = "tinytrack";

    public function connect() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=utf8mb4";
            $pdo = new PDO($dsn, $this->user, $this->password);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}
