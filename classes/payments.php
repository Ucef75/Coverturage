<?php
class Payments {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    // Récupérer l'historique des paiements
    public function getPaymentHistory(int $userId): array {
        try {
            $stmt = $this->pdo->prepare("SELECT id, user_id, amount, description, created_at, 'payment' as type FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'historique des paiements : " . $e->getMessage());
            return [];
        }
    }
    
    // Ajouter un paiement
    public function addPayment(int $userId, float $amount, string $description): bool {
        try {
            if ($userId <= 0 || $amount <= 0) {
                throw new InvalidArgumentException("ID utilisateur ou montant invalide");
            }
            
            $stmt = $this->pdo->prepare("INSERT INTO payments (user_id, amount, description, created_at) VALUES (?, ?, ?, datetime('now'))");
            $stmt->execute([$userId, $amount, $description]);
            
            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de l'ajout du paiement : " . $e->getMessage());
            return false;
        }
    }
}
