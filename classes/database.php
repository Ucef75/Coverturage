<?php
class Database {
    private PDO $connection;

    public function __construct(string $dbPath = '../Database/DB.db') {
        try {
            $this->connection = new PDO("sqlite:" . $dbPath);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
}
