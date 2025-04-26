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
        $sql = "SELECT * FROM rides WHERE id = " . $this->db->escape($rideId);
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            $ride = $result->fetch_assoc();
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
        $sql = "SELECT r.* FROM rides r 
                JOIN bookings b ON r.id = b.ride_id 
                WHERE b.passenger_id = " . $this->db->escape($userId) . "
                AND r.departure_time > NOW()
                ORDER BY r.departure_time ASC
                LIMIT 3";
        
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getAvailableRides($region) {
        $sql = "SELECT r.*, u.username as driver_name, u.score as driver_score 
                FROM rides r
                JOIN users u ON r.driver_id = u.id
                WHERE r.available_seats > 0 
                AND r.status = 'active'
                AND u.Region LIKE '%" . $this->db->escape($region) . "%'
                AND r.departure_time > NOW()
                ORDER BY r.departure_time ASC
                LIMIT 5";
        
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getBookedSeats($rideId) {
        $sql = "SELECT COUNT(*) as booked_seats FROM bookings 
                WHERE ride_id = " . $this->db->escape($rideId) . "
                AND status = 'confirmed'";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc()['booked_seats'];
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