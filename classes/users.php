<?php
class User {
    private $id;
    private $username;
    private $email;
    private $isDriver;
    private $isStudent;
    private $region;
    private $score;
    private $balance;
    private $password;
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function load(int $userId): bool {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $this->id = $user['id'];
                $this->username = $user['username'];
                $this->email = $user['email'];
                $this->password = $user['password'];
                $this->isDriver = (bool)$user['is_driver'];
                $this->isStudent = (bool)$user['is_student'];
                $this->region = $user['Region'] ?? $user['region'] ?? '';
                $this->score = $user['score'] ?? 5.0;
                $this->balance = (float)$user['Balance'] ?? (float)$user['balance'] ?? 0.0;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error loading user: " . $e->getMessage());
            return false;
        }
    }

    // Balance management functions
    public function getBalance(): float {
        return $this->balance;
    }

    public function addBalance(float $amount): bool {
        if ($amount <= 0) {
            return false;
        }

        try {
            $this->db->beginTransaction();
            
            // Get current balance
            $stmt = $this->db->prepare("SELECT Balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$this->id]);
            $currentBalance = (float)$stmt->fetchColumn();
            
            // Update balance
            $newBalance = $currentBalance + $amount;
            $updateStmt = $this->db->prepare("UPDATE users SET Balance = ? WHERE id = ?");
            $success = $updateStmt->execute([$newBalance, $this->id]);
            
            if ($success) {
                $this->balance = $newBalance;
                $this->db->commit();
                return true;
            }
            
            $this->db->rollBack();
            return false;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error adding balance: " . $e->getMessage());
            return false;
        }
    }

    public function deductBalance(float $amount): bool {
        if ($amount <= 0) {
            return false;
        }

        try {
            $this->db->beginTransaction();
            
            // Get current balance
            $stmt = $this->db->prepare("SELECT Balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$this->id]);
            $currentBalance = (float)$stmt->fetchColumn();
            
            // Check if sufficient balance
            if ($currentBalance < $amount) {
                $this->db->rollBack();
                return false;
            }
            
            // Update balance
            $newBalance = $currentBalance - $amount;
            $updateStmt = $this->db->prepare("UPDATE users SET Balance = ? WHERE id = ?");
            $success = $updateStmt->execute([$newBalance, $this->id]);
            
            if ($success) {
                $this->balance = $newBalance;
                $this->db->commit();
                return true;
            }
            
            $this->db->rollBack();
            return false;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error deducting balance: " . $e->getMessage());
            return false;
        }
    }

    public function transferBalance(float $amount, User $recipient): bool {
        if ($amount <= 0 || $this->id === $recipient->getId()) {
            return false;
        }

        try {
            $this->db->beginTransaction();
            
            // Verify recipient exists
            if (!$recipient->load($recipient->getId())) {
                $this->db->rollBack();
                return false;
            }
            
            // Deduct from sender
            if (!$this->deductBalance($amount)) {
                $this->db->rollBack();
                return false;
            }
            
            // Add to recipient
            if (!$recipient->addBalance($amount)) {
                $this->db->rollBack();
                return false;
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error transferring balance: " . $e->getMessage());
            return false;
        }
    }

    // Existing methods (unchanged except for toArray)
    public function updateProfile(string $username, string $region): bool {
        try {
            $stmt = $this->db->prepare("UPDATE users SET username = ?, region = ? WHERE id = ?");
            $success = $stmt->execute([$username, $region, $this->id]);
            
            if ($success) {
                $this->username = $username;
                $this->region = $region;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error updating profile: " . $e->getMessage());
            return false;
        }
    }

    public function changePassword(string $currentPassword, string $newPassword): bool {
        if (!password_verify($currentPassword, $this->password)) {
            return false;
        }

        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $success = $stmt->execute([$hashedPassword, $this->id]);
            
            if ($success) {
                $this->password = $hashedPassword;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error changing password: " . $e->getMessage());
            return false;
        }
    }

    public function deleteAccount(): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$this->id]);
        } catch (PDOException $e) {
            error_log("Error deleting account: " . $e->getMessage());
            return false;
        }
    }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setUsername(string $username): void { $this->username = $username; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setIsDriver(bool $isDriver): void { $this->isDriver = $isDriver; }
    public function setIsStudent(bool $isStudent): void { $this->isStudent = $isStudent; }
    public function setRegion(?string $region): void { $this->region = $region; }
    public function setScore(float $score): void { $this->score = $score; }
    public function setBalance(float $balance): void { $this->balance = $balance; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'is_driver' => $this->isDriver,
            'is_student' => $this->isStudent,
            'region' => $this->region,
            'score' => $this->score,
            'balance' => $this->balance
        ];
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUsername(): ?string { return $this->username; }
    public function getEmail(): ?string { return $this->email; }
    public function isDriver(): bool { return $this->isDriver; }
    public function isStudent(): bool { return $this->isStudent; }
    public function getRegion(): ?string { return $this->region; }
    public function getScore(): float { return $this->score; }
    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->password);
    }
}