<?php
class Database {
    private $connection;
    private $dbPath;
    
    public function __construct() {
        // Set path to your SQLite database file
        $this->dbPath = '../database/DB.db';
        $this->connect();
    }
    
    private function connect() {
        try {
            // Create SQLite connection
            $this->connection = new PDO("sqlite:" . $this->dbPath);
            // Set error mode to exceptions
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute($params);
            if (stripos($sql, 'SELECT') === 0) {
                return $stmt;
            }
            return $success;
        } catch (PDOException $e) {
            die("Query error: " . $e->getMessage()); // <-- show full error
            return false;
        }
    }
    
    public function escape($value) {
        // Not needed with prepared statements, but kept for compatibility
        return $value;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function __destruct() {
        $this->connection = null;
    }
}