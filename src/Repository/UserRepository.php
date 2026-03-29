<?php
namespace Spn\Repository;

use Spn\Database\Connection;

class UserRepository{
    private $conn;
    
    public function __construct(){
        $this->conn = Connection::get();
    }
    
    public function findById(int $id):mixed{
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ? AND deleted = 0");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $res->free();
        $stmt->close();
        return $user;
    }
    
    public function findByName(string $username):mixed{
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ? AND deleted = 0");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $res->free();
        $stmt->close();
        return $user;
    }
    
    public function save(array $data):bool{
        $stmt = $this->conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $data['username'], $data['password'], $data['email']);
        
        $user = $stmt->execute();
        $stmt->close();
        return $user;
    }
    
    public function remove(int $id):bool{
        $stmt = $this->conn->prepare("UPDATE users SET deleted = true WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        $user = $stmt->execute();
        $stmt->close();
        return $user;
    }
}