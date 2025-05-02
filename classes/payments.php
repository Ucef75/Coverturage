<?php
class Payments {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function getPaymentHistory(int $userId): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT *, 'payment' as type FROM payments 
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
            $stmt = $this->pdo->prepare("
                INSERT INTO payments (user_id, amount, description)
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([$userId, $amount, $description]);
        } catch (PDOException $e) {
            error_log("Add payment error: " . $e->getMessage());
            return false;
        }
    }
}