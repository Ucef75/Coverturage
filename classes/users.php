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
    private $picture;
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
                
                // Handle case-sensitive region column
                $this->region = $user['Region'] ?? $user['region'] ?? '';
                
                // Handle numeric values with defaults
                $this->score = (float)($user['score'] ?? 5.0);
                
                // Handle case-sensitive balance column
                $this->balance = (float)($user['balance'] ?? 0.0);
                
                // Add profile picture support (new)
                $this->picture = $user['picture'] ?? null;
                
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error loading user #$userId: " . $e->getMessage());
            return false;
        }
    }

    // Balance management functions
    public function addBalance(float $amount): bool {
        if ($amount <= 0) {
            error_log("Invalid amount: $amount");
            return false;
        }
    
        try {
            $this->db->beginTransaction();
            
            // Use exact column name "Balance" as in your table
            $stmt = $this->db->prepare("SELECT \"Balance\" FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$this->id]);
            $currentBalance = (float)$stmt->fetchColumn();
            
            if ($currentBalance === false) {
                $this->db->rollBack();
                error_log("User not found: {$this->id}");
                return false;
            }
            
            $newBalance = $currentBalance + $amount;
            
            // Use exact column name "Balance" here too
            $updateStmt = $this->db->prepare("UPDATE users SET \"Balance\" = ? WHERE id = ?");
            $success = $updateStmt->execute([$newBalance, $this->id]);
            
            if ($success && $updateStmt->rowCount() > 0) {
                $this->db->commit();
                $this->balance = $newBalance;
                return true;
            }
            
            $this->db->rollBack();
            error_log("Update failed for user {$this->id}");
            return false;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function deductBalance(float $amount): bool {
        if ($amount <= 0) return false;

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("SELECT Balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$this->id]);
            $currentBalance = (float)$stmt->fetchColumn();

            if ($currentBalance < $amount) {
                $this->db->rollBack();
                return false;
            }

            return $this->updateBalance(-$amount);
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error deducting balance: " . $e->getMessage());
            return false;
        }
    }

    public function transferBalance(float $amount, User $recipient): bool {
        if ($amount <= 0 || $this->id === $recipient->getId()) return false;

        try {
            $this->db->beginTransaction();
            
            if (!$recipient->load($recipient->getId()) || !$this->deductBalance($amount) || !$recipient->addBalance($amount)) {
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

    // Profile update functions
    public function updateProfile(string $username, string $region): bool {
        return $this->updateUserInfo($username, $region);
    }

    private function updateUserInfo(string $username, string $region): bool {
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
    public function getProfilePicture(): ?string {
        return $this->picture;
    }
    
    public function setProfilePicture(string $path): void {
        $this->picture = $path;
    }
    public function saveProfilePicture(): bool {
        $stmt = $this->db->prepare("UPDATE users SET picture = ? WHERE id = ?");
        return $stmt->execute([$this->picture, $this->id]);
    }
    

    // Setters and Getters
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

    public function getId(): ?int { return $this->id; }
    public function getUsername(): ?string { return $this->username; }
    public function getEmail(): ?string { return $this->email; }
    public function isDriver(): bool { return $this->isDriver; }
    public function isStudent(): bool { return $this->isStudent; }
    public function getRegion(): ?string { return $this->region; }
    public function getScore(): float { return $this->score; }
    public function verifyPassword(string $password): bool { return password_verify($password, $this->password); }
    public function getBalance(): float { return $this->balance;}
}
