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
        $this->db = $db;
    }
    
    public function load($userId) {
        $sql = "SELECT * FROM users WHERE id = " . $this->db->escape($userId);
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
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
        // In a real app, this would come from the database
        return "https://randomuser.me/api/portraits/men/" . ($this->id % 100) . ".jpg";
    }
    public function getCompletedRidesCount() {
        $sql = "SELECT COUNT(*) as count FROM bookings 
                WHERE passenger_id = " . $this->db->escape($this->getId()) . "
                AND status = 'completed'";
        
        $result = $this->db->query($sql);
        if ($result === false) {
            return 0;
        }
        return $result->fetch_assoc()['count'];
    }
    
    public function getTotalEarnings() {
        $sql = "SELECT SUM(price) as total FROM bookings 
                WHERE driver_id = " . $this->db->escape($this->getId()) . "
                AND status = 'completed'";
        
        $result = $this->db->query($sql);
        if ($result === false) {
            return 0.00;
        }
        return (float)$result->fetch_assoc()['total'] ?? 0.00;
    }
}