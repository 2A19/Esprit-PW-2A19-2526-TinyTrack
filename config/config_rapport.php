<?php
class Config {
    private static $pdo = null;

    public static function getConnexion() {
        if (self::$pdo == null) {
            try {
                self::$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=tinytrack;charset=utf8", "root", "");
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                die("Erreur : " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
?>