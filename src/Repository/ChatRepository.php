<?php
namespace Spn\Repository;

use Spn\Database\Connection;

class ChatRepository{
    private $conn;
    
    public function __construct(){
        $this->conn = Connection::get();
    }
}