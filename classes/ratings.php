<?php

class Rating {
    public $id;
    public $ride_id;
    public $from_user_id;
    public $to_user_id;
    public $score;
    public $comment;
    public $created_at;

    public function __construct($id = null, $ride_id = null, $from_user_id = null, $to_user_id = null, 
                                $score = null, $comment = null) {
        $this->id = $id;
        $this->ride_id = $ride_id;
        $this->from_user_id = $from_user_id;
        $this->to_user_id = $to_user_id;
        $this->score = $score;
        $this->comment = $comment;
        $this->created_at = date('Y-m-d H:i:s');
    }

    public function save() {
        // Logic to save the rating to the database
    }

    public static function find($id) {
        // Logic to find a rating by ID
    }
}