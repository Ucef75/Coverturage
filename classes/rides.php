<?php
class Ride {
    private $db;
    private $id;
    private $driverId;
    private $fromLocation;
    private $toLocation;
    private $departureTime;
    private $price;
    private $availableSeats;
    private $status;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function load($rideId) {
        if (empty($rideId)) {
            return false;
        }
        
        $sql = "SELECT * FROM rides WHERE id = :rideId";
        $stmt = $this->db->query($sql, ['rideId' => $rideId]);
        
        if ($stmt && $stmt->rowCount() > 0) {
            $ride = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $ride['id'];
            $this->driverId = $ride['driver_id'];
            $this->fromLocation = $ride['from_location'];
            $this->toLocation = $ride['to_location'];
            $this->departureTime = $ride['departure_time'];
            $this->price = $ride['price'];
            $this->availableSeats = $ride['available_seats'];
            $this->status = $ride['status'];
            return true;
        }
        return false;
    }
    
    public function getUpcomingRides($userId) {
        if (empty($userId)) {
            return [];
        }
        
        $sql = "SELECT r.* FROM rides r 
                JOIN bookings b ON r.id = b.ride_id 
                WHERE b.passenger_id = :userId
                AND r.departure_time > NOW()
                ORDER BY r.departure_time ASC
                LIMIT 3";
        
        $stmt = $this->db->query($sql, ['userId' => $userId]);
        if ($stmt) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }
    
    public function getAvailableRides($region) {
        if (empty($region)) {
            return [];
        }
        
        $sql = "SELECT r.*, u.username as driver_name, u.score as driver_score 
                FROM rides r
                JOIN users u ON r.driver_id = u.id
                WHERE r.available_seats > 0 
                AND r.status = 'active'
                AND u.Region LIKE :region
                AND r.departure_time > NOW()
                ORDER BY r.departure_time ASC
                LIMIT 5";
        
        $stmt = $this->db->query($sql, ['region' => "%$region%"]);
        if ($stmt) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }
    
    public function getBookedSeats($rideId) {
        if (empty($rideId)) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) as booked_seats FROM bookings 
                WHERE ride_id = :rideId
                AND status = 'confirmed'";
        
        $stmt = $this->db->query($sql, ['rideId' => $rideId]);
        if ($stmt) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['booked_seats'] ?? 0);
        }
        return 0;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getDriverId() { return $this->driverId; }
    public function getFromLocation() { return $this->fromLocation; }
    public function getToLocation() { return $this->toLocation; }
    public function getDepartureTime() { return $this->departureTime; }
    public function getPrice() { return $this->price; }
    public function getAvailableSeats() { return $this->availableSeats; }
    public function getStatus() { return $this->status; }
}
?>
