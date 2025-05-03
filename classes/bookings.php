<?php

class Booking {
    private $id;
    private $ride_id;
    private $passenger_id;
    private $seats;
    private $paid_amount;
    private $status;
    private $created_at;

    private static $pdo;

    // Set the PDO connection (should be called once at application start)
    public static function setPDO(PDO $pdo) {
        self::$pdo = $pdo;
    }

    // Constructor with optional initialization
    public function __construct(
        $ride_id = null,
        $passenger_id = null,
        $seats = null,
        $paid_amount = 0,
        $status = 'confirmed'
    ) {
        $this->ride_id = $ride_id;
        $this->passenger_id = $passenger_id;
        $this->seats = $seats;
        $this->paid_amount = $paid_amount;
        $this->status = $status;
        $this->created_at = date('Y-m-d H:i:s');
    }

    // Getters
    public function getId() { return $this->id; }
    public function getRideId() { return $this->ride_id; }
    public function getPassengerId() { return $this->passenger_id; }
    public function getSeats() { return $this->seats; }
    public function getPaidAmount() { return $this->paid_amount; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->created_at; }

    // Setters with basic validation
    public function setRideId($ride_id) { 
        if (!is_numeric($ride_id)) {
            throw new InvalidArgumentException("Ride ID must be numeric");
        }
        $this->ride_id = $ride_id;
        return $this;
    }

    public function setPassengerId($passenger_id) { 
        if (!is_numeric($passenger_id)) {
            throw new InvalidArgumentException("Passenger ID must be numeric");
        }
        $this->passenger_id = $passenger_id;
        return $this;
    }

    public function setSeats($seats) { 
        if (!is_numeric($seats) || $seats < 1) {
            throw new InvalidArgumentException("Seats must be a positive number");
        }
        $this->seats = $seats;
        return $this;
    }

    public function setPaidAmount($paid_amount) { 
        if (!is_numeric($paid_amount) || $paid_amount < 0) {
            throw new InvalidArgumentException("Paid amount must be a non-negative number");
        }
        $this->paid_amount = $paid_amount;
        return $this;
    }

    public function setStatus($status) { 
        $allowed = ['confirmed', 'cancelled', 'completed', 'pending'];
        if (!in_array($status, $allowed)) {
            throw new InvalidArgumentException("Invalid status value");
        }
        $this->status = $status;
        return $this;
    }

    // CRUD Operations

    // Create or update booking
    public function save() {
        if (!self::$pdo) {
            throw new RuntimeException("PDO connection not initialized");
        }

        if ($this->id) {
            // Update existing record
            $stmt = self::$pdo->prepare(
                "UPDATE bookings SET 
                    ride_id = :ride_id,
                    passenger_id = :passenger_id,
                    seats = :seats,
                    paid_amount = :paid_amount,
                    status = :status
                WHERE id = :id"
            );
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        } else {
            // Insert new record
            $stmt = self::$pdo->prepare(
                "INSERT INTO bookings (
                    ride_id, passenger_id, seats, paid_amount, status, created_at
                ) VALUES (
                    :ride_id, :passenger_id, :seats, :paid_amount, :status, :created_at
                )"
            );
            $stmt->bindParam(':created_at', $this->created_at);
        }

        $stmt->bindParam(':ride_id', $this->ride_id, PDO::PARAM_INT);
        $stmt->bindParam(':passenger_id', $this->passenger_id, PDO::PARAM_INT);
        $stmt->bindParam(':seats', $this->seats, PDO::PARAM_INT);
        $stmt->bindParam(':paid_amount', $this->paid_amount);
        $stmt->bindParam(':status', $this->status);

        if ($stmt->execute()) {
            if (!$this->id) {
                $this->id = self::$pdo->lastInsertId();
            }
            return true;
        }
        return false;
    }

    // Find booking by ID (static method)
    public static function find($id) {
        if (!self::$pdo) {
            throw new RuntimeException("PDO connection not initialized");
        }

        $stmt = self::$pdo->prepare("SELECT * FROM bookings WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $booking = new Booking();
            $booking->id = $row['id'];
            $booking->ride_id = $row['ride_id'];
            $booking->passenger_id = $row['passenger_id'];
            $booking->seats = $row['seats'];
            $booking->paid_amount = $row['paid_amount'];
            $booking->status = $row['status'];
            $booking->created_at = $row['created_at'];
            return $booking;
        }
        return null;
    }

    // Find all bookings (static method)
    public static function all() {
        if (!self::$pdo) {
            throw new RuntimeException("PDO connection not initialized");
        }

        $stmt = self::$pdo->query("SELECT * FROM bookings ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Booking');
    }

    // Delete booking
    public function delete() {
        if (!$this->id) {
            return false;
        }

        $stmt = self::$pdo->prepare("DELETE FROM bookings WHERE id = :id");
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Convert to array (for JSON responses)
    public function toArray() {
        return [
            'id' => $this->id,
            'ride_id' => $this->ride_id,
            'passenger_id' => $this->passenger_id,
            'seats' => $this->seats,
            'paid_amount' => $this->paid_amount,
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
    }
}