<?php
class Payments {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function getPaymentHistory(int $userId): array {
        try {
            $this->ensureTableExists('payments');
            
            $stmt = $this->pdo->prepare("
                SELECT id, user_id, amount, description, created_at, 'payment' as type 
                FROM payments 
                WHERE user_id = ? 
                ORDER BY created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Payment history error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getUserBalance(int $userId): float {
        try {
            $this->ensureTableExists('payments');
            $this->ensureTableExists('rides');
            
            // Get wallet balance
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as balance 
                FROM payments 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $walletBalance = (float)$stmt->fetchColumn();
            
            // Add driver earnings if applicable
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(driver_earnings), 0) as earnings 
                FROM rides 
                WHERE driver_id = ? AND status = 'completed'
            ");
            $stmt->execute([$userId]);
            $rideEarnings = (float)$stmt->fetchColumn();
            
            return $walletBalance + $rideEarnings;
        } catch (PDOException $e) {
            error_log("Balance error: " . $e->getMessage());
            return 0.00;
        }
    }
    
    public function addPayment(int $userId, float $amount, string $description): bool {
        try {
            if ($userId <= 0) {
                throw new InvalidArgumentException("Invalid user ID");
            }
            
            if ($amount <= 0) {
                throw new InvalidArgumentException("Invalid amount");
            }
            
            $this->ensureTableExists('payments');
            
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO payments (user_id, amount, description, created_at)
                VALUES (?, ?, ?, datetime('now'))
            ");
            
            $stmt->execute([$userId, $amount, $description]);
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Payment Exception: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getLastTransaction($userId) {
        $stmt = $this->pdo->prepare("
            SELECT *, 'payment' as type 
            FROM payments 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function ensureTableExists(string $tableName): bool {
        try {
            // SQLite specific table check
            $stmt = $this->pdo->prepare("
                SELECT name FROM sqlite_master 
                WHERE type='table' AND name=?
            ");
            $stmt->execute([$tableName]);
            
            if ($stmt->fetch() === false) {
                switch ($tableName) {
                    case 'payments':
                        $this->pdo->exec("
                            CREATE TABLE payments (
                                id INTEGER PRIMARY KEY AUTOINCREMENT,
                                user_id INTEGER NOT NULL,
                                amount REAL NOT NULL,
                                description TEXT NOT NULL,
                                created_at TEXT DEFAULT CURRENT_TIMESTAMP
                            )
                        ");
                        $this->pdo->exec("CREATE INDEX idx_payments_user_id ON payments(user_id)");
                        error_log("Created missing payments table");
                        break;
                        
                    case 'rides':
                        // Don't create rides table here
                        error_log("Rides table does not exist");
                        return false;
                        
                    default:
                        error_log("Unknown table: $tableName");
                        return false;
                }
            }
            return true;
        } catch (PDOException $e) {
            error_log("Table check error: " . $e->getMessage());
            return false;
        }
    }
}