<?php

class Vehicle {
    public $id;
    public $user_id;
    public $type;
    public $extras;
    public $seats;
    public $plate_number;

    public function __construct($id = null, $user_id = null, $type = null, $extras = null, 
                                $seats = null, $plate_number = null) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->type = $type;
        $this->extras = $extras;
        $this->seats = $seats;
        $this->plate_number = $plate_number;
    }

    public function save() {
        // Logic to save the vehicle to the database
    }

    public static function find($id) {
        // Logic to find a vehicle by ID
    }
}