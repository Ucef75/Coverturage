<?php
class Ride {
    private $pdo;
    private $id;
    private $driverId;
    private $fromLocation;
    private $toLocation;
    private $departureTime;
    private $price;
    private $availableSeats;
    private $status;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function load(int $rideId): bool {
        if ($rideId <= 0) {
            return false;
        }
        
        try {
            $sql = "SELECT * FROM rides WHERE id = :rideId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':rideId', $rideId, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
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
        } catch (PDOException $e) {
            error_log("Error loading ride: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUpcomingRides(int $userId): array {
        if ($userId <= 0) {
            return [];
        }
        
        try {
            $sql = "SELECT r.* FROM rides r 
                    JOIN bookings b ON r.id = b.ride_id 
                    WHERE b.passenger_id = :userId
                    AND r.departure_time > NOW()
                    AND r.status = 'active'
                    ORDER BY r.departure_time ASC
                    LIMIT 3";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting upcoming rides: " . $e->getMessage());
            return [];
        }
    }

    public function getBookedSeats(int $rideId): int {
        if ($rideId <= 0) {
            return 0;
        }
        
        try {
            $sql = "SELECT COUNT(*) as booked_seats FROM bookings 
                    WHERE ride_id = :rideId
                    AND status = 'confirmed'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':rideId', $rideId, PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['booked_seats'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getting booked seats: " . $e->getMessage());
            return 0;
        }
    }
    
    // Type-hinted getters
    public function getId(): ?int { return $this->id; }
    public function getDriverId(): ?int { return $this->driverId; }
    public function getFromLocation(): ?string { return $this->fromLocation; }
    public function getToLocation(): ?string { return $this->toLocation; }
    public function getDepartureTime(): ?string { return $this->departureTime; }
    public function getPrice(): ?float { return $this->price; }
    public function getAvailableSeats(): ?int { return $this->availableSeats; }
    public function getStatus(): ?string { return $this->status; }
}