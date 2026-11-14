<?php
class Model {
    protected $db;

    public function __construct() {
        // Since Database is a singleton or instantiated here
        // We will just create a new PDO connection directly for simplicity,
        // or utilize the Database class we created.
        $this->db = new Database();
    }
    
    // Helper to get PDO instance from Database
    protected function getPDO() {
        return $this->db->getConnection();
    }
}
