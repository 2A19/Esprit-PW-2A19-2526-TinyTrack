<?php
require_once __DIR__ . '/../config/db.php';

abstract class Model {

    protected function db(): PDO {
        return Database::getInstance()->getConnection();
    }
}
