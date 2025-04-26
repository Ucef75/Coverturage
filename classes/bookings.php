<?php

class Booking {
    public $id;
    public $ride_id;
    public $passenger_id;
    public $seats;
    public $payment_method;
    public $paid_amount;
    public $status;
    public $created_at;

    public function __construct($id = null, $ride_id = null, $passenger_id = null, $seats = null, 
                                $payment_method = 'cash', $paid_amount = 0, $status = 'confirmed') {
        $this->id = $id;
        $this->ride_id = $ride_id;
        $this->passenger_id = $passenger_id;
        $this->seats = $seats;
        $this->payment_method = $payment_method;
        $this->paid_amount = $paid_amount;
        $this->status = $status;
        $this->created_at = date('Y-m-d H:i:s');
    }

    public function save() {
        // Logic to save the booking to the database
    }

    public static function find($id) {
        // Logic to find a booking by ID
    }
}