<?php
class User {
    private $db;
    private $id;
    private $username;
    private $email;
    private $isDriver;
    private $isStudent;
    private $region;
    private $score;
    
    public function __construct(Database $db) {
        $this->db = $db->getConnection();
    }
    
    public function load($userId) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$userId]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id = $user['id'];
        $this->username = $user['username'];
        $this->email = $user['email'];
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
    
    public function getProfilePicture() {
        return "https://randomuser.me/api/portraits/men/" . ($this->id % 100) . ".jpg";
    }

    public function getCompletedRidesCount() {
        $sql = "SELECT COUNT(*) as count FROM bookings 
                WHERE passenger_id = :passengerId AND status = 'completed'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':passengerId', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['count'] : 0;
    }
    
    public function getTotalEarnings() {
        $sql = "SELECT SUM(price) as total FROM bookings 
                WHERE driver_id = :driverId AND status = 'completed'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':driverId', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['total'] : 0.00;
    }
}
