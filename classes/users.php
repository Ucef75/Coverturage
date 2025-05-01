<?php
class User {
    private $id;
    private $username;
    private $email;
    private $isDriver;
    private $isStudent;
    private $region;
    private $score;
    private $db;
    private $password;

    public function __construct($db) {
        // Store the database connection object
        $this->db = $db;
    }

    public function load($userId) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->query($sql, [$userId]);
        
        if ($stmt && $stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $user['id'];
            $this->username = $user['username'];
            $this->email = $user['email'];
            $this->password = $user['password']; // Store the password
            $this->isDriver = (bool)$user['is_driver'];
            $this->isStudent = (bool)$user['is_student'];
            $this->region = $user['Region'];
            $this->score = $user['score'];
            return true;
        }
        return false;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function isDriver() { return $this->isDriver; }
    public function isStudent() { return $this->isStudent; }
    public function getRegion() { return $this->region; }
    public function getScore() { return $this->score; }
    public function getPassword() { return $this->password; }
    public function getDb() { return $this->db; }

    // Setters
    public function setId($id) { $this->id = $id; return $this; }
    public function setUsername($username) { $this->username = $username; return $this; }
    public function setEmail($email) { $this->email = $email; return $this; }
    public function setIsDriver($isDriver) { $this->isDriver = (bool)$isDriver; return $this; }
    public function setIsStudent($isStudent) { $this->isStudent = (bool)$isStudent; return $this; }
    public function setRegion($region) { $this->region = $region; return $this; }
    public function setScore($score) { $this->score = $score; return $this; }
    public function setPassword($password) { $this->password = $password; return $this; }

    public function getCompletedRidesCount() {
        $sql = "SELECT COUNT(*) as count FROM bookings 
                WHERE passenger_id = ? AND status = 'completed'";
                
        $stmt = $this->db->query($sql, [$this->id]);
                
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['count'] : 0;
    }
    
    public function getTotalEarnings() {
        $sql = "SELECT SUM(price) as total FROM bookings 
                WHERE driver_id = ? AND status = 'completed'";
                
        $stmt = $this->db->query($sql, [$this->id]);
                
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['total'] : 0.00;
    }

    public function updateProfile($name, $region) {
        $sql = "UPDATE users SET username = ?, Region = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([$name, $region, $this->id])) {
            // Update local properties if DB update succeeded
            $this->username = $name;
            $this->region = $region;
            return true;
        }
        return false;
    }

    public function changePassword($newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([$hashedPassword, $this->id])) {
            $this->password = $hashedPassword;
            return true;
        }
        return false;
    }
}